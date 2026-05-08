<!doctype html>
<html lang="en-GB" data-bs-theme="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?= esc($title) ?> - <?= esc(config('App')->siteName) ?></title>
        <meta name="theme-color" content="#282A36">
        <!-- Favicon and touch icons -->
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/icon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/icon-16x16.png">
        <!-- Stylesheets Remote -->
        <link rel="stylesheet" href="<?= config('Urls')->assets ?>assets/css/vendor/bootstrap.css"/>
        <link rel="stylesheet" href="<?= config('Urls')->assets ?>assets/css/vendor/bootstrap-icons.css"/>
        <!-- highlight.js -->
        <link rel="stylesheet" href="/assets/css/vendor/highlight-dracula.min.css">
        <!-- Stylesheets Local -->
         <link rel="stylesheet" href="/assets/css/templates/default.css"/>
        <link rel="stylesheet" href="/assets/css/templates/chat.css"/>
        <?php if(isset($css)): foreach ($css as $file): $cssPath = FCPATH . 'assets/css/' . $file . '.css'; ?>
        <link rel="stylesheet" href="/assets/css/<?= $file ?>.css<?= file_exists($cssPath) ? '?v=' . filemtime($cssPath) : '' ?>">
        <?php endforeach; endif; ?>
        <!-- JavaScript Remote -->
        <script defer src="<?= config('Urls')->assets ?>assets/js/vendor/bootstrap.bundle.min.js"></script>
        <script defer src="<?= config('Urls')->assets ?>assets/js/shared/logout.js"></script>
        <script defer src="<?= config('Urls')->assets ?>assets/js/shared/appmenu.js"></script>
        <script defer src="<?= config('Urls')->assets ?>assets/js/shared/metrics.js"></script>
        <?php if( session()->get('user_uuid') ): ?>
        <script defer src="<?= config('Urls')->assets ?>assets/js/shared/notifications.js"></script>
        <?php endif; ?>
        <!-- marked.js + highlight.js -->
        <script defer src="/assets/js/vendor/marked.min.js"></script>
        <script defer src="/assets/js/vendor/highlight.min.js"></script>
        <!-- JavaScript Local -->
        <?php if(isset($js)): foreach ($js as $file): $jsPath = FCPATH . 'assets/js/' . $file . '.js'; ?>
        <script defer src="/assets/js/<?= $file ?>.js<?= file_exists($jsPath) ? '?v=' . filemtime($jsPath) : '' ?>"></script>
        <?php endforeach; endif; ?>
    </head>
    <body class="d-flex flex-column vh-100" data-session-uuid="<?= esc($uuid ?? '') ?>">
        <!-- Skip link -->
        <a class="visually-hidden-focusable" href="#main">Skip to main content</a>

        <!-- NAVBAR -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom sticky-top shadow py-0">
            <div class="container-fluid px-0">
                <a class="navbar-brand d-flex align-items-center gap-2 ms-3" href="<?= site_url() ?>">
                    <img src="/icon.svg" alt="Logo" width="45" height="45" class="d-inline-block align-text-top rounded-circle my-1">
                    Philip Newborough
                </a>

                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#chat-sidebar" aria-controls="chat-sidebar" aria-label="Toggle sidebar">
                        <i class="bi bi-layout-sidebar"></i>
                    </button>

                    <button class="btn btn-outline-primary btn d-lg-none me-3 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                </div>

                <div class="collapse navbar-collapse" id="topNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item topnav-item">
                            <a class="nav-link text-white-50 py-3 py-lg-0 px-3" href="<?= config('Urls')->tld ?>"><i class="bi bi-house-fill me-1"></i> Homepage</a>
                        </li>
                        <?php if( session()->get('is_admin') ): ?>
                        <li class="nav-item topnav-item">
                            <a class="nav-link text-white-50 py-3 py-lg-0 px-3" href="<?= config('Urls')->startpage ?>"><i class="bi bi-slash-square-fill me-1"></i> Startpage</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item topnav-item">
                            <a data-api-url="<?= config('Urls')->appmenu ?>" class="nav-link text-white-50 py-3 py-lg-0 px-3 trigger-appmenu" href="#"><i class="bi bi-grid-3x3-gap-fill me-1"></i> App Menu</a>
                        </li>
                        <?php if( session()->get('is_admin') ): ?>
                        <li class="nav-item topnav-item">
                            <a class="nav-link text-white-50 py-3 py-lg-0 px-3" href="/admin"><i class="bi bi-shield-lock-fill me-1"></i> Admin</a>
                        </li>
                        <?php endif; ?>
                        <?php if( session()->get('user_uuid') ): ?>
                        <li class="nav-item topnav-item">
                            <a data-api-url="<?= config('Urls')->notifications ?>" class="nav-link text-white-50 py-3 py-lg-0 px-3 trigger-notifications" href="#"><i id="notification-bell" class="bi bi-bell-fill me-1"></i><span class="d-lg-none me-1"> Notifications</span></a>
                        </li>
                        <li class="nav-item topnav-item">
                            <a class="nav-link text-white-50 py-3 py-lg-0 px-3 trigger-logout" href="#"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item topnav-item">
                            <a class="nav-link text-white-50 py-3 py-lg-0 px-3" href="<?= config('Urls')->auth ?>login?redirect=<?= urlencode(current_url()) ?>"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- /NAVBAR -->

        <!-- Page wrapper -->
        <div class="d-flex flex-grow-1 overflow-hidden">

            <!-- CHAT SIDEBAR -->
            <div class="offcanvas-lg offcanvas-start chat-sidebar bg-dark border-end flex-shrink-0 d-flex flex-column" tabindex="-1" id="chat-sidebar" aria-labelledby="chatSidebarLabel">
                <div class="offcanvas-header d-lg-none border-bottom">
                    <h5 class="offcanvas-title text-white" id="chatSidebarLabel">Conversations</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#chat-sidebar" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body p-0 d-flex flex-column overflow-hidden">
                    <!-- Controls -->
                    <div class="p-3 border-bottom flex-shrink-0">
                        <div class="d-flex gap-2 mb-3">
                            <button id="new-chat-btn" class="btn btn-outline-primary flex-grow-1">
                                <i class="bi bi-plus-lg me-1"></i> New Chat
                            </button>
                            <button id="search-btn" class="btn btn-outline-secondary" title="Search conversations">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <label for="model-select" class="form-label small text-secondary mb-1">Model</label>
                        <select id="model-select" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="">Loading models…</option>
                        </select>
                    </div>
                    <!-- Conversation list -->
                    <nav id="chat-list" class="flex-grow-1 overflow-y-auto py-2" aria-label="Conversation history">
                    </nav>
                </div>
            </div>
            <!-- /CHAT SIDEBAR -->

            <!-- MAIN CONTENT -->
            <main id="main" class="d-flex flex-column flex-grow-1 overflow-hidden">
                <?= $this->renderSection('content') ?>
            </main>
            <!-- /MAIN CONTENT -->

        </div>
        <!-- /Page wrapper -->
    </body>
</html>
