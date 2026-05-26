<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

$conexion = new mysqli("localhost", "root", "", "c2621783_jawsist");
$conexion->set_charset("utf8mb4");

if ($conexion->connect_errno) {
  echo json_encode(["error" => "Error de conexión: " . $conexion->connect_error]);
  exit;
}

$res1 = $conexion->query("SELECT COUNT(*) FROM lista_alfa");
$total_alumnos = $res1 ? $res1->fetch_row()[0] : 0;

$res2 = $conexion->query("SELECT COUNT(*) FROM asistencias");
$total_asistencias = $res2 ? $res2->fetch_row()[0] : 0;

$res3 = $conexion->query("SELECT COUNT(*) FROM asistencias WHERE tipo_asistencia_id = 1");
$total_presentes = $res3 ? $res3->fetch_row()[0] : 0;

$porcentaje_asistencia = $total_asistencias ? round(($total_presentes / $total_asistencias) * 100, 2) : 0;

echo json_encode([
  "total_alumnos" => $total_alumnos,
  "total_asistencias" => $total_asistencias,
  "porcentaje_asistencia" => $porcentaje_asistencia
], JSON_UNESCAPED_UNICODE);