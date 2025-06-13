<?php
include 'database.php';

$hanghoa_id = $_POST['hanghoa_id'];
$loai = $_POST['loai']; // 'nhap' hoặc 'xuat'
$so_luong = (float) $_POST['so_luong'];
$ghi_chu = $conn->real_escape_string($_POST['ghi_chu'] ?? '');

// Lưu vào bảng phiếu nhập/xuất
$conn->query("
    INSERT INTO phieu_nhap_xuat (hanghoa_id, loai, so_luong, ghi_chu)
    VALUES ($hanghoa_id, '$loai', $so_luong, '$ghi_chu')
");

// Cập nhật tồn kho
$sign = $loai === 'nhap' ? '+' : '-';
$conn->query("
    UPDATE hanghoa SET so_luong = so_luong $sign $so_luong WHERE id = $hanghoa_id
");

header("Location: ../frontend/phieu_nhap_xuat.php?msg=done");
exit;