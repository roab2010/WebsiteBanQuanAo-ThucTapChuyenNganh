<?php
// quentaikhoan.php - DIRECT FIX
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error_message = "Vui lòng nhập email đã đăng ký.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email không hợp lệ.";
    } else {
        // Giả lập gửi email reset password
        $success_message = "Hướng dẫn khôi phục mật khẩu đã được gửi đến email: " . htmlspecialchars($email);
    }
}

// Nếu đã đăng nhập thì redirect về trang chủ
if (isset($_SESSION['user'])) {
    header("Location: trangchu.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu - 3 chàng lính ngự lâm</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="nav-links">
                <a href="trangchu.php" class="nav-link">Home</a>
                <span class="separator">|</span>
                <a href="#" class="nav-link">Đồ Nam</a>
                <span class="separator">|</span>
                <a href="#" class="nav-link">Đồ Nữ</a>
                <span class="separator">|</span>
                <a href="#" class="nav-link">Đồ Bé Trai</a>
                <span class="separator">|</span>
                <a href="#" class="nav-link">Đồ Bé Gái</a>
            </div>
            
            <div class="search-container">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" placeholder="Tìm kiếm sản phẩm..." class="search-input">
                    <button class="search-btn">Tìm kiếm</button>
                </div>
            </div>
            
            <div class="header-icons">
                <a href="dangnhap.php" class="icon-link" title="Đăng nhập">👤</a>
                <a href="#" class="icon-link" title="Yêu thích">❤️</a>
                <a href="#" class="icon-link" title="Giỏ hàng">🛒</a>
            </div>
        </div>
    </header>

    <div class="login-container">
        <h2>Quên Mật Khẩu</h2>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <p style="text-align: center; margin-bottom: 20px; color: #666;">
            Nhập email đã đăng ký để nhận hướng dẫn khôi phục mật khẩu.
        </p>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email đăng ký *</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                       placeholder="Nhập email đã đăng ký" required>
            </div>

            <button type="submit" class="login-btn">Gửi Yêu Cầu</button>
        </form>

        <div class="links">
            <a href="dangnhap.php">← Quay lại đăng nhập</a>
            <a href="dangki.php">Tạo tài khoản mới</a>
        </div>

        <div class="demo-info">
            <p><strong>Lưu ý:</strong> Đây là tính năng demo. Trong thực tế, hệ thống sẽ gửi email hướng dẫn reset mật khẩu.</p>
        </div>
    </div>

    <footer>
        <div>
            <h3>VỀ CHÚNG TÔI</h3>
            <p>
                3 chàng lính ngự lâm là thương hiệu thời trang dành cho mọi lứa tuổi, mang đến
                phong cách hiện đại và năng động.
            </p>
        </div>
        <div>
            <h3>HƯỚNG DẪN</h3>
            <p>Cách đặt hàng, chính sách đổi trả, và các câu hỏi thường gặp.</p>
        </div>
        <div>
            <h3>THÔNG TIN LIÊN HỆ</h3>
            <p>
                Email: support@vettins.vn<br />Hotline: 1900 1234<br />Địa chỉ: TP. Hồ
                Chí Minh
            </p>
        </div>
    </footer>

    <?php include 'config-js.php'; ?>
    <script src="scripts.js"></script>
</body>
</html>