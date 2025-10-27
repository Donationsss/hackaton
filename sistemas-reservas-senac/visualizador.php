<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
require_role('visualizador');

$user = current_user();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Buscar slots livres (futuros) e reservas confirmadas
$stmt = $pdo->prepare("SELECT r.*, s.name AS space_name, s.type AS space_type, s.capacity, 
                                u.name AS requester_name, r.request_title
                         FROM reservas r
                         LEFT JOIN spaces s ON s.id = r.space_id
                         LEFT JOIN users u ON u.id = r.created_by
                         WHERE r.date >= CURDATE() AND (r.status = 'livre' OR r.status = 'reservado')
                         ORDER BY r.date, r.time_start");
$stmt->execute();
$slots = $stmt->fetchAll();

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
  <title>Reservas Disponíveis - Sistema SENAC</title>
  <link rel="stylesheet" href="./css/comum.css" />
  <link rel="stylesheet" href="./css/dashboard.css" />
</head>

<body>
  <header class="header">
    <div class="container">
      <div class="header-top">
        <div class="logo">
          <div class="logo-icon"><img src="./imagem.jpg" alt="Senac" style="width: 120px; height: 75px;"></div>
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
                <div class="role">Visualizador</div>
              </div>
              <span aria-hidden="true">▾</span>
            </button>
            <div class="user-menu-dropdown" style="position:absolute; right:0; top:calc(100% + 8px); background:#f6f8ff; border:1px solid var(--gray-100); box-shadow: 0 4px 12px rgba(0,0,0,.08); border-radius:8px; padding:6px; min-width:160px; display:none; z-index:1000; color:var(--gray-800);">
              <a href="<?php echo htmlspecialchars(url('/logout.php')); ?>" class="user-menu-item" style="display:block; padding:8px 10px; border-radius:6px; color:inherit; text-decoration:none;">Sair</a>
            </div>
          </div>
        </div>
      </div>

      <nav class="nav">
        <a href="./visualizador.php" class="nav-link active">📅 Reservas</a>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
      <div class="page-header">
        <div>
          <h2 class="page-title">Reservas Disponíveis</h2>
          <p class="page-subtitle">Veja os horários livres para próximos dias</p>
          <?php if (!empty($success)): ?><div class="stat-badge stat-badge-success" style="margin-top:8px; display:inline-block; "><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
          <?php if (!empty($error)): ?><div class="stat-badge stat-badge-warning" style="margin-top:8px; display:inline-block; "><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        </div>
        <div class="page-actions">
          <a class="btn btn-secondary" href="<?php echo htmlspecialchars(url('/visualizador.php')); ?>">🔄 Atualizar</a>
        </div>
      </div>

      <div class="dashboard-grid">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">📋 Horários e Eventos</h3>
          </div>
          <div class="card-body">
            <?php if (!$slots): ?>
              <p style="text-align: center; padding: 32px; color: #999">Nenhum horário disponível.</p>
            <?php else: ?>
              <div class="list">
                <?php foreach ($slots as $r): ?>
                  <div class="list-item" style="display:flex; flex-direction:column; padding:12px 0; border-bottom:1px solid var(--gray-100); gap:8px;">
                    <div>
                      <div style="font-weight:600;">
                        <?php echo htmlspecialchars($r['space_name'] ?? 'Espaço não informado'); ?>
                        <?php if (!empty($r['request_title'])): ?>
                          - <?php echo htmlspecialchars($r['request_title']); ?>
                        <?php endif; ?>
                      </div>
                      <div style="font-size:12px; color: var(--gray-600); margin-top:4px;">
                        <?php echo htmlspecialchars(date('d/m/Y', strtotime($r['date']))); ?> •
                        <?php echo htmlspecialchars(substr($r['time_start'], 0, 5)); ?> - <?php echo htmlspecialchars(substr($r['time_end'], 0, 5)); ?>
                        <?php if (!empty($r['capacity'])): ?> • Capacidade: <?php echo (int)$r['capacity']; ?><?php endif; ?>
                      </div>
                      <?php if (!empty($r['requester_name'])): ?>
                        <div style="font-size:12px; color: var(--gray-600); margin-top:4px;">
                          Reservado por: <?php echo htmlspecialchars($r['requester_name']); ?>
                        </div>
                      <?php endif; ?>
                      <div style="font-size:12px; color: var(--gray-600); margin-top:4px;">
                        Status: 
                        <?php if ($r['status'] == 'livre'): ?>
                          <span class="stat-badge stat-badge-info">Livre</span>
                        <?php elseif ($r['status'] == 'reservado'): ?>
                          <span class="stat-badge stat-badge-success">Reservado</span>
                        <?php else: ?>
                          <span class="stat-badge stat-badge-warning"><?php echo htmlspecialchars($r['status']); ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
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
          // Ajusta largura do dropdown para o mesmo tamanho do botão/perfil
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