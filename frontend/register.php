<?php
include '../backend/database.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($username) && !empty($password)) {
        // Hash mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            $message = "Tạo tài khoản thành công!";
        } else {
            $message = "Lỗi: Tên đăng nhập đã tồn tại.";
        }
    } else {
        $message = "Vui lòng điền đầy đủ thông tin.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5" style="max-width: 500px;">
        <div class="card shadow">
            <div class="card-body">
                <h4 class="mb-4 text-center">Đăng ký tài khoản</h4>

                <?php if (!empty($message)): ?>
                <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label>Tên đăng nhập</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Quyền</label>
                        <select name="role" class="form-select">
                            <option value="nhanvien">Nhân viên</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button class="btn btn-success w-100">Đăng ký</button>
                </form>
                <div class="text-center mt-3">
                    <a href="login.php">Đã có tài khoản? Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>