<?php
// index.php - CLICK TOÀN BỘ THẺ ĐỂ XEM CHI TIẾT
session_start();
include 'config/database.php';

// Xử lý đăng xuất
if (isset($_GET['logout'])) {
  // 1. Xóa sạch session hiện tại (Đăng xuất)
  session_destroy();

  // 2. MẸO: Khởi động lại một session MỚI ngay lập tức
  session_start();

  // 3. Gán thông báo vào session mới này
  $_SESSION['alert'] = [
    'type' => 'success',
    'message' => 'Đăng xuất thành công! Hẹn gặp lại.'
  ];

  // 4. Chuyển hướng về trang đăng nhập
  header("Location: dangnhap.php");
  exit();
}

$user = $_SESSION['user'] ?? null;
$login_time = $_SESSION['login_time'] ?? null;

// --- XỬ LÝ TÌM KIẾM ---
$search_query = $_GET['search'] ?? '';
$search_results = [];
if (!empty($search_query)) {
  $safe_search = mysqli_real_escape_string($conn, $search_query);
  $sql_search = "SELECT * FROM SAN_PHAM WHERE ten LIKE '%$safe_search%'";
  $result_search = mysqli_query($conn, $sql_search);
  if ($result_search) {
    while ($row = mysqli_fetch_assoc($result_search)) {
      $search_results[] = $row;
    }
  }
}

// --- LẤY SẢN PHẨM TRANG CHỦ ---
$products_by_category = [];
if (empty($search_query)) {
  $sql_home = "SELECT sp.*, dm.ten as ten_danhmuc FROM SAN_PHAM sp INNER JOIN DANH_MUC dm ON sp.danhmuc_id = dm.danhmuc_id ORDER BY dm.danhmuc_id ASC";
  $result_home = mysqli_query($conn, $sql_home);
  if ($result_home) {
    while ($row = mysqli_fetch_assoc($result_home)) {
      $catName = $row['ten_danhmuc'];
      $products_by_category[$catName][] = $row;
    }
  }
}
include './includes/header.php';
?>
<?php if (!empty($search_query)): ?>
  <div class="container mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">Kết quả tìm kiếm cho "<?php echo htmlspecialchars($search_query); ?>"</h2>
    <?php if (count($search_results) > 0): ?>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($search_results as $product): ?>
          <div class="product-card border p-4 rounded-lg shadow hover:shadow-lg transition cursor-pointer"
            onclick="window.location.href='chitiet.php?id=<?php echo $product['sanpham_id']; ?>'">

            <img src="<?php echo htmlspecialchars($product['hinhAnh']); ?>" class="w-full h-64 object-cover mb-4 rounded hover:opacity-90 transition">

            <h3 class="font-bold text-lg hover:text-red-600 transition">
              <?php echo htmlspecialchars($product['ten']); ?>
            </h3>

            <div class="text-red-600 font-bold my-2"><?php echo number_format($product['gia'], 0, ',', '.'); ?>₫</div>

            <div class="mt-4 flex gap-2">
              <button type="button"
                onclick="event.stopPropagation(); openModal(<?php echo $product['sanpham_id']; ?>, '<?php echo addslashes($product['ten']); ?>', <?php echo $product['gia']; ?>, '<?php echo $product['hinhAnh']; ?>')"
                class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 flex-1 text-sm font-bold uppercase">
                THÊM GIỎ
              </button>
              <button class="border border-gray-300 w-10 h-10 flex items-center justify-center rounded hover:bg-red-50 hover:text-red-500 hover:border-red-500 transition"
                onclick="event.stopPropagation(); addToWishlist(<?php echo $product['sanpham_id']; ?>, '<?php echo htmlspecialchars($product['ten']); ?>')">
                ❤️
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-500">Không tìm thấy sản phẩm nào.</p>
    <?php endif; ?>
    <div class="text-center mt-10"><a href="index.php" class="bg-yellow-400 px-6 py-2 rounded font-bold hover:bg-yellow-500">← Quay lại trang chủ</a></div>
  </div>
<?php else: ?>
  <section class="relative flex flex-col justify-center items-center text-center text-white h-[80vh] overflow-hidden">

    <div class="absolute inset-0">
      <img src="./assets/img/slide.jpg" class="w-full h-full object-cover brightness-50">
      <div class="absolute inset-0 bg-black/30"></div>
    </div>

    <?php if ($user): ?>
      <div class="absolute top-4 left-0 right-0 z-20 animate-fade-in-down">
        <span class="inline-block bg-white/20 backdrop-blur-md border border-white/30 text-white px-6 py-2 rounded-full text-sm font-medium shadow-lg">
          👋 Xin chào, <strong><?php echo htmlspecialchars($user); ?></strong>!
        </span>
      </div>
    <?php endif; ?>

    <div class="relative z-10 px-6 mt-10">
      <h1 class="text-5xl md:text-6xl font-extrabold mb-4 drop-shadow-lg animate-fade-in-up">
        Mặc Đi <span class="text-red-500">Sợ *** Gì</span>
      </h1>

      <a href="#store-content" class="mt-8 inline-block px-8 py-3 border border-white text-white font-semibold rounded hover:bg-white hover:text-black transition animate-bounce-slow">
        VIEW STORE
      </a>

      <div class="mt-10">
        <img src="./assets/img/logodatachnen.png" class="mx-auto w-24 md:w-32 drop-shadow-lg">
      </div>
    </div>
  </section>

  <main id="store-content" class="container mx-auto px-4 py-12">
    <?php foreach ($products_by_category as $categoryName => $products): ?>
      <section class="mb-16">
        <h2 class="text-3xl font-bold text-center mb-8 uppercase tracking-wider border-b-2 border-black inline-block mx-auto"><?php echo htmlspecialchars($categoryName); ?></h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <?php
          // --- 1. LOGIC GIỚI HẠN SỐ LƯỢNG ---
          $count = 0;
          foreach ($products as $product):
            if ($count >= 4) break; // Chỉ lấy 4 sản phẩm đầu tiên
          ?>

            <div class="product-card group cursor-pointer"
              onclick="window.location.href='chitiet.php?id=<?php echo $product['sanpham_id']; ?>'">

              <div class="overflow-hidden rounded-lg mb-4 relative">
                <img src="<?php echo htmlspecialchars($product['hinhAnh']); ?>" class="w-full h-[400px] object-cover transform group-hover:scale-105 transition duration-500">

                <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition duration-300 bg-white/95 backdrop-blur border-t">
                  <div class="flex gap-2">
                    <button type="button"
                      onclick="event.stopPropagation(); openModal(<?php echo $product['sanpham_id']; ?>, '<?php echo addslashes($product['ten']); ?>', <?php echo $product['gia']; ?>, '<?php echo $product['hinhAnh']; ?>')"
                      class="flex-1 bg-black text-white py-2 font-bold hover:bg-red-600 transition text-sm">
                      THÊM GIỎ
                    </button>
                    <button class="w-10 h-10 border border-black flex items-center justify-center hover:bg-red-500 hover:text-white hover:border-red-500 transition"
                      onclick="event.stopPropagation(); addToWishlist(<?php echo $product['sanpham_id']; ?>, '<?php echo htmlspecialchars($product['ten']); ?>')"
                      title="Thêm vào yêu thích">❤️</button>
                  </div>
                </div>
              </div>
              <div class="text-center">
                <h3 class="font-bold text-lg mb-1 hover:text-red-600 transition">
                  <?php echo htmlspecialchars($product['ten']); ?>
                </h3>
                <div class="text-gray-600"><?php echo number_format($product['gia'], 0, ',', '.'); ?>₫</div>
              </div>
            </div>
          <?php
            $count++; // Tăng biến đếm sau mỗi lần lặp
          endforeach;
          ?>
        </div>
      </section>
    <?php endforeach; ?>

    <div class="text-center mt-16 mb-12">
      <a href="tat-ca-san-pham.php" class="inline-block border-2 border-black px-10 py-4 font-bold hover:bg-black hover:text-white transition uppercase tracking-widest text-sm">
        XEM TẤT CẢ SẢN PHẨM
      </a>
    </div>
  </main>
<?php endif; ?>

<div id="quickViewModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
  <div class="relative bg-white w-full max-w-4xl mx-auto mt-60 p-6 rounded-lg shadow-2xl animate-fade-in-up flex flex-col md:flex-row gap-8 ">
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