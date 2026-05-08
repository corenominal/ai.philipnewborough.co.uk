<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-3">

    <div class="border-bottom border-1 mb-4 pb-3">
        <h2 class="mb-0">Admin Dashboard</h2>
        <p class="text-secondary mb-0 mt-1" id="dashboard-greeting"></p>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100 border-primary border-opacity-50">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Sessions</div>
                            <div class="fs-2 fw-bold"><?= $stats['sessions'] ?></div>
                        </div>
                        <i class="bi bi-chat-dots-fill fs-3 text-primary opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-info border-opacity-50">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Messages</div>
                            <div class="fs-2 fw-bold"><?= $stats['messages'] ?></div>
                        </div>
                        <i class="bi bi-chat-left-text-fill fs-3 text-info opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-warning border-opacity-50">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Pinned</div>
                            <div class="fs-2 fw-bold"><?= $stats['pinned'] ?></div>
                        </div>
                        <i class="bi bi-pin-fill fs-3 text-warning opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-success border-opacity-50">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Avg / Session</div>
                            <div class="fs-2 fw-bold"><?= $stats['avg'] ?></div>
                        </div>
                        <i class="bi bi-bar-chart-fill fs-3 text-success opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        <!-- Recent sessions -->
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Recent Sessions</span>
                    <a href="/" class="btn btn-sm btn-outline-primary"><i class="bi bi-chat-dots me-1"></i>Open Chat</a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recent_sessions)): ?>
                    <div class="text-center text-secondary py-5">
                        <i class="bi bi-chat-dots fs-1 d-block mb-2 opacity-25"></i>
                        No chat sessions yet.
                    </div>
                    <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recent_sessions as $session): ?>
                        <li class="list-group-item d-flex align-items-center gap-3 py-2">
                            <i class="bi bi-chat-dots text-secondary flex-shrink-0"></i>
                            <div class="flex-grow-1 text-truncate">
                                <a href="/chat/<?= esc($session['uuid']) ?>" class="text-decoration-none text-body">
                                    <?= esc($session['title'] ?? 'Untitled Session') ?>
                                </a>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <?php if (!empty($session['model'])): ?>
                                <span class="badge text-bg-secondary font-monospace" style="font-size:0.7em"><?= esc($session['model']) ?></span>
                                <?php endif; ?>
                                <?php if ($session['pinned']): ?>
                                <i class="bi bi-pin-fill text-warning" title="Pinned"></i>
                                <?php endif; ?>
                                <span class="text-secondary small"><?= date('d M y', strtotime($session['created_at'])) ?></span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div class="col-12 col-lg-4 d-flex flex-column gap-3">

            <!-- Quick actions -->
            <div class="card">
                <div class="card-header fw-semibold">
                    <i class="bi bi-lightning-fill me-2"></i>Quick Actions
                </div>
                <div class="list-group list-group-flush">
                    <a href="/debug" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-bug text-secondary"></i> Debug Panel
                    </a>
                    <a href="<?= config('Urls')->logs ?>admin?search=<?= urlencode($_SERVER['HTTP_HOST'] ?? '') ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-journal-text text-secondary"></i> Event Log <i class="bi bi-box-arrow-up-right ms-auto small text-secondary"></i>
                    </a>
                    <a href="<?= config('Urls')->github ?>blob/main/README.md" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-file-text text-secondary"></i> README.md <i class="bi bi-box-arrow-up-right ms-auto small text-secondary"></i>
                    </a>
                    <a href="<?= config('Urls')->github ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                        <i class="bi bi-github text-secondary"></i> GitHub <i class="bi bi-box-arrow-up-right ms-auto small text-secondary"></i>
                    </a>
                </div>
            </div>

            <!-- System info -->
            <div class="card">
                <div class="card-header fw-semibold">
                    <i class="bi bi-server me-2"></i>System
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">Hostname</span>
                        <span class="badge text-bg-dark font-monospace"><?= esc(gethostname()) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">PHP</span>
                        <span class="badge text-bg-primary"><?= phpversion() ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">CodeIgniter</span>
                        <span class="badge text-bg-info text-dark"><?= \CodeIgniter\CodeIgniter::CI_VERSION ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">Environment</span>
                        <span class="badge text-bg-<?= ENVIRONMENT === 'production' ? 'success' : 'warning text-dark' ?>"><?= esc(ENVIRONMENT) ?></span>
                    </li>
                </ul>
            </div>

        </div>
    </div>

</div>
<?= $this->endSection() ?>
