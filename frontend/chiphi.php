<?php
include '../backend/database.php';
session_start();

// Phân trang
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$search_sql = $keyword !== '' ? "WHERE ten_mon LIKE '%" . $conn->real_escape_string($keyword) . "%'" : '';

// Tổng số bản ghi tìm được
$totalResult = $conn->query("SELECT COUNT(*) AS total FROM chiphi $search_sql");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Lấy dữ liệu theo trang và điều kiện tìm kiếm
$data = $conn->query("SELECT * FROM chiphi $search_sql ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi phí món bán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .table th,
    .table td {
        vertical-align: middle;
    }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="container p-4">
            <h2 class="mb-4">Chi phí món bán</h2>

            <!-- Form thêm mới -->
            <form method="POST" action="../backend/chiphi_xuly.php" class="row g-3 mb-4">
                <input type="hidden" name="action" value="add">
                <div class="col-md-3">
                    <input name="ten_mon" required class="form-control" placeholder="Tên món">
                </div>
                <div class="col-md-2">
                    <input name="gia_von" type="number" step="0.01" required class="form-control" placeholder="Giá vốn">
                </div>
                <div class="col-md-2">
                    <input name="gia_ban" type="number" step="0.01" required class="form-control" placeholder="Giá bán">
                </div>
                <div class="col-md-4">
                    <input name="ghi_chu" class="form-control" placeholder="Ghi chú (nếu có)">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-success w-100">➕</button>
                </div>
            </form>

            <!-- Form tìm kiếm -->
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input name="keyword" class="form-control" placeholder="Tìm tên món..."
                        value="<?= htmlspecialchars($keyword) ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100">🔍 Tìm</button>
                </div>
                <div class="col-md-2">
                    <a href="chiphi.php" class="btn btn-secondary w-100">🧹 Xoá lọc</a>
                </div>
            </form>

            <!-- Danh sách -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tên món</th>
                            <th>Giá vốn</th>
                            <th>Giá bán</th>
                            <th>Ghi chú</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $data->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['ten_mon']) ?></td>
                            <td class="text-end"><?= number_format($row['gia_von']) ?> đ</td>
                            <td class="text-end text-primary fw-bold"><?= number_format($row['gia_ban']) ?> đ</td>
                            <td><?= htmlspecialchars($row['ghi_chu']) ?></td>
                            <td class="text-center">
                                <a href="../backend/chiphi_xuly.php?delete=<?= $row['id'] ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Xoá mục này?')">Xoá</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>