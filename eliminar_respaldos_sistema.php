<?php
    header("Content-Type: application/json");

    $archivo = $_POST["archivo"] ?? "";
    $ruta = __DIR__ . "/" . $archivo;

    if (!$archivo || !file_exists($ruta)) {
    echo json_encode([
        "ok" => false,
        "msg" => "Archivo no encontrado: $archivo"
    ], JSON_UNESCAPED_UNICODE);
    exit;
    }

    if (unlink($ruta)) {
    echo json_encode([
        "ok" => true,
        "msg" => "Respaldo eliminado exitosamente."
    ], JSON_UNESCAPED_UNICODE);
    } else {
    echo json_encode([
        "ok" => false,
        "msg" => "No se pudo eliminar el respaldo."
    ], JSON_UNESCAPED_UNICODE);
    }