<?php
session_start();
include 'config/database.php';

// MoMo trả kết quả về qua phương thức GET trên URL
if (isset($_GET['resultCode'])) {
    $resultCode = $_GET['resultCode']; // 0 là thành công
    $orderId = $_GET['orderId']; // Mã đơn mình gửi đi lúc nãy (Dạng: time_id)

    // Tách lấy ID đơn hàng thật từ chuỗi orderId (Ví dụ: 173000_105 -> lấy 105)
    $parts = explode('_', $orderId);
    $donhang_id = isset($parts[1]) ? intval($parts[1]) : 0;

    if ($resultCode == '0') {
        // === THÀNH CÔNG ===
        // Cập nhật trạng thái đơn hàng (PDO)
        if ($donhang_id > 0) {
            $sql = "UPDATE DON_HANG SET trangThaiTT = 'Da thanh toan MoMo' WHERE donhang_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$donhang_id]);
        }

        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Thanh toán MoMo thành công! Đơn hàng #' . $donhang_id . ' đã được xác nhận.'
        ];
        header("Location: index.php");
    } else {
        // === THẤT BẠI / HỦY ===
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Giao dịch MoMo thất bại hoặc bị hủy.'
        ];
        header("Location: index.php");
    }
} else {
    // Truy cập trực tiếp mà không có dữ liệu
    header("Location: index.php");
}
?>