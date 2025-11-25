<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// --- 1. XỬ LÝ XÓA (Dùng Session Alert & Redirect) ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // (Tùy chọn) Xóa ảnh cũ khỏi thư mục để tiết kiệm dung lượng
    $query_img = mysqli_query($conn, "SELECT hinhAnh FROM SAN_PHAM WHERE sanpham_id=$id");
    $img_data = mysqli_fetch_assoc($query_img);

    // Xóa dữ liệu trong DB
    $sql_delete = "DELETE FROM SAN_PHAM WHERE sanpham_id = $id";

    if (mysqli_query($conn, $sql_delete)) {
        // Nếu xóa DB thành công thì xóa luôn file ảnh nếu có
        if ($img_data && !empty($img_data['hinhAnh'])) {
            $file_path = "../" . $img_data['hinhAnh'];
            if (file_exists($file_path)) unlink($file_path);
        }

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Đã xóa sản phẩm thành công!'];
    } else {
        // Thường lỗi do ràng buộc khóa ngoại (Sản phẩm đã có trong đơn hàng)
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Không thể xóa! Sản phẩm này đã có người mua.'];
    }

    // Reload lại trang
    header("Location: sanpham.php");
    exit();
}

// Lấy danh sách sản phẩm + Tên danh mục
$sql = "SELECT sp.*, dm.ten as ten_danhmuc 
        FROM SAN_PHAM sp 
        LEFT JOIN DANH_MUC dm ON sp.danhmuc_id = dm.danhmuc_id 
        ORDER BY sp.sanpham_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm</title>
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
                <h2 class="text-3xl font-bold text-gray-800">Danh sách Sản phẩm</h2>
                <a href="product-form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-md flex items-center transition transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i> Thêm sản phẩm
                </a>
            </div>

            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">ID</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Hình ảnh</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Tên sản phẩm</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Danh mục</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider">Giá bán</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider text-center">Kho</th>
                            <th class="px-5 py-4 uppercase text-xs font-bold tracking-wider text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition duration-150">
                                <td class="px-5 py-4 text-sm text-gray-500">#<?php echo $row['sanpham_id']; ?></td>

                                <td class="px-5 py-4">
                                    <div class="w-14 h-14 rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                        <img src="../<?php echo htmlspecialchars($row['hinhAnh']); ?>" class="w-full h-full object-cover">
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold text-gray-800 text-base"><?php echo htmlspecialchars($row['ten']); ?></p>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="bg-blue-50 text-blue-700 border border-blue-100 text-xs font-bold px-2.5 py-1 rounded-full">
                                        <?php echo htmlspecialchars($row['ten_danhmuc'] ?? 'Chưa phân loại'); ?>
                                    </span>
                                </td>

                                <td class="px-5 py-4 font-bold text-red-600 text-base">
                                    <?php echo number_format($row['gia'], 0, ',', '.'); ?>₫
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="px-2 py-1 rounded text-sm font-semibold <?php echo $row['soLuongTon'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $row['soLuongTon']; ?>
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="product-form.php?id=<?php echo $row['sanpham_id']; ?>"
                                            class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm"
                                            title="Sửa sản phẩm">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <button type="button"
                                            onclick="openDeleteModal('sanpham.php?delete=<?php echo $row['sanpham_id']; ?>')"
                                            class="w-9 h-9 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm"
                                            title="Xóa sản phẩm">
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
            <p class="text-gray-500 text-sm mb-6">Bạn có chắc chắn muốn xóa sản phẩm này không? Hành động này không thể hoàn tác.</p>
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