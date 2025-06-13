<?php
include '../backend/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Kiểm tra nếu form đã được gửi để thêm món vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id']; // ID món
    $size = $_POST['size']; // Kích thước
    $quantity = $_POST['quantity']; // Số lượng

    // Thêm món vào giỏ hàng
    $cart_query = "INSERT INTO cart (user_id, thucdon_id, size, quantity) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("iisi", $_SESSION['user_id'], $item_id, $size, $quantity);
    $stmt->execute();
}

// Kiểm tra nếu form thanh toán đã được gửi
if (isset($_POST['pay'])) {
    // Lấy giỏ hàng của người dùng
    $cart_query = "SELECT c.id, c.thucdon_id, c.size, c.quantity, td.gia_ban_500ml, td.gia_ban_700ml 
                   FROM cart c
                   JOIN thucdon td ON c.thucdon_id = td.id
                   WHERE c.user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $cart_result = $stmt->get_result();

    $total_price = 0;
    $order_items = [];

    // Tính tổng tiền và lưu thông tin các món vào mảng
    while ($row = $cart_result->fetch_assoc()) {
        $price = ($row['size'] == '500ml') ? $row['gia_ban_500ml'] : $row['gia_ban_700ml'];
        $total_price += $price * $row['quantity'];
        $order_items[] = [
            'thucdon_id' => $row['thucdon_id'],
            'size' => $row['size'],
            'quantity' => $row['quantity'],
            'price' => $price
        ];
    }

    // Lưu đơn hàng vào bảng orders
    $order_query = "INSERT INTO donhang (user_id, total_price) VALUES (?, ?)";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("id", $_SESSION['user_id'], $total_price);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Lưu chi tiết đơn hàng vào bảng donhang_chitiet
    foreach ($order_items as $item) {
        $detail_query = "INSERT INTO donhang_chitiet (order_id, thucdon_id, size, quantity, price) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($detail_query);
        $stmt->bind_param("iisid", $order_id, $item['thucdon_id'], $item['size'], $item['quantity'], $item['price']);
        $stmt->execute();
    }

    // Xóa các món trong giỏ hàng sau khi thanh toán
    $delete_cart_query = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($delete_cart_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();

    // Lưu ngày giờ thanh toán
    $order_time = date("Y-m-d H:i:s");

    // Điều hướng đến trang thông báo hóa đơn
    header("Location: checkout.php?order_id=$order_id&total=$total_price&time=$order_time");
    exit;
}

// Lấy danh sách món từ thực đơn
$query = "SELECT * FROM thucdon";
$result = $conn->query($query);

// Lấy các món trong giỏ hàng của người dùng
$cart_query = "SELECT c.id, td.ten_mon, c.size, c.quantity, td.gia_ban_500ml, td.gia_ban_700ml 
               FROM cart c
               JOIN thucdon td ON c.thucdon_id = td.id
               WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$cart_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng và Tạo Đơn Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .form-select,
    .form-control {
        margin-bottom: 10px;
    }

    .menu-item {
        display: none;
    }

    .cart-item {
        margin-top: 20px;
    }

    .container {
        margin-left: 250px;
        margin-right: 30px;
    }

    .sidebar {
        width: 250px;
        position: fixed;
        top: 0;
        left: 0;
        height: 100%;
        background-color: #f8f9fa;
        padding-top: 20px;
    }

    .sidebar a {
        display: block;
        color: #333;
        padding: 8px 16px;
        text-decoration: none;
        margin: 5px 0;
    }

    .sidebar a:hover {
        background-color: #ddd;
    }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Bao gồm file sidebar.php -->
        <?php include 'sidebar.php'; ?>

        <!-- Nội dung chính -->
        <div class="container mt-4">
            <h2>Tạo Đơn Hàng & Giỏ Hàng</h2>

            <!-- Tìm kiếm món -->
            <form method="GET" class="mb-3">
                <input type="text" name="search" value="<?= $_GET['search'] ?? '' ?>" class="form-control"
                    placeholder="Tìm món...">
                <button type="submit" class="btn btn-primary mt-2">Tìm kiếm</button>
            </form>

            <!-- Chọn món và thêm vào giỏ hàng -->
            <form method="POST" class="mb-4">
                <h4>Chọn món</h4>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <!-- Dropdown cho chọn món -->
                        <label for="item">Chọn món:</label>
                        <select name="item_id" class="form-select mb-3" id="item" onchange="showOptions()">
                            <option value="">Chọn món...</option>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['ten_mon']) ?></option>
                            <?php endwhile; ?>
                        </select>

                        <!-- Dropdown cho kích thước -->
                        <div id="size" class="menu-item">
                            <label for="size">Chọn kích thước:</label>
                            <select name="size" class="form-select mb-3">
                                <option value="500ml">500ml</option>
                                <option value="700ml">700ml</option>
                            </select>
                        </div>

                        <!-- Số lượng món -->
                        <div id="quantity" class="menu-item">
                            <label for="quantity">Số lượng:</label>
                            <input type="number" name="quantity" class="form-control mb-3" min="1" value="1">
                        </div>

                        <!-- Nút thêm vào giỏ hàng -->
                        <button type="submit" name="add_to_cart" class="btn btn-success mt-3">Thêm vào Giỏ Hàng</button>
                    </div>
                </div>
            </form>

            <hr>

            <!-- Giỏ hàng -->
            <h4>Giỏ Hàng</h4>
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
                    <?php
                    $total = 0;
                    while ($row = $cart_result->fetch_assoc()):
                        $price = ($row['size'] == '500ml') ? $row['gia_ban_500ml'] : $row['gia_ban_700ml'];
                        $total += $price * $row['quantity'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ten_mon']) ?></td>
                        <td><?= htmlspecialchars($row['size']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= number_format($price * $row['quantity']) ?> VND</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <h4>Tổng: <?= number_format($total) ?> VND</h4>

            <!-- Nút thanh toán -->
            <form method="POST">
                <button type="submit" name="pay" class="btn btn-success">Thanh Toán</button>
            </form>

            <!-- Nút Xem Lịch Sử Đơn Hàng -->
            <a href="order_history.php" class="btn btn-secondary mt-3">Xem Lịch Sử Đơn Hàng</a>
        </div>
    </div>

    <script>
    function showOptions() {
        var item = document.getElementById('item').value;
        var size = document.getElementById('size');
        var quantity = document.getElementById('quantity');

        // Hiển thị dropdown kích thước và số lượng khi chọn món
        if (item != "") {
            size.style.display = 'block';
            quantity.style.display = 'block';
        } else {
            size.style.display = 'none';
            quantity.style.display = 'none';
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>