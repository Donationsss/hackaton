<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/pages/reservas.php'));
    exit;
}

$space_id = (int)($_POST['space_id'] ?? 0);
$date = $_POST['date'] ?? '';
$time_start = $_POST['time_start'] ?? '';
$time_end = $_POST['time_end'] ?? '';

if ($space_id <= 0 || !$date || !$time_start || !$time_end) {
    header('Location: ' . url('/pages/reservas.php?error=' . urlencode('Preencha todos os campos')));
    exit;
}

try {
    $user = current_user();
    $stmt = $pdo->prepare('INSERT INTO reservas (space_id, date, time_start, time_end, status, created_by, created_at) VALUES (?,?,?,?,?,?,NOW())');
    $stmt->execute([$space_id, $date, $time_start, $time_end, 'livre', (int)$user['id']]);
    header('Location: ' . url('/pages/reservas.php?success=' . urlencode('Slot criado com sucesso')));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/pages/reservas.php?error=' . urlencode('Erro: '.$e->getMessage())));
    exit;
}
