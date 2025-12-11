<?php
session_start();
include 'config/database.php';

// 1. Kiểm tra đăng nhập (Sửa lại dùng Session Alert)
if (!isset($_SESSION['user_id'])) {
    // Gán thông báo màu vàng (Warning)
    $_SESSION['alert'] = [
        'type' => 'warning',
        'message' => 'Vui lòng đăng nhập để mua hàng! 🛒'
    ];

    // Chuyển hướng ngay lập tức sang trang đăng nhập
    header("Location: dangnhap.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $nguoi_id = $_SESSION['user_id'];
    $sanpham_id = $_POST['sanpham_id'];
    $size = $_POST['size'];
    $soLuong = $_POST['soLuong'];

    try {
        // 2. Kiểm tra xem sản phẩm + size này đã có trong giỏ chưa (PDO)
        $check_sql = "SELECT giohang_id FROM GIO_HANG WHERE nguoi_id = ? AND sanpham_id = ? AND size = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$nguoi_id, $sanpham_id, $size]);

        if ($check_stmt->rowCount() > 0) {
            // TRƯỜNG HỢP A: Đã có -> Update số lượng (PDO)
            $sql = "UPDATE GIO_HANG SET soLuong = soLuong + ? 
                    WHERE nguoi_id = ? AND sanpham_id = ? AND size = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$soLuong, $nguoi_id, $sanpham_id, $size]);
        } else {
            // TRƯỜNG HỢP B: Chưa có -> Insert mới (PDO)
            $sql = "INSERT INTO GIO_HANG (nguoi_id, sanpham_id, size, soLuong) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nguoi_id, $sanpham_id, $size, $soLuong]);
        }

        // 1. Tạo thông báo thành công
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Đã thêm sản phẩm vào giỏ hàng! 🛒'
        ];
    } catch (PDOException $e) {
        // 2. Tạo thông báo lỗi nếu có
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ];
    }

    // 3. Quay trở lại trang trước đó
    $back_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $back_url");
    exit();
} else {
    // Nếu truy cập trực tiếp file này thì đá về trang chủ
    header("Location: index.php");
    exit();
}
