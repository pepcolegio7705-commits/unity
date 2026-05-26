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
  <title>Lista de Respaldos | Unity</title>
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
  <div class="container-fluid mt-5">
      <div class="d-flex justify-content-between mb-3">
        <h2>📦 Respaldos</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoBackup">
          ➕ Nuevo respaldo
        </button>
      </div>

      <table class="table table-bordered" id="tablaRespaldos">
        <thead class="table-light">
          <tr>
            <th>📄 Archivo</th>
            <th>📅 Fecha</th>
            <th>📏 Tamaño</th>
            <th class="text-center">⚙️ Acciones</th>
          </tr>
        </thead>
        <tbody id="cuerpoBackups">
          <?php include("cargar_backups.php"); ?>
        </tbody>
      </table>
    </div>

    <!-- Modal: Nuevo respaldo -->
    <div class="modal fade" id="modalNuevoBackup" tabindex="-1">
      <div class="modal-dialog">
        <form id="formBackup">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">🎯 Crear respaldo</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <label for="tabla">Seleccionar tabla:</label>
              <select name="tabla" class="form-select" required>
                <option value="">-- Elegir --</option>
                <?php
                $conexion = new mysqli("localhost", "c2621783_jawsist", "woDA45wozu", "c2621783_jawsist");
                $res = $conexion->query("SHOW TABLES");
                while ($row = $res->fetch_row()) {
                  echo "<option value='{$row[0]}'>{$row[0]}</option>";
                }
                ?>
                <option value="__todo__">🗃️ Toda la base</option>
              </select>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">💾 Generar</button>
            </div>
          </div>
        </form>
      </div>
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

<script>
  // Inicializar tabla
  let tabla = null;
  function inicializarTabla() {
    tabla = $('#tablaRespaldos').DataTable({
      language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
      order: [[1, "desc"]],
      pageLength: 10,
      dom: 'Bfrtip',
      buttons: [
        { extend: 'excelHtml5', text: '📥 Excel', className: 'btn btn-success btn-sm' },
        { extend: 'pdfHtml5', text: '📄 PDF', className: 'btn btn-danger btn-sm' },
        { extend: 'print', text: '🖨️ Imprimir', className: 'btn btn-secondary btn-sm' }
      ]
    });
  }

  function recargarTablaBackups() {
    $.get("cargar_backups.php", function (html) {
      if ($.fn.DataTable.isDataTable("#tablaRespaldos")) {
        $('#tablaRespaldos').DataTable().destroy();
      }
      $("#cuerpoBackups").html(html);
      inicializarTabla();
    });
  }

  // Listeners
  $(document).ready(function () {
    inicializarTabla();

    $("#formBackup").on("submit", function (e) {
      e.preventDefault();
      $.post("generar_backup_manual.php", $(this).serialize(), function (res) {
        if (res.ok) {
          Swal.fire("Listo", res.msg, "success");
          $("#modalNuevoBackup").modal("hide");
          recargarTablaBackups();
        } else {
          Swal.fire("Error", res.msg, "error");
        }
      }, "json");
    });

    $(document).on("click", ".eliminarBackup", function () {
      const nombre = $(this).data("nombre");
      Swal.fire({
        title: "¿Eliminar respaldo?",
        text: `El archivo "${nombre}" será eliminado.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
      }).then((result) => {
        if (result.isConfirmed) {
          $.post("eliminar_backup.php", { archivo: nombre }, function (res) {
            if (res.ok) {
              Swal.fire("Eliminado", res.msg, "success");
              recargarTablaBackups();
            } else {
              Swal.fire("Error", res.msg, "error");
            }
          }, "json");
        }
      });
    });
  });
</script>


</body>
</html>



<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
