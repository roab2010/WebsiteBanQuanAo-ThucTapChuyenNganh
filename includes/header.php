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
                <a href="index.php" class="mr-6 flex items-center">
                    <img src="assets/img/logonho.png" class="h-16 w-auto object-contain" alt="Logo">
                </a>
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

            <div class="header-icons flex items-center gap-4">

                <a href="giohang.php" class="icon-link relative text-lg group" title="Giỏ hàng">
                    <span class="transition-transform group-hover:scale-110 inline-block">🛒</span>
                    <?php if ($cart_count > 0): ?>
                        <span class="absolute -top-2 -right-3 bg-red-600 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white animate-pulse">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if ($user): ?>

                    <div class="relative group ml-2">

                        <button class="flex items-center gap-2 focus:outline-none py-2">
                            <div class="w-8 h-8 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center text-gray-600 overflow-hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>

                            <span class="text-sm font-medium text-gray-700 group-hover:text-black max-w-[150px] truncate hidden md:block">
                                <?php echo htmlspecialchars($user); ?>
                            </span>

                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform group-hover:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="absolute right-0 mt-0 w-56 bg-white rounded-lg shadow-xl border border-gray-100 invisible opacity-0 group-hover:visible group-hover:opacity-100 transform group-hover:translate-y-2 transition-all duration-200 z-50 origin-top-right">

                            <div class="absolute -top-2 right-4 w-4 h-4 bg-white border-t border-l border-gray-100 transform rotate-45"></div>

                            <div class="py-2 relative z-10 bg-white rounded-lg">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-black transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Tài khoản của tôi
                                </a>

                                <a href="donhang.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-black transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    Đơn mua
                                </a>

                                <a href="yeuthich.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-black transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    Sản phẩm yêu thích
                                </a>

                                <div class="border-t border-gray-100 my-1"></div>

                                <a href="#" onclick="event.preventDefault(); showLogoutModal();" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    Đăng xuất
                                </a>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <a href="dangnhap.php" class="icon-link text-lg flex items-center gap-1" title="Đăng nhập">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                <?php endif; ?>

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