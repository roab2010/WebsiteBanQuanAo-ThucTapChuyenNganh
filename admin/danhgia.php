<?php
// BẬT BÁO LỖI ĐỂ DỄ KIỂM TRA
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// --- 1. XỬ LÝ XÓA ĐÁNH GIÁ (PDO) ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt_del = $conn->prepare("DELETE FROM DANH_GIA WHERE danhgia_id = ?");
        if ($stmt_del->execute([$id])) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Đã xóa đánh giá thành công!'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi khi xóa đánh giá.'];
        }
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi SQL: ' . $e->getMessage()];
    }
    header("Location: danhgia.php");
    exit();
}

// --- 2. LẤY DANH SÁCH ĐÁNH GIÁ (PDO) ---
$reviews = [];
$error_msg = "";

try {
    // KẾT NỐI 3 BẢNG: DANH_GIA + SAN_PHAM + NGUOI_DUNG
    $sql = "SELECT dg.*, sp.ten as ten_sp, sp.hinhAnh, nd.ten as ten_khach, nd.email 
            FROM DANH_GIA dg 
            JOIN SAN_PHAM sp ON dg.sanpham_id = sp.sanpham_id 
            JOIN NGUOI_DUNG nd ON dg.nguoi_id = nd.nguoi_id 
            ORDER BY dg.ngayTao DESC";

    $stmt = $conn->query($sql);

    if ($stmt) {
        if ($stmt->rowCount() > 0) {
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_msg = "Chưa có đánh giá nào từ khách hàng.";
        }
    }
} catch (PDOException $e) {
    $error_msg = "❌ LỖI SQL: " . $e->getMessage() . "<br>Kiểm tra lại tên bảng 'DANH_GIA', 'SAN_PHAM', 'NGUOI_DUNG'.";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Đánh giá</title>
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
            <h2 class="text-3xl font-bold mb-6 text-gray-800">Đánh giá khách hàng</h2>

            <?php if ($error_msg): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                    <p class="font-bold">⚠️ Trạng thái:</p>
                    <p><?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">ID</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Sản phẩm</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Khách hàng</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Đánh giá</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Ngày</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $row): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition duration-150">
                                    <td class="px-5 py-4 text-sm text-gray-500">#<?php echo $row['danhgia_id']; ?></td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="../<?php echo htmlspecialchars($row['hinhAnh']); ?>" class="w-10 h-10 object-cover rounded border bg-gray-100">
                                            <span class="font-bold text-sm text-gray-700 line-clamp-1 w-40" title="<?php echo htmlspecialchars($row['ten_sp']); ?>">
                                                <?php echo htmlspecialchars($row['ten_sp']); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <p class="font-bold text-sm"><?php echo htmlspecialchars($row['ten_khach']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($row['email']); ?></p>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="text-yellow-500 text-sm mb-1">
                                            <?php for ($i = 1; $i <= 5; $i++) echo ($i <= $row['soSao']) ? '★' : '☆'; ?>
                                        </div>
                                        <p class="text-sm text-gray-600 italic line-clamp-2 max-w-xs bg-gray-50 p-2 rounded">
                                            "<?php echo htmlspecialchars($row['noiDung']); ?>"
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($row['ngayTao'])); ?>
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <button type="button"
                                            onclick="openDeleteModal('danhgia.php?delete=<?php echo $row['danhgia_id']; ?>')"
                                            class="w-9 h-9 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm"
                                            title="Xóa đánh giá này">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="deleteModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="relative bg-white w-full max-w-sm mx-auto mt-40 p-6 rounded-lg shadow-2xl animate-fade-in-up text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Xác nhận xóa?</h3>
            <p class="text-gray-500 text-sm mb-6">Bạn có chắc chắn muốn xóa đánh giá này không?</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition">Hủy</button>
                <a id="confirmDeleteBtn" href="#" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition shadow-md">Xóa ngay</a>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>
    <script src="../assets/js/scripts.js"></script>
    <script>
        function openDeleteModal(url) {
            document.getElementById('confirmDeleteBtn').href = url;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>

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