<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Acesso ao Sistema</title>
  <style>
    body {
      font-family: sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background-color: #f4f4f4;
    }

    .container {
      display: flex;
      gap: 40px;
    }

    .form-container {
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      width: 300px;
    }

    h2 {
      text-align: center;
      color: #333;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 5px;
    }

    input {
      width: 100%;
      padding: 8px;
      box-sizing: border-box;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .error {
      color: red;
      font-size: 0.8em;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="form-container">
      <h2>Login</h2>
      <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <div class="form-group">
          <label for="login-name">Nome</label>
          <input id="login-name" type="text" name="name" value="{{ old('name') }}" required autofocus>
          @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
          <label for="login-password">Senha</label>
          <input id="login-password" type="password" name="password" required>
        </div>
        <button type="submit">Entrar</button>
      </form>
    </div>

    <div class="form-container">
      <h2>Cadastro</h2>
      <form method="POST" action="{{ route('register.submit') }}">
        @csrf
        <div class="form-group">
          <label for="register-name">Nome Completo</label>
          <input id="register-name" type="text" name="name" value="{{ old('name') }}" required>
          @error('name', 'register') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
          <label for="access_code">Código de Acesso</label>
          <input id="access_code" type="text" name="access_code" required>
          @error('access_code', 'register') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
          <label for="register-password">Senha</label>
          <input id="register-password" type="password" name="password" required>
          @error('password', 'register') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group">
          <label for="password_confirmation">Confirme a Senha</label>
          <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>
        <button type="submit">Cadastrar</button>
      </form>
    </div>
  </div>
</body>

</html>