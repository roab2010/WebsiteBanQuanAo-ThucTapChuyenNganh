<?php
session_start();
include 'config/database.php';


if (isset($_GET['resultCode'])) {
    $resultCode = $_GET['resultCode']; 
    $orderId = $_GET['orderId']; 


    $parts = explode('_', $orderId);
    $donhang_id = isset($parts[1]) ? intval($parts[1]) : 0;

    if ($resultCode == '0') {
  
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
     
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Giao dịch MoMo thất bại hoặc bị hủy.'
        ];
        header("Location: index.php");
    }
} else {
  
    header("Location: index.php");
}
?>