<?php
include 'database.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Thêm công thức
if (isset($_POST['add'])) {
    $tieude = $_POST['tieude'];
    $noidung = $_POST['noidung'];

    $stmt = $conn->prepare("INSERT INTO congthuc_nau (tieude, noidung) VALUES (?, ?)");
    $stmt->bind_param("ss", $tieude, $noidung);
    $stmt->execute();

    header("Location: ../frontend/congthuc_nau.php");
    exit;
}

// Cập nhật công thức
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $tieude = $_POST['tieude'];
    $noidung = $_POST['noidung'];

    $stmt = $conn->prepare("UPDATE congthuc_nau SET tieude = ?, noidung = ? WHERE id = ?");
    $stmt->bind_param("ssi", $tieude, $noidung, $id);
    $stmt->execute();

    header("Location: ../frontend/congthuc_nau.php");
    exit;
}

// Xoá công thức
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM congthuc_nau WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: ../frontend/congthuc_nau.php");
    exit;
}