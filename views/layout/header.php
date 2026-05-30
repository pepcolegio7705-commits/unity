<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sintek-Unity</title>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary: #eab308; /* Yellow 500 */
            --primary-hover: #ca8a04; /* Yellow 600 */
            --secondary: #3b82f6; /* Blue 500 */
            --bg-body: #050505; /* Black */
            --bg-sidebar: #0f172a; /* Very Dark Blue */
            --text-main: #f8fafc; /* Slate 50 */
            --text-muted: #94a3b8; /* Slate 400 */
            --border-color: #1e293b; /* Slate 800 */
            --gradient-accent: linear-gradient(135deg, var(--secondary), var(--primary));
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding-top: 60px; /* Navbar height */
        }

        /* Navbar */
        .navbar { 
            background: var(--bg-sidebar);
            border-bottom: 1px solid var(--border-color);
            height: 60px;
        }
        .navbar-brand {
            font-weight: 800;
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }
        .navbar-brand img {
            height: 30px;
            margin-right: 10px;
        }

        /* Layout */
        .main-wrapper { flex: 1; display: flex; flex-direction: column; }
        .row-wrapper { flex: 1; display: flex; }
        
        /* Sidebar */
        .sidebar { 
            position: fixed; 
            top: 60px; 
            bottom: 0; 
            left: 0; 
            z-index: 100; 
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            width: 240px;
        }
        .sidebar-sticky { 
            height: calc(100vh - 60px); 
            padding: 1rem 0; 
            overflow-x: hidden; 
            overflow-y: auto; 
        }
        .nav-link { 
            color: var(--text-muted); 
            font-weight: 500; 
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .nav-link:hover { 
            color: var(--text-main); 
            background: rgba(59, 130, 246, 0.1); /* Blue tint */
        }
        .nav-link.active { 
            color: var(--primary); 
            background: rgba(234, 179, 8, 0.1); /* Yellow tint */
            border-right: 3px solid var(--primary);
        }

        /* Content Area */
        main.content {
            margin-left: 240px; /* Sidebar width */
            padding: 2rem;
            width: calc(100% - 240px);
        }

        /* Tarjetas (Cards) */
        .card { 
            background: var(--bg-sidebar); 
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            margin-bottom: 1.5rem;
            color: var(--text-main);
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }
        .card-body {
            padding: 1.5rem;
        }

        /* Botones primarios (Gradiente) */
        .btn-primary {
            background: var(--gradient-accent) !important;
            border: none;
            color: #000 !important; /* Letra oscura para contrastar amarillo */
            font-weight: 700;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }

        /* Títulos */
        h1, h2, h3, h5, h6 {
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }
        .page-title {
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }

        /* Modals and Forms */
        .modal-content {
            background-color: var(--bg-sidebar);
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }
        .modal-header, .modal-footer {
            border-color: var(--border-color) !important;
        }
        .modal-title {
            color: var(--text-main);
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .form-control, .form-select {
            background-color: var(--bg-body) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-main) !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2) !important;
        }
        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        /* DataTables Customization */
        .table {
            color: var(--text-main);
        }
        .table thead th {
            background-color: var(--bg-body);
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 2px solid var(--border-color) !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .table td {
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            background-color: transparent !important;
            color: var(--text-main) !important;
        }
        .table-hover > tbody > tr:hover > * {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: var(--text-main);
        }

        /* Footer */
        .footer-system {
            background: var(--bg-sidebar);
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-left: 240px; /* Igual que el main content */
        }
        .footer-system a {
            color: var(--secondary);
            transition: color 0.2s;
        }
        .footer-system a:hover {
            color: var(--primary) !important;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.show { transform: translateX(0); }
            main.content { margin-left: 0; width: 100%; padding: 1rem; }
            .footer-system { margin-left: 0; }
        }
    </style>
</head>
<body>
    <header class="navbar navbar-expand-md fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="?page=dashboard">
                <img src="assets/img/logo.png?v=<?php echo time(); ?>" alt="Sintek-Unity Logo">
                Sintek-Unity
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars text-dark"></i>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarCollapse">
                <ul class="navbar-nav align-items-center mb-2 mb-md-0">
                    <li class="nav-item me-4 d-none d-lg-flex align-items-center text-muted small">
                        <span class="me-3">&copy; <?php echo date('Y'); ?> <strong>Sintek Gestión</strong></span>
                        <span class="me-3"><i class="fa-solid fa-phone me-1"></i> +54 0280154847619</span>
                        <a href="https://www.youtube.com/@sintek-gestion" target="_blank" rel="noopener noreferrer" class="text-muted hover-white" title="YouTube Sintek Gestión">
                            <i class="fa-brands fa-youtube text-danger fs-5"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-danger d-flex align-items-center" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Salir</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="main-wrapper">
        <div class="row-wrapper">
            <nav id="sidebarMenu" class="sidebar collapse d-md-block">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <?php 
                        $rol_sidebar = $_SESSION['rol'] ?? 'Invitado';
                        $is_admin_sidebar = in_array($rol_sidebar, ['Administrador', 'Secretario', 'Preceptor', 'Directivo']);
                        ?>

                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'dashboard') ? 'active' : ''; ?>" href="?page=dashboard">
                                <i class="fa-solid fa-house"></i> Inicio
                            </a>
                        </li>
                        
                        <?php if ($rol_sidebar === 'Docente'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'mis_materias') ? 'active' : ''; ?>" href="?page=mis_materias">
                                <i class="fa-solid fa-book-bookmark"></i> Mis Materias
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if ($is_admin_sidebar): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'alumnos') ? 'active' : ''; ?>" href="?page=alumnos">
                                <i class="fa-solid fa-user-graduate"></i> Alumnos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'docentes') ? 'active' : ''; ?>" href="?page=docentes">
                                <i class="fa-solid fa-chalkboard-user"></i> Docentes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'cursos') ? 'active' : ''; ?>" href="?page=cursos">
                                <i class="fa-solid fa-school"></i> Cursos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'materias') ? 'active' : ''; ?>" href="?page=materias">
                                <i class="fa-solid fa-book"></i> Materias
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'asistencias') ? 'active' : ''; ?>" href="?page=asistencias">
                                <i class="fa-solid fa-clipboard-user"></i> Asistencias
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'calificaciones') ? 'active' : ''; ?>" href="?page=calificaciones">
                                <i class="fa-solid fa-star"></i> Calificaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'instancias') ? 'active' : ''; ?>" href="?page=instancias">
                                <i class="fa-solid fa-calendar-check"></i> Instancias
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'reportes') ? 'active' : ''; ?>" href="?page=reportes">
                                <i class="fa-solid fa-chart-bar"></i> Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'analiticos') ? 'active' : ''; ?>" href="?page=analiticos">
                                <i class="fa-solid fa-file-signature"></i> Libro Matriz
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'usuarios') ? 'active' : ''; ?>" href="?page=usuarios">
                                <i class="fa-solid fa-users"></i> Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'configuracion') ? 'active' : ''; ?>" href="?page=configuracion">
                                <i class="fa-solid fa-gear"></i> Configuración
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'respaldos') ? 'active' : ''; ?>" href="?page=respaldos">
                                <i class="fa-solid fa-database"></i> Respaldos
                            </a>
                        </li>
                        <?php endif; ?>
                        <!-- Aquí irán los siguientes módulos -->
                    </ul>
                </div>
            </nav>

            <main class="content">
