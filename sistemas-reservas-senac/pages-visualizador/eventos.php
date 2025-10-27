<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('visualizador');

$user = current_user();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Buscar eventos confirmados (reservados) futuros
$stmt = $pdo->prepare("SELECT r.*, s.name AS space_name, s.type AS space_type, s.capacity, 
                              u.name AS requester_name
                         FROM reservas r
                         LEFT JOIN spaces s ON s.id = r.space_id
                         LEFT JOIN users u ON u.id = r.created_by
                         WHERE r.status = 'reservado' AND r.date >= CURDATE()
                         ORDER BY r.date ASC, r.time_start ASC");
$stmt->execute();
$events = $stmt->fetchAll();

// Buscar eventos para o mês atual
$today = new DateTime();
$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)$today->format('m');
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)$today->format('Y');
$displayDate = new DateTime("$selectedYear-$selectedMonth-01");
$firstDayOfMonth = new DateTime($displayDate->format('Y-m-01'));
$lastDayOfMonth = new DateTime($displayDate->format('Y-m-t'));

// Agrupar eventos por data
$eventsByDate = [];
foreach ($events as $event) {
    $dateKey = $event['date'];
    if (!isset($eventsByDate[$dateKey])) {
        $eventsByDate[$dateKey] = [];
    }
    $eventsByDate[$dateKey][] = $event;
}

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
  <title>Eventos Confirmados - Sistema SENAC</title>
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
                <div class="role">Visualizador</div>
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
        <a href="../visualizador.php" class="nav-link">📅 Reservas</a>
        <a href="./eventos.php" class="nav-link active">📅 Eventos Confirmados</a>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
      <div class="page-header">
        <div>
          <h2 class="page-title">Eventos Confirmados</h2>
          <p class="page-subtitle">Calendário dos eventos aprovados no sistema</p>
        </div>
      </div>

      <div class="dashboard-grid">
        <div class="card" style="flex: 1;">
          <div class="card-header">
            <h3 class="card-title">📆 Calendário de Eventos</h3>
            <div class="calendar-controls">
              <?php 
              $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
              ?>
              <button class="btn-icon" onclick="changeMonthEvents(-1)">◀</button>
              <span class="calendar-month" id="eventsCalendarMonth"><?php echo $meses[$selectedMonth - 1] . ' ' . $selectedYear; ?></span>
              <button class="btn-icon" onclick="changeMonthEvents(1)">▶</button>
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
                $isCurrentMonth = ($selectedMonth == $currentMonthNum && $selectedYear == $currentYearNum);
                
                // Dias vazios antes do primeiro dia
                for ($i = 0; $i < $firstDayOfWeek; $i++) {
                    echo '<div class="calendar-day calendar-day-empty"></div>';
                }
                
                // Dias do mês
                for ($day = 1; $day <= $lastDayOfMonthNum; $day++) {
                    $isToday = $isCurrentMonth && $day == $todayDay;
                    $dateStr = $selectedYear . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                $dayEvents = $eventsByDate[$dateStr] ?? [];
                $hasEvents = count($dayEvents) > 0;
                $canceledEvents = array_filter($dayEvents, function($e) { return $e['status'] === 'cancelado'; });
                $hasCanceled = count($canceledEvents) > 0;
                    
                    $classes = 'calendar-day';
                    if ($isToday) $classes .= ' calendar-day-today';
                    if ($hasEvents) $classes .= ' calendar-day-reserved';
                    
                    echo '<div class="' . $classes . '" data-day="' . $day . '" data-date="' . $dateStr . '" ' . ($hasEvents ? 'onclick="showEventsForDate(\'' . $dateStr . '\')" style="cursor:pointer;"' : '') . '>' . $day;
                    if ($hasEvents) {
                        echo '<span class="day-indicator" title="' . count($dayEvents) . ' evento(s)"></span>';
                        if ($hasCanceled) {
                            echo '<span class="day-indicator" style="background:#dc2626; position:absolute; bottom:2px; right:2px;" title="Eventos cancelados"></span>';
                        }
                    }
                    echo '</div>';
                }
                ?>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">📋 Eventos do Mês</h3>
          </div>
          <div class="card-body">
            <?php if (empty($events)): ?>
              <p style="text-align: center; padding: 32px; color: #999">Nenhum evento confirmado no momento.</p>
            <?php else: ?>
              <div class="list">
                <?php 
                $currentMonthEvents = array_filter($events, function($e) use ($selectedMonth, $selectedYear) {
                  $eventDate = new DateTime($e['date']);
                  return $eventDate->format('n') == $selectedMonth && $eventDate->format('Y') == $selectedYear;
                });
                if (empty($currentMonthEvents)): ?>
                  <p style="text-align: center; padding: 16px; color: #999">Nenhum evento neste mês.</p>
                <?php else: ?>
                  <?php foreach ($currentMonthEvents as $event): ?>
                    <div class="list-item" style="padding:12px 0; border-bottom:1px solid var(--gray-100);">
                      <div style="font-weight:600; margin-bottom:4px;">
                        <?php echo htmlspecialchars($event['space_name'] ?? 'Espaço'); ?>
                        <?php if (!empty($event['request_title'])): ?>
                          - <?php echo htmlspecialchars($event['request_title']); ?>
                        <?php endif; ?>
                      </div>
                      <div style="font-size:12px; color: var(--gray-600); margin-top:4px;">
                        📅 <?php echo htmlspecialchars(date('d/m/Y', strtotime($event['date']))); ?> • 
                        🕐 <?php echo htmlspecialchars(substr($event['time_start'], 0, 5)); ?> - <?php echo htmlspecialchars(substr($event['time_end'], 0, 5)); ?>
                      </div>
                      <?php if (!empty($event['requester_name'])): ?>
                        <div style="font-size:12px; color: var(--gray-600); margin-top:4px;">
                          👤 Por: <?php echo htmlspecialchars($event['requester_name']); ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
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

  <script src="../js/toast.js"></script>

  <!-- Modal Detalhes do Dia -->
  <div id="eventsModal" style="position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:2000;">
    <div style="background:#fff; width:100%; max-width:600px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2); max-height:80vh; overflow-y:auto;">
      <div style="padding:16px 20px; border-bottom:1px solid var(--gray-100); display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:#fff; z-index:10;">
        <h3 id="eventsModalTitle" style="margin:0;">Detalhes dos Eventos</h3>
        <button id="closeEventsModal" style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
      </div>
      <div id="eventsModalContent" style="padding:16px 20px;">
        <!-- Conteúdo será preenchido dinamicamente -->
      </div>
    </div>
  </div>

  <script>
    const eventsByDate = <?php echo json_encode($eventsByDate); ?>;
    
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
      
      const modal = document.getElementById('eventsModal');
      const closeBtn = document.getElementById('closeEventsModal');
      
      if (closeBtn) closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
      });
      
      modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.style.display = 'none';
      });
    });
    
    <?php if (!empty($success)): ?>
    setTimeout(() => { if (window.Toast) window.Toast.success('<?php echo htmlspecialchars($success); ?>'); }, 100);
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    setTimeout(() => { if (window.Toast) window.Toast.error('<?php echo htmlspecialchars($error); ?>'); }, 100);
    <?php endif; ?>
    
    let currentEventsMonth = <?php echo $selectedMonth; ?>;
    let currentEventsYear = <?php echo $selectedYear; ?>;
    const eventsMonthNames = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    
    function changeMonthEvents(direction) {
      currentEventsMonth += direction;
      if (currentEventsMonth < 1) {
        currentEventsMonth = 12;
        currentEventsYear--;
      } else if (currentEventsMonth > 12) {
        currentEventsMonth = 1;
        currentEventsYear++;
      }
      
      document.getElementById('eventsCalendarMonth').textContent = eventsMonthNames[currentEventsMonth - 1] + ' ' + currentEventsYear;
      renderEventsCalendar(currentEventsMonth, currentEventsYear);
    }
    
    function renderEventsCalendar(month, year) {
      const calendarBody = document.querySelector('.calendar-body');
      const today = new Date();
      const firstDay = new Date(year, month - 1, 1).getDay();
      const daysInMonth = new Date(year, month, 0).getDate();
      const isCurrentMonth = month === today.getMonth() + 1 && year === today.getFullYear();
      
      let html = '';
      
      for (let i = 0; i < firstDay; i++) {
        html += '<div class="calendar-day calendar-day-empty"></div>';
      }
      
      for (let day = 1; day <= daysInMonth; day++) {
        const isToday = isCurrentMonth && day === today.getDate();
        const dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        const dayEvents = eventsByDate[dateStr] || [];
        const hasEvents = dayEvents.length > 0;
        const hasCanceled = dayEvents.some(e => e.status === 'cancelado');
        
        let classes = 'calendar-day';
        if (isToday) classes += ' calendar-day-today';
        if (hasEvents) classes += ' calendar-day-reserved';
        
        html += `<div class="${classes}" data-day="${day}" data-date="${dateStr}" ${hasEvents ? 'onclick="showEventsForDate(\'' + dateStr + '\')" style="cursor:pointer;"' : ''}>${day}`;
        if (hasEvents) {
          html += `<span class="day-indicator" title="${dayEvents.length} evento(s)"></span>`;
          if (hasCanceled) {
            html += '<span class="day-indicator" style="background:#dc2626; position:absolute; bottom:2px; right:2px;" title="Eventos cancelados"></span>';
          }
        }
        html += '</div>';
      }
      
      calendarBody.innerHTML = html;
    }
    
    function showEventsForDate(dateStr) {
      const events = eventsByDate[dateStr] || [];
      const modal = document.getElementById('eventsModal');
      const modalTitle = document.getElementById('eventsModalTitle');
      const modalContent = document.getElementById('eventsModalContent');
      
      const parts = dateStr.split('-');
      modalTitle.textContent = `Eventos de ${String(parts[2]).padStart(2, '0')}/${String(parts[1]).padStart(2, '0')}/${parts[0]}`;
      
      if (events.length === 0) {
        modalContent.innerHTML = '<p style="text-align:center; padding:32px;">Nenhum evento confirmado para este dia.</p>';
      } else {
        let content = `<div style="margin-bottom:16px; color:#6b7280;">${events.length} ${events.length === 1 ? 'evento confirmado' : 'eventos confirmados'}</div>`;
        events.forEach(function(event) {
          content += `
            <div style="padding:12px; background:#f9fafb; border-radius:8px; margin-bottom:12px;">
              <div style="font-weight:600; margin-bottom:4px;">${event.space_name || 'Espaço'}${event.request_title ? ' - ' + event.request_title : ''}</div>
              <div style="font-size:13px; color:#6b7280;">🕐 ${event.time_start ? event.time_start.substring(0,5) : ''} - ${event.time_end ? event.time_end.substring(0,5) : ''}</div>
              ${event.requester_name ? `<div style="font-size:13px; color:#6b7280;">👤 Por: ${event.requester_name}</div>` : ''}
              ${event.request_note ? `<div style="font-size:13px; color:#6b7280; margin-top:4px;">📝 ${event.request_note}</div>` : ''}
            </div>
          `;
        });
        modalContent.innerHTML = content;
      }
      
      modal.style.display = 'flex';
    }
  </script>
</body>

</html>

