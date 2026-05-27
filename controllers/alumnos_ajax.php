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
            $where .= " AND (lh.alumno LIKE :search1 OR lh.dni LIKE :search2 OR lh.legajo LIKE :search3 OR c.nombre LIKE :search4)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
            $params[':search4'] = "%$search%";
        }
        
        $sqlCount = "SELECT COUNT(*) FROM lista_alfa lh 
                     LEFT JOIN asignaciones_cursos ac ON ac.alumno_id = lh.legajo 
                     LEFT JOIN cursos c ON c.id = ac.curso_id 
                     WHERE $where";
        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $recordsTotal = $stmtCount->fetchColumn();
        
        $sql = "SELECT lh.id, lh.alumno AS nombre, lh.dni, lh.legajo, 
                       c.nombre AS curso,
                       ea.estado AS estado
                FROM lista_alfa lh
                LEFT JOIN asignaciones_cursos ac ON ac.alumno_id = lh.legajo
                LEFT JOIN cursos c ON c.id = ac.curso_id
                LEFT JOIN estados_alumnos ea ON ea.id = lh.estatus
                WHERE $where
                ORDER BY lh.legajo DESC
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

    case 'obtener_ultimo_legajo':
        // Consultar el legajo más alto (asumiendo que puede contener texto, hacemos limpieza a números si es necesario, o lo tratamos como entero)
        $stmt = $pdo->query("SELECT MAX(CAST(legajo AS UNSIGNED)) as max_legajo FROM lista_alfa");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $ultimo = $resultado['max_legajo'] ? (int)$resultado['max_legajo'] : 0;
        $siguiente = $ultimo + 1;
        
        echo json_encode(['status' => 'success', 'ultimo' => $ultimo, 'siguiente' => $siguiente]);
        break;
        
    case 'guardar':
        $nombre = trim($_POST['alumno'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $legajo = trim($_POST['legajo'] ?? '');
        $libro = trim($_POST['libro'] ?? '');
        $folio = trim($_POST['folio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $obs = trim($_POST['obs'] ?? '');
        
        if (empty($nombre) || empty($dni)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nombre y DNI son obligatorios.']);
            exit;
        }
        
        try {
            $sql = "INSERT INTO lista_alfa (alumno, dni, legajo, libro, folio, obs, estatus) 
                    VALUES (:nombre, :dni, :legajo, :libro, :folio, :obs, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'dni' => $dni,
                'legajo' => $legajo,
                'libro' => $libro,
                'folio' => $folio,
                'obs' => $obs
            ]);
            
            echo json_encode(['status' => 'success', 'msg' => 'Alumno guardado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar. Posible duplicado.']);
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
            $stmt = $pdo->prepare("DELETE FROM lista_alfa WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Alumno eliminado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'No se puede eliminar el alumno porque tiene registros asociados.']);
        }
        break;
        
    case 'obtener':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT * FROM lista_alfa WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $alumno = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($alumno) {
            $alumno['id_hash'] = $id_hash;
            echo json_encode(['status' => 'success', 'data' => $alumno]);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Alumno no encontrado.']);
        }
        break;

    case 'editar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido']);
            exit;
        }
        $nombre = trim($_POST['alumno'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $legajo = trim($_POST['legajo'] ?? '');
        $libro = trim($_POST['libro'] ?? '');
        $folio = trim($_POST['folio'] ?? '');
        $obs = trim($_POST['obs'] ?? '');
        
        if (empty($nombre) || empty($dni)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nombre y DNI obligatorios.']);
            exit;
        }
        try {
            $sql = "UPDATE lista_alfa SET alumno = :nombre, dni = :dni, legajo = :legajo, libro = :libro, folio = :folio, obs = :obs WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'dni' => $dni,
                'legajo' => $legajo,
                'libro' => $libro,
                'folio' => $folio,
                'obs' => $obs,
                'id' => $id
            ]);
            echo json_encode(['status' => 'success', 'msg' => 'Alumno actualizado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar. Posible duplicado.']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
