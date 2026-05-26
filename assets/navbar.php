<!-- Estilos para resaltar página activa -->
<style>
  .navbar-nav .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 0.25rem;
  }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top small">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><i class="bi bi-house-fill"></i> SGC - Unity</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContenido">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContenido">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <!-- INICIO -->
        <li class="nav-item">
          <a class="nav-link <?= $pagina === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
            <i class="bi bi-house-door-fill"></i> Inicio
          </a>
        </li>

        <!-- GESTIÓN DE PERSONAS -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($pagina, ['usuarios','alumnos','docentes']) ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-people-fill"></i> Personas
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item <?= $pagina === 'usuarios' ? 'active' : '' ?>" href="usuarios"><i class="bi bi-person-fill"></i> Usuarios</a></li>
            <li><a class="dropdown-item <?= $pagina === 'alumnos' ? 'active' : '' ?>" href="alumnos"><i class="bi bi-mortarboard-fill"></i> Alumnos</a></li>
            <li><a class="dropdown-item <?= $pagina === 'docentes' ? 'active' : '' ?>" href="docentes"><i class="bi bi-person-video3"></i> Docentes</a></li>
          </ul>
        </li>

        <!-- GESTIÓN ACADÉMICA -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($pagina, ['cursos','materias','asistencias']) ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-journal-bookmark-fill"></i> Académico
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item <?= $pagina === 'cursos' ? 'active' : '' ?>" href="cursos"><i class="bi bi-journals"></i> Cursos</a></li>
            <li><a class="dropdown-item <?= $pagina === 'materias' ? 'active' : '' ?>" href="materias"><i class="bi bi-book-half"></i> Materias</a></li>
            <li><a class="dropdown-item <?= $pagina === 'asistencias' ? 'active' : '' ?>" href="asistencias.php"><i class="bi bi-clipboard-check"></i> Asistencias</a></li>
          </ul>
        </li>

        <!-- CALIFICACIONES -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($pagina, ['calificaciones','instancias','ciclo_lectivo']) ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-card-checklist"></i> Calificaciones
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="ciclos_lectivos" id="btnCicloLectivo"><i class="bi bi-calendar-event"></i> Ciclo lectivo</a></li>
            <li><a class="dropdown-item" href="instancias" id="btnInstancias"><i class="bi bi-list-check"></i> Instancias</a></li>
            <li><a class="dropdown-item <?= $pagina === 'calificar' ? 'active' : '' ?>" href="calificar"><i class="bi bi-pencil-square me-1"></i> Calificar alumnos</a></li>
            <li><a class="dropdown-item" href="listar_calificaciones">📊 Listar calificaciones</a></li>

          </ul>
        </li>

        <!-- OPERACIONES -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($pagina, ['respaldo','respaldo_sistema']) ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-gear"></i> Operaciones
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="configuracion_institucional"><i class="bi bi-gear-fill me-1"></i> Configuración Institucional</a></li>
            <li><a class="dropdown-item <?= $pagina === 'respaldo' ? 'active' : '' ?>" href="respaldo">📦 Respaldo Base de Datos</a></li>
            <li><a class="dropdown-item <?= $pagina === 'respaldo_sistema' ? 'active' : '' ?>" href="respaldo_sistema">📦 Respaldo Sistema Completo</a></li>
            <li><a class="dropdown-item" href="modulo_accesos"><i class="bi bi-person-lines-fill me-2"></i> Accesos</a></li>
          </ul>
        </li>
      </ul>

      <?php if (isset($_SESSION['nombre'])): ?>
      <!-- PERFIL Y SALIDA -->
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
          <span class="navbar-text text-light me-2"><?= $_SESSION['rol'] ?>: <?= $_SESSION['nombre'] ?></span>
        </li>
        <li class="nav-item">
          <button class="btn btn-outline-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalPerfil">
            <i class="bi bi-person-circle me-1"></i> Mi Perfil
          </button>
        </li>
        <li class="nav-item">
          <a class="nav-link text-light" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a>
        </li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>