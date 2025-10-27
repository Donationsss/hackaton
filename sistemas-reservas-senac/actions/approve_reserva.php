<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . url('/dashboard.php?error=ID inválido'));
    exit;
}

try {
    $user = current_user();
    $stmt = $pdo->prepare("UPDATE reservas SET status='reservado', approved_by=?, approved_at=NOW() WHERE id=? AND status='proposta'");
    $stmt->execute([(int)$user['id'], $id]);
    header('Location: ' . url('/dashboard.php?success=Reserva aprovada'));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/dashboard.php?error=' . urlencode('Falha ao aprovar: '.$e->getMessage())));
    exit;
}
