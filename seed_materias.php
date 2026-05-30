<?php
require_once 'config/database.php';

try {
    $pdo->exec("DROP TABLE IF EXISTS espacios_curriculares");
    $pdo->exec("
        CREATE TABLE espacios_curriculares (
            id INT AUTO_INCREMENT PRIMARY KEY,
            anio_estudio INT NOT NULL,
            orientacion VARCHAR(50) NOT NULL,
            asignatura VARCHAR(150) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $materias_data = [
        // 1° AÑO - COMUN
        [1, 'COMUN', 'CIENCIAS SOCIALES'],
        [1, 'COMUN', 'CONSTRUCCIÓN CIUDADANA'],
        [1, 'COMUN', 'LENGUA'],
        [1, 'COMUN', 'MUSICA'],
        [1, 'COMUN', 'ARTES VISUALES LENG. ARTÍSTICOS'],
        [1, 'COMUN', 'LENG. EXTRANJERA INGLÉS'],
        [1, 'COMUN', 'EDUCACION FISICA'],
        [1, 'COMUN', 'CIENCIAS NATURALES'],
        [1, 'COMUN', 'MATEMATICA'],
        [1, 'COMUN', 'EDUCACIÓN TECNOLÓGICA'],
        [1, 'COMUN', 'ESP. P/INTEGRACION SABERES'],
        
        // 2° AÑO - COMUN
        [2, 'COMUN', 'CIENCIAS SOCIALES'],
        [2, 'COMUN', 'CONSTRUCCIÓN CIUDADANA'],
        [2, 'COMUN', 'LENGUA'],
        [2, 'COMUN', 'MUSICA'],
        [2, 'COMUN', 'ARTES VISUALES LENG. ARTÍSTICOS'],
        [2, 'COMUN', 'LENG. EXTRANJERA INGLÉS'],
        [2, 'COMUN', 'EDUCACION FISICA'],
        [2, 'COMUN', 'CIENCIAS NATURALES'],
        [2, 'COMUN', 'MATEMATICA'],
        [2, 'COMUN', 'EDUCACIÓN TECNOLÓGICA'],
        [2, 'COMUN', 'ESP. P/INTEGRACION SABERES'],

        // 3° AÑO - COMUN
        [3, 'COMUN', 'HISTORIA'],
        [3, 'COMUN', 'GEOGRAFÍA'],
        [3, 'COMUN', 'CONSTRUCCIÓN CIUDADANA'],
        [3, 'COMUN', 'LENGUA Y LITERATURA'],
        [3, 'COMUN', 'LENG. ARTÍSTICOS MÚSICA'],
        [3, 'COMUN', 'LENG. EXTRANJERA INGLÉS'],
        [3, 'COMUN', 'EDUCACION FISICA'],
        [3, 'COMUN', 'BIOLOGÍA'],
        [3, 'COMUN', 'FÍSICO-QUÍMICA'],
        [3, 'COMUN', 'MATEMÁTICA'],
        [3, 'COMUN', 'EDUCACIÓN TECNOLÓGICA'],

        // 4° AÑO - ECONOMÍA
        [4, 'ECONOMIA', 'DERECHO'],
        [4, 'ECONOMIA', 'HISTORIA'],
        [4, 'ECONOMIA', 'GEOGRAFÍA'],
        [4, 'ECONOMIA', 'CONSTRUCCIÓN DE CIUDADANIA'],
        [4, 'ECONOMIA', 'LENGUA Y LITERATURA'],
        [4, 'ECONOMIA', 'EDUCACIÓN FÍSICA'],
        [4, 'ECONOMIA', 'BIOLOGÍA'],
        [4, 'ECONOMIA', 'QUÍMICA'],
        [4, 'ECONOMIA', 'MATEMÁTICA'],
        [4, 'ECONOMIA', 'MICROECONOMÍA'],
        [4, 'ECONOMIA', 'ADMINISTRACIÓN DE LAS ORGANIZACIONES'],
        [4, 'ECONOMIA', 'COMERCIALIZACIÓN Y MARKETING'],
        [4, 'ECONOMIA', 'COMUNICACIÓN E INFORMACIÓN EN LAS ORGANIZACIONES'],
        [4, 'ECONOMIA', 'RECURSOS HUMANOS Y RELACIONES LABORALES'],

        // 5° AÑO - ECONOMÍA
        [5, 'ECONOMIA', 'PROYECTO SOLIDARIO'],
        [5, 'ECONOMIA', 'LENGUA Y LITERATURA'],
        [5, 'ECONOMIA', 'LENGUA EXTRANJERA INGLÉS'],
        [5, 'ECONOMIA', 'EDUCACIÓN FÍSICA'],
        [5, 'ECONOMIA', 'BIOLOGÍA'],
        [5, 'ECONOMIA', 'FÍSICA'],
        [5, 'ECONOMIA', 'MATEMÁTICA'],
        [5, 'ECONOMIA', 'HISTORIA'],
        [5, 'ECONOMIA', 'GEOGRAFÍA'],
        [5, 'ECONOMIA', 'CIUDADANIA Y PARTICIPACIÓN'],
        [5, 'ECONOMIA', 'ECONOMÍA SOCIAL Y DESARROLLO LOCAL'],

        // 6° AÑO - ECONOMÍA
        [6, 'ECONOMIA', 'PROBLEMÁTICAS ACTUALES DEL MUNDO CONTEMPORÁNEO'],
        [6, 'ECONOMIA', 'ECONOMÍA'],
        [6, 'ECONOMIA', 'FILOSOFÍA'],
        [6, 'ECONOMIA', 'PROYECTO VOCACIONAL'],
        [6, 'ECONOMIA', 'LENGUA Y LITERATURA'],
        [6, 'ECONOMIA', 'LENGUA EXTRANJERA INGLÉS'],
        [6, 'ECONOMIA', 'EDUCACIÓN FÍSICA'],
        [6, 'ECONOMIA', 'PROBLEMÁTICAS CONTEXTUALIZADAS DE LAS CIENCIAS NATURALES'],
        [6, 'ECONOMIA', 'MATEMÁTICA'],
        [6, 'ECONOMIA', 'MACROECONOMÍA'],
        [6, 'ECONOMIA', 'EMPRENDIMIENTO SOCIO PRODUCTIVO'],

        // 4° AÑO - HUMANIDADES
        [4, 'HUMANIDADES', 'BIOLOGÍA'],
        [4, 'HUMANIDADES', 'QUÍMICA'],
        [4, 'HUMANIDADES', 'MATEMÁTICA'],
        [4, 'HUMANIDADES', 'LENGUAJES ARTÍSTICOS'],
        [4, 'HUMANIDADES', 'SOCIOLOGÍA'],
        [4, 'HUMANIDADES', 'HISTORIA'],
        [4, 'HUMANIDADES', 'GEOGRAFÍA'],
        [4, 'HUMANIDADES', 'CONSTRUCCIÓN DE CIUDADANIA'],
        [4, 'HUMANIDADES', 'LENGUA Y LITERATURA'],
        [4, 'HUMANIDADES', 'LENGUA EXTRANJERA INGLÉS'],
        [4, 'HUMANIDADES', 'EDUCACIÓN FÍSICA'],

        // 5° AÑO - HUMANIDADES
        [5, 'HUMANIDADES', 'HISTORIA'],
        [5, 'HUMANIDADES', 'GEOGRAFÍA'],
        [5, 'HUMANIDADES', 'METODOLOGÍA DE LA INVESTIGACIÓN EN CIENCIAS SOCIALES'],
        [5, 'HUMANIDADES', 'LENGUA EXTRANJERA INGLÉS'],
        [5, 'HUMANIDADES', 'BIOLOGÍA'],
        [5, 'HUMANIDADES', 'EDUCACIÓN FÍSICA'],
        [5, 'HUMANIDADES', 'PROYECTO SOLIDARIO'],
        [5, 'HUMANIDADES', 'LENGUA Y LITERATURA'],
        [5, 'HUMANIDADES', 'FÍSICA'],
        [5, 'HUMANIDADES', 'MATEMÁTICA'],
        [5, 'HUMANIDADES', 'PSICOLOGÍA'],

        // 6° AÑO - HUMANIDADES
        [6, 'HUMANIDADES', 'PROBLEMATICAS ACTUALESDEL MUNDO CONTEMPORÁNEO'],
        [6, 'HUMANIDADES', 'FILOSOFÍA'],
        [6, 'HUMANIDADES', 'PROBLEMATICAS CONTEXTUALIZADAS DE LAS CIENCIAS NATURALES'],
        [6, 'HUMANIDADES', 'MATEMÁTICA'],
        [6, 'HUMANIDADES', 'PROYECTO DE INVESTIGACIÓN EN CIENCIA SOCIALES'],
        [6, 'HUMANIDADES', 'PROYECTO VOCACIONAL'],
        [6, 'HUMANIDADES', 'ECONOMÍA'],
        [6, 'HUMANIDADES', 'LENGUA Y LITERATURA'],
        [6, 'HUMANIDADES', 'TRABAJO Y CIUDADANIA'],
        [6, 'HUMANIDADES', 'LENGUA EXTRANJERA INGLÉS'],
        [6, 'HUMANIDADES', 'EDUCACIÓN FÍSICA'],
        [6, 'HUMANIDADES', 'CIUDADANIA Y PARTICIPACIÓN']
    ];

    $stmt = $pdo->prepare("INSERT INTO espacios_curriculares (anio_estudio, orientacion, asignatura) VALUES (?, ?, ?)");
    foreach ($materias_data as $row) {
        $stmt->execute($row);
    }

    echo "Materias insertadas con exito!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
