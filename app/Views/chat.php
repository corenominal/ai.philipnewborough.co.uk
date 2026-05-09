<?= $this->extend('templates/chat') ?>

<?= $this->section('content') ?>
<div id="messages-area" class="flex-grow-1 overflow-y-auto px-3 py-4">
    <div id="welcome-screen" class="chat-welcome d-flex flex-column align-items-center justify-content-center h-100">
        <div class="text-center">
            <i class="bi bi-chat-dots text-secondary" style="font-size: 3.5rem;"></i>
            <h4 class="mt-3 text-secondary fw-normal">How can I help you today?</h4>
            <p class="text-secondary small">Select a model and start typing below.</p>
        </div>
    </div>
    <div id="chat-thread" class="chat-thread d-none mx-auto"></div>
</div>

<div id="input-area" class="flex-shrink-0 border-top px-3 py-3">
    <input type="file" id="image-input" accept="image/*" multiple style="display:none">
    <div class="chat-input-wrapper mx-auto">
        <div id="image-preview-area" class="image-preview-area d-none mb-2"></div>
        <div class="position-relative">
            <button id="attach-btn" class="btn chat-attach-btn" type="button" title="Attach image" aria-label="Attach image">
                <i class="bi bi-paperclip"></i>
            </button>
            <textarea
                id="message-input"
                class="form-control chat-textarea"
                placeholder="Message…"
                rows="1"
                aria-label="Chat message"
            ></textarea>
            <button id="send-btn" class="btn btn-primary chat-send-btn" disabled aria-label="Send message">
                <i class="bi bi-arrow-up-short fs-5"></i>
            </button>
        </div>
    </div>
    <p class="text-center text-secondary small mt-2 mb-0">
        <small>Press <kbd>Enter</kbd> to send &middot; <kbd>Shift+Enter</kbd> for new line</small>
    </p>
</div>
<!-- Search modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-label="Search conversations" aria-hidden="true">
    <div class="modal-dialog search-modal-dialog">
        <div class="modal-content shadow-lg">
            <div class="p-3 border-bottom">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0 ps-1 pe-2 text-secondary">
                        <i class="bi bi-search fs-5"></i>
                    </span>
                    <input type="search" id="search-input"
                           class="form-control form-control-lg border-0 shadow-none bg-transparent text-white ps-0"
                           placeholder="Search conversations and messages…"
                           autocomplete="off" spellcheck="false">
                    <button type="button" class="btn-close btn-close-white my-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div id="search-results" class="search-results-body">
                <p class="text-secondary text-center small py-4 mb-0">Type to search…</p>
            </div>
        </div>
    </div>
</div>

<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="deleteModalLabel"><i class="bi bi-trash me-2 text-danger"></i>Delete Conversation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-secondary">
                Are you sure you want to delete this conversation? This cannot be undone.
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteConfirmBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Rename modal -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="renameModalLabel"><i class="bi bi-pencil me-2"></i>Rename Conversation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" id="renameInput" placeholder="Conversation name" maxlength="255">
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="renameConfirmBtn">Rename</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
