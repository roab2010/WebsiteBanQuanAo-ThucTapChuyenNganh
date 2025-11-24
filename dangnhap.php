<?php
// Khởi tạo session
// dangnhap.php - DIRECT FIX
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Xử lý đăng nhập
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error_message = "Vui lòng nhập đầy đủ tên tài khoản và mật khẩu.";
    } else {
        // Giả lập kiểm tra đăng nhập (thực tế sẽ kiểm tra database)
        if ($username === 'admin' && $password === '123456') {
            $_SESSION['user'] = $username;
            $_SESSION['login_time'] = time();
            $success_message = "Đăng nhập thành công! Chào mừng " . $username;
            // Redirect sau 2 giây
            header("refresh:2;url=trangchu.php");
        } else {
            $error_message = "Tên tài khoản hoặc mật khẩu không đúng.";
        }
    }
}

// Kiểm tra nếu đã đăng nhập
if (isset($_SESSION['user'])) {
    header("Location: trangchu.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>3 Chàng lính ngự lâm - Đăng Nhập</title>
    <link rel="stylesheet" href="styles.php" />
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
          <a href="dangnhap.php" class="icon-link" title="Tài khoản">👤</a>
          <a href="#" class="icon-link" title="Túi mua sắm">🛍️</a>
          <a href="#" class="icon-link" title="Yêu thích">❤️</a>
          <a href="#" class="icon-link" title="Giỏ hàng">🛒</a>
        </div>
      </div>
    </header>

    <div class="container">
      <h2>Đăng Nhập</h2>
      
      <?php if ($error_message): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
      <?php endif; ?>
      
      <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
      <?php endif; ?>
      
      <form method="POST" action="">
        <label for="username">Tên Tài Khoản</label>
        <input
          type="text"
          id="username"
          name="username"
          placeholder="Nhập tên tài khoản"
          value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
          required
        />

        <label for="password">Mật Khẩu</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Nhập mật khẩu"
          required
        />

        <button type="submit" class="login-btn">Đăng Nhập Ngay</button>
      </form>

      <div class="links">
    <a href="quentaikhoan.php">Quên Mật Khẩu?</a>
    <a href="dangki.php">Tạo Tài Khoản Ngay</a>
</div>
      
      <div class="demo-info">
        <p><strong>Demo:</strong> Username: admin | Password: 123456</p>
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
    <link rel="stylesheet" href="styles.css">
  </body>
</html>
