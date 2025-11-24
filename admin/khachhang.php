<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy danh sách khách hàng
$sql = "SELECT * FROM NGUOI_DUNG ORDER BY nguoi_id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Khách hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <h2 class="text-3xl font-bold mb-6">Danh sách Khách hàng</h2>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-3 uppercase text-xs font-semibold">ID</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Tên hiển thị</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Email (Tài khoản)</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Số điện thoại</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Địa chỉ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 text-sm">
                                <td class="px-5 py-4">#<?php echo $row['nguoi_id']; ?></td>
                                <td class="px-5 py-4 font-bold text-gray-800">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-3 text-gray-600 font-bold uppercase">
                                            <?php echo substr($row['ten'], 0, 1); ?>
                                        </div>
                                        <?php echo htmlspecialchars($row['ten']); ?>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-blue-600"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="px-5 py-4"><?php echo $row['sdt'] ?? '---'; ?></td>
                                <td class="px-5 py-4 text-gray-500 truncate max-w-xs" title="<?php echo $row['diaChi']; ?>">
                                    <?php echo htmlspecialchars($row['diaChi'] ?? '---'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>

</html>