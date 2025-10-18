<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Lotificaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php use App\Helpers\PathHelper; ?>
    <?= PathHelper::baseTag() ?>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }
        .welcome-section {
            max-width: 1200px;
            margin: 0 auto;
        }
        .welcome-card {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
        }
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
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
                <div class="welcome-section">
                    <div class="welcome-card">
                        <h1 class="mb-3">
                            <i class="bi bi-house-door-fill text-primary me-2"></i>
                            Bienvenido a Lotificaciones
                        </h1>
                        <p class="lead text-muted">Sistema de gestión integral para tu empresa</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <i class="bi bi-people-fill"></i>
                                <h3>0</h3>
                                <p>Empleados Registrados</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card" style="background: linear-gradient(135deg, #14b8a6, #0d9488);">
                                <i class="bi bi-folder-fill"></i>
                                <h3>1</h3>
                                <p>Módulos Disponibles</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">
                                <i class="bi bi-speedometer2"></i>
                                <h3>100%</h3>
                                <p>Sistema Activo</p>
                            </div>
                        </div>
                    </div>

                    <div class="welcome-card mt-4">
                        <h3 class="mb-3">Acceso Rápido</h3>
                        <div class="d-flex gap-3 flex-wrap">
                            <a href="empleados" class="btn btn-primary">
                                <i class="bi bi-people-fill me-2"></i>
                                Gestión de Empleados
                            </a>
                        </div>
                    </div>

                    <div class="welcome-card">
                        <h4>Instrucciones</h4>
                        <p class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i>Utiliza el menú lateral para navegar entre los diferentes módulos</p>
                        <p class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i>Puedes contraer el menú haciendo clic en el ícono de flecha</p>
                        <p class="mb-2"><i class="bi bi-arrow-right-circle text-primary me-2"></i>Cambia el idioma usando el selector en la parte superior</p>
                        <p class="mb-0"><i class="bi bi-arrow-right-circle text-primary me-2"></i>Personaliza el tema usando los botones de paleta de colores</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- i18n Module -->
    <script src="assets/js/modules/i18n.js"></script>
    <!-- Theme Manager (must load before layout) -->
    <script src="assets/js/theme.js"></script>
    <!-- Layout Script -->
    <script src="assets/js/layout.js"></script>
</body>
</html>
