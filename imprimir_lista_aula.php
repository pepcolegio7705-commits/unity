<?php
session_start();
require_once 'config/database.php';
require_once 'config/security.php';
require_once 'fpdf/fpdf.php';

// Validar login (Docentes y Admins pueden imprimir)
if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado.");
}

$curso_id = intval($_GET['curso_id'] ?? 0);
$materia_id = intval($_GET['materia_id'] ?? 0);

if (!$curso_id || !$materia_id) {
    die("Faltan parámetros.");
}

// 1. Obtener Datos de Institución
$stmtInst = $pdo->query("SELECT * FROM institucion LIMIT 1");
$institucion = $stmtInst->fetch(PDO::FETCH_ASSOC);
$nombre_institucion = $institucion ? mb_convert_encoding($institucion['nombre'], 'ISO-8859-1', 'UTF-8') : 'ESCUELA';
$logo_path = ($institucion && !empty($institucion['logo_path']) && file_exists($institucion['logo_path'])) ? $institucion['logo_path'] : '';

// 2. Obtener Info del Curso y Materia
$stmtInfo = $pdo->prepare("
    SELECT c.nombre as curso, e.asignatura as materia 
    FROM cursos c, espacios_curriculares e 
    WHERE c.id = ? AND e.id = ?
");
$stmtInfo->execute([$curso_id, $materia_id]);
$info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    die("Curso o Materia no encontrados.");
}

$curso_nombre = mb_convert_encoding($info['curso'], 'ISO-8859-1', 'UTF-8');
$materia_nombre = mb_convert_encoding($info['materia'], 'ISO-8859-1', 'UTF-8');
$anio_actual = date('Y');

// 3. Obtener Alumnos
$stmtAlu = $pdo->prepare("
    SELECT a.alumno as nombre_alumno, a.dni, a.legajo
    FROM asignaciones_cursos ac
    JOIN lista_alfa a ON ac.alumno_id = a.id
    WHERE ac.curso_id = ?
    ORDER BY a.alumno ASC
");
$stmtAlu->execute([$curso_id]);
$alumnos = $stmtAlu->fetchAll(PDO::FETCH_ASSOC);

// Extend FPDF to create custom header and footer
class PDF extends FPDF {
    public $inst_nombre;
    public $curso_nombre;
    public $materia_nombre;
    public $anio;
    public $logo;

    function Header() {
        if ($this->logo && file_exists($this->logo)) {
            $fpdf_logo = 'assets/img/logo_fpdf.jpg';
            // Generar JPG temporal si no existe o si el logo.png fue modificado recientemente
            if (!file_exists($fpdf_logo) || filemtime($this->logo) > filemtime($fpdf_logo)) {
                $img_data = @file_get_contents($this->logo);
                if ($img_data) {
                    $im = @imagecreatefromstring($img_data);
                    if ($im !== false) {
                        // Create white background for transparent images
                        $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
                        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                        imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
                        imagejpeg($bg, $fpdf_logo, 90);
                        imagedestroy($bg);
                        imagedestroy($im);
                    }
                }
            }
            
            // Usar el JPG convertido si se generó con éxito
            if (file_exists($fpdf_logo)) {
                $this->Image($fpdf_logo, 10, 8, 25);
            }
        }
        
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(30); // Move right if logo exists
        $this->Cell(0, 8, $this->inst_nombre, 0, 1, 'L');
        
        $this->SetFont('Arial', '', 11);
        $this->Cell(30);
        $this->Cell(0, 6, 'Ciclo Lectivo: ' . $this->anio, 0, 1, 'L');
        
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'PLANILLA DE ASISTENCIA / NOTAS', 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, 'Curso: ' . $this->curso_nombre . '   |   Materia: ' . $this->materia_nombre, 0, 1, 'C');
        $this->Ln(5);

        // Table Header
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(10, 8, 'N', 1, 0, 'C', true);
        $this->Cell(65, 8, 'Apellido y Nombre', 1, 0, 'C', true);
        $this->Cell(25, 8, 'DNI', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Legajo', 1, 0, 'C', true);
        $this->Cell(70, 8, 'Firma / Notas (Uso interno)', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb} - Generado el ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->inst_nombre = $nombre_institucion;
$pdf->curso_nombre = $curso_nombre;
$pdf->materia_nombre = $materia_nombre;
$pdf->anio = $anio_actual;
$pdf->logo = $logo_path;

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

if (count($alumnos) == 0) {
    $pdf->Cell(0, 10, 'No hay alumnos inscriptos en este curso.', 1, 1, 'C');
} else {
    $i = 1;
    foreach ($alumnos as $a) {
        $nombre = mb_convert_encoding($a['nombre_alumno'], 'ISO-8859-1', 'UTF-8');
        
        // Truncate name if too long
        if (strlen($nombre) > 40) {
            $nombre = substr($nombre, 0, 37) . '...';
        }
        
        $pdf->Cell(10, 8, $i, 1, 0, 'C');
        $pdf->Cell(65, 8, $nombre, 1, 0, 'L');
        $pdf->Cell(25, 8, $a['dni'], 1, 0, 'C');
        $pdf->Cell(20, 8, $a['legajo'], 1, 0, 'C');
        $pdf->Cell(70, 8, '', 1, 1, 'C'); // Empty for signatures
        $i++;
    }
}

$pdf->Output('I', 'Lista_' . str_replace(' ', '_', $info['curso']) . '.pdf');
?>
