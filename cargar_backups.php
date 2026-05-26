<?php
    $archivos = glob("backups/*.sql");

    // Ordenar por fecha de modificación (más recientes primero)
    usort($archivos, function ($a, $b) {
      return filemtime($b) - filemtime($a);
    });

    foreach ($archivos as $archivo) {
      $nombre = basename($archivo);
      $fecha = date("d/m/Y H:i:s", filemtime($archivo));
      $ruta = htmlspecialchars($archivo);

      // Tamaño legible (KB o MB)
      $tamanoBytes = filesize($archivo);
      $tamano = ($tamanoBytes < 1024 * 1024)
        ? round($tamanoBytes / 1024, 2) . ' KB'
        : round($tamanoBytes / 1024 / 1024, 2) . ' MB';

      echo "<tr>
              <td>$nombre</td>
              <td>$fecha</td>
              <td>$tamano</td>
              <td class='text-nowrap'>
                <a href='$ruta' download class='btn btn-outline-primary btn-sm' title='Descargar'>
                  ⬇️
                </a>
                <button class='btn btn-outline-danger btn-sm eliminarBackup'
                        data-nombre='$nombre' title='Eliminar'>
                  🗑️
                </button>
              </td>
            </tr>";
    }