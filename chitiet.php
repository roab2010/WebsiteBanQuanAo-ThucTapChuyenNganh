<?php
session_start();
include 'config/database.php';

// 1. Lấy ID và làm sạch dữ liệu
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Sản phẩm không hợp lệ!");
}

// 2. Lấy thông tin sản phẩm hiện tại
$sql = "SELECT * FROM SAN_PHAM WHERE sanpham_id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Sản phẩm không tồn tại!");
}
$sql_reviews = "SELECT dg.*, nd.ten as ten_nguoi_dung 
                FROM DANH_GIA dg 
                JOIN NGUOI_DUNG nd ON dg.nguoi_id = nd.nguoi_id 
                WHERE dg.sanpham_id = $id 
                ORDER BY dg.ngayTao DESC";
// 3. Lấy ĐÁNH GIÁ THẬT từ Database (JOIN với bảng NGUOI_DUNG để lấy tên)
$reviews = mysqli_query($conn, $sql_reviews);
$total_reviews = mysqli_num_rows($reviews);
// 3. Lấy 4 Sản phẩm liên quan (Cùng danh mục, trừ chính nó ra)
$cat_id = $product['danhmuc_id'];
$sql_related = "SELECT * FROM SAN_PHAM WHERE danhmuc_id = $cat_id AND sanpham_id != $id LIMIT 4";
$related_products = mysqli_query($conn, $sql_related);

include './includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="flex flex-col md:flex-row gap-10 mb-16">
        <div class="w-full md:w-1/2">
            <img src="<?php echo htmlspecialchars($product['hinhAnh']); ?>"
                alt="<?php echo htmlspecialchars($product['ten']); ?>"
                class="w-full h-auto rounded-lg shadow-lg object-cover">
        </div>

        <div class="w-full md:w-1/2">
            <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($product['ten']); ?></h1>

            <div class="flex items-center gap-4 mb-6">
                <p class="text-3xl text-red-600 font-bold"><?php echo number_format($product['gia'], 0, ',', '.'); ?>₫</p>

                <?php if ($product['soLuongTon'] > 0): ?>
                    <span class="bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded">
                        Còn <?php echo $product['soLuongTon']; ?> sản phẩm
                    </span>
                <?php else: ?>
                    <span class="bg-red-100 text-red-800 text-sm font-bold px-3 py-1 rounded">
                        HẾT HÀNG
                    </span>
                <?php endif; ?>
            </div>

            <p class="text-gray-600 mb-8 leading-relaxed text-lg border-b pb-6">
                <?php echo htmlspecialchars($product['moTa'] ?? 'Chưa có mô tả chi tiết cho sản phẩm này.'); ?>
            </p>

            <?php if ($product['soLuongTon'] > 0): ?>
                <form action="them-gio-hang.php" method="POST">
                    <input type="hidden" name="sanpham_id" value="<?php echo $product['sanpham_id']; ?>">

                    <div class="mb-6">
                        <label class="block font-bold mb-3 text-lg">Chọn Kích thước:</label>
                        <div class="flex gap-4">
                            <?php $sizes = ['S', 'M', 'L', 'XL']; ?>
                            <?php foreach ($sizes as $size): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="size" value="<?php echo $size; ?>" class="peer sr-only" required>
                                    <div class="w-14 h-14 flex items-center justify-center border-2 border-gray-300 rounded-md hover:border-black peer-checked:bg-black peer-checked:text-white peer-checked:border-black transition font-bold">
                                        <?php echo $size; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block font-bold mb-3 text-lg">Số lượng:</label>
                        <div class="flex items-center">
                            <button type="button" onclick="this.nextElementSibling.stepDown()" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-l text-xl font-bold">-</button>

                            <input type="number" name="soLuong" value="1" min="1" max="<?php echo $product['soLuongTon']; ?>"
                                class="w-16 h-10 border-t border-b border-gray-300 text-center focus:outline-none bg-white font-bold" readonly>

                            <button type="button" onclick="this.previousElementSibling.stepUp()" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-r text-xl font-bold">+</button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Tối đa <?php echo $product['soLuongTon']; ?> sản phẩm</p>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" name="add_to_cart" class="flex-1 bg-red-600 text-white py-4 font-bold rounded hover:bg-red-700 transition uppercase text-lg shadow-md">
                            THÊM VÀO GIỎ NGAY
                        </button>

                        <button type="button"
                            onclick="addToWishlist(<?php echo $product['sanpham_id']; ?>, '<?php echo addslashes($product['ten']); ?>')"
                            class="border-2 border-gray-300 w-16 flex items-center justify-center rounded hover:bg-red-50 hover:text-red-500 hover:border-red-500 transition text-2xl"
                            title="Thêm vào yêu thích">
                            ❤️
                        </button>
                    </div>
                </form>

            <?php else: ?>
                <div class="bg-gray-100 p-6 rounded-lg text-center border border-gray-300">
                    <p class="text-xl font-bold text-gray-500 mb-2">❌ Sản phẩm tạm thời hết hàng</p>
                    <p class="text-sm text-gray-400">Vui lòng quay lại sau hoặc chọn sản phẩm khác.</p>

                    <div class="mt-4">
                        <button type="button"
                            onclick="addToWishlist(<?php echo $product['sanpham_id']; ?>, '<?php echo addslashes($product['ten']); ?>')"
                            class="text-blue-600 hover:underline font-bold">
                            ❤️ Lưu vào yêu thích để theo dõi
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mt-10 bg-gray-50 p-6 rounded-lg space-y-3 text-sm text-gray-700">
                <p>✅ Cam kết chính hãng 100%</p>
                <p>✅ Miễn phí vận chuyển cho đơn hàng trên 500k</p>
                <p>✅ Hỗ trợ đổi trả trong vòng 7 ngày</p>
                <p>✅ Hotline hỗ trợ: 1900 xxxx</p>
            </div>
        </div>
    </div>

    <hr class="border-gray-200 my-12">

    <div class="mb-16">
        <h2 class="text-2xl font-bold mb-8 uppercase tracking-wide">
            Đánh giá khách hàng (<?php echo $total_reviews; ?>)
        </h2>

        <?php if ($total_reviews > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php while ($review = mysqli_fetch_assoc($reviews)): ?>
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center font-bold text-gray-600 uppercase">
                                <?php echo substr($review['ten_nguoi_dung'], 0, 1); ?>
                            </div>
                            <div>
                                <h4 class="font-bold"><?php echo htmlspecialchars($review['ten_nguoi_dung']); ?></h4>
                                <div class="text-yellow-500 text-sm">
                                    <?php
                                    // Vòng lặp in ngôi sao
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $review['soSao']) ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                            </div>
                            <span class="text-gray-400 text-sm ml-auto">
                                <?php echo date('d/m/Y', strtotime($review['ngayTao'])); ?>
                            </span>
                        </div>
                        <p class="text-gray-600 italic">"<?php echo htmlspecialchars($review['noiDung']); ?>"</p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-10 bg-gray-50 rounded-lg">
                <p class="text-gray-500 text-lg">Sản phẩm này chưa có đánh giá nào.</p>
                <p class="text-sm text-gray-400 mt-2">Hãy là người đầu tiên mua và đánh giá sản phẩm này!</p>
            </div>
        <?php endif; ?>
    </div>
    <hr class="border-gray-200 my-12">

    <?php if (mysqli_num_rows($related_products) > 0): ?>
        <div>
            <h2 class="text-2xl font-bold mb-8 uppercase tracking-wide text-center">Sản phẩm liên quan</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php while ($related = mysqli_fetch_assoc($related_products)): ?>

                    <div class="product-card group cursor-pointer border p-4 rounded-lg hover:shadow-lg transition">
                        <div class="overflow-hidden rounded-lg mb-4 relative">
                            <a href="chitiet.php?id=<?php echo $related['sanpham_id']; ?>">
                                <img src="<?php echo htmlspecialchars($related['hinhAnh']); ?>"
                                    alt="<?php echo htmlspecialchars($related['ten']); ?>"
                                    class="w-full h-64 object-cover transform group-hover:scale-105 transition duration-500">
                            </a>

                            <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition duration-300 bg-white/95 backdrop-blur border-t">
                                <div class="flex gap-2">
                                    <button type="button"
                                        onclick="openModal(
                                            <?php echo $related['sanpham_id']; ?>, 
                                            '<?php echo addslashes($related['ten']); ?>', 
                                            <?php echo $related['gia']; ?>, 
                                            '<?php echo $related['hinhAnh']; ?>'
                                        )"
                                        class="flex-1 bg-black text-white py-2 font-bold hover:bg-red-600 transition text-xs">
                                        THÊM GIỎ
                                    </button>

                                    <button class="w-10 h-10 border border-black flex items-center justify-center hover:bg-red-500 hover:text-white hover:border-red-500 transition"
                                        onclick="addToWishlist(<?php echo $related['sanpham_id']; ?>, '<?php echo addslashes($related['ten']); ?>')"
                                        title="Thêm vào yêu thích">
                                        ❤️
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <h3 class="font-bold text-sm mb-1 truncate">
                                <a href="chitiet.php?id=<?php echo $related['sanpham_id']; ?>">
                                    <?php echo htmlspecialchars($related['ten']); ?>
                                </a>
                            </h3>
                            <div class="text-red-600 font-bold"><?php echo number_format($related['gia'], 0, ',', '.'); ?>₫</div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<div id="quickViewModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="relative bg-white w-full max-w-4xl mx-auto mt-20 p-6 rounded-lg shadow-2xl animate-fade-in-up flex flex-col md:flex-row gap-8 mt-[300px]">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-black text-2xl font-bold">&times;</button>
        <div class="w-full md:w-1/2 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
            <img id="modalImg" src="" class="max-h-[400px] object-contain">
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
                <a id="modalLink" href="#" class="text-gray-500 hover:text-black hover:underline flex items-center gap-1 group">Xem chi tiết sản phẩm <span class="group-hover:translate-x-1 transition-transform">»</span></a>
            </div>
        </div>
    </div>
</div>

<?php include './includes/footer.php'; ?>