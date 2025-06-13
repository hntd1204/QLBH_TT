<?php
include '../backend/database.php';
session_start();

// Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy thông tin đơn hàng từ GET
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
$total_price = isset($_GET['total']) ? $_GET['total'] : 0;

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn Thanh Toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Bao gồm file sidebar.php -->
        <?php include 'sidebar.php'; ?>

        <!-- Nội dung chính -->
        <div class="container mt-4">
            <h2>Hóa Đơn Thanh Toán</h2>

            <p><strong>Mã Đơn Hàng:</strong> <?= htmlspecialchars($order_data['order_id']) ?></p>

            <!-- Định dạng lại ngày giờ -->
            <p><strong>Ngày Giờ:</strong>
                <?php
                // Định dạng lại ngày giờ từ cơ sở dữ liệu (ngay_tao)
                if (!empty($order_data['ngay_tao']) && $order_data['ngay_tao'] != '0000-00-00 00:00:00') {
                    echo date('d-m-Y H:i:s', strtotime($order_data['ngay_tao']));
                } else {
                    echo "Ngày giờ không hợp lệ";
                }
                ?>
            </p>
            <p><strong>Tổng Tiền:</strong> <?= number_format($order_data['total_price']) ?> VND</p>

            <h4>Chi Tiết Đơn Hàng</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tên món</th>
                        <th>Kích thước</th>
                        <th>Số lượng</th>
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

            <a href="order_history.php" class="btn btn-primary">Xem Lịch Sử Đơn Hàng</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>