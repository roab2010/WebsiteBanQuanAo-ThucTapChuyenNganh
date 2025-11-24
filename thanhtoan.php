<?php
session_start();
include 'config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Lấy thông tin giỏ hàng để tính toán và hiển thị
$sql_cart = "SELECT gh.*, sp.ten, sp.gia, sp.hinhAnh 
             FROM GIO_HANG gh
             JOIN SAN_PHAM sp ON gh.sanpham_id = sp.sanpham_id
             WHERE gh.nguoi_id = $user_id";
$result_cart = mysqli_query($conn, $sql_cart);

// Kiểm tra giỏ hàng có trống không
if (mysqli_num_rows($result_cart) == 0) {
    $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Giỏ hàng trống! Vui lòng chọn sản phẩm.'];
    header("Location: index.php");
    exit();
}

// Đưa dữ liệu giỏ hàng vào mảng để dùng nhiều lần (vừa tính tiền, vừa hiển thị, vừa lưu DB)
$cart_items = [];
$total_money = 0;
while ($row = mysqli_fetch_assoc($result_cart)) {
    $cart_items[] = $row;
    $total_money += $row['gia'] * $row['soLuong'];
}

// 3. Lấy thông tin người dùng để điền sẵn vào form (Tiện lợi)
$sql_user = "SELECT * FROM NGUOI_DUNG WHERE nguoi_id = $user_id";
$result_user = mysqli_query($conn, $sql_user);
$user_info = mysqli_fetch_assoc($result_user);


// --- PHẦN XỬ LÝ KHI BẤM NÚT ĐẶT HÀNG (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_dat_hang'])) {
    $hoTen = mysqli_real_escape_string($conn, $_POST['hoTen']);
    $sdt = mysqli_real_escape_string($conn, $_POST['sdt']);
    $diaChi = mysqli_real_escape_string($conn, $_POST['diaChi']);
    $ghiChu = mysqli_real_escape_string($conn, $_POST['ghiChu'] ?? '');
    $phuongThuc = $_POST['payment_method']; // 'COD' hoặc 'Banking'

    // 1. Tạo đơn hàng trong Database (Trạng thái: Chờ xử lý)
    $sql_order = "INSERT INTO DON_HANG (nguoi_id, hoTenNguoiNhan, sdtNguoiNhan, diaChiGiaoHang, tongTien, phuongThucTT, trangThaiDH) 
                  VALUES ($user_id, '$hoTen', '$sdt', '$diaChi', $total_money, '$phuongThuc', 'Cho xu ly')";

    if (mysqli_query($conn, $sql_order)) {
        $donhang_id = mysqli_insert_id($conn);

        // 2. Lưu chi tiết đơn hàng
        foreach ($cart_items as $item) {
            $sp_id = $item['sanpham_id'];
            $sl = $item['soLuong'];
            $size = $item['size'];
            $gia = $item['gia'];
            $sql_detail = "INSERT INTO CHI_TIET_DON_HANG (donhang_id, sanpham_id, soLuong, size, donGia) 
                           VALUES ($donhang_id, $sp_id, $sl, '$size', $gia)";
            mysqli_query($conn, $sql_detail);
        }

        // 3. Xóa giỏ hàng
        $sql_clear_cart = "DELETE FROM GIO_HANG WHERE nguoi_id = $user_id";
        mysqli_query($conn, $sql_clear_cart);

        // === 4. PHÂN LOẠI THANH TOÁN ===
        if ($phuongThuc == 'Banking') {
            // --- TÍCH HỢP MOMO API TẠI ĐÂY ---
            include 'config/momo_config.php';

            // Cấu hình thông tin gửi đi
            $orderInfo = "Thanh toan don hang #" . $donhang_id;
            $amount = (string)$total_money;
            $orderId = time() . "_" . $donhang_id; // Mã giao dịch duy nhất (Time + ID)
            $requestId = time() . "";
            $extraData = "";

            // Cấu hình đường dẫn trả về (QUAN TRỌNG: Sửa đúng đường dẫn Localhost của bạn)
            // Ví dụ folder của bạn là 'chuyennganh'
            $returnUrl = "http://localhost/chuyennganh/xuly_momo.php";
            $notifyUrl = "http://localhost/chuyennganh/xuly_momo.php"; // Localhost thì cái này ko chạy ngầm được, nhưng cứ khai báo

            // Tạo chữ ký bảo mật (Signature)
            $rawHash = "accessKey=" . $accessKey .
                "&amount=" . $amount .
                "&extraData=" . $extraData .
                "&ipnUrl=" . $notifyUrl .
                "&orderId=" . $orderId .
                "&orderInfo=" . $orderInfo .
                "&partnerCode=" . $partnerCode .
                "&redirectUrl=" . $returnUrl .
                "&requestId=" . $requestId .
                "&requestType=payWithATM";

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            // Đóng gói dữ liệu JSON
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

            // Gửi dữ liệu sang MoMo bằng cURL
            $ch = curl_init($momo_endpoint);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($data))
                )
            );
            $result = curl_exec($ch);
            curl_close($ch);

            $jsonResult = json_decode($result, true);

            // Kiểm tra kết quả trả về
            if (isset($jsonResult['payUrl'])) {
                // Thành công -> Chuyển hướng sang trang MoMo
                header("Location: " . $jsonResult['payUrl']);
                exit();
            } else {
                // Thất bại -> Báo lỗi
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Lỗi kết nối MoMo: ' . ($jsonResult['message'] ?? 'Không xác định')];
                header("Location: index.php");
                exit();
            }
        } else {
            // --- THANH TOÁN COD (GIỮ NGUYÊN) ---
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Đặt hàng thành công! Mã đơn: #' . $donhang_id];
            header("Location: index.php");
            exit();
        }
    } else {
        echo "<script>alert('Lỗi hệ thống: " . mysqli_error($conn) . "');</script>";
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
                        <label class="block font-medium mb-2">Họ và tên người nhận</label>
                        <input type="text" name="hoTen" class="w-full border border-gray-300 px-4 py-3 rounded focus:outline-none focus:border-black"
                            value="<?php echo htmlspecialchars($user_info['ten']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium mb-2">Số điện thoại</label>
                        <input type="text" name="sdt" class="w-full border border-gray-300 px-4 py-3 rounded focus:outline-none focus:border-black"
                            value="<?php echo htmlspecialchars($user_info['sdt'] ?? ''); ?>" required placeholder="Ví dụ: 0987654321">
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium mb-2">Địa chỉ giao hàng</label>
                        <input type="text" name="diaChi" class="w-full border border-gray-300 px-4 py-3 rounded focus:outline-none focus:border-black"
                            value="<?php echo htmlspecialchars($user_info['diaChi'] ?? ''); ?>" required placeholder="Số nhà, tên đường, phường/xã...">
                    </div>

                    <div class="mb-6">
                        <label class="block font-medium mb-2">Ghi chú đơn hàng (Tùy chọn)</label>
                        <textarea name="ghiChu" class="w-full border border-gray-300 px-4 py-3 rounded focus:outline-none focus:border-black" rows="2" placeholder="Ví dụ: Giao hàng giờ hành chính..."></textarea>
                    </div>

                    <h3 class="font-bold text-xl mb-6 border-b pb-2 mt-8">2. Phương thức thanh toán</h3>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" name="payment_method" value="COD" class="w-5 h-5 accent-black" checked>
                            <span class="ml-3 font-medium">Thanh toán khi nhận hàng (COD)</span>
                        </label>

                        <label class="flex items-center p-4 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 transition">
                            <input type="radio" name="payment_method" value="Banking" class="w-5 h-5 accent-black">
                            <div class="ml-3">
                                <span class="block font-medium">Chuyển khoản ngân hàng (QR Code)</span>
                                <span class="text-sm text-gray-500">MBank - 0397789902 - LAM QUOC BAO</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-5/12">
                <div class="bg-gray-50 p-6 rounded-lg shadow-sm border border-gray-200 sticky top-24">
                    <h3 class="font-bold text-xl mb-6 border-b pb-2">Đơn hàng của bạn</h3>

                    <div class="space-y-4 mb-6 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar ">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="flex gap-4 items-center">
                                <div class="relative">
                                    <img src="<?php echo htmlspecialchars($item['hinhAnh']); ?>" class="w-16 h-16 object-cover rounded border bg-white">
                                    <span class="absolute -top-2 -right-2 bg-gray-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                                        <?php echo $item['soLuong']; ?>
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-sm truncate w-40"><?php echo htmlspecialchars($item['ten']); ?></h4>
                                    <p class="text-xs text-gray-500">Size: <?php echo $item['size']; ?></p>
                                </div>
                                <div class="font-bold text-sm">
                                    <?php echo number_format($item['gia'] * $item['soLuong'], 0, ',', '.'); ?>₫
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="border-gray-300 my-4">

                    <div class="flex justify-between mb-2 text-gray-600">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($total_money, 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="flex justify-between mb-4 text-gray-600">
                        <span>Phí vận chuyển:</span>
                        <span class="text-green-600 font-medium">Miễn phí</span>
                    </div>

                    <hr class="border-gray-300 my-4">

                    <div class="flex justify-between items-center mb-6">
                        <span class="text-lg font-bold">Tổng cộng:</span>
                        <span class="text-2xl font-bold text-red-600"><?php echo number_format($total_money, 0, ',', '.'); ?>₫</span>
                    </div>

                    <button type="submit" name="btn_dat_hang" class="w-full bg-black text-white py-4 rounded font-bold hover:bg-red-600 transition text-lg uppercase tracking-wider shadow-lg">
                        ĐẶT HÀNG NGAY
                    </button>

                    <div class="mt-4 text-center text-sm">
                        <a href="giohang.php" class="text-gray-500 hover:underline hover:text-black">Quay lại giỏ hàng</a>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<?php include './includes/footer.php'; ?>