<?php
require_once '../config/security.php';
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'listar_orientaciones':
        $stmt = $pdo->query("SELECT id, nombre FROM orientaciones WHERE activo = 1 ORDER BY nombre ASC");
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
            $where .= " AND (asignatura LIKE :search1 OR orientacion LIKE :search2 OR anio_estudio LIKE :search3)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }
        
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM espacios_curriculares e LEFT JOIN orientaciones o ON e.orientacion_id = o.id WHERE $where");
        $stmtCount->execute($params);
        $recordsTotal = $stmtCount->fetchColumn();
        
        $sql = "SELECT e.id, e.asignatura AS nombre, e.anio_estudio, o.nombre AS orientacion, e.orientacion_id, e.activo 
                FROM espacios_curriculares e
                LEFT JOIN orientaciones o ON e.orientacion_id = o.id
                WHERE $where 
                ORDER BY e.anio_estudio ASC, o.nombre ASC, e.asignatura ASC 
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
        $anio_estudio = intval($_POST['anio_estudio'] ?? 0);
        $orientacion_id = intval($_POST['orientacion'] ?? 1);
        
        if (empty($nombre) || empty($anio_estudio)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nombre de asignatura y año de estudio son obligatorios.']);
            exit;
        }
        
        try {
            $sql = "INSERT INTO espacios_curriculares (asignatura, anio_estudio, orientacion_id, activo) VALUES (:nombre, :anio, :ori, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'anio' => $anio_estudio,
                'ori' => $orientacion_id
            ]);
            echo json_encode(['status' => 'success', 'msg' => 'Espacio curricular creado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar el espacio curricular.']);
        }
        break;

    case 'editar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        $nombre = trim($_POST['nombre'] ?? '');
        $anio_estudio = intval($_POST['anio_estudio'] ?? 0);
        $orientacion_id = intval($_POST['orientacion'] ?? 1);
        
        if (empty($id) || empty($nombre) || empty($anio_estudio)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos incompletos o ID inválido.']);
            exit;
        }
        
        try {
            $sql = "UPDATE espacios_curriculares SET asignatura = :nombre, anio_estudio = :anio, orientacion_id = :ori WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'anio' => $anio_estudio,
                'ori' => $orientacion_id,
                'id' => $id
            ]);
            echo json_encode(['status' => 'success', 'msg' => 'Espacio curricular actualizado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar.']);
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
            // Borrado lógico
            $stmt = $pdo->prepare("UPDATE espacios_curriculares SET activo = 0 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Espacio curricular desactivado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'No se pudo desactivar el registro.']);
        }
        break;

    case 'activar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido o alterado.']);
            exit;
        }
        
        try {
            // Restaurar
            $stmt = $pdo->prepare("UPDATE espacios_curriculares SET activo = 1 WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Espacio curricular restaurado (activado) correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'No se pudo activar el registro.']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
