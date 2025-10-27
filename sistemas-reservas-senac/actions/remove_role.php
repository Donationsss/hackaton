<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

$user_id = (int)($_POST['user_id'] ?? $_GET['user_id'] ?? 0);
$role_name = $_POST['role_name'] ?? '';

if ($user_id <= 0) {
    header('Location: ' . url('/pages/colaboradores-dashboard.php?error=' . urlencode('ID inválido')));
    exit;
}

try {
    if (empty($role_name)) {
        // Remove role - set to viewer
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = "visualizador" LIMIT 1');
        $stmt->execute();
        $role = $stmt->fetch();
        
        if (!$role) {
            throw new Exception('Categoria visualizador não encontrada.');
        }

        $message = 'Cargo removido com sucesso. Usuário agora é visualizador.';
    } else {
        // Change role
        $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
        $stmt->execute([$role_name]);
        $role = $stmt->fetch();
        
        if (!$role) {
            throw new Exception('Categoria não encontrada.');
        }

        $roleNameMap = [
            'colaborador' => 'Colaborador',
            'administrador' => 'Gestor/Admin',
            'visualizador' => 'Visualizador'
        ];
        $message = 'Cargo alterado para ' . ($roleNameMap[$role_name] ?? $role_name) . ' com sucesso.';
    }

    // Update user role
    $stmt = $pdo->prepare('UPDATE users SET role_id = ? WHERE id = ?');
    $stmt->execute([(int)$role['id'], $user_id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Usuário não encontrado.');
    }

    header('Location: ' . url('/pages/colaboradores-dashboard.php?success=' . urlencode($message)));
    exit;
} catch (Throwable $e) {
    header('Location: ' . url('/pages/colaboradores-dashboard.php?error=' . urlencode('Erro ao alterar cargo: ' . $e->getMessage())));
    exit;
}
?>

