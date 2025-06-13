<?php
include '../backend/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Lấy giá trị lọc ngày từ form nếu có
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Lọc đơn hàng theo ngày nếu có ngày bắt đầu và kết thúc
$order_query = "SELECT o.id AS order_id, o.total_price, o.ngay_tao 
                FROM donhang o 
                WHERE o.user_id = ?";

// Thêm điều kiện lọc ngày nếu người dùng nhập ngày
if ($start_date && $end_date) {
    $order_query .= " AND o.ngay_tao BETWEEN ? AND ?";
}

// Thêm điều kiện tìm kiếm nếu người dùng nhập mã đơn hàng
if ($search) {
    $order_query .= " AND o.id LIKE ?";
}

$order_query .= " ORDER BY o.ngay_tao DESC";

$stmt = $conn->prepare($order_query);

// Nếu có lọc theo ngày
if ($start_date && $end_date) {
    $stmt->bind_param("iss", $_SESSION['user_id'], $start_date, $end_date);
}
// Nếu có tìm kiếm theo mã đơn hàng
else if ($search) {
    $search = "%$search%";
    $stmt->bind_param("is", $_SESSION['user_id'], $search);
}
// Không lọc theo ngày và tìm kiếm
else {
    $stmt->bind_param("i", $_SESSION['user_id']);
}

$stmt->execute();
$order_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Đơn Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="d-flex">
        <!-- Bao gồm file sidebar.php -->
        <?php include 'sidebar.php'; ?>

        <!-- Nội dung chính -->
        <div class="container mt-4">
            <h2>Lịch Sử Đơn Hàng</h2>

            <!-- Tìm kiếm và lọc -->
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Tìm mã đơn hàng"
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                    </div>
                </div>
            </form>

            <!-- Hiển thị danh sách đơn hàng -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Tổng tiền</th>
                        <th>Ngày giờ</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $order_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['order_id'] ?></td>
                        <td><?= number_format($row['total_price']) ?> VND</td>
                        <td>
                            <?php
                                // Định dạng lại ngày giờ từ cơ sở dữ liệu (ngay_tao)
                                $ngay_tao = $row['ngay_tao'];
                                if ($ngay_tao) {
                                    echo date('d-m-Y H:i:s', strtotime($ngay_tao));
                                } else {
                                    echo "Ngày giờ không hợp lệ";
                                }
                                ?>
                        </td>
                        <td><a href="order_detail.php?order_id=<?= $row['order_id'] ?>" class="btn btn-info">Xem chi
                                tiết</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <a href="donhang.php" class="btn btn-primary">Quay lại Đơn Hàng</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>