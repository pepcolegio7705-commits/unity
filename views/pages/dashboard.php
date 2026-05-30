<?php
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$rol = $_SESSION['rol'] ?? 'Invitado';
$is_admin = in_array($rol, ['Administrador', 'Secretario', 'Preceptor', 'Directivo']);
?>

<style>
/* Premium Dashboard Styles */
.dashboard-header {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    border-radius: 16px;
    padding: 2rem;
    color: #fff;
    margin-bottom: 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}
.dashboard-header::after {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0; left: 0;
    background: url('data:image/svg+xml;utf8,<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><circle cx="2" cy="2" r="2" fill="white"/></svg>') repeat;
    opacity: 0.03;
    pointer-events: none;
}
.metric-card {
    border: none;
    border-radius: 16px;
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
    z-index: 1;
}
.metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
}
.metric-card.bg-gradient-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; }
.metric-card.bg-gradient-success { background: linear-gradient(135deg, #10b981, #059669); color: white; }
.metric-card.bg-gradient-info { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: white; }
.metric-card.bg-gradient-warning { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }

.metric-icon {
    position: absolute;
    right: -10px;
    bottom: -20px;
    font-size: 6rem;
    opacity: 0.15;
    z-index: -1;
    transition: all 0.3s ease;
}
.metric-card:hover .metric-icon {
    transform: scale(1.1) rotate(-5deg);
    opacity: 0.2;
}
.chart-container {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
    padding: 1.5rem;
}
.glass-panel {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 8px 32px rgba(0,0,0,0.05);
}
.quick-action-btn {
    border-radius: 12px;
    padding: 1rem;
    font-weight: 600;
    transition: all 0.2s;
    background: white;
}
.quick-action-btn:hover {
    transform: scale(1.02);
}
</style>

<div class="container-fluid px-md-4 mb-5">

        <!-- Premium Header -->
        <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold mb-1">¡Hola de nuevo, <?= htmlspecialchars($nombre) ?>! 👋</h2>
                <p class="text-light opacity-75 mb-0">Aquí tienes un resumen actualizado de la institución.</p>
            </div>
            <div class="text-end mt-3 mt-md-0 d-flex align-items-center">
                <div class="me-4 text-start">
                    <span class="d-block small text-light opacity-75 text-uppercase fw-bold">Fecha actual</span>
                    <strong class="fs-5 text-capitalize" id="currentDate"><?= date('d M Y'); ?></strong>
                </div>
                <div class="text-start">
                    <span class="d-block small text-light opacity-75 text-uppercase fw-bold">Hora</span>
                    <strong class="fs-5" id="currentTime"><?= date('H:i'); ?></strong>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4 g-3">
            <div class="col-6 col-md-3">
                <a <?= $is_admin ? 'href="?page=usuarios"' : 'href="#" tabindex="-1" aria-disabled="true"' ?> 
                   class="btn w-100 quick-action-btn shadow-sm text-primary d-flex align-items-center justify-content-center <?= !$is_admin ? 'disabled opacity-50' : '' ?>">
                    <i class="fa-solid fa-user-plus me-2 fs-5"></i> Nuevo Usuario
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a <?= $is_admin ? 'href="?page=cursos"' : 'href="#" tabindex="-1" aria-disabled="true"' ?> 
                   class="btn w-100 quick-action-btn shadow-sm text-success d-flex align-items-center justify-content-center <?= !$is_admin ? 'disabled opacity-50' : '' ?>">
                    <i class="fa-solid fa-book-open me-2 fs-5"></i> Gestionar Cursos
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a <?= $is_admin ? 'href="?page=asistencias"' : 'href="#" tabindex="-1" aria-disabled="true"' ?> 
                   class="btn w-100 quick-action-btn shadow-sm text-warning d-flex align-items-center justify-content-center <?= !$is_admin ? 'disabled opacity-50' : '' ?>">
                    <i class="fa-solid fa-calendar-check me-2 fs-5"></i> Tomar Asistencia
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a <?= $is_admin ? 'href="?page=reportes"' : 'href="#" tabindex="-1" aria-disabled="true"' ?> 
                   class="btn w-100 quick-action-btn shadow-sm text-info d-flex align-items-center justify-content-center <?= !$is_admin ? 'disabled opacity-50' : '' ?>">
                    <i class="fa-solid fa-file-pdf me-2 fs-5"></i> Generar Reporte
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row g-4 mb-4" id="dashboardCards">
            <!-- Loading placeholders -->
            <div class="col-12 text-center text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Cargando métricas...</div>
        </div>

        <!-- Institution Details (Glassmorphism) -->
        <div class="glass-panel p-4 mb-4">
            <div class="row gy-4 align-items-center">
                <div class="col-12 col-md-3 text-center border-md-end">
                    <img id="logoInstitucional" src="assets/img/logo.png" alt="Logo Institucional" class="img-fluid rounded-circle shadow" style="width: 130px; height: 130px; object-fit: cover; border: 4px solid #fff;">
                    <h5 class="mt-3 fw-bold text-dark mb-0">Sintek-Unity</h5>
                    <span class="badge bg-primary mt-1">Plataforma Educativa</span>
                </div>
                
                <div class="col-12 col-md-5 border-md-end px-md-4">
                    <h5 class="text-primary fw-bold mb-3"><i class="fa-solid fa-building me-2"></i> Datos Institucionales</h5>
                    <ul class="list-unstyled mb-0" id="datosInstitucion" style="font-size: 0.95rem; color: #475569;">
                        <li>Cargando...</li>
                    </ul>
                </div>

                <div class="col-12 col-md-4 px-md-4">
                    <h5 class="text-secondary fw-bold mb-3"><i class="fa-solid fa-sitemap me-2"></i> Orientaciones</h5>
                    <ul class="list-unstyled mb-0" id="listaOrientacionesDashboard" style="font-size: 0.95rem; color: #475569;">
                        <li>Cargando...</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-xl-6">
                <div class="chart-container h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fa-solid fa-chart-column text-primary me-2"></i> Alumnos por Curso</h5>
                    </div>
                    <canvas id="graficoCursos" height="150"></canvas>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="chart-container h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fa-solid fa-chart-pie text-success me-2"></i> Asistencias Globales</h5>
                        <span class="badge bg-light text-dark border">Visual compacto</span>
                    </div>
                    <canvas id="graficoAsistenciasPorCurso" height="150"></canvas>
                </div>
            </div>
        </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function () {
    
    // Reloj y fecha en vivo
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('currentDate').innerText = now.toLocaleDateString('es-ES', options);
        document.getElementById('currentTime').innerText = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Cargar Datos Dinámicos
    $.getJSON("dashboard_datos.php", function (res) {
        const $cards = $("#dashboardCards").empty();

        $cards.append(`
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="metric-card bg-gradient-primary p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-2 opacity-75">Alumnos Activos</h6>
                            <h2 class="mb-0 fw-bold">${res.total_alumnos}</h2>
                        </div>
                        <div class="icon-box">
                            <i class="fa-solid fa-users fs-1"></i>
                        </div>
                    </div>
                    <i class="fa-solid fa-users metric-icon"></i>
                </div>
            </div>
        `);

        $cards.append(`
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="metric-card bg-gradient-success p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-2 opacity-75">Asistencias</h6>
                            <h2 class="mb-0 fw-bold">${res.total_asistencias}</h2>
                        </div>
                        <div class="icon-box">
                            <i class="fa-solid fa-calendar-check fs-1"></i>
                        </div>
                    </div>
                    <i class="fa-solid fa-calendar-check metric-icon"></i>
                </div>
            </div>
        `);

        $cards.append(`
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="metric-card bg-gradient-info p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-2 opacity-75">Prom. Asistencia</h6>
                            <h2 class="mb-0 fw-bold">${res.porcentaje_asistencia}%</h2>
                        </div>
                        <div class="icon-box">
                            <i class="fa-solid fa-arrow-trend-up fs-1"></i>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-trend-up metric-icon"></i>
                </div>
            </div>
        `);

        $cards.append(`
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="metric-card bg-gradient-warning p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-semibold mb-2 opacity-75">Calificaciones</h6>
                            <h4 class="mb-0 fw-bold mt-1">En curso</h4>
                        </div>
                        <div class="icon-box">
                            <i class="fa-solid fa-book-bookmark fs-1"></i>
                        </div>
                    </div>
                    <i class="fa-solid fa-book-bookmark metric-icon"></i>
                </div>
            </div>
        `);
    });

    $.getJSON("dashboard_cursos.php", function (res) {
        if(res && res.cursos) {
            const ctx = document.getElementById("graficoCursos").getContext("2d");
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: res.cursos,
                    datasets: [{
                        label: "Alumnos por curso",
                        data: res.cantidad,
                        backgroundColor: "rgba(59, 130, 246, 0.8)",
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }
    });

    $.getJSON("dashboard_asistencias_curso.php", function (res) {
        if(res && res.datasets) {
            const ctx = document.getElementById("graficoAsistenciasPorCurso").getContext("2d");
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: res.cursos,
                    datasets: res.datasets.map(ds => ({
                        ...ds,
                        barThickness: 20
                    }))
                },
                options: {
                    responsive: true,
                    layout: { padding: { top: 10, bottom: 20 } },
                    plugins: {
                        legend: { position: "bottom", labels: { font: { size: 12 }, color: "#343a40" } }
                    },
                    scales: {
                        y: { stacked: true, beginAtZero: true, max: 100, ticks: { callback: value => value + "%" } },
                        x: { stacked: true }
                    }
                }
            });
        }
    });

    $.post('controllers/configuracion_ajax.php', { action: 'consultar' }, function(res) {
        if (res && res.status === 'success' && res.data && res.data.nombre) {
            let data = res.data;
            const html = `
                <li class="mb-2"><i class="fa-solid fa-circle-dot text-primary me-2"></i> <strong>Nombre:</strong> ${data.nombre}</li>
                <li class="mb-2"><i class="fa-solid fa-location-dot text-primary me-2"></i> <strong>Sede:</strong> ${data.direccion}, ${data.localidad}</li>
                <li class="mb-2"><i class="fa-solid fa-id-badge text-primary me-2"></i> <strong>Director/a:</strong> ${data.director}</li>
                <li class="mb-2"><i class="fa-solid fa-envelope text-primary me-2"></i> <strong>Email:</strong> ${data.email}</li>
                <li class="mb-2"><i class="fa-solid fa-phone text-primary me-2"></i> <strong>Teléfono:</strong> ${data.telefono}</li>
            `;
            $('#datosInstitucion').html(html);

            if (data.logo_path && data.logo_path !== '') {
                $('#logoInstitucional').attr('src', data.logo_path + '?' + new Date().getTime());
            }
        } else {
            $('#datosInstitucion').html('<li class="text-muted">No se pudo cargar la información.</li>');
        }
    }, 'json').fail(function() {
        $('#datosInstitucion').html('<li class="text-muted">Error al cargar datos institucionales.</li>');
    });

    $.post('controllers/configuracion_ajax.php', { action: 'listar_orientaciones' }, function(res) {
        let html = '';
        if (res && res.status === 'success' && res.data && res.data.length > 0) {
            res.data.forEach(o => {
                html += `<li class="mb-2"><i class="fa-solid fa-circle-check text-success me-2"></i> ${o.nombre}</li>`;
            });
        } else {
            html = `<li class="mb-2 text-muted"><i class="fa-solid fa-circle-info me-2"></i> Sin orientaciones registradas</li>`;
        }
        $('#listaOrientacionesDashboard').html(html);
    }, 'json').fail(function() {
        $('#listaOrientacionesDashboard').html('<li class="mb-2 text-muted">Error al cargar orientaciones.</li>');
    });
});
</script>
