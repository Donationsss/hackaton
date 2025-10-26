<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('visualizador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/visualizador.php'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['request_title'] ?? '');
$note = trim($_POST['request_note'] ?? '');
$name = trim($_POST['request_name'] ?? '');
if ($id <= 0) {
    header('Location: ' . url('/visualizador.php?error=' . urlencode('ID inválido')));
    exit;
}

try {
    $user = current_user();
    // Marca slot como proposta se ainda estiver livre e registra detalhes
$stmt = $pdo->prepare("UPDATE reservas SET status='proposta', created_by=?, created_at=NOW(), request_title=?, request_note=?, requester_name=? WHERE id=? AND status='livre'");
    $stmt->execute([(int)$user['id'], $title ?: null, $note ?: null, $name ?: null, $id]);

    // Verifica se algo foi atualizado
    if ($stmt->rowCount() === 0) {
        header('Location: ' . url('/visualizador.php?error=' . urlencode('O slot não está mais disponível.')));
        exit;
    }

    header('Location: ' . url('/visualizador.php?success=' . urlencode('Solicitação enviada ao administrador.')));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/visualizador.php?error=' . urlencode('Erro ao solicitar: '.$e->getMessage())));
    exit;
}
