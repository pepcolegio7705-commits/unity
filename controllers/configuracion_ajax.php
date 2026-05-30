<?php
require_once '../config/security.php';
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'guardar':
        $nombre     = strtoupper(trim($_POST['nombre'] ?? ''));
        $direccion  = strtoupper(trim($_POST['direccion'] ?? ''));
        $telefono   = trim($_POST['telefono'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $director   = strtoupper(trim($_POST['director'] ?? ''));
        $localidad  = strtoupper(trim($_POST['localidad'] ?? ''));
        $provincia  = strtoupper(trim($_POST['provincia'] ?? ''));

        try {
            $stmt = $pdo->query("SELECT id FROM institucion LIMIT 1");
            if ($row = $stmt->fetch()) {
                $sql = "UPDATE institucion SET nombre=?, direccion=?, telefono=?, email=?, director=?, localidad=?, provincia=? WHERE id=?";
                $update = $pdo->prepare($sql);
                $update->execute([$nombre, $direccion, $telefono, $email, $director, $localidad, $provincia, $row['id']]);
                echo json_encode(['status' => 'success', 'msg' => 'Institución actualizada correctamente.']);
            } else {
                $sql = "INSERT INTO institucion (nombre, direccion, telefono, email, director, localidad, provincia) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $insert = $pdo->prepare($sql);
                $insert->execute([$nombre, $direccion, $telefono, $email, $director, $localidad, $provincia]);
                echo json_encode(['status' => 'success', 'msg' => 'Institución creada correctamente.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar los datos institucionales.']);
        }
        break;

    case 'consultar':
        try {
            $stmt = $pdo->query("SELECT * FROM institucion LIMIT 1");
            $data = $stmt->fetch();
            if ($data) {
                echo json_encode(['status' => 'success', 'data' => $data]);
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'No hay datos cargados.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al consultar.']);
        }
        break;

    case 'subir_logo':
        if (!empty($_FILES['logo']['name'])) {
            $archivoTmp = $_FILES['logo']['tmp_name'];
            $tipo = mime_content_type($archivoTmp);
            $extensionesPermitidas = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
            
            if (!in_array($tipo, $extensionesPermitidas)) {
                echo json_encode(['status' => 'error', 'msg' => 'Formato no permitido. Solo JPG, PNG, WEBP.']);
                exit;
            }

            $rutaDestino = '../assets/img/logo.png';
            $rutaFpdfJpg = '../assets/img/logo_fpdf.jpg'; // Delete the cached FPDF jpg so it regenerates

            if (move_uploaded_file($archivoTmp, $rutaDestino)) {
                if (file_exists($rutaFpdfJpg)) {
                    @unlink($rutaFpdfJpg);
                }
                
                try {
                    $stmt = $pdo->query("SELECT id FROM institucion LIMIT 1");
                    if ($row = $stmt->fetch()) {
                        $update = $pdo->prepare("UPDATE institucion SET logo_path=? WHERE id=?");
                        $update->execute(['assets/img/logo.png', $row['id']]);
                    }
                    echo json_encode(['status' => 'success', 'msg' => 'Logo actualizado correctamente.']);
                } catch (PDOException $e) {
                    echo json_encode(['status' => 'error', 'msg' => 'Error al actualizar base de datos.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'Error al subir el archivo.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'No se recibió ningún archivo.']);
        }
        break;

    case 'eliminar_logo':
        $rutaDestino = '../assets/img/logo.png';
        $rutaFpdfJpg = '../assets/img/logo_fpdf.jpg';
        
        if (file_exists($rutaDestino)) @unlink($rutaDestino);
        if (file_exists($rutaFpdfJpg)) @unlink($rutaFpdfJpg);

        try {
            $stmt = $pdo->query("SELECT id FROM institucion LIMIT 1");
            if ($row = $stmt->fetch()) {
                $update = $pdo->prepare("UPDATE institucion SET logo_path='' WHERE id=?");
                $update->execute([$row['id']]);
            }
            echo json_encode(['status' => 'success', 'msg' => 'Logo eliminado correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error en base de datos.']);
        }
        break;

    case 'guardar_orientacion':
        $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
        $descripcion = strtoupper(trim($_POST['descripcion'] ?? ''));

        if (empty($nombre)) {
            echo json_encode(['status' => 'error', 'msg' => 'Nombre requerido.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO orientaciones (nombre, descripcion) VALUES (?, ?)");
            $stmt->execute([$nombre, $descripcion]);
            echo json_encode(['status' => 'success', 'msg' => 'Orientación agregada.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar la orientación.']);
        }
        break;

    case 'listar_orientaciones':
        try {
            $stmt = $pdo->query("SELECT id, nombre, descripcion FROM orientaciones ORDER BY nombre ASC");
            $data = $stmt->fetchAll();
            foreach ($data as &$row) {
                $row['id_hash'] = encrypt_id($row['id']);
            }
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al listar orientaciones.']);
        }
        break;

    case 'eliminar_orientacion':
        $id_hash = $_POST['id'] ?? '';
        $id = decrypt_id($id_hash);
        
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido.']);
            exit;
        }

        try {
            $stmtCursos = $pdo->prepare("SELECT COUNT(*) FROM cursos WHERE orientacion_id = ?");
            $stmtCursos->execute([$id]);
            $usadaEnCursos = $stmtCursos->fetchColumn();

            $stmtMaterias = $pdo->prepare("SELECT COUNT(*) FROM materias WHERE orientacion_id = ?");
            $stmtMaterias->execute([$id]);
            $usadaEnMaterias = $stmtMaterias->fetchColumn();

            if ($usadaEnCursos > 0 || $usadaEnMaterias > 0) {
                echo json_encode(['status' => 'error', 'msg' => 'No se puede eliminar: la orientación está asociada a cursos o materias.']);
            } else {
                $stmtDel = $pdo->prepare("DELETE FROM orientaciones WHERE id = ?");
                $stmtDel->execute([$id]);
                echo json_encode(['status' => 'success', 'msg' => 'Orientación eliminada correctamente.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error en base de datos.']);
        }
        break;



    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
