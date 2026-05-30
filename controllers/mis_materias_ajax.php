<?php
require_once '../config/database.php';
session_start();

$action = $_POST['action'] ?? '';
$dni_docente = $_SESSION['dni'] ?? '';

if ($action == 'listar') {
    try {
        // 1. Obtener ciclo activo (basado en el año actual)
        $anio_actual = date('Y');
        $nombre_ciclo = "Ciclo Lectivo " . $anio_actual;
        $stmtCiclo = $pdo->prepare("SELECT id, nombre FROM ciclos_lectivos WHERE nombre = ? LIMIT 1");
        $stmtCiclo->execute([$nombre_ciclo]);
        $ciclo = $stmtCiclo->fetch();
        
        if (!$ciclo) {
            echo json_encode(['status' => 'error', 'msg' => 'No hay un ciclo lectivo configurado como activo en el sistema.']);
            exit;
        }

        // 2. Obtener el ID del docente vinculado a esta cuenta de usuario
        $stmtDoc = $pdo->prepare("SELECT id, nombre, apellido FROM docentes WHERE dni = :dni");
        $stmtDoc->execute(['dni' => $dni_docente]);
        $docente = $stmtDoc->fetch();
        
        if (!$docente) {
            echo json_encode(['status' => 'error', 'msg' => 'Tu usuario no está vinculado a un legajo de docente válido. Por favor contacta a administración.']);
            exit;
        }

        // 3. Obtener materias asignadas a este docente en el ciclo activo
        $sql = "
            SELECT 
                ac.id AS asignacion_id,
                ac.curso_id,
                ac.materia_id,
                c.nombre AS curso_nombre, 
                c.turno,
                o.nombre AS orientacion,
                m.asignatura AS materia_nombre
            FROM asignaciones_docentes ac
            JOIN cursos c ON ac.curso_id = c.id
            JOIN orientaciones o ON c.orientacion_id = o.id
            JOIN espacios_curriculares m ON ac.materia_id = m.id
            WHERE ac.docente_id = :docente_id 
              AND ac.ciclo_lectivo_id = :ciclo_id
            ORDER BY c.nombre ASC, m.asignatura ASC
        ";
        $stmtAssig = $pdo->prepare($sql);
        $stmtAssig->execute([
            'docente_id' => $docente['id'],
            'ciclo_id' => $ciclo['id']
        ]);
        
        $asignaciones = $stmtAssig->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'ciclo' => $ciclo['nombre'],
            'docente' => $docente['apellido'] . ', ' . $docente['nombre'],
            'data' => $asignaciones
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
    }
}
?>
