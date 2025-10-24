/**
 * Gerenciamento de dados com LocalStorage
 */
class DataStore {
    constructor() {
        this.data = this.loadData();
    }

    loadData() {
        try {
            const stored = localStorage.getItem(CONFIG.STORAGE_KEY);
            if (stored) return JSON.parse(stored);
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
        }
        return this.getDefaultData();
    }

    saveData() {
        try {
            localStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(this.data));
            return true;
        } catch (error) {
            console.error('Erro ao salvar dados:', error);
            return false;
        }
    }

    getDefaultData() {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        return {
            reservations: [
                {
                    id: 1, spaceId: 1, spaceName: 'Auditório Principal',
                    requestorName: 'Maria Silva', requestorEmail: 'maria.silva@senac.com.br',
                    requestorPhone: '(11) 98765-4321', type: 'internal',
                    date: Utils.formatDate(today), startTime: '09:00', endTime: '12:00',
                    title: 'Palestra sobre Inovação Tecnológica',
                    description: 'Evento para apresentação de novas tecnologias.',
                    participants: 150, status: 'approved',
                    createdAt: new Date().toISOString()
                },
                {
                    id: 2, spaceId: 2, spaceName: 'Laboratório de Informática 1',
                    requestorName: 'João Santos', requestorEmail: 'joao.santos@senac.com.br',
                    requestorPhone: '(11) 91234-5678', type: 'internal',
                    date: Utils.formatDate(today), startTime: '14:00', endTime: '17:00',
                    title: 'Aula Prática de Programação',
                    description: 'Aula prática sobre desenvolvimento web.',
                    participants: 35, status: 'approved',
                    createdAt: new Date().toISOString()
                },
                {
                    id: 3, spaceId: 3, spaceName: 'Sala de Reuniões Executiva',
                    requestorName: 'Ana Costa', requestorEmail: 'ana.costa@senac.com.br',
                    type: 'internal', date: Utils.formatDate(tomorrow),
                    startTime: '10:00', endTime: '11:30',
                    title: 'Reunião de Planejamento', participants: 15,
                    status: 'pending', createdAt: new Date().toISOString()
                }
            ],
            spaces: [
                { id: 1, name: 'Auditório Principal', capacity: 200, type: 'auditorio',
                  description: 'Auditório equipado com sistema de som e projeção.',
                  resources: ['projetor', 'computador', 'wifi', 'ar_condicionado'], status: 'active' },
                { id: 2, name: 'Laboratório de Informática 1', capacity: 40, type: 'laboratorio',
                  description: 'Laboratório com 40 computadores.',
                  resources: ['computador', 'projetor', 'wifi'], status: 'active' },
                { id: 3, name: 'Sala de Reuniões Executiva', capacity: 20, type: 'sala_reuniao',
                  resources: ['projetor', 'wifi', 'ar_condicionado'], status: 'active' },
                { id: 4, name: 'Sala de Aula 101', capacity: 50, type: 'sala',
                  resources: ['projetor', 'quadro'], status: 'active' }
            ],
            collaborators: [
                { id: 1, name: 'Administrador', email: 'admin@senac.com.br',
                  role: 'admin', department: 'TI', status: 'active' },
                { id: 2, name: 'Maria Silva', email: 'maria.silva@senac.com.br',
                  role: 'collaborator', department: 'Coordenação', status: 'active' }
            ],
            settings: {
                systemName: 'Sistema de Reservas SENAC',
                workingHours: { start: '08:00', end: '22:00' },
                requireApproval: true
            },
            currentUser: {
                id: 1, name: 'Administrador', email: 'admin@senac.com.br',
                role: 'admin', avatar: 'AD'
            }
        };
    }

    // Métodos CRUD para Reservas
    getReservations() { return this.data.reservations || []; }
    
    getReservationById(id) {
        return this.data.reservations.find(r => r.id === id);
    }
    
    addReservation(reservation) {
        const newReservation = {
            ...reservation,
            id: this.generateId(this.data.reservations),
            createdAt: new Date().toISOString(),
            status: this.data.settings.requireApproval ? 'pending' : 'approved'
        };
        this.data.reservations.unshift(newReservation);
        this.saveData();
        return newReservation;
    }
    
    updateReservation(id, updates) {
        const index = this.data.reservations.findIndex(r => r.id === id);
        if (index !== -1) {
            this.data.reservations[index] = { ...this.data.reservations[index], ...updates };
            this.saveData();
            return this.data.reservations[index];
        }
        return null;
    }
    
    deleteReservation(id) {
        const index = this.data.reservations.findIndex(r => r.id === id);
        if (index !== -1) {
            this.data.reservations.splice(index, 1);
            this.saveData();
            return true;
        }
        return false;
    }

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
    }

    timeToMinutes(time) {
        const [hours, minutes] = time.split(':').map(Number);
        return hours * 60 + minutes;
    }

    // Métodos para Espaços
    getSpaces() { return this.data.spaces || []; }
    getSpaceById(id) { return this.data.spaces.find(s => s.id === id); }
    
    addSpace(space) {
        const newSpace = { ...space, id: this.generateId(this.data.spaces), status: 'active' };
        this.data.spaces.push(newSpace);
        this.saveData();
        return newSpace;
    }

    // Métodos para Colaboradores
    getCollaborators() { return this.data.collaborators || []; }
    
    addCollaborator(collaborator) {
        const newCollab = { ...collaborator, id: this.generateId(this.data.collaborators), status: 'active' };
        this.data.collaborators.push(newCollab);
        this.saveData();
        return newCollab;
    }

    deleteCollaborator(id) {
        const index = this.data.collaborators.findIndex(c => c.id === id);
        if (index !== -1) {
            this.data.collaborators.splice(index, 1);
            this.saveData();
            return true;
        }
        return false;
    }

    generateId(array) {
        if (!array || array.length === 0) return 1;
        return Math.max(...array.map(item => item.id)) + 1;
    }

    getCurrentUser() { return this.data.currentUser; }
}

const store = new DataStore();
