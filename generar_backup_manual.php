<?php
header("Content-Type: application/json");

// ⚙️ Conexión a la base
$mysqli = new mysqli("localhost", "root", "", "c2621783_jawsist");
$mysqli->set_charset("utf8mb4");

$tabla = $_POST["tabla"] ?? '';
$fecha = date("Y-m-d_H-i-s");
$nombreBase = ($tabla === "__todo__") ? "base" : $tabla;
$archivo = "backups/respaldo_{$nombreBase}_$fecha.sql";

// 📁 Crear carpeta si no existe
if (!is_dir("backups")) {
  mkdir("backups", 0755, true);
}

$sql = "-- Respaldo generado el $fecha\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

// 🔄 Tablas a respaldar
$tablas = [];

if ($tabla === "__todo__") {
  $res = $mysqli->query("SHOW TABLES");
  while ($row = $res->fetch_row()) {
    $tablas[] = $row[0];
  }
} else {
  $tablas[] = $tabla;
}

// 🔁 Construir respaldo
foreach ($tablas as $t) {
  // Estructura
  $resEstructura = $mysqli->query("SHOW CREATE TABLE `$t`");
  $row = $resEstructura->fetch_array();
  $sql .= "-- Estructura para `$t`\n";
  $sql .= "DROP TABLE IF EXISTS `$t`;\n";
  $sql .= $row[1] . ";\n\n";

  // Datos
  $resDatos = $mysqli->query("SELECT * FROM `$t`");
  if ($resDatos && $resDatos->num_rows > 0) {
    $sql .= "-- Datos para `$t`\n";
    while ($fila = $resDatos->fetch_assoc()) {
      $valores = array_map(fn($v) => "'".$mysqli->real_escape_string($v)."'", array_values($fila));
      $sql .= "INSERT INTO `$t` VALUES (" . implode(",", $valores) . ");\n";
    }
    $sql .= "\n";
  }
}

file_put_contents($archivo, $sql);

if (file_exists($archivo)) {
  echo json_encode(["ok" => true, "msg" => "Respaldo creado correctamente."]);
} else {
  echo json_encode(["ok" => false, "msg" => "No se pudo guardar el respaldo."]);
}