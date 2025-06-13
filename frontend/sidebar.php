<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<?php
session_start(); // nếu chưa có
$current = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex flex-column flex-shrink-0 p-3 bg-light"
    style="width: 250px; height: 100vh; border-right: 1px solid #ccc;">
    <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
        <span class="fs-4 fw-bold">TyTea - Matcha and Tea</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?= $current == 'index.php' ? 'active' : 'text-dark' ?>">
                Tổng quát
            </a>
        </li>
        <li>
            <a href="hanghoa.php" class="nav-link <?= $current == 'hanghoa.php' ? 'active' : 'text-dark' ?>">
                Hàng hoá
            </a>
        </li>
        <li>
            <a href="thucdon.php" class="nav-link <?= $current == 'thucdon.php' ? 'active' : 'text-dark' ?>">
                Thực đơn
            </a>
        </li>
        <li>
            <a href="donhang.php" class="nav-link <?= $current == 'donhang.php' ? 'active' : 'text-dark' ?>">
                Đơn hàng và Giỏ hàng
            </a>
        </li>
        <li>
            <a href="doanhthu.php" class="nav-link <?= $current == 'doanhthu.php' ? 'active' : 'text-dark' ?>">
                Doanh thu
            </a>
        </li>
        <li>
            <a href="congthuc_nuoc.php"
                class="nav-link <?= $current == 'congthuc_nuoc.php' ? 'active' : 'text-dark' ?>">
                Công thức nước
            </a>
        </li>
        <li>
            <a href="congthuc_nau.php" class="nav-link <?= $current == 'congthuc_nau.php' ? 'active' : 'text-dark' ?>">
                Công thức nấu
            </a>
        </li>

    </ul>
    <hr>

    <?php if (isset($_SESSION['username'])): ?>
    <div class="mb-2 text-muted small">
        👤 <?= htmlspecialchars($_SESSION['username']) ?>
        <?php if (!empty($_SESSION['role'])): ?>
        <span class="badge bg-secondary ms-1"><?= strtoupper($_SESSION['role']) ?></span>
        <?php endif; ?>
    </div>
    <a href="../backend/logout.php" class="btn btn-outline-danger btn-sm">Đăng xuất</a>
    <?php else: ?>
    <div class="text-muted small">Chưa đăng nhập</div>
    <?php endif; ?>

    <div class="text-muted mt-3 small">© 2025 TYTEA</div>
</div>