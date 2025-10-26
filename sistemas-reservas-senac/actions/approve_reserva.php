<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/admin.php'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . url('/admin.php?create_error=ID inválido'));
    exit;
}

try {
    $user = current_user();
    $stmt = $pdo->prepare("UPDATE reservas SET status='reservado', approved_by=?, approved_at=NOW() WHERE id=? AND status='proposta'");
    $stmt->execute([(int)$user['id'], $id]);
} catch (Throwable $e) {
    header('Location: ' . url('/admin.php?create_error=' . urlencode('Falha ao aprovar: '.$e->getMessage())));
    exit;
}

header('Location: ' . url('/admin.php?create_success=Reserva aprovada'));
exit;
