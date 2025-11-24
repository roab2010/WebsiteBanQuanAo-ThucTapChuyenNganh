<?php
session_start();
include '../config/database.php';

// BẮT BUỘC: Kiểm tra xem có phải Admin không?
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Thống kê sơ bộ (Ví dụ)
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM SAN_PHAM"))['c'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM DON_HANG"))['c'];
$new_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM DON_HANG WHERE trangThaiDH = 'Cho xu ly'"))['c'];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <h2 class="text-3xl font-bold mb-8">Tổng quan</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow flex items-center border-l-4 border-blue-500">
                    <div class="p-4 bg-blue-100 rounded-full mr-4 text-blue-600">
                        <i class="fas fa-tshirt text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Tổng sản phẩm</p>
                        <p class="text-2xl font-bold"><?php echo $total_products; ?></p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow flex items-center border-l-4 border-green-500">
                    <div class="p-4 bg-green-100 rounded-full mr-4 text-green-600">
                        <i class="fas fa-shopping-bag text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Tổng đơn hàng</p>
                        <p class="text-2xl font-bold"><?php echo $total_orders; ?></p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow flex items-center border-l-4 border-red-500">
                    <div class="p-4 bg-red-100 rounded-full mr-4 text-red-600">
                        <i class="fas fa-bell text-2xl animate-pulse"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Đơn chờ xử lý</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $new_orders; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="font-bold text-lg mb-4">Chào mừng Admin trở lại!</h3>
                <p class="text-gray-600">Chọn các mục bên trái để bắt đầu quản lý cửa hàng của bạn.</p>
            </div>
        </main>
    </div>

</body>

</html>