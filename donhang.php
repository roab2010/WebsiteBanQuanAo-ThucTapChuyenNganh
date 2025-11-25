<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng
$sql_orders = "SELECT * FROM DON_HANG WHERE nguoi_id = $user_id ORDER BY donhang_id DESC";
$result_orders = mysqli_query($conn, $sql_orders);

include './includes/header.php';
?>

<div class="container mx-auto px-4 py-12 min-h-screen">
    <h1 class="text-3xl font-bold mb-8 uppercase tracking-wide border-b pb-4">Lịch sử đơn hàng</h1>

    <?php if (mysqli_num_rows($result_orders) > 0): ?>
        <div class="space-y-8">
            <?php while ($order = mysqli_fetch_assoc($result_orders)):
                $donhang_id = $order['donhang_id'];

                // Màu sắc trạng thái
                $status_color = 'bg-gray-100 text-gray-800';
                if ($order['trangThaiDH'] == 'Dang giao') $status_color = 'bg-blue-100 text-blue-800';
                if ($order['trangThaiDH'] == 'Hoan tat') $status_color = 'bg-green-100 text-green-800';
                if ($order['trangThaiDH'] == 'Huy') $status_color = 'bg-red-100 text-red-800';

                // Logic thanh toán
                $is_paid = false;
                if (stripos($order['trangThaiTT'], 'Da thanh toan') !== false) $is_paid = true;
                if ($order['phuongThucTT'] == 'COD' && $order['trangThaiDH'] == 'Hoan tat') $is_paid = true;
            ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                    <div class="bg-gray-50 p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <span class="font-bold text-lg mr-2">Đơn hàng #<?php echo $donhang_id; ?></span>
                            <span class="text-sm text-gray-500">Đặt ngày: <?php echo date('d/m/Y H:i', strtotime($order['ngayTao'])); ?></span>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $status_color; ?>">
                                <?php echo $order['trangThaiDH']; ?>
                            </span>
                            <?php if ($is_paid): ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-green-100 text-green-800 border border-green-200">ĐÃ THANH TOÁN</span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-gray-100 text-gray-500 border border-gray-200">CHƯA THANH TOÁN</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-4">
                        <?php
                        $sql_items = "SELECT ct.*, sp.ten, sp.hinhAnh 
                                      FROM CHI_TIET_DON_HANG ct 
                                      JOIN SAN_PHAM sp ON ct.sanpham_id = sp.sanpham_id 
                                      WHERE ct.donhang_id = $donhang_id";
                        $result_items = mysqli_query($conn, $sql_items);

                        while ($item = mysqli_fetch_assoc($result_items)):
                            // KIỂM TRA ĐÃ ĐÁNH GIÁ CHƯA?
                            $sp_id = $item['sanpham_id'];
                            $check_review = mysqli_query($conn, "SELECT * FROM DANH_GIA WHERE nguoi_id = $user_id AND sanpham_id = $sp_id");
                            $has_reviewed = mysqli_num_rows($check_review) > 0;
                        ?>
                            <div class="flex gap-4 mb-4 last:mb-0 items-center border-b last:border-0 pb-4 last:pb-0 border-gray-100">
                                <a href="chitiet.php?id=<?php echo $sp_id; ?>">
                                    <img src="<?php echo htmlspecialchars($item['hinhAnh']); ?>" class="w-16 h-16 object-cover rounded border hover:opacity-80">
                                </a>
                                <div class="flex-1">
                                    <h4 class="font-bold text-sm">
                                        <a href="chitiet.php?id=<?php echo $sp_id; ?>" class="hover:text-red-600"><?php echo htmlspecialchars($item['ten']); ?></a>
                                    </h4>
                                    <p class="text-xs text-gray-500">Size: <?php echo $item['size']; ?> | x<?php echo $item['soLuong']; ?></p>
                                </div>

                                <div class="text-right flex flex-col items-end gap-2">
                                    <span class="font-medium text-sm"><?php echo number_format($item['donGia'], 0, ',', '.'); ?>₫</span>

                                    <?php if ($order['trangThaiDH'] == 'Hoan tat'): ?>
                                        <?php if (!$has_reviewed): ?>
                                            <button onclick="openReviewModal(<?php echo $sp_id; ?>, '<?php echo addslashes($item['ten']); ?>', '<?php echo $item['hinhAnh']; ?>')"
                                                class="bg-black  text-white text-xs px-3 py-1.5 rounded hover:bg-yellow-600 transition">
                                                ★ Viết đánh giá
                                            </button>
                                        <?php else: ?>
                                            <span class="text-xs text-yellow-600 font-bold border border-yellow-600 px-2 py-1 rounded">
                                                ★ Đã đánh giá
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                        <div class="text-sm text-gray-500">Phương thức: <strong><?php echo $order['phuongThucTT']; ?></strong></div>
                        <div class="text-xl font-bold text-red-600">Tổng tiền: <?php echo number_format($order['tongTien'], 0, ',', '.'); ?>₫</div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 bg-gray-50 rounded-lg">
            <div class="text-6xl mb-4">📦</div>
            <h2 class="text-xl font-bold mb-2">Bạn chưa có đơn hàng nào</h2>
            <a href="index.php" class="inline-block bg-black text-white px-8 py-3 rounded font-bold hover:bg-gray-800 transition mt-4">MUA SẮM NGAY</a>
        </div>
    <?php endif; ?>
</div>

<div id="reviewModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeReviewModal()"></div>
    <div class="relative bg-white w-full max-w-md mx-auto mt-20 p-6 rounded-lg shadow-2xl animate-fade-in-up">
        <button onclick="closeReviewModal()" class="absolute top-4 right-4 text-gray-400 hover:text-black text-2xl">&times;</button>

        <h3 class="text-xl font-bold text-center mb-4">Đánh giá sản phẩm</h3>

        <div class="flex items-center gap-4 mb-6 bg-gray-50 p-3 rounded">
            <img id="reviewImg" src="" class="w-12 h-12 object-cover rounded">
            <p id="reviewName" class="font-bold text-sm line-clamp-2"></p>
        </div>

        <form action="guidanhgia.php" method="POST">
            <input type="hidden" name="sanpham_id" id="reviewId">

            <div class="mb-4 text-center">
                <label class="block text-sm font-bold mb-2">Bạn cảm thấy thế nào?</label>
                <div class="flex justify-center gap-2 text-3xl cursor-pointer" id="star-rating">
                    <span onclick="setRating(1)" class="star text-gray-300 hover:text-yellow-400 transition">★</span>
                    <span onclick="setRating(2)" class="star text-gray-300 hover:text-yellow-400 transition">★</span>
                    <span onclick="setRating(3)" class="star text-gray-300 hover:text-yellow-400 transition">★</span>
                    <span onclick="setRating(4)" class="star text-gray-300 hover:text-yellow-400 transition">★</span>
                    <span onclick="setRating(5)" class="star text-gray-300 hover:text-yellow-400 transition">★</span>
                </div>
                <input type="hidden" name="soSao" id="ratingInput" value="5" required>
                <p class="text-sm text-yellow-600 mt-1 font-bold" id="ratingText">Tuyệt vời</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold mb-2">Nhận xét của bạn</label>
                <textarea name="noiDung" rows="3" class="w-full border p-3 rounded focus:outline-none focus:border-black" placeholder="Chất lượng sản phẩm, thái độ phục vụ..." required></textarea>
            </div>

            <button type="submit" class="w-full bg-black text-white py-3 rounded font-bold hover:bg-red-600 transition">GỬI ĐÁNH GIÁ</button>
        </form>
    </div>
</div>

<script>
    // JS Xử lý Modal
    function openReviewModal(id, name, img) {
        document.getElementById('reviewId').value = id;
        document.getElementById('reviewName').innerText = name;
        document.getElementById('reviewImg').src = img;
        document.getElementById('reviewModal').classList.remove('hidden');
        setRating(5); // Mặc định 5 sao
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

    // JS Xử lý chọn sao
    function setRating(n) {
        document.getElementById('ratingInput').value = n;
        const stars = document.querySelectorAll('#star-rating .star');
        const texts = ['Tệ', 'Không hài lòng', 'Bình thường', 'Hài lòng', 'Tuyệt vời'];

        stars.forEach((star, index) => {
            if (index < n) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.add('text-gray-300');
                star.classList.remove('text-yellow-400');
            }
        });
        document.getElementById('ratingText').innerText = texts[n - 1];
    }
</script>

<?php include './includes/footer.php'; ?>