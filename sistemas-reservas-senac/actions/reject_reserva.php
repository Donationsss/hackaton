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
    $stmt = $pdo->prepare("UPDATE reservas SET status='livre', approved_by=NULL, approved_at=NULL WHERE id=? AND status='proposta'");
    $stmt->execute([$id]);
} catch (Throwable $e) {
    header('Location: ' . url('/admin.php?create_error=' . urlencode('Falha ao rejeitar: '.$e->getMessage())));
    exit;
}

header('Location: ' . url('/admin.php?create_success=Proposta rejeitada'));
exit;
