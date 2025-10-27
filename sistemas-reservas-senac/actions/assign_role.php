<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/pages/colaboradores-dashboard.php'));
    exit;
}

$user_id = (int)($_POST['user_id'] ?? 0);
$role_name = trim($_POST['role_name'] ?? '');

if ($user_id <= 0 || !in_array($role_name, ['colaborador', 'administrador'])) {
    header('Location: ' . url('/pages/colaboradores-dashboard.php?error=' . urlencode('Dados inválidos')));
    exit;
}

try {
    // Get the role_id
    $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
    $stmt->execute([$role_name]);
    $role = $stmt->fetch();
    
    if (!$role) {
        throw new Exception('Categoria inválida.');
    }

    // Update user role
    $stmt = $pdo->prepare('UPDATE users SET role_id = ? WHERE id = ?');
    $stmt->execute([(int)$role['id'], $user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Usuário não encontrado.');
    }

    header('Location: ' . url('/pages/colaboradores-dashboard.php?success=' . urlencode('Cargo atribuído com sucesso.')));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/pages/colaboradores-dashboard.php?error=' . urlencode('Erro ao atribuir cargo: ' . $e->getMessage())));
    exit;
}
?>

