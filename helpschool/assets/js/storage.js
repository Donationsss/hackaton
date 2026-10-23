/**
 * HelpSchool — Camada de persistência e utilitários compartilhados
 * Todos os dados são mantidos no LocalStorage, sem dependência de backend.
 */
(function (HS) {
  'use strict';

  const KEYS = {
    users: 'helpschool.users',
    tickets: 'helpschool.tickets',
    messages: 'helpschool.messages',
    session: 'helpschool.session',
    version: 'helpschool.data_version'
  };

  const DATA_VERSION = '1.1.0';

  const ROLES = {
    professor: {
      label: 'Professor',
      description: 'Abre chamados e acompanha suas solicitações.',
      tone: 'blue'
    },
    secretaria: {
      label: 'Secretaria',
      description: 'Gerencia chamados, atribui técnicos e atualiza status.',
      tone: 'amber'
    },
    tecnico: {
      label: 'Técnico',
      description: 'Atende chamados, registra diagnóstico e solução.',
      tone: 'green'
    },
    admin: {
      label: 'Administrador',
      description: 'Acesso completo, relatórios e gestão de usuários.',
      tone: 'purple'
    }
  };

  const STATUS = {
    aberto: { label: 'Aberto', tone: 'open', order: 1 },
    em_andamento: { label: 'Em andamento', tone: 'progress', order: 2 },
    resolvido: { label: 'Resolvido', tone: 'resolved', order: 3 },
    concluido: { label: 'Concluído', tone: 'done', order: 4 }
  };

  const PRIORITIES = {
    baixa: { label: 'Baixa', tone: 'low', order: 1 },
    media: { label: 'Média', tone: 'medium', order: 2 },
    alta: { label: 'Alta', tone: 'high', order: 3 }
  };

  const CATEGORIES = {
    infraestrutura: { label: 'Infraestrutura', tone: 'blue' },
    equipamentos: { label: 'Equipamentos', tone: 'purple' },
    ti: { label: 'TI', tone: 'cyan' },
    limpeza: { label: 'Limpeza', tone: 'green' },
    outros: { label: 'Outros', tone: 'slate' }
  };

  const CATEGORY_ICONS = {
    infraestrutura: 'i-building',
    equipamentos: 'i-wrench',
    ti: 'i-grid',
    limpeza: 'i-sparkle',
    outros: 'i-tag'
  };

  // ---------- Utilitários gerais ----------

  const Utils = {
    uid(prefix) {
      const base = prefix || 'id';
      if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        return `${base}_${window.crypto.randomUUID()}`;
      }
      return `${base}_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;
    },

    escapeHTML(value) {
      return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    },

    initials(name) {
      const parts = String(name || '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);
      if (!parts.length) return '?';
      if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    },

    firstName(name) {
      return String(name || '').trim().split(/\s+/)[0] || '';
    },

    roleLabel(role) {
      return ROLES[role] ? ROLES[role].label : role;
    },

    statusLabel(status) {
      return STATUS[status] ? STATUS[status].label : status;
    },

    priorityLabel(priority) {
      return PRIORITIES[priority] ? PRIORITIES[priority].label : priority;
    },

    categoryLabel(category) {
      return CATEGORIES[category] ? CATEGORIES[category].label : category;
    },

    categoryIcon(category) {
      return CATEGORY_ICONS[category] || 'i-tag';
    },

    formatDate(iso, options) {
      if (!iso) return '—';
      const date = new Date(iso);
      if (Number.isNaN(date.getTime())) return '—';
      return new Intl.DateTimeFormat('pt-BR', options || {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      }).format(date);
    },

    formatDateTime(iso) {
      if (!iso) return '—';
      const date = new Date(iso);
      if (Number.isNaN(date.getTime())) return '—';
      return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }).format(date);
    },

    formatTime(iso) {
      if (!iso) return '';
      return new Intl.DateTimeFormat('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
      }).format(new Date(iso));
    },

    formatDayLabel(iso) {
      const date = new Date(iso);
      const today = new Date();
      const yesterday = new Date();
      yesterday.setDate(today.getDate() - 1);

      const sameDay = (a, b) =>
        a.getDate() === b.getDate() &&
        a.getMonth() === b.getMonth() &&
        a.getFullYear() === b.getFullYear();

      if (sameDay(date, today)) return 'Hoje';
      if (sameDay(date, yesterday)) return 'Ontem';
      return Utils.formatDate(iso, { day: '2-digit', month: 'long', year: 'numeric' });
    },

    timeAgo(iso) {
      if (!iso) return '—';
      const diff = Date.now() - new Date(iso).getTime();
      if (Number.isNaN(diff)) return '—';
      const minutes = Math.floor(diff / 60000);
      if (minutes < 1) return 'agora mesmo';
      if (minutes < 60) return `há ${minutes} min`;
      const hours = Math.floor(minutes / 60);
      if (hours < 24) return `há ${hours} h`;
      const days = Math.floor(hours / 24);
      if (days === 1) return 'ontem';
      if (days < 30) return `há ${days} dias`;
      const months = Math.floor(days / 30);
      return `há ${months} ${months === 1 ? 'mês' : 'meses'}`;
    },

    shortTimeAgo(iso) {
      if (!iso) return '';
      const diff = Date.now() - new Date(iso).getTime();
      const minutes = Math.floor(diff / 60000);
      if (minutes < 1) return 'agora';
      if (minutes < 60) return `${minutes}m`;
      const hours = Math.floor(minutes / 60);
      if (hours < 24) return `${hours}h`;
      const days = Math.floor(hours / 24);
      if (days < 7) return `${days}d`;
      return Utils.formatDate(iso, { day: '2-digit', month: '2-digit' });
    },

    debounce(fn, delay) {
      let timer;
      return function debounced() {
        const context = this;
        const args = arguments;
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(context, args), delay || 250);
      };
    },

    normalizeText(value) {
      return String(value == null ? '' : value)
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
    },

    pluralize(count, singular, plural) {
      return `${count} ${count === 1 ? singular : plural || `${singular}s`}`;
    },

    percentage(part, total) {
      if (!total) return 0;
      return Math.round((part / total) * 100);
    },

    daysAgoISO(days, hour) {
      const date = new Date();
      date.setDate(date.getDate() - days);
      if (typeof hour === 'number') date.setHours(hour, 0, 0, 0);
      return date.toISOString();
    },

    hoursAgoISO(hours) {
      return new Date(Date.now() - hours * 3600000).toISOString();
    }
  };

  // ---------- Dados iniciais ----------

  const seedUsers = [
    {
      id: 'u1',
      name: 'Ana Carolina',
      email: 'professor@helpschool.com',
      password: '123456',
      role: 'professor',
      department: 'Ciências da Natureza',
      color: 'blue',
      active: true,
      createdAt: Utils.daysAgoISO(180),
      lastLoginAt: Utils.hoursAgoISO(2)
    },
    {
      id: 'u2',
      name: 'Beatriz Nogueira',
      email: 'secretaria@helpschool.com',
      password: '123456',
      role: 'secretaria',
      department: 'Secretaria Acadêmica',
      color: 'amber',
      active: true,
      createdAt: Utils.daysAgoISO(240),
      lastLoginAt: Utils.hoursAgoISO(4)
    },
    {
      id: 'u3',
      name: 'Carlos Mendes',
      email: 'tecnico@helpschool.com',
      password: '123456',
      role: 'tecnico',
      department: 'Manutenção e TI',
      color: 'green',
      active: true,
      createdAt: Utils.daysAgoISO(300),
      lastLoginAt: Utils.hoursAgoISO(26)
    },
    {
      id: 'u4',
      name: 'Daniel Rocha',
      email: 'admin@helpschool.com',
      password: '123456',
      role: 'admin',
      department: 'Direção',
      color: 'purple',
      active: true,
      createdAt: Utils.daysAgoISO(420),
      lastLoginAt: Utils.hoursAgoISO(1)
    },
    {
      id: 'u5',
      name: 'Marina Costa',
      email: 'professor2@helpschool.com',
      password: '123456',
      role: 'professor',
      department: 'Linguagens',
      color: 'rose',
      active: true,
      createdAt: Utils.daysAgoISO(120),
      lastLoginAt: Utils.daysAgoISO(2, 14)
    },
    {
      id: 'u6',
      name: 'Rafael Lima',
      email: 'tecnico2@helpschool.com',
      password: '123456',
      role: 'tecnico',
      department: 'Manutenção e TI',
      color: 'cyan',
      active: true,
      createdAt: Utils.daysAgoISO(90),
      lastLoginAt: Utils.daysAgoISO(1, 10)
    }
  ];

  function buildHistory(items) {
    return items.map((item, index) => ({
      id: `h_${index}_${Math.random().toString(36).slice(2, 6)}`,
      type: item.type || 'system',
      text: item.text,
      userId: item.userId || 'system',
      createdAt: item.createdAt
    }));
  }

  const seedTickets = [
    {
      id: 'CH-1059',
      title: 'Computador da Sala 12 não liga',
      description: 'O computador utilizado para aulas digitais não liga desde a primeira aula. Já testamos outra tomada e o problema continua.',
      category: 'equipamentos',
      priority: 'alta',
      location: 'Sala 12',
      status: 'aberto',
      requesterId: 'u1',
      assigneeId: null,
      diagnosis: '',
      solution: '',
      createdAt: Utils.hoursAgoISO(2),
      updatedAt: Utils.hoursAgoISO(2),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u1', createdAt: Utils.hoursAgoISO(2) }
      ])
    },
    {
      id: 'CH-1058',
      title: 'Nobreak apitando no Laboratório 01',
      description: 'O nobreak do rack de informática está emitindo um apito contínuo e a luz de bateria pisca em vermelho.',
      category: 'ti',
      priority: 'alta',
      location: 'Laboratório 01',
      status: 'aberto',
      requesterId: 'u5',
      assigneeId: null,
      diagnosis: '',
      solution: '',
      createdAt: Utils.hoursAgoISO(4),
      updatedAt: Utils.hoursAgoISO(4),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u5', createdAt: Utils.hoursAgoISO(4) }
      ])
    },
    {
      id: 'CH-1057',
      title: 'Limpeza após evento no auditório',
      description: 'O auditório precisa de limpeza completa após a apresentação de ontem. Há resíduos no palco e nas cadeiras.',
      category: 'limpeza',
      priority: 'baixa',
      location: 'Auditório',
      status: 'em_andamento',
      requesterId: 'u2',
      assigneeId: 'u6',
      diagnosis: '',
      solution: '',
      createdAt: Utils.hoursAgoISO(7),
      updatedAt: Utils.hoursAgoISO(3),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u2', createdAt: Utils.hoursAgoISO(7) },
        { type: 'assigned', text: 'Chamado atribuído a Rafael Lima', userId: 'u2', createdAt: Utils.hoursAgoISO(5) },
        { type: 'status', text: 'Status alterado de Aberto para Em andamento', userId: 'u6', createdAt: Utils.hoursAgoISO(3) }
      ])
    },
    {
      id: 'CH-1056',
      title: 'Projetor do Laboratório 01 piscando',
      description: 'A imagem do projetor fica piscando intermitentemente durante as apresentações.',
      category: 'equipamentos',
      priority: 'alta',
      location: 'Laboratório 01',
      status: 'resolvido',
      requesterId: 'u1',
      assigneeId: 'u3',
      diagnosis: 'Cabo HDMI com mau contato e entrada do projetor com poeira acumulada.',
      solution: 'Cabo substituído e entrada limpa. Equipamento testado por 30 minutos sem falhas.',
      createdAt: Utils.hoursAgoISO(28),
      updatedAt: Utils.hoursAgoISO(5),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u1', createdAt: Utils.hoursAgoISO(28) },
        { type: 'assigned', text: 'Chamado atribuído a Carlos Mendes', userId: 'u2', createdAt: Utils.hoursAgoISO(24) },
        { type: 'status', text: 'Status alterado de Aberto para Em andamento', userId: 'u3', createdAt: Utils.hoursAgoISO(22) },
        { type: 'diagnosis', text: 'Diagnóstico registrado por Carlos Mendes', userId: 'u3', createdAt: Utils.hoursAgoISO(8) },
        { type: 'solution', text: 'Solução registrada por Carlos Mendes', userId: 'u3', createdAt: Utils.hoursAgoISO(5) },
        { type: 'status', text: 'Status alterado de Em andamento para Resolvido', userId: 'u3', createdAt: Utils.hoursAgoISO(5) }
      ])
    },
    {
      id: 'CH-1055',
      title: 'Porta da Sala 12 desalinhada',
      description: 'A porta está raspando no piso e não fecha completamente, o que dificulta a concentração durante as aulas.',
      category: 'infraestrutura',
      priority: 'media',
      location: 'Sala 12',
      status: 'aberto',
      requesterId: 'u5',
      assigneeId: null,
      diagnosis: '',
      solution: '',
      createdAt: Utils.hoursAgoISO(20),
      updatedAt: Utils.hoursAgoISO(20),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u5', createdAt: Utils.hoursAgoISO(20) }
      ])
    },
    {
      id: 'CH-1054',
      title: 'Acesso ao diário digital bloqueado',
      description: 'Não consigo acessar o diário digital pelo navegador da escola. A tela fica carregando e retorna erro de conexão.',
      category: 'ti',
      priority: 'baixa',
      location: 'Sala dos professores',
      status: 'concluido',
      requesterId: 'u1',
      assigneeId: 'u3',
      diagnosis: 'Cache do navegador corrompido após atualização do sistema.',
      solution: 'Cache e cookies limpos, acesso restabelecido e atalho atualizado.',
      createdAt: Utils.daysAgoISO(4, 8),
      updatedAt: Utils.daysAgoISO(3, 15),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u1', createdAt: Utils.daysAgoISO(4, 8) },
        { type: 'assigned', text: 'Chamado atribuído a Carlos Mendes', userId: 'u2', createdAt: Utils.daysAgoISO(4, 10) },
        { type: 'status', text: 'Status alterado de Aberto para Em andamento', userId: 'u3', createdAt: Utils.daysAgoISO(4, 14) },
        { type: 'solution', text: 'Solução registrada por Carlos Mendes', userId: 'u3', createdAt: Utils.daysAgoISO(3, 15) },
        { type: 'status', text: 'Status alterado de Em andamento para Concluído', userId: 'u3', createdAt: Utils.daysAgoISO(3, 15) }
      ])
    },
    {
      id: 'CH-1053',
      title: 'Torneira da cozinha vazando',
      description: 'A torneira da pia principal está vazando pela base e formando poça no chão.',
      category: 'infraestrutura',
      priority: 'alta',
      location: 'Cozinha',
      status: 'concluido',
      requesterId: 'u2',
      assigneeId: 'u6',
      diagnosis: 'Anel de vedação desgastado.',
      solution: 'Vedação substituída e registro revisado. Vazamento eliminado.',
      createdAt: Utils.daysAgoISO(5, 7),
      updatedAt: Utils.daysAgoISO(4, 16),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u2', createdAt: Utils.daysAgoISO(5, 7) },
        { type: 'assigned', text: 'Chamado atribuído a Rafael Lima', userId: 'u2', createdAt: Utils.daysAgoISO(5, 9) },
        { type: 'status', text: 'Status alterado de Aberto para Em andamento', userId: 'u6', createdAt: Utils.daysAgoISO(5, 10) },
        { type: 'diagnosis', text: 'Diagnóstico registrado por Rafael Lima', userId: 'u6', createdAt: Utils.daysAgoISO(4, 15) },
        { type: 'solution', text: 'Solução registrada por Rafael Lima', userId: 'u6', createdAt: Utils.daysAgoISO(4, 16) },
        { type: 'status', text: 'Status alterado de Em andamento para Concluído', userId: 'u6', createdAt: Utils.daysAgoISO(4, 16) }
      ])
    },
    {
      id: 'CH-1052',
      title: 'Substituição de projetor multimídia',
      description: 'O projetor do auditório apresenta manchas escuras na projeção. A qualidade está comprometida para eventos.',
      category: 'equipamentos',
      priority: 'media',
      location: 'Projetor Multimídia',
      status: 'aberto',
      requesterId: 'u2',
      assigneeId: null,
      diagnosis: '',
      solution: '',
      createdAt: Utils.hoursAgoISO(12),
      updatedAt: Utils.hoursAgoISO(12),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u2', createdAt: Utils.hoursAgoISO(12) }
      ])
    },
    {
      id: 'CH-1051',
      title: 'Wi-Fi instável no Bloco B',
      description: 'As turmas do Bloco B estão perdendo conexão com frequência durante o uso dos tablets.',
      category: 'ti',
      priority: 'alta',
      location: 'Bloco B',
      status: 'em_andamento',
      requesterId: 'u1',
      assigneeId: 'u3',
      diagnosis: 'Sinal do access point do 2º andar está sendo afetado por interferência.',
      solution: '',
      createdAt: Utils.daysAgoISO(1, 9),
      updatedAt: Utils.hoursAgoISO(6),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u1', createdAt: Utils.daysAgoISO(1, 9) },
        { type: 'assigned', text: 'Chamado atribuído a Carlos Mendes', userId: 'u2', createdAt: Utils.daysAgoISO(1, 11) },
        { type: 'status', text: 'Status alterado de Aberto para Em andamento', userId: 'u3', createdAt: Utils.daysAgoISO(1, 13) },
        { type: 'diagnosis', text: 'Diagnóstico registrado por Carlos Mendes', userId: 'u3', createdAt: Utils.hoursAgoISO(6) }
      ])
    },
    {
      id: 'CH-1050',
      title: 'Limpeza do Laboratório 02',
      description: 'Após a aula prática de química, as bancadas precisam de limpeza e descarte correto dos materiais.',
      category: 'limpeza',
      priority: 'baixa',
      location: 'Laboratório 02',
      status: 'resolvido',
      requesterId: 'u5',
      assigneeId: 'u6',
      diagnosis: '',
      solution: 'Bancadas higienizadas e materiais descartados conforme o protocolo.',
      createdAt: Utils.daysAgoISO(2, 14),
      updatedAt: Utils.daysAgoISO(2, 17),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u5', createdAt: Utils.daysAgoISO(2, 14) },
        { type: 'assigned', text: 'Chamado atribuído a Rafael Lima', userId: 'u2', createdAt: Utils.daysAgoISO(2, 15) },
        { type: 'solution', text: 'Solução registrada por Rafael Lima', userId: 'u6', createdAt: Utils.daysAgoISO(2, 17) },
        { type: 'status', text: 'Status alterado de Em andamento para Resolvido', userId: 'u6', createdAt: Utils.daysAgoISO(2, 17) }
      ])
    },
    {
      id: 'CH-1049',
      title: 'Cadeiras quebradas na Sala 08',
      description: 'Três cadeiras estão com a estrutura solta e oferecem risco aos alunos. Foram retiradas da sala.',
      category: 'infraestrutura',
      priority: 'media',
      location: 'Sala 08',
      status: 'aberto',
      requesterId: 'u1',
      assigneeId: null,
      diagnosis: '',
      solution: '',
      createdAt: Utils.daysAgoISO(2, 10),
      updatedAt: Utils.daysAgoISO(2, 10),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u1', createdAt: Utils.daysAgoISO(2, 10) }
      ])
    },
    {
      id: 'CH-1048',
      title: 'Impressora sem conexão',
      description: 'A impressora da sala dos professores não aparece na lista de dispositivos disponíveis.',
      category: 'equipamentos',
      priority: 'media',
      location: 'Sala dos professores',
      status: 'concluido',
      requesterId: 'u5',
      assigneeId: 'u3',
      diagnosis: 'Impressora estava com IP alterado após reinício do roteador.',
      solution: 'IP fixo reconfigurado e impressora adicionada novamente aos computadores.',
      createdAt: Utils.daysAgoISO(8, 8),
      updatedAt: Utils.daysAgoISO(7, 11),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u5', createdAt: Utils.daysAgoISO(8, 8) },
        { type: 'assigned', text: 'Chamado atribuído a Carlos Mendes', userId: 'u2', createdAt: Utils.daysAgoISO(8, 9) },
        { type: 'diagnosis', text: 'Diagnóstico registrado por Carlos Mendes', userId: 'u3', createdAt: Utils.daysAgoISO(8, 14) },
        { type: 'solution', text: 'Solução registrada por Carlos Mendes', userId: 'u3', createdAt: Utils.daysAgoISO(7, 11) },
        { type: 'status', text: 'Status alterado de Em andamento para Concluído', userId: 'u3', createdAt: Utils.daysAgoISO(7, 11) }
      ])
    },
    {
      id: 'CH-1047',
      title: 'Lâmpadas queimadas na biblioteca',
      description: 'Quatro luminárias da área de leitura estão queimadas, deixando o ambiente escuro.',
      category: 'infraestrutura',
      priority: 'baixa',
      location: 'Biblioteca',
      status: 'aberto',
      requesterId: 'u1',
      assigneeId: null,
      diagnosis: '',
      solution: '',
      createdAt: Utils.hoursAgoISO(9),
      updatedAt: Utils.hoursAgoISO(9),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u1', createdAt: Utils.hoursAgoISO(9) }
      ])
    },
    {
      id: 'CH-1046',
      title: 'Vazamento no bebedouro do pátio',
      description: 'O bebedouro próximo à quadra está vazando continuamente e deixando o piso escorregadio.',
      category: 'infraestrutura',
      priority: 'alta',
      location: 'Pátio coberto',
      status: 'em_andamento',
      requesterId: 'u5',
      assigneeId: 'u6',
      diagnosis: 'Boia do reservatório desregulada. Peça de reposição solicitada.',
      solution: '',
      createdAt: Utils.daysAgoISO(1, 8),
      updatedAt: Utils.hoursAgoISO(9),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u5', createdAt: Utils.daysAgoISO(1, 8) },
        { type: 'assigned', text: 'Chamado atribuído a Rafael Lima', userId: 'u2', createdAt: Utils.daysAgoISO(1, 10) },
        { type: 'status', text: 'Status alterado de Aberto para Em andamento', userId: 'u6', createdAt: Utils.daysAgoISO(1, 11) },
        { type: 'diagnosis', text: 'Diagnóstico registrado por Rafael Lima', userId: 'u6', createdAt: Utils.hoursAgoISO(9) }
      ])
    },
    {
      id: 'CH-1045',
      title: 'Computador da secretaria lento',
      description: 'O computador utilizado para matrículas está muito lento, demorando para abrir os sistemas.',
      category: 'ti',
      priority: 'media',
      location: 'Secretaria',
      status: 'resolvido',
      requesterId: 'u2',
      assigneeId: 'u3',
      diagnosis: 'Disco com 98% de ocupação e excesso de programas em inicialização.',
      solution: 'Arquivos antigos arquivados, inicialização otimizada e disco liberado.',
      createdAt: Utils.daysAgoISO(3, 9),
      updatedAt: Utils.daysAgoISO(3, 14),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u2', createdAt: Utils.daysAgoISO(3, 9) },
        { type: 'assigned', text: 'Chamado atribuído a Carlos Mendes', userId: 'u2', createdAt: Utils.daysAgoISO(3, 10) },
        { type: 'diagnosis', text: 'Diagnóstico registrado por Carlos Mendes', userId: 'u3', createdAt: Utils.daysAgoISO(3, 12) },
        { type: 'solution', text: 'Solução registrada por Carlos Mendes', userId: 'u3', createdAt: Utils.daysAgoISO(3, 14) },
        { type: 'status', text: 'Status alterado de Em andamento para Resolvido', userId: 'u3', createdAt: Utils.daysAgoISO(3, 14) }
      ])
    },
    {
      id: 'CH-1044',
      title: 'Fechadura do portão lateral quebrada',
      description: 'A fechadura do portão lateral da escola não está travando, gerando preocupação com a segurança.',
      category: 'infraestrutura',
      priority: 'alta',
      location: 'Portão lateral',
      status: 'concluido',
      requesterId: 'u1',
      assigneeId: 'u6',
      diagnosis: 'Cilindro da fechadura quebrado.',
      solution: 'Fechadura substituída e portão testado pela equipe de segurança.',
      createdAt: Utils.daysAgoISO(6, 8),
      updatedAt: Utils.daysAgoISO(6, 16),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u1', createdAt: Utils.daysAgoISO(6, 8) },
        { type: 'assigned', text: 'Chamado atribuído a Rafael Lima', userId: 'u2', createdAt: Utils.daysAgoISO(6, 9) },
        { type: 'diagnosis', text: 'Diagnóstico registrado por Rafael Lima', userId: 'u6', createdAt: Utils.daysAgoISO(6, 13) },
        { type: 'solution', text: 'Solução registrada por Rafael Lima', userId: 'u6', createdAt: Utils.daysAgoISO(6, 16) },
        { type: 'status', text: 'Status alterado de Em andamento para Concluído', userId: 'u6', createdAt: Utils.daysAgoISO(6, 16) }
      ])
    },
    {
      id: 'CH-1043',
      title: 'Ar-condicionado fazendo ruído',
      description: 'O ar-condicionado da Sala 12 está com um ruído alto, atrapalhando a aula e a concentração dos alunos.',
      category: 'infraestrutura',
      priority: 'media',
      location: 'Sala 12',
      status: 'aberto',
      requesterId: 'u5',
      assigneeId: null,
      diagnosis: '',
      solution: '',
      createdAt: Utils.hoursAgoISO(5),
      updatedAt: Utils.hoursAgoISO(5),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u5', createdAt: Utils.hoursAgoISO(5) }
      ])
    },
    {
      id: 'CH-1042',
      title: 'Projetor não exibe imagem',
      description: 'O projetor liga, mas não exibe imagem do computador. A aula está sendo conduzida sem recursos audiovisuais.',
      category: 'ti',
      priority: 'alta',
      location: 'Laboratório 01',
      status: 'em_andamento',
      requesterId: 'u1',
      assigneeId: 'u3',
      diagnosis: 'Cabo HDMI danificado e porta de vídeo com mau contato.',
      solution: '',
      createdAt: Utils.daysAgoISO(2, 9),
      updatedAt: Utils.hoursAgoISO(3),
      history: buildHistory([
        { type: 'created', text: 'Chamado criado', userId: 'u1', createdAt: Utils.daysAgoISO(2, 9) },
        { type: 'assigned', text: 'Chamado atribuído a Carlos Mendes', userId: 'u2', createdAt: Utils.daysAgoISO(2, 10) },
        { type: 'status', text: 'Status alterado de Aberto para Em andamento', userId: 'u3', createdAt: Utils.daysAgoISO(2, 11) },
        { type: 'diagnosis', text: 'Diagnóstico registrado por Carlos Mendes', userId: 'u3', createdAt: Utils.hoursAgoISO(3) }
      ])
    }
  ];

  const seedMessages = [
    {
      id: 'm1',
      senderId: 'u3',
      recipientId: 'u1',
      text: 'Olá, Ana! Já identifiquei o problema do projetor do Laboratório 01. Estou aguardando a troca do cabo HDMI.',
      createdAt: Utils.hoursAgoISO(3),
      readAt: null
    },
    {
      id: 'm2',
      senderId: 'u1',
      recipientId: 'u3',
      text: 'Perfeito, Carlos. Obrigada pelo retorno! Consegue prever para hoje ainda?',
      createdAt: Utils.hoursAgoISO(2.7),
      readAt: Utils.hoursAgoISO(2.6)
    },
    {
      id: 'm3',
      senderId: 'u3',
      recipientId: 'u1',
      text: 'Sim, o novo cabo chega até às 15h. Assim que instalar, atualizo o chamado.',
      createdAt: Utils.hoursAgoISO(2.5),
      readAt: null
    },
    {
      id: 'm4',
      senderId: 'u2',
      recipientId: 'u3',
      text: 'Carlos, o chamado CH-1051 do Wi-Fi no Bloco B está com diagnóstico registrado. Podemos concluir hoje?',
      createdAt: Utils.hoursAgoISO(5.4),
      readAt: null
    },
    {
      id: 'm5',
      senderId: 'u3',
      recipientId: 'u2',
      text: 'Estou finalizando a configuração do access point. Até o fim da tarde finalizo o atendimento.',
      createdAt: Utils.hoursAgoISO(4.9),
      readAt: Utils.hoursAgoISO(4.7)
    },
    {
      id: 'm6',
      senderId: 'u6',
      recipientId: 'u2',
      text: 'Bom dia, Beatriz! A limpeza do auditório já foi iniciada. Previsão de conclusão em uma hora.',
      createdAt: Utils.hoursAgoISO(4.2),
      readAt: null
    },
    {
      id: 'm7',
      senderId: 'u5',
      recipientId: 'u1',
      text: 'Ana, você também notou o ruído do ar-condicionado da Sala 12? Abri um chamado.',
      createdAt: Utils.hoursAgoISO(4.8),
      readAt: null
    },
    {
      id: 'm8',
      senderId: 'u1',
      recipientId: 'u5',
      text: 'Sim, Marina! Na aula de ontem estava bem alto. Obrigada por registrar.',
      createdAt: Utils.hoursAgoISO(4.6),
      readAt: Utils.hoursAgoISO(4.5)
    },
    {
      id: 'm9',
      senderId: 'u2',
      recipientId: 'u4',
      text: 'Daniel, os relatórios do mês já estão atualizados no sistema. Podemos revisar amanhã?',
      createdAt: Utils.hoursAgoISO(7),
      readAt: null
    },
    {
      id: 'm10',
      senderId: 'u4',
      recipientId: 'u2',
      text: 'Combinado, Beatriz. Vou olhar os indicadores antes da reunião.',
      createdAt: Utils.hoursAgoISO(6.5),
      readAt: Utils.hoursAgoISO(6.2)
    },
    {
      id: 'm11',
      senderId: 'u6',
      recipientId: 'u5',
      text: 'Marina, o bebedouro do pátio está isolado para segurança até a troca da peça.',
      createdAt: Utils.hoursAgoISO(8.5),
      readAt: null
    },
    {
      id: 'm12',
      senderId: 'u1',
      recipientId: 'u2',
      text: 'Beatriz, os computadores da Sala 12 precisam de atenção antes da semana de provas.',
      createdAt: Utils.hoursAgoISO(1.2),
      readAt: null
    }
  ];

  // ---------- Acesso ao LocalStorage ----------

  function read(key, fallback) {
    try {
      const raw = window.localStorage.getItem(key);
      if (!raw) return fallback;
      return JSON.parse(raw);
    } catch (error) {
      console.warn(`HelpSchool: não foi possível ler "${key}".`, error);
      return fallback;
    }
  }

  function write(key, value) {
    try {
      window.localStorage.setItem(key, JSON.stringify(value));
      window.dispatchEvent(new CustomEvent('hs:storage', { detail: { key } }));
      return true;
    } catch (error) {
      console.warn(`HelpSchool: não foi possível salvar "${key}".`, error);
      return false;
    }
  }

  function seedAll() {
    write(KEYS.users, seedUsers);
    write(KEYS.tickets, seedTickets);
    write(KEYS.messages, seedMessages);
    try {
      window.localStorage.setItem(KEYS.version, DATA_VERSION);
    } catch (error) {
      console.warn('HelpSchool: versão de dados não pôde ser salva.', error);
    }
  }

  const Storage = {
    KEYS,
    initialize() {
      let version = null;
      try {
        version = window.localStorage.getItem(KEYS.version);
      } catch (error) {
        version = null;
      }
      const hasUsers = Array.isArray(read(KEYS.users, null));
      const hasTickets = Array.isArray(read(KEYS.tickets, null));
      const hasMessages = Array.isArray(read(KEYS.messages, null));
      if (!hasUsers || !hasTickets || !hasMessages || version !== DATA_VERSION) {
        seedAll();
      }
    },

    reset() {
      seedAll();
    },

    getUsers() {
      return read(KEYS.users, []) || [];
    },

    saveUsers(users) {
      return write(KEYS.users, users);
    },

    getTickets() {
      return read(KEYS.tickets, []) || [];
    },

    saveTickets(tickets) {
      return write(KEYS.tickets, tickets);
    },

    getMessages() {
      return read(KEYS.messages, []) || [];
    },

    saveMessages(messages) {
      return write(KEYS.messages, messages);
    },

    getSession() {
      const persistent = read(KEYS.session, null);
      if (persistent) return persistent;
      try {
        const temporary = window.sessionStorage.getItem(KEYS.session);
        return temporary ? JSON.parse(temporary) : null;
      } catch (error) {
        return null;
      }
    },

    saveSession(session, persistent) {
      if (persistent) {
        try {
          window.sessionStorage.removeItem(KEYS.session);
        } catch (error) { /* armazenamento temporário indisponível */ }
        return write(KEYS.session, session);
      }
      try {
        window.localStorage.removeItem(KEYS.session);
        window.sessionStorage.setItem(KEYS.session, JSON.stringify(session));
        return true;
      } catch (error) {
        return write(KEYS.session, session);
      }
    },

    clearSession() {
      try {
        window.localStorage.removeItem(KEYS.session);
        window.sessionStorage.removeItem(KEYS.session);
      } catch (error) {
        console.warn('HelpSchool: não foi possível encerrar a sessão.', error);
      }
      window.dispatchEvent(new CustomEvent('hs:storage', { detail: { key: KEYS.session } }));
    },

    nextTicketId() {
      const numbers = Storage.getTickets()
        .map((ticket) => parseInt(String(ticket.id).replace(/\D/g, ''), 10))
        .filter((number) => Number.isFinite(number));
      const last = numbers.length ? Math.max.apply(null, numbers) : 1041;
      return `CH-${last + 1}`;
    }
  };

  // ---------- API pública ----------

  HS.KEYS = KEYS;
  HS.ROLES = ROLES;
  HS.STATUS = STATUS;
  HS.PRIORITIES = PRIORITIES;
  HS.CATEGORIES = CATEGORIES;
  HS.Utils = Utils;
  HS.Storage = Storage;
})(window.HelpSchool = window.HelpSchool || {});

