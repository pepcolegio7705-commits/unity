<?php
require_once '../config/security.php';
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'listar_cursos':
        $stmt = $pdo->query("SELECT id, nombre, turno, orientacion_id FROM cursos ORDER BY nombre ASC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

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
            $where .= " AND (m.nombre LIKE :search1 OR c.nombre LIKE :search2 OR o.nombre LIKE :search3)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }
        
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM materias m 
                                    LEFT JOIN cursos c ON m.curso_id = c.id 
                                    LEFT JOIN orientaciones o ON m.orientacion_id = o.id 
                                    WHERE $where");
        $stmtCount->execute($params);
        $recordsTotal = $stmtCount->fetchColumn();
        
        $sql = "SELECT m.id, m.nombre, m.curso_id, m.orientacion_id, c.nombre AS curso, c.turno, o.nombre AS orientacion 
                FROM materias m 
                LEFT JOIN cursos c ON m.curso_id = c.id 
                LEFT JOIN orientaciones o ON m.orientacion_id = o.id 
                WHERE $where 
                ORDER BY m.nombre ASC 
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
        $curso_id = !empty($_POST['curso_id']) ? intval($_POST['curso_id']) : null;
        $orientacion_id = !empty($_POST['orientacion_id']) ? intval($_POST['orientacion_id']) : null;
        
        if (empty($nombre) || empty($curso_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nombre y curso son obligatorios.']);
            exit;
        }
        
        try {
            $sql = "INSERT INTO materias (nombre, curso_id, orientacion_id) VALUES (:nombre, :curso_id, :orientacion_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'curso_id' => $curso_id,
                'orientacion_id' => $orientacion_id
            ]);
            echo json_encode(['status' => 'success', 'msg' => 'Materia creada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar la materia.']);
        }
        break;

    case 'editar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        $nombre = trim($_POST['nombre'] ?? '');
        $curso_id = !empty($_POST['curso_id']) ? intval($_POST['curso_id']) : null;
        $orientacion_id = !empty($_POST['orientacion_id']) ? intval($_POST['orientacion_id']) : null;
        
        if (empty($id) || empty($nombre) || empty($curso_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos incompletos o ID inválido.']);
            exit;
        }
        
        try {
            $sql = "UPDATE materias SET nombre = :nombre, curso_id = :curso_id, orientacion_id = :orientacion_id WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'curso_id' => $curso_id,
                'orientacion_id' => $orientacion_id,
                'id' => $id
            ]);
            echo json_encode(['status' => 'success', 'msg' => 'Materia actualizada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar la materia.']);
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
            $stmt = $pdo->prepare("DELETE FROM materias WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Materia eliminada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'No se puede eliminar la materia porque tiene registros asociados.']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
