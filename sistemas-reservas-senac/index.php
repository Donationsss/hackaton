<?php
require_once __DIR__ . '/inc/auth.php';

// Redireciona para a landing nova
header('Location: ' . url('/landing.php'));
exit;
