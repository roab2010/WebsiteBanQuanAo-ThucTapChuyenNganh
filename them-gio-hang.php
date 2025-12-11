<?php
session_start();
include 'config/database.php';


if (!isset($_SESSION['user_id'])) {
 
    $_SESSION['alert'] = [
        'type' => 'warning',
        'message' => 'Vui lòng đăng nhập để mua hàng! 🛒'
    ];

  
    header("Location: dangnhap.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $nguoi_id = $_SESSION['user_id'];
    $sanpham_id = $_POST['sanpham_id'];
    $size = $_POST['size'];
    $soLuong = $_POST['soLuong'];

    try {
      
        $check_sql = "SELECT giohang_id FROM GIO_HANG WHERE nguoi_id = ? AND sanpham_id = ? AND size = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$nguoi_id, $sanpham_id, $size]);

        if ($check_stmt->rowCount() > 0) {
      
            $sql = "UPDATE GIO_HANG SET soLuong = soLuong + ? 
                    WHERE nguoi_id = ? AND sanpham_id = ? AND size = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$soLuong, $nguoi_id, $sanpham_id, $size]);
        } else {
         
            $sql = "INSERT INTO GIO_HANG (nguoi_id, sanpham_id, size, soLuong) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nguoi_id, $sanpham_id, $size, $soLuong]);
        }

      
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Đã thêm sản phẩm vào giỏ hàng! 🛒'
        ];
    } catch (PDOException $e) {
     
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ];
    }

  
    $back_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $back_url");
    exit();
} else {
  
    header("Location: index.php");
    exit();
}
