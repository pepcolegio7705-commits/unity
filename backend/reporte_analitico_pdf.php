<?php
session_start();
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../fpdf/fpdf.php';

function to_iso($string) {
    return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
}

if (!isset($_SESSION['usuario_id'])) {
    die("Acceso denegado");
}

$alumno_id_hash = $_GET['alumno_id'] ?? '';
$alumno_id = decrypt_id($alumno_id_hash);

if (empty($alumno_id)) {
    die("ID de alumno inválido.");
}

// 1. Obtener Datos Básicos del Alumno
$stmtAlum = $pdo->prepare("SELECT alumno, dni, fechan, lugar, libro, folio FROM lista_alfa WHERE id = :id");
$stmtAlum->execute(['id' => $alumno_id]);
$alumno = $stmtAlum->fetch(PDO::FETCH_ASSOC);

if (!$alumno) {
    die("Alumno no encontrado.");
}

// 2. Obtener Datos Cabecera
$stmtCab = $pdo->prepare("SELECT * FROM analiticos_cabecera WHERE alumno_id = :id");
$stmtCab->execute(['id' => $alumno_id]);
$cabecera = $stmtCab->fetch(PDO::FETCH_ASSOC);

// Parsear fecha nacimiento
$diaNac = $mesNac = $anioNac = "";
if (!empty($alumno['fechan'])) {
    $parts = explode("-", $alumno['fechan']);
    if (count($parts) == 3) {
        $anioNac = $parts[0];
        $mesNac = get_mes_nombre(intval($parts[1]));
        $diaNac = $parts[2];
    }
}

function get_mes_nombre($m) {
    $meses = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
    return $meses[$m] ?? "";
}

// 3. Obtener Notas
$stmtNotas = $pdo->prepare("SELECT * FROM analiticos_notas WHERE alumno_id = :id ORDER BY anio_estudio ASC, id ASC");
$stmtNotas->execute(['id' => $alumno_id]);
$notasRaw = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

$notas_por_anio = [];
for ($i=1; $i<=6; $i++) {
    $notas_por_anio[$i] = [];
}
foreach ($notasRaw as $n) {
    $notas_por_anio[$n['anio_estudio']][] = $n;
}

// 4. Obtener Observaciones
$stmtObs = $pdo->prepare("SELECT anio_estudio, observacion FROM analiticos_observaciones WHERE alumno_id = :id");
$stmtObs->execute(['id' => $alumno_id]);
$obsRaw = $stmtObs->fetchAll(PDO::FETCH_ASSOC);
$observaciones = [];
foreach ($obsRaw as $o) {
    $observaciones[$o['anio_estudio']] = $o['observacion'];
}

// ============================================
// CONSTRUCCIÓN DEL PDF
// ============================================
class AnaliticoPDF extends FPDF {
    
    function drawHeadersTable() {
        $this->SetFont('Arial', 'B', 7);
        // Alto de la fila cabecera
        $h = 10;
        
        $y = $this->GetY();
        $x = $this->GetX();
        
        // Fila superior doble
        $this->Cell(6, $h, "N de", 'LTR', 0, 'C');
        $this->Cell(55, $h, "ASIGNATURA", 1, 0, 'C');
        
        // CALIFICACION (subdividida)
        $this->Cell(28, $h/2, "CALIFICACION", 1, 0, 'C');
        $this->SetXY($x + 6 + 55, $y + $h/2);
        $this->Cell(8, $h/2, to_iso("Nº"), 1, 0, 'C');
        $this->Cell(20, $h/2, "LETRAS", 1, 0, 'C');
        $this->SetXY($x + 6 + 55 + 28, $y); // volver
        
        $this->Cell(35, $h, to_iso("Condición y Establecimiento"), 1, 0, 'C');
        $this->Cell(12, $h, to_iso("Acta Nº"), 1, 0, 'C');
        $this->Cell(15, $h, "Fecha", 1, 0, 'C');
        
        // REPITE (subdividida)
        $this->Cell(25, $h/2, "REPITE", 1, 0, 'C');
        $this->SetXY($x + 6 + 55 + 28 + 35 + 12 + 15, $y + $h/2);
        $this->Cell(10, $h/2, "Nota", 1, 0, 'C');
        $this->Cell(15, $h/2, "Fecha", 1, 0, 'C');
        $this->SetXY($x + 6 + 55 + 28 + 35 + 12 + 15 + 25, $y); // volver
        
        $this->Cell(14, $h/2, to_iso("Calific."), 'LTR', 2, 'C');
        $this->Cell(14, $h/2, "Definitiva", 'LBR', 0, 'C');
        
        $this->SetXY($x, $y + $h/2);
        $this->Cell(6, $h/2, "Orden", 'LBR', 0, 'C');
        $this->SetXY($x, $y + $h); // Siguiente linea
    }
}

$pdf = new AnaliticoPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// TEXTO SUPERIOR (DATOS DEL ALUMNO)
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(10, 8, 'Don..', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(120, 8, to_iso(strtoupper($alumno['alumno'])), 'B', 0, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(25, 8, to_iso('Archivo Nº'), 0, 0, 'R');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(35, 8, to_iso($cabecera['archivo_no'] ?? ''), 'B', 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(15, 8, 'Libro:', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(20, 8, to_iso($alumno['libro']), 'B', 0, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(15, 8, 'Folio:', 0, 0, 'R');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(20, 8, to_iso($alumno['folio']), 'B', 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(20, 8, 'Nacido en..', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(50, 8, to_iso($alumno['lugar']), 'B', 0, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(15, 8, to_iso('el día'), 0, 0, 'C');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(15, 8, $diaNac, 'B', 0, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(10, 8, 'de', 0, 0, 'C');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(35, 8, to_iso($mesNac), 'B', 0, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(10, 8, 'de', 0, 0, 'C');
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(35, 8, $anioNac, 'B', 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(70, 8, 'Tipo y numero de Documento de Identidad..', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(120, 8, 'D.N.I. ' . $alumno['dni'], 'B', 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 8, to_iso('Ingresó con certificado de..'), 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(140, 8, to_iso($cabecera['escuela_procedencia'] ?? ''), 'B', 1, 'L');

$pdf->Ln(2);

// LOOP POR AÑOS (1 A 6)
$nombres_anios = ["", "PRIMER AÑO", "SEGUNDO AÑO", "TERCER AÑO", "CUARTO AÑO", "QUINTO AÑO", "SEXTO AÑO"];
$pdf->drawHeadersTable();

foreach ($notas_por_anio as $anio => $materias) {
    // Forzar salto de página en el 4to año por normativa
    if ($anio == 4) {
        $pdf->AddPage();
        $pdf->drawHeadersTable();
    }

    // Eliminado el "continue" para forzar que siempre se dibujen todos los años (1° a 6° Año)
    // tal como exige el formato oficial del Libro Matriz, aunque no tengan materias cargadas.

    $startY = $pdf->GetY();
    
    // Reducir la altura de la fila para que 3 años quepan en 1 página
    $h = 4.5;
    $numMaterias = max(count($materias), 4); // minimo 4 lineas para que quede estetico
    
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(230,230,230);
    $pdf->Cell(190, 4.5, to_iso($nombres_anios[$anio]), 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 8);
    for ($j = 0; $j < $numMaterias; $j++) {
        $m = $materias[$j] ?? null;
        
        $pdf->Cell(6, $h, $m ? ($j+1) : '', 1, 0, 'C');
        
        // Auto-shrink font para Asignatura
        $asigText = to_iso($m['asignatura'] ?? '');
        $pdf->SetFont('Arial', '', 8);
        $fsAsig = 8;
        while ($pdf->GetStringWidth($asigText) > 53 && $fsAsig > 4) {
            $fsAsig -= 0.5;
            $pdf->SetFont('Arial', '', $fsAsig);
        }
        $pdf->Cell(55, $h, $asigText, 1, 0, 'L');
        $pdf->SetFont('Arial', '', 8); // Restaurar
        
        $pdf->Cell(8, $h, to_iso($m['calificacion_num'] ?? ''), 1, 0, 'C');
        $pdf->Cell(20, $h, to_iso($m['calificacion_letras'] ?? ''), 1, 0, 'C');
        
        // Auto-shrink font para Establecimiento
        $condText = to_iso($m['condicion_establecimiento'] ?? '');
        $pdf->SetFont('Arial', '', 8);
        $fsCond = 8;
        while ($pdf->GetStringWidth($condText) > 33 && $fsCond > 4) {
            $fsCond -= 0.5;
            $pdf->SetFont('Arial', '', $fsCond);
        }
        $pdf->Cell(35, $h, $condText, 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8); // Restaurar
        
        $pdf->Cell(12, $h, to_iso($m['acta_num'] ?? ''), 1, 0, 'C');
        $pdf->Cell(15, $h, to_iso($m['fecha'] ?? ''), 1, 0, 'C');
        $pdf->Cell(10, $h, to_iso($m['repite_nota'] ?? ''), 1, 0, 'C');
        $pdf->Cell(15, $h, to_iso($m['repite_fecha'] ?? ''), 1, 0, 'C');
        $pdf->Cell(14, $h, to_iso($m['calificacion_definitiva'] ?? ''), 1, 1, 'C');
    }
    
    // Observaciones
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(25, 4.5, "Observaciones:", 'L', 0, 'L');
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(165, 4.5, to_iso($observaciones[$anio] ?? ''), 'R', 1, 'L');
    
    // Cierre del bloque
    $pdf->Cell(190, 0, '', 'T', 1);
}

// Salida
$pdf->Output('I', 'Analitico_' . $alumno['dni'] . '.pdf');
?>
