<?php
session_start();
include 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = $_POST['password'];

  // 1. Tìm user trong DB
  $sql = "SELECT * FROM NGUOI_DUNG WHERE email = '$email'";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);

    // 2. Kiểm tra mật khẩu (So khớp Hash)
    // dangnhap.php

    if (password_verify($password, $user['matKhau'])) {
      // ... (Các code lưu session user_id, user... giữ nguyên) ...
      $_SESSION['user_id'] = $user['nguoi_id'];
      $_SESSION['user'] = $user['ten'];

      // GÁN THÔNG BÁO THÀNH CÔNG
      $_SESSION['alert'] = ['type' => 'success', 'message' => 'Đăng nhập thành công. Chào mừng ' . $user['ten'] . '!'];

      // Chuyển về trang chủ
      header("Location: index.php");
      exit();
    } else {
      $error = "Mật khẩu không chính xác!";
      // Hoặc dùng Toast cho đẹp:
      // $_SESSION['alert'] = ['type' => 'error', 'message' => 'Mật khẩu không đúng!'];
    }
  } else {
    $error = "Email này chưa được đăng ký!";
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng Nhập - BCB</title>
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
      <h1 class="text-2xl font-bold uppercase tracking-wider mb-2">Đăng Nhập</h1>
      <p class="text-gray-500 text-sm">Chào mừng bạn quay trở lại</p>
    </div>

    <?php if ($error): ?>
      <div class="bg-red-50 text-red-600 p-3 rounded mb-4 text-sm font-medium border border-red-100">
        ⚠️ <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-4">
        <label class="block text-sm font-bold mb-2">Email</label>
        <input type="email" name="email" placeholder="email@example.com" required
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition">
      </div>

      <div class="mb-6">
        <div class="flex justify-between items-center mb-2">
          <label class="block text-sm font-bold">Mật khẩu</label>
          <a href="#" class="text-xs text-gray-500 hover:text-black">Quên mật khẩu?</a>
        </div>
        <input type="password" name="password" placeholder="******" required
          class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-black focus:ring-1 focus:ring-black transition">
      </div>

      <button type="submit" class="w-full bg-black text-white font-bold py-3 rounded hover:bg-gray-800 transition uppercase tracking-wider">
        Đăng Nhập
      </button>
    </form>

    <div class="mt-6 text-center text-sm">
      <p class="text-gray-600">Chưa có tài khoản? <a href="dangky.php" class="font-bold hover:underline">Đăng ký ngay</a></p>
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