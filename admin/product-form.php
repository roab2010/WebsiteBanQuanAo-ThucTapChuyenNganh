<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Khởi tạo biến mặc định (để form không bị lỗi khi thêm mới)
$id = '';
$ten = '';
$gia = '';
$danhmuc_id = '';
$soLuongTon = '';
$moTa = '';
$hinhAnh = '';
$is_edit = false; // Cờ đánh dấu đang sửa hay thêm

// NẾU CÓ ID TRÊN URL -> LÀ CHẾ ĐỘ SỬA
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql_edit = "SELECT * FROM SAN_PHAM WHERE sanpham_id = $id";
    $rs_edit = mysqli_query($conn, $sql_edit);
    $product = mysqli_fetch_assoc($rs_edit);

    if ($product) {
        $is_edit = true;
        $ten = $product['ten'];
        $gia = $product['gia'];
        $danhmuc_id = $product['danhmuc_id'];
        $soLuongTon = $product['soLuongTon'];
        $moTa = $product['moTa'];
        $hinhAnh = $product['hinhAnh'];
    }
}

// LẤY DANH SÁCH DANH MỤC ĐỂ HIỆN SELECT BOX
$categories = mysqli_query($conn, "SELECT * FROM DANH_MUC");

// --- XỬ LÝ KHI BẤM LƯU (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = mysqli_real_escape_string($conn, $_POST['ten']);
    $gia = $_POST['gia'];
    $danhmuc_id = $_POST['danhmuc_id'];
    $soLuongTon = $_POST['soLuongTon'];
    $moTa = mysqli_real_escape_string($conn, $_POST['moTa']);

    // Xử lý Upload ảnh
    $target_dir = "../assets/img/"; // Thư mục lưu ảnh thật
    $db_path = $hinhAnh; // Mặc định là ảnh cũ

    // Nếu người dùng có chọn ảnh mới
    if (!empty($_FILES["hinhAnh"]["name"])) {
        $file_name = time() . "_" . basename($_FILES["hinhAnh"]["name"]); // Thêm time để không trùng tên
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["hinhAnh"]["tmp_name"], $target_file)) {
            $db_path = "assets/img/" . $file_name; // Đường dẫn lưu vào DB
        }
    }

    if ($is_edit) {
        // UPDATE
        $sql = "UPDATE SAN_PHAM SET 
                ten='$ten', gia=$gia, danhmuc_id=$danhmuc_id, soLuongTon=$soLuongTon, moTa='$moTa', hinhAnh='$db_path' 
                WHERE sanpham_id=$id";
    } else {
        // INSERT
        $sql = "INSERT INTO SAN_PHAM (ten, gia, danhmuc_id, soLuongTon, moTa, hinhAnh) 
                VALUES ('$ten', $gia, $danhmuc_id, $soLuongTon, '$moTa', '$db_path')";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: sanpham.php");
    } else {
        echo "<script>alert('Lỗi SQL: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?php echo $is_edit ? 'Sửa sản phẩm' : 'Thêm sản phẩm'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-8">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <?php echo $is_edit ? '✏️ Sửa sản phẩm #' . $id : '✨ Thêm sản phẩm mới'; ?>
                    </h2>
                    <a href="sanpham.php" class="text-gray-500 hover:text-black">Quay lại</a>
                </div>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">

                    <div>
                        <label class="block font-bold mb-1">Tên sản phẩm</label>
                        <input type="text" name="ten" value="<?php echo htmlspecialchars($ten); ?>" required
                            class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block font-bold mb-1">Giá (VNĐ)</label>
                            <input type="number" name="gia" value="<?php echo $gia; ?>" required
                                class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Số lượng kho</label>
                            <input type="number" name="soLuongTon" value="<?php echo $soLuongTon; ?>" required
                                class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Danh mục</label>
                        <select name="danhmuc_id" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500">
                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $cat['danhmuc_id']; ?>"
                                    <?php if ($cat['danhmuc_id'] == $danhmuc_id) echo 'selected'; ?>>
                                    <?php echo $cat['ten']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Hình ảnh</label>
                        <?php if ($hinhAnh): ?>
                            <img src="../<?php echo $hinhAnh; ?>" class="h-32 w-auto mb-2 border rounded p-1">
                        <?php endif; ?>
                        <input type="file" name="hinhAnh" class="w-full border p-2 rounded bg-gray-50" <?php echo $is_edit ? '' : 'required'; ?>>
                        <p class="text-xs text-gray-500 mt-1">Chấp nhận file: jpg, png, jpeg</p>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Mô tả chi tiết</label>
                        <textarea name="moTa" rows="5" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500"><?php echo htmlspecialchars($moTa); ?></textarea>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded font-bold shadow transition">
                            <?php echo $is_edit ? 'Cập nhật sản phẩm' : 'Thêm mới ngay'; ?>
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>

</body>

</html>