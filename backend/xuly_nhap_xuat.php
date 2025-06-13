<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $hanghoa_id = (int) $_POST['hanghoa_id'];  // ID hàng hóa
    $loai = $_POST['loai'];  // 'nhap' hoặc 'xuat'
    $so_luong = (float) $_POST['so_luong'];  // Số lượng
    $ghi_chu = $conn->real_escape_string($_POST['ghi_chu'] ?? '');  // Ghi chú

    // Kiểm tra dữ liệu hợp lệ
    if ($so_luong <= 0 || $hanghoa_id <= 0) {
        header("Location: ../frontend/phieu_nhap_xuat.php?msg=error&error=Invalid data");
        exit;
    }

    // Lưu vào bảng phiếu nhập/xuất
    $stmt = $conn->prepare("INSERT INTO phieu_nhap_xuat (hanghoa_id, loai, so_luong, ghi_chu) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $hanghoa_id, $loai, $so_luong, $ghi_chu);

    // Kiểm tra lỗi khi thực thi câu lệnh
    if (!$stmt->execute()) {
        header("Location: ../frontend/phieu_nhap_xuat.php?msg=error&error=" . urlencode($stmt->error));
        exit;
    }

    // Cập nhật tồn kho
    $sign = $loai === 'nhap' ? '+' : '-';  // Thêm hoặc trừ tồn kho
    $stmt = $conn->prepare("UPDATE hanghoa SET so_luong = so_luong $sign ? WHERE id = ?");
    $stmt->bind_param("di", $so_luong, $hanghoa_id);

    // Kiểm tra lỗi khi cập nhật tồn kho
    if (!$stmt->execute()) {
        header("Location: ../frontend/phieu_nhap_xuat.php?msg=error&error=" . urlencode($stmt->error));
        exit;
    }

    // Nếu thành công, chuyển hướng về trang phiếu nhập/xuất
    header("Location: ../frontend/hanghoa.php?msg=done");
    exit;
}