<?php
require_once 'config/database.php';
require_once 'config/security.php';

// Aunque no carga header/footer, aseguramos la seguridad
require_login();

$ciclo_id = $_GET['ciclo_lectivo_id'] ?? null;
$curso_id = $_GET['curso_id'] ?? null;
$mes_anio = $_GET['mes_anio'] ?? null; // Formato YYYY-MM

if (!$ciclo_id || !$curso_id || !$mes_anio) {
    die("<h3>Faltan parámetros requeridos para generar el reporte.</h3>");
}

// Desglosar mes y año
list($anio, $mes) = explode('-', $mes_anio);
$dias_del_mes = cal_days_in_month(CAL_GREGORIAN, (int)$mes, (int)$anio);

// Obtener nombres de curso y ciclo
$stmt = $pdo->prepare("SELECT nombre FROM cursos WHERE id = ?");
$stmt->execute([$curso_id]);
$curso_nombre = $stmt->fetchColumn() ?: 'Desconocido';

$stmt = $pdo->prepare("SELECT nombre FROM ciclos_lectivos WHERE id = ?");
$stmt->execute([$ciclo_id]);
$ciclo_nombre = $stmt->fetchColumn() ?: 'Desconocido';

$meses_es = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$mes_nombre = $meses_es[(int)$mes];

// Obtener alumnos del curso en ese ciclo lectivo
$sql_alumnos = "
    SELECT a.id, a.alumno, a.dni
    FROM lista_alfa a
    JOIN historial_trayectoria h ON a.id = h.alumno_id
    WHERE h.curso_id = :cid AND h.ciclo_lectivo_id = :clid
    ORDER BY a.alumno ASC
";
$stmt_alumnos = $pdo->prepare($sql_alumnos);
$stmt_alumnos->execute(['cid' => $curso_id, 'clid' => $ciclo_id]);
$alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);

// Obtener asistencias del mes
$sql_asistencias = "
    SELECT a.alumno_id, DAY(a.fecha) as dia, t.nombre as tipo
    FROM asistencias a
    JOIN tipos_asistencia t ON a.tipo_asistencia_id = t.id
    WHERE a.curso_id = :cid 
    AND YEAR(a.fecha) = :anio AND MONTH(a.fecha) = :mes
";
$stmt_asis = $pdo->prepare($sql_asistencias);
$stmt_asis->execute(['cid' => $curso_id, 'anio' => $anio, 'mes' => $mes]);
$raw_asistencias = $stmt_asis->fetchAll(PDO::FETCH_ASSOC);

// Organizar asistencias
// Formato: $matrix[alumno_id][dia] = inicial
$matrix = [];
foreach ($raw_asistencias as $row) {
    $inicial = '';
    $tipo = strtolower($row['tipo']);
    if (strpos($tipo, 'presente') !== false) {
        $inicial = 'P';
    } elseif (strpos($tipo, 'justificado') !== false) {
        $inicial = 'AJ';
    } elseif (strpos($tipo, 'ausente') !== false) {
        $inicial = 'A';
    } elseif (strpos($tipo, 'tarde') !== false) {
        $inicial = 'T';
    } else {
        $inicial = strtoupper(substr($row['tipo'], 0, 1));
    }
    $matrix[$row['alumno_id']][$row['dia']] = $inicial;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla Mensual de Asistencia - <?php echo htmlspecialchars($curso_nombre . ' - ' . $mes_nombre . ' ' . $anio); ?></title>
    <style>
        /* Configuramos tamaño y orientación */
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #333;
        }
        .info-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        th.alumno-col {
            width: 200px;
            text-align: left;
            padding-left: 8px;
        }
        td.alumno-col {
            text-align: left;
            padding-left: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
        th.dia-col {
            width: 25px;
        }
        /* Para que en impresión el background-color aparezca obligatoriamente */
        .weekend {
            background-color: #e0e0e0 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .leyenda {
            margin-top: 15px;
            font-size: 10px;
            display: flex;
            gap: 20px;
            justify-content: center;
        }
        .leyenda div {
            border: 1px solid #000;
            padding: 3px 8px;
            background-color: #f9f9f9 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        /* Ocultar botones al imprimir */
        @media print {
            .no-print {
                display: none !important;
            }
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .print-btn:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>

<button class="print-btn no-print" onclick="window.print();">Imprimir Planilla</button>

<div class="header">
    <h1>Planilla Mensual de Asistencia</h1>
    <h2>Mes: <?php echo $mes_nombre; ?> <?php echo $anio; ?> | <?php echo htmlspecialchars($ciclo_nombre); ?></h2>
</div>

<div class="info-bar">
    <div>CURSO: <?php echo htmlspecialchars($curso_nombre); ?></div>
    <div>PRECEPTOR/A: _______________________</div>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2">Nº</th>
            <th rowspan="2" class="alumno-col">Apellido y Nombre</th>
            <th colspan="<?php echo $dias_del_mes; ?>">Días del Mes</th>
        </tr>
        <tr>
            <?php 
            for ($d = 1; $d <= $dias_del_mes; $d++) {
                $fecha_actual = sprintf('%04d-%02d-%02d', $anio, $mes, $d);
                $num_dia = date('N', strtotime($fecha_actual)); // 1 (lunes) a 7 (domingo)
                $is_weekend = ($num_dia == 6 || $num_dia == 7);
                $class = $is_weekend ? 'dia-col weekend' : 'dia-col';
                echo "<th class='{$class}'>{$d}</th>";
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php if (count($alumnos) > 0): ?>
            <?php foreach ($alumnos as $index => $alum): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td class="alumno-col"><?php echo htmlspecialchars($alum['alumno']); ?></td>
                    <?php 
                    for ($d = 1; $d <= $dias_del_mes; $d++) {
                        $fecha_actual = sprintf('%04d-%02d-%02d', $anio, $mes, $d);
                        $num_dia = date('N', strtotime($fecha_actual));
                        $is_weekend = ($num_dia == 6 || $num_dia == 7);
                        $class = $is_weekend ? 'weekend' : '';
                        
                        $valor = $matrix[$alum['id']][$d] ?? '';
                        echo "<td class='{$class}'><strong>{$valor}</strong></td>";
                    }
                    ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo $dias_del_mes + 2; ?>" style="padding: 20px;">No hay alumnos registrados en este curso para el ciclo lectivo seleccionado.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="leyenda">
    <div><strong>P</strong> = Presente</div>
    <div><strong>A</strong> = Ausente</div>
    <div><strong>T</strong> = Tarde</div>
    <div><strong>AJ</strong> = Ausente Justificado</div>
</div>

</body>
</html>
