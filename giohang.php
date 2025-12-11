<?php
session_start();
include 'config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý đăng xuất (Nếu có link logout ở trang này)
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: dangnhap.php");
    exit();
}

// 2. Xử lý XÓA sản phẩm khỏi giỏ (PDO)
if (isset($_GET['delete'])) {
    $cart_id = intval($_GET['delete']);

    // Dùng PDO Prepared Statement để xóa
    $sql_delete = "DELETE FROM GIO_HANG WHERE giohang_id = ? AND nguoi_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);

    if ($stmt_delete->execute([$cart_id, $user_id])) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.'
        ];
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'message' => 'Lỗi khi xóa sản phẩm.'
        ];
    }

    header("Location: giohang.php"); // Load lại trang
    exit();
}

// 3. Lấy danh sách sản phẩm trong giỏ (PDO)
$sql = "SELECT gh.*, sp.ten, sp.gia, sp.hinhAnh 
        FROM GIO_HANG gh
        JOIN SAN_PHAM sp ON gh.sanpham_id = sp.sanpham_id
        WHERE gh.nguoi_id = ?
        ORDER BY gh.ngayThem DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);

// Biến tính tổng tiền
$total_money = 0;

include './includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-8 uppercase tracking-wide border-b pb-4">Giỏ hàng của bạn</h1>

    <?php if ($stmt->rowCount() > 0): ?>
        <div class="flex flex-col lg:flex-row gap-8">

            <div class="w-full lg:w-2/3">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="hidden md:grid grid-cols-12 gap-4 p-4 bg-gray-50 border-b font-bold text-sm text-gray-600">
                        <div class="col-span-6">Sản phẩm</div>
                        <div class="col-span-2 text-center">Đơn giá</div>
                        <div class="col-span-2 text-center">Số lượng</div>
                        <div class="col-span-2 text-center">Thành tiền</div>
                    </div>

                    <?php while ($item = $stmt->fetch(PDO::FETCH_ASSOC)):
                        $subtotal = $item['gia'] * $item['soLuong'];
                        $total_money += $subtotal;
                    ?>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 p-4 items-center border-b last:border-0 hover:bg-gray-50 transition">

                            <div class="col-span-12 md:col-span-6 flex items-center gap-4">
                                <a href="#"
                                    onclick="event.preventDefault(); showConfirmModal('giohang.php?delete=<?php echo $item['giohang_id']; ?>')"
                                    class="md:hidden text-gray-400 hover:text-red-500 text-xl">
                                    &times;
                                </a>

                                <img src="<?php echo htmlspecialchars($item['hinhAnh']); ?>" class="w-20 h-24 object-cover rounded border">

                                <div>
                                    <h3 class="font-bold text-sm md:text-base">
                                        <a href="chitiet.php?id=<?php echo $item['sanpham_id']; ?>" class="hover:underline">
                                            <?php echo htmlspecialchars($item['ten']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">Size: <span class="font-bold text-black"><?php echo $item['size']; ?></span></p>

                                    <button type="button"
                                        onclick="showConfirmModal('giohang.php?delete=<?php echo $item['giohang_id']; ?>')"
                                        class="hidden md:inline-block text-xs text-red-500 hover:underline mt-2">
                                        Xóa bỏ
                                    </button>
                                </div>
                            </div>

                            <div class="col-span-4 md:col-span-2 text-center md:text-center text-sm">
                                <span class="md:hidden text-gray-500">Giá: </span>
                                <?php echo number_format($item['gia'], 0, ',', '.'); ?>₫
                            </div>

                            <div class="col-span-4 md:col-span-2 text-center">
                                <span class="inline-block bg-gray-100 px-3 py-1 rounded text-sm font-bold">
                                    x <?php echo $item['soLuong']; ?>
                                </span>
                            </div>

                            <div class="col-span-4 md:col-span-2 text-right md:text-center font-bold text-red-600">
                                <?php echo number_format($subtotal, 0, ',', '.'); ?>₫
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="mt-6">
                    <a href="index.php" class="text-gray-600 hover:text-black hover:underline flex items-center gap-2">
                        ← Tiếp tục mua sắm
                    </a>
                </div>
            </div>

            <div class="w-full lg:w-1/3">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 sticky top-24">
                    <h3 class="font-bold text-lg mb-4 uppercase">Cộng giỏ hàng</h3>

                    <div class="flex justify-between mb-2 text-sm">
                        <span class="text-gray-600">Tạm tính:</span>
                        <span class="font-bold"><?php echo number_format($total_money, 0, ',', '.'); ?>₫</span>
                    </div>

                    <div class="flex justify-between mb-4 text-sm border-b pb-4">
                        <span class="text-gray-600">Phí vận chuyển:</span>
                        <span class="text-green-600">Miễn phí</span>
                    </div>

                    <div class="flex justify-between mb-6 text-xl font-bold">
                        <span>Tổng cộng:</span>
                        <span class="text-red-600"><?php echo number_format($total_money, 0, ',', '.'); ?>₫</span>
                    </div>

                    <a href="thanhtoan.php" class="block w-full bg-black text-white text-center py-3 font-bold rounded hover:bg-red-600 transition uppercase">
                        Tiến hành thanh toán
                    </a>

                    <div class="mt-4 text-xs text-gray-500 text-center">
                        <p>🔒 Bảo mật thanh toán 100%</p>
                    </div>
                </div>
            </div>

        </div>
    <?php else: ?>
        <div class="text-center py-16 bg-gray-50 rounded-lg">
            <div class="text-6xl mb-4">🛒</div>
            <h2 class="text-xl font-bold mb-2">Giỏ hàng của bạn đang trống</h2>
            <p class="text-gray-500 mb-6">Hãy chọn thêm sản phẩm để mua sắm nhé!</p>
            <a href="index.php" class="inline-block bg-black text-white px-8 py-3 rounded font-bold hover:bg-gray-800 transition">
                QUAY LẠI CỬA HÀNG
            </a>
        </div>
    <?php endif; ?>
</div>

<div id="confirmModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeConfirmModal()"></div>

    <div class="relative bg-white w-full max-w-sm mx-auto mt-40 p-6 rounded-lg shadow-2xl animate-fade-in-up text-center">

        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <h3 class="text-lg font-bold text-gray-900 mb-2">Xác nhận xóa?</h3>
        <p class="text-gray-500 text-sm mb-6">Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng không? Hành động này không thể hoàn tác.</p>

        <div class="flex gap-3 justify-center">
            <button onclick="closeConfirmModal()"
                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded font-medium transition">
                Hủy bỏ
            </button>
            <a id="confirmDeleteBtn" href="#"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-medium transition">
                Đồng ý Xóa
            </a>
        </div>
    </div>
</div>

<script>
    function showConfirmModal(deleteUrl) {
        document.getElementById('confirmDeleteBtn').href = deleteUrl;
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }
</script>
<?php include './includes/footer.php'; ?>