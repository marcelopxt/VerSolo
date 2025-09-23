<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'VerSolo')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
  <div class="sidebar">
    <h2><i class="bi bi-leaf"></i>VerSolo</h2>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/"><i
            class="bi bi-file-earmark-check"></i> Realizar Análises</a>
      </li>
      <li class="nav-item"><a class="nav-link {{ request()->is('history') ? 'active' : '' }}" href="/history"><i
            class="bi bi-clock-history"></i> Histórico</a></li>
      <li class="nav-item"><a class="nav-link {{ request()->is('logout') ? 'active' : '' }}" href="/logout"><i
            class="bi bi-box-arrow-left"></i> Sair</a></li>
    </ul>
  </div>

  <main class="content">
    @yield('content')
  </main>
</body>

</html>