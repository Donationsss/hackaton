/**
 * ================================================
 * SISTEMA DE RESERVAS SENAC - JavaScript Completo
 * ================================================
 */

'use strict';

// ============================================
// CONFIGURAÇÕES GLOBAIS
// ============================================
const CONFIG = {
    APP_NAME: 'Sistema de Reservas SENAC',
    VERSION: '1.0.0',
    STORAGE_KEY: 'senac_reservations_data',
    TOAST_DURATION: 4000
};

// ============================================
// GERENCIADOR DE DADOS (LocalStorage)
// ============================================
const DataStore = {
    data: null,

    init() {
        const saved = localStorage.getItem(CONFIG.STORAGE_KEY);
        this.data = saved ? JSON.parse(saved) : this.getDefaultData();
        return this.data;
    },

    save() {
        try {
            localStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(this.data));
            return true;
        } catch (error) {
            console.error('Erro ao salvar dados:', error);
            return false;
        }
    },

    getDefaultData() {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        return {
            reservations: [
                {
                    id: 1,
                    spaceId: 1,
                    spaceName: 'Auditório Principal',
                    requestor: 'Maria Silva',
                    email: 'maria.silva@senac.com.br',
                    phone: '(11) 98765-4321',
                    date: this.formatDate(today),
                    startTime: '09:00',
                    endTime: '12:00',
                    title: 'Palestra sobre Inovação Tecnológica',
                    description: 'Evento para apresentação de novas tecnologias',
                    participants: 150,
                    status: 'approved',
                    type: 'internal',
                    createdAt: new Date().toISOString()
                },
                {
                    id: 2,
                    spaceId: 2,
                    spaceName: 'Laboratório de Informática 1',
                    requestor: 'João Santos',
                    email: 'joao.santos@senac.com.br',
                    phone: '(11) 91234-5678',
                    date: this.formatDate(today),
                    startTime: '14:00',
                    endTime: '17:00',
                    title: 'Aula Prática de Programação',
                    description: 'Aula prática sobre desenvolvimento web',
                    participants: 35,
                    status: 'approved',
                    type: 'internal',
                    createdAt: new Date().toISOString()
                },
                {
                    id: 3,
                    spaceId: 3,
                    spaceName: 'Sala de Reuniões Executiva',
                    requestor: 'Ana Costa',
                    email: 'ana.costa@senac.com.br',
                    phone: '(11) 99876-5432',
                    date: this.formatDate(tomorrow),
                    startTime: '10:00',
                    endTime: '11:30',
                    title: 'Reunião de Planejamento',
                    description: 'Planejamento do próximo semestre',
                    participants: 15,
                    status: 'pending',
                    type: 'internal',
                    createdAt: new Date().toISOString()
                }
            ],
            spaces: [
                { id: 1, name: 'Auditório Principal', capacity: 200, type: 'auditorio', description: 'Auditório com sistema de som e projeção', status: 'available' },
                { id: 2, name: 'Laboratório de Informática 1', capacity: 40, type: 'laboratorio', description: 'Laboratório com 40 computadores', status: 'available' },
                { id: 3, name: 'Sala de Reuniões Executiva', capacity: 20, type: 'sala_reuniao', description: 'Sala com videoconferência', status: 'available' },
                { id: 4, name: 'Sala de Aula 101', capacity: 50, type: 'sala', description: 'Sala de aula tradicional', status: 'available' }
            ],
            collaborators: [
                { id: 1, name: 'Administrador', email: 'admin@senac.com.br', role: 'admin', department: 'TI', status: 'active' },
                { id: 2, name: 'Maria Silva', email: 'maria.silva@senac.com.br', role: 'collaborator', department: 'Coordenação', status: 'active' }
            ]
        };
    },

    formatDate(date) {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    },

    // CRUD Reservations
    getReservations() {
        return this.data.reservations || [];
    },

    getReservationById(id) {
        return this.data.reservations.find(r => r.id === id);
    },

    addReservation(reservation) {
        const newId = this.generateId(this.data.reservations);
        const newReservation = {
            ...reservation,
            id: newId,
            createdAt: new Date().toISOString(),
            status: 'pending'
        };
        this.data.reservations.unshift(newReservation);
        this.save();
        return newReservation;
    },

    updateReservation(id, updates) {
        const index = this.data.reservations.findIndex(r => r.id === id);
        if (index !== -1) {
            this.data.reservations[index] = { ...this.data.reservations[index], ...updates };
            this.save();
            return this.data.reservations[index];
        }
        return null;
    },

    deleteReservation(id) {
        const index = this.data.reservations.findIndex(r => r.id === id);
        if (index !== -1) {
            this.data.reservations.splice(index, 1);
            this.save();
            return true;
        }
        return false;
    },

    // CRUD Spaces
    getSpaces() {
        return this.data.spaces || [];
    },

    getSpaceById(id) {
        return this.data.spaces.find(s => s.id === id);
    },

    addSpace(space) {
        const newId = this.generateId(this.data.spaces);
        const newSpace = { ...space, id: newId, status: 'available' };
        this.data.spaces.push(newSpace);
        this.save();
        return newSpace;
    },

    // CRUD Collaborators
    getCollaborators() {
        return this.data.collaborators || [];
    },

    addCollaborator(collaborator) {
        const newId = this.generateId(this.data.collaborators);
        const newCollab = { ...collaborator, id: newId, status: 'active' };
        this.data.collaborators.push(newCollab);
        this.save();
        return newCollab;
    },

    deleteCollaborator(id) {
        const index = this.data.collaborators.findIndex(c => c.id === id);
        if (index !== -1) {
            this.data.collaborators.splice(index, 1);
            this.save();
            return true;
        }
        return false;
    },

    generateId(array) {
        if (!array || array.length === 0) return 1;
        return Math.max(...array.map(item => item.id)) + 1;
    },

    checkConflict(spaceId, date, startTime, endTime, excludeId = null) {
        return this.data.reservations.some(r => {
            if (r.id === excludeId || r.spaceId !== spaceId || r.date !== date || r.status === 'rejected') {
                return false;
            }
            const rStart = this.timeToMinutes(r.startTime);
            const rEnd = this.timeToMinutes(r.endTime);
            const newStart = this.timeToMinutes(startTime);
            const newEnd = this.timeToMinutes(endTime);
            return (newStart < rEnd && newEnd > rStart);
        });
    },

    timeToMinutes(time) {
        const [hours, minutes] = time.split(':').map(Number);
        return hours * 60 + minutes;
    }
};

// ============================================
// GERENCIADOR DE MODAIS
// ============================================
const Modal = {
    currentModal: null,

    open(modalId, content) {
        this.close(); // Fechar modal anterior se existir

        // Criar estrutura do modal
        const modalHTML = `
            <div class="modal active" id="${modalId}">
                <div class="modal-overlay" onclick="Modal.close()"></div>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <button class="modal-close" onclick="Modal.close()">&times;</button>
                        ${content}
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.currentModal = document.getElementById(modalId);
        document.body.style.overflow = 'hidden';

        // Animação de entrada
        setTimeout(() => {
            this.currentModal.classList.add('show');
        }, 10);
    },

    close() {
        if (this.currentModal) {
            this.currentModal.classList.remove('show');
            setTimeout(() => {
                this.currentModal.remove();
                document.body.style.overflow = '';
                this.currentModal = null;
            }, 300);
        }
    }
};

// ============================================
// SISTEMA DE NOTIFICAÇÕES (TOAST)
// ============================================
const Toast = {
    show(message, type = 'info') {
        const container = this.getContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">${this.getIcon(type)}</span>
                <span class="toast-message">${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        `;

        container.appendChild(toast);

        // Animação de entrada
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto-remover
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, CONFIG.TOAST_DURATION);
    },

    getContainer() {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    },

    getIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }
};

// ============================================
// APLICAÇÃO PRINCIPAL
// ============================================
const App = {
    currentPage: 'index.html',

    init() {
        console.log(`${CONFIG.APP_NAME} v${CONFIG.VERSION} iniciado!`);
        DataStore.init();
        this.setupEventListeners();
        this.loadCurrentPage();
    },

    setupEventListeners() {
        // Event delegation para toda a aplicação
        document.addEventListener('click', (e) => {
            // Botões de ação
            if (e.target.matches('[data-action]')) {
                const action = e.target.dataset.action;
                const id = e.target.dataset.id;
                this.handleAction(action, id, e.target);
            }

            // Links de navegação interna
            if (e.target.matches('a[href^="#"]')) {
                e.preventDefault();
                const target = e.target.getAttribute('href').substring(1);
                this.scrollToSection(target);
            }
        });

        // Tabs de configurações
        document.querySelectorAll('.settings-tab').forEach(tab => {
            tab.addEventListener('click', () => this.switchTab(tab));
        });

        // Toggle switches
        document.querySelectorAll('.toggle-switch input').forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                this.handleToggle(e.target);
            });
        });
    },

    loadCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('reservas.html')) {
            this.initReservasPage();
        } else if (path.includes('espacos.html')) {
            this.initEspacosPage();
        } else if (path.includes('colaboradores.html')) {
            this.initColaboradoresPage();
        } else if (path.includes('configuracoes.html')) {
            this.initConfiguracoesPage();
        } else {
            this.initDashboard();
        }
    },

    handleAction(action, id, element) {
        const actions = {
            'new-reservation': () => this.openNewReservationModal(),
            'view-reservation': () => this.viewReservation(id),
            'edit-reservation': () => this.editReservation(id),
            'approve-reservation': () => this.approveReservation(id),
            'reject-reservation': () => this.rejectReservation(id),
            'delete-reservation': () => this.deleteReservation(id),
            'new-space': () => this.openNewSpaceModal(),
            'reserve-space': () => this.reserveSpace(id),
            'new-collaborator': () => this.openNewCollaboratorModal(),
            'delete-collaborator': () => this.deleteCollaborator(id),
            'export-data': () => this.exportData(),
            'refresh': () => this.refreshPage()
        };

        if (actions[action]) {
            actions[action]();
        }
    },

    // ============================================
    // DASHBOARD
    // ============================================
    initDashboard() {
        console.log('Dashboard iniciado');
        this.updateDashboardStats();
        this.loadRecentReservations();
        this.renderCalendar();
    },

    updateDashboardStats() {
        const reservations = DataStore.getReservations();
        const today = DataStore.formatDate(new Date());
        
        const todayCount = reservations.filter(r => r.date === today).length;
        const pendingCount = reservations.filter(r => r.status === 'pending').length;
        const totalCount = reservations.length;

        // Atualizar números com animação
        this.animateValue('todayReservations', 0, todayCount, 1000);
        this.animateValue('pendingReservations', 0, pendingCount, 1000);
        this.animateValue('totalReservations', 0, totalCount, 1000);
    },

    animateValue(elementId, start, end, duration) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= end) {
                element.textContent = end;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 16);
    },

    loadRecentReservations() {
        const reservations = DataStore.getReservations().slice(0, 5);
        const container = document.querySelector('#recentReservations, .recent-reservations-list');
        
        if (!container) return;

        container.innerHTML = reservations.map(r => `
            <div class="reservation-item" onclick="App.viewReservation(${r.id})" style="cursor: pointer;">
                <div class="reservation-header">
                    <strong class="reservation-title">${r.title}</strong>
                    <span class="badge badge-${r.status}">${this.getStatusText(r.status)}</span>
                </div>
                <div class="reservation-location">📍 ${r.spaceName}</div>
                <div class="reservation-details">
                    👤 ${r.requestor} • 📅 ${r.date} • 🕐 ${r.startTime} - ${r.endTime}
                </div>
            </div>
        `).join('');
    },

    renderCalendar() {
        // Calendário já está no HTML estático
        console.log('Calendário renderizado');
    },

    // ============================================
    // MODAIS DE RESERVA
    // ============================================
    openNewReservationModal() {
        const spaces = DataStore.getSpaces();
        
        const content = `
            <div class="modal-header">
                <h2>📅 Nova Reserva</h2>
            </div>
            <div class="modal-body">
                <form id="reservationForm" onsubmit="App.submitReservation(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tipo de Reserva *</label>
                            <select class="form-control" name="type" required>
                                <option value="">Selecione...</option>
                                <option value="internal">Interna (Professor/Colaborador)</option>
                                <option value="external">Externa (Empresa)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Espaço *</label>
                            <select class="form-control" name="spaceId" required>
                                <option value="">Selecione...</option>
                                ${spaces.map(s => `<option value="${s.id}">${s.name}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nome do Solicitante *</label>
                            <input type="text" class="form-control" name="requestor" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">E-mail *</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Telefone</label>
                            <input type="tel" class="form-control" name="phone" placeholder="(11) 98765-4321">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Data *</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Horário de Início *</label>
                            <input type="time" class="form-control" name="startTime" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Horário de Término *</label>
                            <input type="time" class="form-control" name="endTime" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Título do Evento *</label>
                        <input type="text" class="form-control" name="title" required placeholder="Ex: Palestra sobre Inovação">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Descreva o evento..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Número de Participantes</label>
                        <input type="number" class="form-control" name="participants" min="1" placeholder="Ex: 50">
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Reserva</button>
                    </div>
                </form>
            </div>
        `;

        Modal.open('reservationModal', content);
    },

    submitReservation(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        
        const spaceId = parseInt(formData.get('spaceId'));
        const space = DataStore.getSpaceById(spaceId);
        
        // Converter data
        const dateInput = formData.get('date');
        const [year, month, day] = dateInput.split('-');
        const formattedDate = `${day}/${month}/${year}`;

        const reservation = {
            spaceId: spaceId,
            spaceName: space.name,
            type: formData.get('type'),
            requestor: formData.get('requestor'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            date: formattedDate,
            startTime: formData.get('startTime'),
            endTime: formData.get('endTime'),
            title: formData.get('title'),
            description: formData.get('description'),
            participants: parseInt(formData.get('participants')) || 0
        };

        // Verificar conflito
        if (DataStore.checkConflict(reservation.spaceId, reservation.date, reservation.startTime, reservation.endTime)) {
            Toast.show('❌ Conflito de horário detectado! Este espaço já está reservado neste horário.', 'error');
            return;
        }

        DataStore.addReservation(reservation);
        Modal.close();
        Toast.show('✅ Reserva criada com sucesso!', 'success');
        
        setTimeout(() => {
            window.location.href = 'reservas.html';
        }, 1000);
    },

    viewReservation(id) {
        const reservation = DataStore.getReservationById(id);
        if (!reservation) return;

        const content = `
            <div class="modal-header">
                <h2>📋 Detalhes da Reserva #${reservation.id}</h2>
            </div>
            <div class="modal-body">
                <div class="reservation-details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="badge badge-${reservation.status}">${this.getStatusText(reservation.status)}</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Tipo:</span>
                        <span>${reservation.type === 'internal' ? 'Interna' : 'Externa'}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Título:</span>
                        <strong>${reservation.title}</strong>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Solicitante:</span>
                        <span>${reservation.requestor}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">E-mail:</span>
                        <span>${reservation.email}</span>
                    </div>

                    ${reservation.phone ? `
                    <div class="detail-item">
                        <span class="detail-label">Telefone:</span>
                        <span>${reservation.phone}</span>
                    </div>` : ''}

                    <div class="detail-item">
                        <span class="detail-label">Espaço:</span>
                        <span>${reservation.spaceName}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Data:</span>
                        <span>${reservation.date}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Horário:</span>
                        <span>${reservation.startTime} - ${reservation.endTime}</span>
                    </div>

                    ${reservation.participants ? `
                    <div class="detail-item">
                        <span class="detail-label">Participantes:</span>
                        <span>${reservation.participants} pessoas</span>
                    </div>` : ''}

                    ${reservation.description ? `
                    <div class="detail-item full-width">
                        <span class="detail-label">Descrição:</span>
                        <p style="margin-top: 8px;">${reservation.description}</p>
                    </div>` : ''}
                </div>

                <div class="modal-actions">
                    ${reservation.status === 'pending' ? `
                        <button class="btn btn-success" onclick="App.approveReservation(${reservation.id})">
                            ✓ Aprovar
                        </button>
                        <button class="btn btn-danger" onclick="App.rejectReservation(${reservation.id})">
                            ✗ Rejeitar
                        </button>
                    ` : ''}
                    <button class="btn btn-secondary" onclick="Modal.close()">Fechar</button>
                </div>
            </div>
        `;

        Modal.open('viewReservationModal', content);
    },

    approveReservation(id) {
        DataStore.updateReservation(id, { status: 'approved' });
        Toast.show('✅ Reserva aprovada com sucesso!', 'success');
        Modal.close();
        setTimeout(() => location.reload(), 500);
    },

    rejectReservation(id) {
        if (confirm('Deseja realmente rejeitar esta reserva?')) {
            DataStore.updateReservation(id, { status: 'rejected' });
            Toast.show('Reserva rejeitada', 'warning');
            Modal.close();
            setTimeout(() => location.reload(), 500);
        }
    },

    deleteReservation(id) {
        if (confirm('Deseja realmente excluir esta reserva?')) {
            DataStore.deleteReservation(id);
            Toast.show('✅ Reserva excluída com sucesso', 'success');
            setTimeout(() => location.reload(), 500);
        }
    },

    // ============================================
    // PÁGINA DE RESERVAS
    // ============================================
    initReservasPage() {
        console.log('Página de Reservas iniciada');
        // As reservas já estão no HTML
    },

    // ============================================
    // ESPAÇOS
    // ============================================
    initEspacosPage() {
        console.log('Página de Espaços iniciada');
    },

    openNewSpaceModal() {
        const content = `
            <div class="modal-header">
                <h2>🏢 Adicionar Novo Espaço</h2>
            </div>
            <div class="modal-body">
                <form id="spaceForm" onsubmit="App.submitSpace(event)">
                    <div class="form-group">
                        <label class="form-label">Nome do Espaço *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Capacidade *</label>
                            <input type="number" class="form-control" name="capacity" min="1" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tipo *</label>
                            <select class="form-control" name="type" required>
                                <option value="">Selecione...</option>
                                <option value="sala">Sala de Aula</option>
                                <option value="laboratorio">Laboratório</option>
                                <option value="auditorio">Auditório</option>
                                <option value="sala_reuniao">Sala de Reunião</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar Espaço</button>
                    </div>
                </form>
            </div>
        `;

        Modal.open('spaceModal', content);
    },

    submitSpace(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);

        const space = {
            name: formData.get('name'),
            capacity: parseInt(formData.get('capacity')),
            type: formData.get('type'),
            description: formData.get('description')
        };

        DataStore.addSpace(space);
        Modal.close();
        Toast.show('✅ Espaço adicionado com sucesso!', 'success');
        setTimeout(() => location.reload(), 500);
    },

    reserveSpace(spaceId) {
        this.openNewReservationModal();
        // Pre-selecionar o espaço
        setTimeout(() => {
            const select = document.querySelector('select[name="spaceId"]');
            if (select) select.value = spaceId;
        }, 100);
    },

    // Continua...
    getStatusText(status) {
        const statusMap = {
            'pending': 'Pendente',
            'approved': 'Aprovada',
            'rejected': 'Rejeitada'
        };
        return statusMap[status] || status;
    }
};

// ============================================
// COLABORADORES
// ============================================
App.initColaboradoresPage = function() {
    console.log('Página de Colaboradores iniciada');
};

App.openNewCollaboratorModal = function() {
    const content = `
        <div class="modal-header">
            <h2>👥 Adicionar Colaborador</h2>
        </div>
        <div class="modal-body">
            <form id="collaboratorForm" onsubmit="App.submitCollaborator(event)">
                <div class="form-group">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" class="form-control" name="name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">E-mail *</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Perfil de Acesso *</label>
                        <select class="form-control" name="role" required>
                            <option value="">Selecione...</option>
                            <option value="admin">Gestor/Admin</option>
                            <option value="collaborator">Colaborador</option>
                            <option value="viewer">Visualizador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departamento</label>
                        <input type="text" class="form-control" name="department">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar Colaborador</button>
                </div>
            </form>
        </div>
    `;

    Modal.open('collaboratorModal', content);
};

App.submitCollaborator = function(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);

    const collaborator = {
        name: formData.get('name'),
        email: formData.get('email'),
        role: formData.get('role'),
        department: formData.get('department')
    };

    DataStore.addCollaborator(collaborator);
    Modal.close();
    Toast.show('✅ Colaborador adicionado com sucesso!', 'success');
    setTimeout(() => location.reload(), 500);
};

App.deleteCollaborator = function(id) {
    if (confirm('Deseja realmente excluir este colaborador?')) {
        DataStore.deleteCollaborator(id);
        Toast.show('✅ Colaborador excluído com sucesso', 'success');
        setTimeout(() => location.reload(), 500);
    }
};

// ============================================
// CONFIGURAÇÕES
// ============================================
App.initConfiguracoesPage = function() {
    console.log('Página de Configurações iniciada');
    this.setupSettingsTabs();
};

App.setupSettingsTabs = function() {
    const tabs = document.querySelectorAll('.settings-tab');
    const contents = document.querySelectorAll('.settings-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remover active de todos
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            // Adicionar active no clicado
            tab.classList.add('active');
            const targetId = tab.dataset.tab;
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
};

App.switchTab = function(tab) {
    document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.settings-content').forEach(c => c.classList.remove('active'));
    
    tab.classList.add('active');
    const targetId = tab.dataset.tab;
    const content = document.getElementById(targetId);
    if (content) content.classList.add('active');
};

App.handleToggle = function(toggle) {
    const label = toggle.closest('.setting-item')?.querySelector('.setting-title')?.textContent;
    const state = toggle.checked ? 'ativado' : 'desativado';
    Toast.show(`${label} ${state}`, 'info');
};

// ============================================
// UTILIDADES
// ============================================
App.scrollToSection = function(target) {
    const element = document.getElementById(target);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

App.exportData = function() {
    const data = DataStore.data;
    const json = JSON.stringify(data, null, 2);
    const blob = new Blob([json], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `senac-reservas-${new Date().getTime()}.json`;
    a.click();
    Toast.show('✅ Dados exportados com sucesso!', 'success');
};

App.refreshPage = function() {
    Toast.show('🔄 Atualizando...', 'info');
    setTimeout(() => location.reload(), 500);
};

// ============================================
// INICIALIZAÇÃO
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    App.init();
});

// Tornar funções globais para onclick nos HTMLs
window.App = App;
window.Modal = Modal;
window.Toast = Toast;

// ============================================
// CALENDÁRIO FUNCIONAL
// ============================================
const Calendar = {
    currentDate: new Date(),
    currentMonth: new Date().getMonth(),
    currentYear: new Date().getFullYear(),

    init() {
        this.render();
        this.setupControls();
    },

    setupControls() {
        const prevBtn = document.querySelector('.calendar-controls .btn-icon:first-child');
        const nextBtn = document.querySelector('.calendar-controls .btn-icon:last-child');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                this.previousMonth();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.nextMonth();
            });
        }
    },

    previousMonth() {
        this.currentMonth--;
        if (this.currentMonth < 0) {
            this.currentMonth = 11;
            this.currentYear--;
        }
        this.render();
    },

    nextMonth() {
        this.currentMonth++;
        if (this.currentMonth > 11) {
            this.currentMonth = 0;
            this.currentYear++;
        }
        this.render();
    },

    getMonthName(month) {
        const months = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];
        return months[month];
    },

    getDaysInMonth(month, year) {
        return new Date(year, month + 1, 0).getDate();
    },

    getFirstDayOfMonth(month, year) {
        return new Date(year, month, 1).getDay();
    },

    formatDateForComparison(day, month, year) {
        const d = String(day).padStart(2, '0');
        const m = String(month + 1).padStart(2, '0');
        return `${d}/${m}/${year}`;
    },

    getReservationsForDate(date) {
        const reservations = DataStore.getReservations();
        return reservations.filter(r => r.date === date);
    },

    render() {
        const monthNameElement = document.querySelector('.calendar-month');
        const calendarBody = document.querySelector('.calendar-body');

        if (!monthNameElement || !calendarBody) return;

        // Atualizar nome do mês
        monthNameElement.textContent = `${this.getMonthName(this.currentMonth)} ${this.currentYear}`;

        // Limpar calendário
        calendarBody.innerHTML = '';

        const daysInMonth = this.getDaysInMonth(this.currentMonth, this.currentYear);
        const firstDay = this.getFirstDayOfMonth(this.currentMonth, this.currentYear);
        const today = new Date();
        const isCurrentMonth = this.currentMonth === today.getMonth() && this.currentYear === today.getFullYear();

        // Adicionar dias vazios antes do primeiro dia
        for (let i = 0; i < firstDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day calendar-day-empty';
            calendarBody.appendChild(emptyDay);
        }

        // Adicionar dias do mês
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = this.formatDateForComparison(day, this.currentMonth, this.currentYear);
            const reservations = this.getReservationsForDate(dateStr);
            const hasReservations = reservations.length > 0;
            const isToday = isCurrentMonth && day === today.getDate();

            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            
            if (isToday) {
                dayElement.classList.add('calendar-day-today');
            }
            
            if (hasReservations) {
                dayElement.classList.add('calendar-day-reserved');
                dayElement.style.cursor = 'pointer';
                dayElement.title = `${reservations.length} reserva(s)`;
                
                // Adicionar evento de clique
                dayElement.addEventListener('click', () => {
                    this.showReservationsModal(dateStr, reservations);
                });
            }

            dayElement.innerHTML = `
                ${day}
                ${hasReservations ? '<span class="day-indicator"></span>' : ''}
            `;

            calendarBody.appendChild(dayElement);
        }
    },

    showReservationsModal(date, reservations) {
        const reservationsList = reservations.map(r => `
            <div class="reservation-item" style="margin-bottom: 16px; padding: 16px; background: var(--gray-50); border-radius: 8px; cursor: pointer;" onclick="App.viewReservation(${r.id})">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <strong style="color: var(--gray-800)">${r.title}</strong>
                    <span class="badge badge-${r.status}">${App.getStatusText(r.status)}</span>
                </div>
                <div style="font-size: 13px; color: var(--gray-600); margin-bottom: 4px;">
                    📍 ${r.spaceName}
                </div>
                <div style="font-size: 13px; color: var(--gray-600);">
                    🕐 ${r.startTime} - ${r.endTime} • 👤 ${r.requestor}
                </div>
            </div>
        `).join('');

        const content = `
            <div class="modal-header">
                <h2>📅 Reservas do Dia ${date}</h2>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 20px; color: var(--gray-600);">
                    <strong>${reservations.length}</strong> ${reservations.length === 1 ? 'reserva encontrada' : 'reservas encontradas'}
                </p>
                ${reservationsList}
                <div class="modal-actions" style="margin-top: 24px;">
                    <button class="btn btn-primary" data-action="new-reservation" onclick="Modal.close(); App.openNewReservationModal();">
                        ➕ Nova Reserva
                    </button>
                    <button class="btn btn-secondary" onclick="Modal.close()">
                        Fechar
                    </button>
                </div>
            </div>
        `;

        Modal.open('calendarReservationsModal', content);
    }
};

// Atualizar a função initDashboard do App
App.initDashboard = function() {
    console.log('Dashboard iniciado');
    this.updateDashboardStats();
    this.loadRecentReservations();
    
    // Inicializar calendário
    setTimeout(() => {
        Calendar.init();
    }, 100);
};
