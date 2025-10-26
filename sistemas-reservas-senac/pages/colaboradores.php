<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colaboradores - Sistema SENAC</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <div class="logo-icon"><img src="../imagem.jpg" alt="Senac" style="width: 120px; height: 75px;"></div>
                    <div class="logo-text">
                        <h1>SENAC</h1>
                        <p>Sistema de Reservas</p>
                    </div>
                </div>
                <div class="user-info">
                    <div class="avatar">AD</div>
                    <div class="user-details">
                        <div class="username">Administrador</div>
                        <div class="role">Gestor</div>
                    </div>
                </div>
            </div>
            
            <nav class="nav">
                <a href="../dashboard.php" class="nav-link">📊 Dashboard</a>
                <a href="./reservas.php" class="nav-link">📅 Reservas</a>
                <a href="./espacos.php" class="nav-link">🏢 Espaços</a>
                <a href="./relatorios.php" class="nav-link">📈 Relatórios</a>
                <a href="./colaboradores.php" class="nav-link active">👥 Colaboradores</a>
                <a href="./configuracoes.php" class="nav-link">⚙️ Configurações</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <div>
                    <h2 class="page-title">Gerenciar Colaboradores</h2>
                    <p class="page-subtitle">Gerencie usuários e permissões do sistema</p>
                </div>
                <div class="page-actions">
                    <button class="btn btn-secondary" data-action="export-data">
                        <span class="btn-icon">📥</span>
                        Exportar
                    </button>
                    <button class="btn btn-primary" data-action="new-collaborator">
                        <span class="btn-icon">➕</span>
                        Adicionar Colaborador
                    </button>
                </div>
            </div>

            <div class="collaborators-grid">
                <div class="collaborator-card">
                    <div class="collaborator-avatar-large">AD</div>
                    <h3 class="collaborator-name">Administrador Sistema</h3>
                    <p class="collaborator-email">admin@senac.com.br</p>
                    <span class="role-badge role-admin">Gestor/Admin</span>
                    <div class="collaborator-info">
                        <div class="collaborator-info-item">
                            <span class="info-label">Departamento:</span>
                            <span class="info-value">TI</span>
                        </div>
                        <div class="collaborator-info-item">
                            <span class="info-label">Status:</span>
                            <span class="status-badge status-active">Ativo</span>
                        </div>
                    </div>
                    <div class="collaborator-actions">
                        <button class="btn btn-sm btn-secondary btn-block">Ver Perfil</button>
                    </div>
                </div>

                <div class="collaborator-card">
                    <div class="collaborator-avatar-large" style="background: linear-gradient(135deg, #28A745, #1E7E34)">MS</div>
                    <h3 class="collaborator-name">Maria Silva</h3>
                    <p class="collaborator-email">maria.silva@senac.com.br</p>
                    <span class="role-badge role-collaborator">Colaborador</span>
                    <div class="collaborator-info">
                        <div class="collaborator-info-item">
                            <span class="info-label">Departamento:</span>
                            <span class="info-value">Coordenação</span>
                        </div>
                        <div class="collaborator-info-item">
                            <span class="info-label">Status:</span>
                            <span class="status-badge status-active">Ativo</span>
                        </div>
                    </div>
                    <div class="collaborator-actions">
                        <button class="btn btn-sm btn-secondary">✏️ Editar</button>
                        <button class="btn btn-sm btn-danger" data-action="delete-collaborator" data-id="2">🗑️</button>
                    </div>
                </div>
            </div>

            <div class="permissions-section">
                <h3 class="section-title">Níveis de Acesso</h3>
                <div class="permissions-grid">
                    <div class="permission-card">
                        <div class="permission-icon">👑</div>
                        <h4>Gestor/Admin</h4>
                        <ul class="permission-list">
                            <li>✅ Acesso total ao sistema</li>
                            <li>✅ Gerenciar colaboradores</li>
                            <li>✅ Aprovar/rejeitar reservas</li>
                            <li>✅ Gerenciar espaços</li>
                            <li>✅ Acessar relatórios</li>
                            <li>✅ Configurar sistema</li>
                        </ul>
                    </div>

                    <div class="permission-card">
                        <div class="permission-icon">👤</div>
                        <h4>Colaborador</h4>
                        <ul class="permission-list">
                            <li>✅ Criar reservas</li>
                            <li>✅ Ver próprias reservas</li>
                            <li>✅ Editar reservas pendentes</li>
                            <li>✅ Ver espaços disponíveis</li>
                            <li>❌ Aprovar reservas</li>
                            <li>❌ Gerenciar usuários</li>
                        </ul>
                    </div>

                    <div class="permission-card">
                        <div class="permission-icon">👁️</div>
                        <h4>Visualizador</h4>
                        <ul class="permission-list">
                            <li>✅ Ver reservas</li>
                            <li>✅ Ver espaços</li>
                            <li>❌ Criar reservas</li>
                            <li>❌ Editar informações</li>
                            <li>❌ Aprovar reservas</li>
                            <li>❌ Acessar configurações</li>
                        </ul>
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

    <script src="../js/app.js"></script>
</body>
</html>
