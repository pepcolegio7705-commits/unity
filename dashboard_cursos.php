<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

$conexion = new mysqli("localhost", "root", "", "c2621783_jawsist");
$conexion->set_charset("utf8mb4");

if ($conexion->connect_errno) {
  echo json_encode(["error" => "Conexión fallida: " . $conexion->connect_error]);
  exit;
}

// Consulta basada en asignaciones_cursos
$sql = "
  SELECT c.nombre, COUNT(*) AS cantidad
  FROM asignaciones_cursos ac
  JOIN cursos c ON ac.curso_id = c.id
  GROUP BY c.id
  ORDER BY cantidad DESC
";

$res = $conexion->query($sql);
if (!$res) {
  echo json_encode(["error" => "Error en la consulta: " . $conexion->error]);
  exit;
}

$cursos = [];
$cantidades = [];

while ($f = $res->fetch_assoc()) {
  $cursos[] = $f["nombre"];
  $cantidades[] = (int)$f["cantidad"];
}

echo json_encode([
  "cursos" => $cursos,
  "cantidad" => $cantidades
], JSON_UNESCAPED_UNICODE);