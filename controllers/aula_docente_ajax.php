<?php
session_start();
require_once '../config/database.php';
require_once '../config/security.php';

$action = $_POST['action'] ?? '';
$rol = $_SESSION['rol'] ?? '';

// Verificamos ciclo
$anio_actual = date('Y');
$nombre_ciclo = "Ciclo Lectivo " . $anio_actual;
$stmtCiclo = $pdo->prepare("SELECT id FROM ciclos_lectivos WHERE nombre = ? LIMIT 1");
$stmtCiclo->execute([$nombre_ciclo]);
$ciclo = $stmtCiclo->fetch();
if (!$ciclo) {
    echo json_encode(['status' => 'error', 'msg' => 'Ciclo lectivo actual no configurado.']);
    exit;
}
$ciclo_id = $ciclo['id'];

switch ($action) {
    case 'cargar_aula':
        $curso_id = intval($_POST['curso_id'] ?? 0);
        $materia_id = intval($_POST['materia_id'] ?? 0);
        
        if (!$curso_id || !$materia_id) {
            echo json_encode(['status' => 'error', 'msg' => 'Faltan parámetros.']);
            exit;
        }

        // 1. Obtener Info
        $stmtInfo = $pdo->prepare("
            SELECT c.nombre as curso, e.asignatura as materia 
            FROM cursos c, espacios_curriculares e 
            WHERE c.id = ? AND e.id = ?
        ");
        $stmtInfo->execute([$curso_id, $materia_id]);
        $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);
        $info['ciclo'] = $nombre_ciclo;

        // 2. Obtener Alumnos del curso
        $stmtAlu = $pdo->prepare("
            SELECT a.id as alumno_id, a.alumno as nombre_alumno, a.dni, a.legajo
            FROM asignaciones_cursos ac
            JOIN lista_alfa a ON ac.alumno_id = a.id
            WHERE ac.curso_id = ?
            ORDER BY a.alumno ASC
        ");
        $stmtAlu->execute([$curso_id]);
        $alumnos = $stmtAlu->fetchAll(PDO::FETCH_ASSOC);

        // 3. Obtener Instancias de Calificación activas o inactivas
        $stmtInst = $pdo->prepare("SELECT id, nombre, activa, escala_notas FROM instancias_calificacion WHERE ciclo_lectivo_id = ? ORDER BY id ASC");
        $stmtInst->execute([$ciclo_id]);
        $instancias = $stmtInst->fetchAll(PDO::FETCH_ASSOC);

        // 4. Obtener notas actuales
        $stmtNotas = $pdo->prepare("
            SELECT alumno_id, instancia_id, nota 
            FROM calificaciones 
            WHERE curso_id = ? AND materia_id = ?
        ");
        $stmtNotas->execute([$curso_id, $materia_id]);
        $notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'info' => $info,
            'alumnos' => $alumnos,
            'instancias' => $instancias,
            'notas' => $notas
        ]);
        break;

    case 'guardar_notas':
        $curso_id = intval($_POST['curso_id'] ?? 0);
        $materia_id = intval($_POST['materia_id'] ?? 0);
        $notasPost = $_POST['notas'] ?? []; // formato: notas[alumno_id][instancia_id] = valor
        
        if (!$curso_id || !$materia_id || empty($notasPost)) {
            echo json_encode(['status' => 'error', 'msg' => 'No hay notas para guardar o faltan parámetros.']);
            exit;
        }

        // Obtener instancias activas para validar en servidor que no guarden en inactiva
        $stmtInst = $pdo->prepare("SELECT id FROM instancias_calificacion WHERE ciclo_lectivo_id = ? AND activa = 1");
        $stmtInst->execute([$ciclo_id]);
        $activas = $stmtInst->fetchAll(PDO::FETCH_COLUMN);

        try {
            $pdo->beginTransaction();

            $check = $pdo->prepare("SELECT id FROM calificaciones WHERE alumno_id = ? AND curso_id = ? AND materia_id = ? AND instancia_id = ?");
            $update = $pdo->prepare("UPDATE calificaciones SET nota = ?, fecha_registro = NOW() WHERE id = ?");
            $insert = $pdo->prepare("INSERT INTO calificaciones (alumno_id, curso_id, materia_id, instancia_id, nota) VALUES (?, ?, ?, ?, ?)");

            $guardadas = 0;

            foreach ($notasPost as $alumno_id => $instancias) {
                foreach ($instancias as $instancia_id => $nota) {
                    $nota = trim($nota);
                    
                    // Solo permitir guardar si la instancia está ACTIVA
                    if (!in_array($instancia_id, $activas)) {
                        continue; 
                    }

                    // Ignorar si la nota está vacía y no existía antes (aunque podríamos permitir borrarla)
                    $check->execute([$alumno_id, $curso_id, $materia_id, $instancia_id]);
                    $exists = $check->fetchColumn();

                    if ($exists) {
                        // Actualizar
                        $update->execute([$nota, $exists]);
                        $guardadas++;
                    } else if ($nota !== '') {
                        // Insertar
                        $insert->execute([$alumno_id, $curso_id, $materia_id, $instancia_id, $nota]);
                        $guardadas++;
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'msg' => "Se actualizaron $guardadas registros de notas exitosamente."]);

        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'msg' => 'Ocurrió un error en la base de datos al guardar las notas.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
