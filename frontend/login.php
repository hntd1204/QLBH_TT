<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập | QLBH TYTEA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
        background: linear-gradient(to right, #eafaf1, #fff9f0);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: Arial, sans-serif;
    }

    .login-container {
        background-color: #fff;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
    }

    .logo {
        display: block;
        margin: 0 auto 1rem auto;
        max-width: 100px;
    }

    .title {
        text-align: center;
        font-weight: bold;
        color: #2e8b57;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .btn-login {
        background-color: #2e8b57;
        color: white;
    }

    .btn-login:hover {
        background-color: #246a45;
    }

    .text-small {
        font-size: 0.9rem;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Logo -->
        <img src="../img/logo.jpg" alt="TYTEA Logo" class="logo">

        <!-- Tiêu đề -->
        <div class="title">Đăng nhập hệ thống</div>

        <!-- Hiển thị lỗi nếu có -->
        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger text-small"><?= $_SESSION['error'];
                                                        unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Form đăng nhập -->
        <form method="POST" action="../backend/login_xuly.php">
            <div class="mb-3">
                <label for="username" class="form-label">Tên đăng nhập</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-login w-100">Đăng nhập</button>
        </form>

        <!-- Link đăng ký -->
        <!-- <div class="text-center mt-3 text-small">
            Chưa có tài khoản? <a href="register.php">Đăng ký</a>
        </div> -->
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>