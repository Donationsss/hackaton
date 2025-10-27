<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('colaborador');

$user = current_user();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Buscar reservas do usuário (incluindo canceladas)
$stmt = $pdo->prepare("SELECT r.*, s.name AS space_name
                         FROM reservas r
                         LEFT JOIN spaces s ON s.id = r.space_id
                         WHERE r.created_by = ? AND r.status IN ('reservado', 'proposta', 'cancelado')
                         ORDER BY r.date DESC, r.time_start DESC");
$stmt->execute([(int)$user['id']]);
$reservations = $stmt->fetchAll();

function user_initials(string $name): string
{
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Minhas Reservas - Sistema SENAC</title>
  <link rel="icon" type="image/png" href="../logo.png">
  <link rel="stylesheet" href="../css/comum.css" />
  <link rel="stylesheet" href="../css/dashboard.css" />
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
                <div class="role">Colaborador</div>
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
        <a href="../colaboradores.php" class="nav-link">📅 Solicitar Reserva</a>
        <a href="./espacos.php" class="nav-link">🏢 Espaços</a>
        <a href="./minhas-reservas.php" class="nav-link active">📋 Minhas Reservas</a>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
      <div class="page-header">
        <div>
          <h2 class="page-title">Minhas Reservas</h2>
          <p class="page-subtitle">Todas as suas reservas aprovadas, rejeitadas e pendentes</p>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <?php if (!$reservations): ?>
            <p style="text-align: center; padding: 32px; color: #999">Você ainda não possui reservas.</p>
          <?php else: ?>
            <div class="list">
              <?php foreach ($reservations as $r): ?>
                <div class="list-item" style="display:flex; justify-content:space-between; align-items:center; padding:16px; border-bottom:1px solid var(--gray-100); border-radius:8px; margin-bottom:12px; background:#f9fafb;">
                  <div style="flex:1;">
                    <div style="font-weight:600; margin-bottom:4px; font-size:16px;">
                      <?php echo htmlspecialchars($r['space_name'] ?? 'Espaço não informado'); ?>
                      <?php if (!empty($r['request_title'])): ?>
                        - <?php echo htmlspecialchars($r['request_title']); ?>
                      <?php endif; ?>
                    </div>
                    <div style="font-size:13px; color: var(--gray-600); margin-bottom:4px;">
                      📅 <?php echo htmlspecialchars(date('d/m/Y', strtotime($r['date']))); ?>
                      🕐 <?php echo htmlspecialchars(substr($r['time_start'], 0, 5)); ?> - <?php echo htmlspecialchars(substr($r['time_end'], 0, 5)); ?>
                    </div>
                    <?php if (!empty($r['request_note'])): ?>
                      <div style="font-size:13px; color: var(--gray-600); margin-top:4px; font-style:italic;">
                        <?php echo htmlspecialchars($r['request_note']); ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div style="margin-left:16px; display:flex; gap:8px; align-items:center;">
                    <?php if ($r['status'] == 'reservado'): ?>
                      <span class="stat-badge stat-badge-success" style="padding:8px 16px; border-radius:6px; font-weight:600; white-space:nowrap;">✅ Aprovada</span>
                    <?php elseif ($r['status'] == 'proposta'): ?>
                      <span class="stat-badge stat-badge-warning" style="padding:8px 16px; border-radius:6px; font-weight:600; white-space:nowrap;">⏳ Pendente</span>
                    <?php elseif ($r['status'] == 'cancelado'): ?>
                      <span class="stat-badge" style="padding:8px 16px; border-radius:6px; font-weight:600; white-space:nowrap; background:#f3f4f6; color:#6b7280;">❌ Cancelada</span>
                    <?php else: ?>
                      <span class="stat-badge stat-badge-info" style="padding:8px 16px; border-radius:6px; font-weight:600; white-space:nowrap;"><?php echo htmlspecialchars($r['status']); ?></span>
                    <?php endif; ?>
                    <?php if ($r['status'] == 'cancelado' && !empty($r['cancel_reason'])): ?>
                      <button onclick="showReason('<?php echo htmlspecialchars(str_replace("'", "\\'", $r['cancel_reason'])); ?>')" style="padding:6px 12px; background:transparent; border:1px solid #d1d5db; border-radius:6px; cursor:pointer; font-size:14px;" title="Ver justificativa">👁️</button>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
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
    <?php if (!empty($success)): ?>
    setTimeout(() => { if (window.Toast) window.Toast.success('<?php echo htmlspecialchars($success); ?>'); }, 100);
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    setTimeout(() => { if (window.Toast) window.Toast.error('<?php echo htmlspecialchars($error); ?>'); }, 100);
    <?php endif; ?>
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
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

    function showReason(reason) {
      const modalHtml = `
        <div id="reasonModal" style="position:fixed; inset:0; background:rgba(0,0,0,.4); display:flex; align-items:center; justify-content:center; z-index:2000;">
          <div style="background:#fff; width:100%; max-width:500px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2); padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
              <h3 style="margin:0; color:#dc2626;">❌ Justificativa do Cancelamento</h3>
              <button onclick="closeReasonModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
            </div>
            <div style="padding:16px; background:#fef2f2; border-left:4px solid #dc2626; border-radius:4px;">
              <p style="margin:0; color:#991b1b; line-height:1.6;">${reason}</p>
            </div>
            <div style="margin-top:20px; text-align:right;">
              <button onclick="closeReasonModal()" style="padding:10px 20px; background:#dc2626; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Fechar</button>
            </div>
          </div>
        </div>
      `;
      document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
    
    function closeReasonModal() {
      const modal = document.getElementById('reasonModal');
      if (modal) modal.remove();
    }
  </script>
</body>

</html>

