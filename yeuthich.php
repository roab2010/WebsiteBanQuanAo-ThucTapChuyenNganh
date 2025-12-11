<?php
session_start();
include 'config/database.php';

// --- PHẦN 1: XỬ LÝ AJAX (PHP nhận ID và trả về HTML sản phẩm - PDO) ---
if (isset($_POST['wishlist_ids'])) {
    $ids_raw = json_decode($_POST['wishlist_ids'], true);

    // Nếu danh sách rỗng
    if (empty($ids_raw)) {
        echo '<div class="text-center col-span-full py-10"><p class="text-gray-500">Danh sách yêu thích trống.</p></div>';
        exit;
    }

    // Làm sạch ID (chỉ lấy số)
    $ids = array_map('intval', $ids_raw);

    // Tạo chuỗi placeholder (?,?,?) tương ứng số lượng ID để dùng cho PDO
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Truy vấn PDO
    $sql = "SELECT * FROM SAN_PHAM WHERE sanpham_id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($ids); // Truyền mảng ID vào execute

    if ($stmt->rowCount() > 0) {
        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
?>
            <div class="product-card border p-4 rounded-lg shadow hover:shadow-lg transition relative group">
                <button onclick="removeFromWishlistPage(<?php echo $product['sanpham_id']; ?>, this)"
                    class="absolute top-2 right-2 bg-gray-100 hover:bg-red-500 hover:text-white w-8 h-8 rounded-full flex items-center justify-center transition z-10" title="Xóa">
                    ✕
                </button>

                <a href="chitiet.php?id=<?php echo $product['sanpham_id']; ?>">
                    <img src="<?php echo htmlspecialchars($product['hinhAnh']); ?>" class="w-full h-64 object-cover mb-4 rounded hover:opacity-90 transition">
                </a>

                <h3 class="font-bold text-lg hover:text-red-600 transition text-center">
                    <a href="chitiet.php?id=<?php echo $product['sanpham_id']; ?>">
                        <?php echo htmlspecialchars($product['ten']); ?>
                    </a>
                </h3>

                <div class="text-red-600 font-bold my-2 text-center"><?php echo number_format($product['gia'], 0, ',', '.'); ?>₫</div>

                <div class="mt-4">
                    <button type="button"
                        onclick="openModal(
                            <?php echo $product['sanpham_id']; ?>, 
                            '<?php echo addslashes($product['ten']); ?>', 
                            <?php echo $product['gia']; ?>, 
                            '<?php echo $product['hinhAnh']; ?>',
                            <?php echo $product['soLuongTon']; ?>
                        )"
                        class="w-full bg-black text-white px-4 py-2 rounded hover:bg-gray-800 text-sm font-bold uppercase">
                        THÊM GIỎ
                    </button>
                </div>
            </div>
<?php
        }
    }
    exit; // Kết thúc phần xử lý Ajax
}

// --- PHẦN 2: GIAO DIỆN CHÍNH CỦA TRANG ---
include './includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-8 uppercase tracking-wide border-b pb-4 flex items-center gap-2">
        Danh sách yêu thích ❤️
    </h1>

    <div id="wishlist-grid" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 min-h-[200px]">
        <div class="col-span-full flex justify-center items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600"></div>
        </div>
    </div>

    <div class="mt-10 text-center">
        <a href="index.php" class="text-gray-600 hover:underline">← Quay lại cửa hàng</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadWishlistItems();
    });

    function loadWishlistItems() {
        // 1. Lấy danh sách từ LocalStorage
        const wishlist = JSON.parse(localStorage.getItem('myWishlist')) || [];

        // Lấy ra mảng chỉ chứa ID (Ví dụ: [1, 5, 8])
        const ids = wishlist.map(item => item.id);

        if (ids.length === 0) {
            document.getElementById('wishlist-grid').innerHTML = '<div class="text-center col-span-full py-10"><p class="text-gray-500 text-lg">Bạn chưa yêu thích sản phẩm nào.</p></div>';
            return;
        }

        // 2. Gửi Ajax sang PHP để lấy HTML
        const formData = new FormData();
        formData.append('wishlist_ids', JSON.stringify(ids));

        fetch('yeuthich.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // 3. Hiển thị HTML nhận được
                document.getElementById('wishlist-grid').innerHTML = html;
            })
            .catch(error => console.error('Error:', error));
    }

    function removeFromWishlistPage(id, btn) {
        let wishlist = JSON.parse(localStorage.getItem('myWishlist')) || [];
        wishlist = wishlist.filter(item => item.id !== id);
        localStorage.setItem('myWishlist', JSON.stringify(wishlist));

        const card = btn.closest('.product-card');
        card.style.transition = "all 0.3s ease";
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9)';

        setTimeout(() => {
            card.remove();
            if (document.querySelectorAll('#wishlist-grid .product-card').length === 0) {
                document.getElementById('wishlist-grid').innerHTML = '<div class="text-center col-span-full py-10"><p class="text-gray-500 text-lg">Bạn chưa yêu thích sản phẩm nào.</p></div>';
            }
        }, 300);

        showToast('Đã xóa sản phẩm khỏi danh sách yêu thích', 'success');
    }
</script>

<div id="quickViewModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="relative bg-white w-full max-w-4xl mx-auto mt-20 p-6 rounded-lg shadow-2xl animate-fade-in-up flex flex-col md:flex-row gap-8"> <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-black text-2xl font-bold">&times;</button>

        <div class="w-full md:w-1/2 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
            <img id="modalImg" src="" alt="Product Image" class="max-h-[400px] object-contain">
        </div>

        <div class="w-full md:w-1/2 flex flex-col justify-between">
            <div>
                <h2 id="modalName" class="text-2xl font-bold mb-2 uppercase"></h2>
                <div class="flex items-center gap-4 mb-4">
                    <span id="modalPrice" class="text-3xl text-red-600 font-bold"></span>
                    <span id="modalStockLabel"></span>
                </div>
                <hr class="border-gray-200 my-4">

                <form id="modalBuyForm" action="them-gio-hang.php" method="POST">
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

                <div id="modalOutOfStockMsg" class="hidden bg-gray-100 p-4 rounded text-center border border-gray-200">
                    <p class="text-red-500 font-bold mb-1">❌ Sản phẩm tạm thời hết hàng</p>
                    <p class="text-xs text-gray-500">Vui lòng quay lại sau hoặc xem sản phẩm khác.</p>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-100 text-sm flex justify-end">
                <a id="modalLink" href="#" class="text-gray-500 hover:text-black hover:underline flex items-center gap-1 group">Xem chi tiết sản phẩm <span class="group-hover:translate-x-1 transition-transform">»</span></a>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/scripts.js"></script>
<?php include './includes/footer.php'; ?>