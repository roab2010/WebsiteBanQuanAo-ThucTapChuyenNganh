<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = '';
$ten = '';
$moTa = '';
$anh = '';
$is_edit = false;

// Chế độ SỬA (PDO)
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM DANH_MUC WHERE danhmuc_id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cat) {
        $is_edit = true;
        $ten = $cat['ten'];
        $moTa = $cat['moTa'];
        $anh = $cat['anh'];
    }
}

// XỬ LÝ POST (PDO)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = $_POST['ten'];
    $moTa = $_POST['moTa'];

    // Upload ảnh
    $db_path = $anh;
    if (!empty($_FILES["anh"]["name"])) {
        $target_dir = "../assets/img/";
        $file_name = time() . "_cat_" . basename($_FILES["anh"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["anh"]["tmp_name"], $target_file)) {
            $db_path = "assets/img/" . $file_name;
        }
    }

    try {
        if ($is_edit) {
            // UPDATE
            $sql = "UPDATE DANH_MUC SET ten = ?, moTa = ?, anh = ? WHERE danhmuc_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$ten, $moTa, $db_path, $id]);
        } else {
            // INSERT
            $sql = "INSERT INTO DANH_MUC (ten, moTa, anh) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$ten, $moTa, $db_path]);
        }

        header("Location: danhmuc.php");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Lỗi: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?php echo $is_edit ? 'Sửa danh mục' : 'Thêm danh mục'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logoicon.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="flex h-screen">
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-xl mx-auto bg-white shadow-lg rounded-lg p-8">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <?php echo $is_edit ? '✏️ Sửa danh mục' : '✨ Thêm danh mục mới'; ?>
                    </h2>
                    <a href="danhmuc.php" class="text-gray-500 hover:text-black">Quay lại</a>
                </div>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">

                    <div>
                        <label class="block font-bold mb-1">Tên danh mục</label>
                        <input type="text" name="ten" value="<?php echo htmlspecialchars($ten); ?>" required
                            class="w-full border p-2 rounded focus:outline-none focus:border-blue-500"
                            placeholder="Ví dụ: ACCESSORIES">
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Mô tả ngắn</label>
                        <input type="text" name="moTa" value="<?php echo htmlspecialchars($moTa); ?>"
                            class="w-full border p-2 rounded focus:outline-none focus:border-blue-500"
                            placeholder="Ví dụ: Phụ kiện thời trang">
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Ảnh đại diện (Banner)</label>
                        <?php if ($anh): ?>
                            <img src="../<?php echo $anh; ?>" class="h-20 w-auto mb-2 border rounded p-1">
                        <?php endif; ?>
                        <input type="file" name="anh" class="w-full border p-2 rounded bg-gray-50">
                    </div>

                    <div class="text-right">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded font-bold shadow transition">
                            <?php echo $is_edit ? 'Cập nhật' : 'Thêm mới'; ?>
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>

</body>

</html>