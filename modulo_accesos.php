<?php
  
  if (!isset($_SESSION)) session_start();


  if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== "Administrador") {
    header("Location: acceso_denegado");
    exit;
  }

  $conn = new mysqli("localhost", "root", "", "c2621783_jawsist");
  if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
  }

  $sql = "SELECT u.id, u.nombre, u.dni, u.correo, u.clave, r.nombre AS rol, e.nombre AS estado
          FROM usuarios u
          JOIN roles r ON u.rol_id = r.id
          JOIN estados e ON u.estado_id = e.id
          ORDER BY u.id ASC";
  $resultado = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Listado de Accesos | Unity</title>
  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Íconos opcionales de Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <style>
    body { padding-bottom: 80px; background-color: #f8f9fa; }
    table td { vertical-align: middle; }
  </style>
</head>
<body>

<?php
      $pagina = 'dashboard';
      include "assets/navbar.php";
  ?>
<br><br>

<div class="container mt-4">
  <h4 class="mb-3"><i class="bi bi-person-lines-fill me-2"></i> Registro de Accesos</h4>

  <div class="row mb-3 border p-3 bg-light rounded">
    <div class="col-md-3">
      <label for="filtroDesde" class="form-label">Desde</label>
      <input type="date" id="filtroDesde" class="form-control">
    </div>
    <div class="col-md-3">
      <label for="filtroHasta" class="form-label">Hasta</label>
      <input type="date" id="filtroHasta" class="form-control">
    </div>
    <div class="col-md-3">
      <label for="filtroRol" class="form-label">Rol</label>
      <select id="filtroRol" class="form-select">
        <option value="">Cargando...</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="filtroUsuario" class="form-label">Usuario</label>
      <input type="text" id="filtroUsuario" class="form-control" placeholder="Nombre o correo">
    </div>
  </div>

  <div class="mb-3 text-end">
    <button id="btnLimpiarFiltros" class="btn btn-secondary">
      <i class="bi bi-x-circle me-1"></i> Limpiar filtros
    </button>
  </div>

  <table id="tablaAccesos" class="table table-bordered table-striped table-hover">
    <thead class="table-light">
      <tr>
        <th>Nombre</th>
        <th>Correo/DNI</th>
        <th>Rol</th>
        <th>IP</th>
        <th>Navegador</th>
        <th>Fecha</th>
      </tr>
    </thead>
  </table>
</div>


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

<?php if (isset($_SESSION['exito'])): ?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show text-white bg-success" role="alert">
      <div class="toast-body"><?= $_SESSION['exito'] ?></div>
    </div>
  </div>
  <?php unset($_SESSION['exito']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show text-white bg-danger" role="alert">
      <div class="toast-body"><?= $_SESSION['error'] ?></div>
    </div>
  </div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<br><br>
<?php include "assets/footer.php" ?>

</body>


</html>



<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Botones de exportación -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<!-- Bootstrap 5.3 Bundle JS (incluye Popper.js, necesario para modales y tooltips) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
      // 🧩 Cargar roles en el filtro
      $.ajax({
        url: 'obtener_roles.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
          const select = $('#filtroRol');
          select.empty().append('<option value="">Todos</option>');
          data.forEach(function (rol) {
            select.append(`<option value="${rol.id}">${rol.nombre}</option>`);
          });
        }
      });

      let institucion = {};

      // 🔄 Obtener datos institucionales
      $.ajax({
        url: 'obtener_institucion.php',
        method: 'GET',
        dataType: 'json',
        async: false,
        success: function (data) {
          institucion = data;
        }
      });

      const tabla = $('#tablaAccesos').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
          url: 'listar_accesos.php',
          type: 'POST',
          data: function (d) {
            d.desde = $('#filtroDesde').val();
            d.hasta = $('#filtroHasta').val();
            d.rol = $('#filtroRol').val();
            d.usuario = $('#filtroUsuario').val();
          }
        },
        columns: [
          { data: 'nombre' },
          { data: 'correo' },
          { data: 'rol' },
          { data: 'ip' },
          { data: 'navegador' },
          { data: 'fecha_acceso' }
        ],
        dom: 'Bfrtip',
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
            className: 'btn btn-success',
            //title: institucion.nombre,
            messageTop: `${institucion.nombre}\n${institucion.direccion}`,
            messageBottom: `Tel: ${institucion.telefono} | Email: ${institucion.email}`
          },
          {
            extend: 'pdfHtml5',
            text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
            className: 'btn btn-danger',
            orientation: 'landscape',
            pageSize: 'A4',
            customize: function (doc) {
              // 🧩 Encabezado
              doc.content.splice(0, 0, {
                columns: [
                  {
                    width: '*',
                    alignment: 'center',
                    text: `${institucion.nombre}\n${institucion.direccion} | ${institucion.localidad}, ${institucion.provincia}`,
                    fontSize: 12,
                    margin: [0, 0, 0, 10]
                  },
                  {
                    image: institucion.logo_base64,
                    width: 60,
                    alignment: 'right'
                  }
                ]
              });

              // 🧩 Pie de página
              doc.footer = function (currentPage, pageCount) {
                return {
                  columns: [
                    {
                      text: `Tel: ${institucion.telefono} | Email: ${institucion.email} `,
                      alignment: 'center',
                      fontSize: 9,
                      margin: [0, 10, 0, 0]
                    }
                  ]
                };
              };

              doc.styles.tableHeader.fillColor = '#0d6efd';
              doc.styles.tableHeader.color = 'white';
              doc.defaultStyle.fontSize = 9;
            }
          },
          {
            extend: 'print',
            text: '<i class="bi bi-printer me-1"></i> Imprimir',
            className: 'btn btn-primary',
            //title: institucion.nombre,
            messageTop: `<div style="text-align:center;">
                            <strong>${institucion.nombre}</strong><br>
                            ${institucion.direccion} | ${institucion.localidad}, ${institucion.provincia}
                        </div>
                        <div style="text-align:right;">
                            <img src="${institucion.logo_base64}" width="60">
                        </div>`,
            messageBottom: `<div style="text-align:center;">
                              Tel: ${institucion.telefono} | Email: ${institucion.email}
                            </div>`
          }
        ],
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
      });

      // 🔄 Recargar al cambiar filtros
      $('#filtroDesde, #filtroHasta, #filtroRol, #filtroUsuario').on('change keyup', function () {
        tabla.ajax.reload();
      });

      // 🧹 Limpiar filtros
      $('#btnLimpiarFiltros').on('click', function () {
        $('#filtroDesde').val('');
        $('#filtroHasta').val('');
        $('#filtroRol').val('');
        $('#filtroUsuario').val('');
        tabla.ajax.reload();
      });
    });

</script>