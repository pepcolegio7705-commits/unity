<?php
  session_start();
  if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
  }

  $rol = $_SESSION['rol'] ?? '';

  // Variables comunes
  $nombre = $_SESSION['nombre'];

  if (!in_array($rol, ['Administrador', 'Secretario', 'Preceptor', 'Directivo'])) {
    $rol = 'Invitado';
  }

  $conn = new mysqli("localhost", "root", "", "c2621783_jawsist");
  if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
 }

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- jQuery y DataTables -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!----Estilos de ----------->
  <style>
    @media print {
      html, body {
        width: 100%;
        margin: 0;
        padding: 0;
      }

      body {
        background-image: url('logo7705.png');
        background-size: cover;
        padding-bottom: 80px;
        padding-top: 70px; /* ajustá según la altura de tu navbar */

      }

      .print-area {
        width: 100%;
        border: 5px solid #000;
        padding: 30px;
        background-color: rgba(255, 255, 255, 0.95);
        box-sizing: border-box;
      }

      #printButton, nav {
        display: none;
      }
    }

    #logo {
      width: 100px;
    }

    .titulo {
      text-align: center;
      margin-top: 10px;
    }

    .subtitulo {
      text-align: center;
      font-size: 1.2rem;
      margin-bottom: 20px;
      color: #555;
    }

    table {
      width: 100% !important;
      table-layout: auto;
    }
  </style>


</head>
<body class="bg-light">

  <?php
      $pagina = 'dashboard';
      include "assets/navbar.php";
  ?>
 
  <div class="modal fade" id="modalPerfil" tabindex="-1" aria-labelledby="modalPerfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content shadow">
        <form id="formPerfil" method="post">
          <div class="modal-header bg-secondary text-white">
            <h5 class="modal-title" id="modalPerfilLabel"><i class="bi bi-person-lines-fill me-2"></i> Perfil del Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">

            <div id="mensajePerfil" class="alert d-none" role="alert"></div>
            <input type="hidden" name="id" value="<?= $_SESSION['usuario_id'] ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" value="<?= $_SESSION['nombre'] ?>" required>
              </div>
              <div class="col-md-6">
                <label>DNI</label>
                <input type="text" name="dni" class="form-control" value="<?= $_SESSION['dni']?>" required>
              </div>
              <div class="col-md-6">
                <label>Correo</label>
                <input type="email" name="correo" class="form-control" value="<?= $_SESSION['correo']?>" required>
              </div>
              <div class="col-md-6">
                <label>Clave <small class="text-muted">(dejá en blanco para no cambiarla)</small></label>
                <input type="password" name="clave" class="form-control" maxlength="8">
              </div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-save-fill me-1"></i> Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script src="js/perfil.js"></script>


  <div class="container-fluid py-4">
    <br><br>
      <h2 class="mb-4">Bienvenido, <?= htmlspecialchars($nombre) ?> 👋</h2>

      <?php if ($rol === 'Administrador' || $rol === 'Secretario' || $rol === 'Preceptor' || $rol === 'Directivo' ): ?>

        <h3 class="mb-4 text-success"><i class="bi bi-graph-up"></i> Panel de Métricas</h3>

        <div class="card border rounded p-3 mb-4 shadow-sm" id="bloqueInstitucional">
          <div class="row gy-3">
            <!-- 📋 Datos institucionales -->
            <div class="col-12 col-md-4">
              <h5 class="text-primary"><i class="bi bi-building me-2"></i> Institución</h5>
              <ul class="list-unstyled mb-2" id="datosInstitucion">
                <!-- Se cargan dinámicamente -->
              </ul>
            </div>

            <!-- 🎓 Orientaciones académicas -->
            <div class="col-12 col-md-4 border-md-start">
              <h5 class="text-secondary"><i class="bi bi-diagram-3 me-2"></i> Orientaciones</h5>
              <ul class="list-group list-group-flush" id="listaOrientacionesDashboard">
                <!-- Se cargan dinámicamente -->
              </ul>
            </div>

            <!-- 🖼️ Logo institucional -->
            <div class="col-12 col-md-4 text-center text-md-end border-md-start d-flex align-items-center justify-content-center">
              <img id="logoInstitucional" src="uploads/logo_unity.png" alt="Logo Institucional" class="img-fluid rounded shadow-sm" style="max-height: 180px;">
            </div>
          </div>
        </div>
  
        <div class="row g-4" id="dashboardCards">
          <!-- Cards métricas dinámicas -->
        </div>

        <div class="card mt-4">
          <div class="card-header bg-light">
            <h5 class="mb-0 text-primary"><i class="bi bi-bar-chart-line"></i> Distribución de alumnos por curso</h5>
          </div>
          <div class="card-body">
            <canvas id="graficoCursos" height="100"></canvas>
          </div>
        </div>

        <div class="card mt-4 shadow-sm border-light">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-dark">
              <i class="bi bi-people-fill me-2"></i> Asistencias por curso
            </h5>
            <span class="text-muted small">Visual compacto</span>
          </div>
          <div class="card-body">
            <canvas id="graficoAsistenciasPorCurso" height="150"></canvas>
          </div>
        </div>



        
      <?php else: ?>
        <div class="alert alert-warning">Tu panel personalizado está en desarrollo.</div>
      <?php endif; ?>
  </div>

  <br><br>

  <?php
    include "assets/footer.php";
  ?>
  
</body>
</html>
 <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    $(document).ready(function () {
        $.getJSON("dashboard_datos.php", function (res) {
          const $cards = $("#dashboardCards").empty();

          // Total de alumnos
          $cards.append(`
            <div class="col-md-3">
              <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                  <h5 class="card-title">👥 Alumnos activos</h5>
                  <p class="fs-4 text-success">${res.total_alumnos}</p>
                </div>
              </div>
            </div>
          `);

          // Total asistencias
          $cards.append(`
            <div class="col-md-3">
              <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                  <h5 class="card-title">📋 Asistencias registradas</h5>
                  <p class="fs-4 text-primary">${res.total_asistencias}</p>
                </div>
              </div>
            </div>
          `);

          // Porcentaje asistencia promedio
          $cards.append(`
            <div class="col-md-3">
              <div class="card border-info shadow-sm">
                <div class="card-body text-center">
                  <h5 class="card-title">📊 % Asistencia promedio</h5>
                  <p class="fs-4 text-info">${res.porcentaje_asistencia}%</p>
                </div>
              </div>
            </div>
          `);

          // Placeholder calificaciones
          $cards.append(`
            <div class="col-md-3">
              <div class="card border-secondary shadow-sm">
                <div class="card-body text-center">
                  <h5 class="card-title">📝 Calificaciones</h5>
                  <p class="fs-6 text-muted">Módulo en desarrollo</p>
                </div>
              </div>
            </div>
          `);
        });



      $.getJSON("dashboard_cursos.php", function (res) {
        const ctx = document.getElementById("graficoCursos").getContext("2d");

        new Chart(ctx, {
          type: "bar",
          data: {
            labels: res.cursos,
            datasets: [{
              label: "Alumnos por curso",
              data: res.cantidad,
              backgroundColor: "rgba(13,110,253,0.7)",
              borderRadius: 4
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
      });

      $.getJSON("dashboard_asistencias_curso.php", function (res) {
        const ctx = document.getElementById("graficoAsistenciasPorCurso").getContext("2d");

        new Chart(ctx, {
          type: "bar",
          data: {
            labels: res.cursos,
            datasets: res.datasets.map(ds => ({
              ...ds,
              barThickness: 20 // controla grosor de barra
            }))
          },
          options: {
            responsive: true,
            layout: {
              padding: { top: 10, bottom: 20 }
            },
            plugins: {
              legend: {
                position: "bottom",
                labels: {
                  font: { size: 12 },
                  color: "#343a40"
                }
              },
              tooltip: {
                mode: "index",
                intersect: false,
                callbacks: {
                  label: function(context) {
                    const totalCurso = context.dataset.data.reduce((a, b) => a + b, 0);
                    const valor = context.raw;
                    const porcentaje = totalCurso > 0 ? ((valor / totalCurso) * 100).toFixed(1) : "0.0";
                    return `${context.dataset.label}: ${valor} (${porcentaje}%)`;
                  }
                }
              }
            },
            scales: {
                y: {
                  stacked: true,
                  beginAtZero: true,
                  max: 100,
                  ticks: {
                    callback: value => value + "%",
                    font: { size: 12 },
                    color: "#343a40"
                  },
                  title: {
                    display: true,
                    text: "Distribución porcentual",
                    font: { size: 13 },
                    color: "#6c757d"
                  }
                },
                x: {
                  stacked: true,
                  ticks: {
                    font: { size: 11 },
                    color: "#495057"
                  }
                }
              }
          }
        });
      });

      function cargarDashboardInstitucional() {
        $.post('institucion_acciones.php', { action: 'consultar' }, function(data) {
          if (data) {
            const html = `
              <li><strong>Nombre:</strong> ${data.nombre}</li>
              <li><strong>Dirección:</strong> ${data.direccion}</li>
              <li><strong>Localidad:</strong> ${data.localidad}</li>
              <li><strong>Provincia:</strong> ${data.provincia}</li>
              <li><strong>Director/a:</strong> ${data.director}</li>
              <li><strong>Email:</strong> ${data.email}</li>
              <li><strong>Teléfono:</strong> ${data.telefono}</li>
            `;
            $('#datosInstitucion').html(html);

            // Actualizar logo si existe
            if (data.logo_path && data.logo_path !== '') {
              $('#logoInstitucional').attr('src', data.logo_path + '?' + new Date().getTime());
            } else {
              $('#logoInstitucional').attr('src', 'assets/img/logo_default.png'); // opcional
            }
          }
        }, 'json');

        $.post('institucion_acciones.php', { action: 'listar_orientaciones' }, function(data) {
          let html = '';
          if (data.length > 0) {
            data.forEach(o => {
              html += `<li class="list-group-item">${o.nombre}</li>`;
            });
          } else {
            html = `<li class="list-group-item text-muted">Sin orientaciones registradas</li>`;
          }
          $('#listaOrientacionesDashboard').html(html);
        }, 'json');
      }

      cargarDashboardInstitucional();
    });
</script>

  