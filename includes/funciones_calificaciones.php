<?php

    function obtenerCicloLectivoPorInstancia($instancia_id, $conexion) {
        $sql = "SELECT ciclo_lectivo_id FROM instancias_calificacion WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $instancia_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $dato = $res->fetch_assoc();
        $stmt->close();

        return intval($dato["ciclo_lectivo_id"] ?? 0);
    }