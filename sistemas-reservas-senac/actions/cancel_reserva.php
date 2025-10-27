<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

if (empty($reason)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Justificativa obrigatória']);
    exit;
}

try {
    // Atualizar status para 'cancelado' e salvar a justificativa
    $stmt = $pdo->prepare("UPDATE reservas SET status='cancelado', cancel_reason=? WHERE id=? AND status IN ('proposta', 'reservado')");
    $stmt->execute([$reason, $id]);
    
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Reserva não encontrada ou já cancelada']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Reserva cancelada com sucesso']);
    exit;
} catch (Throwable $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao cancelar: '.$e->getMessage()]);
    exit;
}

