<?php
  require_once "./seguridad.php";
  

  // ✅ Iniciar sesión si no está iniciada
  if (!isset($_SESSION)) session_start();

  // ✅ Verificar sesión y rol permitido
  verificarSesion(); // Asegura que el usuario esté logueado

  // ✅ Compatibilidad con lógica existente
  if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== "Administrador" && $_SESSION['rol'] !== "Secretario" && $_SESSION['rol'] !== "Preceptor" && $_SESSION['rol'] !== "Directivo")) {
        header("Location: acceso_denegado.php");
        exit;
  }

  // ✅ Conexión a la base de datos
  $conn = new mysqli("localhost", "c2621783_jawsist", "woDA45wozu", "c2621783_jawsist");
  if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
  }

  // ✅ Consulta de usuarios
  $sql = "SELECT u.id, u.nombre, u.dni, u.correo, u.clave, r.nombre AS rol, e.nombre AS estado
          FROM usuarios u
          JOIN roles r ON u.rol_id = r.id
          JOIN estados e ON u.estado_id = e.id
          ORDER BY u.id ASC";
  $resultado = $conn->query($sql);

  $rol_id = obtenerRolId();
  if ($rol_id !== 1 && $rol_id !== 4 && $rol_id !== 5 && $rol_id !== 6) {
    header("Location: acceso_denegado.php");
    exit;
  }
  
  // ✅ Cierre de conexión
  $conn->close();
?>