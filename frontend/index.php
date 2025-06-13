<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include '../backend/database.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Trang chính - QLBH_TYTEA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <div class="flex-grow-1">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h5">Trang tổng quan</span>

                    <!-- Thêm vào đây -->
                    <div class="ms-auto d-flex align-items-center">
                        <span class="me-3 text-muted">👤 Đang đăng nhập:
                            <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                        <a href="../backend/logout.php" class="btn btn-outline-danger btn-sm">Đăng xuất</a>
                    </div>
                </div>
            </nav>


            <!-- Nội dung chính -->
            <div class="container py-4">
                <h2 class="mb-4">Chào mừng đến với hệ thống quản lý <strong>TYTEA</strong></h2>
                <p class="text-muted">Chọn chức năng từ menu bên trái để bắt đầu làm việc.</p>

                <!-- DASHBOARD -->
                <div class="row">
                    <?php
                    $res = $conn->query("SELECT 
                        (SELECT COUNT(*) FROM hanghoa) AS total_hanghoa,
                        (SELECT SUM(so_luong) FROM hanghoa) AS tong_tonkho,
                        (SELECT SUM(so_luong * gia_nhap) FROM hanghoa) AS tong_giatri,
                        (SELECT COUNT(*) FROM phieu_nhap_xuat WHERE loai = 'nhap' AND ngay_thao_tac >= CURDATE() - INTERVAL 7 DAY) AS nhap_7ngay
                    ");
                    $row = $res->fetch_assoc();
                    ?>

                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Số hàng hoá</h5>
                                <p class="card-text fs-4"><?= number_format($row['total_hanghoa']) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Tổng tồn kho</h5>
                                <p class="card-text fs-4"><?= number_format($row['tong_tonkho']) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Giá trị tồn kho</h5>
                                <p class="card-text fs-4"><?= number_format($row['tong_giatri'], 0, ',', '.') ?> ₫</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-white bg-danger mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Lượt nhập 7 ngày</h5>
                                <p class="card-text fs-4"><?= number_format($row['nhap_7ngay']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END DASHBOARD -->

            </div>
        </div>
    </div>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>