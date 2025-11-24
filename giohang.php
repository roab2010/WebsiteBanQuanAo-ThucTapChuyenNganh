<?php
session_start();
include 'config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Xử lý XÓA sản phẩm khỏi giỏ
if (isset($_GET['delete'])) {
    $cart_id = intval($_GET['delete']);
    $sql_delete = "DELETE FROM GIO_HANG WHERE giohang_id = $cart_id AND nguoi_id = $user_id";
    mysqli_query($conn, $sql_delete);
    header("Location: giohang.php"); // Load lại trang để cập nhật
    exit();
}

// 3. Lấy danh sách sản phẩm trong giỏ (JOIN bảng GIO_HANG và SAN_PHAM)
$sql = "SELECT gh.*, sp.ten, sp.gia, sp.hinhAnh 
        FROM GIO_HANG gh
        JOIN SAN_PHAM sp ON gh.sanpham_id = sp.sanpham_id
        WHERE gh.nguoi_id = $user_id
        ORDER BY gh.ngayThem DESC";
$result = mysqli_query($conn, $sql);

// Biến tính tổng tiền
$total_money = 0;

include './includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-8 uppercase tracking-wide border-b pb-4">Giỏ hàng của bạn</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="flex flex-col lg:flex-row gap-8">

            <div class="w-full lg:w-2/3">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="hidden md:grid grid-cols-12 gap-4 p-4 bg-gray-50 border-b font-bold text-sm text-gray-600">
                        <div class="col-span-6">Sản phẩm</div>
                        <div class="col-span-2 text-center">Đơn giá</div>
                        <div class="col-span-2 text-center">Số lượng</div>
                        <div class="col-span-2 text-center">Thành tiền</div>
                    </div>

                    <?php while ($item = mysqli_fetch_assoc($result)):
                        $subtotal = $item['gia'] * $item['soLuong'];
                        $total_money += $subtotal;
                    ?>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 p-4 items-center border-b last:border-0 hover:bg-gray-50 transition">

                            <div class="col-span-12 md:col-span-6 flex items-center gap-4">
                                <a href="giohang.php?delete=<?php echo $item['giohang_id']; ?>"
                                    class="md:hidden text-gray-400 hover:text-red-500 text-xl"
                                    onclick="return confirm('Xóa sản phẩm này?')">&times;</a>

                                <img src="<?php echo htmlspecialchars($item['hinhAnh']); ?>" class="w-20 h-24 object-cover rounded border">

                                <div>
                                    <h3 class="font-bold text-sm md:text-base">
                                        <a href="chitiet.php?id=<?php echo $item['sanpham_id']; ?>" class="hover:underline">
                                            <?php echo htmlspecialchars($item['ten']); ?>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-1">Size: <span class="font-bold text-black"><?php echo $item['size']; ?></span></p>

                                    <a href="giohang.php?delete=<?php echo $item['giohang_id']; ?>"
                                        class="hidden md:inline-block text-xs text-red-500 hover:underline mt-2"
                                        onclick="return confirm('Bạn chắc chắn muốn xóa?')">
                                        Xóa bỏ
                                    </a>
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

<?php include './includes/footer.php'; ?>