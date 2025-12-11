<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt_img = $conn->prepare("SELECT hinhAnh FROM SAN_PHAM WHERE sanpham_id = ?");
        $stmt_img->execute([$id]);
        $img_data = $stmt_img->fetch(PDO::FETCH_ASSOC);

        $stmt_del = $conn->prepare("DELETE FROM SAN_PHAM WHERE sanpham_id = ?");
        if ($stmt_del->execute([$id])) {
            if ($img_data && !empty($img_data['hinhAnh'])) {
                $file_path = "../" . $img_data['hinhAnh'];
                if (file_exists($file_path)) unlink($file_path);
            }
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Đã xóa sản phẩm!'];
        }
    } catch (PDOException $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi xóa: ' . $e->getMessage()];
    }
    header("Location: sanpham.php");
    exit();
}


$products = [];
$error_msg = "";

try {

    $sql = "SELECT sp.*, dm.ten as ten_danhmuc 
            FROM SAN_PHAM sp 
            LEFT JOIN DANH_MUC dm ON sp.danhmuc_id = dm.danhmuc_id 
            ORDER BY sp.sanpham_id DESC";
    $stmt = $conn->query($sql);


    $count = $stmt->rowCount();

    if ($count > 0) {

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error_msg = "Kết nối thành công nhưng bảng SAN_PHAM đang TRỐNG (0 dòng).";
    }
} catch (PDOException $e) {
  
    $error_msg = "LỖI TRUY VẤN SQL: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logoicon.png">

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
                <a href="product-form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-md flex items-center transition">
                    <i class="fas fa-plus mr-2"></i> Thêm sản phẩm
                </a>
            </div>

            <?php if ($error_msg): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">⚠️ Có vấn đề xảy ra:</p>
                    <p><?php echo $error_msg; ?></p>
                    <p class="text-sm mt-2 text-gray-600">Gợi ý: Hãy kiểm tra lại tên bảng trong phpMyAdmin (viết hoa/thường) hoặc Insert thêm dữ liệu mẫu.</p>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-4 text-xs font-bold">ID</th>
                            <th class="px-5 py-4 text-xs font-bold">Hình ảnh</th>
                            <th class="px-5 py-4 text-xs font-bold">Tên sản phẩm</th>
                            <th class="px-5 py-4 text-xs font-bold">Danh mục</th>
                            <th class="px-5 py-4 text-xs font-bold">Giá bán</th>
                            <th class="px-5 py-4 text-xs font-bold text-center">Kho</th>
                            <th class="px-5 py-4 text-xs font-bold text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $row): ?>
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
                                            <?php echo htmlspecialchars($row['ten_danhmuc'] ?? '---'); ?>
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
                                                class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button"
                                                onclick="openDeleteModal('sanpham.php?delete=<?php echo $row['sanpham_id']; ?>')"
                                                class="w-9 h-9 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm">
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