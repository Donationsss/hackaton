<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/constants.php';

function find_user_by_id(int $id): ?array {
    global $pdo;
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function current_user(): ?array {
    return isset($_SESSION['user_id']) ? find_user_by_id((int)$_SESSION['user_id']) : null;
}

function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . url('/index.php'));
        exit;
    }
}

function require_role(string $role_name): void {
    $user = current_user();
    if (!$user || $user['role_name'] !== $role_name) {
        http_response_code(403);
        echo 'Acesso negado.';
        exit;
    }
}

function register_user(string $name, string $email, string $password, string $role_name): int {
    global $pdo;

    $stmt = $pdo->prepare('SELECT id FROM roles WHERE name = ?');
    $stmt->execute([$role_name]);
    $role = $stmt->fetch();
    if (!$role) {
        throw new Exception('Categoria inválida.');
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('E-mail já cadastrado.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role_id, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$name, $email, $hash, (int)$role['id']]);
    return (int)$pdo->lastInsertId();
}

function login_user(string $email, string $password) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        return $user;
    }
    return false;
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
