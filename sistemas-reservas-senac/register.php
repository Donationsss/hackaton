<?php
require_once __DIR__ . '/inc/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'visualizador';
        if (!$name || !$email || !$password) {
            throw new Exception('Preencha todos os campos.');
        }
        register_user($name, $email, $password, $role);
        header('Location: ' . url('/index.php?success=Cadastro realizado. Faça login.'));
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cadastro</title>
  <link rel="icon" type="image/png" href="./logo.png">
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; }
    .card { max-width: 520px; margin: 0 auto; border: 1px solid #ddd; padding: 1rem; border-radius: 8px; }
    .row { margin-bottom: .75rem; }
    label { display:block; margin-bottom: .25rem; }
    input, select { width:100%; padding:.5rem; }
    .error { color: #b00020; margin-bottom:.5rem; }
    .actions { display:flex; gap:.5rem; align-items:center; }
  </style>
</head>
<body>
  <div class="card">
    <h2>Criar conta</h2>
    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="<?php echo htmlspecialchars(url('/register.php')); ?>">
      <div class="row">
        <label for="name">Nome</label>
        <input type="text" id="name" name="name" required />
      </div>
      <div class="row">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required />
      </div>
      <div class="row">
        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required />
      </div>
      <div class="row">
        <label for="role">Categoria</label>
        <select id="role" name="role">
          <option value="visualizador">Visualizador</option>
          <option value="administrador">Administrador</option>
        </select>
      </div>
      <div class="actions">
        <button type="submit">Cadastrar</button>
        <a href="<?php echo htmlspecialchars(url('/index.php')); ?>">Voltar ao login</a>
      </div>
    </form>
  </div>
</body>
</html>
