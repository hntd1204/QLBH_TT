<?php
include '../backend/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Phần xử lý thêm mới hàng hoá
if (isset($_POST['add'])) {
    $ten = $_POST['ten'];
    $loai = $_POST['loai'];
    $so_luong = $_POST['so_luong'];
    $don_vi = $_POST['don_vi'];
    $kieu = $_POST['kieu'];
    $gia_nhap = $_POST['gia_nhap'] ?? 0;
    $nha_cung_cap = $_POST['nha_cung_cap'] ?? '';
    $ghi_chu = $_POST['ghi_chu'];

    $stmt = $conn->prepare("INSERT INTO hanghoa (ten, loai, so_luong, don_vi, kieu, gia_nhap, nha_cung_cap, ghi_chu, ngay_thao_tac)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdssdss", $ten, $loai, $so_luong, $don_vi, $kieu, $gia_nhap, $nha_cung_cap, $ghi_chu);
    $stmt->execute();
    header("Location: hanghoa.php?msg=add_success");
    exit;
}

// Phần xử lý cập nhật hàng hoá
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $ten = $_POST['ten'];
    $loai = $_POST['loai'];
    $so_luong = $_POST['so_luong'];
    $don_vi = $_POST['don_vi'];
    $kieu = $_POST['kieu'];
    $gia_nhap = $_POST['gia_nhap'] ?? 0;
    $nha_cung_cap = $_POST['nha_cung_cap'] ?? '';
    $ghi_chu = $_POST['ghi_chu'];

    $stmt = $conn->prepare("UPDATE hanghoa 
        SET ten=?, loai=?, so_luong=?, don_vi=?, kieu=?, gia_nhap=?, nha_cung_cap=?, ghi_chu=?, ngay_thao_tac=NOW()
        WHERE id=?");
    $stmt->bind_param("ssdssdssi", $ten, $loai, $so_luong, $don_vi, $kieu, $gia_nhap, $nha_cung_cap, $ghi_chu, $id);
    $stmt->execute();
    header("Location: hanghoa.php?msg=update_success");
    exit;
}

// Phần xử lý xoá hàng hoá
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM hanghoa WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: hanghoa.php?msg=delete_success");
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

//lịch sử nhập xuất
$history_query = "
    SELECT p.id AS phieu_id, h.ten AS hanghoa_ten, p.loai, p.so_luong, p.ngay_thao_tac, p.ghi_chu
    FROM phieu_nhap_xuat p
    JOIN hanghoa h ON p.hanghoa_id = h.id
    ORDER BY p.ngay_thao_tac DESC
";
$history_result = $conn->query($history_query);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                        <td><?= $row['don_vi'] ?></td>c
                        <td><?= number_format($row['gia_nhap']) ?></td>
                        <td><?= htmlspecialchars($row['nha_cung_cap']) ?></td>
                        <td><?= number_format($giatri) ?></td>
                        <td><?= $row['ngay_thao_tac'] ?></td>
                        <td>
                            <a href="hanghoa.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                            <a href="hanghoa.php?delete=<?= $row['id'] ?>" onclick="return confirm('Xoá hàng hoá này?')"
                                class="btn btn-sm btn-danger">Xoá</a>
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

            <!-- Bảng Lịch sử Nhập/Xuất -->
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Hàng hóa</th>
                        <th>Loại</th>
                        <th>Số lượng</th>
                        <th>Ngày thao tác</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($history_row = $history_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($history_row['hanghoa_ten']) ?></td>
                        <td><?= ($history_row['loai'] == 'nhap') ? 'Nhập' : 'Xuất' ?></td>
                        <td><?= $history_row['so_luong'] ?></td>
                        <td><?= $history_row['ngay_thao_tac'] ?></td>
                        <td><?= htmlspecialchars($history_row['ghi_chu']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Phiếu nhập/xuất -->
    <div class="modal fade" id="addPhieuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="../backend/xuly_nhap_xuat.php" method="POST" class="modal-content">
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
                        <option value="<?= $hh['id'] ?>"><?= htmlspecialchars($hh['ten']) ?></option>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>