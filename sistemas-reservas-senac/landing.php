<?php
require_once __DIR__ . '/inc/auth.php';

// Se logado, manda para a página da categoria
if ($u = current_user()) {
    if ($u['role_name'] === 'administrador') {
        header('Location: ' . url('/dashboard.php'));
        exit;
    }
    header('Location: ' . url('/visualizador.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reservas SENAC</title>
    <link rel="icon" type="image/png" href="./logo.png">
    <link rel="stylesheet" href="./css/comum.css">
    <link rel="stylesheet" href="./css/landing.css">
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Sistema de Reservas SENAC</h1>
                <p class="hero-subtitle">
                    Gerencie reservas de espaços de forma inteligente e eficiente. 
                    Nossa plataforma oferece controle total sobre eventos, espaços e 
                    colaboradores com interface intuitiva e recursos avançados.
                </p>
                <div class="hero-buttons">
                    <a href="<?php echo htmlspecialchars(url('/login.php')); ?>" class="btn btn-light btn-lg">
                        Fazer Login
                    </a>
                    <a href="<?php echo htmlspecialchars(url('/cadastro.php')); ?>" class="btn btn-outline btn-lg">
                        Criar Conta
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Recursos Principais</h2>
                <p class="section-subtitle">
                    Uma solução completa para gerenciamento de reservas e eventos
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3 class="feature-title">Agenda Interativa</h3>
                    <p class="feature-description">
                        Visualize todas as reservas em uma agenda clara e organizada,
                        com indicadores visuais para diferentes tipos de eventos.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3 class="feature-title">Múltiplos Perfis</h3>
                    <p class="feature-description">
                        Sistema com três níveis de acesso: Administrador, Colaborador
                        e Visualizador, cada um com funcionalidades específicas.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3 class="feature-title">Painel Personalizado</h3>
                    <p class="feature-description">
                        Cada usuário tem acesso a um painel personalizado com as
                        informações e funcionalidades adequadas ao seu perfil.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h3 class="feature-title">Notificações Inteligentes</h3>
                    <p class="feature-description">
                        Receba alertas sobre aprovações, cancelamentos e lembretes
                        de reservas próximas de forma organizada.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3 class="feature-title">Relatórios Detalhados</h3>
                    <p class="feature-description">
                        Gere relatórios completos sobre uso de espaços, horários
                        mais utilizados e estatísticas de reservas.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🏢</div>
                    <h3 class="feature-title">Gestão de Espaços</h3>
                    <p class="feature-description">
                        Cadastre e gerencie todos os espaços disponíveis com
                        informações detalhadas de capacidade e recursos.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits">
        <div class="container">
            <div class="benefits-content">
                <div>
                    <h2 class="section-title" style="text-align: left;">
                        Por que escolher nossa plataforma?
                    </h2>
                    <ul class="benefits-list">
                        <li class="benefit-item">
                            <div class="benefit-icon">✅</div>
                            <div class="benefit-text">
                                <h3>Controle Total de Reservas</h3>
                                <p>
                                    Sistema inteligente que organiza e gerencia
                                    todas as reservas de forma automática e eficiente.
                                </p>
                            </div>
                        </li>
                        <li class="benefit-item">
                            <div class="benefit-icon">⚡</div>
                            <div class="benefit-text">
                                <h3>Interface Intuitiva</h3>
                                <p>
                                    Substitua processos complexos por uma interface
                                    moderna, simples e fácil de usar.
                                </p>
                            </div>
                        </li>
                        <li class="benefit-item">
                            <div class="benefit-icon">🔒</div>
                            <div class="benefit-text">
                                <h3>Segurança e Controle</h3>
                                <p>
                                    Gerencie permissões e tenha controle total sobre
                                    quem pode solicitar e aprovar reservas.
                                </p>
                            </div>
                        </li>
                        <li class="benefit-item">
                            <div class="benefit-icon">📱</div>
                            <div class="benefit-text">
                                <h3>Acesso Universal</h3>
                                <p>
                                    Interface responsiva que funciona perfeitamente
                                    em computadores, tablets e smartphones.
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="benefits-image">
                    📋
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Benefícios Comprovados</h2>
            </div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Organização Melhorada</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">60%</div>
                    <div class="stat-label">Economia de Tempo</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Visibilidade Total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Disponibilidade</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2 class="cta-title">Pronto para organizar suas reservas?</h2>
            <p class="cta-subtitle">
                Junte-se à nossa plataforma e experimente uma nova forma de gerenciar eventos
            </p>
            <a href="<?php echo htmlspecialchars(url('/cadastro.php')); ?>" class="btn btn-light btn-lg">
                Criar Conta Gratuitamente
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 SENAC - Sistema de Reservas. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="./js/landing.js"></script>
</body>
</html>
