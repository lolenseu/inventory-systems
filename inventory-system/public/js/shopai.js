document.addEventListener('DOMContentLoaded', () => {

  /*  CONSTANTS  */
  const token = document.querySelector('meta[name="csrf-token"]').content;
  const searchInput = document.getElementById('search-input');
  const searchBtn = document.getElementById('search-btn');
  const authBtn = document.getElementById('authBtn');
  const cartCount = document.querySelector('.cart-count');
  const authModal = document.getElementById('authModal');
  const productModal = document.getElementById('productModal');
  const cartModal = document.getElementById('cartModal');
  const checkoutModal = document.getElementById('checkoutModal');

  /*  UTILITIES  */
  function qs(s) { return document.querySelector(s); }
  function qsa(s) { return document.querySelectorAll(s); }
  function showModal(el) { el.style.display = 'block'; }
  function hideModal(el) { el.style.display = 'none'; }

  function showMessage(msg, type = 'success') {
    const box = qs('#message-container');
    const div = document.createElement('div');
    div.className = `message ${type}`;
    div.textContent = msg;
    box.appendChild(div);
    setTimeout(() => div.remove(), 5000);
  }

  async function fetchJSON(url, options = {}) {
    const res = await fetch(url, options);
    return await res.json();
  }

  /*  NAVIGATION  */
  qsa('.nav-link').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      qsa('.nav-link').forEach(l => l.classList.remove('active'));
      link.classList.add('active');
      const section = link.dataset.section + '-section';
      document.getElementById(section).scrollIntoView({ behavior: 'smooth' });
    });
  });

  /*  SEARCH  */
  searchBtn.addEventListener('click', doSearch);
  searchInput.addEventListener('keypress', e => e.key === 'Enter' && doSearch());

  async function doSearch() {
    const query = searchInput.value.trim();
    if (!query) return showMessage('Please enter a search query', 'warning');

    const data = await fetchJSON(`/shopai/search?q=${encodeURIComponent(query)}`);
    if (!data.success) return showMessage(data.error || 'Search failed', 'error');

    updateProducts(data.products);
    showMessage(`Found ${data.count} products for "${query}"`);
  }

  function updateProducts(products) {
    const container = qs('.product-grid');
    container.innerHTML = '';

    if (products.length === 0) {
      container.innerHTML = `<p class="no-products">No products found.</p>`;
      return;
    }

    products.forEach(p => {
      const div = document.createElement('div');
      div.className = 'product';
      div.dataset.productId = p.id;
      div.innerHTML = `
        <div class="product-image">
          <img src="${p.image_url || '/img/default-product.jpg'}">
          ${p.quantity <= 0 ? '<div class="out-of-stock">Out of Stock</div>' : ''}
        </div>
        <h3>${p.name}</h3>
        <p class="price">₱ ${Number(p.price).toLocaleString('en-PH')}</p>
        <button class="view-product-btn ${p.quantity <= 0 ? 'disabled' : ''}"
          ${p.quantity > 0 ? `onclick="openProductModal(${p.id})"` : ''}>
          ${p.quantity > 0 ? 'View Details' : 'Out of Stock'}
        </button>
      `;
      container.appendChild(div);
    });
  }

  /*  AUTH  */
  const savedCustomer = JSON.parse(localStorage.getItem('customer') || 'null');
  if (savedCustomer) authBtn.textContent = `Hi, ${savedCustomer.full_name}`;

  authBtn.addEventListener('click', () => {
    const cust = localStorage.getItem('customer');
    if (!cust) return showModal(authModal);

    if (confirm('Are you sure you want to log out?')) {
      localStorage.removeItem('customer');
      localStorage.removeItem('cart');
      authBtn.textContent = 'Login / Signup';
      updateCartCount();
      showMessage('Logged out successfully');
    }
  });

  qs('#closeAuthModal').onclick = () => hideModal(authModal);
  window.onclick = e => { if (e.target === authModal) hideModal(authModal); };

  qs('#showRegister').onclick = e => {
    e.preventDefault();
    qs('#loginForm').style.display = 'none';
    qs('#registerForm').style.display = 'block';
  };
  qs('#showLogin').onclick = e => {
    e.preventDefault();
    qs('#registerForm').style.display = 'none';
    qs('#loginForm').style.display = 'block';
  };

  qs('#loginFormElement').onsubmit = async e => {
    e.preventDefault();
    const data = await fetchJSON('/shopai/login', {
      method: 'POST',
      body: new FormData(e.target),
      headers: { 'X-CSRF-TOKEN': token }
    });

    if (!data.success) return showMessage(data.error || 'Login failed', 'error');

    localStorage.setItem('customer', JSON.stringify(data.customer));
    authBtn.textContent = `Hi, ${data.customer.full_name}`;
    hideModal(authModal);
    updateCartCount();
    showMessage(data.message || 'Login successful');
  };

  qs('#registerFormElement').onsubmit = async e => {
    e.preventDefault();
    const data = await fetchJSON('/shopai/register', {
      method: 'POST',
      body: new FormData(e.target),
      headers: { 'X-CSRF-TOKEN': token }
    });

    if (!data.success) {
      const msg = data.errors ? Object.values(data.errors)[0][0] : data.error;
      return showMessage(msg || 'Registration failed', 'error');
    }

    localStorage.setItem('customer', JSON.stringify(data.customer));
    authBtn.textContent = `Hi, ${data.customer.full_name}`;
    hideModal(authModal);
    updateCartCount();
    showMessage(data.message || 'Registration successful');
  };

  /*  PRODUCT MODAL  */
  qs('#closeProductModal').onclick = () => hideModal(productModal);
  window.addEventListener('click', e => { if (e.target === productModal) hideModal(productModal); });

  window.openProductModal = async id => {
    const data = await fetchJSON(`/shopai/product/${id}`);
    if (!data.success) return showMessage('Product not found', 'error');

    const p = data.product;
    qs('#modalProductImage').src = p.image_url || '/img/default-product.jpg';
    qs('#modalProductName').textContent = p.name;
    qs('#modalProductPrice').textContent = `₱ ${parseFloat(p.price).toLocaleString('en-PH')}`;
    qs('#modalProductDescription').textContent = p.description || 'No description available.';
    const qty = qs('#quantity');
    qty.max = p.quantity;
    qty.value = 1;

    productModal.dataset.productId = id;
    showModal(productModal);
  };

  /*  CART COUNT  */
  async function updateCartCount() {
    const customer = JSON.parse(localStorage.getItem('customer') || 'null');

    if (!customer) {
      const local = JSON.parse(localStorage.getItem('cart') || '[]');
      cartCount.textContent = local.reduce((s, i) => s + i.quantity, 0);
      return;
    }

    const data = await fetchJSON('/shopai/cart', { headers: { 'X-CSRF-TOKEN': token } });
    if (data.success) {
      cartCount.textContent = data.cart_items.reduce((s, i) => s + i.quantity, 0);
    }
  }

  /*  ADD TO CART  */
  qs('#addToCartBtn').onclick = () => addToCart(false);
  qs('.buy-now-btn').onclick = () => addToCart(true);

  async function addToCart(openCheckout) {
    const customer = JSON.parse(localStorage.getItem('customer') || 'null');
    if (!customer) {
      hideModal(productModal);
      showModal(authModal);
      return showMessage('Please login', 'warning');
    }

    const id = productModal.dataset.productId;
    const qty = parseInt(qs('#quantity').value);

    const data = await fetchJSON('/shopai/cart/add', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify({ product_id: id, quantity: qty })
    });

    if (!data.success) return showMessage('Failed to add to cart', 'error');

    showMessage('Item added to cart');
    hideModal(productModal);
    updateCartCount();

    if (openCheckout) {
      await loadCartItems();
      showModal(cartModal);
    }
  }

  /*  CART MODAL  */
  qs('#closeCartModal').onclick = () => hideModal(cartModal);
  qs('#cartBtn').onclick = () => { loadCartItems(); showModal(cartModal); };
  window.addEventListener('click', e => { if (e.target === cartModal) hideModal(cartModal); });

  async function loadCartItems() {
    const customer = JSON.parse(localStorage.getItem('customer') || 'null');
    if (!customer) return loadLocalCart();

    return loadServerCart();
  }

  /* ---------- Local Cart ---------- */
  function loadLocalCart() {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    renderCart(cart.map((c, i) => ({
      id: i,
      name: c.name,
      image: c.image,
      price: c.price,
      quantity: c.quantity,
      stock: 9999,
      isLocal: true
    })));
  }

  window.updateLocalCartItem = (i, qty) => {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    qty = parseInt(qty);
    if (qty <= 0) cart.splice(i, 1); else cart[i].quantity = qty;
    localStorage.setItem('cart', JSON.stringify(cart));
    loadLocalCart();
  };

  window.removeLocalCartItem = i => {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.splice(i, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadLocalCart();
  };

  /* ---------- Server Cart ---------- */
  async function loadServerCart() {
    const data = await fetchJSON('/shopai/cart', { headers: { 'X-CSRF-TOKEN': token } });
    if (!data.success) return;

    const mapped = data.cart_items.map(item => ({
      id: item.id,
      name: item.product.name,
      image: item.product.image_url,
      price: item.product.price,
      quantity: item.quantity,
      stock: item.product.quantity,
      isLocal: false
    }));

    renderCart(mapped);
  }

  window.updateServerCartItem = async (id, qty) => {
    qty = parseInt(qty);
    if (qty <= 0) return;

    const data = await fetchJSON(`/shopai/cart/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify({ quantity: qty })
    });

    if (!data.success) return showMessage('Failed to update cart', 'error');
    showMessage('Cart updated');
    loadCartItems();
  };

  window.removeServerCartItem = async id => {
    if (!confirm('Remove this item?')) return;

    const data = await fetchJSON(`/shopai/cart/${id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': token }
    });

    if (!data.success) return showMessage('Failed to remove', 'error');
    showMessage('Item removed');
    loadCartItems();
  };

  /* ---------- Render Cart ---------- */
  function renderCart(items) {
    const el = qs('#cartItems');
    el.innerHTML = '';
    let subtotal = 0;

    items.forEach((item, index) => {
      subtotal += item.price * item.quantity;

      el.innerHTML += `
        <div class="cart-item">
          <div class="cart-item-image"><img src="${item.image || '/img/default-product.jpg'}"></div>
          <div class="cart-item-details">
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-price">₱ ${item.price.toLocaleString()}</div>
            <div class="cart-item-quantity">
              Qty: <input type="number" min="1" max="${item.stock}"
                value="${item.quantity}"
                onchange="${item.isLocal ?
                  `updateLocalCartItem(${index}, this.value)` :
                  `updateServerCartItem(${item.id}, this.value)`}">
            </div>
          </div>
          <button class="cart-item-remove"
            onclick="${item.isLocal ?
              `removeLocalCartItem(${index})` :
              `removeServerCartItem(${item.id})`}">&times;</button>
        </div>
      `;
    });

    qs('#cartSubtotal').textContent = `₱ ${subtotal.toLocaleString()}`;
    qs('#cartTotal').textContent = `₱ ${subtotal.toLocaleString()}`;
  }

  /*  CHECKOUT  */
  qs('.checkout-btn').onclick = () => {
    const customer = JSON.parse(localStorage.getItem('customer') || 'null');
    if (!customer) {
      hideModal(cartModal);
      showModal(authModal);
      return showMessage('Please login');
    }

    loadCartItems();
    updateCheckoutSummary();
    hideModal(cartModal);
    showModal(checkoutModal);
  };

  async function updateCheckoutSummary() {
    const data = await fetchJSON('/shopai/cart', { headers: { 'X-CSRF-TOKEN': token } });
    if (!data.success) return;

    let subtotal = data.cart_items.reduce((s, i) => s + i.product.price * i.quantity, 0);
    qs('#checkoutSubtotal').textContent = `₱ ${subtotal.toLocaleString()}`;
    qs('#checkoutTotal').textContent = `₱ ${subtotal.toLocaleString()}`;
  }

  qs('#checkoutForm').onsubmit = async e => {
    e.preventDefault();

    const fd = new FormData(e.target);
    const body = {
      shipping_address: fd.get('shipping_address'),
      phone: fd.get('phone'),
      notes: fd.get('notes')
    };

    const data = await fetchJSON('/shopai/checkout', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify(body)
    });

    if (!data.success) return showMessage('Checkout failed', 'error');

    showMessage('Order placed successfully');
    hideModal(checkoutModal);
    updateCartCount();
  };

});
