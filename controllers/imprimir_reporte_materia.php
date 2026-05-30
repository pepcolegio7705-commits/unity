<?php
session_start();
require_once '../config/database.php';
require_once '../config/security.php';
require_once '../fpdf/fpdf.php';

// Verificación de acceso
$rol = $_SESSION['rol'] ?? '';
if (!in_array($rol, ['Administrador', 'Directivo', 'Secretario', 'Preceptor'])) {
    die("Acceso denegado.");
}

$ciclo_id = intval($_GET['ciclo_id'] ?? 0);
$curso_id = intval($_GET['curso_id'] ?? 0);
$materia_id = intval($_GET['materia_id'] ?? 0);

if (!$ciclo_id || !$curso_id || !$materia_id) {
    die("Faltan parámetros.");
}

// 1. Obtener Datos de Institución
$stmtInstDb = $pdo->query("SELECT * FROM institucion LIMIT 1");
$institucion = $stmtInstDb->fetch(PDO::FETCH_ASSOC);
$nombre_institucion = $institucion ? mb_convert_encoding($institucion['nombre'], 'ISO-8859-1', 'UTF-8') : 'ESCUELA';
$logo_path = ($institucion && !empty($institucion['logo_path']) && file_exists($institucion['logo_path'])) ? $institucion['logo_path'] : '';

// 2. Obtener Info del Reporte
$stmtCurso = $pdo->prepare("SELECT nombre FROM cursos WHERE id = ?");
$stmtCurso->execute([$curso_id]);
$curso_nombre = $stmtCurso->fetchColumn();

$stmtMateria = $pdo->prepare("SELECT asignatura FROM espacios_curriculares WHERE id = ?");
$stmtMateria->execute([$materia_id]);
$materia_nombre = $stmtMateria->fetchColumn();

$stmtCiclo = $pdo->prepare("SELECT nombre FROM ciclos_lectivos WHERE id = ?");
$stmtCiclo->execute([$ciclo_id]);
$ciclo_nombre = $stmtCiclo->fetchColumn();

// 3. Obtener Datos
$stmtInst = $pdo->prepare("SELECT id, nombre FROM instancias_calificacion WHERE ciclo_lectivo_id = ? ORDER BY id ASC");
$stmtInst->execute([$ciclo_id]);
$instancias = $stmtInst->fetchAll(PDO::FETCH_ASSOC);

$stmtAlu = $pdo->prepare("
    SELECT a.id, a.alumno as nombre
    FROM asignaciones_cursos ac
    JOIN lista_alfa a ON ac.alumno_id = a.id
    WHERE ac.curso_id = ?
    ORDER BY a.alumno ASC
");
$stmtAlu->execute([$curso_id]);
$alumnos = $stmtAlu->fetchAll(PDO::FETCH_ASSOC);

$stmtNotas = $pdo->prepare("
    SELECT alumno_id, instancia_id, nota 
    FROM calificaciones 
    WHERE curso_id = ? AND materia_id = ?
");
$stmtNotas->execute([$curso_id, $materia_id]);
$notasRaw = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

$notas = [];
foreach ($notasRaw as $n) {
    $notas[$n['alumno_id']][$n['instancia_id']] = $n['nota'];
}

// Extend FPDF
class PDF extends FPDF {
    public $inst_nombre;
    public $curso_nombre;
    public $materia_nombre;
    public $ciclo_nombre;
    public $logo;
    public $instancias;

    function Header() {
        if ($this->logo && file_exists($this->logo)) {
            $fpdf_logo = '../assets/img/logo_fpdf.jpg';
            if (!file_exists($fpdf_logo) || filemtime($this->logo) > filemtime($fpdf_logo)) {
                $img_data = @file_get_contents($this->logo);
                if ($img_data) {
                    $im = @imagecreatefromstring($img_data);
                    if ($im !== false) {
                        $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
                        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                        imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
                        imagejpeg($bg, $fpdf_logo, 90);
                        imagedestroy($bg);
                        imagedestroy($im);
                    }
                }
            }
            if (file_exists($fpdf_logo)) {
                $this->Image($fpdf_logo, 10, 8, 25);
            }
        }
        
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(30);
        $this->Cell(0, 8, mb_convert_encoding($this->inst_nombre, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        $this->SetFont('Arial', '', 11);
        $this->Cell(30);
        $this->Cell(0, 6, 'Ciclo Lectivo: ' . mb_convert_encoding($this->ciclo_nombre, 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'REPORTE DE CALIFICACIONES POR MATERIA', 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, 'Curso: ' . mb_convert_encoding($this->curso_nombre, 'ISO-8859-1', 'UTF-8') . '   |   Materia: ' . mb_convert_encoding($this->materia_nombre, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(5);

        // Calculate column widths
        // Available width in Landscape A4 is 297 - 20 = 277mm
        // Name col gets 80, the rest is divided by instances
        $totalWidth = 277;
        $nameWidth = 80;
        $num_inst = count($this->instancias);
        $instWidth = $num_inst > 0 ? floor(($totalWidth - $nameWidth) / $num_inst) : 0;
        
        // Header Row
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $this->Cell($nameWidth, 8, 'Apellido y Nombre', 1, 0, 'C', true);
        
        foreach ($this->instancias as $inst) {
            $nombre_inst = mb_convert_encoding($inst['nombre'], 'ISO-8859-1', 'UTF-8');
            if (strlen($nombre_inst) > 15) {
                $nombre_inst = substr($nombre_inst, 0, 13) . '.';
            }
            $this->Cell($instWidth, 8, $nombre_inst, 1, 0, 'C', true);
        }
        $this->Ln();
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb} - Generado el ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->inst_nombre = $nombre_institucion;
$pdf->curso_nombre = $curso_nombre;
$pdf->materia_nombre = $materia_nombre;
$pdf->ciclo_nombre = $ciclo_nombre;
$pdf->logo = $logo_path;
$pdf->instancias = $instancias;

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

$totalWidth = 277;
$nameWidth = 80;
$num_inst = count($instancias);
$instWidth = $num_inst > 0 ? floor(($totalWidth - $nameWidth) / $num_inst) : 0;

if (count($alumnos) == 0) {
    $pdf->Cell(0, 10, 'No hay alumnos inscriptos en este curso.', 1, 1, 'C');
} else {
    foreach ($alumnos as $a) {
        $nombre = mb_convert_encoding($a['nombre'], 'ISO-8859-1', 'UTF-8');
        if (strlen($nombre) > 40) {
            $nombre = substr($nombre, 0, 37) . '...';
        }
        
        $pdf->Cell($nameWidth, 8, $nombre, 1, 0, 'L');
        
        foreach ($instancias as $inst) {
            $nota = $notas[$a['id']][$inst['id']] ?? '-';
            $nota = $nota === '' ? '-' : $nota;
            $pdf->Cell($instWidth, 8, mb_convert_encoding($nota, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        }
        $pdf->Ln();
    }
}

$pdf->Output('I', 'Reporte_Materia_' . str_replace(' ', '_', $curso_nombre) . '.pdf');
?>
