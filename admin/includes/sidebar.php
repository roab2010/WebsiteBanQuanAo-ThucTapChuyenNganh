<?php
// 1. Lấy tên file hiện tại (ví dụ: index.php, donhang.php)
$current_page = basename($_SERVER['PHP_SELF']);

// 2. Hàm kiểm tra để active menu
// Nếu tên file trùng khớp thì trả về class màu sáng, ngược lại thì màu tối
function activeClass($page, $current)
{
    if ($page == $current) {
        return 'bg-gray-800 border-l-4 border-blue-500 text-white';
    } else {
        return 'hover:bg-gray-800 text-gray-400 hover:text-white transition';
    }
}

// 3. Tính số đơn hàng mới (Để hiện thông báo đỏ)
// Lưu ý: Biến $conn được lấy từ trang cha include file này
$new_orders_count = 0;
if (isset($conn)) {
    $sql_count = "SELECT COUNT(*) as c FROM DON_HANG WHERE trangThaiDH = 'Cho xu ly'";
    $rs = mysqli_query($conn, $sql_count);
    if ($rs) {
        $row = mysqli_fetch_assoc($rs);
        $new_orders_count = $row['c'];
    }
}
?>

<aside class="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0 transition-all duration-300" id="sidebar">
    <div class="h-16 flex items-center justify-center text-xl font-bold border-b border-gray-800 tracking-wider">
        ADMIN PANEL
    </div>

    <nav class="flex-1 py-6 space-y-1">
        <a href="index.php" class="block py-3 px-6 <?php echo activeClass('index.php', $current_page); ?>">
            <i class="fas fa-tachometer-alt mr-3 w-6"></i> Dashboard
        </a>

        <a href="sanpham.php" class="block py-3 px-6 <?php echo activeClass('sanpham.php', $current_page); ?>">
            <i class="fas fa-tshirt mr-3 w-6"></i> Sản phẩm
        </a>

        <a href="donhang.php" class="block py-3 px-6 relative <?php echo activeClass('donhang.php', $current_page); ?> <?php echo activeClass('chitietdonhang.php', $current_page); ?>">
            <i class="fas fa-shopping-cart mr-3 w-6"></i> Đơn hàng
            <?php if ($new_orders_count > 0): ?>
                <span class="absolute right-4 top-3 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                    <?php echo $new_orders_count; ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="khachhang.php" class="block py-3 px-6 <?php echo activeClass('khachhang.php', $current_page); ?>">
            <i class="fas fa-users mr-3 w-6"></i> Khách hàng
        </a>
    </nav>

    <a href="logout.php" class="block py-4 px-6 bg-gray-800 hover:bg-red-700 text-center transition mt-auto border-t border-gray-800">
        <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
    </a>
</aside>