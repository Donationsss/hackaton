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
                    Simplifique o gerenciamento de espaços e eventos com nossa plataforma
                    digital centralizada. Evite conflitos, otimize a comunicação e tenha
                    controle total das suas reservas.
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
                <h2 class="section-title">Funcionalidades Principais</h2>
                <p class="section-subtitle">
                    Tudo o que você precisa para gerenciar reservas de forma eficiente
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3 class="feature-title">Agenda Interativa</h3>
                    <p class="feature-description">
                        Visualize todas as reservas em uma agenda clara e organizada,
                        com alertas automáticos para evitar conflitos de horário.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3 class="feature-title">Múltiplos Perfis</h3>
                    <p class="feature-description">
                        Sistema com três níveis de acesso: Gestor/Admin, Colaborador
                        e Visualização, cada um com permissões específicas.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3 class="feature-title">Dashboard Completo</h3>
                    <p class="feature-description">
                        Acompanhe estatísticas, gráficos e indicadores em tempo real
                        para tomadas de decisão mais assertivas.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h3 class="feature-title">Notificações</h3>
                    <p class="feature-description">
                        Receba alertas sobre aprovações, cancelamentos e lembretes
                        de reservas próximas.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3 class="feature-title">Relatórios Gerenciais</h3>
                    <p class="feature-description">
                        Gere relatórios detalhados sobre uso de espaços, horários
                        mais utilizados e muito mais.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🏢</div>
                    <h3 class="feature-title">Gestão de Espaços</h3>
                    <p class="feature-description">
                        Cadastre e gerencie todos os espaços disponíveis com
                        informações de capacidade e recursos.
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
                        Por que usar nosso sistema?
                    </h2>
                    <ul class="benefits-list">
                        <li class="benefit-item">
                            <div class="benefit-icon">✅</div>
                            <div class="benefit-text">
                                <h3>Elimine Conflitos de Horário</h3>
                                <p>
                                    Sistema inteligente que detecta e previne
                                    automaticamente conflitos de reservas.
                                </p>
                            </div>
                        </li>
                        <li class="benefit-item">
                            <div class="benefit-icon">⚡</div>
                            <div class="benefit-text">
                                <h3>Processo Ágil e Eficiente</h3>
                                <p>
                                    Substitua planilhas e e-mails por um sistema
                                    centralizado e fácil de usar.
                                </p>
                            </div>
                        </li>
                        <li class="benefit-item">
                            <div class="benefit-icon">🔒</div>
                            <div class="benefit-text">
                                <h3>Controle e Segurança</h3>
                                <p>
                                    Gerencie permissões e tenha controle total sobre
                                    quem pode solicitar e aprovar reservas.
                                </p>
                            </div>
                        </li>
                        <li class="benefit-item">
                            <div class="benefit-icon">📱</div>
                            <div class="benefit-text">
                                <h3>Acesso em Qualquer Lugar</h3>
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
                <h2 class="section-title">Resultados Comprovados</h2>
            </div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Redução de Conflitos</div>
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
            <h2 class="cta-title">Pronto para transformar suas reservas?</h2>
            <p class="cta-subtitle">
                Comece agora e experimente a diferença de um sistema profissional
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
