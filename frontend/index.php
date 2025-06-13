<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include '../backend/database.php';

// Lấy tổng doanh thu cho người dùng
$total_revenue_query = "SELECT SUM(total_price) AS total_revenue FROM donhang WHERE user_id = ?";
$stmt_revenue = $conn->prepare($total_revenue_query);
$stmt_revenue->bind_param("i", $_SESSION['user_id']);
$stmt_revenue->execute();
$revenue_result = $stmt_revenue->get_result();
$revenue_data = $revenue_result->fetch_assoc();
$total_revenue = $revenue_data['total_revenue'] ?? 0; // Nếu không có doanh thu thì mặc định là 0

// Truy vấn doanh thu tuần này
$this_week_query = "SELECT SUM(total_price) AS weekly_revenue 
                    FROM donhang 
                    WHERE user_id = ? 
                    AND WEEK(ngay_tao) = WEEK(CURDATE())";
$stmt_weekly = $conn->prepare($this_week_query);
$stmt_weekly->bind_param("i", $_SESSION['user_id']);
$stmt_weekly->execute();
$weekly_result = $stmt_weekly->get_result();
$weekly_data = $weekly_result->fetch_assoc();
$weekly_revenue = $weekly_data['weekly_revenue'] ?? 0;

// Thực hiện các truy vấn khác trong dashboard như tổng số hàng hóa, tồn kho...
$res = $conn->query("SELECT 
    (SELECT COUNT(*) FROM hanghoa) AS total_hanghoa,
    (SELECT SUM(so_luong) FROM hanghoa) AS tong_tonkho,
    (SELECT SUM(so_luong * gia_nhap) FROM hanghoa) AS tong_giatri,
    (SELECT COUNT(*) FROM phieu_nhap_xuat WHERE loai = 'nhap' AND ngay_thao_tac >= CURDATE() - INTERVAL 7 DAY) AS nhap_7ngay
");
$row = $res->fetch_assoc();
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

                <div class="row mt-5">
                    <!-- Doanh thu Tổng -->
                    <div class="col-md-6">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Tổng Doanh Thu</h5>
                                <p class="card-text fs-4">
                                    <?= number_format($total_revenue, 0, ',', '.') ?> VND
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Doanh thu tuần này -->
                    <div class="col-md-6">
                        <div class="card text-white bg-secondary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Doanh Thu Tuần này</h5>
                                <p class="card-text fs-4"><?= number_format($weekly_revenue, 0, ',', '.') ?> VND</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>