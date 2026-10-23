/**
 * HelpSchool — Orquestração da SPA, navegação, modais e gestão de usuários
 */
(function (HS) {
  'use strict';

  const { Storage, Utils, Auth, Tickets, Chat, Dashboard, ROLES } = HS;

  const VIEW_META = {
    dashboard: { title: 'Visão geral', eyebrow: 'Painel', documentTitle: 'Visão geral' },
    tickets: { title: 'Chamados', eyebrow: 'Central de atendimento', documentTitle: 'Chamados' },
    chat: { title: 'Mensagens', eyebrow: 'Comunicação interna', documentTitle: 'Mensagens' },
    reports: { title: 'Relatórios', eyebrow: 'Inteligência operacional', documentTitle: 'Relatórios' },
    users: { title: 'Usuários', eyebrow: 'Administração', documentTitle: 'Usuários' }
  };

  const state = {
    initialized: false,
    currentView: 'dashboard',
    currentUser: null,
    modal: null,
    modalLayer: null,
    lastFocused: null,
    userFilters: { search: '', role: 'all' }
  };

  function icon(name) {
    return `<svg aria-hidden="true"><use href="#${name}"></use></svg>`;
  }

  function canAccessView(view) {
    if (!Auth.getCurrentUser()) return false;
    if (view === 'reports') return Auth.canViewReports();
    if (view === 'users') return Auth.canManageUsers();
    return true;
  }

  function closeUserMenu() {
    const menu = document.getElementById('user-menu');
    const button = document.getElementById('user-menu-btn');
    if (menu) menu.hidden = true;
    if (button) button.setAttribute('aria-expanded', 'false');
  }

  function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.remove('is-open');
    if (overlay) overlay.classList.remove('is-visible');
    document.body.classList.remove('sidebar-open');
  }

  function openSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.add('is-open');
    if (overlay) overlay.classList.add('is-visible');
    document.body.classList.add('sidebar-open');
    const closeButton = document.getElementById('sidebar-close');
    if (closeButton) window.setTimeout(() => closeButton.focus(), 100);
  }

  function updateNavigation(view) {
    document.querySelectorAll('.nav-item[data-view]').forEach((item) => {
      const isActive = item.getAttribute('data-view') === view;
      item.classList.toggle('is-active', isActive);
      if (isActive) item.setAttribute('aria-current', 'page');
      else item.removeAttribute('aria-current');
    });

    document.querySelectorAll('.mobile-nav__item[data-view]').forEach((item) => {
      const isActive = item.getAttribute('data-view') === view;
      item.classList.toggle('is-active', isActive);
      if (isActive) item.setAttribute('aria-current', 'page');
      else item.removeAttribute('aria-current');
    });
  }

  function navigate(view, options) {
    if (!Auth.getCurrentUser()) return;
    if (!VIEW_META[view]) view = 'dashboard';
    if (!canAccessView(view)) view = 'dashboard';

    state.currentView = view;

    document.querySelectorAll('[data-view-panel]').forEach((panel) => {
      const isActive = panel.getAttribute('data-view-panel') === view;
      panel.hidden = !isActive;
      panel.classList.toggle('is-active', isActive);
    });

    const meta = VIEW_META[view];
    const title = document.getElementById('page-title');
    const eyebrow = document.getElementById('page-eyebrow');

    if (title) title.textContent = meta.title;
    if (eyebrow) eyebrow.textContent = meta.eyebrow;
    document.title = `${meta.documentTitle} — HelpSchool`;

    updateNavigation(view);
    closeSidebar();
    closeUserMenu();

    if (!options || !options.skipRender) {
      if (view === 'dashboard') Dashboard.renderDashboard();
      if (view === 'tickets') Tickets.render();
      if (view === 'chat') Chat.render();
      if (view === 'reports') Dashboard.renderReports();
      if (view === 'users') renderUsers();
    }

    window.scrollTo({ top: 0, behavior: 'auto' });
  }

  function updateBadges() {
    const user = Auth.getCurrentUser();
    if (!user) return;

    const visible = Auth.scopeTickets(Storage.getTickets());
    const activeTickets = visible.filter(
      (ticket) => ticket.status === 'aberto' || ticket.status === 'em_andamento'
    ).length;
    const unread = Chat.getUnreadCount();

    const setBadge = (id, count) => {
      const element = document.getElementById(id);
      if (!element) return;
      element.textContent = count > 99 ? '99+' : String(count);
      element.hidden = count === 0;
    };

    setBadge('nav-tickets-badge', activeTickets);
    setBadge('mobile-tickets-badge', activeTickets);
    setBadge('nav-chat-badge', unread);
    setBadge('mobile-chat-badge', unread);

    const dot = document.getElementById('topbar-chat-dot');
    if (dot) dot.hidden = unread === 0;

    const chatButton = document.querySelector('.topbar__chat-btn');
    if (chatButton) {
      chatButton.setAttribute('aria-label', unread ? `Abrir mensagens, ${unread} não lidas` : 'Abrir mensagens');
    }
  }

  function refreshAll() {
    if (!Auth.getCurrentUser()) return;

    Auth.applyPermissions();
    updateBadges();
    Dashboard.renderAll();
    Tickets.render();
    Chat.render();

    if (Auth.canManageUsers()) {
      renderUsers();
    }

    if (!canAccessView(state.currentView)) {
      navigate('dashboard');
    }
  }

  function enterApp(user) {
    state.currentUser = user;

    const loginScreen = document.getElementById('login-screen');
    const appScreen = document.getElementById('app-screen');
    if (loginScreen) loginScreen.hidden = true;
    if (appScreen) appScreen.hidden = false;

    document.body.classList.add('is-authenticated');
    Auth.applyPermissions();
    Tickets.resetFilters();
    Chat.reset();

    state.currentView = 'dashboard';
    navigate('dashboard');
    refreshAll();
    updateBadges();

    HS.App.toast(`Bem-vindo(a), ${Utils.firstName(user.name)}!`, 'success');
  }

  function showAuth(options) {
    const loginScreen = document.getElementById('login-screen');
    const appScreen = document.getElementById('app-screen');

    closeModal();
    closeSidebar();
    closeUserMenu();

    if (appScreen) appScreen.hidden = true;
    if (loginScreen) loginScreen.hidden = false;
    document.body.classList.remove('is-authenticated');

    state.currentUser = null;
    state.currentView = 'dashboard';
    document.title = 'HelpSchool — Gestão de chamados escolares';

    const email = document.getElementById('login-email');
    const password = document.getElementById('login-password');
    const error = document.getElementById('login-error');
    if (email) email.value = '';
    if (password) password.value = '';
    if (error) {
      error.hidden = true;
      error.textContent = '';
    }

    Chat.reset();

    if (options && options.message) {
      window.setTimeout(() => HS.App.toast(options.message, options.tone || 'info'), 120);
    }
  }

  // ---------- Sistema de modais ----------

  function openModal(config) {
    const root = document.getElementById('modal-root');
    if (!root) return null;

    closeModal();

    state.lastFocused = document.activeElement;
    const modalId = Utils.uid('modal');
    const titleId = `${modalId}-title`;
    const subtitle = config.subtitle
      ? `<p class="modal__subtitle">${Utils.escapeHTML(config.subtitle)}</p>`
      : '';
    const footer = config.footer
      ? `<footer class="modal__footer">${config.footer}</footer>`
      : '';

    root.innerHTML = `
      <div class="modal-layer">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal modal--${config.size || 'md'}" id="${modalId}" role="dialog" aria-modal="true" aria-labelledby="${titleId}" tabindex="-1">
          <header class="modal__header">
            <div class="modal__heading">
              <h3 id="${titleId}">${Utils.escapeHTML(config.title || '')}</h3>
              ${subtitle}
            </div>
            <button class="icon-btn modal__close" type="button" data-modal-close aria-label="Fechar janela">
              ${icon('i-x')}
            </button>
          </header>
          <div class="modal__body">${config.content || ''}</div>
          ${footer}
        </div>
      </div>`;

    const layer = root.querySelector('.modal-layer');
    const modal = root.querySelector('.modal');
    state.modal = modal;
    state.modalLayer = layer;
    document.body.classList.add('has-modal');

    if (typeof config.onMount === 'function') {
      config.onMount(modal);
    }

    window.setTimeout(() => {
      if (!state.modal) return;
      const autofocus = modal.querySelector('[data-autofocus]');
      if (autofocus) autofocus.focus();
      else if (!modal.contains(document.activeElement)) modal.focus();
    }, 30);

    state.modalConfig = config;
    return modal;
  }

  function closeModal() {
    const root = document.getElementById('modal-root');
    const current = state.modal;

    if (current && state.modalConfig && typeof state.modalConfig.onClose === 'function') {
      state.modalConfig.onClose();
    }

    if (root) root.innerHTML = '';
    state.modal = null;
    state.modalLayer = null;
    state.modalConfig = null;
    document.body.classList.remove('has-modal');

    if (state.lastFocused && document.contains(state.lastFocused)) {
      try {
        state.lastFocused.focus({ preventScroll: true });
      } catch (error) {
        state.lastFocused.focus();
      }
    }
    state.lastFocused = null;
  }

  function confirmAction(config) {
    const tone = config.tone || 'primary';
    const content = `
      <div class="confirm-dialog">
        <span class="confirm-dialog__icon confirm-dialog__icon--${tone}">
          ${icon(config.icon || 'i-help')}
        </span>
        <p>${Utils.escapeHTML(config.message || '')}</p>
        ${config.detail ? `<span class="confirm-dialog__detail">${Utils.escapeHTML(config.detail)}</span>` : ''}
      </div>
      <div class="form-actions form-actions--right">
        <button class="btn btn--ghost" type="button" data-modal-close>Cancelar</button>
        <button class="btn btn--${tone}" type="button" id="confirm-action-button">${Utils.escapeHTML(config.confirmLabel || 'Confirmar')}</button>
      </div>`;

    const modal = openModal({
      title: config.title || 'Confirmar ação',
      subtitle: config.subtitle || '',
      content,
      size: 'sm',
      onMount(modalElement) {
        const button = modalElement.querySelector('#confirm-action-button');
        if (button) {
          button.addEventListener('click', () => {
            if (typeof config.onConfirm === 'function') config.onConfirm();
          });
        }
      }
    });

    return modal;
  }

  // ---------- Notificações ----------

  function toast(message, type) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const allowed = ['success', 'error', 'info', 'warning'];
    const tone = allowed.includes(type) ? type : 'info';
    const iconName = {
      success: 'i-check-circle',
      error: 'i-alert',
      warning: 'i-alert',
      info: 'i-help'
    }[tone];

    const element = document.createElement('div');
    element.className = `toast toast--${tone}`;
    element.setAttribute('role', tone === 'error' ? 'alert' : 'status');
    element.innerHTML = `
      <span class="toast__icon">${icon(iconName)}</span>
      <p>${Utils.escapeHTML(message)}</p>
      <button class="toast__close" type="button" aria-label="Fechar notificação">${icon('i-x')}</button>`;

    const remove = () => {
      if (!element.parentNode) return;
      element.classList.add('is-leaving');
      window.setTimeout(() => element.remove(), 220);
    };

    element.querySelector('.toast__close').addEventListener('click', remove);
    container.appendChild(element);

    while (container.children.length > 4) {
      container.firstElementChild.remove();
    }

    window.setTimeout(remove, 4600);
  }

  // ---------- Perfil e ajuda ----------

  function openProfileModal() {
    const user = Auth.getCurrentUser();
    if (!user) return;

    const role = ROLES[user.role] || { label: user.role, description: '' };
    const content = `
      <div class="profile-modal">
        <div class="profile-modal__hero">
          <span class="avatar avatar--xl" data-color="${Utils.escapeHTML(user.color || 'blue')}">${Utils.escapeHTML(Utils.initials(user.name))}</span>
          <div>
            <h3>${Utils.escapeHTML(user.name)}</h3>
            <span class="role-chip role-chip--${Utils.escapeHTML(user.role)}">${Utils.escapeHTML(role.label)}</span>
          </div>
        </div>
        <div class="profile-grid">
          <div><span>E-mail</span><strong>${Utils.escapeHTML(user.email)}</strong></div>
          <div><span>Setor</span><strong>${Utils.escapeHTML(user.department || 'Não informado')}</strong></div>
          <div><span>Último acesso</span><strong>${user.lastLoginAt ? Utils.formatDateTime(user.lastLoginAt) : 'Primeiro acesso'}</strong></div>
          <div><span>Conta criada em</span><strong>${Utils.formatDate(user.createdAt)}</strong></div>
        </div>
        <div class="profile-permissions">
          <span>${icon('i-shield')}</span>
          <div>
            <strong>Permissões do perfil</strong>
            <p>${Utils.escapeHTML(role.description)}</p>
          </div>
        </div>
        <div class="form-actions form-actions--right">
          <button class="btn btn--primary" type="button" data-modal-close>Fechar</button>
        </div>
      </div>`;

    openModal({
      title: 'Meu perfil',
      subtitle: 'Informações da sua conta no HelpSchool',
      content,
      size: 'sm'
    });
  }

  function openHelpModal() {
    const content = `
      <div class="help-guide">
        <div class="help-guide__intro">
          <span>${icon('i-sparkle')}</span>
          <div>
            <strong>Guia rápido do HelpSchool</strong>
            <p>Um fluxo simples para resolver os chamados da escola com rastreabilidade.</p>
          </div>
        </div>
        <div class="help-steps">
          <div class="help-step">
            <span>1</span>
            <div><strong>Abra o chamado</strong><p>Informe título, descrição, categoria, prioridade e localização do problema.</p></div>
          </div>
          <div class="help-step">
            <span>2</span>
            <div><strong>Receba a atribuição</strong><p>A secretaria define o técnico responsável e acompanha o andamento.</p></div>
          </div>
          <div class="help-step">
            <span>3</span>
            <div><strong>Registre o atendimento</strong><p>O técnico documenta diagnóstico e solução, mantendo todo o histórico.</p></div>
          </div>
          <div class="help-step">
            <span>4</span>
            <div><strong>Conclua e acompanhe</strong><p>O chamado é finalizado e passa a compor os indicadores e relatórios.</p></div>
          </div>
        </div>
        <div class="help-guide__tip">
          <strong>Dica</strong>
          <p>Use o chat interno para alinhar detalhes com a equipe sem perder informações fora do sistema.</p>
        </div>
        <div class="form-actions form-actions--right">
          <button class="btn btn--primary" type="button" data-modal-close>Entendi</button>
        </div>
      </div>`;

    openModal({
      title: 'Central de ajuda',
      subtitle: 'Tudo o que você precisa para começar',
      content,
      size: 'md'
    });
  }

  // ---------- Gestão de usuários ----------

  function filteredUsers() {
    const search = Utils.normalizeText(state.userFilters.search);
    return Storage.getUsers()
      .filter((user) => {
        if (state.userFilters.role !== 'all' && user.role !== state.userFilters.role) return false;
        if (!search) return true;
        return Utils.normalizeText(`${user.name} ${user.email} ${user.department} ${Utils.roleLabel(user.role)}`).includes(search);
      })
      .sort((a, b) => {
        if (a.active !== b.active) return a.active ? -1 : 1;
        return a.name.localeCompare(b.name, 'pt-BR');
      });
  }

  function renderUsers() {
    if (!Auth.canManageUsers()) return;

    const wrap = document.getElementById('users-table-wrap');
    const countElement = document.getElementById('users-count');
    if (!wrap) return;

    const current = Auth.getCurrentUser();
    const list = filteredUsers();
    const total = Storage.getUsers().length;

    if (countElement) {
      countElement.textContent = `${Utils.pluralize(list.length, 'usuário', 'usuários')} exibido${list.length === 1 ? '' : 's'} de ${total}`;
    }

    if (!list.length) {
      wrap.innerHTML = `
        <div class="empty-state">
          <span class="empty-state__icon">${icon('i-users')}</span>
          <h3>Nenhum usuário encontrado</h3>
          <p>Ajuste a pesquisa ou o filtro de perfil.</p>
        </div>`;
      return;
    }

    wrap.innerHTML = `
      <div class="table-wrap">
        <table class="data-table users-table">
          <thead>
            <tr>
              <th scope="col">Usuário</th>
              <th scope="col">Perfil</th>
              <th scope="col">Setor</th>
              <th scope="col">Status</th>
              <th scope="col">Último acesso</th>
              <th scope="col"><span class="sr-only">Ações</span></th>
            </tr>
          </thead>
          <tbody>
            ${list.map((user) => {
              const isSelf = current && current.id === user.id;
              return `
                <tr>
                  <td data-label="Usuário">
                    <div class="user-table-cell">
                      <span class="avatar" data-color="${Utils.escapeHTML(user.color || 'blue')}">${Utils.escapeHTML(Utils.initials(user.name))}</span>
                      <div>
                        <strong>${Utils.escapeHTML(user.name)} ${isSelf ? '<span class="self-chip">você</span>' : ''}</strong>
                        <span>${Utils.escapeHTML(user.email)}</span>
                      </div>
                    </div>
                  </td>
                  <td data-label="Perfil"><span class="role-chip role-chip--${Utils.escapeHTML(user.role)}">${Utils.escapeHTML(Utils.roleLabel(user.role))}</span></td>
                  <td data-label="Setor">${Utils.escapeHTML(user.department)}</td>
                  <td data-label="Status"><span class="account-status ${user.active ? 'is-active' : 'is-inactive'}"><span></span>${user.active ? 'Ativo' : 'Inativo'}</span></td>
                  <td data-label="Último acesso"><span class="date-cell">${user.lastLoginAt ? Utils.formatDate(user.lastLoginAt) : 'Nunca acessou'}</span></td>
                  <td class="user-actions-cell">
                    <div class="row-actions">
                      <button class="icon-btn icon-btn--sm" type="button" data-user-action="edit" data-user-id="${Utils.escapeHTML(user.id)}" aria-label="Editar ${Utils.escapeHTML(user.name)}" title="Editar">${icon('i-edit')}</button>
                      ${isSelf ? '' : `
                        <button class="icon-btn icon-btn--sm" type="button" data-user-action="toggle" data-user-id="${Utils.escapeHTML(user.id)}" aria-label="${user.active ? 'Desativar' : 'Ativar'} ${Utils.escapeHTML(user.name)}" title="${user.active ? 'Desativar' : 'Ativar'}">${icon(user.active ? 'i-lock' : 'i-check-circle')}</button>
                        <button class="icon-btn icon-btn--sm icon-btn--danger" type="button" data-user-action="delete" data-user-id="${Utils.escapeHTML(user.id)}" aria-label="Excluir ${Utils.escapeHTML(user.name)}" title="Excluir">${icon('i-trash')}</button>
                      `}
                    </div>
                  </td>
                </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>`;
  }

  function openUserModal(userId) {
    if (!Auth.canManageUsers()) return;

    const existing = userId ? Auth.getUserById(userId) : null;
    const isEdit = Boolean(existing);
    const roleOptions = Object.keys(ROLES).map((key) => {
      return `<option value="${key}" ${existing && existing.role === key ? 'selected' : ''}>${ROLES[key].label}</option>`;
    }).join('');
    const initialRole = existing ? existing.role : 'professor';

    const content = `
      <form id="user-form" class="user-form" novalidate>
        <div class="form-intro">
          <span class="form-intro__icon">${icon('i-users')}</span>
          <div>
            <strong>${isEdit ? 'Editar usuário' : 'Novo usuário'}</strong>
            <p>${isEdit ? 'Atualize os dados e permissões desta conta.' : 'Cadastre um novo acesso para a equipe da escola.'}</p>
          </div>
        </div>

        <div class="form-grid">
          <div class="field">
            <label for="user-name">Nome completo <span class="required-mark">*</span></label>
            <input type="text" id="user-name" name="name" maxlength="80" required value="${existing ? Utils.escapeHTML(existing.name) : ''}" autocomplete="off" placeholder="Ex.: Maria Oliveira">
          </div>
          <div class="field">
            <label for="user-email">E-mail institucional <span class="required-mark">*</span></label>
            <input type="email" id="user-email" name="email" maxlength="120" required value="${existing ? Utils.escapeHTML(existing.email) : ''}" autocomplete="off" placeholder="nome@helpschool.com">
          </div>
        </div>

        <div class="form-grid">
          <div class="field">
            <label for="user-role">Perfil de acesso <span class="required-mark">*</span></label>
            <div class="select-wrap select-wrap--full">
              ${icon('i-shield')}
              <select id="user-role" name="role" required>${roleOptions}</select>
            </div>
            <span class="field__hint" id="user-role-hint">${Utils.escapeHTML(Auth.getRoleDescription(initialRole))}</span>
          </div>
          <div class="field">
            <label for="user-department">Setor <span class="required-mark">*</span></label>
            <input type="text" id="user-department" name="department" maxlength="80" required value="${existing ? Utils.escapeHTML(existing.department || '') : ''}" autocomplete="off" placeholder="Ex.: Coordenação Pedagógica">
          </div>
        </div>

        <div class="form-grid">
          <div class="field">
            <label for="user-password">${isEdit ? 'Nova senha (opcional)' : 'Senha de acesso'} ${isEdit ? '' : '<span class="required-mark">*</span>'}</label>
            <div class="input-shell">
              <span class="input-shell__icon">${icon('i-lock')}</span>
              <input type="password" id="user-password" name="password" minlength="6" ${isEdit ? '' : 'required'} autocomplete="new-password" placeholder="${isEdit ? 'Deixe em branco para manter' : 'Mínimo de 6 caracteres'}">
            </div>
          </div>
          <div class="field field--switch">
            <span class="field__label-row"><label for="user-active">Conta ativa</label></span>
            <label class="switch">
              <input type="checkbox" id="user-active" name="active" ${!existing || existing.active ? 'checked' : ''}>
              <span class="switch__track"><span></span></span>
              <span class="switch__label">Permitir acesso ao sistema</span>
            </label>
          </div>
        </div>

        <p class="form-error" role="alert" hidden></p>

        <div class="form-actions">
          <button class="btn btn--ghost" type="button" data-modal-close>Cancelar</button>
          <button class="btn btn--primary" type="submit">${icon(isEdit ? 'i-check' : 'i-plus')} ${isEdit ? 'Salvar alterações' : 'Criar usuário'}</button>
        </div>
      </form>`;

    openModal({
      title: isEdit ? 'Editar usuário' : 'Novo usuário',
      subtitle: isEdit ? 'Gestão de perfil e permissões' : 'Adicione um novo acesso à plataforma',
      content,
      size: 'md',
      onMount(modal) {
        const form = modal.querySelector('#user-form');
        const roleSelect = modal.querySelector('#user-role');
        const roleHint = modal.querySelector('#user-role-hint');
        const nameInput = modal.querySelector('#user-name');
        if (!form) return;

        if (nameInput) nameInput.focus();

        if (roleSelect && roleHint) {
          roleSelect.addEventListener('change', () => {
            roleHint.textContent = Auth.getRoleDescription(roleSelect.value);
          });
        }

        form.addEventListener('submit', (event) => {
          event.preventDefault();
          const error = form.querySelector('.form-error');
          const payload = {
            name: form.querySelector('#user-name').value.trim(),
            email: form.querySelector('#user-email').value.trim(),
            role: form.querySelector('#user-role').value,
            department: form.querySelector('#user-department').value.trim(),
            password: form.querySelector('#user-password').value,
            active: form.querySelector('#user-active').checked
          };

          const result = Auth.saveUser(payload, existing ? existing.id : null);

          if (!result.ok) {
            if (error) {
              error.textContent = result.error;
              error.hidden = false;
            }
            return;
          }

          closeModal();
          toast(isEdit ? 'Usuário atualizado com sucesso.' : 'Usuário criado com sucesso.', 'success');
          refreshAll();
          renderUsers();

          if (!canAccessView(state.currentView)) navigate('dashboard');
        });
      }
    });
  }

  function handleUserAction(action, userId) {
    const user = Auth.getUserById(userId);
    if (!user) {
      toast('Usuário não encontrado.', 'error');
      return;
    }

    if (action === 'edit') {
      openUserModal(userId);
      return;
    }

    if (action === 'toggle') {
      confirmAction({
        title: user.active ? 'Desativar usuário' : 'Reativar usuário',
        subtitle: 'Controle de acesso',
        message: `${user.name} ${user.active ? 'não poderá mais acessar o HelpSchool enquanto a conta estiver inativa.' : 'voltará a ter acesso ao HelpSchool.'}`,
        confirmLabel: user.active ? 'Desativar conta' : 'Reativar conta',
        tone: user.active ? 'danger' : 'success',
        icon: user.active ? 'i-lock' : 'i-check-circle',
        onConfirm() {
          const result = Auth.toggleUserActive(userId);
          if (!result.ok) {
            toast(result.error, 'error');
            return;
          }
          closeModal();
          toast(user.active ? 'Usuário desativado.' : 'Usuário reativado.', 'success');
          refreshAll();
        }
      });
      return;
    }

    if (action === 'delete') {
      confirmAction({
        title: 'Excluir usuário',
        subtitle: 'Esta ação não pode ser desfeita',
        message: `Tem certeza de que deseja excluir ${user.name}? O usuário perderá o acesso e os chamados existentes serão preservados no histórico.`,
        detail: 'Recomendamos desativar a conta caso precise manter o vínculo com registros antigos.',
        confirmLabel: 'Excluir usuário',
        tone: 'danger',
        icon: 'i-trash',
        onConfirm() {
          const result = Auth.deleteUser(userId);
          if (!result.ok) {
            toast(result.error, 'error');
            return;
          }
          closeModal();
          toast('Usuário excluído.', 'success');
          refreshAll();
        }
      });
    }
  }

  function initUsersView() {
    const search = document.getElementById('user-search');
    const role = document.getElementById('user-role-filter');
    const wrap = document.getElementById('users-table-wrap');

    if (search) {
      search.addEventListener('input', Utils.debounce(() => {
        state.userFilters.search = search.value;
        renderUsers();
      }, 160));
    }

    if (role) {
      role.addEventListener('change', () => {
        state.userFilters.role = role.value;
        renderUsers();
      });
    }

    if (wrap) {
      wrap.addEventListener('click', (event) => {
        const button = event.target.closest('[data-user-action]');
        if (!button) return;
        handleUserAction(button.getAttribute('data-user-action'), button.getAttribute('data-user-id'));
      });
    }
  }

  // ---------- Reset de dados demo ----------

  function handleResetDemo() {
    confirmAction({
      title: 'Restaurar dados de demonstração',
      subtitle: 'Todas as alterações locais serão substituídas',
      message: 'Os chamados, mensagens e usuários voltarão ao estado inicial. Esta ação não pode ser desfeita.',
      confirmLabel: 'Restaurar dados',
      tone: 'danger',
      icon: 'i-refresh',
      onConfirm() {
        Storage.reset();
        closeModal();

        if (!Auth.getCurrentUser()) {
          Storage.clearSession();
          showAuth();
          toast('Dados restaurados. Entre novamente com uma conta demo.', 'info');
          return;
        }

        Tickets.resetFilters();
        Chat.reset();
        refreshAll();
        toast('Dados de demonstração restaurados.', 'success');
      }
    });
  }

  // ---------- Eventos globais ----------

  function bindCoreEvents() {
    document.addEventListener('click', (event) => {
      const viewTrigger = event.target.closest('[data-view]');
      if (viewTrigger && !viewTrigger.hasAttribute('data-user-action')) {
        event.preventDefault();
        const targetView = viewTrigger.getAttribute('data-view');
        if (VIEW_META[targetView]) navigate(targetView);
        return;
      }

      const actionTrigger = event.target.closest('[data-action]');
      if (actionTrigger) {
        const action = actionTrigger.getAttribute('data-action');
        event.preventDefault();

        if (action === 'new-ticket') Tickets.openNewModal();
        if (action === 'new-user') openUserModal();
        if (action === 'export-csv') Dashboard.exportCSV();
        if (action === 'logout') Auth.logout();
        if (action === 'profile') {
          closeUserMenu();
          openProfileModal();
        }
        if (action === 'help') openHelpModal();
        if (action === 'reset-demo') {
          closeUserMenu();
          handleResetDemo();
        }
        return;
      }

      if (!event.target.closest('.user-menu-wrap')) closeUserMenu();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        if (state.modal) {
          closeModal();
          return;
        }
        if (document.getElementById('sidebar').classList.contains('is-open')) {
          closeSidebar();
          return;
        }
        if (!document.getElementById('user-menu').hidden) {
          closeUserMenu();
          document.getElementById('user-menu-btn').focus();
        }
      }

      if (event.key === 'Tab' && state.modal) {
        const focusable = state.modal.querySelectorAll(
          'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        if (!focusable.length) {
          event.preventDefault();
          state.modal.focus();
          return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      }
    });

    const modalRoot = document.getElementById('modal-root');
    if (modalRoot) {
      modalRoot.addEventListener('click', (event) => {
        if (event.target.closest('[data-modal-close]')) closeModal();
      });
    }

    const menuButton = document.getElementById('user-menu-btn');
    const userMenu = document.getElementById('user-menu');
    if (menuButton && userMenu) {
      menuButton.addEventListener('click', (event) => {
        event.stopPropagation();
        const willOpen = userMenu.hidden;
        userMenu.hidden = !willOpen;
        menuButton.setAttribute('aria-expanded', String(willOpen));
        if (willOpen) {
          const firstItem = userMenu.querySelector('.user-menu__item');
          if (firstItem) window.setTimeout(() => firstItem.focus(), 40);
        }
      });
    }

    const sidebarOpen = document.getElementById('sidebar-open');
    const sidebarClose = document.getElementById('sidebar-close');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const mobileMore = document.getElementById('mobile-more');
    if (sidebarOpen) sidebarOpen.addEventListener('click', openSidebar);
    if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
    if (mobileMore) mobileMore.addEventListener('click', openSidebar);

    const logoutItems = document.querySelectorAll('[data-action="logout"]');
    logoutItems.forEach((item) => {
      item.addEventListener('click', () => closeUserMenu());
    });

    document.addEventListener('hs:login', (event) => {
      enterApp(event.detail.user);
    });

    document.addEventListener('hs:logout', () => {
      showAuth({ message: 'Sessão encerrada com segurança.', tone: 'info' });
    });

    window.addEventListener('hs:storage', (event) => {
      if (!Auth.getCurrentUser()) return;
      if (!event.detail) return;
      if (event.detail.key === 'helpschool.tickets' || event.detail.key === 'helpschool.messages') {
        updateBadges();
      }
    });
  }

  function boot() {
    Storage.initialize();
    Auth.initialize();
    Tickets.init();
    Chat.init();
    Dashboard.init();
    initUsersView();
    bindCoreEvents();

    const session = Storage.getSession();
    const user = Auth.getCurrentUser();

    if (session && !user) {
      Storage.clearSession();
    }

    if (user) {
      enterApp(user);
    } else {
      showAuth();
    }

    state.initialized = true;
  }

  HS.App = {
    init: boot,
    navigate,
    openModal,
    closeModal,
    confirmAction,
    toast,
    refreshAll,
    updateBadges,
    renderUsers,
    openUserModal,
    getState() {
      return Object.assign({}, state);
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})(window.HelpSchool = window.HelpSchool || {});
