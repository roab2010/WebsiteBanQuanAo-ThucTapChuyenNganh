<?php
session_start();
include 'config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Lấy thông tin giỏ hàng (PDO)
$stmt_cart = $conn->prepare("
    SELECT gh.*, sp.ten, sp.gia, sp.hinhAnh 
    FROM GIO_HANG gh
    JOIN SAN_PHAM sp ON gh.sanpham_id = sp.sanpham_id
    WHERE gh.nguoi_id = ?
");
$stmt_cart->execute([$user_id]);

if ($stmt_cart->rowCount() == 0) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Giỏ hàng trống!'];
    header("Location: index.php");
    exit();
}

$cart_items = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);
$total_money = 0;
foreach ($cart_items as $item) {
    $total_money += $item['gia'] * $item['soLuong'];
}

// 3. Lấy thông tin user (PDO)
$stmt_user = $conn->prepare("SELECT * FROM NGUOI_DUNG WHERE nguoi_id = ?");
$stmt_user->execute([$user_id]);
$user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);


// --- XỬ LÝ ĐẶT HÀNG ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_dat_hang'])) {
    $hoTen = $_POST['hoTen'];
    $sdt = $_POST['sdt'];
    $diaChi = $_POST['diaChi'];
    $ghiChu = $_POST['ghiChu'] ?? '';
    $phuongThuc = $_POST['payment_method']; // COD hoặc Banking

    try {
        $conn->beginTransaction();

        // 1. Tạo đơn hàng
        $stmt_order = $conn->prepare("
            INSERT INTO DON_HANG (nguoi_id, hoTenNguoiNhan, sdtNguoiNhan, diaChiGiaoHang, tongTien, phuongThucTT, trangThaiDH) 
            VALUES (?, ?, ?, ?, ?, ?, 'Cho xu ly')
        ");
        $stmt_order->execute([$user_id, $hoTen, $sdt, $diaChi, $total_money, $phuongThuc]);
        $donhang_id = $conn->lastInsertId();

        // 2. Lưu chi tiết & Trừ kho
        $stmt_detail = $conn->prepare("INSERT INTO CHI_TIET_DON_HANG (donhang_id, sanpham_id, soLuong, size, donGia) VALUES (?, ?, ?, ?, ?)");
        $stmt_stock = $conn->prepare("UPDATE SAN_PHAM SET soLuongTon = soLuongTon - ? WHERE sanpham_id = ?");

        foreach ($cart_items as $item) {
            $stmt_detail->execute([$donhang_id, $item['sanpham_id'], $item['soLuong'], $item['size'], $item['gia']]);
            $stmt_stock->execute([$item['soLuong'], $item['sanpham_id']]);
        }

        // 3. Xóa giỏ hàng
        $stmt_del = $conn->prepare("DELETE FROM GIO_HANG WHERE nguoi_id = ?");
        $stmt_del->execute([$user_id]);

        $conn->commit();

        // === 4. XỬ LÝ THANH TOÁN ===

        if ($phuongThuc == 'Banking') {
            // --- TÍCH HỢP MOMO (Code cũ của bạn) ---
            include 'config/momo_config.php';

            $orderInfo = "Thanh toan don hang #" . $donhang_id;
            $amount = (string)$total_money;
            $orderId = time() . "_" . $donhang_id;
            $requestId = time() . "";
            $extraData = "";

            // SỬA LẠI ĐƯỜNG DẪN TRẢ VỀ CHO ĐÚNG LOCALHOST CỦA BẠN
            // (Bạn tự thay đúng link web bạn đang chạy nhé)
            $returnUrl = "http://localhost/chuyennganh/xuly_momo.php";
            $notifyUrl = "http://localhost/chuyennganh/xuly_momo.php";

            $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $notifyUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $returnUrl . "&requestId=" . $requestId . "&requestType=payWithATM";

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            $data = array(
                'partnerCode' => $partnerCode,
                'partnerName' => "BCB Store",
                "storeId" => "MomoTestStore",
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $returnUrl,
                'ipnUrl' => $notifyUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => 'payWithATM',
                'signature' => $signature
            );

            $ch = curl_init($momo_endpoint);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen(json_encode($data))));
            $result = curl_exec($ch);
            curl_close($ch);

            $jsonResult = json_decode($result, true);

            if (isset($jsonResult['payUrl'])) {
                header("Location: " . $jsonResult['payUrl']);
                exit();
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi MoMo: ' . ($jsonResult['message'] ?? 'Unknown error')];
                header("Location: index.php");
                exit();
            }
        } else {
            // --- COD ---
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Đặt hàng thành công! Mã đơn: #' . $donhang_id];
            header("Location: index.php");
            exit();
        }
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Lỗi hệ thống: " . $e->getMessage() . "');</script>";
    }
}

include './includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-8 uppercase tracking-wide text-center">Thanh Toán</h1>

    <form method="POST" action="">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-7/12">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="font-bold text-xl mb-6 border-b pb-2">1. Thông tin giao hàng</h3>

                    <div class="mb-4">
                        <label class="block font-medium mb-2">Họ tên</label>
                        <input type="text" name="hoTen" class="w-full border border-gray-300 px-4 py-3 rounded" value="<?php echo htmlspecialchars($user_info['ten']); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium mb-2">Số điện thoại</label>
                        <input type="text" name="sdt" class="w-full border border-gray-300 px-4 py-3 rounded" value="<?php echo htmlspecialchars($user_info['sdt'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium mb-2">Địa chỉ</label>
                        <input type="text" name="diaChi" class="w-full border border-gray-300 px-4 py-3 rounded" value="<?php echo htmlspecialchars($user_info['diaChi'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-6">
                        <label class="block font-medium mb-2">Ghi chú</label>
                        <textarea name="ghiChu" class="w-full border border-gray-300 px-4 py-3 rounded" rows="2"></textarea>
                    </div>

                    <h3 class="font-bold text-xl mb-6 border-b pb-2 mt-8">2. Phương thức thanh toán</h3>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border border-gray-200 rounded cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="COD" class="w-5 h-5 accent-black" checked>
                            <span class="ml-3 font-medium">Thanh toán khi nhận hàng (COD)</span>
                        </label>
                        <label class="flex items-center p-4 border border-gray-200 rounded cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="Banking" class="w-5 h-5 accent-black">
                            <div class="ml-3">
                                <span class="block font-medium">Thanh toán Online (MoMo/ATM)</span>
                                <span class="text-sm text-gray-500">Thẻ ATM nội địa / Ví MoMo</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-5/12">
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200 sticky top-24">
                    <h3 class="font-bold text-xl mb-6 border-b pb-2">Đơn hàng</h3>
                    <div class="space-y-4 mb-6 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="flex gap-4 items-center">
                                <img src="<?php echo htmlspecialchars($item['hinhAnh']); ?>" class="w-16 h-16 object-cover rounded border bg-white">
                                <div class="flex-1">
                                    <h4 class="font-medium text-sm"><?php echo htmlspecialchars($item['ten']); ?></h4>
                                    <p class="text-xs text-gray-500">Size: <?php echo $item['size']; ?> x <?php echo $item['soLuong']; ?></p>
                                </div>
                                <div class="font-bold text-sm"><?php echo number_format($item['gia'] * $item['soLuong'], 0, ',', '.'); ?>₫</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr class="border-gray-300 my-4">
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-lg font-bold">Tổng cộng:</span>
                        <span class="text-2xl font-bold text-red-600"><?php echo number_format($total_money, 0, ',', '.'); ?>₫</span>
                    </div>
                    <button type="submit" name="btn_dat_hang" class="w-full bg-black text-white text-center py-3 font-bold rounded hover:bg-red-600 transition uppercase shadow-lg">
                        ĐẶT HÀNG NGAY
                    </button>
                    <div class="mt-4 text-center text-sm"><a href="giohang.php" class="text-gray-500 hover:underline">Quay lại giỏ hàng</a></div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include './includes/footer.php'; ?>