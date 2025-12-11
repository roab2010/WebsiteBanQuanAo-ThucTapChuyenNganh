<?php
session_start();
include 'config/database.php';


if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sanpham_id = intval($_POST['sanpham_id']);
$soSao = intval($_POST['soSao']);
$noiDung = $_POST['noiDung']; 

try {
    
    $check_sql = "SELECT danhgia_id FROM DANH_GIA WHERE nguoi_id = ? AND sanpham_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$user_id, $sanpham_id]);

    if ($check_stmt->rowCount() == 0) {
        
        $sql = "INSERT INTO DANH_GIA (nguoi_id, sanpham_id, soSao, noiDung) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute([$user_id, $sanpham_id, $soSao, $noiDung])) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Cảm ơn bạn đã đánh giá! ❤️'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi khi lưu đánh giá.'];
        }
    } else {
        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Bạn đã đánh giá sản phẩm này rồi!'];
    }
} catch (PDOException $e) {
    
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
}

header("Location: donhang.php");
exit();
