<?php
        header("Content-Type: application/json");
        $directorio = __DIR__ . "/respaldos/";

        $archivos = [];
        if (is_dir($directorio)) {
        $files = scandir($directorio);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === "zip") {
            $ruta = "respaldos/" . $file;
            $fecha = date("d/m/Y H:i", filemtime($directorio . $file));
            $archivos[] = [
                "nombre" => $file,
                "fecha" => $fecha,
                "ruta" => $ruta
            ];
            }
        }
        }

        echo json_encode($archivos, JSON_UNESCAPED_UNICODE);