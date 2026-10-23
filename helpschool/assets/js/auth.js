/**
 * HelpSchool — Autenticação, sessão e controle de permissões
 */
(function (HS) {
  'use strict';

  const { Storage, Utils, ROLES } = HS;
  const state = { initialized: false };

  function users() {
    return Storage.getUsers();
  }

  function findUserById(id) {
    return users().find((user) => user.id === id) || null;
  }

  function findUserByEmail(email) {
    const normalized = String(email || '').trim().toLowerCase();
    return users().find((user) => String(user.email).toLowerCase() === normalized) || null;
  }

  const Auth = {
    initialize() {
      if (state.initialized) return;
      state.initialized = true;

      const form = document.getElementById('login-form');
      const emailInput = document.getElementById('login-email');
      const passwordInput = document.getElementById('login-password');
      const errorBox = document.getElementById('login-error');
      const submitButton = document.getElementById('login-submit');
      const toggleButton = document.getElementById('toggle-password');
      const rememberInput = document.getElementById('remember-session');

      if (!form || !emailInput || !passwordInput || !errorBox || !submitButton) return;

      const hideError = () => {
        errorBox.hidden = true;
        errorBox.textContent = '';
        emailInput.removeAttribute('aria-invalid');
        passwordInput.removeAttribute('aria-invalid');
      };

      const showError = (message, field) => {
        errorBox.textContent = message;
        errorBox.hidden = false;
        if (field) {
          field.setAttribute('aria-invalid', 'true');
          field.focus();
        }
      };

      [emailInput, passwordInput].forEach((input) => {
        input.addEventListener('input', hideError);
      });

      form.addEventListener('submit', (event) => {
        event.preventDefault();
        hideError();

        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const persistent = rememberInput ? rememberInput.checked : true;

        if (!email) {
          showError('Informe seu e-mail institucional.', emailInput);
          return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          showError('Digite um endereço de e-mail válido.', emailInput);
          return;
        }

        if (!password) {
          showError('Informe sua senha.', passwordInput);
          return;
        }

        submitButton.classList.add('is-loading');
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');

        window.setTimeout(() => {
          const result = Auth.login(email, password, persistent);
          submitButton.classList.remove('is-loading');
          submitButton.disabled = false;
          submitButton.removeAttribute('aria-busy');

          if (!result.ok) {
            showError(result.error, result.field === 'email' ? emailInput : passwordInput);
            return;
          }

          form.reset();
          if (rememberInput) rememberInput.checked = true;
          hideError();
          document.dispatchEvent(new CustomEvent('hs:login', { detail: { user: result.user } }));
        }, 420);
      });

      if (toggleButton) {
        toggleButton.addEventListener('click', () => {
          const isPassword = passwordInput.type === 'password';
          passwordInput.type = isPassword ? 'text' : 'password';
          toggleButton.setAttribute('aria-pressed', String(isPassword));
          toggleButton.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
          toggleButton.innerHTML = `<svg><use href="#${isPassword ? 'i-eye-off' : 'i-eye'}"></use></svg>`;
          passwordInput.focus();
        });
      }

      document.querySelectorAll('[data-demo-email]').forEach((button) => {
        button.addEventListener('click', () => {
          hideError();
          emailInput.value = button.getAttribute('data-demo-email');
          passwordInput.value = button.getAttribute('data-demo-password') || '123456';
          submitButton.classList.remove('is-pulse');
          void submitButton.offsetWidth;
          submitButton.classList.add('is-pulse');
          submitButton.focus();
        });
      });

      const year = document.getElementById('auth-year');
      if (year) year.textContent = String(new Date().getFullYear());
    },

    login(email, password, persistent) {
      const user = findUserByEmail(email);

      if (!user) {
        return { ok: false, error: 'Não encontramos uma conta com este e-mail.', field: 'email' };
      }

      if (!user.active) {
        return { ok: false, error: 'Esta conta está desativada. Procure o administrador.', field: 'email' };
      }

      if (String(user.password) !== String(password)) {
        return { ok: false, error: 'Senha incorreta. Verifique e tente novamente.', field: 'password' };
      }

      const session = {
        userId: user.id,
        loginAt: new Date().toISOString()
      };

      Storage.saveSession(session, Boolean(persistent));

      const allUsers = users();
      const index = allUsers.findIndex((item) => item.id === user.id);
      if (index >= 0) {
        allUsers[index].lastLoginAt = session.loginAt;
        Storage.saveUsers(allUsers);
      }

      return { ok: true, user };
    },

    logout() {
      Storage.clearSession();
      document.dispatchEvent(new CustomEvent('hs:logout'));
    },

    getSession() {
      return Storage.getSession();
    },

    getCurrentUser() {
      const session = Storage.getSession();
      if (!session || !session.userId) return null;
      const user = findUserById(session.userId);
      if (!user || !user.active) return null;
      return user;
    },

    getUserById(id) {
      return findUserById(id);
    },

    getActiveUsers() {
      return users().filter((user) => user.active);
    },

    getTechnicians() {
      return users().filter((user) => user.role === 'tecnico' && user.active);
    },

    getRoleLabel(role) {
      return ROLES[role] ? ROLES[role].label : role;
    },

    getRoleDescription(role) {
      return ROLES[role] ? ROLES[role].description : '';
    },

    canViewAllTickets() {
      const user = Auth.getCurrentUser();
      return Boolean(user && (user.role === 'admin' || user.role === 'secretaria'));
    },

    canViewTicket(ticket) {
      const user = Auth.getCurrentUser();
      if (!user || !ticket) return false;
      if (user.role === 'admin' || user.role === 'secretaria') return true;
      if (user.role === 'tecnico') return ticket.assigneeId === user.id;
      return ticket.requesterId === user.id;
    },

    scopeTickets(ticketList) {
      const user = Auth.getCurrentUser();
      if (!user) return [];
      if (user.role === 'admin' || user.role === 'secretaria') return ticketList.slice();
      if (user.role === 'tecnico') {
        return ticketList.filter((ticket) => ticket.assigneeId === user.id);
      }
      return ticketList.filter((ticket) => ticket.requesterId === user.id);
    },

    canCreateTickets() {
      return Boolean(Auth.getCurrentUser());
    },

    canAssignTickets() {
      const user = Auth.getCurrentUser();
      return Boolean(user && (user.role === 'admin' || user.role === 'secretaria'));
    },

    canChangeStatus() {
      const user = Auth.getCurrentUser();
      return Boolean(user && (user.role === 'admin' || user.role === 'secretaria'));
    },

    canHandleTicket(ticket) {
      const user = Auth.getCurrentUser();
      if (!user || !ticket) return false;
      if (user.role === 'admin') return true;
      return user.role === 'tecnico' && ticket.assigneeId === user.id;
    },

    canViewReports() {
      const user = Auth.getCurrentUser();
      return Boolean(user && user.role !== 'professor');
    },

    canManageUsers() {
      const user = Auth.getCurrentUser();
      return Boolean(user && user.role === 'admin');
    },

    applyPermissions() {
      const user = Auth.getCurrentUser();
      const canReports = Auth.canViewReports();
      const canUsers = Auth.canManageUsers();

      document.querySelectorAll('[data-permission="reports"]').forEach((element) => {
        element.hidden = !canReports;
      });

      document.querySelectorAll('[data-permission="users"]').forEach((element) => {
        element.hidden = !canUsers;
      });

      const adminLabel = document.getElementById('admin-nav-label');
      if (adminLabel) adminLabel.hidden = !canUsers;

      if (user) {
        const roleMeta = ROLES[user.role] || { label: user.role };
        const avatar = document.getElementById('topbar-avatar');
        const menuAvatar = document.getElementById('menu-avatar');
        const name = document.getElementById('topbar-name');
        const role = document.getElementById('topbar-role');
        const menuName = document.getElementById('menu-name');
        const menuEmail = document.getElementById('menu-email');

        if (avatar) {
          avatar.textContent = Utils.initials(user.name);
          avatar.dataset.color = user.color || 'blue';
        }
        if (menuAvatar) {
          menuAvatar.textContent = Utils.initials(user.name);
          menuAvatar.dataset.color = user.color || 'blue';
        }
        if (name) name.textContent = user.name;
        if (role) role.textContent = roleMeta.label;
        if (menuName) menuName.textContent = user.name;
        if (menuEmail) menuEmail.textContent = user.email;
      }
    },

    saveUser(payload, userId) {
      const allUsers = users();
      const editingId = userId || null;
      const email = String(payload.email || '').trim().toLowerCase();
      const name = String(payload.name || '').trim();
      const role = payload.role;
      const department = String(payload.department || '').trim();

      if (!name || name.length < 3) {
        return { ok: false, error: 'Informe o nome completo do usuário.' };
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        return { ok: false, error: 'Informe um e-mail válido.' };
      }

      if (!ROLES[role]) {
        return { ok: false, error: 'Selecione um perfil de acesso válido.' };
      }

      if (!department) {
        return { ok: false, error: 'Informe o setor do usuário.' };
      }

      const duplicate = allUsers.find(
        (user) => String(user.email).toLowerCase() === email && user.id !== editingId
      );

      if (duplicate) {
        return { ok: false, error: 'Já existe um usuário com este e-mail.' };
      }

      if (editingId) {
        const index = allUsers.findIndex((user) => user.id === editingId);
        if (index < 0) return { ok: false, error: 'Usuário não encontrado.' };

        const previous = allUsers[index];
        const currentUser = Auth.getCurrentUser();

        if (currentUser && currentUser.id === editingId && payload.active === false) {
          return { ok: false, error: 'Você não pode desativar a própria conta.' };
        }

        const removingAdmin = previous.role === 'admin' && (role !== 'admin' || payload.active === false);
        const activeAdmins = allUsers.filter(
          (user) => user.role === 'admin' && user.active && user.id !== editingId
        );

        if (removingAdmin && activeAdmins.length === 0) {
          return { ok: false, error: 'É necessário manter pelo menos um administrador ativo.' };
        }

        const password = String(payload.password || '').trim();
        if (password && password.length < 6) {
          return { ok: false, error: 'A senha deve ter pelo menos 6 caracteres.' };
        }

        allUsers[index] = Object.assign({}, previous, {
          name,
          email,
          role,
          department,
          active: payload.active !== false,
          color: payload.color || previous.color || 'blue'
        });

        if (password) allUsers[index].password = password;
        Storage.saveUsers(allUsers);

        return { ok: true, user: allUsers[index] };
      }

      const password = String(payload.password || '').trim();
      if (password.length < 6) {
        return { ok: false, error: 'Defina uma senha com pelo menos 6 caracteres.' };
      }

      const colorMap = { professor: 'blue', secretaria: 'amber', tecnico: 'green', admin: 'purple' };
      const user = {
        id: `u_${Date.now().toString(36)}`,
        name,
        email,
        password,
        role,
        department,
        color: colorMap[role] || 'blue',
        active: payload.active !== false,
        createdAt: new Date().toISOString()
      };

      allUsers.push(user);
      Storage.saveUsers(allUsers);

      return { ok: true, user };
    },

    deleteUser(userId) {
      const current = Auth.getCurrentUser();
      if (!current) return { ok: false, error: 'Sessão inválida.' };
      if (current.id === userId) {
        return { ok: false, error: 'Você não pode excluir a própria conta.' };
      }

      const allUsers = users();
      const target = allUsers.find((user) => user.id === userId);
      if (!target) return { ok: false, error: 'Usuário não encontrado.' };

      if (target.role === 'admin') {
        const otherAdmins = allUsers.filter(
          (user) => user.role === 'admin' && user.active && user.id !== userId
        );
        if (otherAdmins.length === 0) {
          return { ok: false, error: 'É necessário manter pelo menos um administrador ativo.' };
        }
      }

      Storage.saveUsers(allUsers.filter((user) => user.id !== userId));
      return { ok: true };
    },

    toggleUserActive(userId) {
      const current = Auth.getCurrentUser();
      if (current && current.id === userId) {
        return { ok: false, error: 'Você não pode desativar a própria conta.' };
      }

      const allUsers = users();
      const index = allUsers.findIndex((user) => user.id === userId);
      if (index < 0) return { ok: false, error: 'Usuário não encontrado.' };

      if (allUsers[index].role === 'admin' && allUsers[index].active) {
        const otherAdmins = allUsers.filter(
          (user) => user.role === 'admin' && user.active && user.id !== userId
        );
        if (otherAdmins.length === 0) {
          return { ok: false, error: 'É necessário manter pelo menos um administrador ativo.' };
        }
      }

      allUsers[index].active = !allUsers[index].active;
      Storage.saveUsers(allUsers);
      return { ok: true, user: allUsers[index] };
    }
  };

  HS.Auth = Auth;
})(window.HelpSchool = window.HelpSchool || {});
