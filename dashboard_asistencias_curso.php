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

$sql = "
  SELECT c.nombre AS curso, ta.nombre AS tipo, COUNT(*) AS cantidad
  FROM asistencias a
  JOIN tipos_asistencia ta ON a.tipo_asistencia_id = ta.id
  JOIN cursos c ON a.curso_id = c.id
  GROUP BY c.id, ta.id
  ORDER BY c.nombre, ta.id
";

$res = $conexion->query($sql);
if (!$res) {
  echo json_encode(["error" => "Error en la consulta: " . $conexion->error]);
  exit;
}

// Reorganizar datos para gráfico agrupado
$data = [];

while ($f = $res->fetch_assoc()) {
  $curso = $f["curso"];
  $tipo = $f["tipo"];
  $cantidad = (int)$f["cantidad"];

  if (!isset($data[$curso])) {
    $data[$curso] = [];
  }

  $data[$curso][$tipo] = $cantidad;
}

$cursos = array_keys($data);
$tipos = ["Presente", "Ausente", "Ausente justificado"];
$datasets = [];

foreach ($tipos as $tipo) {
  $row = [
    "label" => $tipo,
    "data" => [],
    "backgroundColor" => ""
  ];

  switch ($tipo) {
    case "Presente": $row["backgroundColor"] = "rgba(25,135,84,0.7)"; break;
    case "Ausente": $row["backgroundColor"] = "rgba(220,53,69,0.7)"; break;
    case "Ausente justificado": $row["backgroundColor"] = "rgba(255,193,7,0.7)"; break;
  }

  foreach ($cursos as $curso) {
    $row["data"][] = $data[$curso][$tipo] ?? 0;
  }

  $datasets[] = $row;
}

echo json_encode([
  "cursos" => $cursos,
  "datasets" => $datasets
], JSON_UNESCAPED_UNICODE);

foreach ($tipos as $tipo) {
  $row = [
    "label" => $tipo,
    "data" => [],
    "backgroundColor" => ""
  ];

  switch ($tipo) {
    case "Presente": $row["backgroundColor"] = "rgba(25,135,84,0.7)"; break;
    case "Ausente": $row["backgroundColor"] = "rgba(220,53,69,0.7)"; break;
    case "Ausente justificado": $row["backgroundColor"] = "rgba(255,193,7,0.7)"; break;
  }

  foreach ($cursos as $curso) {
    $total = array_sum($data[$curso]); // suma total del curso
    $valor = $data[$curso][$tipo] ?? 0;
    $porcentaje = $total > 0 ? round(($valor / $total) * 100, 1) : 0;
    $row["data"][] = $porcentaje;
  }

  $datasets[] = $row;
}