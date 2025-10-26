<?php
require_once __DIR__ . '/inc/auth.php';
require_login();
logout_user();
header('Location: ' . url('/index.php?success=Você saiu da sessão.'));
exit;
