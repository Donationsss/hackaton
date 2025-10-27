<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

$user = current_user();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Buscar todos os espaços do banco de dados
$stmt = $pdo->prepare("SELECT * FROM spaces ORDER BY name");
$stmt->execute();
$spaces = $stmt->fetchAll();

// Buscar espaço específico se necessário para edição
$editingSpace = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM spaces WHERE id = ?');
    $stmt->execute([$editId]);
    $editingSpace = $stmt->fetch();
}

// Contar espaços
$stmt2 = $pdo->prepare("SELECT COUNT(*) as count FROM spaces");
$stmt2->execute();
$totalSpaces = $stmt2->fetch()['count'] ?? 0;

function user_initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $ini = '';
    foreach ($parts as $p) {
        if ($p !== '') {
            $ini .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
        }
        if (mb_strlen($ini, 'UTF-8') >= 2) break;
    }
    return $ini ?: 'US';
}

// Função para obter ícone baseado no nome do espaço
function get_space_icon(string $spaceName): string {
    $spaceNameLower = mb_strtolower($spaceName, 'UTF-8');
    
    if (strpos($spaceNameLower, 'auditório') !== false || strpos($spaceNameLower, 'auditorio') !== false) {
        return '🎭';
    } elseif (strpos($spaceNameLower, 'laboratório') !== false || strpos($spaceNameLower, 'laboratorio') !== false) {
        return '💻';
    } elseif (strpos($spaceNameLower, 'reunião') !== false || strpos($spaceNameLower, 'reuniao') !== false || strpos($spaceNameLower, 'executiva') !== false) {
        return '👔';
    } elseif (strpos($spaceNameLower, 'aula') !== false) {
        return '📚';
    }
    
    return '🏢';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espaços - Sistema SENAC</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link rel="stylesheet" href="../css/comum.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <header class="header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <div class="logo-icon"><img src="../logo.png" alt="Senac" style="width: 120px; height: 75px;"></div>
                    <div class="logo-text">
                        <h1>SENAC</h1>
                        <p>Sistema de Reservas</p>
                    </div>
                </div>
                <div class="user-info">
                    <div class="user-menu" style="position: relative;">
                        <button class="user-menu-toggle" style="display:flex; align-items:center; gap:10px; background:transparent; border:0; cursor:pointer;">
                            <div class="avatar"><?php echo htmlspecialchars(user_initials($user['name'] ?? 'Usuário')); ?></div>
                            <div class="user-details" style="text-align:left;">
                                <div class="username"><?php echo htmlspecialchars($user['name'] ?? 'Usuário'); ?></div>
                                <div class="role">Gestor</div>
                            </div>
                            <span aria-hidden="true">▾</span>
                        </button>
                        <div class="user-menu-dropdown" style="position:absolute; right:0; top:calc(100% + 8px); background:#f6f8ff; border:1px solid var(--gray-100); box-shadow: 0 4px 12px rgba(0,0,0,.08); border-radius:8px; padding:6px; min-width:160px; display:none; z-index:1000; color:var(--gray-800);">
                            <a href="../logout.php" class="user-menu-item" style="display:block; padding:8px 10px; border-radius:6px; color:inherit; text-decoration:none;">Sair</a>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="nav">
                <a href="../dashboard.php" class="nav-link">📊 Dashboard</a>
                <a href="./reservas.php" class="nav-link">📅 Reservas</a>
                <a href="./espacos.php" class="nav-link active">🏢 Espaços</a>
                <a href="./relatorios.php" class="nav-link">📈 Relatórios</a>
                <a href="./colaboradores-dashboard.php" class="nav-link">👥 Colaboradores</a>
                <a href="./configuracoes.php" class="nav-link">⚙️ Configurações</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <div>
                    <h2 class="page-title">Espaços Disponíveis</h2>
                    <p class="page-subtitle">Gerencie todos os espaços disponíveis para reserva</p>
                    <?php if (!empty($success)): ?><div class="stat-badge stat-badge-success" style="margin-top:8px; display:inline-block;"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
                    <?php if (!empty($error)): ?><div class="stat-badge stat-badge-warning" style="margin-top:8px; display:inline-block;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                </div>
                <div class="page-actions">
                    <button class="btn btn-primary" data-action="new-space">
                        <span class="btn-icon">➕</span>
                        Adicionar Espaço
                    </button>
                </div>
            </div>

            <div class="quick-stats">
                <div class="quick-stat-item">
                    <span class="quick-stat-number"><?php echo $totalSpaces; ?></span>
                    <span class="quick-stat-label">Total de Espaços</span>
                </div>
                <div class="quick-stat-item">
                    <span class="quick-stat-number"><?php echo $totalSpaces; ?></span>
                    <span class="quick-stat-label">Disponíveis</span>
                </div>
                <div class="quick-stat-item">
                    <span class="quick-stat-number">0</span>
                    <span class="quick-stat-label">Em Manutenção</span>
                </div>
            </div>

            <div class="spaces-grid">
                <?php foreach ($spaces as $space): ?>
                    <div class="space-card">
                        <div class="space-card-header">
                            <span class="space-badge space-badge-available">Disponível</span>
                        </div>
                        <div class="space-card-icon"><?php echo get_space_icon($space['name']); ?></div>
                        <h3 class="space-card-title"><?php echo htmlspecialchars($space['name']); ?></h3>
                        <p class="space-card-description">
                            Espaço moderno e equipado com capacidade para <?php echo (int)$space['capacity']; ?> pessoas, ideal para eventos corporativos e educacionais.
                        </p>
                        <div class="space-card-details">
                            <div class="space-detail-item">
                                <span class="space-detail-icon">👥</span>
                                <span class="space-detail-text"><?php echo (int)$space['capacity']; ?> pessoas</span>
                            </div>
                            <div class="space-detail-item">
                                <span class="space-detail-icon">📍</span>
                                <span class="space-detail-text"><?php echo ucfirst(str_replace('_', ' ', $space['type'])); ?></span>
                            </div>
                        </div>
                        <div class="space-card-resources">
                            <span class="resource-tag">🎤 Som</span>
                            <span class="resource-tag">📽️ Projetor</span>
                            <span class="resource-tag">❄️ Ar-Condicionado</span>
                            <span class="resource-tag">📶 Wi-Fi</span>
                        </div>
                        <div class="space-card-actions">
                            <button class="btn btn-primary btn-block" data-action="reserve-space" data-id="<?php echo (int)$space['id']; ?>">Reservar Espaço</button>
                            <div class="space-card-icons">
                                <button class="btn-icon btn-icon-sm" title="Editar" data-action="edit-space" data-id="<?php echo (int)$space['id']; ?>">✏️</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 SENAC - Sistema de Reservas. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Modal Adicionar/Editar Espaço -->
    <div id="spaceModal" style="position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:2000;">
        <div style="background:#fff; width:100%; max-width:520px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
            <div style="padding:16px 20px; border-bottom:1px solid var(--gray-100); display:flex; justify-content:space-between; align-items:center;">
                <h3 id="modalTitle" style="margin:0;">Adicionar Espaço</h3>
                <button id="closeModal" class="btn btn-sm btn-secondary">Fechar</button>
            </div>
            <form id="spaceForm" method="post" action="">
                <input type="hidden" name="id" id="spaceId">
                <div style="padding:16px 20px;">
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px;">Nome do Espaço</label>
                        <input type="text" name="name" id="spaceName" required style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb;" />
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px;">Tipo</label>
                        <select name="type" id="spaceType" required style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb;">
                            <option value="">Selecione...</option>
                            <option value="auditorio">Auditório</option>
                            <option value="laboratorio">Laboratório</option>
                            <option value="sala_reuniao">Sala de Reunião</option>
                            <option value="sala">Sala de Aula</option>
                        </select>
                    </div>
                    <div style="margin-bottom:4px;">
                        <label style="display:block; font-weight:600; margin-bottom:6px;">Capacidade</label>
                        <input type="number" name="capacity" id="spaceCapacity" required min="1" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb;" />
                    </div>
                </div>
                <div style="padding:12px 20px; border-top:1px solid var(--gray-100); display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" id="cancelBtn" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dropdown perfil
            document.querySelectorAll('.user-menu').forEach(function(menu) {
                const toggle = menu.querySelector('.user-menu-toggle');
                const dropdown = menu.querySelector('.user-menu-dropdown');
                if (!toggle || !dropdown) return;
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const w = toggle.getBoundingClientRect().width;
                    dropdown.style.width = w + 'px';
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                });
                document.addEventListener('click', function() {
                    dropdown.style.display = 'none';
                });
            });

            // Modal Adicionar/Editar Espaço
            const modal = document.getElementById('spaceModal');
            const form = document.getElementById('spaceForm');
            const addBtn = document.querySelector('[data-action="new-space"]');
            const closeBtn = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');

            const spaces = <?php echo json_encode($spaces); ?>;

            function openModal(mode = 'add', spaceId = null) {
                document.getElementById('modalTitle').textContent = mode === 'edit' ? 'Editar Espaço' : 'Adicionar Espaço';
                form.action = mode === 'edit' ? '../actions/edit_space.php' : '../actions/create_space.php';
                
                if (mode === 'edit' && spaceId) {
                    const space = spaces.find(s => s.id == spaceId);
                    if (space) {
                        document.getElementById('spaceId').value = space.id;
                        document.getElementById('spaceName').value = space.name;
                        document.getElementById('spaceType').value = space.type;
                        document.getElementById('spaceCapacity').value = space.capacity;
                    }
                } else {
                    document.getElementById('spaceId').value = '';
                    document.getElementById('spaceName').value = '';
                    document.getElementById('spaceType').value = '';
                    document.getElementById('spaceCapacity').value = '';
                }
                
                modal.style.display = 'flex';
            }

            function closeModal() {
                modal.style.display = 'none';
            }

            if (addBtn) addBtn.addEventListener('click', () => openModal('add'));
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });

            // Botões de editar
            document.querySelectorAll('[data-action="edit-space"]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const spaceId = this.dataset.id;
                    openModal('edit', spaceId);
                });
            });
        });
    </script>
</body>

</html>
