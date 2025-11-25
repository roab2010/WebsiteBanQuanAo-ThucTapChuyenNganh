<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// --- 1. XỬ LÝ XÓA (Dùng Session Alert thay vì echo script) ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Kiểm tra danh mục có sản phẩm không
    $check = mysqli_query($conn, "SELECT * FROM SAN_PHAM WHERE danhmuc_id = $id");

    if (mysqli_num_rows($check) > 0) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Không thể xóa! Danh mục này đang chứa sản phẩm.'];
    } else {
        $sql_delete = "DELETE FROM DANH_MUC WHERE danhmuc_id = $id";
        if (mysqli_query($conn, $sql_delete)) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Đã xóa danh mục thành công!'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi hệ thống: ' . mysqli_error($conn)];
        }
    }
    // Reload lại trang để hiện thông báo
    header("Location: danhmuc.php");
    exit();
}

$sql = "SELECT * FROM DANH_MUC ORDER BY danhmuc_id ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Danh mục</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
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
                        <?php endwhile; ?>
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
            <p class="text-gray-500 text-sm mb-6">Bạn có chắc chắn muốn xóa danh mục này không? Hành động này không thể hoàn tác.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium transition">Hủy</button>
                <a id="confirmDeleteBtn" href="#" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition shadow-md">Xóa ngay</a>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>

    <script src="../assets/js/scripts.js"></script>
    <script>
        // Hàm mở modal xóa
        function openDeleteModal(url) {
            document.getElementById('confirmDeleteBtn').href = url;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        // Hàm đóng modal
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