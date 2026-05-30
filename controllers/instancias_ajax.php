<?php
session_start();
require_once '../config/database.php';
require_once '../config/security.php';

// Only admins can manage instances
$rol = $_SESSION['rol'] ?? '';
if (!in_array($rol, ['Administrador', 'Secretario', 'Preceptor', 'Directivo'])) {
    echo json_encode(['status' => 'error', 'msg' => 'No tienes permisos para esta acción.']);
    exit;
}

$action = $_POST['action'] ?? '';

// Get current active cycle based on current year as done everywhere else
$anio_actual = date('Y');
$nombre_ciclo = "Ciclo Lectivo " . $anio_actual;
$stmtCiclo = $pdo->prepare("SELECT id, nombre FROM ciclos_lectivos WHERE nombre = ? LIMIT 1");
$stmtCiclo->execute([$nombre_ciclo]);
$ciclo = $stmtCiclo->fetch();

if (!$ciclo) {
    // Si no existe lo creamos
    $insert = $pdo->prepare("INSERT INTO ciclos_lectivos (nombre, fecha_inicio, fecha_cierre) VALUES (?, ?, ?)");
    $insert->execute([$nombre_ciclo, "$anio_actual-03-01", "$anio_actual-12-20"]);
    $ciclo_id = $pdo->lastInsertId();
    $ciclo_nombre = $nombre_ciclo;
} else {
    $ciclo_id = $ciclo['id'];
    $ciclo_nombre = $ciclo['nombre'];
}

switch ($action) {
    case 'listar':
        try {
            $stmt = $pdo->prepare("SELECT * FROM instancias_calificacion WHERE ciclo_lectivo_id = ? ORDER BY id ASC");
            $stmt->execute([$ciclo_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($data as &$row) {
                $row['id_hash'] = encrypt_id($row['id']);
                unset($row['id']);
            }
            
            echo json_encode(['status' => 'success', 'data' => $data, 'ciclo_nombre' => $ciclo_nombre]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al cargar instancias: ' . $e->getMessage()]);
        }
        break;

    case 'guardar':
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? 'Trimestre');
        $escala_notas = trim($_POST['escala_notas'] ?? 'Numerica');
        $notas = trim($_POST['notas'] ?? '');
        $activa = isset($_POST['activa']) ? 1 : 0;
        
        if (empty($nombre)) {
            echo json_encode(['status' => 'error', 'msg' => 'El nombre es obligatorio.']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO instancias_calificacion (nombre, tipo, escala_notas, notas, ciclo_lectivo_id, activa) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $tipo, $escala_notas, $notas, $ciclo_id, $activa]);
            echo json_encode(['status' => 'success', 'msg' => 'Instancia creada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar.']);
        }
        break;

    case 'editar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? 'Trimestre');
        $escala_notas = trim($_POST['escala_notas'] ?? 'Numerica');
        $notas = trim($_POST['notas'] ?? '');
        $activa = isset($_POST['activa']) ? 1 : 0;
        
        if (empty($id) || empty($nombre)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos inválidos.']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE instancias_calificacion SET nombre = ?, tipo = ?, escala_notas = ?, notas = ?, activa = ? WHERE id = ?");
            $stmt->execute([$nombre, $tipo, $escala_notas, $notas, $activa, $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Instancia actualizada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar.']);
        }
        break;

    case 'toggle_estado':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        $activa = intval($_POST['activa'] ?? 0);
        
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido.']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE instancias_calificacion SET activa = ? WHERE id = ?");
            $stmt->execute([$activa, $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Estado actualizado.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar estado.']);
        }
        break;

    case 'eliminar':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido.']);
            exit;
        }
        
        try {
            $pdo->beginTransaction();
            // Optional: delete or keep calificaciones? Usually delete cascaded.
            // But if foreign keys are not cascaded, we delete manually.
            $stmtDel = $pdo->prepare("DELETE FROM calificaciones WHERE instancia_id = ?");
            $stmtDel->execute([$id]);
            
            $stmt = $pdo->prepare("DELETE FROM instancias_calificacion WHERE id = ?");
            $stmt->execute([$id]);
            $pdo->commit();
            
            echo json_encode(['status' => 'success', 'msg' => 'Instancia eliminada.']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'msg' => 'No se pudo eliminar la instancia.']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
