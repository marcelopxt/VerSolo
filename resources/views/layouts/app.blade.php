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
  @if (session('success'))
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
          <i class="bi bi-check-circle-fill me-2"></i>
          <strong class="me-auto">Sucesso!</strong>
          <small>Agora mesmo</small>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ session('success') }}
        </div>
      </div>
    </div>
  @endif
  <div class="sidebar">
    <h2><i class="bi bi-leaf"></i>VerSolo</h2>
    @auth('employee')
      <li class="nav-item mt-auto p-3" style="color: #ccc;">
        <i class="bi bi-person-circle"></i>
        <span>Logado como: <strong>{{ auth('employee')->user()->name }}</strong></span>
      </li>
    @endauth
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link {{ in_array(Route::currentRouteName(), ["dashboard", "report.submit"]) ? 'active' : '' }}"
          href="{{ route('dashboard') }}">
          <i class="bi bi-file-earmark-check"></i> Realizar Análises
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ Route::currentRouteName() == 'history' ? 'active' : '' }}" href="{{ route('history') }}">
          <i class="bi bi-clock-history"></i> Histórico
        </a>
      </li>
      <li class="nav-item">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <a class="nav-link" href="{{ route('logout') }}"
            onclick="event.preventDefault(); this.closest('form').submit();">
            <i class="bi bi-box-arrow-left"></i> Sair
          </a>
        </form>
      </li>
    </ul>
  </div>

  <main class="content">
    @yield('content')
  </main>
</body>

</html>