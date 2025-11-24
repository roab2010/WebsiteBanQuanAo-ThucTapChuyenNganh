<?php
// JavaScript Variables cho PHP
$site_name = '3 chàng lính ngự lâm';
$api_url = '/api/';
$is_logged_in = isset($_SESSION['user']) ? 'true' : 'false';
$current_user = isset($_SESSION['user']) ? $_SESSION['user'] : '';

// Set content type
header('Content-Type: application/javascript');
?>
// Dynamic JavaScript với PHP Variables
const SITE_CONFIG = {
  name: '<?php echo addslashes($site_name); ?>',
  apiUrl: '<?php echo $api_url; ?>',
  isLoggedIn: <?php echo $is_logged_in; ?>,
  currentUser: '<?php echo addslashes($current_user); ?>',
  primaryColor: '#d60000',
  secondaryColor: '#007bff'
};

// Login form handler (chỉ chạy nếu có form login)
document.addEventListener('DOMContentLoaded', function() {
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();

      if (username === "" || password === "") {
        showAlert("Vui lòng nhập đầy đủ tên tài khoản và mật khẩu.", 'error');
      } else {
        // Submit form thông thường để PHP xử lý
        this.submit();
      }
    });
  }
});

// Load more function
function loadMore() {
  if (SITE_CONFIG.isLoggedIn) {
    showAlert(`Chào ${SITE_CONFIG.currentUser}! Tính năng 'Xem thêm' đang được phát triển.`, 'info');
  } else {
    showAlert("Vui lòng đăng nhập để sử dụng tính năng này!", 'warning');
  }
}

// Search functionality
function performSearch(query) {
  if (query.trim() === '') {
    showAlert('Vui lòng nhập từ khóa tìm kiếm!', 'warning');
    return;
  }
  
  // Redirect to search results
  window.location.href = `trangchu.php?search=${encodeURIComponent(query)}`;
}

// Alert system
function showAlert(message, type = 'info') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type}`;
  alertDiv.textContent = message;
  
  // Insert at top of body
  document.body.insertBefore(alertDiv, document.body.firstChild);
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.parentNode.removeChild(alertDiv);
    }
  }, 5000);
}

// Cart functionality
function addToCart(productId, productName) {
  if (!SITE_CONFIG.isLoggedIn) {
    showAlert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!', 'warning');
    return;
  }
  
  showAlert(`Đã thêm "${productName}" vào giỏ hàng!`, 'success');
  
  // Update cart count (giả lập)
  updateCartCount();
}

// Update cart count
function updateCartCount() {
  const cartIcon = document.querySelector('.icon-link[title="Giỏ hàng"]');
  if (cartIcon) {
    // Giả lập cart count
    const currentCount = parseInt(cartIcon.dataset.count || '0');
    cartIcon.dataset.count = currentCount + 1;
    cartIcon.innerHTML = `🛒 (${cartIcon.dataset.count})`;
  }
}

// Wishlist functionality
function addToWishlist(productId, productName) {
  if (!SITE_CONFIG.isLoggedIn) {
    showAlert('Vui lòng đăng nhập để thêm sản phẩm vào yêu thích!', 'warning');
    return;
  }
  
  showAlert(`Đã thêm "${productName}" vào danh sách yêu thích!`, 'success');
}

// Search form handler
document.addEventListener('DOMContentLoaded', function() {
  const searchForm = document.querySelector('.search-box');
  if (searchForm) {
    const searchInput = searchForm.querySelector('.search-input');
    const searchBtn = searchForm.querySelector('.search-btn');
    
    if (searchBtn) {
      searchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        performSearch(searchInput.value);
      });
    }
    
    if (searchInput) {
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          performSearch(this.value);
        }
      });
    }
  }
});

// User session info
if (SITE_CONFIG.isLoggedIn) {
  console.log(`Chào mừng ${SITE_CONFIG.currentUser} đến với ${SITE_CONFIG.name}!`);
} else {
  console.log(`Chào mừng bạn đến với ${SITE_CONFIG.name}!`);
}

// Utility functions
function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price);
}

function formatDate(date) {
  return new Intl.DateTimeFormat('vi-VN').format(new Date(date));
}
