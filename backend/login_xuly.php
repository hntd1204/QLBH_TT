<?php
session_start();
include 'database.php';

// Kiểm tra nếu không phải POST thì quay về trang login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../frontend/login.php");
    exit;
}

// Lấy dữ liệu từ form
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Kiểm tra dữ liệu rỗng
if ($username === '' || $password === '') {
    $_SESSION['error'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
    header("Location: ../frontend/login.php");
    exit;
}

// Truy vấn người dùng
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra tồn tại và xác thực mật khẩu
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // So sánh mật khẩu nhập với hash trong DB
    if (password_verify($password, $user['password'])) {
        // Đăng nhập thành công
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        header("Location: ../frontend/index.php");
        exit;
    }
}

// Đăng nhập thất bại
$_SESSION['error'] = "Sai tên đăng nhập hoặc mật khẩu!";
header("Location: ../frontend/login.php");
exit;