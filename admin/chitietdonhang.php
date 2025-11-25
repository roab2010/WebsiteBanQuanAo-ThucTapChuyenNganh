<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- 1. XỬ LÝ CẬP NHẬT TRẠNG THÁI (Code mới dùng Session Alert) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['trangThaiDH'];
    $sql_update = "UPDATE DON_HANG SET trangThaiDH = '$status' WHERE donhang_id = $id";

    if (mysqli_query($conn, $sql_update)) {
        // Gán thông báo thành công
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Cập nhật trạng thái đơn hàng thành công!'];
    } else {
        // Gán thông báo lỗi
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi hệ thống: ' . mysqli_error($conn)];
    }

    // Reload lại trang để hiện thông báo
    header("Location: donhang.php?id=$id");
    exit();
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

// Logic kiểm tra thanh toán
$pt_tt = trim($order['phuongThucTT']);
$tt_dh = trim($order['trangThaiDH']);
$tt_tt = trim($order['trangThaiTT']);
$is_paid = false;
if (stripos($tt_tt, 'Da thanh toan') !== false) $is_paid = true;
if ($pt_tt == 'COD' && $tt_dh == 'Hoan tat') $is_paid = true;
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?php echo $id; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/logoicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .toast {
            display: flex;
            align-items: center;
            background: white;
            min-width: 300px;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            border-left: 6px solid #ccc;
            animation: slideInRight 0.5s ease forwards, fadeOut 0.5s ease 3s forwards;
        }

        .toast.success {
            border-color: #22c55e;
            color: #22c55e;
        }

        .toast.success .toast-icon {
            background-color: #dcfce7;
        }

        .toast.error {
            border-color: #ef4444;
            color: #ef4444;
        }

        .toast.error .toast-icon {
            background-color: #fee2e2;
        }

        .toast-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .toast-message {
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
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
                        <div class="space-y-4 text-sm">
                            <div><span class="text-gray-500 block mb-1">Người nhận:</span> <span class="font-bold text-base"><?php echo $order['hoTenNguoiNhan']; ?></span></div>
                            <div><span class="text-gray-500 block mb-1">Số điện thoại:</span> <span class="font-medium"><?php echo $order['sdtNguoiNhan']; ?></span></div>
                            <div><span class="text-gray-500 block mb-1">Địa chỉ:</span> <span class="font-medium"><?php echo $order['diaChiGiaoHang']; ?></span></div>

                            <div class="pt-3 border-t border-gray-100">
                                <span class="text-gray-500 block mb-1">Thanh toán:</span>
                                <?php if ($is_paid): ?>
                                    <span class="font-bold text-green-600 flex items-center gap-1"><i class="fas fa-check-circle"></i> <?php echo $pt_tt; ?> - Đã thanh toán</span>
                                <?php else: ?>
                                    <span class="font-bold text-orange-500 flex items-center gap-1"><i class="fas fa-clock"></i> <?php echo $pt_tt; ?> - Chưa thanh toán</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white shadow rounded-lg p-6 border-l-4 border-blue-500">
                        <h3 class="font-bold text-lg mb-4">Xử lý đơn hàng</h3>
                        <form method="POST">
                            <label class="block text-sm font-bold mb-2 text-gray-700">Trạng thái đơn hàng:</label>
                            <div class="relative">
                                <select name="trangThaiDH" class="block appearance-none w-full bg-gray-50 border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                    <option value="Cho xu ly" <?php if ($tt_dh == 'Cho xu ly') echo 'selected'; ?>>🕒 Chờ xử lý</option>
                                    <option value="Dang giao" <?php if ($tt_dh == 'Dang giao') echo 'selected'; ?>>🚚 Đang giao hàng</option>
                                    <option value="Hoan tat" <?php if ($tt_dh == 'Hoan tat') echo 'selected'; ?>>✅ Hoàn tất</option>
                                    <option value="Huy" <?php if ($tt_dh == 'Huy') echo 'selected'; ?>>❌ Hủy đơn</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                                    </svg>
                                </div>
                            </div>
                            <button type="submit" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                                Cập nhật trạng thái
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="toast-container"></div>
    <script src="../assets/js/scripts.js"></script>

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast("<?php echo $_SESSION['alert']['message']; ?>", "<?php echo $_SESSION['alert']['type']; ?>");
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

</body>

</html>