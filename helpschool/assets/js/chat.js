/**
 * HelpSchool — Chat interno privado entre usuários
 */
(function (HS) {
  'use strict';

  const { Storage, Utils, Auth } = HS;

  const state = {
    initialized: false,
    currentContactId: null,
    contactSearch: ''
  };

  function messages() {
    return Storage.getMessages();
  }

  function saveMessages(list) {
    return Storage.saveMessages(list);
  }

  function conversationBetween(userA, userB) {
    return messages()
      .filter((message) => {
        return (message.senderId === userA && message.recipientId === userB) ||
          (message.senderId === userB && message.recipientId === userA);
      })
      .sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));
  }

  function unreadFrom(contactId) {
    const user = Auth.getCurrentUser();
    if (!user) return 0;
    return messages().filter((message) => {
      return message.recipientId === user.id &&
        message.senderId === contactId &&
        !message.readAt;
    }).length;
  }

  function getUnreadCount() {
    const user = Auth.getCurrentUser();
    if (!user) return 0;
    return messages().filter((message) => message.recipientId === user.id && !message.readAt).length;
  }

  function lastMessageBetween(contactId) {
    const user = Auth.getCurrentUser();
    if (!user) return null;
    const conversation = conversationBetween(user.id, contactId);
    return conversation.length ? conversation[conversation.length - 1] : null;
  }

  function markConversationRead(contactId) {
    const user = Auth.getCurrentUser();
    if (!user) return false;

    const list = messages();
    const now = new Date().toISOString();
    let changed = false;

    list.forEach((message) => {
      if (message.recipientId === user.id && message.senderId === contactId && !message.readAt) {
        message.readAt = now;
        changed = true;
      }
    });

    if (changed) saveMessages(list);
    return changed;
  }

  function contactPreview(message) {
    const user = Auth.getCurrentUser();
    if (!message) return 'Inicie uma conversa';
    const prefix = user && message.senderId === user.id ? 'Você: ' : '';
    return `${prefix}${message.text}`;
  }

  function renderContacts() {
    const listElement = document.getElementById('chat-contact-list');
    const totalElement = document.getElementById('chat-total-contacts');
    if (!listElement) return;

    const user = Auth.getCurrentUser();
    if (!user) return;

    const search = Utils.normalizeText(state.contactSearch);
    const contacts = Auth.getActiveUsers()
      .filter((contact) => contact.id !== user.id)
      .filter((contact) => {
        if (!search) return true;
        return Utils.normalizeText(`${contact.name} ${contact.email} ${Utils.roleLabel(contact.role)}`).includes(search);
      })
      .map((contact) => {
        const last = lastMessageBetween(contact.id);
        return {
          contact,
          last,
          unread: unreadFrom(contact.id),
          sortDate: last ? new Date(last.createdAt).getTime() : 0
        };
      })
      .sort((a, b) => b.sortDate - a.sortDate || a.contact.name.localeCompare(b.contact.name, 'pt-BR'));

    if (totalElement) {
      totalElement.textContent = Utils.pluralize(contacts.length, 'contato', 'contatos');
    }

    if (!contacts.length) {
      listElement.innerHTML = `
        <div class="chat-contact-empty">
          <svg><use href="#i-search"></use></svg>
          <strong>Nenhum contato encontrado</strong>
          <span>Tente outro nome ou perfil.</span>
        </div>`;
      return;
    }

    listElement.innerHTML = contacts.map(({ contact, last, unread }) => {
      const active = state.currentContactId === contact.id;
      const dateLabel = last ? Utils.shortTimeAgo(last.createdAt) : '';
      return `
        <button class="contact-item ${active ? 'is-active' : ''}" type="button" data-chat-contact="${Utils.escapeHTML(contact.id)}" aria-current="${active ? 'true' : 'false'}">
          <span class="avatar" data-color="${Utils.escapeHTML(contact.color || 'blue')}">${Utils.escapeHTML(Utils.initials(contact.name))}</span>
          <span class="contact-item__body">
            <span class="contact-item__top">
              <strong>${Utils.escapeHTML(contact.name)}</strong>
              <time>${Utils.escapeHTML(dateLabel)}</time>
            </span>
            <span class="contact-item__bottom">
              <span class="contact-item__preview">${Utils.escapeHTML(contactPreview(last))}</span>
              ${unread ? `<span class="unread-count" aria-label="${unread} mensagens não lidas">${unread > 9 ? '9+' : unread}</span>` : ''}
            </span>
          </span>
        </button>`;
    }).join('');
  }

  function renderMessageBubbles(conversation) {
    if (!conversation.length) {
      const contact = Auth.getUserById(state.currentContactId);
      return `
        <div class="chat-thread-empty">
          <span class="chat-thread-empty__icon"><svg><use href="#i-message-circle"></use></svg></span>
          <h3>Comece a conversa</h3>
          <p>Envie uma mensagem para ${Utils.escapeHTML(contact ? contact.name : 'este contato')}.</p>
        </div>`;
    }

    const user = Auth.getCurrentUser();
    let lastDay = '';
    const items = [];

    conversation.forEach((message) => {
      const day = Utils.formatDayLabel(message.createdAt);
      if (day !== lastDay) {
        items.push(`<div class="chat-day"><span>${Utils.escapeHTML(day)}</span></div>`);
        lastDay = day;
      }

      const outgoing = message.senderId === user.id;
      items.push(`
        <div class="message-row ${outgoing ? 'message-row--outgoing' : 'message-row--incoming'}">
          <div class="message-bubble">
            <p>${Utils.escapeHTML(message.text)}</p>
            <span class="message-bubble__meta">
              ${Utils.formatTime(message.createdAt)}
              ${outgoing ? `<svg class="message-status ${message.readAt ? 'is-read' : ''}" aria-label="${message.readAt ? 'Mensagem lida' : 'Mensagem enviada'}"><use href="#i-check"></use></svg>` : ''}
            </span>
          </div>
        </div>`);
    });

    return items.join('');
  }

  function renderConversation() {
    const emptyState = document.getElementById('chat-empty-state');
    const activeState = document.getElementById('chat-active');
    const messagesElement = document.getElementById('chat-messages');
    const nameElement = document.getElementById('chat-header-name');
    const roleElement = document.getElementById('chat-header-role');
    const avatarElement = document.getElementById('chat-header-avatar');
    const shell = document.getElementById('chat-shell');

    if (!emptyState || !activeState || !messagesElement) return;

    const contact = state.currentContactId ? Auth.getUserById(state.currentContactId) : null;
    const user = Auth.getCurrentUser();

    if (!contact || !user) {
      state.currentContactId = null;
      emptyState.hidden = false;
      activeState.hidden = true;
      if (shell) shell.classList.remove('has-active');
      return;
    }

    emptyState.hidden = true;
    activeState.hidden = false;
    if (shell) shell.classList.add('has-active');

    if (nameElement) nameElement.textContent = contact.name;
    if (roleElement) {
      roleElement.textContent = `${Utils.roleLabel(contact.role)} · ${contact.department || 'Escola'}`;
    }
    if (avatarElement) {
      avatarElement.textContent = Utils.initials(contact.name);
      avatarElement.dataset.color = contact.color || 'blue';
    }

    const conversation = conversationBetween(user.id, contact.id);
    messagesElement.innerHTML = renderMessageBubbles(conversation);

    window.requestAnimationFrame(() => {
      messagesElement.scrollTop = messagesElement.scrollHeight;
    });

    if (contact.active === false) {
      const input = document.getElementById('chat-input');
      const send = document.getElementById('chat-send');
      if (input) {
        input.disabled = true;
        input.placeholder = 'Este usuário está inativo.';
      }
      if (send) send.disabled = true;
    } else {
      const input = document.getElementById('chat-input');
      const send = document.getElementById('chat-send');
      if (input) {
        input.disabled = false;
        input.placeholder = 'Escreva uma mensagem...';
      }
      if (send) send.disabled = false;
    }
  }

  function render() {
    renderContacts();
    renderConversation();
  }

  function openConversation(contactId) {
    const user = Auth.getCurrentUser();
    if (!user) return;
    const contact = Auth.getUserById(contactId);
    if (!contact || contact.id === user.id) {
      HS.App.toast('Contato não encontrado.', 'error');
      return;
    }

    state.currentContactId = contactId;
    markConversationRead(contactId);
    render();

    const input = document.getElementById('chat-input');
    if (input && window.innerWidth > 900) {
      window.setTimeout(() => input.focus(), 80);
    }
  }

  function closeConversation() {
    state.currentContactId = null;
    render();
  }

  function sendMessage(text) {
    const user = Auth.getCurrentUser();
    const contactId = state.currentContactId;
    const content = String(text || '').trim();

    if (!user || !contactId) return { ok: false, error: 'Selecione uma conversa.' };
    if (!content) return { ok: false, error: 'Digite uma mensagem.' };
    if (content.length > 2000) return { ok: false, error: 'A mensagem deve ter no máximo 2000 caracteres.' };

    const contact = Auth.getUserById(contactId);
    if (!contact || !contact.active) return { ok: false, error: 'Este contato não está disponível.' };

    const list = messages();
    const message = {
      id: Utils.uid('m'),
      senderId: user.id,
      recipientId: contactId,
      text: content,
      createdAt: new Date().toISOString(),
      readAt: null
    };

    list.push(message);
    if (!saveMessages(list)) {
      return { ok: false, error: 'Não foi possível enviar a mensagem.' };
    }

    render();
    if (HS.App) HS.App.updateBadges();
    return { ok: true, message };
  }

  function autoGrowTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = `${Math.min(textarea.scrollHeight, 132)}px`;
  }

  function reset() {
    state.currentContactId = null;
    state.contactSearch = '';

    const search = document.getElementById('chat-search');
    const input = document.getElementById('chat-input');
    const shell = document.getElementById('chat-shell');

    if (search) search.value = '';
    if (input) {
      input.value = '';
      input.style.height = 'auto';
    }
    if (shell) shell.classList.remove('has-active');

    render();
  }

  function init() {
    if (state.initialized) return;
    state.initialized = true;

    const search = document.getElementById('chat-search');
    const contactList = document.getElementById('chat-contact-list');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const back = document.getElementById('chat-back');

    if (search) {
      search.addEventListener('input', () => {
        state.contactSearch = search.value;
        renderContacts();
      });
    }

    if (contactList) {
      contactList.addEventListener('click', (event) => {
        const button = event.target.closest('[data-chat-contact]');
        if (!button) return;
        openConversation(button.getAttribute('data-chat-contact'));
      });
    }

    if (form && input) {
      input.addEventListener('input', () => autoGrowTextarea(input));

      input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
          event.preventDefault();
          form.requestSubmit();
        }
      });

      form.addEventListener('submit', (event) => {
        event.preventDefault();
        const result = sendMessage(input.value);
        if (!result.ok) {
          HS.App.toast(result.error, 'error');
          return;
        }
        input.value = '';
        autoGrowTextarea(input);
        input.focus();
      });
    }

    if (back) {
      back.addEventListener('click', closeConversation);
    }

    window.addEventListener('hs:storage', (event) => {
      if (!event.detail || event.detail.key !== HS.KEYS.messages) return;
      if (!Auth.getCurrentUser()) return;
      render();
      if (HS.App) HS.App.updateBadges();
    });
  }

  HS.Chat = {
    init,
    render,
    reset,
    openConversation,
    closeConversation,
    sendMessage,
    getUnreadCount,
    renderContacts
  };
})(window.HelpSchool = window.HelpSchool || {});
