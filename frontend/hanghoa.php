<?php
include '../backend/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Phần xử lý phiếu nhập/xuất
if (isset($_POST['save_nhap_xuat'])) {
    $hanghoa_id = $_POST['hanghoa_id'];
    $loai = $_POST['loai']; // 'nhap' hoặc 'xuat'
    $so_luong = (float) $_POST['so_luong'];
    $ghi_chu = $conn->real_escape_string($_POST['ghi_chu'] ?? '');

    // Lưu vào bảng phiếu nhập/xuất
    $conn->query("
        INSERT INTO phieu_nhap_xuat (hanghoa_id, loai, so_luong, ghi_chu)
        VALUES ($hanghoa_id, '$loai', $so_luong, '$ghi_chu')
    ");

    // Cập nhật tồn kho
    $sign = $loai === 'nhap' ? '+' : '-';
    $conn->query("
        UPDATE hanghoa SET so_luong = so_luong $sign $so_luong WHERE id = $hanghoa_id
    ");

    header("Location: hanghoa.php?msg=done");
    exit;
}

// Lọc và phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$cond_sql = " WHERE 1=1";

if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $cond_sql .= " AND ten LIKE '%$search%'";
}
if (!empty($_GET['filter_loai'])) {
    $filter_loai = $conn->real_escape_string($_GET['filter_loai']);
    $cond_sql .= " AND loai = '$filter_loai'";
}
if (!empty($_GET['filter_kieu'])) {
    $kieu = $conn->real_escape_string($_GET['filter_kieu']);
    $cond_sql .= " AND kieu = '$kieu'";
}
if (!empty($_GET['filter_ncc'])) {
    $filter_ncc = $conn->real_escape_string($_GET['filter_ncc']);
    $cond_sql .= " AND nha_cung_cap = '$filter_ncc'";
}

// Lấy danh sách loại hàng duy nhất
$loaiOptions = [];
$loai_result = $conn->query("SELECT DISTINCT loai FROM hanghoa WHERE loai IS NOT NULL AND loai != ''");
while ($r = $loai_result->fetch_assoc()) {
    $loaiOptions[] = $r['loai'];
}

// Lấy danh sách nhà cung cấp duy nhất
$nccOptions = [];
$ncc_result = $conn->query("SELECT DISTINCT nha_cung_cap FROM hanghoa WHERE nha_cung_cap IS NOT NULL AND nha_cung_cap != ''");
while ($r = $ncc_result->fetch_assoc()) {
    $nccOptions[] = $r['nha_cung_cap'];
}

$count_result = $conn->query("SELECT COUNT(*) AS total FROM hanghoa $cond_sql");
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$query = "SELECT * FROM hanghoa $cond_sql ORDER BY ngay_thao_tac DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Tính tổng số lượng và tồn kho hiện tại
$total_sl = 0;
$total_giatri = 0;
$tong_gia_tri_nhap = 0;

// Tính tổng giá trị nhập (từ bảng phiếu nhập)
$gia_nhap_query = $conn->query("
    SELECT SUM(px.so_luong * h.gia_nhap) AS tong_nhap
    FROM phieu_nhap_xuat px
    JOIN hanghoa h ON px.hanghoa_id = h.id
    WHERE px.loai = 'nhap'
");
$tong_gia_tri_nhap = $gia_nhap_query->fetch_assoc()['tong_nhap'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Hàng hóa và Phiếu nhập/xuất</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="container-fluid p-4">
            <h2 class="mb-4">Danh sách Hàng hoá</h2>

            <!-- Bộ lọc -->
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" value="<?= $_GET['search'] ?? '' ?>" class="form-control"
                        placeholder="Tìm theo tên...">
                </div>
                <div class="col-md-3">
                    <select name="filter_loai" class="form-select">
                        <option value="">-- Lọc theo loại --</option>
                        <?php foreach ($loaiOptions as $loai): ?>
                        <option value="<?= $loai ?>" <?= ($_GET['filter_loai'] ?? '') == $loai ? 'selected' : '' ?>>
                            <?= htmlspecialchars($loai) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="filter_kieu" class="form-select">
                        <option value="">-- Lọc theo kiểu --</option>
                        <option value="Nguyên liệu"
                            <?= ($_GET['filter_kieu'] ?? '') == 'Nguyên liệu' ? 'selected' : '' ?>>Nguyên liệu</option>
                        <option value="Vật liệu" <?= ($_GET['filter_kieu'] ?? '') == 'Vật liệu' ? 'selected' : '' ?>>Vật
                            liệu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="filter_ncc" class="form-select">
                        <option value="">-- Lọc theo Nhà cung cấp --</option>
                        <?php foreach ($nccOptions as $ncc): ?>
                        <option value="<?= $ncc ?>" <?= ($_GET['filter_ncc'] ?? '') == $ncc ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ncc) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-success me-2">Lọc</button>
                    <a href="../backend/export_excel.php" class="btn btn-outline-secondary">📤 Xuất Excel</a>
                </div>
            </form>

            <!-- Nút thêm -->
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Thêm
                hàng hoá</button>
            <button type="button" class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#addPhieuModal">+ Tạo
                Phiếu nhập/xuất</button>

            <!-- Bảng -->
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Tên</th>
                        <th>Loại</th>
                        <th>Số lượng</th>
                        <th>Đơn vị</th>
                        <th>Kiểu</th>
                        <th>Giá nhập</th>
                        <th>Nhà cung cấp</th>
                        <th>Giá trị tồn kho</th>
                        <th>Ngày thao tác</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $total_sl += $row['so_luong'];
                        $giatri = $row['so_luong'] * $row['gia_nhap'];
                        $total_giatri += $giatri;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ten']) ?></td>
                        <td><?= htmlspecialchars($row['loai']) ?></td>
                        <td class="<?= $row['so_luong'] < 10 ? 'text-danger fw-bold' : '' ?>">
                            <?= $row['so_luong'] ?>
                            <?= $row['so_luong'] < 10 ? '<span class="badge bg-danger ms-2">Thấp</span>' : '' ?>
                        </td>
                        <td><?= $row['don_vi'] ?></td>
                        <td><?= $row['kieu'] ?></td>
                        <td><?= number_format($row['gia_nhap']) ?></td>
                        <td><?= htmlspecialchars($row['nha_cung_cap']) ?></td>
                        <td><?= number_format($giatri) ?></td>
                        <td><?= $row['ngay_thao_tac'] ?></td>
                        <td>
                            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                            <a href="../backend/hanghoa_xuly.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Xoá hàng hoá này?')">Xoá</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="table-warning fw-bold">
                        <td colspan="2">TỔNG</td>
                        <td><?= $total_sl ?></td>
                        <td colspan="4"></td>
                        <td><?= number_format($total_giatri) ?></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr class="table-info fw-bold">
                        <td colspan="7">TỔNG GIÁ TRỊ NHẬP HÀNG</td>
                        <td colspan="3"><?= number_format($tong_gia_tri_nhap) ?></td>
                    </tr>
                </tfoot>
            </table>

            <!-- Phân trang -->
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link"
                            href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Modal Sửa -->
    <?php if (isset($_GET['edit'])):
        $id = (int) $_GET['edit'];
        $edit = $conn->query("SELECT * FROM hanghoa WHERE id=$id")->fetch_assoc();
    ?>
    <script>
    window.addEventListener('load', () => new bootstrap.Modal(document.getElementById('editModal')).show());
    </script>
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="../backend/hanghoa_xuly.php" method="POST" class="modal-content">
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa hàng hoá</h5>
                </div>
                <div class="modal-body">
                    <input name="ten" class="form-control mb-2" value="<?= $edit['ten'] ?>" placeholder="Tên hàng hoá"
                        required>
                    <input name="loai" class="form-control mb-2" value="<?= $edit['loai'] ?>" placeholder="Loại">
                    <input name="so_luong" type="number" step="0.01" class="form-control mb-2"
                        value="<?= $edit['so_luong'] ?>" placeholder="Số lượng">
                    <input name="don_vi" class="form-control mb-2" value="<?= $edit['don_vi'] ?>" placeholder="Đơn vị">
                    <select name="kieu" class="form-select mb-2">
                        <option value="Nguyên liệu" <?= ($edit['kieu'] == 'Nguyên liệu') ? 'selected' : '' ?>>Nguyên
                            liệu</option>
                        <option value="Vật liệu" <?= ($edit['kieu'] == 'Vật liệu') ? 'selected' : '' ?>>Vật liệu
                        </option>
                    </select>
                    <input name="gia_nhap" type="number" step="1000" class="form-control mb-2"
                        value="<?= $edit['gia_nhap'] ?>" placeholder="Giá nhập">
                    <input name="nha_cung_cap" class="form-control mb-2" value="<?= $edit['nha_cung_cap'] ?>"
                        placeholder="Nhà cung cấp">
                    <textarea name="ghi_chu" class="form-control"
                        placeholder="Ghi chú"><?= $edit['ghi_chu'] ?></textarea>
                </div>
                <div class="modal-footer">
                    <a href="hanghoa.php" class="btn btn-secondary">Huỷ</a>
                    <button type="submit" name="update" class="btn btn-success">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>


    <!-- Modal Thêm -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="hanghoa.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm hàng hoá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input required name="ten" class="form-control mb-2" placeholder="Tên hàng hoá">
                    <input name="loai" class="form-control mb-2" placeholder="Loại">
                    <input type="number" step="0.01" name="so_luong" class="form-control mb-2" placeholder="Số lượng">
                    <input name="don_vi" class="form-control mb-2" placeholder="Đơn vị">
                    <select name="kieu" class="form-select mb-2">
                        <option value="Nguyên liệu">Nguyên liệu</option>
                        <option value="Vật liệu">Vật liệu</option>
                    </select>
                    <input type="number" name="gia_nhap" step="1000" class="form-control mb-2" placeholder="Giá nhập">
                    <input name="nha_cung_cap" class="form-control mb-2" placeholder="Nhà cung cấp">
                    <textarea name="ghi_chu" class="form-control" placeholder="Ghi chú"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="add" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function editProduct(id, ten, loai, so_luong, don_vi, kieu, gia_nhap, nha_cung_cap, ghi_chu) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-ten').value = ten;
        document.getElementById('edit-loai').value = loai;
        document.getElementById('edit-so_luong').value = so_luong;
        document.getElementById('edit-don_vi').value = don_vi;
        document.getElementById('edit-kieu').value = kieu;
        document.getElementById('edit-gia_nhap').value = gia_nhap;
        document.getElementById('edit-nha_cung_cap').value = nha_cung_cap;
        document.getElementById('edit-ghi_chu').value = ghi_chu;

        var myModal = new bootstrap.Modal(document.getElementById('editModal'), {});
        myModal.show();
    }
    </script>

    <!-- Modal Phiếu nhập/xuất -->
    <div class="modal fade" id="addPhieuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="hanghoa.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tạo Phiếu nhập/xuất</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select name="hanghoa_id" class="form-select mb-2" required>
                        <?php
                        $res = $conn->query("SELECT id, ten FROM hanghoa");
                        while ($hh = $res->fetch_assoc()):
                        ?>
                        <option value="<?= $hh['id'] ?>"><?= $hh['ten'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select name="loai" class="form-select mb-2" required>
                        <option value="nhap">Nhập</option>
                        <option value="xuat">Xuất</option>
                    </select>
                    <input type="number" name="so_luong" class="form-control mb-2" min="0.01" step="0.01" required>
                    <input type="text" name="ghi_chu" class="form-control mb-2" placeholder="Ghi chú">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" name="save_nhap_xuat" class="btn btn-primary">Lưu Phiếu</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>