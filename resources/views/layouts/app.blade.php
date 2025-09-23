<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>@yield('title', 'Meu Site')</title>
  <link rel="stylesheet" href="{{ 'css/app.css' }}">
</head>

<body>
  <header>
    <h1>VerSolo</h1>
    <nav>
    </nav>
  </header>

  <main>
    @yield('content')
  </main>
</body>

</html>