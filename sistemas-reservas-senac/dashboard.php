<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
require_role('administrador');

$user = current_user();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Buscar estatísticas do banco de dados
// Reservas hoje
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservas WHERE DATE(date) = CURDATE() AND status = 'reservado'");
$stmt->execute();
$todayReservations = $stmt->fetch()['count'] ?? 0;

// Reservas pendentes
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservas WHERE status = 'proposta'");
$stmt->execute();
$pendingReservations = $stmt->fetch()['count'] ?? 0;

// Total de reservas
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reservas");
$stmt->execute();
$totalReservations = $stmt->fetch()['count'] ?? 0;

// Taxa de aprovação
$stmt = $pdo->prepare("SELECT 
    COUNT(CASE WHEN status = 'reservado' THEN 1 END) as approved,
    COUNT(*) as total 
    FROM reservas 
    WHERE status IN ('reservado', 'proposta')");
$stmt->execute();
$stats = $stmt->fetch();
$approvalRate = $stats['total'] > 0 ? round(($stats['approved'] / $stats['total']) * 100) : 0;

// Reservas recentes
$stmt = $pdo->prepare("SELECT r.*, s.name AS space_name, u.name AS requester_name 
                         FROM reservas r
                         LEFT JOIN spaces s ON s.id = r.space_id
                         LEFT JOIN users u ON u.id = r.created_by
                         WHERE r.status IN ('reservado', 'proposta')
                         ORDER BY r.date DESC, r.time_start DESC
                         LIMIT 5");
$stmt->execute();
$recentReservations = $stmt->fetchAll();

// Espaços disponíveis
$stmt = $pdo->prepare("SELECT * FROM spaces ORDER BY name LIMIT 4");
$stmt->execute();
$spaces = $stmt->fetchAll();

// Calendário - gerar dias do mês atual
$today = new DateTime();
$firstDayOfMonth = new DateTime($today->format('Y-m-01'));
$lastDayOfMonth = new DateTime($today->format('Y-m-t'));
$calendarDays = [];
$day = clone $firstDayOfMonth;
while ($day <= $lastDayOfMonth) {
    $calendarDays[] = $day->format('Y-m-d');
    $day->modify('+1 day');
}

// Buscar reservas do mês atual para marcar no calendário
$currentMonthStart = $today->format('Y-m-01');
$currentMonthEnd = $today->format('Y-m-t');
$stmt = $pdo->prepare("SELECT date, COUNT(*) as count FROM reservas WHERE date >= ? AND date <= ? GROUP BY date");
$stmt->execute([$currentMonthStart, $currentMonthEnd]);
$reservationsByDate = [];
while ($row = $stmt->fetch()) {
    $dayNum = (int)date('d', strtotime($row['date']));
    $reservationsByDate[$dayNum] = (int)$row['count'];
}

// Reservas pendentes (propostas)
$stmt = $pdo->prepare("SELECT r.*, s.name AS space_name, u.name AS requester_name 
                         FROM reservas r
                         LEFT JOIN spaces s ON s.id = r.space_id
                         LEFT JOIN users u ON u.id = r.created_by
                         WHERE r.status = 'proposta'
                         ORDER BY r.date, r.time_start
                         LIMIT 5");
$stmt->execute();
$pendingActions = $stmt->fetchAll();

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
  <title>Dashboard - Sistema SENAC</title>
  <link rel="stylesheet" href="./css/comum.css" />
  <link rel="stylesheet" href="./css/dashboard.css" />
</head>

<body>
  <header class="header">
    <div class="container">
      <div class="header-top">
        <div class="logo">
          <div class="logo-icon"><img src="./imagem.jpg" alt="Senac" style="width: 120px; height: 75px;">
          </div>
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
              <a href="./logout.php" class="user-menu-item" style="display:block; padding:8px 10px; border-radius:6px; color:inherit; text-decoration:none;">Sair</a>
            </div>
          </div>
        </div>
      </div>

      <nav class="nav">
        <a href="./dashboard.php" class="nav-link active">📊 Dashboard</a>
        <a href="./pages/reservas.php" class="nav-link">📅 Reservas</a>
        <a href="./pages/espacos.php" class="nav-link">🏢 Espaços</a>
        <a href="./pages/relatorios.php" class="nav-link">📈 Relatórios</a>
        <a href="./pages/colaboradores-dashboard.php" class="nav-link">👥 Colaboradores</a>
        <a href="./pages/configuracoes.php" class="nav-link">⚙️ Configurações</a>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
      <div class="page-header">
        <div>
          <h2 class="page-title">Dashboard</h2>
          <p class="page-subtitle">Bem-vindo ao Sistema de Reservas SENAC</p>
          <?php if (!empty($success)): ?><div class="stat-badge stat-badge-success" style="margin-top:8px; display:inline-block;"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
          <?php if (!empty($error)): ?><div class="stat-badge stat-badge-warning" style="margin-top:8px; display:inline-block;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        </div>
        <div class="page-actions">
          <button class="btn btn-secondary" data-action="refresh">
            <span class="btn-icon">🔄</span>
            Atualizar
          </button>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card stat-primary">
          <div class="stat-icon">📅</div>
          <div class="stat-content">
            <h3 class="stat-number"><?php echo $todayReservations; ?></h3>
            <p class="stat-label">Reservas Hoje</p>
            <span class="stat-badge stat-badge-success">Em tempo real</span>
          </div>
        </div>

        <div class="stat-card stat-warning">
          <div class="stat-icon">⏳</div>
          <div class="stat-content">
            <h3 class="stat-number"><?php echo $pendingReservations; ?></h3>
            <p class="stat-label">Aguardando Aprovação</p>
            <span class="stat-badge stat-badge-warning">Requer atenção</span>
          </div>
        </div>

        <div class="stat-card stat-info">
          <div class="stat-icon">📊</div>
          <div class="stat-content">
            <h3 class="stat-number"><?php echo $totalReservations; ?></h3>
            <p class="stat-label">Total de Reservas</p>
            <span class="stat-badge stat-badge-info">Todas as reservas</span>
          </div>
        </div>

        <div class="stat-card stat-success">
          <div class="stat-icon">📈</div>
          <div class="stat-content">
            <h3 class="stat-number"><?php echo $approvalRate; ?>%</h3>
            <p class="stat-label">Taxa de Aprovação</p>
            <span class="stat-badge stat-badge-success">Excelente</span>
          </div>
        </div>
      </div>

      <div class="dashboard-grid">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">📋 Reservas Recentes</h3>
            <a href="./pages/reservas.php" class="card-link">Ver todas →</a>
          </div>
          <div class="card-body">
            <?php if (!$recentReservations): ?>
              <p style="text-align: center; padding: 32px; color: #999">Nenhuma reserva recente.</p>
            <?php else: ?>
              <div class="list">
                <?php foreach ($recentReservations as $r): ?>
                  <div class="list-item" style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid var(--gray-100); gap:16px;">
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
                      </div>
                      <?php if (!empty($r['requester_name'])): ?>
                        <div style="font-size:12px; color: var(--gray-600); margin-top:4px;">
                          Por: <?php echo htmlspecialchars($r['requester_name']); ?>
                        </div>
                      <?php endif; ?>
                      <div style="font-size:12px; color: var(--gray-600); margin-top:4px;">
                        Status: 
                        <?php if ($r['status'] == 'proposta'): ?>
                          <span class="stat-badge stat-badge-warning">Pendente</span>
                        <?php elseif ($r['status'] == 'reservado'): ?>
                          <span class="stat-badge stat-badge-success">Confirmado</span>
                        <?php else: ?>
                          <span class="stat-badge stat-badge-info"><?php echo htmlspecialchars($r['status']); ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">📆 Calendário</h3>
            <div class="calendar-controls">
              <button class="btn-icon" title="Mês anterior" onclick="alert('Navegação de mês em breve')">◀</button>
              <span class="calendar-month"><?php 
                $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                echo $meses[(int)date('m') - 1] . ' ' . date('Y');
              ?></span>
              <button class="btn-icon" title="Próximo mês" onclick="alert('Navegação de mês em breve')">▶</button>
            </div>
          </div>
          <div class="card-body">
            <div class="calendar">
              <div class="calendar-header">
                <div class="calendar-day-name">Dom</div>
                <div class="calendar-day-name">Seg</div>
                <div class="calendar-day-name">Ter</div>
                <div class="calendar-day-name">Qua</div>
                <div class="calendar-day-name">Qui</div>
                <div class="calendar-day-name">Sex</div>
                <div class="calendar-day-name">Sáb</div>
              </div>
              <div class="calendar-body">
                <?php
                // Renderizar calendário
                $firstDayOfWeek = (int)$firstDayOfMonth->format('w');
                $lastDayOfMonthNum = (int)$lastDayOfMonth->format('d');
                $todayDay = (int)$today->format('d');
                $currentMonthNum = (int)$today->format('m');
                $currentYearNum = (int)$today->format('Y');
                
                // Dias vazios antes do primeiro dia
                for ($i = 0; $i < $firstDayOfWeek; $i++) {
                    echo '<div class="calendar-day calendar-day-empty"></div>';
                }
                
                // Dias do mês
                for ($day = 1; $day <= $lastDayOfMonthNum; $day++) {
                    $isToday = $day == $todayDay;
                    $hasReservations = isset($reservationsByDate[$day]);
                    $classes = 'calendar-day';
                    if ($isToday) $classes .= ' calendar-day-today';
                    if ($hasReservations) $classes .= ' calendar-day-reserved';
                    
                    echo '<div class="' . $classes . '" data-day="' . $day . '">' . $day;
                    if ($hasReservations) {
                        echo '<span class="day-indicator" title="' . $reservationsByDate[$day] . ' reserva(s)"></span>';
                    }
                    echo '</div>';
                }
                ?>
              </div>
            </div>
            <div
              style="
                  margin-top: 16px;
                  padding-top: 16px;
                  border-top: 1px solid var(--gray-100);
                  font-size: 12px;
                  color: var(--gray-600);
                ">
              <div style="display: flex; gap: 16px; flex-wrap: wrap">
                <div style="display: flex; align-items: center; gap: 6px">
                  <div
                    style="
                        width: 12px;
                        height: 12px;
                        background: var(--primary);
                        border-radius: 50%;
                      "></div>
                  <span>Dia atual</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px">
                  <div
                    style="
                        width: 12px;
                        height: 12px;
                        border: 2px solid var(--secondary);
                        border-radius: 50%;
                      "></div>
                  <span>Com reservas</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">🏢 Espaços Disponíveis</h3>
            <a href="./pages/espacos.php" class="card-link">Ver todos →</a>
          </div>
          <div class="card-body">
            <?php if (!$spaces): ?>
              <p style="text-align: center; padding: 32px; color: #999">Nenhum espaço cadastrado.</p>
            <?php else: ?>
              <?php foreach ($spaces as $space): ?>
                <div class="space-item">
                  <div class="space-header">
                    <strong class="space-name"><?php echo htmlspecialchars($space['name']); ?></strong>
                    <span class="stat-badge stat-badge-info"><?php echo htmlspecialchars($space['type']); ?></span>
                  </div>
                  <div class="space-details">
                    <?php echo htmlspecialchars($space['type']); ?> • Capacidade: <?php echo (int)$space['capacity']; ?> pessoas
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="card card-warning">
          <div class="card-header">
            <h3 class="card-title">⚠️ Ações Pendentes</h3>
          </div>
          <div class="card-body">
            <?php if (!$pendingActions): ?>
              <p style="text-align: center; padding: 32px; color: #999">Nenhuma ação pendente.</p>
            <?php else: ?>
              <?php foreach ($pendingActions as $action): ?>
                <div class="pending-item">
                  <div class="pending-header">
                    <strong class="pending-title">
                      <?php echo htmlspecialchars($action['space_name'] ?? 'Espaço'); ?>
                      <?php if (!empty($action['request_title'])): ?>
                        - <?php echo htmlspecialchars($action['request_title']); ?>
                      <?php endif; ?>
                    </strong>
                  </div>
                  <div class="pending-details">
                    <?php if (!empty($action['requester_name'])): ?>
                      <?php echo htmlspecialchars($action['requester_name']); ?> • 
                    <?php endif; ?>
                    <?php echo htmlspecialchars(date('d/m/Y', strtotime($action['date']))); ?> • 
                    <?php echo htmlspecialchars(substr($action['time_start'], 0, 5)); ?>-<?php echo htmlspecialchars(substr($action['time_end'], 0, 5)); ?>
                  </div>
                  <div class="pending-actions">
                    <a href="<?php echo url('/actions/approve_reserva.php?id=' . (int)$action['id']); ?>" class="btn btn-xs btn-success">✓ Aprovar</a>
                    <a href="<?php echo url('/actions/reject_reserva.php?id=' . (int)$action['id']); ?>" class="btn btn-xs btn-danger">✗ Rejeitar</a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="container">
      <p>
        &copy; 2025 SENAC - Sistema de Reservas. Todos os direitos reservados.
      </p>
    </div>
  </footer>

  <!-- Modal Calendário -->
  <div id="calendarModal" style="position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:2000;">
    <div style="background:#fff; width:100%; max-width:600px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2); max-height:80vh; overflow-y:auto;">
      <div style="padding:16px 20px; border-bottom:1px solid var(--gray-100); display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:#fff; z-index:10;">
        <h3 id="calendarModalTitle" style="margin:0;">Detalhes do Dia</h3>
        <button id="closeCalendarModal" class="btn btn-sm btn-secondary">Fechar</button>
      </div>
      <div id="calendarModalContent" style="padding:16px 20px;">
        <!-- Conteúdo será preenchido dinamicamente -->
      </div>
    </div>
  </div>

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

      // Modal Calendário
      const calendarModal = document.getElementById('calendarModal');
      const closeCalendarModal = document.getElementById('closeCalendarModal');
      
      if (closeCalendarModal) {
        closeCalendarModal.addEventListener('click', function() {
          calendarModal.style.display = 'none';
        });
      }
      
      calendarModal.addEventListener('click', function(e) {
        if (e.target === calendarModal) {
          calendarModal.style.display = 'none';
        }
      });

      // Dados das reservas do PHP
      const reservations = <?php echo json_encode($recentReservations); ?>;
      const pendingActions = <?php echo json_encode($pendingActions); ?>;
      
      // Formatar reservas por data para o calendário
      const reservationsByDate = {};
      [...recentReservations, ...pendingActions].forEach(function(r) {
        const dateKey = r.date;
        if (!reservationsByDate[dateKey]) {
          reservationsByDate[dateKey] = [];
        }
        reservationsByDate[dateKey].push(r);
      });

      // Calendário clicável  
      document.querySelectorAll('.calendar-day[data-day]').forEach(function(dayElement) {
        dayElement.addEventListener('click', function(e) {
          e.stopPropagation();
          
          if (this.classList.contains('calendar-day-empty')) return;
          
          // Extrair o dia clicado
          const clickedDay = parseInt(this.dataset.day);
          
          // Buscar reservas para este dia específico
          const dayReservations = [...recentReservations, ...pendingActions].filter(function(res) {
            if (!res.date) return false;
            const resDate = new Date(res.date);
            const resDay = resDate.getDate();
            return resDay == clickedDay;
          });

          // Atualizar título do modal
          document.getElementById('calendarModalTitle').textContent = `Reservas do dia ${String(clickedDay).padStart(2, '0')}/<?php echo date('m/Y'); ?>`;

          if (dayReservations.length > 0) {
            let content = `<div style="margin-bottom:16px; color:#6b7280;">${dayReservations.length} ${dayReservations.length === 1 ? 'reserva encontrada' : 'reservas encontradas'}</div>`;
            
            dayReservations.forEach(function(res) {
              content += `
                <div style="padding:12px; background:#f9fafb; border-radius:8px; margin-bottom:12px;">
                  <div style="font-weight:600; margin-bottom:4px;">${res.space_name || 'Espaço'}${res.request_title ? ' - ' + res.request_title : ''}</div>
                  <div style="font-size:13px; color:#6b7280;">${res.time_start ? res.time_start.substring(0,5) : ''} - ${res.time_end ? res.time_end.substring(0,5) : ''}</div>
                  ${res.requester_name ? `<div style="font-size:13px; color:#6b7280;">Por: ${res.requester_name}</div>` : ''}
                  <div style="margin-top:8px;">
                    <span style="padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600; 
                      background: ${res.status === 'reservado' ? '#dcfce7' : '#fef3c7'}; 
                      color: ${res.status === 'reservado' ? '#166534' : '#92400e'};">
                      ${res.status === 'reservado' ? '✅ Aprovada' : '⏳ Pendente'}
                    </span>
                  </div>
                </div>
              `;
            });
            
            document.getElementById('calendarModalContent').innerHTML = content;
          } else {
            document.getElementById('calendarModalContent').innerHTML = '<p style="text-align:center; padding:32px;">Nenhuma reserva para este dia.</p>';
          }
          
          calendarModal.style.display = 'flex';
        });
      });
    });
  </script>
</body>

</html>