<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/database.php';


if (!($conn instanceof PDO)) {
    die("<h1 style='color:red'>LỖI: File config/database.php chưa phải là PDO!</h1>");
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$orders = [];
$error_msg = "";

try {

    $sql = "SELECT * FROM DON_HANG ORDER BY donhang_id DESC";
    $stmt = $conn->query($sql);

    if ($stmt->rowCount() > 0) {
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error_msg = "Chưa có đơn hàng nào trong hệ thống.";
    }
} catch (PDOException $e) {
    $error_msg = "❌ LỖI SQL: " . $e->getMessage() . "<br>Kiểm tra lại tên bảng 'DON_HANG' (Có thể là 'don_hang'? check lại database)";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Đơn hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logoicon.png">
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
            <h2 class="text-3xl font-bold mb-6">Danh sách Đơn hàng</h2>

            <?php if ($error_msg): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <p class="font-bold">⚠️ Trạng thái:</p>
                    <p><?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>

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
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $row):
                                $pt_tt = trim($row['phuongThucTT']);
                                $tt_dh = trim($row['trangThaiDH']);
                                $tt_tt = trim($row['trangThaiTT']);

                                $status_bg = 'bg-gray-200 text-gray-700';
                                if ($tt_dh == 'Cho xu ly') $status_bg = 'bg-yellow-200 text-yellow-800';
                                if ($tt_dh == 'Dang giao') $status_bg = 'bg-blue-200 text-blue-800';
                                if ($tt_dh == 'Hoan tat') $status_bg = 'bg-green-200 text-green-800';
                                if ($tt_dh == 'Huy') $status_bg = 'bg-red-200 text-red-800';

                                $is_paid = false;
                                if (stripos($tt_tt, 'Da thanh toan') !== false) $is_paid = true;
                                if ($pt_tt == 'COD' && $tt_dh == 'Hoan tat') $is_paid = true;
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
                                        <span class="block text-xs font-semibold text-gray-500 mb-1"><?php echo $pt_tt; ?></span>
                                        <?php if ($is_paid): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg> Đã TT
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">Chưa TT</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_bg; ?>">
                                            <?php echo $tt_dh; ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <a href="chitietdonhang.php?id=<?php echo $row['donhang_id']; ?>"
                                            class="text-blue-600 hover:text-blue-900 font-bold text-xs border border-blue-600 px-3 py-1 rounded hover:bg-blue-50 transition">
                                            Xử lý
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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