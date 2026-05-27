<?php
require_once '../config/security.php';
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// Array de acciones que requieren validación CSRF
$acciones_escritura = ['guardar', 'eliminar', 'editar'];
if (in_array($action, $acciones_escritura)) {
    if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'msg' => 'Falsificación de petición detectada (CSRF).']);
        exit;
    }
}

switch ($action) {
    case 'listar':
        $draw = intval($_POST['draw'] ?? 1);
        $start = intval($_POST['start'] ?? 0);
        $length = intval($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        
        $where = "1=1";
        $params = [];
        if (!empty($search)) {
            $where .= " AND (u.nombre LIKE :search1 OR u.correo LIKE :search2 OR u.dni LIKE :search3)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }
        
        $sqlCount = "SELECT COUNT(*) FROM usuarios u WHERE $where";
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $recordsTotal = $stmtCount->fetchColumn();
        
        $sql = "SELECT u.id, u.nombre, u.dni, u.correo, r.nombre AS rol, u.fecha_creacion, u.rol_id
                FROM usuarios u
                LEFT JOIN roles r ON u.rol_id = r.id
                WHERE $where
                ORDER BY u.id DESC
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
        
    case 'obtener_roles':
        try {
            $stmt = $pdo->query("SELECT id, nombre FROM roles ORDER BY nombre ASC");
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $roles]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al obtener roles.']);
        }
        break;
        
    case 'guardar':
        $nombre = trim($_POST['nombre'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $clave = $_POST['clave'] ?? '';
        $rol_id = intval($_POST['rol_id'] ?? 0);
        
        if (empty($nombre) || empty($dni) || empty($correo) || empty($clave) || empty($rol_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'Todos los campos son obligatorios.']);
            exit;
        }
        
        try {
            // Check duplicates
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE dni = :dni OR correo = :correo");
            $stmtCheck->execute(['dni' => $dni, 'correo' => $correo]);
            if ($stmtCheck->fetch()) {
                echo json_encode(['status' => 'error', 'msg' => 'El DNI o correo ya está registrado.']);
                exit;
            }
            
            $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre, dni, correo, clave, rol_id, fecha_creacion) 
                    VALUES (:nombre, :dni, :correo, :clave, :rol, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'dni' => $dni,
                'correo' => $correo,
                'clave' => $clave_hash,
                'rol' => $rol_id
            ]);
            
            echo json_encode(['status' => 'success', 'msg' => 'Usuario guardado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar el usuario.']);
        }
        break;

    case 'obtener':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT nombre, dni, correo, rol_id FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            $usuario['id_hash'] = $id_hash;
            echo json_encode(['status' => 'success', 'data' => $usuario]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Usuario no encontrado.']);
        }
        break;

    case 'editar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido']);
            exit;
        }
        
        $nombre = trim($_POST['nombre'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $clave = $_POST['clave'] ?? '';
        $rol_id = intval($_POST['rol_id'] ?? 0);
        
        if (empty($nombre) || empty($dni) || empty($correo) || empty($rol_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nombre, DNI, Correo y Rol son obligatorios.']);
            exit;
        }
        
        try {
            // Check duplicates (excluding self)
            $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE (dni = :dni OR correo = :correo) AND id != :id");
            $stmtCheck->execute(['dni' => $dni, 'correo' => $correo, 'id' => $id]);
            if ($stmtCheck->fetch()) {
                echo json_encode(['status' => 'error', 'msg' => 'El DNI o correo ya pertenece a otro usuario.']);
                exit;
            }
            
            if (!empty($clave)) {
                $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre = :nombre, dni = :dni, correo = :correo, clave = :clave, rol_id = :rol WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nombre' => $nombre,
                    'dni' => $dni,
                    'correo' => $correo,
                    'clave' => $clave_hash,
                    'rol' => $rol_id,
                    'id' => $id
                ]);
            } else {
                $sql = "UPDATE usuarios SET nombre = :nombre, dni = :dni, correo = :correo, rol_id = :rol WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nombre' => $nombre,
                    'dni' => $dni,
                    'correo' => $correo,
                    'rol' => $rol_id,
                    'id' => $id
                ]);
            }
            
            $logout_required = false;
            if ($id == $_SESSION['user_id']) {
                $logout_required = true;
            }
            
            echo json_encode(['status' => 'success', 'msg' => 'Usuario actualizado correctamente.', 'logout_required' => $logout_required]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar el usuario.']);
        }
        break;
        
    case 'eliminar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido o alterado.']);
            exit;
        }
        
        // Prevent deleting oneself
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'msg' => 'No puedes eliminar tu propio usuario activo.']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Usuario eliminado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'No se puede eliminar el usuario porque tiene registros asociados.']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
