<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'VerSolo')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
</head>

<body>
  <div class="sidebar">
    <h2><i class="bi bi-leaf"></i> VerSolo</h2>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link {{ Route::currentRouteName() == 'home' ? 'active' : '' }}" href="{{ route('home') }}">
          <i class="bi bi-file-earmark-check"></i> Realizar Análises
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ Route::currentRouteName() == 'history' ? 'active' : '' }}" href="{{ route('history') }}">
          <i class="bi bi-clock-history"></i> Histórico
        </a>
      </li>
      {{--
      <li class="nav-item">
        <a class="nav-link {{ Route::currentRouteName() == 'logout' ? 'active' : '' }}" href="{{ route('logout') }}">
          <i class="bi bi-box-arrow-left"></i> Sair
        </a>
      </li>
      --}}
    </ul>
  </div>

  <main class="content">
    @yield('content')
  </main>
</body>

</html>