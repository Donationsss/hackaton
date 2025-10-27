<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

try {
    global $pdo;
    
    // Limpar dados de teste (reservas de exemplo)
    $stmt = $pdo->prepare("DELETE FROM reservas WHERE status IN ('proposta', 'reservado', 'cancelado')");
    $stmt->execute();
    
    $deletedCount = $stmt->rowCount();
    
    if ($deletedCount > 0) {
        header('Location: ' . url('/pages/configuracoes.php?success=' . urlencode('Dados de teste limpos com sucesso! ' . $deletedCount . ' reservas removidas.')));
    } else {
        header('Location: ' . url('/pages/configuracoes.php?success=' . urlencode('Nenhum dado de teste encontrado para limpar.')));
    }
    
} catch (Throwable $e) {
    error_log('Erro ao limpar dados de teste: ' . $e->getMessage());
    header('Location: ' . url('/pages/configuracoes.php?error=' . urlencode('Erro ao limpar dados de teste: ' . $e->getMessage())));
}

exit;
