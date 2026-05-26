<?php
require_once '../config/security.php';
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'listar_orientaciones':
        $stmt = $pdo->query("SELECT id, nombre FROM orientaciones ORDER BY nombre ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'listar':
        $draw = intval($_POST['draw'] ?? 1);
        $start = intval($_POST['start'] ?? 0);
        $length = intval($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (c.nombre LIKE :search1 OR c.turno LIKE :search2 OR o.nombre LIKE :search3)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }
        
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM cursos c LEFT JOIN orientaciones o ON c.orientacion_id = o.id WHERE $where");
        $stmtCount->execute($params);
        $recordsTotal = $stmtCount->fetchColumn();
        
        $sql = "SELECT c.id, c.nombre, c.turno, c.orientacion_id, o.nombre AS orientacion 
                FROM cursos c 
                LEFT JOIN orientaciones o ON c.orientacion_id = o.id 
                WHERE $where 
                ORDER BY c.nombre ASC 
                LIMIT :start, :length";
                
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($data as &$row) {
            $row['id_hash'] = encrypt_id($row['id']);
            unset($row['id']);
        }
        
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => $data
        ]);
        break;
        
    case 'guardar':
        $nombre = trim($_POST['nombre'] ?? '');
        $turno = trim($_POST['turno'] ?? '');
        $orientacion_id = !empty($_POST['orientacion_id']) ? intval($_POST['orientacion_id']) : null;
        
        if (empty($nombre) || empty($turno)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nombre y turno son obligatorios.']);
            exit;
        }
        
        try {
            $sql = "INSERT INTO cursos (nombre, turno, orientacion_id) VALUES (:nombre, :turno, :orientacion_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'turno' => $turno,
                'orientacion_id' => $orientacion_id
            ]);
            echo json_encode(['status' => 'success', 'msg' => 'Curso creado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar el curso.']);
        }
        break;

    case 'editar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        $nombre = trim($_POST['nombre'] ?? '');
        $turno = trim($_POST['turno'] ?? '');
        $orientacion_id = !empty($_POST['orientacion_id']) ? intval($_POST['orientacion_id']) : null;
        
        if (empty($id) || empty($nombre) || empty($turno)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos incompletos o ID inválido.']);
            exit;
        }
        
        try {
            $sql = "UPDATE cursos SET nombre = :nombre, turno = :turno, orientacion_id = :orientacion_id WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'turno' => $turno,
                'orientacion_id' => $orientacion_id,
                'id' => $id
            ]);
            echo json_encode(['status' => 'success', 'msg' => 'Curso actualizado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar el curso.']);
        }
        break;

    case 'eliminar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido o alterado.']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Curso eliminado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'No se puede eliminar el curso porque tiene registros asociados.']);
        }
        break;
        
    case 'listar_alumnos_curso':
        $id_hash = $_POST['curso_id'] ?? '';
        $curso_id = decrypt_id($id_hash);
        
        if (empty($curso_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID de curso inválido']);
            exit;
        }

        $sql = "
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
            ORDER BY a.alumno ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['curso_id' => $curso_id]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            $row['asignacion_id_hash'] = encrypt_id($row['asignacion_id']);
            unset($row['asignacion_id']);
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    case 'buscar_alumnos_para_asignar':
        $q = trim($_POST['q'] ?? '');
        $id_hash = $_POST['curso_id'] ?? '';
        $curso_id = decrypt_id($id_hash);

        if (empty($curso_id) || empty($q)) {
            echo json_encode([]);
            exit;
        }

        $sql = "
            SELECT a.id, a.alumno, a.dni 
            FROM lista_alfa a
            WHERE (a.alumno LIKE :q OR a.dni LIKE :q2)
            AND a.id NOT IN (
                SELECT alumno_id FROM asignaciones_cursos WHERE curso_id = :curso_id
            )
            LIMIT 10
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'q' => "%$q%",
            'q2' => "%$q%",
            'curso_id' => $curso_id
        ]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'id' => encrypt_id($row['id']),
                'text' => $row['alumno'] . " (DNI: " . $row['dni'] . ")"
            ];
        }

        echo json_encode(['results' => $result]);
        break;

    case 'asignar_alumno':
        $curso_id_hash = $_POST['curso_id'] ?? '';
        $alumno_id_hash = $_POST['alumno_id'] ?? '';
        
        $curso_id = decrypt_id($curso_id_hash);
        $alumno_id = decrypt_id($alumno_id_hash);

        if (empty($curso_id) || empty($alumno_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos inválidos.']);
            exit;
        }

        $check = $pdo->prepare("SELECT id FROM asignaciones_cursos WHERE alumno_id = :a AND curso_id = :c");
        $check->execute(['a' => $alumno_id, 'c' => $curso_id]);
        if ($check->fetch()) {
            echo json_encode(['status' => 'error', 'msg' => 'El alumno ya está asignado a este curso.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO asignaciones_cursos (alumno_id, curso_id, fecha_asignacion) VALUES (:a, :c, CURDATE())");
            $stmt->execute(['a' => $alumno_id, 'c' => $curso_id]);
            echo json_encode(['status' => 'success', 'msg' => 'Alumno asignado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al asignar alumno.']);
        }
        break;

    case 'desasignar_alumno':
        $asignacion_id_hash = $_POST['asignacion_id'] ?? '';
        $asignacion_id = decrypt_id($asignacion_id_hash);

        if (empty($asignacion_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM asignaciones_cursos WHERE id = :id");
            $stmt->execute(['id' => $asignacion_id]);
            echo json_encode(['status' => 'success', 'msg' => 'Alumno desasignado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al desasignar alumno.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
