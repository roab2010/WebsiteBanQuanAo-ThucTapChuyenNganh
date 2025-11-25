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
$noiDung = mysqli_real_escape_string($conn, $_POST['noiDung']);

// Kiểm tra xem đã đánh giá chưa (Chống spam F5)
$check = mysqli_query($conn, "SELECT * FROM DANH_GIA WHERE nguoi_id = $user_id AND sanpham_id = $sanpham_id");

if (mysqli_num_rows($check) == 0) {
    $sql = "INSERT INTO DANH_GIA (nguoi_id, sanpham_id, soSao, noiDung) 
            VALUES ($user_id, $sanpham_id, $soSao, '$noiDung')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Cảm ơn bạn đã đánh giá! ❤️'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi: ' . mysqli_error($conn)];
    }
} else {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Bạn đã đánh giá sản phẩm này rồi!'];
}

header("Location: donhang.php");
exit();
