<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/pages/espacos.php'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$capacity = (int)($_POST['capacity'] ?? 0);

if ($id <= 0 || $name === '' || $type === '' || $capacity <= 0) {
    header('Location: ' . url('/pages/espacos.php?error=' . urlencode('Preencha todos os campos corretamente')));
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE spaces SET name = ?, type = ?, capacity = ? WHERE id = ?');
    $stmt->execute([$name, $type, $capacity, $id]);
    
    header('Location: ' . url('/pages/espacos.php?success=' . urlencode('Espaço atualizado com sucesso')));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/pages/espacos.php?error=' . urlencode('Erro ao atualizar espaço: ' . $e->getMessage())));
    exit;
}
?>

