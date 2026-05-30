<?php
session_start();
require_once '../config/database.php';
require_once '../config/security.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Sesión expirada']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'obtener_cursos':
        if ($_SESSION['rol'] === 'Docente') {
            $stmt = $pdo->prepare("SELECT DISTINCT c.id, c.nombre, c.turno 
                                   FROM cursos c
                                   JOIN asignaciones_docentes a ON c.id = a.curso_id
                                   JOIN usuarios u ON u.dni = (SELECT dni FROM docentes WHERE id = a.docente_id LIMIT 1)
                                   WHERE u.id = ?
                                   ORDER BY c.nombre ASC");
            $stmt->execute([$_SESSION['usuario_id']]);
        } else {
            $stmt = $pdo->query("SELECT id, nombre, turno FROM cursos ORDER BY nombre ASC");
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'obtener_materias':
        $curso_id = $_POST['curso_id'] ?? 0;
        
        $stmtC = $pdo->prepare("SELECT nombre, orientacion_id FROM cursos WHERE id = :id");
        $stmtC->execute(['id' => $curso_id]);
        $curso = $stmtC->fetch(PDO::FETCH_ASSOC);
        
        if ($curso) {
            $anio = intval(substr($curso['nombre'], 0, 1));
            $ori_id = $curso['orientacion_id'] ?: 1;
            
            if ($_SESSION['rol'] === 'Docente') {
                $stmt = $pdo->prepare("SELECT e.id, e.asignatura AS nombre 
                                       FROM espacios_curriculares e
                                       JOIN asignaciones_docentes a ON e.id = a.espacio_curricular_id
                                       JOIN usuarios u ON u.dni = (SELECT dni FROM docentes WHERE id = a.docente_id LIMIT 1)
                                       WHERE e.anio_estudio = :a AND e.orientacion_id = :o AND e.activo = 1 
                                         AND a.curso_id = :c AND u.id = :u
                                       ORDER BY e.asignatura ASC");
                $stmt->execute(['a' => $anio, 'o' => $ori_id, 'c' => $curso_id, 'u' => $_SESSION['usuario_id']]);
            } else {
                $stmt = $pdo->prepare("SELECT id, asignatura AS nombre FROM espacios_curriculares WHERE anio_estudio = :a AND orientacion_id = :o AND activo = 1 ORDER BY asignatura ASC");
                $stmt->execute(['a' => $anio, 'o' => $ori_id]);
            }
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            echo json_encode([]);
        }
        break;

    case 'obtener_instancias':
        $sql = "
            SELECT i.id, i.nombre, i.tipo, i.activa, c.nombre AS ciclo_nombre
            FROM instancias_calificacion i
            JOIN ciclos_lectivos c ON i.ciclo_lectivo_id = c.id
            ORDER BY i.creado_en DESC
        ";
        $stmt = $pdo->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'obtener_alumnos_para_calificar':
        $curso_id     = $_POST['curso_id'] ?? 0;
        $materia_id   = $_POST['materia_id'] ?? 0;
        $instancia_id = $_POST['instancia_id'] ?? 0;

        if (!$curso_id || !$materia_id || !$instancia_id) {
            echo json_encode([]);
            exit;
        }

        $sql = "
            SELECT 
                lh.id AS alumno_id,
                lh.alumno,
                cal.nota,
                cal.observaciones
            FROM lista_alfa lh
            LEFT JOIN asignaciones_cursos ac ON ac.alumno_id = lh.id
            LEFT JOIN calificaciones cal 
                ON cal.alumno_id = lh.id 
                AND cal.curso_id = :curso_id 
                AND cal.materia_id = :materia_id 
                AND cal.instancia_id = :instancia_id
            WHERE ac.curso_id = :curso_id_ac
            ORDER BY lh.alumno ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'curso_id' => $curso_id,
            'materia_id' => $materia_id,
            'instancia_id' => $instancia_id,
            'curso_id_ac' => $curso_id
        ]);
        
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'guardar_notas':
        $curso_id     = $_POST['curso_id'] ?? '';
        $materia_id   = $_POST['materia_id'] ?? '';
        $instancia_id = $_POST['instancia_id'] ?? '';
        $alumno_ids   = $_POST['alumno_id'] ?? [];
        $notas        = $_POST['nota'] ?? [];
        $observaciones = $_POST['observaciones'] ?? [];

        if (!$curso_id || !$materia_id || !$instancia_id || empty($alumno_ids)) {
            echo json_encode(["status" => "error", "msg" => "Datos incompletos"]);
            exit;
        }

        $fecha = date("Y-m-d");
        $guardadas = 0;
        
        try {
            $pdo->beginTransaction();
            
            $stmtCiclo = $pdo->query("SELECT id FROM ciclos_lectivos ORDER BY id DESC LIMIT 1");
            $ciclo_id = $stmtCiclo->fetchColumn() ?: 1;

            foreach ($alumno_ids as $i => $alumno_id) {
                $nota = trim($notas[$i] ?? '');
                $obs = trim($observaciones[$i] ?? '');

                if ($nota === '') continue; // Skip empty notes (or save them as empty?)
                
                $check = $pdo->prepare("SELECT id FROM calificaciones WHERE alumno_id = :a AND curso_id = :c AND materia_id = :m AND instancia_id = :i");
                $check->execute(['a' => $alumno_id, 'c' => $curso_id, 'm' => $materia_id, 'i' => $instancia_id]);
                
                if ($check->fetch()) {
                    $update = $pdo->prepare("UPDATE calificaciones SET nota = :n, observaciones = :o, fecha_registro = :f, ciclo_lectivo_id = :cl WHERE alumno_id = :a AND curso_id = :c AND materia_id = :m AND instancia_id = :i");
                    $update->execute(['n' => $nota, 'o' => $obs, 'f' => $fecha, 'cl' => $ciclo_id, 'a' => $alumno_id, 'c' => $curso_id, 'm' => $materia_id, 'i' => $instancia_id]);
                } else {
                    $insert = $pdo->prepare("INSERT INTO calificaciones (alumno_id, curso_id, materia_id, instancia_id, ciclo_lectivo_id, nota, observaciones, fecha_registro) VALUES (:a, :c, :m, :i, :cl, :n, :o, :f)");
                    $insert->execute(['a' => $alumno_id, 'c' => $curso_id, 'm' => $materia_id, 'i' => $instancia_id, 'cl' => $ciclo_id, 'n' => $nota, 'o' => $obs, 'f' => $fecha]);
                }
                $guardadas++;
            }
            $pdo->commit();
            echo json_encode(["status" => "success", "msg" => "$guardadas calificaciones guardadas."]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(["status" => "error", "msg" => "Error al guardar calificaciones."]);
        }
        break;

    case 'listar_matriz':
        $curso_id     = $_POST['curso_id'] ?? 0;
        $instancia_id = $_POST['instancia_id'] ?? 0;

        if (!$curso_id || !$instancia_id) {
            echo json_encode(["status" => "error", "msg" => "Faltan parámetros."]);
            exit;
        }

        $stmtCurso = $pdo->prepare("SELECT nombre, turno, orientacion_id FROM cursos WHERE id = :id");
        $stmtCurso->execute(['id' => $curso_id]);
        $curso = $stmtCurso->fetch(PDO::FETCH_ASSOC);

        if (!$curso) {
            echo json_encode(["status" => "error", "msg" => "Curso no encontrado."]);
            exit;
        }

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
            echo json_encode(["status" => "error", "msg" => "El curso no tiene alumnos asignados."]);
            exit;
        }

        $anio = intval(substr($curso['nombre'], 0, 1));
        $ori_id = $curso['orientacion_id'] ?: 1;

        $stmtMaterias = $pdo->prepare("SELECT id, asignatura AS nombre FROM espacios_curriculares WHERE anio_estudio = :a AND orientacion_id = :o AND activo = 1 ORDER BY asignatura");
        $stmtMaterias->execute(['a' => $anio, 'o' => $ori_id]);
        $materias = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);

        if (empty($materias)) {
            echo json_encode(["status" => "error", "msg" => "El curso no tiene materias asignadas."]);
            exit;
        }

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

        foreach ($alumnos as &$al) {
            $al['notas'] = new stdClass();
            foreach ($materias as $mat) {
                $key = $al['id'] . '_' . $mat['id'];
                $matId = $mat['id'];
                $al['notas']->$matId = $notasMap[$key] ?? "-";
            }
        }

        echo json_encode([
            "status" => "success",
            "curso" => $curso,
            "materias" => $materias,
            "alumnos" => $alumnos
        ]);
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida']);
}
