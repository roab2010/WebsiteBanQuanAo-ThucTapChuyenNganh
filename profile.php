<?php
session_start();
include 'config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- PHẦN XỬ LÝ CẬP NHẬT THÔNG TIN (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu (PDO tự động làm sạch, không cần escape string)
    $ten = $_POST['ten'];
    $sdt = $_POST['sdt'];
    $diaChi = $_POST['diaChi'];

    // Validate cơ bản
    if (empty($ten)) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Tên hiển thị không được để trống!'];
    } else {
        try {
            // Cập nhật vào Database (PDO Prepared Statement)
            $sql_update = "UPDATE NGUOI_DUNG SET ten = ?, sdt = ?, diaChi = ? WHERE nguoi_id = ?";
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update->execute([$ten, $sdt, $diaChi, $user_id])) {
                // Cập nhật lại Session tên người dùng
                $_SESSION['user'] = $ten;

                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Cập nhật hồ sơ thành công!'];

                // Refresh lại trang
                header("Location: profile.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }
}

// 2. Lấy thông tin người dùng hiện tại (PDO)
$stmt_user = $conn->prepare("SELECT * FROM NGUOI_DUNG WHERE nguoi_id = ?");
$stmt_user->execute([$user_id]);
$user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

include './includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h1 class="text-xl font-bold text-gray-800">Hồ sơ của tôi</h1>
            <p class="text-sm text-gray-500">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
        </div>

        <div class="p-8">
            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Tên hiển thị</label>
                    <input type="text" name="ten" value="<?php echo htmlspecialchars($user_info['ten']); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email (Tên đăng nhập)</label>
                    <input type="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" readonly
                        class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded text-gray-500 cursor-not-allowed"
                        title="Không thể thay đổi email">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Số điện thoại</label>
                    <input type="text" name="sdt" value="<?php echo htmlspecialchars($user_info['sdt'] ?? ''); ?>"
                        placeholder="Thêm số điện thoại"
                        class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Địa chỉ giao hàng mặc định</label>
                    <textarea name="diaChi" rows="3"
                        placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."
                        class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition"><?php echo htmlspecialchars($user_info['diaChi'] ?? ''); ?></textarea>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex items-center gap-4">
                    <button type="submit" class="bg-black text-white px-6 py-2 rounded font-bold hover:bg-red-600 transition shadow-md">
                        Lưu thay đổi
                    </button>
                    <a href="index.php" class="text-gray-500 hover:text-black hover:underline">Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include './includes/footer.php'; ?>