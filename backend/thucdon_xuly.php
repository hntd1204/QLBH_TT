<?php
include 'database.php';

if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO thucdon (ten_mon, danh_muc, gia_ban, ghi_chu) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $_POST['ten_mon'], $_POST['danh_muc'], $_POST['gia_ban'], $_POST['ghi_chu']);
    $stmt->execute();
    header("Location: ../frontend/thucdon.php");
    exit;
}

if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE thucdon SET ten_mon=?, danh_muc=?, gia_ban=?, ghi_chu=? WHERE id=?");
    $stmt->bind_param("ssdsi", $_POST['ten_mon'], $_POST['danh_muc'], $_POST['gia_ban'], $_POST['ghi_chu'], $_POST['id']);
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