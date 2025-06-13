<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Công thức</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .img-fixed {
        max-width: 100%;
        height: auto;
        border: 1px solid #ccc;
        border-radius: 10px;
    }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="container p-4">
            <h2 class="mb-4">Hình ảnh công thức</h2>
            <img src="../img/congthuc.jpg" alt="Công thức minh họa" class="img-fixed">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>