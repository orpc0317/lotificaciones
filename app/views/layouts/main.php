<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Lotificaciones' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php use App\Helpers\PathHelper; ?>
    <?= PathHelper::baseTag() ?>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables Buttons -->
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- DataTables ColReorder CSS -->
    <link href="https://cdn.datatables.net/colreorder/1.6.2/css/colReorder.dataTables.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Modern font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">
    <link href="assets/css/layout.css" rel="stylesheet">

    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        body { 
            background: var(--bg); 
            color: var(--text); 
            font-family: 'Inter', 'Segoe UI', Roboto, Arial, Helvetica, sans-serif;
            overflow-x: hidden;
        }
        body, .card, .modal-content, .btn, .nav-tabs .nav-link { 
            transition: background-color 220ms ease, color 220ms ease, border-color 220ms ease; 
        }
        .card { background: var(--card-bg); border: 1px solid var(--border); }
        .card-header { background: transparent; border-bottom: 1px solid var(--border); }
        .btn-primary { background: var(--primary-600); border-color: var(--primary-600); }
        .btn-outline-primary { color: var(--primary-600); border-color: var(--primary-600); }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="d-flex align-items-center">
                    <i class="bi bi-building-fill text-white fs-4 me-2" id="appIcon"></i>
                    <span class="sidebar-title text-white fw-bold" id="sidebarTitle">Lotificaciones</span>
                </div>
                <button class="btn btn-sm btn-link text-white p-0" id="toggleSidebar" title="Contraer menú">
                    <i class="bi bi-chevron-left fs-5"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav flex-column" id="mainMenu">
                    <!-- Menu items will be generated here -->
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button class="btn btn-sm btn-link d-lg-none" id="toggleSidebarMobile">
                            <i class="bi bi-list fs-4"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <!-- Theme palette dropdown -->
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center" type="button" id="paletteDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-palette me-1"></i>
                                <span id="currentPaletteName" data-i18n="theme_blue">Blue</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="paletteDropdown">
                                <li>
                                    <a class="dropdown-item palette-option" href="#" data-palette="blue">
                                        <span class="palette-color-dot" style="background: var(--swatch-blue);"></span>
                                        <span data-i18n="theme_blue">Blue</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item palette-option" href="#" data-palette="teal">
                                        <span class="palette-color-dot" style="background: var(--swatch-teal);"></span>
                                        <span data-i18n="theme_teal">Verde</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item palette-option" href="#" data-palette="violet">
                                        <span class="palette-color-dot" style="background: var(--swatch-violet);"></span>
                                        <span data-i18n="theme_violet">Violet</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- Language selector -->
                        <select id="langSelect" class="form-select form-select-sm" style="width:auto;">
                            <option value="es">Español</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                <?php if (isset($content)) echo $content; ?>
            </main>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <!-- DataTables ColReorder -->
    <script src="https://cdn.datatables.net/colreorder/1.6.2/js/dataTables.colReorder.min.js"></script>
    <!-- SheetJS for XLSX export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Application Constants -->
    <script src="assets/js/constants.js"></script>

    <!-- i18n Module -->
    <script src="assets/js/modules/i18n.js"></script>

    <!-- Theme Management -->
    <script src="assets/js/theme.js"></script>

    <!-- Layout Script -->
    <script src="assets/js/layout.js"></script>

    <?php if (isset($scripts)) echo $scripts; ?>
</body>
</html>
