<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

$user = current_user();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

function user_initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $ini = '';
    foreach ($parts as $p) {
        if ($p !== '') {
            $ini .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
        }
        if (mb_strlen($ini, 'UTF-8') >= 2) break;
    }
    return $ini ?: 'US';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Sistema SENAC</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../css/comum.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/toast.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <div class="logo-icon"><img src="../logo.png" alt="Senac" style="width: 120px; height: 75px;"></div>
                    <div class="logo-text">
                        <h1>SENAC</h1>
                        <p>Sistema de Reservas</p>
                    </div>
                </div>
                <div class="user-info">
                    <div class="user-menu" style="position: relative;">
                        <button class="user-menu-toggle" style="display:flex; align-items:center; gap:10px; background:transparent; border:0; cursor:pointer;">
                            <div class="avatar"><?php echo htmlspecialchars(user_initials($user['name'] ?? 'Usuário')); ?></div>
                            <div class="user-details" style="text-align:left;">
                                <div class="username"><?php echo htmlspecialchars($user['name'] ?? 'Usuário'); ?></div>
                                <div class="role">Gestor</div>
                            </div>
                            <span aria-hidden="true">▾</span>
                        </button>
                        <div class="user-menu-dropdown" style="position:absolute; right:0; top:calc(100% + 8px); background:#f6f8ff; border:1px solid var(--gray-100); box-shadow: 0 4px 12px rgba(0,0,0,.08); border-radius:8px; padding:6px; min-width:160px; display:none; z-index:1000; color:var(--gray-800);">
                            <a href="../logout.php" class="user-menu-item" style="display:block; padding:8px 10px; border-radius:6px; color:inherit; text-decoration:none;">Sair</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <nav class="nav">
                <a href="../dashboard.php" class="nav-link">📊 Dashboard</a>
                <a href="./reservas.php" class="nav-link">📅 Reservas</a>
                <a href="./espacos.php" class="nav-link">🏢 Espaços</a>
                <a href="./relatorios.php" class="nav-link">📈 Relatórios</a>
                <a href="./colaboradores-dashboard.php" class="nav-link">👥 Colaboradores</a>
                <a href="./configuracoes.php" class="nav-link active">⚙️ Configurações</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <div>
                    <h2 class="page-title">Configurações do Sistema</h2>
                    <p class="page-subtitle">Personalize e configure o sistema</p>
                </div>
            </div>

            <div class="settings-tabs">
                <button class="settings-tab active" data-tab="general">⚙️ Geral</button>
                <button class="settings-tab" data-tab="notifications">🔔 Notificações</button>
                <button class="settings-tab" data-tab="reservations">📅 Reservas</button>
                <button class="settings-tab" data-tab="security">🔒 Segurança</button>
            </div>

            <div class="settings-content active" id="general">
                <div class="settings-grid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">🏢 Informações da Instituição</h3>
                        </div>
                        <div class="card-body">
                            <form class="settings-form">
                                <div class="form-group">
                                    <label class="form-label">Nome da Instituição</label>
                                    <input type="text" class="form-control" value="SENAC">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">CNPJ</label>
                                    <input type="text" class="form-control" value="03.709.814/0001-98">
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Telefone</label>
                                        <input type="tel" class="form-control" value="(11) 3000-0000">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">E-mail</label>
                                        <input type="email" class="form-control" value="contato@senac.com.br">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">🎨 Aparência</h3>
                        </div>
                        <div class="card-body">
                            <div class="setting-item">
                                <div class="setting-info">
                                    <div class="setting-title">Modo Escuro</div>
                                    <div class="setting-description">Ativar tema escuro</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <div class="setting-title">Animações</div>
                                    <div class="setting-description">Ativar animações suaves</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-content" id="notifications">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🔔 Configurações de Notificações</h3>
                    </div>
                    <div class="card-body">
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-title">Notificações por E-mail</div>
                                <div class="setting-description">Receber notificações por e-mail</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-title">Nova Reserva Criada</div>
                                <div class="setting-description">Notificar quando criar reserva</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="setting-item">
                            <div class="setting-info">
                                <div class="setting-title">Lembrete de Reserva</div>
                                <div class="setting-description">Lembrete 24h antes</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-content" id="reservations">
                <div class="settings-grid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">📅 Regras de Reserva</h3>
                        </div>
                        <div class="card-body">
                            <div class="setting-item">
                                <div class="setting-info">
                                    <div class="setting-title">Aprovação Obrigatória</div>
                                    <div class="setting-description">Reservas precisam de aprovação</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <div class="setting-title">Detecção de Conflitos</div>
                                    <div class="setting-description">Alertar conflitos automaticamente</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">⏰ Horários e Limites</h3>
                        </div>
                        <div class="card-body">
                            <form class="settings-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Horário de Abertura</label>
                                        <input type="time" class="form-control" value="07:00">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Horário de Fechamento</label>
                                        <input type="time" class="form-control" value="22:00">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Salvar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-content" id="security">
                <div class="settings-grid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">🔒 Segurança da Conta</h3>
                        </div>
                        <div class="card-body">
                            <form class="settings-form">
                                <div class="form-group">
                                    <label class="form-label">Senha Atual</label>
                                    <input type="password" class="form-control" placeholder="Digite sua senha atual">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nova Senha</label>
                                    <input type="password" class="form-control" placeholder="Digite a nova senha">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" class="form-control" placeholder="Confirme a nova senha">
                                </div>
                                <button type="submit" class="btn btn-primary">Alterar Senha</button>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">🛡️ Autenticação</h3>
                        </div>
                        <div class="card-body">
                            <div class="setting-item">
                                <div class="setting-info">
                                    <div class="setting-title">Autenticação em Dois Fatores</div>
                                    <div class="setting-description">Segurança extra</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <div class="setting-title">Logout Automático</div>
                                    <div class="setting-description">Após 30min de inatividade</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card danger-zone">
                <div class="card-header">
                    <h3 class="card-title">⚠️ Zona de Perigo</h3>
                </div>
                <div class="card-body">
                    <div class="danger-item">
                        <div class="danger-info">
                            <div class="danger-title">Limpar Dados de Teste</div>
                            <div class="danger-description">Remove todas as reservas de exemplo</div>
                        </div>
                        <button class="btn btn-danger" onclick="clearTestData()">
                            Limpar Dados
                        </button>
                    </div>
                    <div class="danger-item">
                        <div class="danger-info">
                            <div class="danger-title">Resetar Configurações</div>
                            <div class="danger-description">Restaurar configurações padrão</div>
                        </div>
                        <button class="btn btn-danger" onclick="clearTestData()">
                            Limpar Dados
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 SENAC - Sistema de Reservas. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="../js/toast.js"></script>
    <script>
        function clearTestData() {
            if (confirm('Tem certeza que deseja limpar todos os dados de teste? Esta ação não pode ser desfeita.')) {
                // Redirecionar para a action PHP que limpa os dados
                window.location.href = '../actions/clear_test_data.php';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar mensagens de toast
            <?php if (!empty($success)): ?>
                window.Toast.success('<?php echo htmlspecialchars($success); ?>');
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                window.Toast.error('<?php echo htmlspecialchars($error); ?>');
            <?php endif; ?>

            // Dropdown perfil
            document.querySelectorAll('.user-menu').forEach(function(menu) {
                const toggle = menu.querySelector('.user-menu-toggle');
                const dropdown = menu.querySelector('.user-menu-dropdown');
                if (!toggle || !dropdown) return;
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const w = toggle.getBoundingClientRect().width;
                    dropdown.style.width = w + 'px';
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                });
                document.addEventListener('click', function() {
                    dropdown.style.display = 'none';
                });
            });

            // Tabs de configurações
            document.querySelectorAll('.settings-tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const tabName = this.dataset.tab;
                    
                    // Remove active de todos
                    document.querySelectorAll('.settings-tab').forEach(function(t) {
                        t.classList.remove('active');
                    });
                    document.querySelectorAll('.settings-content').forEach(function(c) {
                        c.classList.remove('active');
                    });
                    
                    // Adiciona active no clicado
                    this.classList.add('active');
                    const targetContent = document.getElementById(tabName);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>
