<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
require_role('administrador');

$user = current_user();

// Criar slot livre (data/hora)
$create_error = $_GET['create_error'] ?? '';
$create_success = $_GET['create_success'] ?? '';

// Carregar espaços
$spaces = [];
try {
  $st = $pdo->query('SELECT id, name FROM spaces ORDER BY name');
  $spaces = $st->fetchAll();
} catch (Throwable $e) {
  $spaces = [];
}

// Buscar propostas pendentes
$stmt = $pdo->query("SELECT r.*, s.name AS space_name, u.name AS criado_por
                     FROM reservas r
                     LEFT JOIN spaces s ON s.id = r.space_id
                     LEFT JOIN users u ON u.id = r.created_by
                     WHERE r.status = 'proposta'
                     ORDER BY r.date, r.time_start");
$propostas = $stmt->fetchAll();

// Todas as reservas (visão geral)
$stmt2 = $pdo->query("SELECT r.*, u1.name AS criado_por, u2.name AS aprovado_por
                      FROM reservas r
                      LEFT JOIN users u1 ON u1.id = r.created_by
                      LEFT JOIN users u2 ON u2.id = r.approved_by
                      ORDER BY r.date DESC, r.time_start DESC
                      LIMIT 100");
$todas = $stmt2->fetchAll();
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - Administrador</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 1.5rem; }
    header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #ddd; padding:.5rem; text-align:left; }
    .card { border:1px solid #ddd; padding:1rem; border-radius:8px; margin-bottom:1rem; }
    .row { margin-bottom:.5rem; }
    input { padding:.4rem; }
    .actions { display:flex; gap:.5rem; align-items:center; }
    .success { color:#2e7d32; }
    .error { color:#b00020; }
    .pill { padding:.2rem .5rem; border-radius:12px; background:#eee; }
  </style>
</head>
<body>
  <header>
    <h2>Bem-vindo, <?php echo htmlspecialchars($user['name']); ?> (Administrador)</h2>
    <div class="actions">
      <a href="<?php echo htmlspecialchars(url('/visualizador.php')); ?>">Ver reservas livres</a>
      <a href="<?php echo htmlspecialchars(url('/logout.php')); ?>">Sair</a>
    </div>
  </header>

  <div class="card">
    <h3>Criar slot livre</h3>
    <?php if ($create_success): ?><div class="success"><?php echo htmlspecialchars($create_success); ?></div><?php endif; ?>
    <?php if ($create_error): ?><div class="error"><?php echo htmlspecialchars($create_error); ?></div><?php endif; ?>
    <form method="post" action="<?php echo htmlspecialchars(url('/actions/create_slot.php')); ?>">
      <div class="row">
        <label>Espaço</label>
        <select name="space_id" required>
          <option value="">Selecione um espaço</option>
          <?php foreach ($spaces as $sp): ?>
            <option value="<?php echo (int)$sp['id']; ?>"><?php echo htmlspecialchars($sp['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="row">
        <label>Data</label>
        <input type="date" name="date" required />
      </div>
      <div class="row">
        <label>Hora início</label>
        <input type="time" name="time_start" required />
      </div>
      <div class="row">
        <label>Hora fim</label>
        <input type="time" name="time_end" required />
      </div>
      <button type="submit">Criar slot</button>
    </form>
  </div>

  <div class="card">
    <h3>Propostas pendentes</h3>
    <?php if (!$propostas): ?>
      <p>Sem propostas no momento.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Espaço</th>
            <th>Data</th>
            <th>Início</th>
            <th>Fim</th>
            <th>Título</th>
            <th>Solicitante</th>
            <th>Criado por</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($propostas as $p): ?>
            <tr>
              <td><?php echo htmlspecialchars($p['space_name'] ?? '—'); ?></td>
              <td><?php echo htmlspecialchars($p['date']); ?></td>
              <td><?php echo htmlspecialchars(substr($p['time_start'],0,5)); ?></td>
              <td><?php echo htmlspecialchars(substr($p['time_end'],0,5)); ?></td>
              <td><?php echo htmlspecialchars($p['request_title'] ?? '—'); ?></td>
              <td><?php echo htmlspecialchars($p['requester_name'] ?? '—'); ?></td>
              <td><?php echo htmlspecialchars($p['criado_por'] ?? '—'); ?></td>
              <td class="actions">
                <form method="post" action="<?php echo htmlspecialchars(url('/actions/approve_reserva.php')); ?>" style="display:inline">
                  <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>" />
                  <button type="submit">Aprovar</button>
                </form>
                <form method="post" action="<?php echo htmlspecialchars(url('/actions/reject_reserva.php')); ?>" style="display:inline">
                  <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>" />
                  <button type="submit">Rejeitar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3>Últimas reservas</h3>
    <table>
      <thead>
        <tr>
          <th>Data</th>
          <th>Início</th>
          <th>Fim</th>
          <th>Status</th>
          <th>Criado por</th>
          <th>Aprovado por</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($todas as $r): ?>
          <tr>
            <td><?php echo htmlspecialchars($r['date']); ?></td>
            <td><?php echo htmlspecialchars(substr($r['time_start'],0,5)); ?></td>
            <td><?php echo htmlspecialchars(substr($r['time_end'],0,5)); ?></td>
            <td><span class="pill"><?php echo htmlspecialchars($r['status']); ?></span></td>
            <td><?php echo htmlspecialchars($r['criado_por'] ?? '—'); ?></td>
            <td><?php echo htmlspecialchars($r['aprovado_por'] ?? '—'); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
