<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - Sistema SENAC</title>
    <link rel="stylesheet" href="./css/comum.css" />
    <link rel="stylesheet" href="./css/dashboard.css" />
  </head>
  <body>
    <header class="header">
      <div class="container">
        <div class="header-top">
          <div class="logo">
            <div class="logo-icon"><img src="./imagem.jpg" alt="Senac" style="width: 120px; height: 75px;">
            </div>
            <div class="logo-text">
              <h1>SENAC</h1>
              <p>Sistema de Reservas</p>
            </div>
          </div>
          <div class="user-info">
            <div class="user-menu" style="position: relative;">
              <button class="user-menu-toggle" style="display:flex; align-items:center; gap:10px; background:transparent; border:0; cursor:pointer;">
                <div class="avatar">AD</div>
                <div class="user-details" style="text-align:left;">
                  <div class="username">Administrador</div>
                  <div class="role">Gestor</div>
                </div>
                <span aria-hidden="true">▾</span>
              </button>
<div class="user-menu-dropdown" style="position:absolute; right:0; top:calc(100% + 8px); background:#f6f8ff; border:1px solid var(--gray-100); box-shadow: 0 4px 12px rgba(0,0,0,.08); border-radius:8px; padding:6px; min-width:160px; display:none; z-index:1000;">
                <a href="./logout.php" class="user-menu-item" style="display:block; padding:8px 10px; border-radius:6px; color:inherit; text-decoration:none;">Sair</a>
              </div>
            </div>
          </div>
        </div>

        <nav class="nav">
          <a href="./dashboard.php" class="nav-link active">📊 Dashboard</a>
          <a href="./pages/reservas.php" class="nav-link">📅 Reservas</a>
          <a href="./pages/espacos.php" class="nav-link">🏢 Espaços</a>
          <a href="./pages/relatorios.php" class="nav-link">📈 Relatórios</a>
          <a href="./pages/colaboradores.php" class="nav-link">👥 Colaboradores</a>
          <a href="./pages/configuracoes.php" class="nav-link">⚙️ Configurações</a>
        </nav>
      </div>
    </header>

    <main class="main-content">
      <div class="container">
        <div class="page-header">
          <div>
            <h2 class="page-title">Dashboard</h2>
            <p class="page-subtitle">Bem-vindo ao Sistema de Reservas SENAC</p>
          </div>
          <div class="page-actions">
            <button class="btn btn-secondary" data-action="refresh">
              <span class="btn-icon">🔄</span>
              Atualizar
            </button>
            <button class="btn btn-primary" data-action="new-reservation">
              <span class="btn-icon">➕</span>
              Nova Reserva
            </button>
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-card stat-primary">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
              <h3 class="stat-number" id="todayReservations">0</h3>
              <p class="stat-label">Reservas Hoje</p>
              <span class="stat-badge stat-badge-success">Em tempo real</span>
            </div>
          </div>

          <div class="stat-card stat-warning">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
              <h3 class="stat-number" id="pendingReservations">0</h3>
              <p class="stat-label">Aguardando Aprovação</p>
              <span class="stat-badge stat-badge-warning">Requer atenção</span>
            </div>
          </div>

          <div class="stat-card stat-info">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
              <h3 class="stat-number" id="totalReservations">0</h3>
              <p class="stat-label">Total de Reservas</p>
              <span class="stat-badge stat-badge-info">Todas as reservas</span>
            </div>
          </div>

          <div class="stat-card stat-success">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
              <h3 class="stat-number">82%</h3>
              <p class="stat-label">Taxa de Aprovação</p>
              <span class="stat-badge stat-badge-success">Excelente</span>
            </div>
          </div>
        </div>

        <div class="dashboard-grid">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">📋 Reservas Recentes</h3>
              <a href="./pages/reservas.php" class="card-link">Ver todas →</a>
            </div>
            <div class="card-body" id="recentReservations">
              <!-- Carregado dinamicamente pelo JavaScript -->
              <p style="text-align: center; padding: 32px; color: #999">
                Carregando...
              </p>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">📆 Calendário</h3>
              <div class="calendar-controls">
                <button class="btn-icon" title="Mês anterior">◀</button>
                <span class="calendar-month">Carregando...</span>
                <button class="btn-icon" title="Próximo mês">▶</button>
              </div>
            </div>
            <div class="card-body">
              <div class="calendar">
                <div class="calendar-header">
                  <div class="calendar-day-name">Dom</div>
                  <div class="calendar-day-name">Seg</div>
                  <div class="calendar-day-name">Ter</div>
                  <div class="calendar-day-name">Qua</div>
                  <div class="calendar-day-name">Qui</div>
                  <div class="calendar-day-name">Sex</div>
                  <div class="calendar-day-name">Sáb</div>
                </div>
                <div class="calendar-body">
                  <!-- Dias serão gerados dinamicamente pelo JavaScript -->
                </div>
              </div>
              <div
                style="
                  margin-top: 16px;
                  padding-top: 16px;
                  border-top: 1px solid var(--gray-100);
                  font-size: 12px;
                  color: var(--gray-600);
                "
              >
                <div style="display: flex; gap: 16px; flex-wrap: wrap">
                  <div style="display: flex; align-items: center; gap: 6px">
                    <div
                      style="
                        width: 12px;
                        height: 12px;
                        background: var(--primary);
                        border-radius: 50%;
                      "
                    ></div>
                    <span>Dia atual</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 6px">
                    <div
                      style="
                        width: 12px;
                        height: 12px;
                        border: 2px solid var(--secondary);
                        border-radius: 50%;
                      "
                    ></div>
                    <span>Com reservas</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">🏢 Espaços Disponíveis</h3>
              <a href="./pages/espacos.php" class="card-link">Ver todos →</a>
            </div>
            <div class="card-body">
              <div class="space-item">
                <div class="space-header">
                  <strong class="space-name">Auditório Principal</strong>
                  <button
                    class="btn btn-sm btn-primary"
                    data-action="reserve-space"
                    data-id="1"
                  >
                    Reservar
                  </button>
                </div>
                <div class="space-details">
                  Auditório • Capacidade: 200 pessoas
                </div>
              </div>
              <div class="space-item">
                <div class="space-header">
                  <strong class="space-name"
                    >Laboratório de Informática 1</strong
                  >
                  <button
                    class="btn btn-sm btn-primary"
                    data-action="reserve-space"
                    data-id="2"
                  >
                    Reservar
                  </button>
                </div>
                <div class="space-details">
                  Laboratório • Capacidade: 40 pessoas
                </div>
              </div>
              <div class="space-item">
                <div class="space-header">
                  <strong class="space-name">Sala de Reuniões Executiva</strong>
                  <button
                    class="btn btn-sm btn-primary"
                    data-action="reserve-space"
                    data-id="3"
                  >
                    Reservar
                  </button>
                </div>
                <div class="space-details">
                  Sala de Reunião • Capacidade: 20 pessoas
                </div>
              </div>
              <div class="space-item">
                <div class="space-header">
                  <strong class="space-name">Sala de Aula 101</strong>
                  <button
                    class="btn btn-sm btn-primary"
                    data-action="reserve-space"
                    data-id="4"
                  >
                    Reservar
                  </button>
                </div>
                <div class="space-details">
                  Sala de Aula • Capacidade: 50 pessoas
                </div>
              </div>
            </div>
          </div>

          <div class="card card-warning">
            <div class="card-header">
              <h3 class="card-title">⚠️ Ações Pendentes</h3>
            </div>
            <div class="card-body">
              <div class="pending-item">
                <div class="pending-header">
                  <strong class="pending-title">Reunião de Planejamento</strong>
                </div>
                <div class="pending-details">
                  Ana Costa • 25/10/2025 • 10:00-11:30
                </div>
                <div class="pending-actions">
                  <button
                    class="btn btn-xs btn-success"
                    data-action="approve-reservation"
                    data-id="3"
                  >
                    ✓ Aprovar
                  </button>
                  <button
                    class="btn btn-xs btn-danger"
                    data-action="reject-reservation"
                    data-id="3"
                  >
                    ✗ Rejeitar
                  </button>
                  <button
                    class="btn btn-xs btn-secondary"
                    data-action="view-reservation"
                    data-id="3"
                  >
                    👁️ Ver
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <footer class="footer">
      <div class="container">
        <p>
          &copy; 2025 SENAC - Sistema de Reservas. Todos os direitos reservados.
        </p>
      </div>
    </footer>

    <script src="./js/dashboard.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.user-menu').forEach(function(menu){
          const toggle = menu.querySelector('.user-menu-toggle');
          const dropdown = menu.querySelector('.user-menu-dropdown');
          if (!toggle || !dropdown) return;
          toggle.addEventListener('click', function(e){
            e.stopPropagation();
            // Ajusta largura do dropdown para o mesmo tamanho do botão/perfil
            const w = toggle.getBoundingClientRect().width;
            dropdown.style.width = w + 'px';
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
          });
          document.addEventListener('click', function(){ dropdown.style.display = 'none'; });
        });
      });
    </script>
  </body>
</html>
