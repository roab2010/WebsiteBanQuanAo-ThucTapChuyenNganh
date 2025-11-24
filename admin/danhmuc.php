<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// XỬ LÝ XÓA
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Kiểm tra xem danh mục này có sản phẩm không?
    $check = mysqli_query($conn, "SELECT * FROM SAN_PHAM WHERE danhmuc_id = $id");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Không thể xóa! Danh mục này đang chứa sản phẩm.'); window.location.href='danhmuc.php';</script>";
    } else {
        $sql_delete = "DELETE FROM DANH_MUC WHERE danhmuc_id = $id";
        if (mysqli_query($conn, $sql_delete)) {
            echo "<script>alert('Đã xóa danh mục!'); window.location.href='danhmuc.php';</script>";
        }
    }
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
</head>

<body class="bg-gray-100">

    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold">Danh sách Danh mục</h2>
                <a href="category-form.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-bold shadow flex items-center transition">
                    <i class="fas fa-plus mr-2"></i> Thêm danh mục
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden w-full lg:w-2/3">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-3 uppercase text-xs font-semibold">ID</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Ảnh đại diện</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Tên danh mục</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Mô tả</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 text-sm">
                                <td class="px-5 py-4">#<?php echo $row['danhmuc_id']; ?></td>
                                <td class="px-5 py-4">
                                    <?php if ($row['anh']): ?>
                                        <img src="../<?php echo htmlspecialchars($row['anh']); ?>" class="w-12 h-12 object-cover rounded border">
                                    <?php else: ?>
                                        <span class="text-gray-400">No img</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 font-bold text-blue-700">
                                    <?php echo htmlspecialchars($row['ten']); ?>
                                </td>
                                <td class="px-5 py-4 text-gray-500 italic">
                                    <?php echo htmlspecialchars($row['moTa']); ?>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex justify-center gap-3">
                                        <a href="category-form.php?id=<?php echo $row['danhmuc_id']; ?>" class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="danhmuc.php?delete=<?php echo $row['danhmuc_id']; ?>"
                                            class="text-red-500 hover:text-red-700"
                                            onclick="return confirm('Xóa danh mục này?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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