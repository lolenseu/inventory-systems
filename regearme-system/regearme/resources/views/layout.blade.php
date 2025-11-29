<!DOCTYPE html>
<html lang="en" class="@yield('body_class')">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Regearme')</title>
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
  <link rel="icon" type="image/png" href="https://cdn-icons-png.flaticon.com/256/5735/5735191.png">
  @yield('css')
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
</head>
<body>
  <!-- Animated Background -->
  <div class="animated-background">
    <img src="{{ asset('gif/albion.gif') }}" alt="Animated Background" class="background-gif" />
  </div>

  <nav class="navbar">
    <div class="left-section">
      <div class="logo">ReGearMe</div>
      @auth
        <div class="username">{{ Auth::user()->in_game_name }}</div>
      @endauth
    </div>

    <ul class="nav-links">
      @guest
        <li><a href="{{ route('login') }}">Login</a></li>
        <li><a href="{{ route('register') }}">Sign Up</a></li>
      @endguest

      @auth
        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
        
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

  <div class="bottom-left-info">
    <p id="raw-content"></p>
    <p class="developer-tag">© 2025 ReGearMe | Developed by lolenseu</p>
  </div>

  <script src="{{ asset('js/layout.js') }}"></script>
  <script>
  fetch('{{ url("/raw-content") }}')
      .then(response => response.text())
      .then(data => {
          document.getElementById('raw-content').innerText = data;
      })
      .catch(err => {
          console.error('Error loading raw file:', err);
          document.getElementById('raw-content').innerText = '';
      });
  </script>
</body>
</html>