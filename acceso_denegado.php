<?php
  
  if (!isset($_SESSION)) session_start();

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
  <title>Listado de Usuarios | Unity</title>
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

<?php include "assets/navbar.php" ?>
<br><br>


<div class="container mt-5 text-center">
  <h3><i class="bi bi-shield-lock-fill me-2"></i> Acceso denegado</h3>
  <p>No tenés permisos para acceder a esta sección.</p>
  <a href="dashboard" class="btn btn-primary">Volver al inicio</a>
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

