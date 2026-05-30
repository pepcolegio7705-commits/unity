<?php
require_once 'config/database.php';
try {
    $pdo->exec("ALTER TABLE lista_alfa MODIFY email VARCHAR(120), MODIFY telefono VARCHAR(40)");
    echo "Exito";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
