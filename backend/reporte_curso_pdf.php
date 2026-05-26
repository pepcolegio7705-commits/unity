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

$curso_id_hash = $_GET['curso_id'] ?? '';
$curso_id = decrypt_id($curso_id_hash);

if (empty($curso_id)) {
    die("ID de curso inválido");
}

// Obtener datos del curso
$stmt = $pdo->prepare("SELECT nombre, turno FROM cursos WHERE id = :id");
$stmt->execute(['id' => $curso_id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$curso) {
    die("Curso no encontrado");
}

// Obtener alumnos
$sql = "
    SELECT a.alumno, a.dni, a.legajo, a.libro, a.folio
    FROM asignaciones_cursos ac
    JOIN lista_alfa a ON ac.alumno_id = a.id
    WHERE ac.curso_id = :curso_id
    ORDER BY a.alumno ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['curso_id' => $curso_id]);
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

class PDF extends FPDF {
    public $cursoNombre;
    public $cursoTurno;

    function Header() {
        // Asegurar que la imagen sea compatible con FPDF (JPG)
        $logo_path = '../assets/img/logo.png';
        $logo_jpg = '../assets/img/logo_fpdf.jpg';
        
        if (!file_exists($logo_jpg) && file_exists($logo_path)) {
            $img = @imagecreatefromstring(file_get_contents($logo_path));
            if ($img !== false) {
                $bg = imagecreatetruecolor(imagesx($img), imagesy($img));
                imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
                imagejpeg($bg, $logo_jpg, 90);
                imagedestroy($img);
                imagedestroy($bg);
            }
        }
        
        $image_to_use = file_exists($logo_jpg) ? $logo_jpg : $logo_path;
        if (file_exists($image_to_use)) {
            try {
                $this->Image($image_to_use, 10, 8, 30);
            } catch (Exception $e) {}
        }

        // Arial bold 15
        $this->SetFont('Arial', 'B', 16);
        // Título del Establecimiento
        $this->Cell(40); // Move to right
        $this->Cell(110, 10, to_iso('Sintek - Sistema Escolar'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40);
        $this->Cell(110, 8, to_iso('Lista de Alumnos Inscriptos'), 0, 1, 'C');

        $this->SetFont('Arial', 'I', 11);
        $this->Cell(40);
        $this->Cell(110, 8, to_iso('Curso: ' . $this->cursoNombre . ' - Turno: ' . $this->cursoTurno), 0, 1, 'C');
        $this->Ln(15);

        // Cabecera de la tabla
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(200, 200, 200);
        $this->Cell(10, 10, '#', 1, 0, 'C', true);
        $this->Cell(70, 10, to_iso('Alumno'), 1, 0, 'C', true);
        $this->Cell(25, 10, 'DNI', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Libro', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Folio', 1, 0, 'C', true);
        $this->Cell(35, 10, 'Legajo', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, to_iso('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->cursoNombre = $curso['nombre'];
$pdf->cursoTurno = $curso['turno'];
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10); // Reducido a 10 para que entre mejor todo

$contador = 1;
foreach ($alumnos as $al) {
    // Truncar nombre si es muy largo
    $nombre = strlen($al['alumno']) > 32 ? substr($al['alumno'], 0, 30) . '..' : $al['alumno'];
    
    $pdf->Cell(10, 10, $contador, 1, 0, 'C');
    $pdf->Cell(70, 10, to_iso($nombre), 1, 0, 'L');
    $pdf->Cell(25, 10, to_iso($al['dni']), 1, 0, 'C');
    $pdf->Cell(25, 10, to_iso($al['libro']), 1, 0, 'C');
    $pdf->Cell(25, 10, to_iso($al['folio']), 1, 0, 'C');
    $pdf->Cell(35, 10, to_iso($al['legajo']), 1, 1, 'C');
    $contador++;
}

$pdf->Output('I', 'Lista_Alumnos_' . str_replace(" ", "_", $curso['nombre']) . '.pdf');
?>
