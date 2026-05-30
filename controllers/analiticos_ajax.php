<?php
require_once '../config/security.php';
require_once '../config/database.php';
require_login();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// Acciones que requieren validación CSRF
$acciones_escritura = ['guardar_cabecera', 'guardar_nota', 'eliminar_nota', 'guardar_observacion'];
if (in_array($action, $acciones_escritura)) {
    if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
        echo json_encode(['status' => 'error', 'msg' => 'Falsificación de petición detectada (CSRF).']);
        exit;
    }
}

switch ($action) {

    // ==========================================
    // ALUMNOS
    // ==========================================
    case 'obtener_alumnos':
        try {
            $stmt = $pdo->query("SELECT id, alumno, dni FROM lista_alfa ORDER BY alumno ASC");
            $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($alumnos as &$a) {
                $a['id_hash'] = encrypt_id($a['id']);
                unset($a['id']);
            }
            echo json_encode(['status' => 'success', 'data' => $alumnos]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al obtener alumnos.']);
        }
        break;

    case 'buscar_por_dni':
        $dni = trim($_POST['dni'] ?? '');
        if (empty($dni)) {
            echo json_encode(['status' => 'error', 'msg' => 'DNI no proporcionado.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id, alumno, dni, legajo, escp FROM lista_alfa WHERE dni = :dni LIMIT 1");
            $stmt->execute(['dni' => $dni]);
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($alumno) {
                $alumno['id_hash'] = encrypt_id($alumno['id']);
                unset($alumno['id']);
                echo json_encode(['status' => 'success', 'data' => $alumno]);
            } else {
                echo json_encode(['status' => 'error', 'msg' => 'Alumno no encontrado.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error en la búsqueda.']);
        }
        break;

    // ==========================================
    // CABECERA
    // ==========================================
    case 'obtener_cabecera':
        $alumno_id = decrypt_id($_POST['alumno_id'] ?? '');
        if (empty($alumno_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID de alumno inválido.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM analiticos_cabecera WHERE alumno_id = :alumno_id");
        $stmt->execute(['alumno_id' => $alumno_id]);
        $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cabecera) {
            echo json_encode(['status' => 'success', 'data' => $cabecera]);
        } else {
            // Si no existe, devolvemos success pero data null para que el form se limpie
            echo json_encode(['status' => 'success', 'data' => null]);
        }
        break;

    case 'guardar_cabecera':
        $alumno_id = decrypt_id($_POST['alumno_id'] ?? '');
        $archivo_no = trim($_POST['archivo_no'] ?? '');
        $escuela_procedencia = trim($_POST['escuela_procedencia'] ?? '');
        $fecha_emision = trim($_POST['fecha_emision'] ?? '');
        $observaciones_generales = trim($_POST['observaciones_generales'] ?? '');

        if (empty($alumno_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID de alumno inválido.']);
            exit;
        }

        try {
            // Verificar si ya existe cabecera
            $stmtCheck = $pdo->prepare("SELECT id FROM analiticos_cabecera WHERE alumno_id = :alumno_id");
            $stmtCheck->execute(['alumno_id' => $alumno_id]);
            $existe = $stmtCheck->fetchColumn();

            if ($existe) {
                $sql = "UPDATE analiticos_cabecera SET 
                            archivo_no = :archivo_no, 
                            escuela_procedencia = :escuela_procedencia, 
                            fecha_emision = :fecha_emision, 
                            observaciones_generales = :observaciones_generales 
                        WHERE alumno_id = :alumno_id";
            } else {
                $sql = "INSERT INTO analiticos_cabecera (alumno_id, archivo_no, escuela_procedencia, fecha_emision, observaciones_generales) 
                        VALUES (:alumno_id, :archivo_no, :escuela_procedencia, :fecha_emision, :observaciones_generales)";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'alumno_id' => $alumno_id,
                'archivo_no' => $archivo_no,
                'escuela_procedencia' => $escuela_procedencia,
                'fecha_emision' => empty($fecha_emision) ? null : $fecha_emision,
                'observaciones_generales' => $observaciones_generales
            ]);

            echo json_encode(['status' => 'success', 'msg' => 'Cabecera guardada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar la cabecera: ' . $e->getMessage()]);
        }
        break;

    // ==========================================
    // NOTAS (HISTORIAL ACADÉMICO)
    // ==========================================
    case 'listar_notas':
        $alumno_id = decrypt_id($_POST['alumno_id'] ?? '');
        if (empty($alumno_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID de alumno inválido.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM analiticos_notas WHERE alumno_id = :alumno_id ORDER BY anio_estudio ASC, id ASC");
            $stmt->execute(['alumno_id' => $alumno_id]);
            $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Encriptar IDs para el frontend
            foreach ($notas as &$nota) {
                $nota['id_hash'] = encrypt_id($nota['id']);
            }

            echo json_encode(['status' => 'success', 'data' => $notas]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al listar notas.']);
        }
        break;

    case 'guardar_nota':
        $id_hash = $_POST['nota_id_hash'] ?? '';
        $alumno_id = decrypt_id($_POST['alumno_id'] ?? '');
        $anio_estudio = intval($_POST['anio_estudio'] ?? 0);
        
        $asignatura = trim($_POST['asignatura'] ?? '');
        $calificacion_num = trim($_POST['calificacion_num'] ?? '');
        $calificacion_letras = trim($_POST['calificacion_letras'] ?? '');
        $condicion_establecimiento = trim($_POST['condicion_establecimiento'] ?? '');
        $acta_num = trim($_POST['acta_num'] ?? '');
        $fecha = trim($_POST['fecha'] ?? '');
        $repite_nota = trim($_POST['repite_nota'] ?? '');
        $repite_fecha = trim($_POST['repite_fecha'] ?? '');
        $calificacion_definitiva = trim($_POST['calificacion_definitiva'] ?? '');

        if (empty($alumno_id) || empty($anio_estudio) || empty($asignatura)) {
            echo json_encode(['status' => 'error', 'msg' => 'Faltan datos obligatorios (Asignatura).']);
            exit;
        }

        try {
            if (!empty($id_hash)) {
                $id = decrypt_id($id_hash);
                $sql = "UPDATE analiticos_notas SET 
                            asignatura = :asignatura,
                            calificacion_num = :calificacion_num,
                            calificacion_letras = :calificacion_letras,
                            condicion_establecimiento = :condicion_establecimiento,
                            acta_num = :acta_num,
                            fecha = :fecha,
                            repite_nota = :repite_nota,
                            repite_fecha = :repite_fecha,
                            calificacion_definitiva = :calificacion_definitiva
                        WHERE id = :id AND alumno_id = :alumno_id";
                $params = [
                    'asignatura' => $asignatura,
                    'calificacion_num' => $calificacion_num,
                    'calificacion_letras' => $calificacion_letras,
                    'condicion_establecimiento' => $condicion_establecimiento,
                    'acta_num' => $acta_num,
                    'fecha' => $fecha,
                    'repite_nota' => $repite_nota,
                    'repite_fecha' => $repite_fecha,
                    'calificacion_definitiva' => $calificacion_definitiva,
                    'id' => $id,
                    'alumno_id' => $alumno_id
                ];
            } else {
                $sql = "INSERT INTO analiticos_notas (
                            alumno_id, anio_estudio, asignatura, calificacion_num, calificacion_letras,
                            condicion_establecimiento, acta_num, fecha, repite_nota, repite_fecha, calificacion_definitiva
                        ) VALUES (
                            :alumno_id, :anio_estudio, :asignatura, :calificacion_num, :calificacion_letras,
                            :condicion_establecimiento, :acta_num, :fecha, :repite_nota, :repite_fecha, :calificacion_definitiva
                        )";
                $params = [
                    'alumno_id' => $alumno_id,
                    'anio_estudio' => $anio_estudio,
                    'asignatura' => $asignatura,
                    'calificacion_num' => $calificacion_num,
                    'calificacion_letras' => $calificacion_letras,
                    'condicion_establecimiento' => $condicion_establecimiento,
                    'acta_num' => $acta_num,
                    'fecha' => $fecha,
                    'repite_nota' => $repite_nota,
                    'repite_fecha' => $repite_fecha,
                    'calificacion_definitiva' => $calificacion_definitiva
                ];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['status' => 'success', 'msg' => 'Asignatura guardada correctamente.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar la asignatura.']);
        }
        break;

    case 'eliminar_nota':
        $id = decrypt_id($_POST['id'] ?? '');
        if (empty($id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM analiticos_notas WHERE id = :id");
            $stmt->execute(['id' => $id]);
            echo json_encode(['status' => 'success', 'msg' => 'Registro eliminado.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al eliminar.']);
        }
        break;

    // ==========================================
    // ESPACIOS CURRICULARES
    // ==========================================
    case 'obtener_materias':
        $anio = intval($_POST['anio'] ?? 0);
        if ($anio < 1 || $anio > 6) {
            echo json_encode(['status' => 'error', 'msg' => 'Año inválido.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT orientacion, asignatura FROM espacios_curriculares WHERE anio_estudio = :anio ORDER BY orientacion ASC, id ASC");
            $stmt->execute(['anio' => $anio]);
            $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar por orientación
            $agrupado = [];
            foreach ($materias as $m) {
                $agrupado[$m['orientacion']][] = $m['asignatura'];
            }

            echo json_encode(['status' => 'success', 'data' => $agrupado]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al obtener materias.']);
        }
        break;

    // ==========================================
    // OBSERVACIONES
    // ==========================================
    case 'obtener_observaciones':
        $alumno_id = decrypt_id($_POST['alumno_id'] ?? '');
        if (empty($alumno_id)) {
            echo json_encode(['status' => 'error', 'msg' => 'ID inválido.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT anio_estudio, observacion FROM analiticos_observaciones WHERE alumno_id = :alumno_id");
            $stmt->execute(['alumno_id' => $alumno_id]);
            $obs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Retorna array asociativo: [anio => obs]
            
            echo json_encode(['status' => 'success', 'data' => $obs]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al obtener observaciones.']);
        }
        break;

    case 'guardar_observacion':
        $alumno_id = decrypt_id($_POST['alumno_id'] ?? '');
        $anio_estudio = intval($_POST['anio_estudio'] ?? 0);
        $observacion = trim($_POST['observacion'] ?? '');

        if (empty($alumno_id) || empty($anio_estudio)) {
            echo json_encode(['status' => 'error', 'msg' => 'Datos incompletos.']);
            exit;
        }

        try {
            $sql = "INSERT INTO analiticos_observaciones (alumno_id, anio_estudio, observacion) 
                    VALUES (:alumno_id, :anio_estudio, :observacion) 
                    ON DUPLICATE KEY UPDATE observacion = :observacion2";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'alumno_id' => $alumno_id,
                'anio_estudio' => $anio_estudio,
                'observacion' => $observacion,
                'observacion2' => $observacion
            ]);

            echo json_encode(['status' => 'success', 'msg' => 'Observación actualizada.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Error al guardar observación.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'msg' => 'Acción no válida.']);
}
?>
