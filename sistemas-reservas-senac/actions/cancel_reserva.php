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

if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    // Atualizar status para 'livre' (cancela a reserva)
    $stmt = $pdo->prepare("UPDATE reservas SET status='livre', request_title=NULL, request_note=NULL, created_by=NULL WHERE id=? AND status IN ('proposta', 'reservado')");
    $stmt->execute([$id]);
    
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

