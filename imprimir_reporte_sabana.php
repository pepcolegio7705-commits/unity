<?php
session_start();
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'fpdf/fpdf.php';

// Verificación de acceso
$rol = $_SESSION['rol'] ?? '';
if (!in_array($rol, ['Administrador', 'Directivo', 'Secretario', 'Preceptor'])) {
    die("Acceso denegado.");
}

$ciclo_id = intval($_GET['ciclo_id'] ?? 0);
$curso_id = intval($_GET['curso_id'] ?? 0);
$instancia_id = intval($_GET['instancia_id'] ?? 0);

if (!$ciclo_id || !$curso_id || !$instancia_id) {
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

$stmtInsta = $pdo->prepare("SELECT nombre FROM instancias_calificacion WHERE id = ?");
$stmtInsta->execute([$instancia_id]);
$instancia_nombre = $stmtInsta->fetchColumn();

$stmtCiclo = $pdo->prepare("SELECT nombre FROM ciclos_lectivos WHERE id = ?");
$stmtCiclo->execute([$ciclo_id]);
$ciclo_nombre = $stmtCiclo->fetchColumn();

// 3. Obtener Datos
$stmtAlu = $pdo->prepare("
    SELECT a.id, a.alumno as nombre
    FROM asignaciones_cursos ac
    JOIN lista_alfa a ON ac.alumno_id = a.id
    WHERE ac.curso_id = ?
    ORDER BY a.alumno ASC
");
$stmtAlu->execute([$curso_id]);
$alumnos = $stmtAlu->fetchAll(PDO::FETCH_ASSOC);

$stmtMat = $pdo->prepare("
    SELECT DISTINCT e.id, e.asignatura 
    FROM calificaciones c
    JOIN espacios_curriculares e ON c.materia_id = e.id
    WHERE c.curso_id = ? AND c.instancia_id = ?
    ORDER BY e.asignatura ASC
");
$stmtMat->execute([$curso_id, $instancia_id]);
$materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

if (empty($materias)) {
    $stmtMatFallback = $pdo->prepare("
        SELECT DISTINCT e.id, e.asignatura 
        FROM asignaciones_docentes ad
        JOIN espacios_curriculares e ON ad.materia_id = e.id
        WHERE ad.curso_id = ? AND ad.activo = 1
        ORDER BY e.asignatura ASC
    ");
    $stmtMatFallback->execute([$curso_id]);
    $materias = $stmtMatFallback->fetchAll(PDO::FETCH_ASSOC);
}

$stmtNotas = $pdo->prepare("
    SELECT alumno_id, materia_id, nota 
    FROM calificaciones 
    WHERE curso_id = ? AND instancia_id = ?
");
$stmtNotas->execute([$curso_id, $instancia_id]);
$notasRaw = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

$notas = [];
foreach ($notasRaw as $n) {
    $notas[$n['alumno_id']][$n['materia_id']] = $n['nota'];
}

// Extend FPDF
class PDF extends FPDF {
    public $inst_nombre;
    public $curso_nombre;
    public $instancia_nombre;
    public $ciclo_nombre;
    public $logo;
    public $materias;

    function Header() {
        if ($this->logo && file_exists($this->logo)) {
            $fpdf_logo = 'assets/img/logo_fpdf.jpg';
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
        $this->Cell(0, 8, mb_convert_encoding('BOLETÍN GENERAL (SÁBANA) - ' . $this->instancia_nombre, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, 'Curso: ' . mb_convert_encoding($this->curso_nombre, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Ln(5);

        // Landscape Legal size has 355 width. Let's assume A4 Landscape (297mm width). Margin is 10mm each side = 277mm usable.
        // We might need to use Legal size later if it's too tight.
        $totalWidth = 277;
        $nameWidth = 60;
        $num_mat = count($this->materias);
        $matWidth = $num_mat > 0 ? floor(($totalWidth - $nameWidth) / $num_mat) : 0;
        
        // Header Row
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(230, 230, 230);
        $this->Cell($nameWidth, 8, 'Apellido y Nombre', 1, 0, 'C', true);
        
        foreach ($this->materias as $mat) {
            $nombre_mat = mb_convert_encoding($mat['asignatura'], 'ISO-8859-1', 'UTF-8');
            // Abreviar el nombre para que entre en la celda
            if (strlen($nombre_mat) > 10) {
                $nombre_mat = substr($nombre_mat, 0, 8) . '.';
            }
            $this->Cell($matWidth, 8, $nombre_mat, 1, 0, 'C', true);
        }
        $this->Ln();
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb} - Generado el ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
}

// Inicializar PDF (Orientación Landscape, A4)
$pdf = new PDF('L', 'mm', 'A4');
$pdf->inst_nombre = $nombre_institucion;
$pdf->curso_nombre = $curso_nombre;
$pdf->instancia_nombre = $instancia_nombre;
$pdf->ciclo_nombre = $ciclo_nombre;
$pdf->logo = $logo_path;
$pdf->materias = $materias;

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);

$totalWidth = 277;
$nameWidth = 60;
$num_mat = count($materias);
$matWidth = $num_mat > 0 ? floor(($totalWidth - $nameWidth) / $num_mat) : 0;

if (count($alumnos) == 0) {
    $pdf->Cell(0, 10, 'No hay alumnos inscriptos en este curso.', 1, 1, 'C');
} else {
    foreach ($alumnos as $a) {
        $nombre = mb_convert_encoding($a['nombre'], 'ISO-8859-1', 'UTF-8');
        if (strlen($nombre) > 30) {
            $nombre = substr($nombre, 0, 28) . '...';
        }
        
        $pdf->Cell($nameWidth, 7, $nombre, 1, 0, 'L');
        
        foreach ($materias as $mat) {
            $nota = $notas[$a['id']][$mat['id']] ?? '-';
            $nota = $nota === '' ? '-' : $nota;
            $pdf->Cell($matWidth, 7, mb_convert_encoding($nota, 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        }
        $pdf->Ln();
    }
}

$pdf->Output('I', 'Sabana_' . str_replace(' ', '_', $curso_nombre) . '.pdf');
?>
