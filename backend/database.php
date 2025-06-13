<?php
$host = "localhost"; // hoặc 127.0.0.1
$user = "root"; // tên user MySQL
$password = ""; // mật khẩu nếu có
$database = "qlbh_tytea";

// Kết nối
$conn = new mysqli($host, $user, $password, $database);

// Kiểm tra lỗi
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}