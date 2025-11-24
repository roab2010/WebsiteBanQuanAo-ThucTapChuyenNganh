<?php
// includes/header.php

if (file_exists(__DIR__ . '/../session-manager.php')) {
    include __DIR__ . '/../session-manager.php';
} else {
    // Fallback nếu không tìm thấy file
    if (session_status() === PHP_SESSION_NONE) session_start();
}

// Định nghĩa biến $user ngay tại đây để file nào include nó cũng dùng được
$user = $_SESSION['user'] ?? null;
$search_query = $search_query ?? '';

$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Dùng hàm SUM để cộng dồn cột soLuong
    $sql_count = "SELECT SUM(soLuong) as total FROM GIO_HANG WHERE nguoi_id = $user_id";
    $result_count = mysqli_query($conn, $sql_count);
    $row_count = mysqli_fetch_assoc($result_count);

    // Nếu có kết quả thì lấy, không thì bằng 0
    $cart_count = $row_count['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>3 chàng lính ngự lâm - Trang Chủ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/styles.css" />
</head>

<body>
    <header>
        <div class="header-container">
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <span class="separator">|</span>
                <a href="#" class="nav-link">Top</a>
                <span class="separator">|</span>
                <a href="#" class="nav-link">Bottoms</a>
                <span class="separator">|</span>
                <a href="#" class="nav-link">OutWear</a>
                <span class="separator">|</span>
                <a href="#" class="nav-link">Accessories</a>
            </div>

            <div class="search-container">
                <form method="GET" action="index.php">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." class="search-input" value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="search-btn">Tìm kiếm</button>
                    </div>
                </form>
            </div>

            <div class="header-icons">
                <?php if ($user): ?>
                    <span class="user-info">Xin chào, <?php echo htmlspecialchars($user); ?></span>
                    <a href="?logout=1" class="icon-link" title="Đăng xuất">🚪</a>
                <?php else: ?>
                    <a href="dangnhap.php" class="icon-link" title="Đăng nhập">👤</a>
                <?php endif; ?>
                <a href="#" class="icon-link" title="Yêu thích">❤️</a>
                <a href="giohang.php" class="icon-link relative" title="Giỏ hàng">
                    🛒
                    <?php if ($cart_count > 0): ?>
                        <span class="absolute -top-2 -right-3 bg-red-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full border border-white">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <div id="toast-container"></div>

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            // Đợi trang tải xong thì gọi hàm hiện thông báo
            document.addEventListener('DOMContentLoaded', function() {
                showToast("<?php echo $_SESSION['alert']['message']; ?>", "<?php echo $_SESSION['alert']['type']; ?>");
            });
        </script>
        <?php
        // Hiển thị xong thì xóa ngay để F5 không hiện lại
        unset($_SESSION['alert']);
        ?>
    <?php endif; ?>
</body>