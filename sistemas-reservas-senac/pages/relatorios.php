<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

$user = current_user();

// Buscar estatísticas do banco de dados
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservas WHERE status IN ('reservado', 'proposta')");
$stmt->execute();
$totalReservations = $stmt->fetch()['count'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservas WHERE status = 'reservado'");
$stmt->execute();
$approvedReservations = $stmt->fetch()['count'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservas WHERE status = 'proposta'");
$stmt->execute();
$pendingReservations = $stmt->fetch()['count'] ?? 0;

// Taxa de aprovação
$approvalRate = $totalReservations > 0 ? round(($approvedReservations / $totalReservations) * 100) : 0;

// Taxa de ocupação (reservas por espaço)
$stmt = $pdo->prepare("SELECT s.name, COUNT(r.id) as total_reservas
                       FROM spaces s
                       LEFT JOIN reservas r ON r.space_id = s.id
                       GROUP BY s.id
                       ORDER BY total_reservas DESC
                       LIMIT 3");
$stmt->execute();
$topSpaces = $stmt->fetchAll();

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

// Exportação PDF
if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
    // Simulação de exportação - em produção usar biblioteca como TCPDF ou FPDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="relatorio_reservas.pdf"');
    echo "Relatório de Reservas SENAC\n";
    echo "Total: $totalReservations\n";
    echo "Aprovadas: $approvedReservations\n";
    echo "Pendentes: $pendingReservations\n";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema SENAC</title>
    <link rel="stylesheet" href="../css/comum.css">
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
                    <div class="user-menu" style="position: relative;">
                        <button class="user-menu-toggle" style="display:flex; align-items:center; gap:10px; background:transparent; border:0; cursor:pointer;">
                            <div class="avatar"><?php echo htmlspecialchars(user_initials($user['name'] ?? 'Usuário')); ?></div>
                            <div class="user-details" style="text-align:left;">
                                <div class="username"><?php echo htmlspecialchars($user['name'] ?? 'Usuário'); ?></div>
                                <div class="role">Gestor</div>
                            </div>
                            <span aria-hidden="true">▾</span>
                        </button>
                        <div class="user-menu-dropdown" style="position:absolute; right:0; top:calc(100% + 8px); background:#f6f8ff; border:1px solid var(--gray-100); box-shadow: 0 4px 12px rgba(0,0,0,.08); border-radius:8px; padding:6px; min-width:160px; display:none; z-index:1000;">
                            <a href="../logout.php" class="user-menu-item" style="display:block; padding:8px 10px; border-radius:6px; color:inherit; text-decoration:none;">Sair</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <nav class="nav">
                <a href="../dashboard.php" class="nav-link">📊 Dashboard</a>
                <a href="./reservas.php" class="nav-link">📅 Reservas</a>
                <a href="./espacos.php" class="nav-link">🏢 Espaços</a>
                <a href="./relatorios.php" class="nav-link active">📈 Relatórios</a>
                <a href="./colaboradores-dashboard.php" class="nav-link">👥 Colaboradores</a>
                <a href="./configuracoes.php" class="nav-link">⚙️ Configurações</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <div>
                    <h2 class="page-title">Relatórios e Estatísticas</h2>
                    <p class="page-subtitle">Análise detalhada de reservas e utilização de espaços</p>
                </div>
                <div class="page-actions">
                    <a href="?export=pdf" class="btn btn-secondary">
                        <span class="btn-icon">📥</span>
                        Exportar PDF
                    </a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $totalReservations; ?></h3>
                        <p class="stat-label">Total de Reservas</p>
                        <span class="stat-badge stat-badge-success">Dados em tempo real</span>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $approvedReservations; ?></h3>
                        <p class="stat-label">Reservas Aprovadas</p>
                        <span class="stat-badge stat-badge-success"><?php echo $approvalRate; ?>% de aprovação</span>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $pendingReservations; ?></h3>
                        <p class="stat-label">Reservas Pendentes</p>
                        <span class="stat-badge stat-badge-warning">Aguardando aprovação</span>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $approvalRate; ?>%</h3>
                        <p class="stat-label">Taxa de Aprovação</p>
                        <span class="stat-badge stat-badge-info">Média do sistema</span>
                    </div>
                </div>
            </div>

            <div class="reports-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📊 Reservas por Tipo</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-placeholder">
                            <div class="chart-bar-group">
                                <div class="chart-bar-item">
                                    <div class="chart-bar-label">Aprovadas</div>
                                    <div class="chart-bar-wrapper">
                                        <?php 
                                        $approvedPercent = $totalReservations > 0 ? round(($approvedReservations / $totalReservations) * 100) : 0;
                                        ?>
                                        <div class="chart-bar" style="width: <?php echo $approvedPercent; ?>%; background: var(--primary)">
                                            <span class="chart-bar-value"><?php echo $approvedReservations; ?> (<?php echo $approvedPercent; ?>%)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="chart-bar-item">
                                    <div class="chart-bar-label">Pendentes</div>
                                    <div class="chart-bar-wrapper">
                                        <?php 
                                        $pendingPercent = $totalReservations > 0 ? round(($pendingReservations / $totalReservations) * 100) : 0;
                                        ?>
                                        <div class="chart-bar" style="width: <?php echo $pendingPercent; ?>%; background: var(--secondary)">
                                            <span class="chart-bar-value"><?php echo $pendingReservations; ?> (<?php echo $pendingPercent; ?>%)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🏆 Espaços Mais Utilizados</h3>
                    </div>
                    <div class="card-body">
                        <div class="ranking-list">
                            <?php if ($topSpaces): ?>
                                <?php foreach ($topSpaces as $i => $space): ?>
                                    <div class="ranking-item">
                                        <div class="ranking-position"><?php echo ($i + 1); ?>º</div>
                                        <div class="ranking-content">
                                            <div class="ranking-name"><?php echo htmlspecialchars($space['name']); ?></div>
                                            <div class="ranking-stats"><?php echo $space['total_reservas']; ?> reservas</div>
                                        </div>
                                        <div class="ranking-badge">
                                            <?php 
                                            $medals = ['🥇', '🥈', '🥉'];
                                            echo $medals[$i] ?? '🏅';
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="text-align: center; padding: 32px; color: #999">Ainda não há dados suficientes.</p>
                            <?php endif; ?>
                        </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>
