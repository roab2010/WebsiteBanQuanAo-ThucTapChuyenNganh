<?php
session_start();
include '../config/database.php';


if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}


$stmt_prod = $conn->query("SELECT COUNT(*) as c FROM SAN_PHAM");
$total_products = $stmt_prod->fetch(PDO::FETCH_ASSOC)['c'];

$stmt_ord = $conn->query("SELECT COUNT(*) as c FROM DON_HANG");
$total_orders = $stmt_ord->fetch(PDO::FETCH_ASSOC)['c'];


$stmt_new = $conn->query("SELECT COUNT(*) as c FROM DON_HANG WHERE trangThaiDH = 'Cho xu ly'");
$new_orders = $stmt_new->fetch(PDO::FETCH_ASSOC)['c'];


$stmt_user = $conn->query("SELECT COUNT(*) as c FROM NGUOI_DUNG");
$total_users = $stmt_user->fetch(PDO::FETCH_ASSOC)['c'];


$stmt_rev = $conn->query("SELECT SUM(tongTien) as total FROM DON_HANG WHERE trangThaiDH = 'Hoan tat'");
$revenue_data = $stmt_rev->fetch(PDO::FETCH_ASSOC);
$total_revenue = $revenue_data['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin Quản Trị</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logoicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen">

        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Tổng quan</h2>
                    <p class="text-gray-500 mt-1">Chào mừng, <strong><?php echo $_SESSION['admin_name']; ?></strong>!</p>
                </div>
                <div class="text-sm text-gray-500">
                    Hôm nay: <?php echo date('d/m/Y'); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500 flex items-center">
                    <div class="p-4 bg-yellow-100 rounded-full mr-4 text-yellow-600">
                        <i class="fas fa-coins text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase font-bold">Doanh thu</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($total_revenue, 0, ',', '.'); ?>₫</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500 flex items-center relative overflow-hidden">
                    <div class="p-4 bg-red-100 rounded-full mr-4 text-red-600 z-10">
                        <i class="fas fa-bell text-2xl <?php echo $new_orders > 0 ? 'animate-swing' : ''; ?>"></i>
                    </div>
                    <div class="z-10">
                        <p class="text-gray-500 text-xs uppercase font-bold">Đơn chờ xử lý</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $new_orders; ?></p>
                    </div>
                    <?php if ($new_orders > 0): ?>
                        <div class="absolute right-0 top-0 bottom-0 w-2 bg-red-500 animate-pulse"></div>
                    <?php endif; ?>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500 flex items-center">
                    <div class="p-4 bg-blue-100 rounded-full mr-4 text-blue-600">
                        <i class="fas fa-tshirt text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase font-bold">Sản phẩm</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $total_products; ?></p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500 flex items-center">
                    <div class="p-4 bg-purple-100 rounded-full mr-4 text-purple-600">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase font-bold">Khách hàng</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $total_users; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-lg rounded-xl p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg text-gray-800">Đơn hàng vừa đặt</h3>
                    <a href="donhang.php" class="text-blue-600 text-sm hover:underline">Xem tất cả →</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs uppercase text-gray-500 border-b">
                                <th class="py-3">Mã đơn</th>
                                <th class="py-3">Khách hàng</th>
                                <th class="py-3">Tổng tiền</th>
                                <th class="py-3">Trạng thái</th>
                                <th class="py-3 text-right">Ngày đặt</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php
                       
                            $sql_recent = "SELECT * FROM DON_HANG ORDER BY donhang_id DESC LIMIT 5";
                            $stmt_recent = $conn->query($sql_recent);

                            if ($stmt_recent->rowCount() > 0) {
                                while ($row = $stmt_recent->fetch(PDO::FETCH_ASSOC)):
                                    $status_class = ($row['trangThaiDH'] == 'Cho xu ly') ? 'text-yellow-600 bg-yellow-100' : 'text-gray-600 bg-gray-100';
                            ?>
                                    <tr class="border-b last:border-0 hover:bg-gray-50 transition">
                                        <td class="py-3 font-bold">#<?php echo $row['donhang_id']; ?></td>
                                        <td class="py-3"><?php echo htmlspecialchars($row['hoTenNguoiNhan']); ?></td>
                                        <td class="py-3 font-bold text-gray-800"><?php echo number_format($row['tongTien'], 0, ',', '.'); ?>₫</td>
                                        <td class="py-3">
                                            <span class="px-2 py-1 rounded text-xs font-bold <?php echo $status_class; ?>">
                                                <?php echo $row['trangThaiDH']; ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-right text-gray-500"><?php echo date('d/m H:i', strtotime($row['ngayTao'])); ?></td>
                                    </tr>
                            <?php
                                endwhile;
                            } else {
                                echo '<tr><td colspan="5" class="py-4 text-center text-gray-500">Chưa có đơn hàng nào.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <style>
        @keyframes swing {
            0% {
                transform: rotate(0deg);
            }

            20% {
                transform: rotate(15deg);
            }

            40% {
                transform: rotate(-10deg);
            }

            60% {
                transform: rotate(5deg);
            }

            80% {
                transform: rotate(-5deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        .animate-swing {
            animation: swing 1s infinite ease-in-out;
        }
    </style>
</body>

</html>