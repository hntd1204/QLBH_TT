<?php
include '../backend/database.php';
session_start();

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lấy giá trị lọc từ form nếu có
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Kiểm tra xem người dùng đã nhập khoảng thời gian chưa
if ($start_date && $end_date) {
    // Truy vấn tổng doanh thu cho khoảng thời gian lọc
    $total_revenue_query = "SELECT SUM(total_price) AS total_revenue 
                            FROM donhang 
                            WHERE user_id = ? 
                            AND DATE(ngay_tao) BETWEEN ? AND ?";
    $stmt_revenue = $conn->prepare($total_revenue_query);
    $stmt_revenue->bind_param("iss", $_SESSION['user_id'], $start_date, $end_date);
    $stmt_revenue->execute();
    $revenue_result = $stmt_revenue->get_result();
    $revenue_data = $revenue_result->fetch_assoc();
    $total_revenue = $revenue_data['total_revenue'] ?? 0; // Nếu không có doanh thu thì mặc định là 0
} else {
    // Nếu không có khoảng thời gian, trả về doanh thu tổng cho tất cả đơn hàng
    $total_revenue_query = "SELECT SUM(total_price) AS total_revenue 
                            FROM donhang 
                            WHERE user_id = ?";
    $stmt_revenue = $conn->prepare($total_revenue_query);
    $stmt_revenue->bind_param("i", $_SESSION['user_id']);
    $stmt_revenue->execute();
    $revenue_result = $stmt_revenue->get_result();
    $revenue_data = $revenue_result->fetch_assoc();
    $total_revenue = $revenue_data['total_revenue'] ?? 0;
}

// Truy vấn doanh thu theo ngày trong khoảng thời gian tùy chỉnh hoặc tất cả
$details_query = "SELECT DATE(ngay_tao) AS date, SUM(total_price) AS total_revenue, COUNT(id) AS order_count
                  FROM donhang 
                  WHERE user_id = ? 
                  AND (? = '' OR DATE(ngay_tao) BETWEEN ? AND ?)
                  GROUP BY DATE(ngay_tao)";
$stmt_details = $conn->prepare($details_query);
$stmt_details->bind_param("isss", $_SESSION['user_id'], $start_date, $start_date, $end_date);
$stmt_details->execute();
$details_result = $stmt_details->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Doanh Thu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Bao gồm file sidebar.php -->
        <?php include 'sidebar.php'; ?>

        <!-- Nội dung chính -->
        <div class="container mt-4">
            <h2>Doanh Thu</h2>

            <!-- Lọc theo khoảng thời gian -->
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label for="start_date">Ngày bắt đầu:</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="end_date">Ngày kết thúc:</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary mt-4">Lọc</button>
                    </div>
                </div>
            </form>

            <!-- Hiển thị tổng doanh thu -->
            <h3>Tổng Doanh Thu: <?= number_format($total_revenue) ?> VND</h3>

            <div class="mt-4">
                <?php if ($start_date && $end_date): ?>
                <h4>Doanh thu từ <?= date('d-m-Y', strtotime($start_date)) ?> đến
                    <?= date('d-m-Y', strtotime($end_date)) ?></h4>
                <?php endif; ?>
            </div>

            <!-- Hiển thị doanh thu chi tiết theo từng ngày -->
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Tổng Doanh Thu</th>
                        <th>Số Đơn Hàng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($details_result->num_rows > 0): ?>
                    <?php while ($row = $details_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d-m-Y', strtotime($row['date'])) ?></td>
                        <td><?= number_format($row['total_revenue']) ?> VND</td>
                        <td><?= $row['order_count'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="3">Không có đơn hàng trong khoảng thời gian này.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>