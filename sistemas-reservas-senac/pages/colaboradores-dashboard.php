<?php
require_once __DIR__ . '/../inc/auth.php';
require_login();
require_role('administrador');

$user = current_user();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Buscar todos os usuários com colaborador ou administrador
$stmt = $pdo->prepare("SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name IN ('colaborador', 'administrador') ORDER BY u.name");
$stmt->execute();
$collaborators = $stmt->fetchAll();

// Buscar todos os usuários para o modal (exceto colaboradores e administradores já atribuídos)
$stmt2 = $pdo->prepare("SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name NOT IN ('colaborador', 'administrador') ORDER BY u.name");
$stmt2->execute();
$availableUsers = $stmt2->fetchAll();

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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colaboradores - Sistema SENAC</title>
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
                <a href="./espacos.php" class="nav-link">🏢 Espaços</a>
                <a href="./relatorios.php" class="nav-link">📈 Relatórios</a>
                <a href="./colaboradores-dashboard.php" class="nav-link active">👥 Colaboradores</a>
                <a href="./configuracoes.php" class="nav-link">⚙️ Configurações</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <div>
                    <h2 class="page-title">Gerenciar Colaboradores</h2>
                    <p class="page-subtitle">Gerencie usuários e permissões do sistema</p>
                </div>
                <div class="page-actions">
                    <button class="btn btn-primary" data-action="add-collaborator">
                        <span class="btn-icon">➕</span>
                        Adicionar Colaborador
                    </button>
                </div>
            </div>

            <div class="collaborators-grid">
                <?php foreach ($collaborators as $collab): ?>
                    <div class="collaborator-card">
                        <div class="collaborator-avatar-large">
                            <?php echo htmlspecialchars(user_initials($collab['name'])); ?>
                        </div>
                        <h3 class="collaborator-name"><?php echo htmlspecialchars($collab['name']); ?></h3>
                        <p class="collaborator-email"><?php echo htmlspecialchars($collab['email']); ?></p>
                        <?php if ($collab['role_name'] == 'administrador'): ?>
                            <span class="role-badge role-admin">Gestor/Admin</span>
                        <?php else: ?>
                            <span class="role-badge role-collaborator">Colaborador</span>
                        <?php endif; ?>
                        <div class="collaborator-info">
                            <div class="collaborator-info-item">
                                <span class="info-label">Status:</span>
                                <span class="status-badge status-active">Ativo</span>
                            </div>
                        </div>
                        <div class="collaborator-actions" style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
                            <button class="btn btn-sm btn-primary" onclick="viewProfile(<?php echo (int)$collab['id']; ?>)">👤 Perfil</button>
                            <?php if ($collab['role_name'] == 'colaborador'): ?>
                                <button class="btn btn-sm btn-secondary" onclick="editRole(<?php echo (int)$collab['id']; ?>, '<?php echo htmlspecialchars($collab['name']); ?>', '<?php echo htmlspecialchars($collab['role_name']); ?>')">✏️ Editar</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="permissions-section">
                <h3 class="section-title">📋 Níveis de Acesso</h3>
                <div class="permissions-grid">
                    <div class="permission-card">
                        <div class="permission-icon">👑</div>
                        <h4>Gestor/Admin</h4>
                        <ul class="permission-list">
                            <li>✅ Acesso total ao sistema</li>
                            <li>✅ Gerenciar colaboradores</li>
                            <li>✅ Aprovar/rejeitar reservas</li>
                            <li>✅ Gerenciar espaços</li>
                            <li>✅ Acessar relatórios</li>
                            <li>✅ Configurar sistema</li>
                        </ul>
                    </div>

                    <div class="permission-card">
                        <div class="permission-icon">👤</div>
                        <h4>Colaborador</h4>
                        <ul class="permission-list">
                            <li>✅ Criar reservas</li>
                            <li>✅ Ver próprias reservas</li>
                            <li>✅ Editar reservas pendentes</li>
                            <li>✅ Ver espaços disponíveis</li>
                            <li>❌ Aprovar reservas</li>
                            <li>❌ Gerenciar usuários</li>
                        </ul>
                    </div>

                    <div class="permission-card">
                        <div class="permission-icon">👁️</div>
                        <h4>Visualizador</h4>
                        <ul class="permission-list">
                            <li>✅ Ver reservas</li>
                            <li>✅ Ver espaços</li>
                            <li>❌ Criar reservas</li>
                            <li>❌ Editar informações</li>
                            <li>❌ Aprovar reservas</li>
                            <li>❌ Acessar configurações</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
      </main>

      <script src="../js/toast.js"></script>
      <script>
        <?php if (!empty($success)): ?>
        setTimeout(() => { if (window.Toast) window.Toast.success('<?php echo htmlspecialchars($success); ?>'); }, 100);
        <?php endif; ?>
        <?php if (!empty($error)): ?>
        setTimeout(() => { if (window.Toast) window.Toast.error('<?php echo htmlspecialchars($error); ?>'); }, 100);
        <?php endif; ?>
      </script>

      <footer class="footer">
        <div class="container">
            <p>&copy; 2025 SENAC - Sistema de Reservas. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Modal Adicionar Colaborador -->
    <div id="addCollaboratorModal" style="position:fixed; inset:0; background:rgba(0,0,0,.4); display:none; align-items:center; justify-content:center; z-index:2000;">
        <div style="background:#fff; width:100%; max-width:600px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
            <div style="padding:16px 20px; border-bottom:1px solid var(--gray-100); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin:0;">Adicionar Colaborador</h3>
                <button id="closeAddModal" class="btn btn-sm btn-secondary">Fechar</button>
            </div>
            <form method="post" action="../actions/assign_role.php">
                <div style="padding:16px 20px; max-height:400px; overflow-y:auto;">
                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-weight:600; margin-bottom:8px;">Selecione o Usuário</label>
                        <select name="user_id" required style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb;">
                            <option value="">Selecione um usuário...</option>
                            <?php foreach ($availableUsers as $u): ?>
                                <option value="<?php echo (int)$u['id']; ?>">
                                    <?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['email']); ?>) - <?php echo htmlspecialchars($u['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="margin-bottom:4px;">
                        <label style="display:block; font-weight:600; margin-bottom:8px;">Atribuir Cargo</label>
                        <select name="role_name" required style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb;">
                            <option value="colaborador">Colaborador</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>
                </div>
                <div style="padding:12px 20px; border-top:1px solid var(--gray-100); display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" id="cancelAdd" class="btn btn-secondary">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atribuir Cargo</button>
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

            // Modal Adicionar Colaborador
            const modal = document.getElementById('addCollaboratorModal');
            const addBtn = document.querySelector('[data-action="add-collaborator"]');
            const closeBtn = document.getElementById('closeAddModal');
            const cancelBtn = document.getElementById('cancelAdd');

            function openModal() {
                modal.style.display = 'flex';
            }

            function closeModal() {
                modal.style.display = 'none';
            }

            if (addBtn) addBtn.addEventListener('click', openModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });
        });

        function viewProfile(userId) {
            const collaborators = <?php echo json_encode($collaborators); ?>;
            const collab = collaborators.find(c => c.id == userId);
            if (!collab) return;

            const content = `
                <div style="text-align:center; margin-bottom:24px;">
                    <div style="width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg, #004A8D, #3A7BB9); display:inline-flex; align-items:center; justify-content:center; font-size:32px; font-weight:bold; color:white; margin-bottom:16px;">
                        ${collab.name.charAt(0).toUpperCase()}
                    </div>
                    <h3 style="margin-bottom:8px;">${collab.name}</h3>
                    <span style="padding:6px 12px; background:#f0f9ff; color:#004A8D; border-radius:6px; font-size:12px; font-weight:600;">
                        ${collab.role_name === 'administrador' ? 'Gestor/Admin' : 'Colaborador'}
                    </span>
                </div>
                <div style="border-top:1px solid var(--gray-100); padding-top:16px;">
                    <div style="margin-bottom:12px;">
                        <strong style="color:#6b7280; font-size:12px;">E-mail:</strong>
                        <div style="font-size:16px; margin-top:4px;">${collab.email}</div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <strong style="color:#6b7280; font-size:12px;">Status:</strong>
                        <div style="margin-top:4px;">
                            <span style="padding:4px 12px; background:#dcfce7; color:#166534; border-radius:4px; font-size:12px; font-weight:600;">Ativo</span>
                        </div>
                    </div>
                </div>
            `;

            const modal = `
                <div style="position:fixed; inset:0; background:rgba(0,0,0,.4); display:flex; align-items:center; justify-content:center; z-index:2000;" onclick="if(event.target===this) this.remove()">
                    <div style="background:#fff; width:100%; max-width:500px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2); padding:24px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                            <h3 style="margin:0;">Perfil do Usuário</h3>
                            <button onclick="this.closest('[style*=position]').remove()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
                        </div>
                        ${content}
                        <div style="border-top:1px solid var(--gray-100); margin-top:16px; padding-top:16px;">
                            <button onclick="this.closest('[style*=position]').remove()" style="width:100%; padding:10px; background:#004A8D; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Fechar</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modal);
        }

        function editRole(userId, userName, currentRole) {
            const modalHtml = `
                <div style="position:fixed; inset:0; background:rgba(0,0,0,.4); display:flex; align-items:center; justify-content:center; z-index:2000;" id="editRoleModal" onclick="if(event.target===this) closeEditModal()">
                    <div style="background:#fff; width:100%; max-width:500px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2); padding:24px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                            <h3 style="margin:0;">Editar Cargo de ${userName}</h3>
                            <button onclick="closeEditModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#6b7280;">&times;</button>
                        </div>
                        <form id="editRoleForm" method="post" action="../actions/remove_role.php">
                            <input type="hidden" name="user_id" value="${userId}">
                            <div style="margin-bottom:16px;">
                                <label style="display:block; font-weight:600; margin-bottom:8px;">Alterar Cargo Para:</label>
                                <select name="role_name" id="roleSelect" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; background:#f9fafb;">
                                    <option value="">Remover cargo (tornar visualizador)</option>
                                    <option value="colaborador" ${currentRole === 'colaborador' ? 'selected' : ''}>Colaborador</option>
                                    <option value="administrador" ${currentRole === 'administrador' ? 'selected' : ''}>Administrador</option>
                                </select>
                            </div>
                            <div style="border-top:1px solid var(--gray-100); margin-top:20px; padding-top:16px; display:flex; gap:8px; justify-content:flex-end;">
                                <button type="button" onclick="closeEditModal()" style="padding:10px 20px; background:#f3f4f6; color:#374151; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Cancelar</button>
                                <button type="submit" style="padding:10px 20px; background:#004A8D; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closeEditModal() {
            const modal = document.getElementById('editRoleModal');
            if (modal) modal.remove();
        }
    </script>
</body>
</html>
