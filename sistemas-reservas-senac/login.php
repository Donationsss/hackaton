<?php
require_once __DIR__ . '/inc/auth.php';

// Se já logado, redireciona conforme a categoria
if ($u = current_user()) {
    if ($u['role_name'] === 'administrador') {
        header('Location: ' . url('/dashboard.php'));
        exit;
    } elseif ($u['role_name'] === 'colaborador') {
        header('Location: ' . url('/colaboradores.php'));
        exit;
    }
    header('Location: ' . url('/visualizador.php'));
    exit;
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = login_user($email, $password);
    if ($user) {
        if ($user['role_name'] === 'administrador') {
            header('Location: ' . url('/dashboard.php'));
        } elseif ($user['role_name'] === 'colaborador') {
            header('Location: ' . url('/colaboradores.php'));
        } else {
            header('Location: ' . url('/visualizador.php'));
        }
        exit;
    } else {
        header('Location: ' . url('/login.php?error=' . urlencode('Credenciais inválidas')));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema SENAC</title>
    <link rel="stylesheet" href="./css/comum.css">
    <link rel="stylesheet" href="./css/auth.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <!-- Banner Lateral -->
            <div class="auth-banner">
                <div class="auth-banner-content">
                    <div class="auth-logo">
                        <img src="./imagem.jpg" alt="SENAC" style="max-width: 150px; height: auto;">
                    </div>
                    <h2 class="auth-banner-title">Bem-vindo de volta!</h2>
                    <p class="auth-banner-text">
                        Acesse sua conta para gerenciar reservas, espaços e muito mais.
                    </p>
                    <div class="auth-banner-icon">🔐</div>
                </div>
            </div>

            <!-- Formulário de Login -->
            <div class="auth-form-container">
                <div class="auth-form-header">
                    <h1 class="auth-form-title">Fazer Login</h1>
                    <p class="auth-form-subtitle">Entre com suas credenciais para acessar o sistema</p>
                </div>

                <?php if ($error): ?><div class="form-error" style="margin-bottom:8px;color:#b00020;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <?php if ($success): ?><div class="form-success" style="margin-bottom:8px;color:#2e7d32;"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

                <form class="auth-form" id="loginForm" method="post" action="<?php echo htmlspecialchars(url('/login.php')); ?>">
                    <div class="form-group">
                        <label class="form-label" for="email">E-mail</label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email"
                            placeholder="seu@email.com"
                            required
                        >
                        <span class="form-error" id="emailError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Senha</label>
                        <div class="password-input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password"
                                placeholder="••••••••"
                                required
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                👁️
                            </button>
                        </div>
                        <span class="form-error" id="passwordError"></span>
                    </div>

                    <div class="form-actions">
                        <div class="checkbox-group">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Lembrar de mim</label>
                        </div>
                        <a href="<?php echo htmlspecialchars(url('/recuperar-senha.php')); ?>" class="form-link">Esqueceu a senha?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Entrar
                    </button>
                </form>

                <div class="auth-footer">
                    Não tem uma conta? <a href="<?php echo htmlspecialchars(url('/cadastro.php')); ?>">Cadastre-se</a>
                </div>
            </div>
        </div>
    </div>

    <script src="./js/login.js"></script>
</body>
</html>
