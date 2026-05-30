<?php
require_once 'config/security.php';
session_start();

// Validar inicio de sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$page = $_GET['page'] ?? 'dashboard';
$rol = $_SESSION['rol'] ?? 'Invitado';

// Páginas permitidas según el rol
if ($rol === 'Docente') {
    $allowed_pages = [
        'dashboard',
        'mis_materias',
        'aula_docente'
    ];
} else {
    $allowed_pages = [
        'dashboard',
        'alumnos',
        'docentes',
        'cursos',
        'materias',
        'asistencias',
        'calificaciones',
        'instancias',
        'reportes',
        'analiticos',
        'usuarios',
        'configuracion',
        'respaldos',
        'imprimir_asistencia'
    ];
}

if (!in_array($page, $allowed_pages)) {
    // Si la página no está en las permitidas, cargamos la vista heredada si existe
    // Esto permite la migración incremental
    $legacy_file = $page . '.php';
    if (file_exists($legacy_file) && $page !== 'index' && $page !== 'login') {
        require_once $legacy_file;
        exit;
    }
    $page = 'dashboard'; // fallback
}

// Cargar Header (solo para módulos MVC migrados y no para impresión)
if ($page !== 'imprimir_asistencia') {
    require_once 'views/layout/header.php';
}

// Cargar vista del módulo MVC
$view_path = 'views/pages/' . $page . '.php';
if (file_exists($view_path)) {
    require_once $view_path;
} else {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Módulo en construcción o no encontrado.</div></div>";
}

// Cargar Footer
if ($page !== 'imprimir_asistencia') {
    require_once 'views/layout/footer.php';
}
?>
