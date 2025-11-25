<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// XỬ LÝ CẬP NHẬT TRẠNG THÁI
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['trangThaiDH'];
    $sql_update = "UPDATE DON_HANG SET trangThaiDH = '$status' WHERE donhang_id = $id";

    if (mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Cập nhật trạng thái thành công!'); window.location.href='chitietdonhang.php?id=$id';</script>";
    } else {
        echo "<script>alert('Lỗi!');</script>";
    }
}

// Lấy thông tin đơn hàng
$sql = "SELECT * FROM DON_HANG WHERE donhang_id = $id";
$order = mysqli_fetch_assoc(mysqli_query($conn, $sql));

// Lấy chi tiết sản phẩm
$sql_items = "SELECT ct.*, sp.ten, sp.hinhAnh 
              FROM CHI_TIET_DON_HANG ct 
              JOIN SAN_PHAM sp ON ct.sanpham_id = sp.sanpham_id 
              WHERE ct.donhang_id = $id";
$items = mysqli_query($conn, $sql_items);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?php echo $id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/logoicon.png">
</head>

<body class="bg-gray-100">

    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold">Chi tiết đơn hàng #<?php echo $id; ?></h2>
                <a href="donhang.php" class="text-gray-600 hover:text-black">← Quay lại danh sách</a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2">
                    <div class="bg-white shadow rounded-lg p-6 mb-6">
                        <h3 class="font-bold text-lg mb-4 border-b pb-2">Sản phẩm đã mua</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-xs uppercase text-gray-500 border-b">
                                        <th class="pb-2">Sản phẩm</th>
                                        <th class="pb-2">Đơn giá</th>
                                        <th class="pb-2">Số lượng</th>
                                        <th class="pb-2 text-right">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    <?php while ($item = mysqli_fetch_assoc($items)): ?>
                                        <tr class="border-b last:border-0">
                                            <td class="py-3 flex items-center gap-3">
                                                <img src="../<?php echo $item['hinhAnh']; ?>" class="w-12 h-12 rounded border object-cover">
                                                <div>
                                                    <p class="font-bold"><?php echo $item['ten']; ?></p>
                                                    <p class="text-gray-500 text-xs">Size: <?php echo $item['size']; ?></p>
                                                </div>
                                            </td>
                                            <td class="py-3"><?php echo number_format($item['donGia'], 0, ',', '.'); ?>₫</td>
                                            <td class="py-3">x<?php echo $item['soLuong']; ?></td>
                                            <td class="py-3 text-right font-bold"><?php echo number_format($item['donGia'] * $item['soLuong'], 0, ',', '.'); ?>₫</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-right mt-4 pt-4 border-t">
                            <span class="text-gray-600 mr-2">Tổng cộng:</span>
                            <span class="text-2xl font-bold text-red-600"><?php echo number_format($order['tongTien'], 0, ',', '.'); ?>₫</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-6">

                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="font-bold text-lg mb-4 border-b pb-2">Thông tin giao hàng</h3>
                        <div class="space-y-3 text-sm">
                            <p><span class="text-gray-500 block">Người nhận:</span> <span class="font-bold"><?php echo $order['hoTenNguoiNhan']; ?></span></p>
                            <p><span class="text-gray-500 block">Số điện thoại:</span> <?php echo $order['sdtNguoiNhan']; ?></p>
                            <p><span class="text-gray-500 block">Địa chỉ:</span> <?php echo $order['diaChiGiaoHang']; ?></p>
                            <p><span class="text-gray-500 block">Ngày đặt:</span> <?php echo date('d/m/Y H:i', strtotime($order['ngayTao'])); ?></p>
                            <p><span class="text-gray-500 block">Thanh toán:</span>
                                <span class="font-bold <?php echo strpos($order['trangThaiTT'], 'Da thanh toan') !== false ? 'text-green-600' : 'text-orange-500'; ?>">
                                    <?php echo $order['phuongThucTT']; ?> - <?php echo $order['trangThaiTT']; ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="bg-white shadow rounded-lg p-6 border-l-4 border-blue-500">
                        <h3 class="font-bold text-lg mb-4">Xử lý đơn hàng</h3>
                        <form method="POST">
                            <label class="block text-sm font-bold mb-2">Trạng thái đơn hàng:</label>
                            <select name="trangThaiDH" class="w-full border p-2 rounded mb-4 focus:outline-none focus:border-blue-500">
                                <option value="Cho xu ly" <?php if ($order['trangThaiDH'] == 'Cho xu ly') echo 'selected'; ?>>🕒 Chờ xử lý</option>
                                <option value="Dang giao" <?php if ($order['trangThaiDH'] == 'Dang giao') echo 'selected'; ?>>🚚 Đang giao hàng</option>
                                <option value="Hoan tat" <?php if ($order['trangThaiDH'] == 'Hoan tat') echo 'selected'; ?>>✅ Hoàn tất</option>
                                <option value="Huy" <?php if ($order['trangThaiDH'] == 'Huy') echo 'selected'; ?>>❌ Hủy đơn</option>
                            </select>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded transition">
                                Cập nhật trạng thái
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </main>
    </div>

</body>

</html>