<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - Sistema SENAC</title>
    <link rel="stylesheet" href="../css/comum.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/paginas.css">
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
                <a href="./reservas.php" class="nav-link active">📅 Reservas</a>
                <a href="./espacos.php" class="nav-link">🏢 Espaços</a>
                <a href="./relatorios.php" class="nav-link">📈 Relatórios</a>
                <a href="./colaboradores.php" class="nav-link">👥 Colaboradores</a>
                <a href="./configuracoes.php" class="nav-link">⚙️ Configurações</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <div>
                    <h2 class="page-title">Gerenciar Reservas</h2>
                    <p class="page-subtitle">Visualize e gerencie todas as reservas de espaços</p>
                </div>

            </div>

            <div class="filters-bar">
                <div class="filter-group">
                    <label class="filter-label">Status:</label>
                    <select class="filter-select">
                        <option value="all">Todas</option>
                        <option value="pending">Pendentes</option>
                        <option value="approved">Aprovadas</option>
                        <option value="rejected">Rejeitadas</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Tipo:</label>
                    <select class="filter-select">
                        <option value="all">Todos</option>
                        <option value="internal">Interna</option>
                        <option value="external">Externa</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Data:</label>
                    <input type="date" class="filter-input">
                </div>
            </div>

            <div class="card">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Solicitante</th>
                                <th>Espaço</th>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="td-id">#001</td>
                                <td>
                                    <div class="td-user">
                                        <strong>Maria Silva</strong>
                                        <span class="td-email">maria@senac.com.br</span>
                                    </div>
                                </td>
                                <td>Auditório Principal</td>
                                <td>24/10/2025</td>
                                <td class="td-time">09:00 - 12:00</td>
                                <td><span class="badge-type badge-internal">Interna</span></td>
                                <td><span class="badge badge-approved">Aprovada</span></td>
                                <td class="td-actions">
                                    <button class="btn-icon btn-icon-sm" data-action="view-reservation" data-id="1" title="Ver detalhes">👁️</button>
                                    <button class="btn-icon btn-icon-sm" data-action="delete-reservation" data-id="1" title="Excluir">🗑️</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="td-id">#002</td>
                                <td>
                                    <div class="td-user">
                                        <strong>João Santos</strong>
                                        <span class="td-email">joao@senac.com.br</span>
                                    </div>
                                </td>
                                <td>Laboratório 1</td>
                                <td>24/10/2025</td>
                                <td class="td-time">14:00 - 17:00</td>
                                <td><span class="badge-type badge-internal">Interna</span></td>
                                <td><span class="badge badge-approved">Aprovada</span></td>
                                <td class="td-actions">
                                    <button class="btn-icon btn-icon-sm" data-action="view-reservation" data-id="2" title="Ver detalhes">👁️</button>
                                    <button class="btn-icon btn-icon-sm" data-action="delete-reservation" data-id="2" title="Excluir">🗑️</button>
                                </td>
                            </tr>
                            <tr>
                                <td class="td-id">#003</td>
                                <td>
                                    <div class="td-user">
                                        <strong>Ana Costa</strong>
                                        <span class="td-email">ana@senac.com.br</span>
                                    </div>
                                </td>
                                <td>Sala de Reuniões</td>
                                <td>25/10/2025</td>
                                <td class="td-time">10:00 - 11:30</td>
                                <td><span class="badge-type badge-internal">Interna</span></td>
                                <td><span class="badge badge-pending">Pendente</span></td>
                                <td class="td-actions">
                                    <button class="btn btn-xs btn-success" data-action="approve-reservation" data-id="3" title="Aprovar">✓</button>
                                    <button class="btn btn-xs btn-danger" data-action="reject-reservation" data-id="3" title="Rejeitar">✗</button>
                                    <button class="btn-icon btn-icon-sm" data-action="view-reservation" data-id="3" title="Ver">👁️</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-pagination">
                    <div class="pagination-info">Mostrando 1-3 de 3 reservas</div>
                    <div class="pagination-controls">
                        <button class="btn-icon active">1</button>
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

    <script src="../js/paginas.js"></script>
</body>

</html>