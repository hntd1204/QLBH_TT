<?php
include 'database.php';
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=hanghoa_export.xls");

echo "Tên\tLoại\tSố lượng\tĐơn vị\tKiểu\tNgày thao tác\tGhi chú\n";

$sql = "SELECT * FROM hanghoa";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    echo "{$row['ten']}\t{$row['loai']}\t{$row['so_luong']}\t{$row['don_vi']}\t{$row['kieu']}\t{$row['ngay_thao_tac']}\t{$row['ghi_chu']}\n";
}