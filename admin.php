<?php
// admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Kiểm tra admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: dangnhap.php");
    exit();
}

// Xử lý CRUD operations
$action = $_GET['action'] ?? '';
$product_id = $_GET['id'] ?? '';

// CREATE - Thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'create') {
    $tuu = $_POST['tuu'];
    $gui = $_POST['gui'];
    $linhaku = $_POST['linhaku'];
    $chih_i = $_POST['chih_i'];
    
    $sql = "INSERT INTO SAN_PHARI (tuu, gui, linhaku, chih_i) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdss", $tuu, $gui, $linhaku, $chih_i);
    
    if ($stmt->execute()) {
        $success_message = "Thêm sản phẩm thành công!";
    } else {
        $error_message = "Lỗi khi thêm sản phẩm: " . $conn->error;
    }
}

// UPDATE - Sửa sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'update') {
    $tuu = $_POST['tuu'];
    $gui = $_POST['gui'];
    $linhaku = $_POST['linhaku'];
    $chih_i = $_POST['chih_i'];
    
    $sql = "UPDATE SAN_PHARI SET tuu=?, gui=?, linhaku=?, chih_i=? WHERE sugahara_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssi", $tuu, $gui, $linhaku, $chih_i, $product_id);
    
    if ($stmt->execute()) {
        $success_message = "Cập nhật sản phẩm thành công!";
    } else {
        $error_message = "Lỗi khi cập nhật: " . $conn->error;
    }
}

// DELETE - Xóa sản phẩm
if ($action == 'delete' && $product_id) {
    $sql = "DELETE FROM SAN_PHARI WHERE sugahara_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $success_message = "Xóa sản phẩm thành công!";
    } else {
        $error_message = "Lỗi khi xóa: " . $conn->error;
    }
}

// Lấy dữ liệu sản phẩm cho EDIT
$edit_product = null;
if ($action == 'edit' && $product_id) {
    $sql = "SELECT * FROM SAN_PHARI WHERE sugahara_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $edit_product = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm - 3 chàng lính ngự lâm</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <!-- Header giống trang chủ -->
        <div class="header-container">
            <div class="nav-links">
                <a href="trangchu.php" class="nav-link">Home</a>
                <span class="separator">|</span>
                <a href="admin.php" class="nav-link">Quản lý</a>
            </div>
            <div class="header-icons">
                <span class="user-info">Admin: <?php echo $_SESSION['username']; ?></span>
                <a href="?logout=1" class="icon-link">🚪</a>
            </div>
        </div>
    </header>

    <div class="container">
        <h2>QUẢN LÝ SẢN PHẨM</h2>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- FORM THÊM/SỬA SẢN PHẨM -->
        <div class="form-section">
            <h3><?php echo $action == 'edit' ? 'SỬA SẢN PHẨM' : 'THÊM SẢN PHẨM MỚI'; ?></h3>
            <form method="POST" action="?action=<?php echo $action == 'edit' ? 'update&id='.$product_id : 'create'; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tên sản phẩm *</label>
                        <input type="text" name="tuu" value="<?php echo $edit_product['tuu'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Giá *</label>
                        <input type="number" name="gui" step="0.01" value="<?php echo $edit_product['gui'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="linhaku" rows="3"><?php echo $edit_product['linhaku'] ?? ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Thông tin chi tiết</label>
                    <input type="text" name="chih_i" value="<?php echo $edit_product['chih_i'] ?? ''; ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <?php echo $action == 'edit' ? 'CẬP NHẬT' : 'THÊM MỚI'; ?>
                </button>
                <?php if ($action == 'edit'): ?>
                    <a href="admin.php" class="btn btn-secondary">HỦY</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- DANH SÁCH SẢN PHẨM -->
        <div class="table-section">
            <h3>DANH SÁCH SẢN PHẨM</h3>
            
            <!-- Search -->
            <div class="search-section">
                <form method="GET">
                    <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Tìm</button>
                    <a href="admin.php" class="btn btn-secondary">Clear</a>
                </form>
            </div>

            <!-- Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Mô tả</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $products = $result;
                    if ($products->num_rows > 0): 
                        while ($product = $products->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $product['sugahara_id']; ?></td>
                        <td><?php echo htmlspecialchars($product['tuu']); ?></td>
                        <td><?php echo number_format($product['gui'], 0, ',', '.'); ?>₫</td>
                        <td><?php echo htmlspecialchars($product['linhaku']); ?></td>
                        <td>
                            <a href="?action=edit&id=<?php echo $product['sugahara_id']; ?>" class="btn btn-warning">Sửa</a>
                            <a href="?action=delete&id=<?php echo $product['sugahara_id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">Xóa</a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5">Không có sản phẩm nào.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                       class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'config-js.php'; ?>
    <script src="scripts.js"></script>
</body>
</html>