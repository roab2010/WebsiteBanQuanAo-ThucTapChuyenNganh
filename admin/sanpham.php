<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// --- XỬ LÝ XÓA SẢN PHẨM ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // (Tùy chọn) Xóa ảnh cũ khỏi thư mục nếu cần
    // $img = mysqli_fetch_assoc(mysqli_query($conn, "SELECT hinhAnh FROM SAN_PHAM WHERE sanpham_id=$id"));
    // if($img && file_exists("../".$img['hinhAnh'])) unlink("../".$img['hinhAnh']);

    $sql_delete = "DELETE FROM SAN_PHAM WHERE sanpham_id = $id";
    if (mysqli_query($conn, $sql_delete)) {
        echo "<script>alert('Đã xóa sản phẩm!'); window.location.href='sanpham.php';</script>";
    } else {
        echo "<script>alert('Lỗi: Không thể xóa sản phẩm đang có trong đơn hàng!'); window.location.href='sanpham.php';</script>";
    }
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
</head>

<body class="bg-gray-100">

    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold">Danh sách Sản phẩm</h2>
                <a href="product-form.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-bold shadow flex items-center transition">
                    <i class="fas fa-plus mr-2"></i> Thêm sản phẩm
                </a>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-800 text-white text-left">
                            <th class="px-5 py-3 uppercase text-xs font-semibold">ID</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Hình ảnh</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Tên sản phẩm</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Danh mục</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Giá</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold">Kho</th>
                            <th class="px-5 py-3 uppercase text-xs font-semibold text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50 text-sm">
                                <td class="px-5 py-4">#<?php echo $row['sanpham_id']; ?></td>
                                <td class="px-5 py-4">
                                    <img src="../<?php echo htmlspecialchars($row['hinhAnh']); ?>" class="w-12 h-12 object-cover rounded border">
                                </td>
                                <td class="px-5 py-4 font-bold text-gray-700">
                                    <?php echo htmlspecialchars($row['ten']); ?>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded">
                                        <?php echo htmlspecialchars($row['ten_danhmuc']); ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-bold text-red-600">
                                    <?php echo number_format($row['gia'], 0, ',', '.'); ?>₫
                                </td>
                                <td class="px-5 py-4"><?php echo $row['soLuongTon']; ?></td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="product-form.php?id=<?php echo $row['sanpham_id']; ?>" class="text-blue-500 hover:text-blue-700" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="sanpham.php?delete=<?php echo $row['sanpham_id']; ?>"
                                            class="text-red-500 hover:text-red-700 ml-2"
                                            onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')" title="Xóa">
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