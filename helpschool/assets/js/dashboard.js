/**
 * HelpSchool — Indicadores, gráficos e relatórios
 */
(function (HS) {
  'use strict';

  const { Storage, Utils, Auth, Tickets, STATUS, PRIORITIES, CATEGORIES } = HS;
  const state = { initialized: false };

  const STATUS_COLORS = {
    aberto: '#F59E0B',
    em_andamento: '#2563EB',
    resolvido: '#14B8A6',
    concluido: '#10B981'
  };

  const PRIORITY_COLORS = {
    baixa: '#94A3B8',
    media: '#F59E0B',
    alta: '#EF4444'
  };

  const TECH_COLORS = ['#2563EB', '#10B981', '#8B5CF6', '#F59E0B', '#0EA5E9', '#EC4899'];

  function visibleTickets() {
    return Auth.scopeTickets(Storage.getTickets());
  }

  function countByStatus(ticketList) {
    const counts = { aberto: 0, em_andamento: 0, resolvido: 0, concluido: 0 };
    ticketList.forEach((ticket) => {
      if (Object.prototype.hasOwnProperty.call(counts, ticket.status)) {
        counts[ticket.status] += 1;
      }
    });
    return counts;
  }

  function countBy(ticketList, key) {
    return ticketList.reduce((accumulator, ticket) => {
      const value = ticket[key] || 'outros';
      accumulator[value] = (accumulator[value] || 0) + 1;
      return accumulator;
    }, {});
  }

  function capitalize(text) {
    return text ? text.charAt(0).toUpperCase() + text.slice(1) : text;
  }

  function greeting() {
    const hour = new Date().getHours();
    if (hour < 12) return 'Bom dia';
    if (hour < 18) return 'Boa tarde';
    return 'Boa noite';
  }

  function renderStats(ticketList) {
    const container = document.getElementById('stats-grid');
    if (!container) return;

    const counts = countByStatus(ticketList);
    const total = ticketList.length;
    const lastWeek = ticketList.filter((ticket) => {
      return Date.now() - new Date(ticket.createdAt).getTime() <= 7 * 24 * 3600000;
    }).length;
    const highOpen = ticketList.filter(
      (ticket) => ticket.priority === 'alta' && (ticket.status === 'aberto' || ticket.status === 'em_andamento')
    ).length;
    const completed = counts.concluido;
    const completionRate = Utils.percentage(completed, total);

    const cards = [
      {
        label: 'Total de chamados',
        value: total,
        icon: 'i-ticket',
        tone: 'blue',
        meta: `${lastWeek} ${lastWeek === 1 ? 'novo' : 'novos'} nos últimos 7 dias`
      },
      {
        label: 'Chamados abertos',
        value: counts.aberto,
        icon: 'i-clock',
        tone: 'amber',
        meta: highOpen ? `${highOpen} de alta prioridade em andamento` : 'Nenhuma urgência pendente'
      },
      {
        label: 'Em andamento',
        value: counts.em_andamento,
        icon: 'i-wrench',
        tone: 'indigo',
        meta: `${counts.resolvido} ${counts.resolvido === 1 ? 'resolvido aguardando' : 'resolvidos aguardando'} conclusão`
      },
      {
        label: 'Concluídos',
        value: completed,
        icon: 'i-check-circle',
        tone: 'green',
        meta: `${completionRate}% do total de chamados`
      }
    ];

    container.innerHTML = cards.map((card, index) => `
      <article class="stat-card stat-card--${card.tone}" style="--delay:${index * 45}ms">
        <div class="stat-card__top">
          <span class="stat-card__icon"><svg><use href="#${card.icon}"></use></svg></span>
          <span class="stat-card__trend">${index === 3 ? 'Taxa de conclusão' : 'Visão atual'}</span>
        </div>
        <strong class="stat-card__value">${card.value}</strong>
        <span class="stat-card__label">${card.label}</span>
        <p class="stat-card__meta">${card.meta}</p>
      </article>`).join('');
  }

  function renderStatusChart(ticketList) {
    const container = document.getElementById('status-chart');
    const totalElement = document.getElementById('status-chart-total');
    if (!container) return;

    const counts = countByStatus(ticketList);
    const total = ticketList.length;

    if (totalElement) {
      totalElement.textContent = Utils.pluralize(total, 'chamado', 'chamados');
    }

    if (!total) {
      container.innerHTML = `
        <div class="chart-empty">
          <span><svg><use href="#i-chart"></use></svg></span>
          <p>Nenhum chamado para exibir no gráfico.</p>
        </div>`;
      return;
    }

    let cursor = 0;
    const segments = Object.keys(STATUS).map((key) => {
      const percentage = (counts[key] / total) * 100;
      const start = cursor;
      cursor += percentage;
      return `${STATUS_COLORS[key]} ${start}% ${cursor}%`;
    }).join(', ');

    const legend = Object.keys(STATUS).map((key) => {
      const meta = STATUS[key];
      const count = counts[key];
      const percentage = Utils.percentage(count, total);
      return `
        <div class="chart-legend__item">
          <span class="chart-legend__dot" style="background:${STATUS_COLORS[key]}"></span>
          <span class="chart-legend__label">${meta.label}</span>
          <strong>${count}</strong>
          <span class="chart-legend__percent">${percentage}%</span>
        </div>`;
    }).join('');

    container.innerHTML = `
      <div class="status-chart__body">
        <div class="donut" style="--donut: conic-gradient(${segments})" role="img" aria-label="Gráfico de chamados por status">
          <div class="donut__center">
            <strong>${total}</strong>
            <span>chamados</span>
          </div>
        </div>
        <div class="chart-legend">${legend}</div>
      </div>`;
  }

  function renderRecurringList(containerId, ticketList, limit) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const items = Tickets.getRecurring(ticketList, limit || 5);

    if (!items.length) {
      container.innerHTML = `
        <div class="chart-empty chart-empty--compact">
          <span><svg><use href="#i-activity"></use></svg></span>
          <p>Ainda não há ocorrências suficientes.</p>
        </div>`;
      return;
    }

    const max = items[0].count;
    container.innerHTML = items.map((item, index) => {
      const width = Math.max(8, Math.round((item.count / max) * 100));
      return `
        <div class="recurring-item">
          <span class="recurring-item__rank ${index === 0 ? 'is-top' : ''}">${index + 1}</span>
          <div class="recurring-item__body">
            <div class="recurring-item__top">
              <strong>${Utils.escapeHTML(item.label)}</strong>
              <span>${Utils.pluralize(item.count, 'ocorrência', 'ocorrências')}</span>
            </div>
            <div class="recurring-item__track"><span style="width:${width}%"></span></div>
            <small>${item.open ? `${item.open} em aberto no momento` : 'Todas as ocorrências atendidas'}</small>
          </div>
        </div>`;
    }).join('');
  }

  function renderRecentTickets(ticketList) {
    const container = document.getElementById('recent-tickets');
    if (!container) return;

    const recent = ticketList.slice()
      .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
      .slice(0, 5);

    if (!recent.length) {
      container.innerHTML = `
        <div class="empty-state empty-state--compact">
          <span class="empty-state__icon"><svg><use href="#i-ticket"></use></svg></span>
          <h3>Nenhum chamado registrado</h3>
          <p>As solicitações recentes aparecerão neste painel.</p>
        </div>`;
      return;
    }

    container.innerHTML = recent.map((ticket) => {
      const requester = Auth.getUserById(ticket.requesterId);
      return `
        <button class="recent-item" type="button" data-ticket-open="${Utils.escapeHTML(ticket.id)}">
          <span class="ticket-category-icon category-chip--${(CATEGORIES[ticket.category] || {}).tone || 'slate'}"><svg><use href="#${Utils.categoryIcon(ticket.category)}"></use></svg></span>
          <span class="recent-item__main">
            <span class="recent-item__title">${Utils.escapeHTML(ticket.title)}</span>
            <span class="recent-item__meta">${Utils.escapeHTML(ticket.id)} · ${Utils.escapeHTML(requester ? Utils.firstName(requester.name) : 'Usuário')} · ${Utils.formatDate(ticket.createdAt)}</span>
          </span>
          ${Tickets.statusBadge(ticket.status)}
          <span class="recent-item__arrow"><svg><use href="#i-arrow-right"></use></svg></span>
        </button>`;
    }).join('');
  }

  function renderRecentActivity(ticketList) {
    const container = document.getElementById('recent-activity');
    if (!container) return;

    const activity = [];
    ticketList.forEach((ticket) => {
      (ticket.history || []).forEach((entry) => {
        activity.push({
          id: entry.id,
          text: entry.text,
          type: entry.type,
          createdAt: entry.createdAt,
          userId: entry.userId,
          ticketId: ticket.id,
          ticketTitle: ticket.title
        });
      });
    });

    activity.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
    const recent = activity.slice(0, 6);

    if (!recent.length) {
      container.innerHTML = `
        <div class="empty-state empty-state--compact">
          <span class="empty-state__icon"><svg><use href="#i-activity"></use></svg></span>
          <h3>Sem movimentações</h3>
          <p>As ações realizadas nos chamados aparecerão aqui.</p>
        </div>`;
      return;
    }

    container.innerHTML = recent.map((item) => {
      const actor = Auth.getUserById(item.userId);
      return `
        <div class="activity-item">
          <span class="activity-item__dot"></span>
          <div class="activity-item__body">
            <p>${Utils.escapeHTML(item.text)}</p>
            <span>${Utils.escapeHTML(actor ? Utils.firstName(actor.name) : 'Sistema')} · ${Utils.escapeHTML(item.ticketId)} · ${Utils.timeAgo(item.createdAt)}</span>
          </div>
        </div>`;
    }).join('');
  }

  function renderDashboard() {
    const user = Auth.getCurrentUser();
    if (!user) return;

    const ticketList = visibleTickets();
    const greetingElement = document.getElementById('dashboard-greeting');
    const dateElement = document.getElementById('dashboard-date');
    const summaryElement = document.getElementById('dashboard-summary');

    if (greetingElement) {
      greetingElement.textContent = `${greeting()}, ${Utils.firstName(user.name)} 👋`;
    }

    if (dateElement) {
      const dateText = new Intl.DateTimeFormat('pt-BR', {
        weekday: 'long',
        day: '2-digit',
        month: 'long'
      }).format(new Date());
      dateElement.textContent = capitalize(dateText);
    }

    if (summaryElement) {
      if (user.role === 'professor') {
        summaryElement.textContent = 'Acompanhe o andamento das suas solicitações de suporte e manutenção.';
      } else if (user.role === 'tecnico') {
        summaryElement.textContent = 'Veja os chamados atribuídos a você e mantenha os atendimentos em dia.';
      } else {
        summaryElement.textContent = 'Aqui está o panorama completo dos chamados da escola.';
      }
    }

    renderStats(ticketList);
    renderStatusChart(ticketList);
    renderRecurringList('recurring-list', ticketList, 5);
    renderRecentTickets(ticketList);
    renderRecentActivity(ticketList);
  }

  function renderBars(containerId, items, options) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (!items.length) {
      container.innerHTML = `
        <div class="chart-empty chart-empty--compact">
          <span><svg><use href="#i-chart"></use></svg></span>
          <p>Sem dados suficientes para este indicador.</p>
        </div>`;
      return;
    }

    const max = Math.max.apply(null, items.map((item) => item.count)) || 1;

    container.innerHTML = items.map((item, index) => {
      const width = item.count ? Math.max(6, Math.round((item.count / max) * 100)) : 0;
      const color = item.color || TECH_COLORS[index % TECH_COLORS.length];
      return `
        <div class="bar-row">
          <div class="bar-row__label">
            ${item.prefix || `<span class="bar-row__dot" style="background:${color}"></span>`}
            <span>${Utils.escapeHTML(item.label)}</span>
          </div>
          <div class="bar-row__track" aria-hidden="true">
            <span class="bar-row__fill" style="width:${width}%;background:${color}"></span>
          </div>
          <span class="bar-row__value">${item.count}<small>${Utils.percentage(item.count, options.total)}%</small></span>
        </div>`;
    }).join('');
  }

  function renderReportSummary(ticketList) {
    const container = document.getElementById('report-summary');
    if (!container) return;

    const total = ticketList.length;
    const counts = countByStatus(ticketList);
    const categoryCounts = countBy(ticketList, 'category');
    const categories = Object.keys(CATEGORIES).map((key) => ({
      key,
      label: CATEGORIES[key].label,
      count: categoryCounts[key] || 0
    })).sort((a, b) => b.count - a.count);

    const topCategory = categories[0] && categories[0].count ? categories[0] : null;
    const recurring = Tickets.getRecurring(ticketList, 1)[0] || null;
    const finalizationRate = Utils.percentage(counts.concluido + counts.resolvido, total);

    const cards = [
      {
        label: 'Total de chamados',
        value: total,
        meta: 'Base para todos os indicadores',
        icon: 'i-ticket',
        tone: 'blue'
      },
      {
        label: 'Taxa de resolução',
        value: `${finalizationRate}%`,
        meta: `${counts.resolvido + counts.concluido} chamados resolvidos`,
        icon: 'i-check-circle',
        tone: 'green'
      },
      {
        label: 'Categoria principal',
        value: topCategory ? topCategory.label : '—',
        meta: topCategory ? Utils.pluralize(topCategory.count, 'ocorrência', 'ocorrências') : 'Sem dados no período',
        icon: 'i-tag',
        tone: 'purple',
        small: Boolean(topCategory)
      },
      {
        label: 'Local mais recorrente',
        value: recurring ? recurring.label : '—',
        meta: recurring ? Utils.pluralize(recurring.count, 'chamado registrado', 'chamados registrados') : 'Sem recorrências',
        icon: 'i-activity',
        tone: 'amber',
        small: Boolean(recurring)
      }
    ];

    container.innerHTML = cards.map((card) => `
      <article class="report-card report-card--${card.tone}">
        <span class="report-card__icon"><svg><use href="#${card.icon}"></use></svg></span>
        <div>
          <span>${card.label}</span>
          <strong class="${card.small ? 'report-card__value--small' : ''}">${Utils.escapeHTML(String(card.value))}</strong>
          <p>${Utils.escapeHTML(card.meta)}</p>
        </div>
      </article>`).join('');
  }

  function renderReports() {
    if (!Auth.canViewReports()) return;

    const ticketList = visibleTickets();
    const total = ticketList.length;
    renderReportSummary(ticketList);

    const categoryCounts = countBy(ticketList, 'category');
    const categoryItems = Object.keys(CATEGORIES).map((key) => ({
      label: CATEGORIES[key].label,
      count: categoryCounts[key] || 0,
      color: TECH_COLORS[Object.keys(CATEGORIES).indexOf(key) % TECH_COLORS.length],
      prefix: `<span class="bar-icon bar-icon--${CATEGORIES[key].tone}"><svg><use href="#${Utils.categoryIcon(key)}"></use></svg></span>`
    })).filter((item) => item.count > 0).sort((a, b) => b.count - a.count);

    renderBars('category-chart', categoryItems, { total });

    const priorityCounts = countBy(ticketList, 'priority');
    const priorityItems = Object.keys(PRIORITIES).map((key) => ({
      label: PRIORITIES[key].label,
      count: priorityCounts[key] || 0,
      color: PRIORITY_COLORS[key],
      prefix: `<span class="bar-row__dot" style="background:${PRIORITY_COLORS[key]}"></span>`
    })).filter((item) => item.count > 0).sort((a, b) => b.count - a.count);

    renderBars('priority-chart', priorityItems, { total });

    const techs = Auth.getActiveUsers().filter((user) => user.role === 'tecnico');
    const techItems = techs.map((tech, index) => {
      const count = ticketList.filter((ticket) => ticket.assigneeId === tech.id).length;
      return {
        label: tech.name,
        count,
        color: TECH_COLORS[index % TECH_COLORS.length],
        prefix: `<span class="avatar avatar--xs" data-color="${tech.color || 'blue'}">${Utils.escapeHTML(Utils.initials(tech.name))}</span>`
      };
    });

    const unassigned = ticketList.filter((ticket) => !ticket.assigneeId).length;
    if (unassigned) {
      techItems.push({
        label: 'Sem responsável',
        count: unassigned,
        color: '#94A3B8',
        prefix: '<span class="bar-row__dot" style="background:#94A3B8"></span>'
      });
    }

    techItems.sort((a, b) => b.count - a.count || a.label.localeCompare(b.label, 'pt-BR'));
    renderBars('technician-chart', techItems.filter((item) => item.count > 0), { total });
    renderRecurringList('report-recurring', ticketList, 6);
  }

  function renderAll() {
    renderDashboard();
    renderReports();
  }

  function csvCell(value) {
    const text = String(value == null ? '' : value).replace(/"/g, '""');
    return `"${text}"`;
  }

  function exportCSV() {
    if (!Auth.canViewReports()) {
      HS.App.toast('Você não tem permissão para exportar relatórios.', 'error');
      return;
    }

    const ticketList = visibleTickets()
      .slice()
      .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

    const header = [
      'ID', 'Título', 'Descrição', 'Categoria', 'Prioridade', 'Localização',
      'Status', 'Solicitante', 'Responsável', 'Data de abertura', 'Diagnóstico', 'Solução aplicada'
    ];

    const rows = ticketList.map((ticket) => {
      const requester = Auth.getUserById(ticket.requesterId);
      const assignee = ticket.assigneeId ? Auth.getUserById(ticket.assigneeId) : null;
      return [
        ticket.id,
        ticket.title,
        ticket.description,
        Utils.categoryLabel(ticket.category),
        Utils.priorityLabel(ticket.priority),
        ticket.location,
        Utils.statusLabel(ticket.status),
        requester ? requester.name : 'Usuário removido',
        assignee ? assignee.name : 'Não atribuído',
        Utils.formatDateTime(ticket.createdAt),
        ticket.diagnosis || '',
        ticket.solution || ''
      ].map(csvCell).join(';');
    });

    const csv = `\uFEFF${[header.map(csvCell).join(';')].concat(rows).join('\r\n')}`;
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    const today = new Date().toISOString().slice(0, 10);

    link.href = url;
    link.download = `helpschool-chamados-${today}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);

    HS.App.toast(`${Utils.pluralize(ticketList.length, 'chamado exportado', 'chamados exportados')} para CSV.`, 'success');
  }

  function init() {
    if (state.initialized) return;
    state.initialized = true;

    const recent = document.getElementById('recent-tickets');
    if (recent) {
      recent.addEventListener('click', (event) => {
        const button = event.target.closest('[data-ticket-open]');
        if (!button) return;
        Tickets.openDetail(button.getAttribute('data-ticket-open'));
      });
    }
  }

  HS.Dashboard = {
    init,
    renderDashboard,
    renderReports,
    renderAll,
    exportCSV
  };
})(window.HelpSchool = window.HelpSchool || {});
