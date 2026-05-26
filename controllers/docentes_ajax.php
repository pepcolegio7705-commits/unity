<?php
require_once '../config/security.php';
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'listar':
        $draw = intval($_POST['draw'] ?? 1);
        $start = intval($_POST['start'] ?? 0);
        $length = intval($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (nombre LIKE :search1 OR dni LIKE :search2 OR email LIKE :search3)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }
        
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM docentes WHERE $where");
        $stmtCount->execute($params);
        $recordsTotal = $stmtCount->fetchColumn();
        
        $sql = "SELECT id, nombre, dni, titulacion, email, telefono, activo 
                FROM docentes 
                WHERE $where 
                ORDER BY nombre ASC 
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
        $dni = trim($_POST['dni'] ?? '');
        $titulacion = trim($_POST['titulacion'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $activo = intval($_POST['activo'] ?? 1);
        
        if (empty($nombre) || empty($dni)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nombre y DNI son obligatorios.']);
            exit;
        }
        
        try {
            $sql = "INSERT INTO docentes (nombre, dni, titulacion, email, telefono, activo) 
                    VALUES (:nombre, :dni, :titulacion, :email, :telefono, :activo)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'dni' => $dni,
                'titulacion' => $titulacion,
                'email' => $email,
                'telefono' => $telefono,
                'activo' => $activo
            ]);
            echo json_encode(['status' => 'success', 'msg' => 'Docente guardado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar docente. Posible DNI duplicado.']);
        }
        break;

    case 'editar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        $nombre = trim($_POST['nombre'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $titulacion = trim($_POST['titulacion'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $activo = intval($_POST['activo'] ?? 1);
        
        if (empty($id) || empty($nombre) || empty($dni)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos incompletos o ID inválido.']);
            exit;
        }
        
        try {
            $sql = "UPDATE docentes 
                    SET nombre = :nombre, dni = :dni, titulacion = :titulacion, 
                        email = :email, telefono = :telefono, activo = :activo 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'dni' => $dni,
                'titulacion' => $titulacion,
                'email' => $email,
                'telefono' => $telefono,
                'activo' => $activo,
                'id' => $id
            ]);
            echo json_encode(['status' => 'success', 'msg' => 'Docente actualizado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar docente.']);
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
            $stmt = $pdo->prepare("DELETE FROM docentes WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Docente eliminado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'No se puede eliminar el docente porque tiene cursos asignados.']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
