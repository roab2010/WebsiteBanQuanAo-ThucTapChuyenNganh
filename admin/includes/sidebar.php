<?php
// 1. Lấy tên file hiện tại (ví dụ: index.php, donhang.php)
$current_page = basename($_SERVER['PHP_SELF']);

// 2. Hàm kiểm tra để active menu (Tô sáng mục đang chọn)
function activeClass($page, $current)
{
    // Nếu trang hiện tại trùng với link, hoặc là trang con của nó (ví dụ chi tiết đơn hàng cũng tính là đơn hàng)
    if (
        $page == $current || ($page == 'donhang.php' && $current == 'chitietdonhang.php')
        || ($page == 'sanpham.php' && $current == 'product-form.php')
        || ($page == 'danhmuc.php' && $current == 'category-form.php')
    ) {
        return 'bg-gray-800 border-l-4 border-blue-500 text-white';
    } else {
        return 'hover:bg-gray-800 text-gray-400 hover:text-white transition';
    }
}

// 3. Tính số đơn hàng mới (Để hiện thông báo đỏ)
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

<aside class="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0 transition-all duration-300 hidden md:flex" id="sidebar">
    <div class="h-16 flex items-center justify-center text-xl font-bold border-b border-gray-800 tracking-wider">
        <a href="../index.php" target="_blank" title="Xem trang chủ" class="flex items-center gap-2 hover:text-blue-400 transition">
            <i class="fas fa-store"></i> ADMIN
        </a>
    </div>

    <nav class="flex-1 py-6 space-y-1">

        <a href="index.php" class="block py-3 px-6 <?php echo activeClass('index.php', $current_page); ?>">
            <i class="fas fa-tachometer-alt mr-3 w-6 text-center"></i> Dashboard
        </a>

        <a href="sanpham.php" class="block py-3 px-6 <?php echo activeClass('sanpham.php', $current_page); ?>">
            <i class="fas fa-tshirt mr-3 w-6 text-center"></i> Sản phẩm
        </a>

        <a href="danhmuc.php" class="block py-3 px-6 <?php echo activeClass('danhmuc.php', $current_page); ?>">
            <i class="fas fa-list mr-3 w-6 text-center"></i> Danh mục
        </a>

        <a href="donhang.php" class="block py-3 px-6 relative <?php echo activeClass('donhang.php', $current_page); ?>">
            <i class="fas fa-shopping-cart mr-3 w-6 text-center"></i> Đơn hàng
            <?php if ($new_orders_count > 0): ?>
                <span class="absolute right-4 top-3 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse">
                    <?php echo $new_orders_count; ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="khachhang.php" class="block py-3 px-6 <?php echo activeClass('khachhang.php', $current_page); ?>">
            <i class="fas fa-users mr-3 w-6 text-center"></i> Khách hàng
        </a>
    </nav>

    <div class="border-t border-gray-800 p-4">
        <div class="flex items-center gap-3 mb-4 px-2">
            <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center font-bold">A</div>
            <div>
                <p class="text-sm font-bold text-white">Admin</p>
                <p class="text-xs text-gray-500">Super Admin</p>
            </div>
        </div>
        <a href="logout.php" class="block py-2 px-4 bg-red-600 hover:bg-red-700 text-white text-center rounded transition text-sm font-bold">
            <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
        </a>
    </div>
</aside>