<?php
session_start();
require_once '../config/database.php';
require_once '../config/security.php';

// Verificación de acceso
$rol = $_SESSION['rol'] ?? '';
if (!in_array($rol, ['Administrador', 'Directivo', 'Secretario', 'Preceptor'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Acceso denegado.']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'obtener_ciclos':
        try {
            $stmt = $pdo->query("SELECT id, nombre FROM ciclos_lectivos ORDER BY nombre DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error BD']);
        }
        break;

    case 'obtener_cursos':
        $ciclo_id = intval($_POST['ciclo_id'] ?? 0);
        try {
            // Los cursos no tienen columna 'division' en esta DB, el nombre completo suele estar en 'nombre'
            $stmt = $pdo->query("SELECT id, nombre FROM cursos ORDER BY nombre ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error BD']);
        }
        break;

    case 'obtener_materias':
        $curso_id = intval($_POST['curso_id'] ?? 0);
        try {
            // Obtenemos materias del curso via la tabla puente materias_cursos, o si no hay puente, desde la asignacion?
            // En unity2, la vinculación es usualmente `asignaciones_docentes` o en cursos directamente.
            // Para simplificar, buscamos los espacios curriculares asignados a docentes de este curso
            $stmt = $pdo->prepare("
                SELECT DISTINCT e.id, e.asignatura 
                FROM asignaciones_docentes ad
                JOIN espacios_curriculares e ON ad.materia_id = e.id
                WHERE ad.curso_id = ? AND ad.activo = 1
                ORDER BY e.asignatura ASC
            ");
            $stmt->execute([$curso_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay asignaciones de docentes, busquemos todas las notas de ese curso para inferir materias
            if (empty($data)) {
                $stmt = $pdo->prepare("
                    SELECT DISTINCT e.id, e.asignatura 
                    FROM calificaciones c
                    JOIN espacios_curriculares e ON c.materia_id = e.id
                    WHERE c.curso_id = ?
                    ORDER BY e.asignatura ASC
                ");
                $stmt->execute([$curso_id]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error BD']);
        }
        break;

    case 'obtener_instancias':
        $ciclo_id = intval($_POST['ciclo_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT id, nombre FROM instancias_calificacion WHERE ciclo_lectivo_id = ? ORDER BY id ASC");
            $stmt->execute([$ciclo_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error BD']);
        }
        break;

    case 'generar_reporte_materia':
        $ciclo_id = intval($_POST['ciclo_id'] ?? 0);
        $curso_id = intval($_POST['curso_id'] ?? 0);
        $materia_id = intval($_POST['materia_id'] ?? 0);

        try {
            // 1. Obtener Instancias del Ciclo
            $stmtInst = $pdo->prepare("SELECT id, nombre FROM instancias_calificacion WHERE ciclo_lectivo_id = ? ORDER BY id ASC");
            $stmtInst->execute([$ciclo_id]);
            $instancias = $stmtInst->fetchAll(PDO::FETCH_ASSOC);

            // 2. Obtener Alumnos del Curso
            $stmtAlu = $pdo->prepare("
                SELECT a.id, a.alumno as nombre
                FROM asignaciones_cursos ac
                JOIN lista_alfa a ON ac.alumno_id = a.id
                WHERE ac.curso_id = ?
                ORDER BY a.alumno ASC
            ");
            $stmtAlu->execute([$curso_id]);
            $alumnos = $stmtAlu->fetchAll(PDO::FETCH_ASSOC);

            // 3. Obtener Notas de esta materia
            $stmtNotas = $pdo->prepare("
                SELECT alumno_id, instancia_id, nota 
                FROM calificaciones 
                WHERE curso_id = ? AND materia_id = ?
            ");
            $stmtNotas->execute([$curso_id, $materia_id]);
            $notasRaw = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

            // Indexar notas para busqueda rapida: $notas[alumno_id][instancia_id]
            $notas = [];
            foreach ($notasRaw as $n) {
                $notas[$n['alumno_id']][$n['instancia_id']] = $n['nota'];
            }

            // 4. Formatear para DataTables
            $alumnosFormat = [];
            foreach ($alumnos as $alu) {
                $fila = ['alumno' => $alu['nombre']];
                foreach ($instancias as $inst) {
                    $nota = $notas[$alu['id']][$inst['id']] ?? '-';
                    $fila['inst_'.$inst['id']] = $nota === '' ? '-' : $nota;
                }
                $alumnosFormat[] = $fila;
            }

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'instancias' => $instancias,
                    'alumnos' => $alumnosFormat
                ]
            ]);

        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al generar reporte: ' . $e->getMessage()]);
        }
        break;

    case 'generar_reporte_sabana':
        $ciclo_id = intval($_POST['ciclo_id'] ?? 0);
        $curso_id = intval($_POST['curso_id'] ?? 0);
        $instancia_id = intval($_POST['instancia_id'] ?? 0);

        try {
            // 1. Obtener Alumnos del Curso
            $stmtAlu = $pdo->prepare("
                SELECT a.id, a.alumno as nombre
                FROM asignaciones_cursos ac
                JOIN lista_alfa a ON ac.alumno_id = a.id
                WHERE ac.curso_id = ?
                ORDER BY a.alumno ASC
            ");
            $stmtAlu->execute([$curso_id]);
            $alumnos = $stmtAlu->fetchAll(PDO::FETCH_ASSOC);

            // 2. Obtener todas las materias (espacios) en los que este curso tiene notas para esa instancia, O que tienen docentes asignados
            // Para asegurar un reporte sabana parejo, traemos las materias que tienen notas en esa instancia para ese curso
            $stmtMat = $pdo->prepare("
                SELECT DISTINCT e.id, e.asignatura 
                FROM calificaciones c
                JOIN espacios_curriculares e ON c.materia_id = e.id
                WHERE c.curso_id = ? AND c.instancia_id = ?
                ORDER BY e.asignatura ASC
            ");
            $stmtMat->execute([$curso_id, $instancia_id]);
            $materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);
            
            // Si la consulta anterior está vacia, probamos sacar las materias de asignaciones_docentes
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

            // 3. Obtener Notas de esta instancia
            $stmtNotas = $pdo->prepare("
                SELECT alumno_id, materia_id, nota 
                FROM calificaciones 
                WHERE curso_id = ? AND instancia_id = ?
            ");
            $stmtNotas->execute([$curso_id, $instancia_id]);
            $notasRaw = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

            // Indexar notas: $notas[alumno_id][materia_id]
            $notas = [];
            foreach ($notasRaw as $n) {
                $notas[$n['alumno_id']][$n['materia_id']] = $n['nota'];
            }

            // 4. Formatear para DataTables
            $alumnosFormat = [];
            foreach ($alumnos as $alu) {
                $fila = ['alumno' => $alu['nombre']];
                foreach ($materias as $mat) {
                    $nota = $notas[$alu['id']][$mat['id']] ?? '-';
                    $fila['mat_'.$mat['id']] = $nota === '' ? '-' : $nota;
                }
                $alumnosFormat[] = $fila;
            }

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'materias' => $materias,
                    'alumnos' => $alumnosFormat
                ]
            ]);

        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al generar sabana: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
