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

    if (mysqli_query($conn, $sql)) {
        // 1. Tạo thông báo thành công
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Đã thêm sản phẩm vào giỏ hàng! 🛒'
        ];
    } else {
        // 2. Tạo thông báo lỗi
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Lỗi: ' . mysqli_error($conn)
        ];
    }

    // 3. Quay trở lại trang trước đó (Trang chủ hoặc Trang chi tiết)
    // $_SERVER['HTTP_REFERER'] là đường dẫn của trang vừa bấm nút
    $back_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $back_url");
    exit();
}
