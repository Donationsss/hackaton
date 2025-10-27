<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/pages/espacos.php'));
    exit;
}

$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$capacity = (int)($_POST['capacity'] ?? 0);

if ($name === '' || $type === '' || $capacity <= 0) {
    header('Location: ' . url('/pages/espacos.php?error=' . urlencode('Preencha todos os campos corretamente')));
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO spaces (name, type, capacity) VALUES (?, ?, ?)');
    $stmt->execute([$name, $type, $capacity]);
    
    header('Location: ' . url('/pages/espacos.php?success=' . urlencode('Espaço criado com sucesso')));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/pages/espacos.php?error=' . urlencode('Erro ao criar espaço: ' . $e->getMessage())));
    exit;
}
?>

