<?php
session_start();
include 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu và làm sạch
    $ten = mysqli_real_escape_string($conn, $_POST['ten']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validate cơ bản
    if ($password !== $confirm_password) {
        $error = "Mật khẩu nhập lại không khớp!";
    } else {
        // 2. Kiểm tra email đã tồn tại trong bảng NGUOI_DUNG chưa
        $check_sql = "SELECT * FROM NGUOI_DUNG WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Email này đã được sử dụng, vui lòng chọn email khác!";
        } else {
            // 3. Mã hóa mật khẩu
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 4. Thêm vào Database
            $sql = "INSERT INTO NGUOI_DUNG (ten, email, matKhau) VALUES ('$ten', '$email', '$hashed_password')";

            if (mysqli_query($conn, $sql)) {
                // Gán thông báo thành công
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Đăng ký thành công! Vui lòng đăng nhập.'];
                // Chuyển hướng NGAY LẬP TỨC sang trang đăng nhập
                header("Location: dangnhap.php");


                exit();
            } else {
                // Lỗi thì ở lại trang hiện tại và báo lỗi
                $error = "Lỗi hệ thống: " . mysqli_error($conn);
                // Hoặc dùng Toast nếu muốn:
                // $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi: ' . mysqli_error($conn)];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - BCB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/logoicon.png">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold uppercase tracking-wider mb-2">Đăng Ký</h1>
            <p class="text-gray-500 text-sm">Tạo tài khoản để mua sắm ngay</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded mb-4 text-sm font-medium border border-red-100">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 text-green-600 p-3 rounded mb-4 text-sm font-medium border border-green-100">
                ✅ <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Họ và Tên</label>
                <input type="text" name="ten" placeholder="Ví dụ: Nguyễn Văn A" required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" placeholder="email@example.com" required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Mật khẩu</label>
                <input type="password" name="password" placeholder="******" required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold mb-2">Nhập lại mật khẩu</label>
                <input type="password" name="confirm_password" placeholder="******" required
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition">
            </div>

            <button type="submit" class="w-full bg-black text-white font-bold py-3 rounded hover:bg-gray-800 transition uppercase tracking-wider">
                Đăng Ký
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <p class="text-gray-600">Đã có tài khoản? <a href="dangnhap.php" class="font-bold hover:underline">Đăng nhập ngay</a></p>
            <div class="mt-4 pt-4 border-t">
                <a href="index.php" class="text-gray-400 hover:text-black transition">← Quay về trang chủ</a>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/scripts.js"></script> <?php if (isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast("<?php echo $_SESSION['alert']['message']; ?>", "<?php echo $_SESSION['alert']['type']; ?>");
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

</body>

</html>