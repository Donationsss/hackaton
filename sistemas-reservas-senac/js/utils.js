/**
 * Funções utilitárias
 */
const Utils = {
    formatDate(dateString) {
        if (!dateString) return '';
        const parts = dateString.split('/');
        if (parts.length === 3) return dateString;
        
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    },

    formatTime(time) {
        if (!time) return '';
        return time.substring(0, 5);
    },

    parseDate(dateString) {
        const parts = dateString.split('/');
        if (parts.length === 3) {
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }
        return new Date(dateString);
    },

    formatDateToInput(dateString) {
        const date = this.parseDate(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    },

    getTodayFormatted() {
        const today = new Date();
        const day = String(today.getDate()).padStart(2, '0');
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const year = today.getFullYear();
        return `${day}/${month}/${year}`;
    },

    getMonthName(month) {
        const months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                       'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        return months[month];
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    sanitizeHTML(str) {
        const temp = document.createElement('div');
        temp.textContent = str;
        return temp.innerHTML;
    },

    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    getStatusText(status) {
        const map = { pending: 'Pendente', approved: 'Aprovada', rejected: 'Rejeitada', completed: 'Concluída' };
        return map[status] || status;
    },

    getTypeText(type) {
        const map = { internal: 'Interna', external: 'Externa' };
        return map[type] || type;
    },

    getRoleText(role) {
        const map = { admin: 'Gestor/Admin', collaborator: 'Colaborador', viewer: 'Visualizador' };
        return map[role] || role;
    },

    getSpaceTypeText(type) {
        const map = { 
            sala: 'Sala de Aula', laboratorio: 'Laboratório', auditorio: 'Auditório',
            sala_reuniao: 'Sala de Reunião', outro: 'Outro'
        };
        return map[type] || type;
    },

    getResourceText(resource) {
        const map = { 
            projetor: 'Projetor', computador: 'Computador', quadro: 'Quadro',
            wifi: 'Wi-Fi', ar_condicionado: 'Ar-condicionado'
        };
        return map[resource] || resource;
    }
};
