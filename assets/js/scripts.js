
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

function addToCart(productId, productName) {


  alert(`Bạn vừa bấm thêm "${productName}" vào giỏ (ID: ${productId}).\nChức năng này sẽ hoàn thiện ở bước tiếp theo! 🛒`);
}


function loadMore() {
  alert("Tính năng đang phát triển... Bạn hãy đợi nhé!");
}


function openModal(id, name, price, image, stock) {

  document.getElementById('modalId').value = id;
  document.getElementById('modalImg').src = image;
  document.getElementById('modalName').innerText = name;

  let formattedPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
  document.getElementById('modalPrice').innerText = formattedPrice;
  document.getElementById('modalLink').href = 'chitiet.php?id=' + id;
  document.getElementById('modalQty').value = 1;


  const stockLabel = document.getElementById('modalStockLabel');
  const buyForm = document.getElementById('modalBuyForm'); 
  const outOfStockMsg = document.getElementById('modalOutOfStockMsg'); 

  if (stock > 0) {

    stockLabel.innerHTML = `<span class="text-sm text-green-600 bg-green-100 px-2 py-1 rounded">Còn ${stock} sản phẩm</span>`;

    buyForm.classList.remove('hidden');
    outOfStockMsg.classList.add('hidden');


    document.getElementById('modalQty').setAttribute('max', stock);
  } else {
  
    stockLabel.innerHTML = `<span class="text-sm text-red-600 bg-red-100 px-2 py-1 rounded">HẾT HÀNG</span>`;
   
    buyForm.classList.add('hidden');
    outOfStockMsg.classList.remove('hidden');
  }


  document.getElementById('quickViewModal').classList.remove('hidden');
}


function closeModal() {
  document.getElementById('quickViewModal').classList.add('hidden');
}


function updateQty(change) {
  let qtyInput = document.getElementById('modalQty');
  let currentQty = parseInt(qtyInput.value);

  if (currentQty + change >= 1) {
    qtyInput.value = currentQty + change;
  }
}



function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');


  const toast = document.createElement('div');
  toast.classList.add('toast', type);


  let icon = '✅';
  if (type === 'error') icon = '❌';
  if (type === 'warning') icon = '⚠️';

  toast.innerHTML = `
        <div class="toast-icon">${icon}</div>
        <div class="toast-message">${message}</div>
    `;


  container.appendChild(toast);


  setTimeout(() => {
    toast.remove();
  }, 3500);
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



function execPostRequest($url, $data) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: '.strlen($data))
  );
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}