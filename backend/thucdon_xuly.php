<?php
include 'database.php';

if (isset($_POST['add'])) {
    // Thêm món với giá cho hai kích thước 500ml và 700ml
    $stmt = $conn->prepare("INSERT INTO thucdon (ten_mon, danh_muc, gia_ban_500ml, gia_ban_700ml, ghi_chu) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdds", $_POST['ten_mon'], $_POST['danh_muc'], $_POST['gia_ban_500ml'], $_POST['gia_ban_700ml'], $_POST['ghi_chu']);
    $stmt->execute();
    header("Location: ../frontend/thucdon.php");
    exit;
}

if (isset($_POST['update'])) {
    // Cập nhật món với giá cho hai kích thước 500ml và 700ml
    $stmt = $conn->prepare("UPDATE thucdon SET ten_mon=?, danh_muc=?, gia_ban_500ml=?, gia_ban_700ml=?, ghi_chu=? WHERE id=?");
    $stmt->bind_param("ssddsi", $_POST['ten_mon'], $_POST['danh_muc'], $_POST['gia_ban_500ml'], $_POST['gia_ban_700ml'], $_POST['ghi_chu'], $_POST['id']);
    $stmt->execute();
    header("Location: ../frontend/thucdon.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM thucdon WHERE id=$id");
    header("Location: ../frontend/thucdon.php");
    exit;
}