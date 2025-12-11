<?php
// 1. BẬT BÁO LỖI (Để biết tại sao không hiện)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// --- XỬ LÝ XÓA ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        // Kiểm tra ràng buộc
        $check_stmt = $conn->prepare("SELECT sanpham_id FROM SAN_PHAM WHERE danhmuc_id = ?");
        $check_stmt->execute([$id]);

        if ($check_stmt->rowCount() > 0) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Không thể xóa! Danh mục này đang chứa sản phẩm.'];
        } else {
            $stmt_del = $conn->prepare("DELETE FROM DANH_MUC WHERE danhmuc_id = ?");
            if ($stmt_del->execute([$id])) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Đã xóa danh mục!'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi xóa danh mục.'];
            }
        }
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi SQL: ' . $e->getMessage()];
    }
    header("Location: danhmuc.php");
    exit();
}

// --- LẤY DỮ LIỆU & KIỂM TRA LỖI ---
$categories = [];
$error_msg = "";

try {
    // Truy vấn
    $sql = "SELECT * FROM DANH_MUC ORDER BY danhmuc_id ASC";
    $stmt = $conn->query($sql);

    // Kiểm tra xem query có chạy được không
    if ($stmt) {
        if ($stmt->rowCount() > 0) {
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error_msg = "Kết nối thành công nhưng BẢNG 'DANH_MUC' ĐANG TRỐNG (0 dòng). Hãy thêm dữ liệu mẫu!";
        }
    } else {
        $error_msg = "Lỗi truy vấn! Có thể sai tên bảng (Phân biệt HOA/thường).";
    }
} catch (PDOException $e) {
    $error_msg = "LỖI CRITICAL: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục</title>
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
                <h2 class="text-3xl font-bold text-gray-800">Danh sách Danh mục</h2>
                <a href="category-form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-md flex items-center transition transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i> Thêm danh mục
                </a>
            </div>

            <?php if ($error_msg): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">⚠️ Có lỗi xảy ra:</p>
                    <p><?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-lg rounded-xl overflow-hidden w-full lg:w-2/3 mx-auto lg:mx-0">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">ID</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Ảnh</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Tên danh mục</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Mô tả</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $row): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition duration-150">
                                    <td class="px-5 py-4 text-sm text-gray-500">#<?php echo $row['danhmuc_id']; ?></td>

                                    <td class="px-5 py-4">
                                        <?php if ($row['anh']): ?>
                                            <img src="../<?php echo htmlspecialchars($row['anh']); ?>" class="w-12 h-12 object-cover rounded-lg border border-gray-200 shadow-sm">
                                        <?php else: ?>
                                            <span class="w-12 h-12 flex items-center justify-center bg-gray-100 text-gray-400 rounded-lg text-xs">No img</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="font-bold text-gray-800 text-base"><?php echo htmlspecialchars($row['ten']); ?></span>
                                    </td>

                                    <td class="px-5 py-4 text-sm text-gray-500 italic max-w-xs truncate">
                                        <?php echo htmlspecialchars($row['moTa']); ?>
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="category-form.php?id=<?php echo $row['danhmuc_id']; ?>"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition"
                                                title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <button type="button"
                                                onclick="openDeleteModal('danhmuc.php?delete=<?php echo $row['danhmuc_id']; ?>')"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm"
                                                title="Xóa">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
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
            <p class="text-gray-500 text-sm mb-6">Bạn có chắc chắn muốn xóa danh mục này không?</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 rounded-lg font-medium">Hủy</button>
                <a id="confirmDeleteBtn" href="#" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium shadow-md">Xóa ngay</a>
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