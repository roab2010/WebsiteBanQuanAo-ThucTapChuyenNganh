<?php
// includes/header.php

// 1. KHỞI ĐỘNG SESSION (Dùng file quản lý nếu có)
if (file_exists(__DIR__ . '/../session-manager.php')) {
    include __DIR__ . '/../session-manager.php';
} else {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

// 2. KẾT NỐI DATABASE (Bắt buộc để đếm giỏ hàng)
require_once __DIR__ . '/../config/database.php';

// 3. ĐỊNH NGHĨA BIẾN CHUNG
$user = $_SESSION['user'] ?? null;
$search_query = $search_query ?? ''; // Tránh lỗi undefined ở trang chi tiết

// 4. ĐẾM SỐ LƯỢNG GIỎ HÀNG
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql_count = "SELECT SUM(soLuong) as total FROM GIO_HANG WHERE nguoi_id = $user_id";
    $result_count = mysqli_query($conn, $sql_count);

    if ($result_count) {
        $row_count = mysqli_fetch_assoc($result_count);
        $cart_count = $row_count['total'] ?? 0;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>3 chàng lính ngự lâm - Trang Chủ</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="assets/css/styles.css" />
</head>

<body>
    <header class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-100">
        <div class="header-container">
            <div class="nav-links">
                <a href="index.php" class="mg-5"><img src="../assets/img/logonho.png" class="h-16 w-20" alt=""></a>
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
                    <span class="user-info text-sm font-medium mr-2">Hi, <?php echo htmlspecialchars($user); ?></span>
                    <a href="#" class="icon-link text-lg" title="Đăng xuất" onclick="event.preventDefault(); showLogoutModal();">🚪</a>
                <?php else: ?>
                    <a href="dangnhap.php" class="icon-link text-lg" title="Đăng nhập">👤</a>
                <?php endif; ?>

                <a href="#" class="icon-link text-lg" title="Yêu thích">❤️</a>

                <a href="giohang.php" class="icon-link relative text-lg" title="Giỏ hàng">
                    🛒
                    <?php if ($cart_count > 0): ?>
                        <span class="absolute -top-2 -right-3 bg-red-600 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </header>

    <div id="toast-container"></div>

    <?php
    // Nhúng Config JS để truyền biến PHP sang JS
    if (file_exists(__DIR__ . '/../config/config-js.php')) {
        include __DIR__ . '/../config/config-js.php';
    }
    ?>
    <script src="assets/js/scripts.js"></script>

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast("<?php echo $_SESSION['alert']['message']; ?>", "<?php echo $_SESSION['alert']['type']; ?>");
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <div id="logoutModal" class="fixed inset-0 z-[9999] hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeLogoutModal()"></div>

        <div class="relative bg-white w-full max-w-sm mx-auto mt-40 p-6 rounded-lg shadow-2xl animate-fade-in-up text-center">

            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                <span class="text-2xl">🚪</span>
            </div>

            <h3 class="text-lg font-bold text-gray-900 mb-2">Đăng xuất?</h3>
            <p class="text-gray-500 text-sm mb-6">Bạn có chắc chắn muốn đăng xuất khỏi tài khoản không?</p>

            <div class="flex gap-3 justify-center">
                <button onclick="closeLogoutModal()"
                    class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition">
                    Hủy
                </button>
                <a href="index.php?logout=1"
                    class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition shadow-md">
                    Đăng xuất ngay
                </a>
            </div>
        </div>
    </div>

    <script>
        function showLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }
    </script>
</body>