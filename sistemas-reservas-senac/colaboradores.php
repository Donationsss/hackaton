<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
require_role('colaborador');

$user = current_user();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Buscar slots livres (futuros)
$stmt = $pdo->prepare("SELECT r.*, s.name AS space_name, s.type AS space_type, s.capacity
                         FROM reservas r
                         LEFT JOIN spaces s ON s.id = r.space_id
                         WHERE r.status = 'livre' AND r.date >= CURDATE()
                         ORDER BY r.date, r.time_start");
$stmt->execute();
$livres = $stmt->fetchAll();

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
                <div class="role">Colaborador</div>
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
        <a href="./colaboradores.php" class="nav-link active">📅 Solicitar Reserva</a>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
      <div class="page-header">
        <div>
          <h2 class="page-title">Solicitar Reservas</h2>
          <p class="page-subtitle">Veja os horários livres e solicite suas reservas</p>
          <?php if (!empty($success)): ?><div class="stat-badge stat-badge-success" style="margin-top:8px; display:inline-block; "><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
          <?php if (!empty($error)): ?><div class="stat-badge stat-badge-warning" style="margin-top:8px; display:inline-block; "><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        </div>
        <div class="page-actions">
          <a class="btn btn-secondary" href="<?php echo htmlspecialchars(url('/colaboradores.php')); ?>">🔄 Atualizar</a>
        </div>
      </div>

      <div class="dashboard-grid">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">📋 Slots Livres</h3>
          </div>
          <div class="card-body">
            <?php if (!$livres): ?>
              <p style="text-align: center; padding: 32px; color: #999">Nenhum slot livre disponível.</p>
            <?php else: ?>
              <div class="list">
                <?php foreach ($livres as $r): ?>
                  <div class="list-item" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--gray-100); gap:16px;">
                    <div>
                      <div style="font-weight:600;">
                        <?php echo htmlspecialchars($r['space_name'] ?? 'Espaço não informado'); ?>
                      </div>
                      <div style="font-size:12px; color: var(--gray-600); margin-top:4px;">
                        <?php echo htmlspecialchars(date('d/m/Y', strtotime($r['date']))); ?> •
                        <?php echo htmlspecialchars(substr($r['time_start'], 0, 5)); ?> - <?php echo htmlspecialchars(substr($r['time_end'], 0, 5)); ?>
                        <?php if (!empty($r['capacity'])): ?> • Capacidade: <?php echo (int)$r['capacity']; ?><?php endif; ?>
                      </div>
                      <div style="font-size:12px; color: var(--gray-600); margin-top:4px;">Status: <span class="stat-badge stat-badge-info">livre</span></div>
                    </div>
                    <div>
                      <button class="btn btn-primary" type="button"
                        data-action="open-request"
                        data-id="<?php echo (int)$r['id']; ?>"
                        data-space="<?php echo htmlspecialchars($r['space_name'] ?? ''); ?>"
                        data-date="<?php echo htmlspecialchars(date('d/m/Y', strtotime($r['date']))); ?>"
                        data-start="<?php echo htmlspecialchars(substr($r['time_start'], 0, 5)); ?>"
                        data-end="<?php echo htmlspecialchars(substr($r['time_end'], 0, 5)); ?>">Solicitar reserva</button>
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

  <!-- Modal Solicitar Reserva -->
  <div id="requestModal" style="position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:2000;">
    <div style="background:#fff; width:100%; max-width:520px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
      <div style="padding:16px 20px; border-bottom:1px solid var(--gray-100); display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;">Solicitar reserva</h3>
        <button id="closeRequestModal" class="btn btn-sm btn-secondary">Fechar</button>
      </div>
      <form method="post" action="<?php echo htmlspecialchars(url('/actions/solicitar_reserva.php')); ?>">
        <div style="padding:16px 20px;">
          <input type="hidden" name="id" id="req_id" />
          <div style="margin-bottom:10px; font-size:14px; color:var(--gray-700);">
            <div><strong>Espaço:</strong> <span id="req_space">—</span></div>
            <div><strong>Data:</strong> <span id="req_date">—</span></div>
            <div><strong>Horário:</strong> <span id="req_time">—</span></div>
          </div>
          <div class="row" style="margin-bottom:12px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Seu nome (opcional)</label>
            <input type="text" name="request_name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" placeholder="Ex.: Maria Silva"
              style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb;" />
          </div>
          <div class="row" style="margin-bottom:12px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Título (opcional)</label>
            <input type="text" name="request_title" placeholder="Ex.: Reunião do projeto X"
              style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb;" />
          </div>
          <div class="row" style="margin-bottom:4px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">Observações (opcional)</label>
            <textarea name="request_note" rows="4" placeholder="Inclua detalhes úteis para o administrador"
              style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb; resize:vertical;"></textarea>
          </div>
        </div>
        <div style="padding:12px 20px; border-top:1px solid var(--gray-100); display:flex; gap:8px; justify-content:flex-end;">
          <button type="button" id="cancelRequest" class="btn btn-secondary">Cancelar</button>
          <button type="submit" class="btn btn-primary">Enviar solicitação</button>
        </div>
      </form>
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
          // Ajusta largura do dropdown para o mesmo tamanho do botão/perfil
          const w = toggle.getBoundingClientRect().width;
          dropdown.style.width = w + 'px';
          dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function() {
          dropdown.style.display = 'none';
        });
      });

      // Modal Solicitar Reserva
      const modal = document.getElementById('requestModal');
      const closeBtn = document.getElementById('closeRequestModal');
      const cancelBtn = document.getElementById('cancelRequest');

      function openModal(data) {
        document.getElementById('req_id').value = data.id;
        document.getElementById('req_space').textContent = data.space;
        document.getElementById('req_date').textContent = data.date;
        document.getElementById('req_time').textContent = data.start + ' - ' + data.end;
        modal.style.display = 'flex';
      }

      function closeModal() {
        modal.style.display = 'none';
      }
      closeBtn.addEventListener('click', closeModal);
      cancelBtn.addEventListener('click', closeModal);
      modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
      });
      document.querySelectorAll('[data-action="open-request"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
          openModal({
            id: this.dataset.id,
            space: this.dataset.space,
            date: this.dataset.date,
            start: this.dataset.start,
            end: this.dataset.end,
          });
        });
      });
    });
  </script>
</body>

</html>

