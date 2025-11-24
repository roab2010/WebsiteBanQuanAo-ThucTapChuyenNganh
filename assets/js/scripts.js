/* assets/js/scripts.js */

// ==============================================
// 1. CHỨC NĂNG YÊU THÍCH (Lưu vào LocalStorage)
// ==============================================
function addToWishlist(productId, productName) {

  let wishlist = JSON.parse(localStorage.getItem('myWishlist')) || [];


  let exists = wishlist.find(item => item.id === productId);

  if (exists) {
    showToast(`Sản phẩm "${productName}" đã có trong danh sách yêu thích!`, 'warning');
  } else {

    wishlist.push({ id: productId, name: productName });


    localStorage.setItem('myWishlist', JSON.stringify(wishlist));

    showToast(`Đã thêm "${productName}" vào yêu thích!`, 'success');
  }
}

// ==============================================
// 2. CHỨC NĂNG THÊM GIỎ HÀNG
// ==============================================
function addToCart(productId, productName) {
  // Vì database mới yêu cầu SIZE, nên tạm thời mình thông báo chọn size
  // Sau này khi làm trang chi tiết sản phẩm có chọn size, ta sẽ xử lý code này sau.

  /* Logic sắp tới sẽ làm:
     1. Kiểm tra user đã đăng nhập chưa?
     2. Lấy size khách chọn.
     3. Gửi Ajax về server PHP để lưu vào Database.
  */

  // Tạm thời hiện thông báo cho vui để biết nút bấm ăn
  alert(`Bạn vừa bấm thêm "${productName}" vào giỏ (ID: ${productId}).\nChức năng này sẽ hoàn thiện ở bước tiếp theo! 🛒`);
}

// ==============================================
// 3. CHỨC NĂNG XEM THÊM (Load More)
// ==============================================
function loadMore() {
  alert("Tính năng đang phát triển... Bạn hãy đợi nhé!");
}

// ==============================================
// 4. HIỆU ỨNG ALERT ĐẸP (Thay cho alert mặc định xấu xí - Tùy chọn)
// ==============================================
// Bạn có thể để trống phần này nếu thích dùng alert mặc định của trình duyệt


// 1. Hàm mở Modal
function openModal(id, name, price, image) {
  // Điền dữ liệu vào Modal
  document.getElementById('modalId').value = id;
  document.getElementById('modalImg').src = image;
  document.getElementById('modalName').innerText = name;

  // Format giá tiền cho đẹp (Ví dụ: 500000 -> 500.000₫)
  let formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
  document.getElementById('modalPrice').innerText = formattedPrice;

  // Cập nhật link "Xem chi tiết"
  document.getElementById('modalLink').href = 'chitiet.php?id=' + id;

  // Reset số lượng về 1
  document.getElementById('modalQty').value = 1;

  // Bỏ class hidden để hiện Modal
  document.getElementById('quickViewModal').classList.remove('hidden');
}

// 2. Hàm đóng Modal
function closeModal() {
  document.getElementById('quickViewModal').classList.add('hidden');
}

// 3. Hàm tăng giảm số lượng (+/-)
function updateQty(change) {
  let qtyInput = document.getElementById('modalQty');
  let currentQty = parseInt(qtyInput.value);

  if (currentQty + change >= 1) {
    qtyInput.value = currentQty + change;
  }
}



function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');

  // Tạo phần tử div
  const toast = document.createElement('div');
  toast.classList.add('toast', type);

  // Chọn icon dựa trên loại
  let icon = '✅';
  if (type === 'error') icon = '❌';
  if (type === 'warning') icon = '⚠️';

  toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-message">${message}</div>
    `;

  // Thêm vào container
  container.appendChild(toast);

  // Tự động xóa khỏi DOM sau 3.5s (để khớp với animation fadeOut)
  setTimeout(() => {
    toast.remove();
  }, 3500);
}



// Xóa yêu thích 
function removeFromWishlistPage(id, btn) {
  // BỎ ĐOẠN NÀY ĐI: if(!confirm('Bỏ sản phẩm này...?')) return;

  // Xóa trong LocalStorage
  let wishlist = JSON.parse(localStorage.getItem('myWishlist')) || [];
  wishlist = wishlist.filter(item => item.id !== id);
  localStorage.setItem('myWishlist', JSON.stringify(wishlist));

  // Xóa giao diện (Hiệu ứng mờ dần)
  const card = btn.closest('.product-card');
  card.style.transition = "all 0.3s ease";
  card.style.opacity = '0';
  card.style.transform = 'scale(0.9)';

  setTimeout(() => {
    card.remove();
    // Kiểm tra nếu hết sản phẩm thì hiện thông báo trống
    if (document.querySelectorAll('#wishlist-grid .product-card').length === 0) {
      document.getElementById('wishlist-grid').innerHTML = '<div class="text-center col-span-full py-10"><p class="text-gray-500 text-lg">Bạn chưa yêu thích sản phẩm nào.</p></div>';
    }
  }, 300);

  // Dùng Toast đẹp thay cho alert
  showToast('Đã xóa sản phẩm khỏi danh sách yêu thích', 'success');
}