<?php
session_start();
// Lưu ý đường dẫn: Phải đi ra ngoài 1 cấp (../) mới thấy config
include '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Tìm trong bảng ADMIN (chứ không phải NGUOI_DUNG)
    $sql = "SELECT * FROM ADMIN WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);

        if (password_verify($password, $admin['matKhau'])) {
            // Lưu Session đặc biệt cho Admin
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['ten'];
            $_SESSION['admin_role'] = $admin['vaiTro'];

            header("Location: index.php"); // Chuyển vào Dashboard
            exit();
        } else {
            $error = "Sai mật khẩu Admin!";
        }
    } else {
        $error = "Email không tồn tại hoặc không có quyền Admin!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Admin</title>
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/logoicon.png">
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-900 h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-2xl w-96">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">QUẢN TRỊ VIÊN</h1>
            <p class="text-gray-500 text-sm">Vui lòng đăng nhập để quản lý</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm text-center font-bold">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2 text-gray-700">Email</label>
                <input type="email" name="email" value="admin@gmail.com" required
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-gray-800">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-bold mb-2 text-gray-700">Mật khẩu</label>
                <input type="password" name="password" placeholder="******" required
                    class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-gray-800">
            </div>
            <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded font-bold hover:bg-black transition">
                ĐĂNG NHẬP
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="../index.php" class="text-sm text-gray-500 hover:underline">← Về trang chủ bán hàng</a>
        </div>
    </div>

</body>

</html>