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
            $where .= " AND (nombre LIKE :search1 OR apellido LIKE :search2 OR dni LIKE :search3 OR legajo LIKE :search4)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
            $params[':search4'] = "%$search%";
        }
        
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM docentes WHERE $where");
        $stmtCount->execute($params);
        $recordsTotal = $stmtCount->fetchColumn();
        
        $sql = "SELECT id, nombre, apellido, dni, cuil, direccion, legajo, titulo as titulacion, mail as email, telefono, estado as activo 
                FROM docentes 
                WHERE $where 
                ORDER BY apellido ASC, nombre ASC 
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
        $apellido = trim($_POST['apellido'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $cuil = trim($_POST['cuil'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $legajo = trim($_POST['legajo'] ?? '');
        $titulo = trim($_POST['titulacion'] ?? '');
        $mail = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $estado = intval($_POST['activo'] ?? 1);
        
        if (empty($nombre) || empty($apellido) || empty($dni)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nombre, Apellido y DNI son obligatorios.']);
            exit;
        }
        if (empty($mail)) {
            $mail = $dni . '@docente.local';
        }
        
        try {
            $pdo->beginTransaction();
            $clave_hash = password_hash($dni, PASSWORD_DEFAULT);
            $nombre_completo = trim($nombre . ' ' . $apellido);
            
            // 1. Insertar en usuarios
            $sqlU = "INSERT INTO usuarios (nombre, dni, correo, clave, rol_id, estado_id) 
                     VALUES (:nom, :dni, :corr, :clv, 2, :est)";
            $stmtU = $pdo->prepare($sqlU);
            $stmtU->execute([
                'nom' => $nombre_completo,
                'dni' => $dni,
                'corr' => $mail,
                'clv' => $clave_hash,
                'est' => $estado
            ]);
            
            // 2. Insertar en docentes
            $sqlD = "INSERT INTO docentes (nombre, apellido, dni, cuil, direccion, legajo, titulo, mail, telefono, estado) 
                     VALUES (:nombre, :apellido, :dni, :cuil, :direccion, :legajo, :titulo, :mail, :telefono, :estado)";
            $stmtD = $pdo->prepare($sqlD);
            $stmtD->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'dni' => $dni,
                'cuil' => $cuil,
                'direccion' => $direccion,
                'legajo' => $legajo,
                'titulo' => $titulo,
                'mail' => $mail,
                'telefono' => $telefono,
                'estado' => $estado
            ]);
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'msg' => 'Docente guardado correctamente. Clave por defecto: Su DNI.']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar docente. Es posible que el DNI o Email ya existan.']);
        }
        break;

    case 'editar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $cuil = trim($_POST['cuil'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $legajo = trim($_POST['legajo'] ?? '');
        $titulo = trim($_POST['titulacion'] ?? '');
        $mail = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $estado = intval($_POST['activo'] ?? 1);
        
        if (empty($id) || empty($nombre) || empty($apellido) || empty($dni)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos incompletos o ID inválido.']);
            exit;
        }
        if (empty($mail)) {
            $mail = $dni . '@docente.local';
        }
        
        try {
            $pdo->beginTransaction();
            $nombre_completo = trim($nombre . ' ' . $apellido);
            
            // Obtener DNI viejo para ubicarlo en usuarios
            $stmtOld = $pdo->prepare("SELECT dni FROM docentes WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldDni = $stmtOld->fetchColumn();
            
            // 1. Actualizar docentes
            $sql = "UPDATE docentes 
                    SET nombre = :nombre, apellido = :apellido, dni = :dni, cuil = :cuil, 
                        direccion = :direccion, legajo = :legajo, titulo = :titulo, 
                        mail = :mail, telefono = :telefono, estado = :estado 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'dni' => $dni,
                'cuil' => $cuil,
                'direccion' => $direccion,
                'legajo' => $legajo,
                'titulo' => $titulo,
                'mail' => $mail,
                'telefono' => $telefono,
                'estado' => $estado,
                'id' => $id
            ]);
            
            // 2. Actualizar usuarios
            if ($oldDni) {
                $sqlU = "UPDATE usuarios 
                         SET nombre = :nom, dni = :dni, correo = :corr, estado_id = :est 
                         WHERE dni = :old_dni AND rol_id = 2";
                $stmtU = $pdo->prepare($sqlU);
                $stmtU->execute([
                    'nom' => $nombre_completo,
                    'dni' => $dni,
                    'corr' => $mail,
                    'est' => $estado,
                    'old_dni' => $oldDni
                ]);
            }
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'msg' => 'Docente actualizado correctamente.']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar. DNI o Email duplicado.']);
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
            $pdo->beginTransaction();
            
            $stmtOld = $pdo->prepare("SELECT dni FROM docentes WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldDni = $stmtOld->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM docentes WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            if ($oldDni) {
                $stmtU = $pdo->prepare("DELETE FROM usuarios WHERE dni = :dni AND rol_id = 2");
                $stmtU->execute(['dni' => $oldDni]);
            }
            
            $pdo->commit();
            echo json_encode(['status' => 'success', 'msg' => 'Docente eliminado correctamente.']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'msg' => 'No se puede eliminar el docente porque tiene cursos/calificaciones asignadas. Desactívelo en su lugar.']);
        }
        break;
        
    case 'guardar_asignacion':
        $docente_hash = $_POST['docente_id'] ?? '';
        $docente_id = decrypt_id($docente_hash);
        $curso_id = intval($_POST['curso_id'] ?? 0);
        $materia_id = intval($_POST['espacio_curricular_id'] ?? 0);
        $ciclo_id = intval($_POST['ciclo_lectivo_id'] ?? 0);
        
        if (!$docente_id || !$curso_id || !$materia_id || !$ciclo_id) {
            echo json_encode(['status' => 'error', 'msg' => 'Faltan datos obligatorios para la asignación.']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO asignaciones_docentes (docente_id, curso_id, materia_id, ciclo_lectivo_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$docente_id, $curso_id, $materia_id, $ciclo_id]);
            echo json_encode(['status' => 'success', 'msg' => 'Materia asignada correctamente al docente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Este docente ya tiene asignada esta materia en este curso y ciclo lectivo.']);
        }
        break;

    case 'listar_asignaciones':
        $docente_hash = $_POST['docente_id'] ?? '';
        $docente_id = decrypt_id($docente_hash);
        $ciclo_id = intval($_POST['ciclo_id'] ?? 0);
        
        if (!$docente_id) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido.']);
            exit;
        }
        
        $sql = "SELECT a.id, c.nombre as curso, e.asignatura as materia, cl.nombre as ciclo
                FROM asignaciones_docentes a
                JOIN cursos c ON a.curso_id = c.id
                JOIN espacios_curriculares e ON a.materia_id = e.id
                JOIN ciclos_lectivos cl ON a.ciclo_lectivo_id = cl.id
                WHERE a.docente_id = :docente_id";
        
        $params = ['docente_id' => $docente_id];
        
        if ($ciclo_id > 0) {
            $sql .= " AND a.ciclo_lectivo_id = :ciclo_id";
            $params['ciclo_id'] = $ciclo_id;
        }
        
        $sql .= " ORDER BY c.nombre ASC, e.asignatura ASC";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al cargar asignaciones.']);
        }
        break;

    case 'eliminar_asignacion':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['status' => 'error', 'msg' => 'ID de asignación inválido.']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("DELETE FROM asignaciones_docentes WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'msg' => 'Asignación eliminada.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al eliminar.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
