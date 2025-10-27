<?php
require_once __DIR__ . '/inc/auth.php';

// Se já logado, redireciona
if ($u = current_user()) {
    if ($u['role_name'] === 'administrador') {
        header('Location: ' . url('/dashboard.php'));
        exit;
    }
    header('Location: ' . url('/visualizador.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $sobrenome = trim($_POST['sobrenome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? ''); // opcional, não usado no backend agora
        $cargo = trim($_POST['cargo'] ?? '');       // opcional, não usado no backend agora
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirmPassword'] ?? '';
        $terms = isset($_POST['terms']);

        if (!$nome || !$sobrenome || !$email || !$password || !$confirm || !$terms) {
            throw new Exception('Preencha todos os campos obrigatórios.');
        }
        if ($password !== $confirm) {
            throw new Exception('As senhas não coincidem.');
        }

        $fullName = trim($nome . ' ' . $sobrenome);
        // Por padrão, novos cadastros entram como "visualizador"
        register_user($fullName, $email, $password, 'visualizador');
        header('Location: ' . url('/login.php?success=' . urlencode('Cadastro realizado. Faça login.')));
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema SENAC</title>
    <link rel="icon" type="image/png" href="./logo.png">
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
                        <img src="./logo.png" alt="SENAC" style="max-width: 150px; height: auto;">
                    </div>
                    <h2 class="auth-banner-title">Junte-se a nós!</h2>
                    <p class="auth-banner-text">
                        Crie sua conta e comece a gerenciar reservas de forma eficiente e profissional.
                    </p>
                    <div class="auth-banner-icon">✨</div>
                </div>
            </div>

            <!-- Formulário de Cadastro -->
            <div class="auth-form-container">
                <div class="auth-form-header">
                    <h1 class="auth-form-title">Criar Conta</h1>
                    <p class="auth-form-subtitle">Preencha os dados abaixo para se cadastrar</p>
                </div>

                <?php if ($error): ?><div class="form-error" style="margin-bottom:8px;color:#b00020;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

                <form class="auth-form" id="cadastroForm" method="post" action="<?php echo htmlspecialchars(url('/cadastro.php')); ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="nome">Nome</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nome" 
                                name="nome"
                                placeholder="Seu nome"
                                required
                            >
                            <span class="form-error" id="nomeError"></span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="sobrenome">Sobrenome</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="sobrenome" 
                                name="sobrenome"
                                placeholder="Seu sobrenome"
                                required
                            >
                            <span class="form-error" id="sobrenomeError"></span>
                        </div>
                    </div>

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
                        <label class="form-label" for="telefone">Telefone</label>
                        <input 
                            type="tel" 
                            class="form-control" 
                            id="telefone" 
                            name="telefone"
                            placeholder="(00) 00000-0000"
                        >
                        <span class="form-error" id="telefoneError"></span>
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
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-bar-fill" id="strengthBar"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Use pelo menos 8 caracteres</span>
                        </div>
                        <span class="form-error" id="passwordError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirmPassword">Confirmar Senha</label>
                        <div class="password-input-group">
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirmPassword" 
                                name="confirmPassword"
                                placeholder="••••••••"
                                required
                            >
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                👁️
                            </button>
                        </div>
                        <span class="form-error" id="confirmPasswordError"></span>
                    </div>

                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">
                                Aceito os <a href="#" class="form-link">Termos de Uso</a> e a 
                                <a href="#" class="form-link">Política de Privacidade</a>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Criar Conta
                    </button>
                </form>

                <div class="auth-footer">
                    Já tem uma conta? <a href="<?php echo htmlspecialchars(url('/login.php')); ?>">Fazer login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="./js/cadastro.js"></script>
</body>
</html>
