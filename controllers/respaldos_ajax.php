<?php
require_once '../config/security.php';
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Acciones que requieren CSRF
$acciones_escritura = ['crear', 'restaurar', 'eliminar'];
if (in_array($action, $acciones_escritura)) {
    if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'msg' => 'Falsificación de petición detectada (CSRF).']);
        exit;
    }
}

$respaldos_dir = '../respaldos/';

switch ($action) {
    case 'listar':
        $archivos = [];
        if (is_dir($respaldos_dir)) {
            $files = scandir($respaldos_dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $ruta = $respaldos_dir . $file;
                    $archivos[] = [
                        'nombre' => $file,
                        'fecha' => date('Y-m-d H:i:s', filemtime($ruta)),
                        'tamano' => round(filesize($ruta) / 1024, 2) . ' KB',
                        'id_hash' => encrypt_id($file)
                    ];
                }
            }
        }
        // Ordenar por fecha descendente
        usort($archivos, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        echo json_encode([
            "draw" => intval($_POST['draw'] ?? 1),
            "recordsTotal" => count($archivos),
            "recordsFiltered" => count($archivos),
            "data" => $archivos
        ]);
        break;

    case 'crear':
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $respaldos_dir . $filename;
            $file = fopen($filepath, 'w');

            // Escribir cabecera
            fwrite($file, "-- Sintek-Unity PDO SQL Dump\n");
            fwrite($file, "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n");
            fwrite($file, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            // Obtener tablas
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Estructura de la tabla
                $stmt2 = $pdo->query("SHOW CREATE TABLE `$table`");
                $row2 = $stmt2->fetch(PDO::FETCH_NUM);
                fwrite($file, "DROP TABLE IF EXISTS `$table`;\n");
                fwrite($file, $row2[1] . ";\n\n");

                // Datos de la tabla
                $stmt3 = $pdo->query("SELECT * FROM `$table`");
                $rowsCount = $stmt3->rowCount();
                if ($rowsCount > 0) {
                    while ($row = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                        $keys = array_keys($row);
                        $keys = array_map(function($key) { return "`$key`"; }, $keys);
                        
                        $values = array_values($row);
                        $values = array_map(function($value) use ($pdo) {
                            if (is_null($value)) return 'NULL';
                            return $pdo->quote($value);
                        }, $values);

                        $insert = "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                        fwrite($file, $insert);
                    }
                    fwrite($file, "\n");
                }
            }

            fwrite($file, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($file);

            echo json_encode(['status' => 'success', 'msg' => 'Respaldo creado correctamente.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al crear respaldo: ' . $e->getMessage()]);
        }
        break;

    case 'restaurar':
        $id_hash = $_POST['id'] ?? '';
        $file = decrypt_id($id_hash);
        
        if (empty($file) || strpos($file, '..') !== false || pathinfo($file, PATHINFO_EXTENSION) !== 'sql') {
            echo json_encode(['status' => 'error', 'msg' => 'Archivo inválido.']);
            exit;
        }

        $ruta = $respaldos_dir . $file;
        if (!file_exists($ruta)) {
            echo json_encode(['status' => 'error', 'msg' => 'El archivo no existe.']);
            exit;
        }

        try {
            // Leer el archivo sql
            $sql = file_get_contents($ruta);
            
            // Habilitar temporalmente la emulación de prepared statements para procesar múltiples queries de una vez
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $pdo->exec($sql);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            echo json_encode(['status' => 'success', 'msg' => 'Base de datos restaurada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al restaurar: ' . $e->getMessage()]);
        }
        break;

    case 'eliminar':
        $id_hash = $_POST['id'] ?? '';
        $file = decrypt_id($id_hash);
        
        if (empty($file) || strpos($file, '..') !== false) {
            echo json_encode(['status' => 'error', 'msg' => 'Archivo inválido.']);
            exit;
        }

        $ruta = $respaldos_dir . $file;
        if (file_exists($ruta)) {
            unlink($ruta);
            echo json_encode(['status' => 'success', 'msg' => 'Respaldo eliminado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'El archivo no existe.']);
        }
        break;

    case 'descargar':
        $id_hash = $_GET['id'] ?? '';
        $file = decrypt_id($id_hash);
        
        if (empty($file) || strpos($file, '..') !== false || pathinfo($file, PATHINFO_EXTENSION) !== 'sql') {
            die('Archivo inválido.');
        }

        $ruta = $respaldos_dir . $file;
        if (file_exists($ruta)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($ruta).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($ruta));
            readfile($ruta);
            exit;
        } else {
            die('El archivo no existe.');
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
