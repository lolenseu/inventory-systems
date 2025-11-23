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

    // Search functionality
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    searchBtn.addEventListener('click', function() {
        const query = searchInput.value.toLowerCase().trim();
        const products = document.querySelectorAll('#products-section .product');
        let visibleCount = 0;
        products.forEach(product => {
            const name = product.querySelector('h3').textContent.toLowerCase();
            if (query === '' || name.includes(query)) {
                product.style.display = 'block';
                visibleCount++;
            } else {
                product.style.display = 'none';
            }
        });
        if (query !== '' && visibleCount === 0) {
            showMessage('No products found', 'warning');
        }
    });

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
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
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
                this.textContent = 'Login / Signup';
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
                showMessage(data.message || 'Login successful!');
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
                showMessage(data.message || 'Registration successful!');
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

    // Add to cart
    document.getElementById('addToCartBtn').addEventListener('click', function() {
        const id = productModal.dataset.productId;
        const qty = parseInt(document.getElementById('quantity').value);
        if (!id || qty <= 0) return;
        const name = document.getElementById('modalProductName').textContent;
        const priceStr = document.getElementById('modalProductPrice').textContent.replace(/[₱ ,]/g, '');
        const price = parseFloat(priceStr);
        const img = document.getElementById('modalProductImage').src;
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existingIdx = cart.findIndex(item => item.id == id);
        if (existingIdx > -1) {
            cart[existingIdx].quantity += qty;
        } else {
            cart.push({id, name, price, image: img, quantity: qty});
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        showMessage(`${qty} item(s) added to cart!`);
        productModal.style.display = 'none';
    });

    // Buy now
    document.querySelector('.buy-now-btn').addEventListener('click', function() {
        const id = productModal.dataset.productId;
        const qty = parseInt(document.getElementById('quantity').value);
        if (!id || qty <= 0) return;
        const name = document.getElementById('modalProductName').textContent;
        const priceStr = document.getElementById('modalProductPrice').textContent.replace(/[₱ ,]/g, '');
        const price = parseFloat(priceStr);
        const img = document.getElementById('modalProductImage').src;
        const cart = [{id, name, price, image: img, quantity: qty}];
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount();
        showMessage('Proceeding to checkout');
        productModal.style.display = 'none';
        // Open cart modal
        loadCartItems();
        document.getElementById('cartModal').style.display = 'block';
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

    function loadCartItems() {
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
                        Qty: <input type="number" min="1" value="${item.quantity}" onchange="updateCartItem(${index}, this.value)">
                    </div>
                </div>
                <button class="cart-item-remove" onclick="removeCartItem(${index})">&times;</button>
            `;
            cartItemsEl.appendChild(itemDiv);
            subtotal += item.price * item.quantity;
        });
        document.getElementById('cartSubtotal').textContent = `₱ ${subtotal.toLocaleString()}`;
        document.getElementById('cartTotal').textContent = `₱ ${subtotal.toLocaleString()}`;
        updateCartCount();
    }

    // Global cart functions
    window.updateCartItem = function(index, qtyStr) {
        const qty = parseInt(qtyStr);
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        if (qty > 0) {
            cart[index].quantity = qty;
        } else {
            cart.splice(index, 1);
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCartItems();
    };

    window.removeCartItem = function(index) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCartItems();
    };

    // Checkout
    document.querySelector('.checkout-btn').addEventListener('click', function() {
        const customer = localStorage.getItem('customer');
        if (!customer) {
            showMessage('Please login to checkout', 'warning');
            cartModal.style.display = 'none';
            authModal.style.display = 'block';
            return;
        }
        showMessage('Redirecting to payment... (Implement full checkout)', 'warning');
        // localStorage.removeItem('cart'); or post to server
    });

    document.getElementById('continueShoppingBtn').addEventListener('click', () => cartModal.style.display = 'none');

    // Init cart count
    updateCartCount();
});
