<?php
session_start();
include 'config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Lấy danh sách đơn hàng (PDO)
// Dùng prepare để an toàn hơn
$sql_orders = "SELECT * FROM DON_HANG WHERE nguoi_id = ? ORDER BY donhang_id DESC";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->execute([$user_id]);

include './includes/header.php';
?>

<div class="container mx-auto px-4 py-12 min-h-screen">
    <h1 class="text-3xl font-bold mb-8 uppercase tracking-wide border-b pb-4">Lịch sử đơn hàng</h1>

    <?php if ($stmt_orders->rowCount() > 0): ?>
        <div class="space-y-8">
            <?php while ($order = $stmt_orders->fetch(PDO::FETCH_ASSOC)):
                $donhang_id = $order['donhang_id'];

                // --- Xử lý màu sắc trạng thái ---
                $status_color = 'bg-gray-100 text-gray-800';
                if ($order['trangThaiDH'] == 'Dang giao') $status_color = 'bg-blue-100 text-blue-800';
                if ($order['trangThaiDH'] == 'Hoan tat') $status_color = 'bg-green-100 text-green-800';
                if ($order['trangThaiDH'] == 'Huy') $status_color = 'bg-red-100 text-red-800';

                // --- Xử lý Logic Thanh Toán ($is_paid) ---
                $is_paid = false;
                $tt_tt = trim($order['trangThaiTT']);
                $pt_tt = trim($order['phuongThucTT']);
                $tt_dh = trim($order['trangThaiDH']);

                // 1. Nếu database ghi đã thanh toán (MoMo/Banking)
                if (stripos($tt_tt, 'Da thanh toan') !== false) {
                    $is_paid = true;
                }
                // 2. HOẶC: Nếu là COD mà đơn đã HOÀN TẤT
                if ($pt_tt == 'COD' && $tt_dh == 'Hoan tat') {
                    $is_paid = true;
                }
            ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                    <div class="bg-gray-50 p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <span class="font-bold text-lg mr-2">Đơn hàng #<?php echo $donhang_id; ?></span>
                            <span class="text-sm text-gray-500">
                                Đặt ngày: <?php echo date('d/m/Y H:i', strtotime($order['ngayTao'])); ?>
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $status_color; ?>">
                                <?php echo $order['trangThaiDH']; ?>
                            </span>

                            <?php if ($is_paid): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-green-100 text-green-800 border border-green-200">
                                    ĐÃ THANH TOÁN
                                </span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-gray-100 text-gray-500 border border-gray-200">
                                    CHƯA THANH TOÁN
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-4">
                        <?php
                        // Lấy chi tiết sản phẩm (PDO)
                        $sql_items = "SELECT ct.*, sp.ten, sp.hinhAnh 
                                      FROM CHI_TIET_DON_HANG ct 
                                      JOIN SAN_PHAM sp ON ct.sanpham_id = sp.sanpham_id 
                                      WHERE ct.donhang_id = ?"; // Dùng dấu ?

                        $stmt_items = $conn->prepare($sql_items);
                        $stmt_items->execute([$donhang_id]); // Truyền ID vào

                        while ($item = $stmt_items->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <div class="flex gap-4 mb-4 last:mb-0 items-center border-b last:border-0 pb-4 last:pb-0 border-gray-100">
                                <img src="<?php echo htmlspecialchars($item['hinhAnh']); ?>" class="w-16 h-16 object-cover rounded border">
                                <div class="flex-1">
                                    <h4 class="font-bold text-sm"><?php echo htmlspecialchars($item['ten']); ?></h4>
                                    <p class="text-xs text-gray-500">
                                        Phân loại: <?php echo $item['size']; ?> | x<?php echo $item['soLuong']; ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="font-medium text-sm">
                                        <?php echo number_format($item['donGia'], 0, ',', '.'); ?>₫
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Phương thức: <strong><?php echo $order['phuongThucTT']; ?></strong>
                        </div>
                        <div class="text-xl font-bold text-red-600">
                            Tổng tiền: <?php echo number_format($order['tongTien'], 0, ',', '.'); ?>₫
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    <?php else: ?>
        <div class="text-center py-16 bg-gray-50 rounded-lg">
            <div class="text-6xl mb-4">📦</div>
            <h2 class="text-xl font-bold mb-2">Bạn chưa có đơn hàng nào</h2>
            <p class="text-gray-500 mb-6">Hãy dạo một vòng và chọn món đồ yêu thích nhé!</p>
            <a href="index.php" class="inline-block bg-black text-white px-8 py-3 rounded font-bold hover:bg-gray-800 transition">
                MUA SẮM NGAY
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include './includes/footer.php'; ?>