<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ShopAI</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <link href="{{ asset('css/shopai.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <header class="navbar">
      <div class="logo">SHOPAI</div>
      <div class="search-container">
        <input type="text" placeholder="Search products..." id="search-input">
        <button id="search-btn">Search</button>
      </div>
      <ul class="nav-links">
        <li><a href="#home-section" class="nav-link active" data-section="home">Home</a></li>
        <li><a href="#products-section" class="nav-link" data-section="products">Products</a></li>
        <li><a href="#services-section" class="nav-link" data-section="services">Services</a></li>
        <li><a href="#brands-section" class="nav-link" data-section="brands">Brands</a></li>
        <li><a href="#about-section" class="nav-link" data-section="about">About</a></li>
        <li><a href="#faq-section" class="nav-link" data-section="faq">FAQ</a></li>
        <li><a href="#contact-section" class="nav-link" data-section="contact">Contact</a></li>
      </ul>
      <div class="header-actions">
        <button class="cart-btn" id="cartBtn">
          <i class="fa fa-shopping-cart"></i>
          <span class="cart-count">0</span>
        </button>
        <button class="auth-btn" id="authBtn">Login / Signup</button>
      </div>
    </header>

    <div class="first-container" id="home-section">
      <div class="slideshow-container">
        @forelse($slideshowProducts as $index => $product)
        <div class="mySlides fade">
          <img src="{{ $product->image_url ?? asset('img/product' . (($index % 5) + 1) . '.jpeg') }}" alt="{{ $product->name }}">
          <div class="slide-content">
            <h1>{{ $product->name }}</h1>
            <p>{{ Str::limit($product->description ?? 'Premium Quality Product', 80) }}</p>
            <div class="slide-price">₱ {{ number_format($product->price, 0) }}</div>
            <button class="slide-btn" onclick="openProductModal({{ $product->id }})">Shop Now</button>
          </div>
        </div>
        @empty
        <!-- Fallback static slides if no DB products -->
        <div class="mySlides fade">
          <img src="{{ asset('img/product1.jpeg') }}" alt="Premium Product">
          <div class="slide-content">
            <h1>Featured Products</h1>
            <p>Discover our top expensive items</p>
            <button class="slide-btn">Explore</button>
          </div>
        </div>
        @endforelse
      </div>
      <br>
      <div class="dots-container">
        @for($i = 1; $i <= max(5, $slideshowProducts->count()); $i++)
        <span class="dot" onclick="currentSlide({{ $i }})" data-slide="{{ $i }}"></span>
        @endfor
      </div>
    </div>

    <div class="second-container" id="products-section">
      <h2 class="section-title">Our Products</h2>
      <div class="product-grid"> <!-- Changed to grid for responsive -->
        @forelse($products as $product)
        <div class="product" data-product-id="{{ $product->id }}">
          <div class="product-image">
            <img src="{{ $product->image_url ?? asset('img/default-product.jpg') }}" alt="{{ $product->name }}">
            @if($product->quantity <= 0)
            <div class="out-of-stock">Out of Stock</div>
            @endif
          </div>
          <h3>{{ $product->name }}</h3>
          <p class="price">₱ {{ number_format($product->price, 0) }}</p>
          @if($product->quantity > 0)
          <button class="view-product-btn" onclick="openProductModal({{ $product->id }})">View Details</button>
          @else
          <button class="view-product-btn disabled">Out of Stock</button>
          @endif
        </div>
        @empty
        <p>No products available. Add some to your database!</p>
        @endforelse
      </div>
    </div>

    <div class="third-container" id="services-section">
      <h2 class="section-title">Our Services</h2>
      <div class="services-grid">
        <div class="service-card">
          <i class="fa fa-truck service-icon"></i>
          <h3>Fast Delivery</h3>
          <p>We offer fast and reliable delivery services to ensure your products reach you on time.</p>
        </div>
        <div class="service-card">
          <i class="fa fa-headphones service-icon"></i>
          <h3>Customer Support</h3>
          <p>Our customer support team is available 24/7 to assist you with any queries or issues.</p>
        </div>
        <div class="service-card">
          <i class="fa fa-refresh service-icon"></i>
          <h3>Easy Returns</h3>
          <p>We provide hassle-free returns and exchanges for your convenience.</p>
        </div>
        <div class="service-card">
          <i class="fa fa-credit-card service-icon"></i>
          <h3>Secure Payment</h3>
          <p>Our payment gateway is secure and encrypted to protect your financial information.</p>
        </div>
        <div class="service-card">
          <i class="fa fa-gift service-icon"></i>
          <h3>Gift Wrapping</h3>
          <p>We offer gift wrapping services to make your presents extra special.</p>
        </div>
        <div class="service-card">
          <i class="fa fa-tags service-icon"></i>
          <h3>Exclusive Offers</h3>
          <p>Enjoy exclusive offers and discounts on your favorite products.</p>
        </div>
      </div>
    </div>

    <div class="fourth-container" id="brands-section">
      <h2 class="section-title">Available Brands</h2>
      <div class="brands-row">
        <div class="brand"><img src="{{ asset('img/brand1.jpg') }}" alt="Brand 1"></div>
        <div class="brand"><img src="{{ asset('img/brand2.jpg') }}" alt="Brand 2"></div>
        <div class="brand"><img src="{{ asset('img/brand3.png') }}" alt="Brand 3"></div>
        <div class="brand"><img src="{{ asset('img/brand4.jpg') }}" alt="Brand 4"></div>
        <div class="brand"><img src="{{ asset('img/brand5.png') }}" alt="Brand 5"></div>
      </div>
    </div>

    <footer>
      <div class="footer-content">
        <div class="footer-section about" id="about-section">
          <h2>About Us</h2>
          <p>Welcome to ShopAI! We are dedicated to providing you with the best online shopping experience. Our team is passionate about delivering high-quality products and exceptional customer service. We believe in the power of technology to make shopping easier and more enjoyable for everyone.</p>
          <p>At ShopAI, we offer a wide range of products to meet your needs. From the latest gadgets to everyday essentials, we have something for everyone. Our mission is to bring you the best products at competitive prices, with fast and reliable delivery.</p>
          <p>Thank you for choosing ShopAI. We look forward to serving you!</p>
        </div>
        <div class="footer-section faq" id="faq-section">
          <h2>FAQ</h2>
          <ul>
            <li><a href="#faq1">How do I place an order?</a></li>
            <li><a href="#faq2">What payment methods do you accept?</a></li>
            <li><a href="#faq3">How can I track my order?</a></li>
            <li><a href="#faq4">What is your return policy?</a></li>
          </ul>
        </div>
        <div class="footer-section contact" id="contact-section">
          <h2>Contact Us</h2>
          <p>Email: support@shopai.com</p>
          <p>Phone: +9673280015</p>
          <p>Address: WCQV+8H9, Tagudin, 2714 Ilocos Sur</p>
        </div>
      </div>
      <div class="footer-bottom"><p>© 2025 ShopAI. All rights reserved.</p></div>
    </footer>

    <!-- Auth Modal -->
    <div id="authModal" class="modal">
      <div class="modal-content auth-modal-content">
        <span class="close-btn" id="closeAuthModal">×</span>
        <div class="auth-container">
          <!-- Login Form -->
          <div class="auth-form" id="loginForm">
            <h3>Login</h3>
            <form id="loginFormElement">
              <input type="email" name="email" placeholder="Email" required>
              <input type="password" name="password" placeholder="Password" required>
              <button type="submit">Login</button>
            </form>
            <p class="auth-switch">Don't have an account? <a href="#" id="showRegister">Sign up here</a></p>
          </div>
          <!-- Register Form -->
          <div class="auth-form" id="registerForm" style="display: none;">
            <h3>Sign Up</h3>
            <form id="registerFormElement">
              <input type="text" name="full_name" placeholder="Full Name" required>
              <input type="email" name="email" placeholder="Email" required>
              <input type="password" name="password" placeholder="Password" required>
              <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
              <input type="text" name="phone" placeholder="Phone (optional)">
              <input type="text" name="address" placeholder="Address (optional)">
              <button type="submit">Sign Up</button>
            </form>
            <p class="auth-switch">Already have an account? <a href="#" id="showLogin">Login here</a></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Product View Modal -->
    <div id="productModal" class="modal">
      <div class="modal-content product-modal-content">
        <span class="close-btn" id="closeProductModal">×</span>
        <div class="product-view-container">
          <div class="product-image">
            <img id="modalProductImage" src="" alt="Product">
          </div>
          <div class="product-details">
            <h2 id="modalProductName">Product Name</h2>
            <p class="price" id="modalProductPrice">₱0.00</p>
            <p id="modalProductDescription">Product description goes here.</p>
            <div class="product-quantity">
              <label for="quantity">Quantity:</label>
              <input type="number" id="quantity" min="1" value="1" max="10">
            </div>
            <div class="modal-actions">
              <button class="add-to-cart-btn" id="addToCartBtn"><i class="fa fa-shopping-cart"></i> Add to Cart</button>
              <button class="buy-now-btn">Buy Now</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cart Modal -->
    <div id="cartModal" class="modal">
      <div class="modal-content cart-modal-content">
        <span class="close-btn" id="closeCartModal">×</span>
        <h3>Your Shopping Cart</h3>
        <div class="cart-items" id="cartItems">
          <!-- Cart items will be added here -->
        </div>
        <div class="cart-summary">
          <div class="summary-row">
            <span>Subtotal:</span>
            <span id="cartSubtotal">₱0.00</span>
          </div>
          <div class="summary-row">
            <span>Shipping:</span>
            <span>₱0.00</span>
          </div>
          <div class="summary-row total">
            <span>Total:</span>
            <span id="cartTotal">₱0.00</span>
          </div>
        </div>
        <div class="cart-actions">
          <button class="checkout-btn">Proceed to Checkout</button>
          <button class="continue-shopping-btn" id="continueShoppingBtn">Continue Shopping</button>
        </div>
      </div>
    </div>

    <button class="back-to-top" id="backToTop" onclick="scrollToTop()"><i class="fa fa-arrow-up"></i></button>
  </div>
  
  <!-- Success/Error Messages -->
  <div id="message-container" class="message-container"></div>
  
  <script src="{{ asset('js/shopai.js') }}"></script>
</body>
</html>