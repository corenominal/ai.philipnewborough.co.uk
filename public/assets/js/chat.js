'use strict';

// ===== STATE =====
let currentSessionUuid = null;
let isStreaming         = false;
let userScrolledUp     = false;

// ===== DOM REFS =====
let messagesArea, chatThread, welcomeScreen;
let messageInput, sendBtn, chatList, modelSelect, newChatBtn, searchBtn;
let searchModal, searchInput, searchResults;
let searchDebounceTimer = null;

// ===== INIT =====
document.addEventListener('DOMContentLoaded', () => {
    messagesArea  = document.getElementById('messages-area');
    chatThread    = document.getElementById('chat-thread');
    welcomeScreen = document.getElementById('welcome-screen');
    messageInput  = document.getElementById('message-input');
    sendBtn       = document.getElementById('send-btn');
    chatList      = document.getElementById('chat-list');
    modelSelect   = document.getElementById('model-select');
    newChatBtn    = document.getElementById('new-chat-btn');
    searchBtn     = document.getElementById('search-btn');
    searchInput   = document.getElementById('search-input');
    searchResults = document.getElementById('search-results');
    searchModal   = bootstrap.Modal.getOrCreateInstance(document.getElementById('searchModal'));

    messageInput.addEventListener('input', onInputChange);
    messageInput.addEventListener('keydown', onKeyDown);
    sendBtn.addEventListener('click', sendMessage);
    newChatBtn.addEventListener('click', startNewChat);
    modelSelect.addEventListener('change', () => localStorage.setItem('chat_model', modelSelect.value));
    searchBtn.addEventListener('click', openSearchModal);

    document.getElementById('searchModal').addEventListener('shown.bs.modal', () => searchInput.focus());
    document.getElementById('searchModal').addEventListener('hidden.bs.modal', () => {
        searchInput.value = '';
        searchResults.innerHTML = '<p class="text-secondary text-center small py-4 mb-0">Type to search…</p>';
        clearTimeout(searchDebounceTimer);
    });
    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounceTimer);
        const q = searchInput.value.trim();
        if (q.length < 2) {
            searchResults.innerHTML = '<p class="text-secondary text-center small py-4 mb-0">Type to search…</p>';
            return;
        }
        searchResults.innerHTML = '<p class="text-secondary text-center small py-4 mb-0"><span class="spinner-border spinner-border-sm me-2"></span>Searching…</p>';
        searchDebounceTimer = setTimeout(() => performSearch(q), 300);
    });

    document.addEventListener('keydown', e => {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            openSearchModal();
        }
    });

    messagesArea.addEventListener('scroll', () => {
        const threshold = 80;
        userScrolledUp = messagesArea.scrollTop + messagesArea.clientHeight < messagesArea.scrollHeight - threshold;
    });

    loadModels().then(() => {
        const initialUuid = document.body.dataset.sessionUuid;
        if (initialUuid) {
            loadSession(initialUuid);
        }
    });

    loadSessions();
});

// ===== MODELS =====
async function loadModels() {
    try {
        const res  = await fetch('/chat/api/models');
        const data = await res.json();
        const models = data.models || [];

        modelSelect.innerHTML = '';
        if (models.length === 0) {
            modelSelect.innerHTML = '<option value="">No models found</option>';
            return;
        }

        models.forEach(name => {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            modelSelect.appendChild(opt);
        });

        const saved = localStorage.getItem('chat_model');
        if (saved && models.includes(saved)) {
            modelSelect.value = saved;
        }
    } catch (e) {
        modelSelect.innerHTML = '<option value="">Ollama unavailable</option>';
    }
}

// ===== SESSIONS =====
async function loadSessions() {
    try {
        const res      = await fetch('/chat/api/sessions');
        const data     = await res.json();
        const sessions = (data.sessions || []).map(s => ({ ...s, pinned: +s.pinned }));
        renderSidebar(sessions);
    } catch (e) {
        console.error('Failed to load sessions', e);
    }
}

function renderSidebar(sessions) {
    chatList.innerHTML = '';

    const groups = groupByDate(sessions);
    const order  = ['Pinned', 'Today', 'Yesterday', 'Last 7 Days', 'Last 30 Days', 'Older'];

    for (const label of order) {
        const items = groups[label] || [];
        if (items.length === 0) continue;

        const header = document.createElement('p');
        header.className = 'px-3 mb-1 mt-3 text-uppercase fw-semibold text-secondary sidebar-section-label';
        header.textContent = label;
        chatList.appendChild(header);

        items.forEach(session => chatList.appendChild(buildSessionItem(session)));
    }
}

function buildSessionItem(session) {
    const item = document.createElement('div');
    item.className = 'chat-list-item d-flex align-items-center' + (session.uuid === currentSessionUuid ? ' active' : '');
    item.dataset.uuid = session.uuid;

    const pinHtml = session.pinned
        ? '<i class="bi bi-pin-fill text-warning me-1" style="font-size:0.7rem;flex-shrink:0;"></i>'
        : '';

    item.innerHTML = `
        <div class="d-flex align-items-center w-100 overflow-hidden">
            ${pinHtml}
            <span class="chat-item-title flex-grow-1">${escHtml(session.title)}</span>
            <div class="chat-item-actions ms-1">
                <button class="btn text-secondary btn-sm pin-btn" data-uuid="${escHtml(session.uuid)}" title="${session.pinned ? 'Unpin' : 'Pin'}">
                    <i class="bi bi-pin${session.pinned ? '-fill text-warning' : ''}"></i>
                </button>
                <button class="btn text-secondary btn-sm rename-btn" data-uuid="${escHtml(session.uuid)}" title="Rename">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn text-danger btn-sm delete-btn" data-uuid="${escHtml(session.uuid)}" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;

    item.addEventListener('click', e => {
        if (e.target.closest('.chat-item-actions')) return;
        loadSession(session.uuid);
        // Close mobile offcanvas
        const offcanvas = bootstrap?.Offcanvas?.getInstance(document.getElementById('chat-sidebar'));
        offcanvas?.hide();
    });

    item.querySelector('.pin-btn').addEventListener('click', e => {
        e.stopPropagation();
        togglePin(session.uuid, session.pinned);
    });

    item.querySelector('.rename-btn').addEventListener('click', e => {
        e.stopPropagation();
        renameSession(session.uuid, session.title);
    });

    item.querySelector('.delete-btn').addEventListener('click', e => {
        e.stopPropagation();
        deleteSession(session.uuid);
    });

    return item;
}

function groupByDate(sessions) {
    const now       = new Date();
    const today     = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today); yesterday.setDate(yesterday.getDate() - 1);
    const last7     = new Date(today); last7.setDate(last7.getDate() - 7);
    const last30    = new Date(today); last30.setDate(last30.getDate() - 30);

    const groups = { Pinned: [], Today: [], Yesterday: [], 'Last 7 Days': [], 'Last 30 Days': [], Older: [] };

    sessions.forEach(s => {
        if (s.pinned) { groups.Pinned.push(s); return; }
        const d = new Date(s.updated_at);
        if (d >= today)     groups.Today.push(s);
        else if (d >= yesterday) groups.Yesterday.push(s);
        else if (d >= last7)     groups['Last 7 Days'].push(s);
        else if (d >= last30)    groups['Last 30 Days'].push(s);
        else                     groups.Older.push(s);
    });

    return groups;
}

// ===== LOAD SESSION =====
async function loadSession(uuid) {
    try {
        const res = await fetch(`/chat/api/messages/${uuid}`);
        if (!res.ok) return;
        const data = await res.json();

        currentSessionUuid = uuid;
        window.history.pushState({}, '', `/chat/${uuid}`);

        if (data.session?.model) {
            modelSelect.value = data.session.model;
        }

        renderMessages(data.messages || []);
        updateSidebarActive(uuid);
    } catch (e) {
        console.error('Failed to load session', e);
    }
}

function renderMessages(messages) {
    welcomeScreen.classList.add('d-none');
    chatThread.classList.remove('d-none');
    chatThread.innerHTML = '';

    messages.forEach(msg => appendMessage(msg.role, msg.content, false, { model: msg.model, created_at: msg.created_at }));
    scrollToBottom(true);
}

// ===== SEND MESSAGE =====
async function sendMessage() {
    const message = messageInput.value.trim();
    if (!message || isStreaming) return;

    isStreaming    = true;
    userScrolledUp = false;
    sendBtn.disabled = true;

    messageInput.value = '';
    autoResizeTextarea();

    welcomeScreen.classList.add('d-none');
    chatThread.classList.remove('d-none');

    appendMessage('user', message);

    const assistantDiv = document.createElement('div');
    assistantDiv.className = 'message message-assistant';
    assistantDiv.innerHTML = '<div class="message-bubble"><span class="typing-indicator"><span></span><span></span><span></span></span></div>';
    chatThread.appendChild(assistantDiv);
    scrollToBottom();

    const bubble = assistantDiv.querySelector('.message-bubble');
    let assistantContent = '';
    let firstChunk       = true;

    try {
        const response = await fetch('/chat/api/stream', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                session_uuid: currentSessionUuid,
                message,
                model: modelSelect.value,
            }),
        });

        if (!response.ok || !response.body) {
            bubble.innerHTML = '<span class="text-danger">Request failed.</span>';
            return;
        }

        const reader  = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer    = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const events = buffer.split('\n\n');
            buffer = events.pop();

            for (const block of events) {
                const lines     = block.split('\n');
                let eventType   = 'message';
                let eventData   = '';

                for (const line of lines) {
                    if (line.startsWith('event: '))      eventType = line.slice(7).trim();
                    else if (line.startsWith('data: '))  eventData = line.slice(6);
                }

                if (!eventData) continue;

                let parsed;
                try { parsed = JSON.parse(eventData); } catch { continue; }

                if (eventType === 'session') {
                    currentSessionUuid = parsed.uuid;
                    window.history.pushState({}, '', `/chat/${parsed.uuid}`);
                    loadSessions();
                } else if (eventType === 'error') {
                    bubble.innerHTML = `<span class="text-danger">${escHtml(parsed.error ?? 'Unknown error')}</span>`;
                } else if (eventType === 'done') {
                    bubble.innerHTML = renderBubbleHtml(assistantContent, false);
                    addCopyButtons(bubble);
                    applyHighlighting(bubble);
                    assistantDiv.dataset.markdown = parseThinking(assistantContent).response || assistantContent;
                    appendMessageFooter(assistantDiv, { model: parsed.model, created_at: parsed.created_at });
                } else if (parsed.content !== undefined) {
                    if (firstChunk) {
                        bubble.innerHTML = '';
                        firstChunk = false;
                    }
                    assistantContent += parsed.content;
                    bubble.innerHTML = renderBubbleHtml(assistantContent, true);
                    if (!userScrolledUp) scrollToBottom();
                }
            }
        }
    } catch (e) {
        bubble.innerHTML = `<span class="text-danger">Error: ${escHtml(e.message)}</span>`;
    } finally {
        isStreaming = false;
        updateSendBtn();
        scrollToBottom();
    }
}

// ===== NEW CHAT =====
function startNewChat() {
    currentSessionUuid = null;
    window.history.pushState({}, '', '/');
    welcomeScreen.classList.remove('d-none');
    chatThread.classList.add('d-none');
    chatThread.innerHTML = '';
    messageInput.value = '';
    autoResizeTextarea();
    updateSendBtn();
    updateSidebarActive(null);
    messageInput.focus();
}

// ===== SESSION ACTIONS =====
async function togglePin(uuid, currentPinned) {
    await fetch(`/chat/api/session/${uuid}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pinned: currentPinned ? 0 : 1 }),
    });
    loadSessions();
}

async function renameSession(uuid, currentTitle) {
    const newTitle = await showRenameModal(currentTitle);
    if (!newTitle || newTitle === currentTitle) return;

    await fetch(`/chat/api/session/${uuid}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: newTitle }),
    });
    loadSessions();
}

async function deleteSession(uuid) {
    const confirmed = await showDeleteModal();
    if (!confirmed) return;

    await fetch(`/chat/api/session/${uuid}`, { method: 'DELETE' });

    if (currentSessionUuid === uuid) startNewChat();
    loadSessions();
}

// ===== MODALS =====
function showDeleteModal() {
    return new Promise(resolve => {
        const modalEl    = document.getElementById('deleteModal');
        const modal      = bootstrap.Modal.getOrCreateInstance(modalEl);
        const confirmBtn = document.getElementById('deleteConfirmBtn');
        let settled      = false;

        function done(value) {
            if (settled) return;
            settled = true;
            confirmBtn.removeEventListener('click', onConfirm);
            resolve(value);
        }

        function onConfirm() {
            modal.hide();
            done(true);
        }

        confirmBtn.addEventListener('click', onConfirm);
        modalEl.addEventListener('hidden.bs.modal', () => done(false), { once: true });
        modal.show();
    });
}

function showRenameModal(currentTitle) {
    return new Promise(resolve => {
        const modalEl    = document.getElementById('renameModal');
        const modal      = bootstrap.Modal.getOrCreateInstance(modalEl);
        const input      = document.getElementById('renameInput');
        const confirmBtn = document.getElementById('renameConfirmBtn');
        let settled      = false;

        input.value = currentTitle;

        function done(value) {
            if (settled) return;
            settled = true;
            confirmBtn.removeEventListener('click', onConfirm);
            input.removeEventListener('keydown', onKeydown);
            resolve(value);
        }

        function onConfirm() {
            const title = input.value.trim();
            modal.hide();
            done(title || null);
        }

        function onKeydown(e) {
            if (e.key === 'Enter') { e.preventDefault(); onConfirm(); }
        }

        confirmBtn.addEventListener('click', onConfirm);
        input.addEventListener('keydown', onKeydown);
        modalEl.addEventListener('shown.bs.modal', () => input.select(), { once: true });
        modalEl.addEventListener('hidden.bs.modal', () => done(null), { once: true });
        modal.show();
    });
}

// ===== SEARCH =====
function openSearchModal() {
    searchModal.show();
}

async function performSearch(q) {
    try {
        const res  = await fetch('/chat/api/search?q=' + encodeURIComponent(q));
        const data = await res.json();
        renderSearchResults(data.results || [], q);
    } catch (e) {
        searchResults.innerHTML = '<p class="text-danger text-center small py-4 mb-0">Search failed.</p>';
    }
}

function renderSearchResults(results, q) {
    if (results.length === 0) {
        searchResults.innerHTML = '<p class="text-secondary text-center small py-4 mb-0">No results found.</p>';
        return;
    }

    const titleMatches   = results.filter(r => r.title.toLowerCase().includes(q.toLowerCase()));
    const messageMatches = results.filter(r => r.snippet && !r.title.toLowerCase().includes(q.toLowerCase()));

    const frag = document.createDocumentFragment();

    function addGroup(label, items) {
        if (items.length === 0) return;
        const header = document.createElement('p');
        header.className = 'search-section-label mb-0';
        header.textContent = label;
        frag.appendChild(header);
        items.forEach(r => frag.appendChild(buildSearchResultItem(r, q)));
    }

    if (titleMatches.length > 0 && messageMatches.length > 0) {
        addGroup('Conversations', titleMatches);
        addGroup('Messages', messageMatches);
    } else {
        results.forEach(r => frag.appendChild(buildSearchResultItem(r, q)));
    }

    searchResults.innerHTML = '';
    searchResults.appendChild(frag);
}

function buildSearchResultItem(result, q) {
    const item = document.createElement('div');
    item.className = 'search-result-item';

    const date = new Date(result.updated_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

    const snippetHtml = result.snippet
        ? `<div class="search-result-snippet">${highlightMatch(result.snippet, q)}</div>`
        : '';

    item.innerHTML = `
        <div class="search-result-title">${highlightMatch(result.title, q)}</div>
        <div class="search-result-meta">${escHtml(date)}</div>
        ${snippetHtml}
    `;

    item.addEventListener('click', () => {
        searchModal.hide();
        loadSession(result.uuid);
        const offcanvas = bootstrap?.Offcanvas?.getInstance(document.getElementById('chat-sidebar'));
        offcanvas?.hide();
    });

    return item;
}

function highlightMatch(text, query) {
    if (!query) return escHtml(text);
    const terms = [...new Set(query.trim().split(/\s+/).filter(t => t.length >= 2))];
    if (terms.length === 0) return escHtml(text);
    const pattern  = terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
    const matchRe  = new RegExp(`^(${pattern})$`, 'i');
    return text.split(new RegExp(`(${pattern})`, 'gi'))
               .map(p => matchRe.test(p) ? `<mark>${escHtml(p)}</mark>` : escHtml(p))
               .join('');
}

// ===== THINKING =====
function parseThinking(raw) {
    const complete = raw.match(/^<think>([\s\S]*?)<\/think>([\s\S]*)$/);
    if (complete) {
        return { thinking: complete[1].trim(), response: complete[2].trimStart(), incomplete: false };
    }
    if (raw.startsWith('<think>')) {
        return { thinking: raw.slice(7), response: '', incomplete: true };
    }
    return { thinking: null, response: raw, incomplete: false };
}

function renderBubbleHtml(raw, streaming = false) {
    const { thinking, response, incomplete } = parseThinking(raw);
    let html = '';

    if (thinking !== null) {
        const inProgress = incomplete && streaming;
        const label = inProgress ? 'Thinking…' : 'Thinking';
        const cls   = inProgress ? ' thinking-in-progress' : '';
        html += `<details class="thinking-block${cls}"${incomplete ? ' open' : ''}>
            <summary class="thinking-summary">
                <i class="bi bi-lightbulb-fill"></i>
                <span class="thinking-summary-label">${label}</span>
                <i class="bi bi-chevron-down thinking-chevron"></i>
            </summary>
            <div class="thinking-content">${renderMarkdown(thinking)}</div>
        </details>`;
    }

    if (response) html += renderMarkdown(response);
    return html;
}

// ===== HELPERS =====
function appendMessage(role, content, animate = true, meta = null) {
    const div = document.createElement('div');
    div.className = `message message-${role}`;

    if (role === 'user') {
        div.innerHTML = `<div class="message-bubble">${escHtml(content)}</div>`;
    } else {
        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';
        bubble.innerHTML = renderBubbleHtml(content, false);
        addCopyButtons(bubble);
        applyHighlighting(bubble);
        div.appendChild(bubble);
        div.dataset.markdown = parseThinking(content).response || content;
        appendMessageFooter(div, meta);
    }

    chatThread.appendChild(div);
    if (animate && !userScrolledUp) scrollToBottom();
}

function renderMarkdown(text) {
    if (window.marked) {
        return window.marked.parse(text, { breaks: true, gfm: true });
    }
    return escHtml(text).replace(/\n/g, '<br>');
}

function addCopyButtons(container) {
    container.querySelectorAll('pre').forEach(pre => {
        if (pre.querySelector('.copy-code-btn')) return;
        const btn = document.createElement('button');
        btn.className = 'btn btn-sm btn-primary copy-code-btn';
        btn.innerHTML = '<i class="bi bi-clipboard"></i>';
        btn.title = 'Copy';
        btn.addEventListener('click', () => {
            const code = pre.querySelector('code')?.textContent ?? pre.textContent;
            navigator.clipboard.writeText(code).then(() => {
                btn.innerHTML = '<i class="bi bi-check text-dark"></i>';
                setTimeout(() => { btn.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 2000);
            });
        });
        pre.appendChild(btn);
    });
}

function appendMessageFooter(messageDiv, meta = null) {
    const footer  = document.createElement('div');
    footer.className = 'message-footer';

    const metaEl = document.createElement('div');
    metaEl.className = 'message-meta';
    if (meta?.model) {
        const s = document.createElement('span');
        s.textContent = meta.model;
        metaEl.appendChild(s);
    }
    if (meta?.created_at) {
        const d = new Date(String(meta.created_at).replace(' ', 'T'));
        const s = document.createElement('span');
        s.textContent = d.toLocaleString('en-GB', {
            day: 'numeric', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
        metaEl.appendChild(s);
    }
    footer.appendChild(metaEl);

    const actions = document.createElement('div');
    actions.className = 'message-actions';
    actions.appendChild(createCopyBtn('bi-clipboard', 'Copy Markdown', () => messageDiv.dataset.markdown ?? ''));
    footer.appendChild(actions);

    messageDiv.appendChild(footer);
}

function createCopyBtn(icon, label, getContent) {
    const btn = document.createElement('button');
    btn.className = 'btn btn-sm btn-primary';
    btn.title = label;
    btn.innerHTML = `<i class="bi ${icon}"></i>`;
    btn.addEventListener('click', () => {
        navigator.clipboard.writeText(getContent()).then(() => {
            btn.innerHTML = '<i class="bi bi-check text-dark"></i>';
            setTimeout(() => { btn.innerHTML = `<i class="bi ${icon}"></i>`; }, 2000);
        });
    });
    return btn;
}

function applyHighlighting(container) {
    if (!window.hljs) return;
    container.querySelectorAll('pre code').forEach(block => hljs.highlightElement(block));
}

function updateSidebarActive(uuid) {
    document.querySelectorAll('.chat-list-item').forEach(el => {
        el.classList.toggle('active', el.dataset.uuid === uuid);
    });
}

function scrollToBottom(force = false) {
    if (force || !userScrolledUp) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
}

function onInputChange() {
    autoResizeTextarea();
    updateSendBtn();
}

function onKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (!sendBtn.disabled) sendMessage();
    }
}

function autoResizeTextarea() {
    messageInput.style.height = 'auto';
    messageInput.style.height = Math.min(messageInput.scrollHeight, 200) + 'px';
}

function updateSendBtn() {
    sendBtn.disabled = !messageInput.value.trim() || isStreaming;
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
