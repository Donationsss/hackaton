<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('colaborador');

$user = current_user();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Buscar todos os espaços
$stmt = $pdo->prepare("SELECT * FROM spaces ORDER BY name");
$stmt->execute();
$spaces = $stmt->fetchAll();

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

// Função para obter ícone baseado no nome do espaço
function get_space_icon(string $spaceName): string {
    $spaceNameLower = mb_strtolower($spaceName, 'UTF-8');
    
    if (strpos($spaceNameLower, 'auditório') !== false || strpos($spaceNameLower, 'auditorio') !== false) {
        return '🎭';
    } elseif (strpos($spaceNameLower, 'laboratório') !== false || strpos($spaceNameLower, 'laboratorio') !== false) {
        return '💻';
    } elseif (strpos($spaceNameLower, 'reunião') !== false || strpos($spaceNameLower, 'reuniao') !== false || strpos($spaceNameLower, 'executiva') !== false) {
        return '👔';
    } elseif (strpos($spaceNameLower, 'aula') !== false) {
        return '📚';
    }
    
    return '🏢';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Espaços - Sistema SENAC</title>
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
        <a href="./espacos.php" class="nav-link active">🏢 Espaços</a>
        <a href="./minhas-reservas.php" class="nav-link">📋 Minhas Reservas</a>
      </nav>
    </div>
  </header>

  <main class="main-content">
    <div class="container">
      <div class="page-header">
        <div>
          <h2 class="page-title">Espaços Disponíveis</h2>
          <p class="page-subtitle">Visualize os espaços disponíveis para reserva</p>
        </div>
      </div>

      <div class="spaces-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:24px; margin-top:24px;">
        <?php if (!$spaces): ?>
          <p style="text-align: center; padding: 32px; color: #999">Nenhum espaço disponível.</p>
        <?php else: ?>
          <?php foreach ($spaces as $space): ?>
            <div class="space-card" style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.08); padding:20px;">
              <div style="text-align:center; font-size:48px; margin-bottom:12px;"><?php echo get_space_icon($space['name']); ?></div>
              <h3 style="margin-bottom:8px; text-align:center;"><?php echo htmlspecialchars($space['name']); ?></h3>
              <div style="text-align:center; color:#6b7280; font-size:14px; margin-bottom:16px;">
                <?php echo ucfirst(str_replace('_', ' ', $space['type'])); ?>
              </div>
              <div style="display:flex; justify-content:center; gap:16px; margin-bottom:16px;">
                <div style="text-align:center;">
                  <div style="font-size:24px; font-weight:600; color:#004A8D;"><?php echo (int)$space['capacity']; ?></div>
                  <div style="font-size:12px; color:#6b7280;">Capacidade</div>
                </div>
              </div>
              <div style="background:#f9fafb; padding:12px; border-radius:8px; font-size:13px; color:#6b7280;">
                <div style="margin-bottom:6px;">✓ Equipamentos modernos</div>
                <div style="margin-bottom:6px;">✓ Ar-condicionado</div>
                <div>✓ Wi-Fi disponível</div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
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
  </script>
</body>

</html>

