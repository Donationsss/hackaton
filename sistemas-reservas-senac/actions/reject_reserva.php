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
    $stmt = $pdo->prepare("UPDATE reservas SET status='livre', approved_by=NULL, approved_at=NULL WHERE id=? AND status='proposta'");
    $stmt->execute([$id]);
    header('Location: ' . url('/dashboard.php?success=Reserva rejeitada'));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/dashboard.php?error=' . urlencode('Falha ao rejeitar: '.$e->getMessage())));
    exit;
}
