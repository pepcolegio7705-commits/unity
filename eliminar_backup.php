<?php
header("Content-Type: application/json");

$archivo = $_POST["archivo"] ?? '';
$ruta = "backups/" . basename($archivo); // ⚠️ Sanitizar para evitar path traversal

if (!$archivo || !file_exists($ruta)) {
  echo json_encode(["ok" => false, "msg" => "Archivo no encontrado o inválido."]);
  exit;
}

if (unlink($ruta)) {
  echo json_encode(["ok" => true, "msg" => "Respaldo eliminado correctamente."]);
} else {
  echo json_encode(["ok" => false, "msg" => "No se pudo eliminar el archivo."]);
}