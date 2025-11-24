<?php
session_start();
include 'config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Vui lòng đăng nhập để mua hàng!'); window.location.href='dangnhap.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $nguoi_id = $_SESSION['user_id'];
    $sanpham_id = $_POST['sanpham_id'];
    $size = $_POST['size'];
    $soLuong = $_POST['soLuong'];

    // 2. Kiểm tra xem sản phẩm + size này đã có trong giỏ của user chưa
    // (Nếu có rồi thì tăng số lượng, chưa có thì thêm mới)

    // Câu lệnh kiểm tra
    $check_sql = "SELECT * FROM GIO_HANG WHERE nguoi_id = $nguoi_id AND sanpham_id = $sanpham_id AND size = '$size'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        // TRƯỜNG HỢP A: Đã có -> Update số lượng
        $sql = "UPDATE GIO_HANG SET soLuong = soLuong + $soLuong 
                WHERE nguoi_id = $nguoi_id AND sanpham_id = $sanpham_id AND size = '$size'";
    } else {
        // TRƯỜNG HỢP B: Chưa có -> Insert mới
        $sql = "INSERT INTO GIO_HANG (nguoi_id, sanpham_id, size, soLuong) 
                VALUES ($nguoi_id, $sanpham_id, '$size', $soLuong)";
    }

    // 3. Thực thi
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Đã thêm vào giỏ hàng thành công!'); window.location.href='index.php';</script>";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
} else {
    // Nếu ai đó cố truy cập trực tiếp file này
    header("Location: index.php");
}
