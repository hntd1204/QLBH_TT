<?php
include 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ten_mon = trim($_POST['ten_mon'] ?? '');
    $gia_von = floatval($_POST['gia_von'] ?? 0);
    $gia_ban = floatval($_POST['gia_ban'] ?? 0);
    $ghi_chu = trim($_POST['ghi_chu'] ?? '');

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO chiphi (ten_mon, gia_von, gia_ban, ghi_chu) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdds", $ten_mon, $gia_von, $gia_ban, $ghi_chu);
        $stmt->execute();
    }

    header("Location: ../frontend/chiphi.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM chiphi WHERE id = $id");
    header("Location: ../frontend/chiphi.php");
    exit;
}