<?php
include '../backend/database.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Xử lý tìm kiếm và phân trang
$search = $_GET['search'] ?? '';
$page = max((int)($_GET['page'] ?? 1), 1);
$limit = 5;
$offset = ($page - 1) * $limit;

$search_sql = $conn->real_escape_string($search);
$where = $search ? "WHERE tieude LIKE '%$search%'" : '';

// Tổng số công thức
$count_res = $conn->query("SELECT COUNT(*) AS total FROM congthuc_nau $where");
$total_rows = $count_res->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_rows / $limit);

// Lấy danh sách công thức theo trang
$ds_congthuc = $conn->query("
    SELECT * FROM congthuc_nau 
    $where 
    ORDER BY id DESC 
    LIMIT $limit OFFSET $offset
");

// Lấy dữ liệu chỉnh sửa nếu có
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit = (int) $_GET['edit'];
    $edit_data = $conn->query("SELECT * FROM congthuc_nau WHERE id = $id_edit")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Công thức nấu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="container p-4">
            <h2 class="mb-4">Công thức nấu</h2>

            <!-- Tìm kiếm -->
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-4">
                    <input name="search" value="<?= htmlspecialchars($search) ?>" class="form-control"
                        placeholder="Tìm theo tiêu đề...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary" type="submit">🔍 Tìm kiếm</button>
                </div>
            </form>

            <!-- Nút thêm -->
            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Thêm
                công thức</button>

            <!-- Danh sách công thức (chỉ hiển thị tiêu đề) -->
            <?php if ($ds_congthuc->num_rows > 0): ?>
            <?php while ($ct = $ds_congthuc->fetch_assoc()): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <a href="#content-<?= $ct['id'] ?>" class="text-decoration-none" data-bs-toggle="collapse">
                        <strong><?= htmlspecialchars($ct['tieude']) ?></strong>
                    </a>
                    <div>
                        <a href="?edit=<?= $ct['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                        <a href="../backend/congthuc_nau_xuly.php?delete=<?= $ct['id'] ?>" class="btn btn-sm btn-danger"
                            onclick="return confirm('Xoá công thức này?')">Xoá</a>
                    </div>
                </div>
                <!-- Nội dung sẽ hiển thị khi nhấn vào tiêu đề -->
                <div id="content-<?= $ct['id'] ?>" class="collapse">
                    <div class="card-body">
                        <p><?= nl2br(htmlspecialchars($ct['noidung'])) ?></p>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <p class="text-muted">Không tìm thấy công thức nào.</p>
            <?php endif; ?>

            <!-- Phân trang -->
            <?php if ($total_pages > 1): ?>
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal thêm công thức -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="../backend/congthuc_nau_xuly.php" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm công thức</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input name="tieude" class="form-control mb-2" placeholder="Tiêu đề" required>
                    <textarea name="noidung" rows="5" class="form-control" placeholder="Nội dung công thức"
                        required></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                    <button class="btn btn-primary" name="add" type="submit">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal sửa công thức -->
    <?php if ($edit_data): ?>
    <script>
    window.addEventListener('load', () => {
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
    </script>
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="../backend/congthuc_nau_xuly.php" class="modal-content">
                <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa công thức</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input name="tieude" class="form-control mb-2" value="<?= htmlspecialchars($edit_data['tieude']) ?>"
                        required>
                    <textarea name="noidung" rows="5" class="form-control"
                        required><?= htmlspecialchars($edit_data['noidung']) ?></textarea>
                </div>
                <div class="modal-footer">
                    <a href="congthuc_nau.php" class="btn btn-secondary">Huỷ</a>
                    <button class="btn btn-success" name="update" type="submit">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>