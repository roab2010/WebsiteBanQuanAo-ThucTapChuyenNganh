<?php
session_start();
include 'config/database.php';

// 1. Lấy ID danh mục từ URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// 2. Lấy thông tin Danh mục (để hiện cái Tiêu đề to đùng)
$sql_cat = "SELECT * FROM DANH_MUC WHERE danhmuc_id = $id";
$result_cat = mysqli_query($conn, $sql_cat);
$category = mysqli_fetch_assoc($result_cat);

if (!$category) {
    die("Danh mục không tồn tại!");
}

// 3. Lấy sản phẩm thuộc danh mục này
$sql_products = "SELECT * FROM SAN_PHAM WHERE danhmuc_id = $id ORDER BY sanpham_id DESC";
$products = mysqli_query($conn, $sql_products);

include './includes/header.php';
?>

<div class="bg-gray-50 py-10 min-h-screen">
    <div class="container mx-auto px-4">

        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold uppercase tracking-wide border-b-2 border-black inline-block pb-2">
                <?php echo htmlspecialchars($category['ten']); ?>
            </h1>
            <?php if (!empty($category['moTa'])): ?>
                <p class="text-gray-500 mt-4 text-sm italic"><?php echo htmlspecialchars($category['moTa']); ?></p>
            <?php endif; ?>
        </div>

        <?php if (mysqli_num_rows($products) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php while ($product = mysqli_fetch_assoc($products)): ?>

                    <div class="product-card bg-white border border-gray-100 p-4 rounded-lg shadow-sm hover:shadow-xl transition cursor-pointer group"
                        onclick="window.location.href='chitiet.php?id=<?php echo $product['sanpham_id']; ?>'">

                        <div class="overflow-hidden rounded-lg mb-4 relative">
                            <img src="<?php echo htmlspecialchars($product['hinhAnh']); ?>" class="w-full h-[350px] object-cover transform group-hover:scale-105 transition duration-500">

                            <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition duration-300 bg-white/95 backdrop-blur border-t">
                                <div class="flex gap-2">
                                    <button type="button"
                                        onclick="event.stopPropagation(); openModal(<?php echo $product['sanpham_id']; ?>, '<?php echo addslashes($product['ten']); ?>', <?php echo $product['gia']; ?>, '<?php echo $product['hinhAnh']; ?>')"
                                        class="flex-1 bg-black text-white py-2 font-bold hover:bg-red-600 transition text-xs uppercase">
                                        Thêm giỏ
                                    </button>
                                    <button class="w-10 h-10 border border-black flex items-center justify-center hover:bg-red-500 hover:text-white transition"
                                        onclick="event.stopPropagation(); addToWishlist(<?php echo $product['sanpham_id']; ?>, '<?php echo addslashes($product['ten']); ?>')">❤️</button>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <h3 class="font-bold text-sm mb-1 hover:text-red-600 transition truncate">
                                <?php echo htmlspecialchars($product['ten']); ?>
                            </h3>
                            <div class="text-red-600 font-bold"><?php echo number_format($product['gia'], 0, ',', '.'); ?>₫</div>
                        </div>
                    </div>

                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <div class="text-6xl mb-4">📂</div>
                <h3 class="text-xl font-bold text-gray-600">Chưa có sản phẩm nào trong mục này</h3>
                <a href="index.php" class="text-blue-500 hover:underline mt-2 inline-block">Quay lại trang chủ</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<div id="quickViewModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="relative bg-white w-full max-w-4xl mx-auto mt-20 p-6 rounded-lg shadow-2xl animate-fade-in-up flex flex-col md:flex-row gap-8">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-black text-2xl font-bold">&times;</button>
        <div class="w-full md:w-1/2 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
            <img id="modalImg" src="" alt="Product Image" class="max-h-[400px] object-contain">
        </div>
        <div class="w-full md:w-1/2 flex flex-col justify-between">
            <div>
                <h2 id="modalName" class="text-2xl font-bold mb-2 uppercase"></h2>
                <div class="flex items-center gap-4 mb-4">
                    <span id="modalPrice" class="text-3xl text-red-600 font-bold"></span>
                    <span class="text-sm text-green-600 bg-green-100 px-2 py-1 rounded">Còn hàng</span>
                </div>
                <hr class="border-gray-200 my-4">
                <form action="them-gio-hang.php" method="POST">
                    <input type="hidden" name="sanpham_id" id="modalId">
                    <div class="mb-6">
                        <label class="block font-bold mb-2 text-sm">Kích thước:</label>
                        <div class="flex gap-3">
                            <label class="cursor-pointer"><input type="radio" name="size" value="S" class="peer sr-only" required>
                                <div class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded hover:border-black peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition">S</div>
                            </label>
                            <label class="cursor-pointer"><input type="radio" name="size" value="M" class="peer sr-only">
                                <div class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded hover:border-black peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition">M</div>
                            </label>
                            <label class="cursor-pointer"><input type="radio" name="size" value="L" class="peer sr-only">
                                <div class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded hover:border-black peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition">L</div>
                            </label>
                            <label class="cursor-pointer"><input type="radio" name="size" value="XL" class="peer sr-only">
                                <div class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded hover:border-black peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition">XL</div>
                            </label>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block font-bold mb-2 text-sm">Số lượng:</label>
                        <div class="flex items-center">
                            <button type="button" onclick="updateQty(-1)" class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-l font-bold">-</button>
                            <input type="number" name="soLuong" id="modalQty" value="1" min="1" class="w-12 h-8 text-center border-t border-b border-gray-200 focus:outline-none" readonly>
                            <button type="button" onclick="updateQty(1)" class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-r font-bold">+</button>
                        </div>
                    </div>
                    <button type="submit" name="add_to_cart" class="w-full bg-red-600 text-white font-bold py-3 rounded hover:bg-red-700 transition uppercase">THÊM VÀO GIỎ NGAY</button>
                </form>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-100 text-sm flex justify-end">
                <a id="modalLink" href="#" class="text-gray-500 hover:text-black hover:underline flex items-center gap-1 group">
                    Xem chi tiết sản phẩm <span class="group-hover:translate-x-1 transition-transform">»</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include './includes/footer.php'; ?>