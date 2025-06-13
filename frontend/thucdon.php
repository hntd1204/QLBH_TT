<?php include '../backend/database.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Lọc và tìm kiếm
$cond_sql = " WHERE 1=1";
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $cond_sql .= " AND ten_mon LIKE '%$search%'";
}
if (!empty($_GET['filter_dm'])) {
    $filter_dm = $conn->real_escape_string($_GET['filter_dm']);
    $cond_sql .= " AND danh_muc = '$filter_dm'";
}

$count = $conn->query("SELECT COUNT(*) as total FROM thucdon $cond_sql")->fetch_assoc()['total'];
$total_pages = ceil($count / $limit);

$query = "SELECT * FROM thucdon $cond_sql ORDER BY ngay_tao DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Lấy danh sách danh mục duy nhất
$danhmuc_res = $conn->query("SELECT DISTINCT danh_muc FROM thucdon WHERE danh_muc IS NOT NULL AND danh_muc != ''");
$danhmucs = [];
while ($r = $danhmuc_res->fetch_assoc()) $danhmucs[] = $r['danh_muc'];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Thực đơn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="container-fluid p-4">
            <h2 class="mb-4">Danh sách Thực đơn</h2>

            <!-- Bộ lọc -->
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="<?= $_GET['search'] ?? '' ?>" class="form-control"
                        placeholder="Tìm món...">
                </div>
                <div class="col-md-4">
                    <select name="filter_dm" class="form-select">
                        <option value="">-- Lọc theo danh mục --</option>
                        <?php foreach ($danhmucs as $dm): ?>
                        <option value="<?= $dm ?>" <?= ($_GET['filter_dm'] ?? '') == $dm ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dm) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex">
                    <button class="btn btn-success me-2">Lọc</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+
                        Thêm món</button>
                </div>
            </form>

            <!-- Bảng -->
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Tên món</th>
                        <th>Danh mục</th>
                        <th>Giá bán</th>
                        <th>Ghi chú</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ten_mon']) ?></td>
                        <td><?= htmlspecialchars($row['danh_muc']) ?></td>
                        <td><?= number_format($row['gia_ban']) ?></td>
                        <td><?= htmlspecialchars($row['ghi_chu']) ?></td>
                        <td>
                            <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                            <a href="../backend/thucdon_xuly.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Xoá món này?')">Xoá</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
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

    <!-- Modal Thêm -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="../backend/thucdon_xuly.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm món</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input name="ten_mon" class="form-control mb-2" placeholder="Tên món" required>
                    <input name="danh_muc" class="form-control mb-2" placeholder="Danh mục">
                    <input name="gia_ban" type="number" step="1000" class="form-control mb-2" placeholder="Giá bán">
                    <textarea name="ghi_chu" class="form-control" placeholder="Ghi chú"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                    <button type="submit" name="add" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sửa -->
    <?php if (isset($_GET['edit'])):
        $id = (int) $_GET['edit'];
        $edit = $conn->query("SELECT * FROM thucdon WHERE id=$id")->fetch_assoc();
    ?>
    <script>
    window.addEventListener('load', () => new bootstrap.Modal(document.getElementById('editModal')).show());
    </script>
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="../backend/thucdon_xuly.php" method="POST" class="modal-content">
                <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa món</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input name="ten_mon" class="form-control mb-2" value="<?= $edit['ten_mon'] ?>">
                    <input name="danh_muc" class="form-control mb-2" value="<?= $edit['danh_muc'] ?>">
                    <input name="gia_ban" type="number" step="1000" class="form-control mb-2"
                        value="<?= $edit['gia_ban'] ?>">
                    <textarea name="ghi_chu" class="form-control"><?= $edit['ghi_chu'] ?></textarea>
                </div>
                <div class="modal-footer">
                    <a href="thucdon.php" class="btn btn-secondary">Huỷ</a>
                    <button type="submit" name="update" class="btn btn-success">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>