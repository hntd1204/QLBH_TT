<?php
include 'database.php';

// Thêm mới
if (isset($_POST['add'])) {
    // Lấy dữ liệu từ form
    $ten = $_POST['ten'];
    $loai = $_POST['loai'];
    $so_luong = $_POST['so_luong'];
    $don_vi = $_POST['don_vi'];
    $kieu = $_POST['kieu'];
    $gia_nhap = $_POST['gia_nhap'] ?? 0;
    $nha_cung_cap = $_POST['nha_cung_cap'] ?? '';
    $ghi_chu = $_POST['ghi_chu'];

    // Kiểm tra và chuẩn bị câu lệnh SQL
    $stmt = $conn->prepare("INSERT INTO hanghoa (ten, loai, so_luong, don_vi, kieu, gia_nhap, nha_cung_cap, ghi_chu, ngay_thao_tac)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    // Kiểm tra xem các biến có dữ liệu hợp lệ không
    if (empty($ten) || empty($loai) || empty($so_luong)) {
        echo "Vui lòng điền đầy đủ thông tin!";
        exit;
    }

    // Gán các tham số vào câu lệnh SQL
    $stmt->bind_param("ssdssdss", $ten, $loai, $so_luong, $don_vi, $kieu, $gia_nhap, $nha_cung_cap, $ghi_chu);

    // Thực thi câu lệnh SQL
    if ($stmt->execute()) {
        header("Location: ../frontend/hanghoa.php?msg=success");
    } else {
        echo "Lỗi khi lưu dữ liệu!";
    }
    exit;
}

// Sửa
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $ten = $_POST['ten'];
    $loai = $_POST['loai'];
    $so_luong = $_POST['so_luong'];
    $don_vi = $_POST['don_vi'];
    $kieu = $_POST['kieu'];
    $gia_nhap = $_POST['gia_nhap'] ?? 0;
    $nha_cung_cap = $_POST['nha_cung_cap'] ?? '';
    $ghi_chu = $_POST['ghi_chu'];

    $stmt = $conn->prepare("UPDATE hanghoa 
        SET ten=?, loai=?, so_luong=?, don_vi=?, kieu=?, gia_nhap=?, nha_cung_cap=?, ghi_chu=?, ngay_thao_tac=NOW()
        WHERE id=?");
    $stmt->bind_param("ssdssdssi", $ten, $loai, $so_luong, $don_vi, $kieu, $gia_nhap, $nha_cung_cap, $ghi_chu, $id);
    $stmt->execute();
    header("Location: ../frontend/hanghoa.php");
    exit;
}

// Xoá
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM hanghoa WHERE id=$id");
    header("Location: ../frontend/hanghoa.php");
    exit;
}