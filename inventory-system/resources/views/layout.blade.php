<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Inventory System')</title>
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  @yield('css')
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
</head>
<body>
  <nav class="navbar">
    <div class="left-section">
      <div class="logo">Inventory System</div>
      @auth
        <div class="username">{{ Auth::user()->name }}</div>
      @endauth
    </div>

    <ul class="nav-links">
      @guest
        <li><a href="{{ route('login') }}">Login</a></li>
        <!--<li><a href="{{ route('register') }}">Sign Up</a></li>-->
      @endguest

      @auth
        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
        <li><a href="{{ url('/products') }}">Products</a></li>
        <li><a href="{{ url('/orders') }}">Orders</a></li>
        <li><a href="{{ url('/reports') }}">Reports</a></li>
        
        <li>
          <a href="{{ route('login') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
            @csrf
          </form>
        </li>
      @endauth
    </ul>
  </nav>

  <main>
    <div id="main-loader">
      <div class="spinner"></div>
    </div>

    <div id="main-content" style="display: none;">
      @yield('content')
    </div>
  </main>

  <script>
    window.addEventListener("load", () => {
      const loader = document.getElementById("main-loader");
      const content = document.getElementById("main-content");

      loader.style.opacity = "0";
      loader.style.transition = "opacity 0.4s ease";

      setTimeout(() => {
        loader.style.display = "none";
        content.style.display = "block";
        content.style.opacity = "0";
        content.style.transition = "opacity 0.4s ease";
        setTimeout(() => content.style.opacity = "1", 50);
      }, 200);
    });
  </script>
</body>
</html>