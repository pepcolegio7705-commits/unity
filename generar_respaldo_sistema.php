<?php
    ini_set("display_errors", 1);
    error_reporting(E_ALL);

    header("Content-Type: application/json");

    // 📂 Ruta raíz del sistema
    $sourcePath = __DIR__;

    // 📁 Nombre y ubicación del ZIP
    $nombreZip = "respaldo_unity_" . date("Ymd_His") . ".zip";
    $zipPath = __DIR__ . "/respaldos/" . $nombreZip;

    // 🧰 Crear ZIP
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourcePath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $file) {
        if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($sourcePath) + 1);

        // ❌ Excluir respaldos y el propio script
        if (
            strpos($relativePath, "respaldos/") === 0 ||
            strpos($relativePath, "generar_respaldo_sistema.php") !== false ||
            strpos($relativePath, ".git") !== false
        ) continue;

        $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();
    echo json_encode([
        "ok" => true,
        "msg" => "Respaldo creado correctamente.",
        "archivo" => "respaldos/$nombreZip"
    ], JSON_UNESCAPED_UNICODE);
    } else {
    echo json_encode([
        "ok" => false,
        "msg" => "No se pudo generar el respaldo ZIP."
    ], JSON_UNESCAPED_UNICODE);
    }