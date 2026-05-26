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

$curso_id = $_GET['curso_id'] ?? 0;
$instancia_id = $_GET['instancia_id'] ?? 0;

if (!$curso_id || !$instancia_id) {
    die("Parámetros incompletos");
}

// Obtener Curso
$stmtCurso = $pdo->prepare("SELECT nombre, turno FROM cursos WHERE id = :id");
$stmtCurso->execute(['id' => $curso_id]);
$curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);

if (!$curso) {
    die("Curso no encontrado.");
}

// Obtener Instancia
$stmtInst = $pdo->prepare("
    SELECT i.nombre, i.tipo, c.nombre AS ciclo_nombre
    FROM instancias_calificacion i
    JOIN ciclos_lectivos c ON i.ciclo_lectivo_id = c.id
    WHERE i.id = :id
");
$stmtInst->execute(['id' => $instancia_id]);
$instancia = $stmtInst->fetch(PDO::FETCH_ASSOC);

if (!$instancia) {
    die("Instancia no encontrada.");
}

// Obtener Alumnos
$stmtAlumnos = $pdo->prepare("
    SELECT lh.id, lh.alumno
    FROM asignaciones_cursos ac
    INNER JOIN lista_alfa lh ON lh.id = ac.alumno_id
    WHERE ac.curso_id = :id
    ORDER BY lh.alumno
");
$stmtAlumnos->execute(['id' => $curso_id]);
$alumnos = $stmtAlumnos->fetchAll(PDO::FETCH_ASSOC);

if (empty($alumnos)) {
    die("El curso no tiene alumnos asignados.");
}

// Obtener Materias
$stmtMaterias = $pdo->prepare("SELECT id, nombre FROM materias WHERE curso_id = :id ORDER BY nombre");
$stmtMaterias->execute(['id' => $curso_id]);
$materias = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);

if (empty($materias)) {
    die("El curso no tiene materias asignadas.");
}

// Obtener Notas
$stmtNotas = $pdo->prepare("
    SELECT alumno_id, materia_id, nota
    FROM calificaciones
    WHERE curso_id = :curso_id AND instancia_id = :instancia_id
");
$stmtNotas->execute(['curso_id' => $curso_id, 'instancia_id' => $instancia_id]);
$notasRaw = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

$notasMap = [];
foreach ($notasRaw as $nr) {
    $key = $nr['alumno_id'] . '_' . $nr['materia_id'];
    $notasMap[$key] = $nr['nota'];
}

// Mapear Notas a Alumnos
foreach ($alumnos as &$al) {
    $al['notas'] = [];
    foreach ($materias as $mat) {
        $key = $al['id'] . '_' . $mat['id'];
        $al['notas'][$mat['id']] = $notasMap[$key] ?? "-";
    }
}

class PDF extends FPDF {
    public $titulo;
    public $subtitulo;

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
        $this->Cell(40);
        $this->Cell(190, 10, to_iso('Sintek - Sistema Escolar'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(40);
        $this->Cell(190, 8, to_iso($this->titulo), 0, 1, 'C');

        $this->SetFont('Arial', 'I', 11);
        $this->Cell(40);
        $this->Cell(190, 8, to_iso($this->subtitulo), 0, 1, 'C');
        $this->Ln(15);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, to_iso('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('L', 'mm', 'A4'); // Horizontal
$pdf->titulo = 'Boletín Matriz - ' . $instancia['nombre'] . ' (' . $instancia['tipo'] . ')';
$pdf->subtitulo = 'Curso: ' . $curso['nombre'] . ' | Turno: ' . $curso['turno'] . ' | Ciclo: ' . $instancia['ciclo_nombre'];

$pdf->AliasNbPages();
$pdf->AddPage();

// Calculate widths
$usadoTotal = 277; // Margen izq y der son 10 + 10 = 20, ancho A4 L = 297, usable = 277
$anchoAlumno = 60;
$anchoMateria = count($materias) > 0 ? ($usadoTotal - $anchoAlumno) / count($materias) : 0;

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(220, 220, 220);

// Cabecera de la tabla
$pdf->Cell($anchoAlumno, 10, 'Alumno', 1, 0, 'C', true);

foreach ($materias as $mat) {
    // Acortar nombre materia si es muy largo
    $nombreMateria = strlen($mat['nombre']) > 15 ? substr($mat['nombre'], 0, 13) . '..' : $mat['nombre'];
    $pdf->Cell($anchoMateria, 10, to_iso($nombreMateria), 1, 0, 'C', true);
}
$pdf->Ln();

// Cuerpo
$pdf->SetFont('Arial', '', 9);

foreach ($alumnos as $al) {
    // Check if we need to break page
    if ($pdf->GetY() > 180) {
        $pdf->AddPage();
        // Redraw header
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($anchoAlumno, 10, 'Alumno', 1, 0, 'C', true);
        foreach ($materias as $mat) {
            $nombreMateria = strlen($mat['nombre']) > 15 ? substr($mat['nombre'], 0, 13) . '..' : $mat['nombre'];
            $pdf->Cell($anchoMateria, 10, to_iso($nombreMateria), 1, 0, 'C', true);
        }
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);
    }

    $nombreAlumno = strlen($al['alumno']) > 30 ? substr($al['alumno'], 0, 28) . '..' : $al['alumno'];
    $pdf->Cell($anchoAlumno, 8, to_iso($nombreAlumno), 1, 0, 'L');

    foreach ($materias as $mat) {
        $nota = $al['notas'][$mat['id']];
        // Resaltar notas bajas?
        if ($nota !== '-' && is_numeric($nota) && floatval($nota) < 4) {
            $pdf->SetTextColor(200, 0, 0); // Rojo
        } elseif ($nota !== '-' && is_numeric($nota) && floatval($nota) >= 7) {
            $pdf->SetTextColor(0, 120, 0); // Verde
        } else {
            $pdf->SetTextColor(0, 0, 0);
        }

        $pdf->Cell($anchoMateria, 8, to_iso($nota), 1, 0, 'C');
    }
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Ln();
}

$pdf->Output('I', 'Boletin_' . str_replace(" ", "_", $curso['nombre']) . '.pdf');
?>
