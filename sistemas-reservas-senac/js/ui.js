/**
 * Componentes de Interface
 */
const UI = {
    showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div style="flex: 1;">${Utils.sanitizeHTML(message)}</div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">&times;</button>
        `;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, CONFIG.TOAST_DURATION);
    },

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    },

    loadPage(pageName) {
        const content = document.getElementById('pageContent');
        
        // Mapeamento de páginas
        const pages = {
            dashboard: this.renderDashboardPage,
            reservas: this.renderReservasPage,
            espacos: this.renderEspacosPage,
            relatorios: this.renderRelatoriosPage,
            colaboradores: this.renderColaboradoresPage,
            configuracoes: this.renderConfiguracoesPage
        };

        const renderFunc = pages[pageName];
        if (renderFunc) {
            content.innerHTML = renderFunc.call(this);
            this.initPageEvents(pageName);
        }
    },

    renderDashboardPage() {
        return `
            <div class="section-header">
                <h2>Dashboard</h2>
                <div class="section-actions">
                    <button class="btn btn-secondary" onclick="App.refreshData()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                        Atualizar
                    </button>
                    <button class="btn btn-primary" onclick="UI.openModal('reservationModal')">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Nova Reserva
                    </button>
                </div>
            </div>
            <div class="stats-grid" id="statsGrid"></div>
            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">
                        <h3>Reservas Recentes</h3>
                    </div>
                    <div class="card-body" id="recentReservations"></div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3>Calendário</h3>
                        <div class="calendar-controls">
                            <button class="btn-icon" onclick="App.prevMonth()">◀</button>
                            <span id="currentMonth">Outubro 2025</span>
                            <button class="btn-icon" onclick="App.nextMonth()">▶</button>
                        </div>
                    </div>
                    <div class="card-body" id="calendarContainer"></div>
                </div>
            </div>
        `;
    },

    renderReservasPage() {
        return `
            <div class="section-header">
                <h2>Gerenciar Reservas</h2>
                <div class="section-actions">
                    <div class="search-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="text" placeholder="Buscar..." id="searchReservations">
                    </div>
                    <select class="select-filter" id="filterStatus">
                        <option value="all">Todas</option>
                        <option value="pending">Pendentes</option>
                        <option value="approved">Aprovadas</option>
                        <option value="rejected">Rejeitadas</option>
                    </select>
                    <button class="btn btn-primary" onclick="UI.openModal('reservationModal')">Nova Reserva</button>
                </div>
            </div>
            <div class="card">
                <div class="card-body no-padding">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Solicitante</th><th>Espaço</th><th>Data</th>
                                    <th>Horário</th><th>Tipo</th><th>Status</th><th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="reservationsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    },

    renderEspacosPage() {
        return `
            <div class="section-header">
                <h2>Espaços Disponíveis</h2>
                <div class="section-actions">
                    <button class="btn btn-primary" onclick="UI.openModal('spaceModal')">Adicionar Espaço</button>
                </div>
            </div>
            <div class="spaces-grid" id="spacesGrid"></div>
        `;
    },

    renderRelatoriosPage() {
        return `
            <div class="section-header">
                <h2>Relatórios e Estatísticas</h2>
            </div>
            <div class="stats-grid">
                <div class="card">
                    <div class="card-body">
                        <h4>Total de Reservas</h4>
                        <h2 id="totalReservations">0</h2>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h4>Taxa de Aprovação</h4>
                        <h2 id="approvalRate">0%</h2>
                    </div>
                </div>
            </div>
        `;
    },

    renderColaboradoresPage() {
        return `
            <div class="section-header">
                <h2>Gerenciar Colaboradores</h2>
                <div class="section-actions">
                    <button class="btn btn-primary" onclick="UI.openModal('collaboratorModal')">Adicionar Colaborador</button>
                </div>
            </div>
            <div class="card">
                <div class="card-body no-padding">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nome</th><th>Email</th><th>Perfil</th><th>Departamento</th><th>Status</th><th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="collaboratorsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    },

    renderConfiguracoesPage() {
        return `
            <div class="section-header">
                <h2>Configurações do Sistema</h2>
            </div>
            <div class="card">
                <div class="card-body">
                    <h4>Configurações em desenvolvimento</h4>
                    <p>Esta seção estará disponível em breve.</p>
                </div>
            </div>
        `;
    },

    initPageEvents(pageName) {
        if (pageName === 'dashboard') {
            App.renderDashboard();
        } else if (pageName === 'reservas') {
            App.renderReservations();
        } else if (pageName === 'espacos') {
            App.renderSpaces();
        } else if (pageName === 'colaboradores') {
            App.renderCollaborators();
        }
    }
};
