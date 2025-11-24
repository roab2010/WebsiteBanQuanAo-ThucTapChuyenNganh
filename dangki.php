<?php
// dangki.php
// dangki.php - DIRECT FIX
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_message = '';
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Vui lòng điền đầy đủ thông tin bắt buộc.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Mật khẩu xác nhận không khớp.";
    } elseif (strlen($password) < 6) {
        $error_message = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email không hợp lệ.";
    } else {
        // Giả lập đăng ký thành công (thực tế sẽ lưu vào database)
        $success_message = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
        
        // Redirect sau 3 giây
        header("refresh:3;url=dangnhap.php");
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
    <title>Đăng Ký - 3 chàng lính ngự lâm</title>
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
        <h2>Đăng Ký Tài Khoản</h2>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Tên đăng nhập *</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           placeholder="Nhập tên đăng nhập" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                           placeholder="Nhập email" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Mật khẩu *</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Nhập mật khẩu (ít nhất 6 ký tự)" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu *</label>
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Nhập lại mật khẩu" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fullname">Họ và tên</label>
                    <input type="text" id="fullname" name="fullname" 
                           value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" 
                           placeholder="Nhập họ và tên">
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                           placeholder="Nhập số điện thoại">
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="agree_terms" required>
                    Tôi đồng ý với <a href="#" style="color: var(--secondary-color);">điều khoản sử dụng</a> và <a href="#" style="color: var(--secondary-color);">chính sách bảo mật</a>
                </label>
            </div>

            <button type="submit" class="login-btn">Đăng Ký</button>
        </form>

        <div class="links">
            <span>Đã có tài khoản? <a href="dangnhap.php">Đăng nhập ngay</a></span>
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