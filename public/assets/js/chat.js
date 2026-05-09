'use strict';

// ===== STATE =====
let currentSessionUuid = null;
let isStreaming         = false;
let userScrolledUp     = false;
let pendingImages      = []; // [{dataUrl, base64, name}]

// ===== DOM REFS =====
let messagesArea, chatThread, welcomeScreen;
let messageInput, sendBtn, chatList, modelSelect, newChatBtn, searchBtn;
let searchModal, searchInput, searchResults;
let attachBtn, imageInput, imagePreviewArea;
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

    attachBtn        = document.getElementById('attach-btn');
    imageInput       = document.getElementById('image-input');
    imagePreviewArea = document.getElementById('image-preview-area');

    messageInput.addEventListener('input', onInputChange);
    messageInput.addEventListener('keydown', onKeyDown);
    sendBtn.addEventListener('click', sendMessage);
    newChatBtn.addEventListener('click', startNewChat);
    modelSelect.addEventListener('change', () => localStorage.setItem('chat_model', modelSelect.value));
    searchBtn.addEventListener('click', openSearchModal);
    attachBtn.addEventListener('click', () => imageInput.click());
    imageInput.addEventListener('change', handleFileSelect);

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
                <button class="btn btn-sm chat-item-menu-btn" type="button" aria-haspopup="true" aria-expanded="false" title="More actions">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="chat-item-menu" role="menu">
                    <button class="chat-item-menu-item pin-btn" type="button" role="menuitem">
                        <i class="bi bi-pin${session.pinned ? '-fill text-warning' : ''} me-2"></i>${session.pinned ? 'Unpin' : 'Pin'}
                    </button>
                    <button class="chat-item-menu-item rename-btn" type="button" role="menuitem">
                        <i class="bi bi-pencil me-2"></i>Rename
                    </button>
                    <div class="chat-item-menu-divider"></div>
                    <button class="chat-item-menu-item text-danger delete-btn" type="button" role="menuitem">
                        <i class="bi bi-trash me-2"></i>Delete
                    </button>
                </div>
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

    const menuBtn = item.querySelector('.chat-item-menu-btn');
    const menu    = item.querySelector('.chat-item-menu');

    menuBtn.addEventListener('click', e => {
        e.stopPropagation();
        const isOpen = menu.classList.contains('show');
        closeAllChatItemMenus();
        if (!isOpen) openChatItemMenu(menuBtn, menu);
    });

    item.querySelector('.pin-btn').addEventListener('click', e => {
        e.stopPropagation();
        closeAllChatItemMenus();
        togglePin(session.uuid, session.pinned);
    });

    item.querySelector('.rename-btn').addEventListener('click', e => {
        e.stopPropagation();
        closeAllChatItemMenus();
        renameSession(session.uuid, session.title);
    });

    item.querySelector('.delete-btn').addEventListener('click', e => {
        e.stopPropagation();
        closeAllChatItemMenus();
        deleteSession(session.uuid);
    });

    return item;
}

function openChatItemMenu(btn, menu) {
    document.body.appendChild(menu);
    menu.classList.add('show');
    btn.setAttribute('aria-expanded', 'true');

    const rect = btn.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    let top  = rect.bottom + 4;
    let left = rect.right - menuRect.width;
    if (left < 8) left = 8;
    if (top + menuRect.height > window.innerHeight - 8) {
        top = rect.top - menuRect.height - 4;
    }
    menu.style.top  = `${top}px`;
    menu.style.left = `${left}px`;

    menu._ownerBtn = btn;
}

function closeAllChatItemMenus() {
    document.querySelectorAll('.chat-item-menu.show').forEach(menu => {
        menu.classList.remove('show');
        menu.style.top  = '';
        menu.style.left = '';
        if (menu._ownerBtn) {
            menu._ownerBtn.setAttribute('aria-expanded', 'false');
            const actions = menu._ownerBtn.closest('.chat-item-actions');
            if (actions) actions.appendChild(menu);
            menu._ownerBtn = null;
        }
    });
}

document.addEventListener('click', e => {
    if (!e.target.closest('.chat-item-menu') && !e.target.closest('.chat-item-menu-btn')) {
        closeAllChatItemMenus();
    }
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeAllChatItemMenus();
});
window.addEventListener('resize', closeAllChatItemMenus);
window.addEventListener('scroll', closeAllChatItemMenus, true);

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

    messages.forEach(msg => appendMessage(msg.role, msg.content, false, { model: msg.model, created_at: msg.created_at }, msg.images || []));
    scrollToBottom(true);
}

// ===== SEND MESSAGE =====
async function sendMessage() {
    const message = messageInput.value.trim();
    if ((!message && pendingImages.length === 0) || isStreaming) return;

    isStreaming    = true;
    userScrolledUp = false;
    updateSendBtn();

    const imagesToSend = [...pendingImages];
    pendingImages = [];
    renderImagePreview();

    messageInput.value = '';
    autoResizeTextarea();

    welcomeScreen.classList.add('d-none');
    chatThread.classList.remove('d-none');

    appendMessage('user', message, true, null, imagesToSend.map(img => img.dataUrl));

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
                images: imagesToSend.map(img => img.dataUrl),
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
                    const cursor = document.createElement('span');
                    cursor.className = 'streaming-cursor';
                    bubble.appendChild(cursor);
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

// ===== IMAGE HANDLING =====
function handleFileSelect(e) {
    const files = Array.from(e.target.files);
    e.target.value = '';
    files.forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = ev => {
            const dataUrl = ev.target.result;
            pendingImages.push({ dataUrl, base64: dataUrl.split(',')[1], name: file.name });
            renderImagePreview();
            updateSendBtn();
        };
        reader.readAsDataURL(file);
    });
}

function renderImagePreview() {
    if (pendingImages.length === 0) {
        imagePreviewArea.classList.add('d-none');
        imagePreviewArea.innerHTML = '';
        attachBtn.classList.remove('has-images');
        return;
    }
    imagePreviewArea.classList.remove('d-none');
    attachBtn.classList.add('has-images');
    imagePreviewArea.innerHTML = '';
    pendingImages.forEach((img, idx) => {
        const item    = document.createElement('div');
        item.className = 'image-preview-item';
        const imgEl   = document.createElement('img');
        imgEl.src     = img.dataUrl;
        imgEl.alt     = img.name;
        const rmBtn   = document.createElement('button');
        rmBtn.className = 'image-preview-remove';
        rmBtn.type      = 'button';
        rmBtn.title     = 'Remove';
        rmBtn.innerHTML = '<i class="bi bi-x"></i>';
        rmBtn.addEventListener('click', () => {
            pendingImages.splice(idx, 1);
            renderImagePreview();
            updateSendBtn();
        });
        item.appendChild(imgEl);
        item.appendChild(rmBtn);
        imagePreviewArea.appendChild(item);
    });
}

// ===== HELPERS =====
function appendMessage(role, content, animate = true, meta = null, images = []) {
    const div = document.createElement('div');
    div.className = `message message-${role}`;

    if (role === 'user') {
        let imagesHtml = '';
        if (images.length > 0) {
            imagesHtml = '<div class="message-images">' +
                images.map(src => `<img class="message-image mb-2" src="${escHtml(src)}" alt="Attached image" loading="lazy">`).join('') +
                '</div>';
        }
        div.innerHTML = `<div class="message-bubble">${imagesHtml}${content ? escHtml(content) : ''}</div>`;
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
    if (isStreaming) {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    } else {
        sendBtn.disabled = !messageInput.value.trim() && pendingImages.length === 0;
        sendBtn.innerHTML = '<i class="bi bi-arrow-up-short fs-5"></i>';
    }
}

// ===== LIGHTBOX =====
function openLightbox(src) {
    const overlay = document.createElement('div');
    overlay.className = 'lightbox-overlay';
    overlay.innerHTML = `<img class="lightbox-img" src="${escHtml(src)}" alt="Image">`;

    function close() {
        document.removeEventListener('keydown', onKey);
        overlay.remove();
    }

    function onKey(e) {
        if (e.key === 'Escape') close();
    }

    overlay.addEventListener('click', close);
    document.addEventListener('keydown', onKey);
    document.body.appendChild(overlay);
}

document.addEventListener('click', e => {
    const img = e.target.closest('.message-image');
    if (img) openLightbox(img.src);
});

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
