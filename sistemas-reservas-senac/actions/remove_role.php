<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

$user_id = (int)($_GET['user_id'] ?? 0);

if ($user_id <= 0) {
    header('Location: ' . url('/pages/colaboradores-dashboard.php?error=' . urlencode('ID inválido')));
    exit;
}

try {
    // Get the viewer role_id (role with least permissions)
    $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = "visualizador" LIMIT 1');
    $stmt->execute();
    $role = $stmt->fetch();
    
    if (!$role) {
        throw new Exception('Categoria visualizador não encontrada.');
    }

    // Update user role to viewer
    $stmt = $pdo->prepare('UPDATE users SET role_id = ? WHERE id = ?');
    $stmt->execute([(int)$role['id'], $user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Usuário não encontrado.');
    }

    header('Location: ' . url('/pages/colaboradores-dashboard.php?success=' . urlencode('Cargo removido com sucesso. Usuário agora é visualizador.')));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/pages/colaboradores-dashboard.php?error=' . urlencode('Erro ao remover cargo: ' . $e->getMessage())));
    exit;
}
?>

