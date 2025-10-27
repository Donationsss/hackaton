<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

$user = current_user();

// Buscar todas as reservas (solicitadas por colaboradores)
$stmt = $pdo->prepare("SELECT r.*, s.name AS space_name, u.name AS requester_name, u.email AS requester_email
                         FROM reservas r
                         LEFT JOIN spaces s ON s.id = r.space_id
                         LEFT JOIN users u ON u.id = r.created_by
                         WHERE r.status IN ('proposta', 'reservado')
                         ORDER BY r.date DESC, r.time_start DESC");
$stmt->execute();
$reservations = $stmt->fetchAll();

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
                <a href="./reservas.php" class="nav-link active">📅 Reservas</a>
                <a href="./espacos.php" class="nav-link">🏢 Espaços</a>
                <a href="./relatorios.php" class="nav-link">📈 Relatórios</a>
                <a href="./colaboradores-dashboard.php" class="nav-link">👥 Colaboradores</a>
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
                            <?php if (!$reservations): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 32px; color: #999">Nenhuma reserva encontrada.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reservations as $i => $r): ?>
                                    <tr>
                                        <td class="td-id">#<?php echo str_pad($i + 1, 3, '0', STR_PAD_LEFT); ?></td>
                                        <td>
                                            <div class="td-user">
                                                <strong><?php echo htmlspecialchars($r['requester_name'] ?? $r['requester_name'] ?? 'Não informado'); ?></strong>
                                                <?php if (!empty($r['requester_email'])): ?>
                                                    <span class="td-email"><?php echo htmlspecialchars($r['requester_email']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($r['space_name'] ?? 'Não informado'); ?></td>
                                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($r['date']))); ?></td>
                                        <td class="td-time"><?php echo htmlspecialchars(substr($r['time_start'], 0, 5)); ?> - <?php echo htmlspecialchars(substr($r['time_end'], 0, 5)); ?></td>
                                        <td><span class="badge-type badge-internal">Interna</span></td>
                                        <td>
                                            <?php if ($r['status'] == 'reservado'): ?>
                                                <span class="badge badge-approved">Aprovada</span>
                                            <?php elseif ($r['status'] == 'proposta'): ?>
                                                <span class="badge badge-pending">Pendente</span>
                                            <?php else: ?>
                                                <span class="badge badge-info"><?php echo htmlspecialchars($r['status']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="td-actions">
                                            <?php if ($r['status'] == 'proposta'): ?>
                                                <a href="<?php echo url('/actions/approve_reserva.php?id=' . (int)$r['id']); ?>" class="btn btn-xs btn-success" title="Aprovar">✓</a>
                                                <a href="<?php echo url('/actions/reject_reserva.php?id=' . (int)$r['id']); ?>" class="btn btn-xs btn-danger" title="Rejeitar">✗</a>
                                            <?php endif; ?>
                                            <button class="btn-icon btn-icon-sm" data-action="view-reservation" data-id="<?php echo (int)$r['id']; ?>" title="Ver detalhes">👁️</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-pagination">
                    <div class="pagination-info">Total de reservas: <?php echo count($reservations); ?></div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 SENAC - Sistema de Reservas. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Modal Ver Reserva -->
    <div id="viewReservationModal" style="position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:2000;">
        <div style="background:#fff; width:100%; max-width:600px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
            <div style="padding:16px 20px; border-bottom:1px solid var(--gray-100); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0;">Detalhes da Reserva</h3>
                <button id="closeViewModal" class="btn btn-sm btn-secondary">Fechar</button>
            </div>
            <div style="padding:16px 20px;" id="reservationDetails">
                <!-- Conteúdo será preenchido dinamicamente -->
            </div>
        </div>
    </div>

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

            // Modal Ver Reserva
            const modal = document.getElementById('viewReservationModal');
            const closeBtn = document.getElementById('closeViewModal');

            if (closeBtn) closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            modal.addEventListener('click', function(e) {
                if (e.target === modal) modal.style.display = 'none';
            });

            // Botões de visualizar
            const reservations = <?php echo json_encode($reservations); ?>;
            
            document.querySelectorAll('[data-action="view-reservation"]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const resId = this.dataset.id;
                    const reservation = reservations.find(r => r.id == resId);
                    
                    if (reservation) {
                        document.getElementById('reservationDetails').innerHTML = `
                            <div style="margin-bottom:12px;">
                                <strong style="display:block; color:#6b7280; font-size:12px; margin-bottom:4px;">Espaço:</strong>
                                <div style="font-size:16px;">${reservation.space_name || 'Não informado'}</div>
                            </div>
                            <div style="margin-bottom:12px;">
                                <strong style="display:block; color:#6b7280; font-size:12px; margin-bottom:4px;">Solicitante:</strong>
                                <div style="font-size:16px;">${reservation.requester_name || 'Não informado'}</div>
                            </div>
                            <div style="margin-bottom:12px;">
                                <strong style="display:block; color:#6b7280; font-size:12px; margin-bottom:4px;">Data:</strong>
                                <div style="font-size:16px;">${reservation.date || 'Não informado'}</div>
                            </div>
                            <div style="margin-bottom:12px;">
                                <strong style="display:block; color:#6b7280; font-size:12px; margin-bottom:4px;">Horário:</strong>
                                <div style="font-size:16px;">${reservation.time_start ? reservation.time_start.substring(0,5) : ''} - ${reservation.time_end ? reservation.time_end.substring(0,5) : ''}</div>
                            </div>
                            <div style="margin-bottom:12px;">
                                <strong style="display:block; color:#6b7280; font-size:12px; margin-bottom:4px;">Status:</strong>
                                <div style="font-size:16px;">
                                    <span style="padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; 
                                        background: ${reservation.status === 'reservado' ? '#dcfce7' : reservation.status === 'proposta' ? '#fef3c7' : '#f3f4f6'}; 
                                        color: ${reservation.status === 'reservado' ? '#166534' : reservation.status === 'proposta' ? '#92400e' : '#374151'};">
                                        ${reservation.status === 'reservado' ? '✅ Aprovada' : reservation.status === 'proposta' ? '⏳ Pendente' : reservation.status}
                                    </span>
                                </div>
                            </div>
                            ${reservation.request_title ? `
                            <div style="margin-bottom:12px;">
                                <strong style="display:block; color:#6b7280; font-size:12px; margin-bottom:4px;">Título:</strong>
                                <div style="font-size:16px;">${reservation.request_title}</div>
                            </div>` : ''}
                            ${reservation.request_note ? `
                            <div style="margin-bottom:12px;">
                                <strong style="display:block; color:#6b7280; font-size:12px; margin-bottom:4px;">Observações:</strong>
                                <div style="font-size:14px; color:#6b7280;">${reservation.request_note}</div>
                            </div>` : ''}
                        `;
                    } else {
                        document.getElementById('reservationDetails').innerHTML = '<p style="text-align:center; padding:32px;">Detalhes não encontrados.</p>';
                    }
                    
                    modal.style.display = 'flex';
                });
            });
        });
    </script>
</body>

</html>