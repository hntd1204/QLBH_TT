<?php
include '../backend/database.php';
session_start();

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

// Lấy thông tin đơn hàng từ bảng donhang
$order_query = "SELECT o.id AS order_id, o.total_price, o.ngay_tao 
                FROM donhang o 
                WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order_result = $stmt->get_result();

// Kiểm tra nếu đơn hàng tồn tại
if ($order_result->num_rows == 0) {
    echo "Đơn hàng không hợp lệ hoặc không thuộc về bạn.";
    exit;
}

$order_data = $order_result->fetch_assoc();

// Lấy chi tiết các món trong đơn hàng từ bảng donhang_chitiet
$detail_query = "SELECT dc.thucdon_id, td.ten_mon, dc.size, dc.quantity, dc.price
                 FROM donhang_chitiet dc
                 JOIN thucdon td ON dc.thucdon_id = td.id
                 WHERE dc.order_id = ?";
$stmt = $conn->prepare($detail_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$detail_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Đơn Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Bao gồm file sidebar.php -->
        <?php include 'sidebar.php'; ?>

        <!-- Nội dung chính -->
        <div class="container mt-4">
            <h2>Chi Tiết Đơn Hàng</h2>

            <!-- Thông tin đơn hàng -->
            <p><strong>Mã Đơn Hàng:</strong> <?= htmlspecialchars($order_data['order_id']) ?></p>
            <p><strong>Tổng Tiền:</strong> <?= number_format($order_data['total_price']) ?> VND</p>
            <p><strong>Ngày Giờ:</strong>
                <?php
                // Định dạng ngày giờ từ cơ sở dữ liệu
                if (!empty($order_data['ngay_tao']) && $order_data['ngay_tao'] != '0000-00-00 00:00:00') {
                    echo date('d-m-Y H:i:s', strtotime($order_data['ngay_tao']));
                } else {
                    echo "Ngày giờ không hợp lệ";
                }
                ?>
            </p>

            <h4>Chi Tiết Các Món Trong Đơn Hàng</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tên Món</th>
                        <th>Kích Thước</th>
                        <th>Số Lượng</th>
                        <th>Giá</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $detail_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ten_mon']) ?></td>
                        <td><?= htmlspecialchars($row['size']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= number_format($row['price'] * $row['quantity']) ?> VND</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <a href="order_history.php" class="btn btn-primary">Quay lại Lịch Sử Đơn Hàng</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>