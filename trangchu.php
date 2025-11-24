<?php
// trangchu.php - DIRECT FIX
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Xử lý đăng xuất
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: dangnhap.php");
  exit();
}
// Lấy thông tin user từ session
$user = $_SESSION['user'] ?? null;
$login_time = $_SESSION['login_time'] ?? null;

// Xử lý tìm kiếm
$search_query = $_GET['search'] ?? '';
$search_results = [];

if (!empty($search_query)) {
  // Giả lập kết quả tìm kiếm (thực tế sẽ query database)
  $all_products = [
    ['name' => 'Áo thun nam cổ tròn tay ngắn', 'price' => '129.000₫', 'category' => 'Đồ Nam'],
    ['name' => 'Áo len nam cổ tròn tay dài', 'price' => '249.000₫', 'category' => 'Đồ Nam'],
    ['name' => 'Áo polo nam tay ngắn', 'price' => '199.000₫', 'category' => 'Đồ Nam'],
    ['name' => 'Áo thun nữ cổ tròn tay ngắn', 'price' => '129.000₫', 'category' => 'Đồ Nữ'],
    ['name' => 'Áo len nữ tay dài', 'price' => '249.000₫', 'category' => 'Đồ Nữ'],
    ['name' => 'Chân váy nữ chữ A', 'price' => '199.000₫', 'category' => 'Đồ Nữ'],
    ['name' => 'Đầm bé gái tay ngắn', 'price' => '199.000₫', 'category' => 'Đồ Trẻ Em'],
    ['name' => 'Áo thun bé trai tay ngắn', 'price' => '99.000₫', 'category' => 'Đồ Trẻ Em'],
    ['name' => 'Áo thun bé gái tay dài', 'price' => '129.000₫', 'category' => 'Đồ Trẻ Em'],
  ];

  foreach ($all_products as $product) {
    if (stripos($product['name'], $search_query) !== false) {
      $search_results[] = $product;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>3 chàng lính ngự lâm - Trang Chủ</title>
  <link rel="stylesheet" href="styles.css" />
</head>

<body>
  <header>
    <div class="header-container">
      <div class="nav-links">
        <a href="trangchu.php" class="nav-link">Home</a>
        <span class="separator">|</span>
        <a href="#" class="nav-link">Top</a>
        <span class="separator">|</span>
        <a href="#" class="nav-link">Bottoms</a>
        <span class="separator">|</span>
        <a href="#" class="nav-link">OutWear</a>
        <span class="separator">|</span>
        <a href="#" class="nav-link">Accessories</a>
      </div>

      <div class="search-container">
        <form method="GET" action="">
          <div class="search-box">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." class="search-input" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="search-btn">Tìm kiếm</button>
          </div>
        </form>
      </div>

      <div class="header-icons">
        <?php if ($user): ?>
          <span class="user-info">Xin chào, <?php echo htmlspecialchars($user); ?></span>
          <a href="?logout=1" class="icon-link" title="Đăng xuất">🚪</a>
        <?php else: ?>
          <a href="dangnhap.php" class="icon-link" title="Đăng nhập">👤</a>
        <?php endif; ?>
        <a href="#" class="icon-link" title="Yêu thích">❤️</a>
        <a href="#" class="icon-link" title="Giỏ hàng">🛒</a>
      </div>
    </div>
  </header>

  <?php if ($user): ?>
    <div class="welcome-message">
      <p>Chào mừng <strong><?php echo htmlspecialchars($user); ?></strong>!
        <?php if ($login_time): ?>
          Bạn đã đăng nhập lúc <?php echo date('H:i:s d/m/Y', $login_time); ?>
        <?php endif; ?>
      </p>
    </div>
  <?php endif; ?>

  <?php if (!empty($search_results)): ?>
    <div class="container">
      <h2>Kết quả tìm kiếm cho "<?php echo htmlspecialchars($search_query); ?>" (<?php echo count($search_results); ?> sản phẩm)</h2>
      <div class="products">
        <?php foreach ($search_results as $product): ?>
          <div class="product-card">
            <img src="https://source.unsplash.com/300x400/?<?php echo urlencode($product['name']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <div class="price"><?php echo htmlspecialchars($product['price']); ?></div>
            <small><?php echo htmlspecialchars($product['category']); ?></small>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(<?php echo rand(1, 100); ?>, '<?php echo htmlspecialchars($product['name']); ?>')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(<?php echo rand(1, 100); ?>, '<?php echo htmlspecialchars($product['name']); ?>')">Yêu thích</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="text-center mt-20">
        <a href="trangchu.php" class="btn btn-warning">← Quay lại trang chủ</a>
      </div>
    </div>
  <?php else: ?>

    <!-- Banner Section -->
    <section class="banner">
      <div class="banner-text">
        <h2>MẶC ĐI SỢ *** GÌ</h2>
        <a href="#" class="btn btn-primary">Xem ngay</a>
      </div>
      <img
        src="./img/slide.jpg"
        alt="Banner thời trang" />
    </section>

    <!-- Main Content -->
    <main class="container">
      <!-- TOP -->
      <section class="category">
        <h2>TOP</h2>
        <div class="products">
          <div class="product-card">
            <img src="./img/BLACKTEE.jpg" alt="black tee" />
            <h3>BCB BLACK TEE</h3>
            <div class="price">490.000₫</div>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(1, 'BCB BLACK TEE')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(1, 'BCB BLACK TEE')">Yêu thích</button>
            </div>
          </div>
          <div class="product-card">
            <img src="./img/WHITETEE.jpg" alt="white tee" />
            <h3>BCB WHITE TEE</h3>
            <div class="price">490.000₫</div>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(2, 'BCB WHITE TEE')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(2, 'BCB WHITE TEE')">Yêu thích</button>
            </div>
          </div>
          <div class="product-card">
            <img src="./img/REDTEE.jpg" alt="red tee" />
            <h3>BCB RED TEE</h3>
            <div class="price">490.000₫</div>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(3, 'BCB RED TEE')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(3, 'BCB RED TEE')">Yêu thích</button>
            </div>
          </div>
        </div>
      </section>

      <!-- BOTTOMS -->
      <section class="category">
        <h2>BOTTOMS</h2>
        <div class="products">
          <div class="product-card">
            <img src="./img/QUANSNITCHCLUB V2.jpg" alt="quần snit v2" />
            <h3>BCB SNITCHCLUB V2 DIRTY WASHED 3D DENIM PANTS</h3>
            <div class="price">890.000₫</div>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(4, 'BCB SNITCHCLUB V2 DIRTY WASHED 3D DENIM PANTS')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(4, 'BCB SNITCHCLUB V2 DIRTY WASHED 3D DENIM PANTS')">Yêu thích</button>
            </div>
          </div>
          <div class="product-card">
            <img src="./img/QUANASHOUT.jpg" alt="quần ash out" />
            <h3>BCB ASH OUT DENIM PANTS</h3>
            <div class="price">1.490.000₫</div>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(5, 'BCB ASH OUT DENIM PANTS')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(5, 'BCB ASH OUT DENIM PANTS')">Yêu thích</button>
            </div>
          </div>
          <div class="product-card">
            <img src="./img/QUANADDICTED.jpg" alt="quần addicted" />
            <h3>BCB ADDICTED FVR BC WASHED PANTS</h3>
            <div class="price">890.000₫</div>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(6, 'BCB ADDICTED FVR BC WASHED PANTS')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(6, 'BCB ADDICTED FVR BC WASHED PANTS')">Yêu thích</button>
            </div>
          </div>
        </div>
      </section>

      <!-- out wear -->
      <section class="category">
        <h2>OUTWEAR</h2>
        <div class="products">
          <div class="product-card">
            <img src="./img/aokhoacASHOUT.jpg" alt="áo khoác ash out" />
            <h3>BCB ASH OUT DENIM JACKET</h3>
            <div class="price">1.990.000₫</div>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(7, 'BCB ASH OUT DENIM JACKET')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(7, 'BCB ASH OUT DENIM JACKET')">Yêu thích</button>
            </div>
          </div>
          <div class="product-card">
            <img src="./img/aokhoacnavy.jpg" alt="áo khoác navy" />
            <h3> BCB Hoodie navy 02zipup hoodie</h3>
            <div class="price">750.000₫</div>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(8, 'BCB Hoodie navy 02zipup hoodie')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(8, 'BCB Hoodie navy 02zipup hoodie')">Yêu thích</button>
            </div>
          </div>
          <div class="product-card">
            <img src="./img/aokhoacADDICTED.jpg" alt="áo khoác addicted" />
            <h3>BCB ADDICTED FVR WASHED HOODIE</h3>
            <div class="price">990.000₫</div>
            <div class="mt-20">
              <button class="btn btn-primary" onclick="addToCart(9, 'BCB ADDICTED FVR WASHED HOODIE')">Thêm vào giỏ</button>
              <button class="btn btn-secondary" onclick="addToWishlist(9, 'BCB ADDICTED FVR WASHED HOODIE')">Yêu thích</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Xem thêm -->
      <div class="text-center mt-20">
        <button class="btn btn-primary" onclick="loadMore()">Xem Thêm Sản Phẩm</button>
      </div>
    </main>

  <?php endif; ?>

  <!-- Footer -->
  <footer>
    <div>
      <h3>VỀ CHÚNG TÔI</h3>
      <p>
        3 chàng lính ngự lâm (BCB) là thương hiệu thời trang dành cho mọi lứa tuổi,
        mang đến phong cách hiện đại và năng động.
      </p>
    </div>
    <div>
      <h3>DANH MỤC</h3>
      <p>TOP, BOTTOMS, OUTWEAR, ACCESSORIES</p>
    </div>
    <div>
      <h3>THÔNG TIN LIÊN HỆ</h3>
      <p>
        Email: support@vettins.vn<br />Hotline: 0397789902<br />Địa chỉ: 180 CAO LỖ PHƯỜNG 4 QUẬN 8 TP. Hồ
        Chí Minh
      </p>
    </div>
  </footer>

  <!-- JavaScript Configuration -->
  <?php include 'config-js.php'; ?>
  <script src="scripts.js"></script>
</body>

</html>