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
    case 'listar':
        // Parámetros DataTables
        $start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
        $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : "";
        $draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;

        // Filtros personalizados
        $curso_id = $_POST['curso_id'] ?? "";
        $fecha    = $_POST['fecha'] ?? "";
        $tipo_id  = $_POST['tipo_id'] ?? "";
        $alumno   = $_POST['alumno'] ?? "";

        // Consulta principal
        $sql = "
        SELECT 
            a.id,
            l.alumno,
            l.dni,
            c.nombre AS curso,
            a.fecha,
            t.nombre AS tipo,
            t.id AS tipo_id,
            a.observaciones
        FROM asistencias a
        JOIN lista_alfa l ON a.alumno_id = l.id
        JOIN cursos c ON a.curso_id = c.id
        JOIN tipos_asistencia t ON a.tipo_asistencia_id = t.id
        WHERE 1=1
        ";
        
        $countSql = "
        SELECT COUNT(*) 
        FROM asistencias a
        JOIN lista_alfa l ON a.alumno_id = l.id
        JOIN cursos c ON a.curso_id = c.id
        JOIN tipos_asistencia t ON a.tipo_asistencia_id = t.id
        WHERE 1=1
        ";

        $params = [];
        $condiciones = "";
        
        if ($curso_id !== "") { $condiciones .= " AND a.curso_id = :curso_id"; $params['curso_id'] = $curso_id; }
        if ($fecha !== "")    { $condiciones .= " AND a.fecha = :fecha"; $params['fecha'] = $fecha; }
        if ($tipo_id !== "")  { $condiciones .= " AND a.tipo_asistencia_id = :tipo_id"; $params['tipo_id'] = $tipo_id; }
        if ($alumno !== "")   {
            $condiciones .= " AND (l.alumno LIKE :alumno1 OR l.dni LIKE :alumno2)";
            $params['alumno1'] = "%$alumno%";
            $params['alumno2'] = "%$alumno%";
        }
        if ($search !== "") {
            $condiciones .= " AND (l.alumno LIKE :search1 OR l.dni LIKE :search2 OR a.observaciones LIKE :search3)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }
        
        $sql .= $condiciones;
        $countSql .= $condiciones;

        try {
            // Total filtrado
            $stmt2 = $pdo->prepare($countSql);
            $stmt2->execute($params);
            $filtered = $stmt2->fetchColumn();

            // Orden y paginación
            $sql .= " ORDER BY a.fecha DESC LIMIT :start, :length";
            
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(":$key", $val);
            }
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':length', $length, PDO::PARAM_INT);
            $stmt->execute();
            
            $datos = [];
            while ($f = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $f['id_hash'] = encrypt_id($f['id']);
                $datos[] = $f;
            }

            // Total sin filtros
            $total_rows = $pdo->query("SELECT COUNT(*) FROM asistencias")->fetchColumn();

            echo json_encode([
                "draw" => $draw,
                "recordsTotal" => (int)$total_rows,
                "recordsFiltered" => (int)$filtered,
                "data" => $datos
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            echo json_encode([
                "draw" => $draw,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error interno en BD: " . $e->getMessage()
            ]);
        }
        break;

    case 'guardar':
        $curso_id = $_POST['curso_id'] ?? null;
        $fecha = $_POST['fecha'] ?? null;
        $tipos = $_POST['tipo_asistencia'] ?? [];  
        $observaciones = $_POST['observaciones'] ?? [];
        
        $registrado_por = $_SESSION['usuario_id'] ?? 1; 
        
        if (!$curso_id || !$fecha) {
            echo json_encode(["status" => "error", "msg" => "Faltan datos de curso o fecha"]);
            exit;
        }
        
        $respuestas = 0;
        $errores = [];
        
        foreach ($tipos as $alumno_id => $tipo_id) {
            $observacion = $observaciones[$alumno_id] ?? "";
            
            // Verificar duplicados
            $check = $pdo->prepare("SELECT id FROM asistencias WHERE alumno_id = :a AND curso_id = :c AND fecha = :f");
            $check->execute(['a' => $alumno_id, 'c' => $curso_id, 'f' => $fecha]);
            
            if ($check->fetch()) {
                $errores[] = "Ya existe asistencia registrada para el alumno ID $alumno_id";
                continue;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO asistencias (alumno_id, curso_id, fecha, tipo_asistencia_id, observaciones, registrado_por)
                VALUES (:a, :c, :f, :t, :o, :r)
            ");
            try {
                $stmt->execute([
                    'a' => $alumno_id,
                    'c' => $curso_id,
                    'f' => $fecha,
                    't' => $tipo_id,
                    'o' => $observacion,
                    'r' => $registrado_por
                ]);
                $respuestas++;
            } catch (PDOException $e) {
                $errores[] = "Error al guardar asistencia del alumno ID $alumno_id";
            }
        }
        
        if ($respuestas > 0) {
            echo json_encode(["status" => "success", "msg" => "$respuestas asistencia(s) registrada(s).", "errores" => $errores]);
        } else {
            echo json_encode(["status" => "error", "msg" => "No se registró ninguna asistencia.", "errores" => $errores]);
        }
        break;

    case 'editar':
        $id_hash = $_POST['editarAsistenciaID'] ?? '';
        $id = decrypt_id($id_hash);
        $tipo_id = $_POST['editarTipoAsistencia'] ?? '';
        $obs = $_POST['editarObservaciones'] ?? '';

        if (empty($id) || empty($tipo_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'Faltan datos.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE asistencias SET tipo_asistencia_id = :tipo, observaciones = :obs WHERE id = :id");
            $stmt->execute(['tipo' => $tipo_id, 'obs' => $obs, 'id' => $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Asistencia actualizada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar asistencia.']);
        }
        break;

    case 'obtener_tipos':
        $stmt = $pdo->query("SELECT id, nombre FROM tipos_asistencia ORDER BY id ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'obtener_cursos':
        $stmt = $pdo->query("SELECT id, nombre FROM cursos ORDER BY nombre ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'obtener_alumnos_curso':
        $curso_id = $_POST['curso_id'] ?? 0;
        $stmt = $pdo->prepare("
            SELECT 
                ac.id AS asignacion_id, 
                a.id AS alumno_id,     
                a.alumno AS nombre_alumno, 
                a.dni, 
                a.legajo, 
                ac.fecha_asignacion
            FROM asignaciones_cursos ac
            JOIN lista_alfa a ON ac.alumno_id = a.id
            WHERE ac.curso_id = :curso_id
            ORDER BY a.alumno
        ");
        $stmt->execute(['curso_id' => $curso_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida']);
}
