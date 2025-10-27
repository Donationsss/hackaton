<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/pages/reservas.php?error=' . urlencode('Método não permitido')));
    exit;
}

$slot_id = (int)($_POST['slot_id'] ?? 0);

if ($slot_id <= 0) {
    header('Location: ' . url('/pages/reservas.php?error=' . urlencode('ID inválido')));
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM reservas WHERE id=? AND status=\'livre\'');
    $stmt->execute([$slot_id]);
    
    if ($stmt->rowCount() === 0) {
        header('Location: ' . url('/pages/reservas.php?error=' . urlencode('Slot não encontrado ou não é livre')));
        exit;
    }
    
    header('Location: ' . url('/pages/reservas.php?success=' . urlencode('Slot removido com sucesso')));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/pages/reservas.php?error=' . urlencode('Erro ao remover slot: ' . $e->getMessage())));
    exit;
}
?>

