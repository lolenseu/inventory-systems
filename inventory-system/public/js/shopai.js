// Slideshow and Dots - keep existing
let slideIndex = 0;
let slideInterval;
showSlides();

function showSlides() {
    let i;
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";  
    }
    slideIndex++;
    if (slideIndex > slides.length) {slideIndex = 1}    
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex-1].style.display = "block";  
    dots[slideIndex-1].className += " active";
    slideInterval = setTimeout(showSlides, 2000);
}

function currentSlide(n) {
    clearTimeout(slideInterval);
    slideIndex = n;
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";  
    }
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex-1].style.display = "block";  
    dots[slideIndex-1].className += " active";
    slideInterval = setTimeout(showSlides, 5000);
}

function scrollToTop() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}

// New code starts here
document.addEventListener('DOMContentLoaded', function() {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Navigation links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const section = this.getAttribute('data-section') + '-section';
            document.getElementById(section).scrollIntoView({ behavior: 'smooth' });
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Search functionality - Enhanced with API call
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    
    // Search on button click
    searchBtn.addEventListener('click', performSearch);
    
    // Search on enter key
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    async function performSearch() {
        const query = searchInput.value.trim();
        
        if (!query) {
            showMessage('Please enter a search query', 'warning');
            return;
        }

        try {
            const response = await fetch(`/shopai/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update products section with search results
                updateProductsDisplay(data.products, query);
                showMessage(`Found ${data.count} products for "${query}"`, 'success');
            } else {
                showMessage(data.error || 'Search failed', 'error');
                // Show all products if search fails
                window.location.reload();
            }
        } catch (err) {
            showMessage('Search service unavailable', 'error');
        }
    }

    function updateProductsDisplay(products, query) {
        const productsContainer = document.querySelector('.product-grid');
        productsContainer.innerHTML = '';
        
        if (products.length === 0) {
            productsContainer.innerHTML = '<p class="no-products">No products found for your search.</p>';
            return;
        }
        
        products.forEach(product => {
            const productDiv = document.createElement('div');
            productDiv.className = 'product';
            productDiv.dataset.productId = product.id;
            
            productDiv.innerHTML = `
                <div class="product-image">
                    <img src="${product.image_url || '/img/default-product.jpg'}" alt="${product.name}">
                    ${product.quantity <= 0 ? '<div class="out-of-stock">Out of Stock</div>' : ''}
                </div>
                <h3>${product.name}</h3>
                <p class="price">₱ ${Number(product.price).toLocaleString('en-PH')}</p>
                ${product.quantity > 0 ? 
                    `<button class="view-product-btn" onclick="openProductModal(${product.id})">View Details</button>` :
                    `<button class="view-product-btn disabled">Out of Stock</button>`
                }
            `;
            
            productsContainer.appendChild(productDiv);
        });
    }

    // Back to top - fixed ID
    window.onscroll = function() {
        const backToTopButton = document.getElementById("backToTop");
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            backToTopButton.style.display = "block";
        } else {
            backToTopButton.style.display = "none";
        }
    };

    // Message function
    function showMessage(msg, type = 'success') {
        const container = document.getElementById('message-container');
        const message = document.createElement('div');
        message.className = `message ${type}`;
        message.textContent = msg;
        container.appendChild(message);
        setTimeout(() => message.remove(), 5000);
    }

    // Cart functions
    const cartCount = document.querySelector('.cart-count');
    function updateCartCount() {
        const customer = JSON.parse(localStorage.getItem('customer') || 'null');
        if (customer) {
            // Use server cart
            fetchCartFromServer();
        } else {
            // Use local storage cart
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
        }
    }

    async function fetchCartFromServer() {
        try {
            const response = await fetch('/shopai/cart', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const totalItems = data.cart_items.reduce((sum, item) => sum + item.quantity, 0);
                cartCount.textContent = totalItems;
            }
        } catch (err) {
            console.error('Error fetching cart:', err);
        }
    }

    // Auth functions
    const authBtn = document.getElementById('authBtn');
    const customer = localStorage.getItem('customer');
    if (customer) {
        const cust = JSON.parse(customer);
        authBtn.textContent = `Hi, ${cust.full_name}`;
    }

    authBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const customerStr = localStorage.getItem('customer');
        if (customerStr) {
            if (confirm('Are you sure you want to log out?')) {
                localStorage.removeItem('customer');
                localStorage.removeItem('cart');
                this.textContent = 'Login / Signup';
                updateCartCount();
                showMessage('Logged out successfully!');
            }
        } else {
            document.getElementById('authModal').style.display = 'block';
        }
    });

    // Auth modal close
    const authModal = document.getElementById('authModal');
    document.getElementById('closeAuthModal').addEventListener('click', () => authModal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target === authModal) authModal.style.display = 'none';
    });

    // Auth form switch
    document.getElementById('showRegister').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('registerForm').style.display = 'block';
    });
    document.getElementById('showLogin').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('registerForm').style.display = 'none';
        document.getElementById('loginForm').style.display = 'block';
    });

    // Login submit
    document.getElementById('loginFormElement').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('/shopai/login', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                }
            });
            const data = await res.json();
            if (data.success) {
                localStorage.setItem('customer', JSON.stringify(data.customer));
                authBtn.textContent = `Hi, ${data.customer.full_name}`;
                authModal.style.display = 'none';
                updateCartCount();
                showMessage(data.message || 'Login successful!');
                e.target.reset();
            } else {
                showMessage(data.error || 'Login failed', 'error');
            }
        } catch (err) {
            showMessage('Network error', 'error');
        }
    });

    // Register submit
    document.getElementById('registerFormElement').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('/shopai/register', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                }
            });
            const data = await res.json();
            if (data.success) {
                localStorage.setItem('customer', JSON.stringify(data.customer));
                authBtn.textContent = `Hi, ${data.customer.full_name}`;
                authModal.style.display = 'none';
                updateCartCount();
                showMessage(data.message || 'Registration successful!');
                e.target.reset();
            } else {
                let errMsg = 'Registration failed';
                if (data.errors) {
                    errMsg = Object.values(data.errors)[0][0];
                } else if (data.error) {
                    errMsg = data.error;
                }
                showMessage(errMsg, 'error');
            }
        } catch (err) {
            showMessage('Network error', 'error');
        }
    });

    // Product modal
    const productModal = document.getElementById('productModal');
    document.getElementById('closeProductModal').addEventListener('click', () => productModal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target === productModal) productModal.style.display = 'none';
    });

    window.openProductModal = async function(id) {
        try {
            const res = await fetch(`/shopai/product/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                const p = data.product;
                document.getElementById('modalProductImage').src = p.image_url || '/img/default-product.jpg';
                document.getElementById('modalProductName').textContent = p.name;
                document.getElementById('modalProductPrice').textContent = `₱ ${parseFloat(p.price).toLocaleString('en-PH')}`;
                document.getElementById('modalProductDescription').textContent = p.description || 'No description available.';
                const qtyEl = document.getElementById('quantity');
                qtyEl.max = p.quantity;
                qtyEl.value = 1;
                productModal.dataset.productId = id;
                productModal.style.display = 'block';
            } else {
                showMessage(data.error || 'Product not found', 'error');
            }
        } catch (err) {
            showMessage('Error loading product', 'error');
        }
    };

    // Add to cart - Enhanced for server cart
    document.getElementById('addToCartBtn').addEventListener('click', async function() {
        const customer = JSON.parse(localStorage.getItem('customer') || 'null');
        if (!customer) {
            showMessage('Please login to add items to cart', 'warning');
            productModal.style.display = 'none';
            authModal.style.display = 'block';
            return;
        }

        const id = productModal.dataset.productId;
        const qty = parseInt(document.getElementById('quantity').value);
        if (!id || qty <= 0) return;

        try {
            const response = await fetch('/shopai/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    product_id: id,
                    quantity: qty
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showMessage(`${qty} item(s) added to cart!`, 'success');
                productModal.style.display = 'none';
                updateCartCount();
            } else {
                showMessage(data.error || 'Failed to add to cart', 'error');
            }
        } catch (err) {
            showMessage('Network error', 'error');
        }
    });

    // Buy now - Enhanced
    document.querySelector('.buy-now-btn').addEventListener('click', async function() {
        const customer = JSON.parse(localStorage.getItem('customer') || 'null');
        if (!customer) {
            showMessage('Please login to purchase', 'warning');
            productModal.style.display = 'none';
            authModal.style.display = 'block';
            return;
        }

        const id = productModal.dataset.productId;
        const qty = parseInt(document.getElementById('quantity').value);
        if (!id || qty <= 0) return;

        try {
            const response = await fetch('/shopai/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    product_id: id,
                    quantity: qty
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showMessage('Item added to cart, proceeding to checkout', 'success');
                productModal.style.display = 'none';
                updateCartCount();
                // Open cart modal
                loadCartItems();
                document.getElementById('cartModal').style.display = 'block';
            } else {
                showMessage(data.error || 'Failed to add to cart', 'error');
            }
        } catch (err) {
            showMessage('Network error', 'error');
        }
    });

    // Cart modal
    const cartModal = document.getElementById('cartModal');
    const cartBtn = document.getElementById('cartBtn');
    document.getElementById('closeCartModal').addEventListener('click', () => cartModal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target === cartModal) cartModal.style.display = 'none';
    });
    cartBtn.addEventListener('click', function() {
        loadCartItems();
        cartModal.style.display = 'block';
    });

    async function loadCartItems() {
        const customer = JSON.parse(localStorage.getItem('customer') || 'null');
        if (!customer) {
            // Use local storage cart
            loadLocalCart();
        } else {
            // Use server cart
            loadServerCart();
        }
    }

    function loadLocalCart() {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const cartItemsEl = document.getElementById('cartItems');
        cartItemsEl.innerHTML = '';
        let subtotal = 0;
        cart.forEach((item, index) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'cart-item';
            itemDiv.innerHTML = `
                <div class="cart-item-image"><img src="${item.image}" alt="${item.name}"></div>
                <div class="cart-item-details">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">₱ ${item.price.toLocaleString()}</div>
                    <div class="cart-item-quantity">
                        Qty: <input type="number" min="1" value="${item.quantity}" onchange="updateLocalCartItem(${index}, this.value)">
                    </div>
                </div>
                <button class="cart-item-remove" onclick="removeLocalCartItem(${index})">&times;</button>
            `;
            cartItemsEl.appendChild(itemDiv);
            subtotal += item.price * item.quantity;
        });
        document.getElementById('cartSubtotal').textContent = `₱ ${subtotal.toLocaleString()}`;
        document.getElementById('cartTotal').textContent = `₱ ${subtotal.toLocaleString()}`;
    }

    async function loadServerCart() {
        try {
            const response = await fetch('/shopai/cart', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                const cartItemsEl = document.getElementById('cartItems');
                cartItemsEl.innerHTML = '';
                let subtotal = 0;
                
                data.cart_items.forEach((item, index) => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'cart-item';
                    itemDiv.innerHTML = `
                        <div class="cart-item-image"><img src="${item.product.image_url || '/img/default-product.jpg'}" alt="${item.product.name}"></div>
                        <div class="cart-item-details">
                            <div class="cart-item-name">${item.product.name}</div>
                            <div class="cart-item-price">₱ ${item.product.price.toLocaleString()}</div>
                            <div class="cart-item-quantity">
                                Qty: <input type="number" min="1" max="${item.product.quantity}" value="${item.quantity}" onchange="updateServerCartItem(${item.id}, this.value)">
                            </div>
                        </div>
                        <button class="cart-item-remove" onclick="removeServerCartItem(${item.id})">&times;</button>
                    `;
                    cartItemsEl.appendChild(itemDiv);
                    subtotal += item.product.price * item.quantity;
                });
                
                document.getElementById('cartSubtotal').textContent = `₱ ${subtotal.toLocaleString()}`;
                document.getElementById('cartTotal').textContent = `₱ ${subtotal.toLocaleString()}`;
            }
        } catch (err) {
            console.error('Error loading server cart:', err);
        }
    }

    // Global cart functions
    window.updateLocalCartItem = function(index, qtyStr) {
        const qty = parseInt(qtyStr);
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        if (qty > 0) {
            cart[index].quantity = qty;
        } else {
            cart.splice(index, 1);
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        loadLocalCart();
    };

    window.removeLocalCartItem = function(index) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        loadLocalCart();
    };

    window.updateServerCartItem = async function(cartId, qtyStr) {
        const qty = parseInt(qtyStr);
        if (qty <= 0) return;

        try {
            const response = await fetch(`/shopai/cart/${cartId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    quantity: qty
                })
            });

            const data = await response.json();
            
            if (data.success) {
                showMessage('Cart updated successfully', 'success');
                loadCartItems();
            } else {
                showMessage(data.error || 'Failed to update cart', 'error');
            }
        } catch (err) {
            showMessage('Network error', 'error');
        }
    };

    window.removeServerCartItem = async function(cartId) {
        if (!confirm('Are you sure you want to remove this item?')) {
            return;
        }

        try {
            const response = await fetch(`/shopai/cart/${cartId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                }
            });

            const data = await response.json();
            
            if (data.success) {
                showMessage('Item removed from cart', 'success');
                loadCartItems();
            } else {
                showMessage(data.error || 'Failed to remove item', 'error');
            }
        } catch (err) {
            showMessage('Network error', 'error');
        }
    };

    // Checkout modal
    const checkoutModal = document.getElementById('checkoutModal');
    const ordersModal = document.getElementById('ordersModal');
    
    // Checkout button
    document.querySelector('.checkout-btn').addEventListener('click', function() {
        const customer = JSON.parse(localStorage.getItem('customer') || 'null');
        if (!customer) {
            showMessage('Please login to checkout', 'warning');
            cartModal.style.display = 'none';
            authModal.style.display = 'block';
            return;
        }
        
        loadCartItems();
        loadCheckoutSummary();
        cartModal.style.display = 'none';
        checkoutModal.style.display = 'block';
    });

    function loadCheckoutSummary() {
        const customer = JSON.parse(localStorage.getItem('customer') || 'null');
        if (!customer) return;

        fetch('/shopai/cart', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let subtotal = 0;
                data.cart_items.forEach(item => {
                    subtotal += item.product.price * item.quantity;
                });
                
                document.getElementById('checkoutSubtotal').textContent = `₱ ${subtotal.toLocaleString()}`;
                document.getElementById('checkoutTotal').textContent = `₱ ${subtotal.toLocaleString()}`;
            }
        })
        .catch(err => {
            console.error('Error loading checkout summary:', err);
        });
    }

    // Checkout form submit
    document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const orderData = {
            shipping_address: formData.get('shipping_address'),
            phone: formData.get('phone'),
            notes: formData.get('notes')
        };

        try {
            const response = await fetch('/shopai/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(orderData)
            });

            const data = await response.json();
            
            if (data.success) {
                showMessage(`Order placed successfully! Order #${data.order_number}`, 'success');
                checkoutModal.style.display = 'none';
                e.target.reset();
                updateCartCount();
            } else {
                showMessage(data.error || 'Checkout failed', 'error');
            }
        } catch (err) {
            showMessage('Network error', 'error');
        }
    });

    // Close checkout modal
    document.getElementById('closeCheckoutModal').addEventListener('click', () => checkoutModal.style.display = 'none');
    document.getElementById('cancelCheckoutBtn').addEventListener('click', () => checkoutModal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target === checkoutModal) checkoutModal.style.display = 'none';
    });

    // Orders modal
    document.getElementById('continueShoppingBtn').addEventListener('click', () => cartModal.style.display = 'none');

    // Load orders
    async function loadOrders() {
        const customer = JSON.parse(localStorage.getItem('customer') || 'null');
        if (!customer) return;

        try {
            const response = await fetch('/shopai/orders', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                }
            });

            const data = await response.json();
            
            if (data.success) {
                const ordersList = document.getElementById('ordersList');
                ordersList.innerHTML = '';
                
                if (data.orders.length === 0) {
                    ordersList.innerHTML = '<p class="no-orders">No orders found.</p>';
                    return;
                }
                
                data.orders.forEach(order => {
                    const orderDiv = document.createElement('div');
                    orderDiv.className = 'order-item';
                    orderDiv.innerHTML = `
                        <div class="order-header">
                            <div class="order-info">
                                <h4>Order #${order.order_number}</h4>
                                <span class="order-date">${new Date(order.created_at).toLocaleDateString()}</span>
                                <span class="badge ${order.status_badge.color}">${order.status_badge.text}</span>
                            </div>
                            <div class="order-total">
                                <strong>Total: ₱ ${Number(order.total_amount).toLocaleString()}</strong>
                            </div>
                        </div>
                        <div class="order-items">
                            ${order.items.map(item => `
                                <div class="order-item-details">
                                    <img src="${item.product.image_url || '/img/default-product.jpg'}" alt="${item.product.name}" class="order-item-image">
                                    <div class="order-item-info">
                                        <span class="order-item-name">${item.product.name}</span>
                                        <span class="order-item-quantity">Qty: ${item.quantity}</span>
                                        <span class="order-item-price">₱ ${Number(item.price).toLocaleString()}</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    ordersList.appendChild(orderDiv);
                });
            }
        } catch (err) {
            console.error('Error loading orders:', err);
        }
    }

    // Orders button - Add this to your navigation or header
    // You can add a button like: <button id="ordersBtn">My Orders</button>
    if (document.getElementById('ordersBtn')) {
        document.getElementById('ordersBtn').addEventListener('click', function() {
            const customer = JSON.parse(localStorage.getItem('customer') || 'null');
            if (!customer) {
                showMessage('Please login to view orders', 'warning');
                authModal.style.display = 'block';
                return;
            }
            loadOrders();
            ordersModal.style.display = 'block';
        });
    }

    // Close orders modal
    document.getElementById('closeOrdersModal').addEventListener('click', () => ordersModal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target === ordersModal) ordersModal.style.display = 'none';
    });

    // Init cart count
    updateCartCount();
});