/**
 * HelpSchool — Regras de negócio e interface dos chamados
 */
(function (HS) {
  'use strict';

  const { Storage, Utils, Auth, STATUS, PRIORITIES, CATEGORIES } = HS;

  const state = {
    initialized: false,
    filters: {
      search: '',
      status: 'all',
      priority: 'all',
      sort: 'recent'
    }
  };

  const HISTORY_ICONS = {
    created: 'i-plus',
    assigned: 'i-user',
    status: 'i-refresh',
    diagnosis: 'i-alert',
    solution: 'i-check-circle',
    system: 'i-activity'
  };

  function allTickets() {
    return Storage.getTickets();
  }

  function getVisibleTickets() {
    return Auth.scopeTickets(allTickets());
  }

  function getTicket(id) {
    return allTickets().find((ticket) => ticket.id === id) || null;
  }

  function setTicket(updatedTicket) {
    const tickets = allTickets();
    const index = tickets.findIndex((ticket) => ticket.id === updatedTicket.id);
    if (index < 0) return false;
    tickets[index] = updatedTicket;
    return Storage.saveTickets(tickets);
  }

  function addHistory(ticket, type, text, userId) {
    if (!Array.isArray(ticket.history)) ticket.history = [];
    ticket.history.push({
      id: Utils.uid('h'),
      type,
      text,
      userId: userId || 'system',
      createdAt: new Date().toISOString()
    });
  }

  function statusBadge(status) {
    const meta = STATUS[status] || { label: status, tone: 'open' };
    return `<span class="badge badge--${meta.tone}"><span class="badge__dot"></span>${Utils.escapeHTML(meta.label)}</span>`;
  }

  function priorityBadge(priority) {
    const meta = PRIORITIES[priority] || { label: priority, tone: 'low' };
    return `<span class="priority-badge priority-badge--${meta.tone}"><span></span>${Utils.escapeHTML(meta.label)}</span>`;
  }

  function categoryChip(category) {
    const meta = CATEGORIES[category] || { label: category, tone: 'slate' };
    return `<span class="category-chip category-chip--${meta.tone}"><svg><use href="#${Utils.categoryIcon(category)}"></use></svg>${Utils.escapeHTML(meta.label)}</span>`;
  }

  function avatar(user, extraClass) {
    if (!user) {
      return `<span class="avatar avatar--muted ${extraClass || ''}" aria-hidden="true">?</span>`;
    }
    return `<span class="avatar ${extraClass || ''}" data-color="${Utils.escapeHTML(user.color || 'blue')}" title="${Utils.escapeHTML(user.name)}">${Utils.escapeHTML(Utils.initials(user.name))}</span>`;
  }

  function requesterName(ticket) {
    const user = Auth.getUserById(ticket.requesterId);
    return user ? user.name : 'Usuário removido';
  }

  function assigneeName(ticket) {
    if (!ticket.assigneeId) return null;
    const user = Auth.getUserById(ticket.assigneeId);
    return user ? user.name : null;
  }

  function sortTickets(ticketList, sort) {
    const copy = ticketList.slice();
    switch (sort) {
      case 'oldest':
        return copy.sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
      case 'priority':
        return copy.sort((a, b) => {
          const diff = (PRIORITIES[b.priority] || { order: 0 }).order - (PRIORITIES[a.priority] || { order: 0 }).order;
          return diff || new Date(b.createdAt) - new Date(a.createdAt);
        });
      case 'title':
        return copy.sort((a, b) => String(a.title).localeCompare(String(b.title), 'pt-BR'));
      case 'recent':
      default:
        return copy.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
    }
  }

  function filterTickets(ticketList) {
    const search = Utils.normalizeText(state.filters.search);
    const status = state.filters.status;
    const priority = state.filters.priority;

    return ticketList.filter((ticket) => {
      if (status !== 'all' && ticket.status !== status) return false;
      if (priority !== 'all' && ticket.priority !== priority) return false;
      if (!search) return true;

      const haystack = Utils.normalizeText([
        ticket.id,
        ticket.title,
        ticket.description,
        ticket.location,
        Utils.categoryLabel(ticket.category),
        requesterName(ticket),
        assigneeName(ticket) || ''
      ].join(' '));

      return haystack.includes(search);
    });
  }

  function scopeLabel() {
    const user = Auth.getCurrentUser();
    if (!user) return '';
    if (user.role === 'professor') return 'Exibindo suas solicitações';
    if (user.role === 'tecnico') return 'Exibindo chamados atribuídos a você';
    return 'Exibindo todos os chamados da escola';
  }

  function renderEmptyState(filtered) {
    const hasFilters = Boolean(state.filters.search) ||
      state.filters.status !== 'all' ||
      state.filters.priority !== 'all';
    const canCreate = Auth.canCreateTickets();

    if (filtered) {
      return `
        <div class="empty-state">
          <span class="empty-state__icon"><svg><use href="#i-search"></use></svg></span>
          <h3>Nenhum chamado encontrado</h3>
          <p>Tente ajustar a pesquisa ou remover os filtros selecionados.</p>
          <button class="btn btn--outline btn--sm" type="button" id="empty-clear-filters">Limpar filtros</button>
        </div>`;
    }

    return `
      <div class="empty-state">
        <span class="empty-state__icon"><svg><use href="#i-ticket"></use></svg></span>
        <h3>Nenhum chamado por aqui</h3>
        <p>${hasFilters ? 'Não há chamados dentro dos filtros atuais.' : 'As solicitações de manutenção e suporte aparecerão aqui.'}</p>
        ${canCreate ? '<button class="btn btn--primary btn--sm" type="button" data-action="new-ticket"><svg><use href="#i-plus"></use></svg> Abrir chamado</button>' : ''}
      </div>`;
  }

  function renderTable(ticketList, filtered) {
    const wrap = document.getElementById('tickets-table-wrap');
    if (!wrap) return;

    if (!ticketList.length) {
      wrap.innerHTML = renderEmptyState(filtered);
      const emptyClear = document.getElementById('empty-clear-filters');
      if (emptyClear) emptyClear.addEventListener('click', clearFilters);
      return;
    }

    const rows = ticketList.map((ticket) => {
      const requester = Auth.getUserById(ticket.requesterId);
      return `
        <tr data-ticket-open="${Utils.escapeHTML(ticket.id)}" tabindex="0" aria-label="Abrir chamado ${Utils.escapeHTML(ticket.id)}: ${Utils.escapeHTML(ticket.title)}">
          <td data-label="ID"><span class="ticket-id">${Utils.escapeHTML(ticket.id)}</span></td>
          <td data-label="Chamado">
            <div class="ticket-main-cell">
              <span class="ticket-category-icon category-chip--${(CATEGORIES[ticket.category] || {}).tone || 'slate'}"><svg><use href="#${Utils.categoryIcon(ticket.category)}"></use></svg></span>
              <div>
                <strong>${Utils.escapeHTML(ticket.title)}</strong>
                <span>${Utils.escapeHTML(Utils.categoryLabel(ticket.category))} · ${Utils.escapeHTML(ticket.location)}</span>
              </div>
            </div>
          </td>
          <td data-label="Solicitante">
            <div class="requester-cell">
              ${avatar(requester, 'avatar--xs')}
              <span>${Utils.escapeHTML(requester ? requester.name : 'Usuário removido')}</span>
            </div>
          </td>
          <td data-label="Status">${statusBadge(ticket.status)}</td>
          <td data-label="Prioridade">${priorityBadge(ticket.priority)}</td>
          <td data-label="Data"><span class="date-cell">${Utils.formatDate(ticket.createdAt)}</span></td>
          <td class="ticket-row-action"><span class="row-arrow"><svg><use href="#i-arrow-right"></use></svg></span></td>
        </tr>`;
    }).join('');

    wrap.innerHTML = `
      <div class="table-wrap">
        <table class="data-table ticket-table">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">Chamado</th>
              <th scope="col">Solicitante</th>
              <th scope="col">Status</th>
              <th scope="col">Prioridade</th>
              <th scope="col">Data</th>
              <th scope="col"><span class="sr-only">Ações</span></th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>`;
  }

  function render() {
    const visible = getVisibleTickets();
    const filtered = filterTickets(visible);
    const sorted = sortTickets(filtered, state.filters.sort);
    renderTable(sorted, visible.length > 0);

    const count = document.getElementById('tickets-count');
    const scope = document.getElementById('tickets-scope');
    if (count) {
      count.textContent = `${Utils.pluralize(sorted.length, 'chamado encontrado', 'chamados encontrados')}`;
    }
    if (scope) scope.textContent = scopeLabel();

    return sorted;
  }

  function clearFilters() {
    state.filters = { search: '', status: 'all', priority: 'all', sort: 'recent' };

    const search = document.getElementById('ticket-search');
    const status = document.getElementById('filter-status');
    const priority = document.getElementById('filter-priority');
    const sort = document.getElementById('filter-sort');

    if (search) search.value = '';
    if (status) status.value = 'all';
    if (priority) priority.value = 'all';
    if (sort) sort.value = 'recent';

    render();
  }

  function resetFilters() {
    clearFilters();
  }

  function renderHistory(ticket) {
    const history = Array.isArray(ticket.history) ? ticket.history.slice() : [];
    history.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

    if (!history.length) {
      return '<p class="muted-text">Nenhuma movimentação registrada até o momento.</p>';
    }

    return history.map((entry) => {
      const actor = Auth.getUserById(entry.userId);
      const actorName = actor ? actor.name : 'Sistema';
      const icon = HISTORY_ICONS[entry.type] || HISTORY_ICONS.system;
      return `
        <div class="timeline-item">
          <span class="timeline-item__icon timeline-item__icon--${Utils.escapeHTML(entry.type || 'system')}"><svg><use href="#${icon}"></use></svg></span>
          <div class="timeline-item__body">
            <p>${Utils.escapeHTML(entry.text)}</p>
            <span>${Utils.escapeHTML(actorName)} · ${Utils.formatDateTime(entry.createdAt)}</span>
          </div>
        </div>`;
    }).join('');
  }

  function renderManagement(ticket) {
    if (!Auth.canAssignTickets()) return '';

    const technicians = Auth.getTechnicians();
    const assigneeValue = ticket.assigneeId || '';
    const statusOptions = Object.keys(STATUS).map((key) => {
      const meta = STATUS[key];
      return `<option value="${key}" ${ticket.status === key ? 'selected' : ''}>${meta.label}</option>`;
    }).join('');

    const technicianOptions = [
      `<option value="">Não atribuído</option>`,
      ...technicians.map((tech) => `<option value="${Utils.escapeHTML(tech.id)}" ${assigneeValue === tech.id ? 'selected' : ''}>${Utils.escapeHTML(tech.name)}</option>`)
    ].join('');

    return `
      <section class="detail-section">
        <div class="detail-section__heading">
          <span class="detail-section__icon detail-section__icon--blue"><svg><use href="#i-sliders"></use></svg></span>
          <div><h4>Gestão do chamado</h4><p>Defina o responsável e o status do atendimento.</p></div>
        </div>
        <form id="ticket-management-form" class="management-form">
          <div class="form-grid form-grid--inline">
            <div class="field">
              <label for="manage-assignee">Técnico responsável</label>
              <div class="select-wrap select-wrap--full">
                <svg aria-hidden="true"><use href="#i-user"></use></svg>
                <select id="manage-assignee" name="assigneeId">${technicianOptions}</select>
              </div>
            </div>
            <div class="field">
              <label for="manage-status">Status do chamado</label>
              <div class="select-wrap select-wrap--full">
                <svg aria-hidden="true"><use href="#i-refresh"></use></svg>
                <select id="manage-status" name="status">${statusOptions}</select>
              </div>
            </div>
          </div>
          <p class="form-error" role="alert" hidden></p>
          <div class="form-actions form-actions--right">
            <button class="btn btn--primary btn--sm" type="submit">
              <svg><use href="#i-check"></use></svg> Salvar alterações
            </button>
          </div>
        </form>
      </section>`;
  }

  function renderTechnicianPanel(ticket) {
    if (!Auth.canHandleTicket(ticket)) return '';

    return `
      <section class="detail-section detail-section--tech">
        <div class="detail-section__heading">
          <span class="detail-section__icon detail-section__icon--green"><svg><use href="#i-wrench"></use></svg></span>
          <div><h4>Atendimento técnico</h4><p>Registre o diagnóstico e a solução aplicada.</p></div>
        </div>
        <form id="ticket-technician-form" class="technician-form">
          <div class="field">
            <label for="tech-diagnosis">Diagnóstico</label>
            <textarea id="tech-diagnosis" name="diagnosis" rows="3" placeholder="Descreva a causa identificada do problema..." maxlength="1200">${Utils.escapeHTML(ticket.diagnosis || '')}</textarea>
          </div>
          <div class="field">
            <label for="tech-solution">Solução aplicada</label>
            <textarea id="tech-solution" name="solution" rows="3" placeholder="Descreva o que foi feito para resolver..." maxlength="1200">${Utils.escapeHTML(ticket.solution || '')}</textarea>
          </div>
          <p class="form-error" role="alert" hidden></p>
          <div class="form-actions form-actions--between">
            <button class="btn btn--outline btn--sm" type="submit" data-tech-action="save">
              <svg><use href="#i-check"></use></svg> Salvar atualizações
            </button>
            <button class="btn btn--success btn--sm" type="submit" data-tech-action="finish">
              <svg><use href="#i-check-circle"></use></svg> Finalizar atendimento
            </button>
          </div>
        </form>
      </section>`;
  }

  function renderDetailContent(ticket) {
    const requester = Auth.getUserById(ticket.requesterId);
    const assignee = ticket.assigneeId ? Auth.getUserById(ticket.assigneeId) : null;
    const canManage = Auth.canAssignTickets();
    const canHandle = Auth.canHandleTicket(ticket);

    return `
      <div class="ticket-detail">
        <div class="ticket-detail__top">
          <div class="ticket-detail__badges">
            ${statusBadge(ticket.status)}
            ${priorityBadge(ticket.priority)}
            ${categoryChip(ticket.category)}
          </div>
          <span class="ticket-detail__id">${Utils.escapeHTML(ticket.id)}</span>
        </div>

        <h3 class="ticket-detail__title">${Utils.escapeHTML(ticket.title)}</h3>
        <p class="ticket-detail__description">${Utils.escapeHTML(ticket.description)}</p>

        <div class="ticket-meta-grid">
          <div class="ticket-meta">
            <span class="ticket-meta__icon"><svg><use href="#i-user"></use></svg></span>
            <div><span>Solicitante</span><strong>${Utils.escapeHTML(requester ? requester.name : 'Usuário removido')}</strong></div>
          </div>
          <div class="ticket-meta">
            <span class="ticket-meta__icon"><svg><use href="#i-map-pin"></use></svg></span>
            <div><span>Localização</span><strong>${Utils.escapeHTML(ticket.location)}</strong></div>
          </div>
          <div class="ticket-meta">
            <span class="ticket-meta__icon"><svg><use href="#i-calendar"></use></svg></span>
            <div><span>Abertura</span><strong>${Utils.formatDateTime(ticket.createdAt)}</strong></div>
          </div>
          <div class="ticket-meta">
            <span class="ticket-meta__icon"><svg><use href="#i-wrench"></use></svg></span>
            <div><span>Responsável</span><strong>${Utils.escapeHTML(assignee ? assignee.name : 'Aguardando atribuição')}</strong></div>
          </div>
        </div>

        <div class="ticket-knowledge-grid">
          <div class="knowledge-card ${ticket.diagnosis ? '' : 'knowledge-card--empty'}">
            <div class="knowledge-card__header">
              <span class="knowledge-card__icon knowledge-card__icon--amber"><svg><use href="#i-alert"></use></svg></span>
              <div><h4>Diagnóstico</h4><span>${ticket.diagnosis ? 'Registrado pela equipe técnica' : 'Ainda não informado'}</span></div>
            </div>
            <p>${ticket.diagnosis ? Utils.escapeHTML(ticket.diagnosis) : 'Nenhum diagnóstico registrado até o momento.'}</p>
          </div>
          <div class="knowledge-card ${ticket.solution ? '' : 'knowledge-card--empty'}">
            <div class="knowledge-card__header">
              <span class="knowledge-card__icon knowledge-card__icon--green"><svg><use href="#i-check-circle"></use></svg></span>
              <div><h4>Solução aplicada</h4><span>${ticket.solution ? 'Resolução documentada' : 'Aguardando atendimento'}</span></div>
            </div>
            <p>${ticket.solution ? Utils.escapeHTML(ticket.solution) : 'A solução será exibida aqui após o atendimento.'}</p>
          </div>
        </div>

        ${canManage || canHandle ? '<div class="detail-actions-divider"><span>Ações do atendimento</span></div>' : ''}
        ${renderManagement(ticket)}
        ${renderTechnicianPanel(ticket)}

        <section class="detail-section detail-section--history">
          <div class="detail-section__heading">
            <span class="detail-section__icon detail-section__icon--slate"><svg><use href="#i-activity"></use></svg></span>
            <div><h4>Histórico de movimentações</h4><p>Registro automático de todas as ações deste chamado.</p></div>
          </div>
          <div class="timeline">${renderHistory(ticket)}</div>
        </section>
      </div>`;
  }

  function bindManagementForm(modal, ticket) {
    const form = modal.querySelector('#ticket-management-form');
    if (!form) return;

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const error = form.querySelector('.form-error');
      const assigneeId = form.querySelector('#manage-assignee').value;
      const status = form.querySelector('#manage-status').value;

      const result = updateManagement(ticket.id, { assigneeId, status });

      if (!result.ok) {
        if (error) {
          error.textContent = result.error;
          error.hidden = false;
        }
        return;
      }

      HS.App.closeModal();
      HS.App.toast('Chamado atualizado com sucesso.', 'success');
      HS.App.refreshAll();
      openDetail(ticket.id);
    });
  }

  function bindTechnicianForm(modal, ticket) {
    const form = modal.querySelector('#ticket-technician-form');
    if (!form) return;

    let finishIntent = false;

    form.querySelectorAll('[data-tech-action]').forEach((button) => {
      button.addEventListener('click', () => {
        finishIntent = button.getAttribute('data-tech-action') === 'finish';
      });
    });

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const error = form.querySelector('.form-error');
      const diagnosis = form.querySelector('#tech-diagnosis').value;
      const solution = form.querySelector('#tech-solution').value;

      const result = updateTechnician(ticket.id, diagnosis, solution, finishIntent);
      finishIntent = false;

      if (!result.ok) {
        if (error) {
          error.textContent = result.error;
          error.hidden = false;
        }
        return;
      }

      HS.App.closeModal();
      HS.App.toast(result.message, 'success');
      HS.App.refreshAll();
      openDetail(ticket.id);
    });
  }

  function openDetail(ticketId, options) {
    const ticket = getTicket(ticketId);
    if (!ticket) {
      HS.App.toast('Chamado não encontrado.', 'error');
      return;
    }

    if (!Auth.canViewTicket(ticket)) {
      HS.App.toast('Você não tem permissão para visualizar este chamado.', 'error');
      return;
    }

    const modal = HS.App.openModal({
      title: 'Detalhes do chamado',
      subtitle: 'Informações completas, atendimento e histórico',
      content: renderDetailContent(ticket),
      size: 'lg',
      onMount(modalElement) {
        bindManagementForm(modalElement, ticket);
        bindTechnicianForm(modalElement, ticket);
      }
    });

    if (options && options.focusSelector && modal) {
      window.setTimeout(() => {
        const target = modal.querySelector(options.focusSelector);
        if (target) target.focus();
      }, 80);
    }

    return modal;
  }

  function openNewModal(prefill) {
    if (!Auth.canCreateTickets()) {
      HS.App.toast('Faça login para abrir um chamado.', 'error');
      return;
    }

    const user = Auth.getCurrentUser();
    const highPriority = prefill && prefill.priority === 'alta';
    const categoryOptions = Object.keys(CATEGORIES).map((key) => {
      return `<option value="${key}">${CATEGORIES[key].label}</option>`;
    }).join('');

    const content = `
      <form id="new-ticket-form" class="ticket-form" novalidate>
        <div class="form-intro">
          <span class="form-intro__icon"><svg><use href="#i-ticket"></use></svg></span>
          <div>
            <strong>Nova solicitação</strong>
            <p>O chamado será associado a <b>${Utils.escapeHTML(user.name)}</b> e registrado no histórico da escola.</p>
          </div>
        </div>

        <div class="field">
          <label for="new-title">Título do chamado <span class="required-mark">*</span></label>
          <input type="text" id="new-title" name="title" maxlength="100" required placeholder="Ex.: Projetor do Laboratório 01 sem imagem" autocomplete="off">
          <span class="field__hint">Resuma o problema em uma frase objetiva.</span>
        </div>

        <div class="field">
          <label for="new-description">Descrição <span class="required-mark">*</span></label>
          <textarea id="new-description" name="description" rows="4" maxlength="1500" required placeholder="Descreva o problema, quando começou e o impacto nas atividades..."></textarea>
        </div>

        <div class="form-grid">
          <div class="field">
            <label for="new-category">Categoria <span class="required-mark">*</span></label>
            <div class="select-wrap select-wrap--full">
              <svg aria-hidden="true"><use href="#i-tag"></use></svg>
              <select id="new-category" name="category" required>${categoryOptions}</select>
            </div>
          </div>
          <div class="field">
            <label for="new-location">Localização <span class="required-mark">*</span></label>
            <div class="input-shell">
              <svg class="input-shell__icon" aria-hidden="true"><use href="#i-map-pin"></use></svg>
              <input type="text" id="new-location" name="location" maxlength="80" required placeholder="Ex.: Laboratório 01" autocomplete="off">
            </div>
          </div>
        </div>

        <fieldset class="field field--fieldset">
          <legend>Prioridade <span class="required-mark">*</span></legend>
          <div class="priority-options">
            <label class="priority-option">
              <input type="radio" name="priority" value="baixa">
              <span class="priority-option__content"><span class="priority-dot priority-dot--low"></span><strong>Baixa</strong><small>Pode aguardar</small></span>
            </label>
            <label class="priority-option">
              <input type="radio" name="priority" value="media" checked>
              <span class="priority-option__content"><span class="priority-dot priority-dot--medium"></span><strong>Média</strong><small>Atenção normal</small></span>
            </label>
            <label class="priority-option">
              <input type="radio" name="priority" value="alta" ${highPriority ? 'checked' : ''}>
              <span class="priority-option__content"><span class="priority-dot priority-dot--high"></span><strong>Alta</strong><small>Impacto imediato</small></span>
            </label>
          </div>
        </fieldset>

        <p class="form-error" role="alert" hidden></p>

        <div class="form-actions">
          <button class="btn btn--ghost" type="button" data-modal-close>Cancelar</button>
          <button class="btn btn--primary" type="submit">
            <svg><use href="#i-plus"></use></svg> Abrir chamado
          </button>
        </div>
      </form>`;

    const modal = HS.App.openModal({
      title: 'Abrir novo chamado',
      subtitle: 'Registre uma solicitação de manutenção ou suporte',
      content,
      size: 'md',
      onMount(modalElement) {
        const form = modalElement.querySelector('#new-ticket-form');
        if (!form) return;

        form.querySelector('#new-title').focus();

        form.addEventListener('submit', (event) => {
          event.preventDefault();
          const error = form.querySelector('.form-error');
          const title = form.querySelector('#new-title').value.trim();
          const description = form.querySelector('#new-description').value.trim();
          const category = form.querySelector('#new-category').value;
          const location = form.querySelector('#new-location').value.trim();
          const priorityInput = form.querySelector('input[name="priority"]:checked');
          const priority = priorityInput ? priorityInput.value : 'media';

          const invalidField = [];
          if (title.length < 5) invalidField.push('Informe um título com pelo menos 5 caracteres.');
          if (description.length < 15) invalidField.push('Descreva o problema com pelo menos 15 caracteres.');
          if (!CATEGORIES[category]) invalidField.push('Selecione uma categoria válida.');
          if (location.length < 2) invalidField.push('Informe a localização do problema.');

          if (invalidField.length) {
            if (error) {
              error.textContent = invalidField[0];
              error.hidden = false;
            }
            return;
          }

          const result = createTicket({
            title,
            description,
            category,
            location,
            priority
          });

          if (!result.ok) {
            if (error) {
              error.textContent = result.error;
              error.hidden = false;
            }
            return;
          }

          HS.App.closeModal();
          HS.App.toast(`Chamado ${result.ticket.id} aberto com sucesso.`, 'success');
          HS.App.navigate('tickets');
          HS.App.refreshAll();
          openDetail(result.ticket.id);
        });
      }
    });

    return modal;
  }

  function createTicket(payload) {
    const user = Auth.getCurrentUser();
    if (!user) return { ok: false, error: 'Sessão expirada. Entre novamente.' };
    if (!Auth.canCreateTickets()) return { ok: false, error: 'Você não tem permissão para abrir chamados.' };

    const now = new Date().toISOString();
    const ticket = {
      id: Storage.nextTicketId(),
      title: payload.title.trim(),
      description: payload.description.trim(),
      category: payload.category,
      priority: payload.priority,
      location: payload.location.trim(),
      status: 'aberto',
      requesterId: user.id,
      assigneeId: null,
      diagnosis: '',
      solution: '',
      createdAt: now,
      updatedAt: now,
      history: []
    };

    addHistory(ticket, 'created', 'Chamado criado', user.id);

    const tickets = allTickets();
    tickets.unshift(ticket);

    if (!Storage.saveTickets(tickets)) {
      return { ok: false, error: 'Não foi possível salvar o chamado. Verifique o armazenamento local.' };
    }

    return { ok: true, ticket };
  }

  function updateManagement(ticketId, payload) {
    if (!Auth.canAssignTickets()) {
      return { ok: false, error: 'Você não tem permissão para gerenciar este chamado.' };
    }

    const tickets = allTickets();
    const index = tickets.findIndex((ticket) => ticket.id === ticketId);
    if (index < 0) return { ok: false, error: 'Chamado não encontrado.' };

    const ticket = tickets[index];
    const user = Auth.getCurrentUser();
    const newStatus = payload.status;
    const newAssigneeId = payload.assigneeId || null;

    if (!STATUS[newStatus]) {
      return { ok: false, error: 'Selecione um status válido.' };
    }

    if (newAssigneeId) {
      const technician = Auth.getUserById(newAssigneeId);
      if (!technician || technician.role !== 'tecnico' || !technician.active) {
        return { ok: false, error: 'Selecione um técnico ativo.' };
      }
    }

    if (['em_andamento', 'resolvido', 'concluido'].includes(newStatus) && !newAssigneeId) {
      return { ok: false, error: 'Atribua um técnico antes de iniciar ou finalizar o atendimento.' };
    }

    let changed = false;

    if (newAssigneeId !== (ticket.assigneeId || null)) {
      const previousName = ticket.assigneeId ? (assigneeName(ticket) || 'técnico anterior') : null;
      const technician = newAssigneeId ? Auth.getUserById(newAssigneeId) : null;
      ticket.assigneeId = newAssigneeId;
      if (technician) {
        addHistory(ticket, 'assigned', `Chamado atribuído a ${technician.name}`, user.id);
      } else {
        addHistory(ticket, 'assigned', `Responsável removido${previousName ? ` (${previousName})` : ''}`, user.id);
      }
      changed = true;
    }

    if (newStatus !== ticket.status) {
      const from = Utils.statusLabel(ticket.status);
      const to = Utils.statusLabel(newStatus);
      ticket.status = newStatus;
      addHistory(ticket, 'status', `Status alterado de ${from} para ${to}`, user.id);
      changed = true;
    }

    if (!changed) {
      return { ok: false, error: 'Nenhuma alteração foi identificada.' };
    }

    ticket.updatedAt = new Date().toISOString();
    tickets[index] = ticket;

    if (!Storage.saveTickets(tickets)) {
      return { ok: false, error: 'Não foi possível salvar as alterações.' };
    }

    return { ok: true, ticket };
  }

  function updateTechnician(ticketId, diagnosis, solution, finish) {
    const tickets = allTickets();
    const index = tickets.findIndex((ticket) => ticket.id === ticketId);
    if (index < 0) return { ok: false, error: 'Chamado não encontrado.' };

    const ticket = tickets[index];
    if (!Auth.canHandleTicket(ticket)) {
      return { ok: false, error: 'Você não tem permissão para atender este chamado.' };
    }

    const cleanDiagnosis = String(diagnosis || '').trim();
    const cleanSolution = String(solution || '').trim();
    const hadDiagnosis = Boolean(String(ticket.diagnosis || '').trim());
    const hadSolution = Boolean(String(ticket.solution || '').trim());
    const user = Auth.getCurrentUser();

    if (finish && cleanSolution.length < 10) {
      return { ok: false, error: 'Descreva a solução aplicada antes de finalizar o atendimento.' };
    }

    if (cleanDiagnosis !== String(ticket.diagnosis || '').trim()) {
      ticket.diagnosis = cleanDiagnosis;
      if (cleanDiagnosis) {
        addHistory(ticket, 'diagnosis', `Diagnóstico ${hadDiagnosis ? 'atualizado' : 'registrado'} por ${user.name}`, user.id);
      }
    }

    if (cleanSolution !== String(ticket.solution || '').trim()) {
      ticket.solution = cleanSolution;
      if (cleanSolution) {
        addHistory(ticket, 'solution', `Solução ${hadSolution ? 'atualizada' : 'registrada'} por ${user.name}`, user.id);
      }
    }

    if (finish) {
      if (ticket.status !== 'concluido') {
        const from = Utils.statusLabel(ticket.status);
        ticket.status = 'concluido';
        addHistory(ticket, 'status', `Status alterado de ${from} para Concluído`, user.id);
      }
    } else if (ticket.status === 'aberto' && cleanDiagnosis) {
      ticket.status = 'em_andamento';
      addHistory(ticket, 'status', 'Status alterado de Aberto para Em andamento', user.id);
    }

    ticket.updatedAt = new Date().toISOString();
    tickets[index] = ticket;

    if (!Storage.saveTickets(tickets)) {
      return { ok: false, error: 'Não foi possível salvar as informações.' };
    }

    return {
      ok: true,
      ticket,
      message: finish ? 'Atendimento finalizado com sucesso.' : 'Atendimento atualizado com sucesso.'
    };
  }

  function getRecurring(ticketList, limit) {
    const map = new Map();

    (ticketList || getVisibleTickets()).forEach((ticket) => {
      const label = String(ticket.location || '').trim();
      if (!label) return;
      const key = Utils.normalizeText(label);
      if (!map.has(key)) {
        map.set(key, { label, count: 0, open: 0 });
      }
      const item = map.get(key);
      item.count += 1;
      if (ticket.status === 'aberto' || ticket.status === 'em_andamento') item.open += 1;
    });

    return Array.from(map.values())
      .sort((a, b) => b.count - a.count || a.label.localeCompare(b.label, 'pt-BR'))
      .slice(0, limit || 5);
  }

  function init() {
    if (state.initialized) return;
    state.initialized = true;

    const search = document.getElementById('ticket-search');
    const status = document.getElementById('filter-status');
    const priority = document.getElementById('filter-priority');
    const sort = document.getElementById('filter-sort');
    const clear = document.getElementById('clear-filters');
    const wrap = document.getElementById('tickets-table-wrap');

    if (search) {
      search.addEventListener('input', Utils.debounce(() => {
        state.filters.search = search.value;
        render();
      }, 180));
    }

    if (status) {
      status.addEventListener('change', () => {
        state.filters.status = status.value;
        render();
      });
    }

    if (priority) {
      priority.addEventListener('change', () => {
        state.filters.priority = priority.value;
        render();
      });
    }

    if (sort) {
      sort.addEventListener('change', () => {
        state.filters.sort = sort.value;
        render();
      });
    }

    if (clear) clear.addEventListener('click', clearFilters);

    if (wrap) {
      wrap.addEventListener('click', (event) => {
        const row = event.target.closest('[data-ticket-open]');
        if (row) openDetail(row.getAttribute('data-ticket-open'));
      });

      wrap.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const row = event.target.closest('tr[data-ticket-open]');
        if (!row) return;
        event.preventDefault();
        openDetail(row.getAttribute('data-ticket-open'));
      });
    }
  }

  HS.Tickets = {
    init,
    render,
    resetFilters,
    getTicket,
    getVisibleTickets,
    getRecurring,
    statusBadge,
    priorityBadge,
    categoryChip,
    avatar,
    openDetail,
    openNewModal,
    createTicket,
    updateManagement,
    updateTechnician,
    scopeLabel
  };
})(window.HelpSchool = window.HelpSchool || {});
