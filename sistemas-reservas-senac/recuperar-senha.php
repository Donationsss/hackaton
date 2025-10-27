<?php
require_once __DIR__ . '/inc/auth.php';

// Se logado, não precisa recuperar senha
if (current_user()) {
    header('Location: ' . url('/landing.php'));
    exit;
}

$info = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        $error = 'Informe um e-mail válido.';
    } else {
        // Placeholder: aqui você poderia enviar um e-mail com token de recuperação
        $info = 'Se o e-mail estiver cadastrado, enviaremos instruções para redefinir a senha.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Sistema SENAC</title>
    <link rel="icon" type="image/png" href="./logo.png">
    <link rel="stylesheet" href="./css/comum.css">
    <link rel="stylesheet" href="./css/auth.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container auth-single">
            <!-- Formulário de Recuperação -->
            <div class="auth-form-container">
                <div class="auth-form-header">
                    <div class="auth-logo" style="text-align: center; margin-bottom: 24px;">
                        <img src="./logo.png" alt="SENAC" style="max-width: 120px; height: auto;">
                    </div>
                    <h1 class="auth-form-title" style="text-align: center;">Recuperar Senha</h1>
                    <p class="auth-form-subtitle" style="text-align: center;">
                        Insira seu e-mail e enviaremos instruções para redefinir sua senha
                    </p>
                </div>

                <?php if ($error): ?><div class="form-error" style="margin-bottom:8px;color:#b00020; text-align:center; "><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <?php if ($info): ?><div class="form-success" style="margin-bottom:8px;color:#2e7d32; text-align:center; "><?php echo htmlspecialchars($info); ?></div><?php endif; ?>

                <form class="auth-form" id="recuperarSenhaForm" method="post" action="<?php echo htmlspecialchars(url('/recuperar-senha.php')); ?>">
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

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Enviar Instruções
                    </button>
                </form>

                <div class="auth-footer">
                    Lembrou sua senha? <a href="<?php echo htmlspecialchars(url('/login.php')); ?>">Fazer login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="./js/recuperar-senha.js"></script>
</body>
</html>
