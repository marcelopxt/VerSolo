<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Acesso ao Sistema</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
  <div class="login-page-wrapper">
    <div>
      <h1 class="login-logo">
        <i class="bi bi-leaf"></i>VerSolo
      </h1>
      <div class="login-forms-container">
        <div class="form-container">
          <h2>Entrar</h2>
          <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div>
              <div class="form-group">
                <label for="login-name">Nome</label>
                <input id="login-name" type="text" name="name" class="form-control"
                  value="{{ $errors->register->isEmpty() ? old('name') : '' }}" required autofocus>
                @error('name') <span class="error">{{ $message }}</span> @enderror
              </div>
              <div class="form-group">
                <label for="login-password">Senha</label>
                <input id="login-password" type="password" name="password" class="form-control" required>
              </div>
            </div>
            <div class="button-group">
              <button type="submit" class="btn btn-primary">Entrar</button>
            </div>
          </form>
        </div>
        <div class="form-container">
          <h2>Cadastrar</h2>
          <form method="POST" action="{{ route('register.submit') }}">
            @csrf
            <div>
              <div class="form-group">
                <label for="register-name">Nome Completo</label>
                <input id="register-name" type="text" name="name" class="form-control"
                  value="{{ $errors->hasBag('register') ? old('name') : '' }}" required>
                @error('name', 'register') <span class="error">{{ $message }}</span> @enderror
              </div>
              <div class="form-group">
                <label for="access_code">Código de Acesso</label>
                <input id="access_code" type="text" name="access_code" class="form-control" required>
                @error('access_code', 'register') <span class="error">{{ $message }}</span> @enderror
              </div>
              <div class="form-group">
                <label for="register-password">Senha</label>
                <input id="register-password" type="password" name="password" class="form-control" required>
                @error('password', 'register') <span class="error">{{ $message }}</span> @enderror
              </div>
              <div class="form-group">
                <label for="password_confirmation">Confirme a Senha</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control"
                  required>
              </div>
            </div>
            <div class="button-group">
              <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>