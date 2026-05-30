<?php
require_once 'config/database.php';

try {
    // Tabla analiticos_cabecera
    $sql_cabecera = "CREATE TABLE IF NOT EXISTS analiticos_cabecera (
        id INT AUTO_INCREMENT PRIMARY KEY,
        alumno_id INT NOT NULL,
        archivo_no VARCHAR(50),
        escuela_procedencia VARCHAR(255),
        fecha_emision DATE,
        observaciones_generales TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
    $pdo->exec($sql_cabecera);

    // Tabla analiticos_notas
    $sql_notas = "CREATE TABLE IF NOT EXISTS analiticos_notas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        alumno_id INT NOT NULL,
        anio_estudio INT NOT NULL,
        asignatura VARCHAR(150) NOT NULL,
        calificacion_num VARCHAR(10),
        calificacion_letras VARCHAR(50),
        condicion_establecimiento VARCHAR(100),
        acta_num VARCHAR(50),
        fecha VARCHAR(50),
        repite_nota VARCHAR(10),
        repite_fecha VARCHAR(50),
        calificacion_definitiva VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
    $pdo->exec($sql_notas);

    // Tabla analiticos_observaciones
    $sql_obs = "CREATE TABLE IF NOT EXISTS analiticos_observaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        alumno_id INT NOT NULL,
        anio_estudio INT NOT NULL,
        observacion VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY(alumno_id, anio_estudio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
    $pdo->exec($sql_obs);

    echo json_encode(['status' => 'success', 'msg' => 'Tablas creadas correctamente']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}
?>
