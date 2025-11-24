<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy tất cả đơn hàng, mới nhất lên đầu
$sql = "SELECT * FROM DON_HANG ORDER BY donhang_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <h2 class="text-3xl font-bold mb-6">Danh sách Đơn hàng</h2>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-3 uppercase text-xs font-semibold">ID</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Khách hàng</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Tổng tiền</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Thanh toán</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Trạng thái</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)):
                            // Màu sắc trạng thái
                            $status_bg = 'bg-gray-200 text-gray-700';
                            if ($row['trangThaiDH'] == 'Cho xu ly') $status_bg = 'bg-yellow-200 text-yellow-800';
                            if ($row['trangThaiDH'] == 'Dang giao') $status_bg = 'bg-blue-200 text-blue-800';
                            if ($row['trangThaiDH'] == 'Hoan tat') $status_bg = 'bg-green-200 text-green-800';
                            if ($row['trangThaiDH'] == 'Huy') $status_bg = 'bg-red-200 text-red-800';
                        ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-5 py-4 text-sm font-bold">#<?php echo $row['donhang_id']; ?></td>
                                <td class="px-5 py-4 text-sm">
                                    <p class="font-bold text-gray-900"><?php echo htmlspecialchars($row['hoTenNguoiNhan']); ?></p>
                                    <p class="text-gray-500 text-xs"><?php echo $row['sdtNguoiNhan']; ?></p>
                                </td>
                                <td class="px-5 py-4 text-sm font-bold text-red-600">
                                    <?php echo number_format($row['tongTien'], 0, ',', '.'); ?>₫
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <span class="block text-xs font-semibold"><?php echo $row['phuongThucTT']; ?></span>
                                    <?php if (strpos($row['trangThaiTT'], 'Da thanh toan') !== false): ?>
                                        <span class="text-xs text-green-600 font-bold">✔ Đã TT</span>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-500">Chưa TT</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_bg; ?>">
                                        <?php echo $row['trangThaiDH']; ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <a href="chitietdonhang.php?id=<?php echo $row['donhang_id']; ?>"
                                        class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-bold transition">
                                        Xem / Xử lý
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>

</html>