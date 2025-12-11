<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


if (!file_exists('../config/database.php')) {
    die("<h1 style='color:red'>LỖI: Không tìm thấy file config/database.php</h1>");
}
include '../config/database.php';


if (!($conn instanceof PDO)) {
    die("<h1 style='color:red'>LỖI NGHIÊM TRỌNG: File config/database.php chưa chuyển sang PDO! Hãy sửa file config trước.</h1>");
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$customers = [];
$error_msg = "";

try {
   
    $sql = "SELECT * FROM NGUOI_DUNG ORDER BY nguoi_id DESC";
    $stmt = $conn->query($sql);

    if ($stmt->rowCount() > 0) {
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error_msg = "Kết nối OK nhưng chưa có khách hàng nào.";
    }
} catch (PDOException $e) {

    $error_msg = "❌ LỖI SQL: " . $e->getMessage() . "<br>Gợi ý: Kiểm tra lại tên bảng trong Database xem là 'NGUOI_DUNG' hay 'nguoi_dung'?";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Khách hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logoicon.png">
</head>

<body class="bg-gray-100">

    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <h2 class="text-3xl font-bold mb-6">Danh sách Khách hàng</h2>

            <?php if ($error_msg): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                    <p class="font-bold">⚠️ Thông báo:</p>
                    <p><?php echo $error_msg; ?></p>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-3 text-xs font-semibold">ID</th>
                            <th class="px-5 py-3 text-xs font-semibold">Tên hiển thị</th>
                            <th class="px-5 py-3 text-xs font-semibold">Email</th>
                            <th class="px-5 py-3 text-xs font-semibold">SĐT</th>
                            <th class="px-5 py-3 text-xs font-semibold">Địa chỉ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($customers)): ?>
                            <?php foreach ($customers as $row): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 text-sm">
                                    <td class="px-5 py-4">#<?php echo $row['nguoi_id']; ?></td>
                                    <td class="px-5 py-4 font-bold"><?php echo htmlspecialchars($row['ten']); ?></td>
                                    <td class="px-5 py-4 text-blue-600"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="px-5 py-4"><?php echo $row['sdt'] ?? '---'; ?></td>
                                    <td class="px-5 py-4 text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($row['diaChi'] ?? '---'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>