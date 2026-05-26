<?php
    $conn = new mysqli("localhost", "root", "", "c2621783_jawsist");
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Parámetros de DataTables
    $draw = intval($_POST['draw']);
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search = trim($_POST['search']['value'] ?? '');

    // Filtros personalizados
    $desde   = $_POST['desde'] ?? '';
    $hasta   = $_POST['hasta'] ?? '';
    $rol     = $_POST['rol'] ?? '';
    $usuario = $_POST['usuario'] ?? '';

    // 🧩 Construir condiciones dinámicas
    $condiciones = [];
    $params = [];
    $tipos = "";

    if ($desde && $hasta) {
    $condiciones[] = "fecha_acceso BETWEEN ? AND ?";
    $params[] = $desde . " 00:00:00";
    $params[] = $hasta . " 23:59:59";
    $tipos .= "ss";
    }

    if ($rol !== "") {
    $condiciones[] = "rol_id = ?";
    $params[] = intval($rol);
    $tipos .= "i";
    }

    if ($usuario !== "") {
    $condiciones[] = "(nombre LIKE ? OR correo LIKE ?)";
    $params[] = "%$usuario%";
    $params[] = "%$usuario%";
    $tipos .= "ss";
    }

    if ($search !== "") {
    $condiciones[] = "(nombre LIKE ? OR correo LIKE ? OR ip LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $tipos .= "sss";
    }

    $where = count($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";

    // 🧠 Total sin filtros
    $totalQuery = $conn->query("SELECT COUNT(*) as total FROM accesos");
    $total = $totalQuery->fetch_assoc()['total'];

    // 🧠 Total con filtros
    $sqlCount = "SELECT COUNT(*) as filtrados FROM accesos $where";
    $stmtCount = $conn->prepare($sqlCount);
    if ($params) $stmtCount->bind_param($tipos, ...$params);
    $stmtCount->execute();
    $resCount = $stmtCount->get_result();
    $filtrados = $resCount->fetch_assoc()['filtrados'];

    // 🧩 Datos paginados
    $sqlData = "SELECT nombre, correo, rol_id, ip, navegador, fecha_acceso FROM accesos $where ORDER BY fecha_acceso DESC LIMIT ?, ?";
    $params[] = $start;
    $params[] = $length;
    $tipos .= "ii";

    $stmtData = $conn->prepare($sqlData);
    $stmtData->bind_param($tipos, ...$params);
    $stmtData->execute();
    $resData = $stmtData->get_result();

    $data = [];
    while ($row = $resData->fetch_assoc()) {
    $data[] = [
        "nombre"        => $row['nombre'],
        "correo"        => $row['correo'],
        "rol"           => rolNombre($row['rol_id']),
        "ip"            => $row['ip'],
        "navegador"     => substr($row['navegador'], 0, 60) . "...",
        "fecha_acceso"  => date("d/m/Y H:i", strtotime($row['fecha_acceso']))
    ];
    }

    // 🧠 Función para mostrar nombre del rol
    function rolNombre($id) {
    $roles = [
        1 => "Administrador",
        2 => "Directivo",
        3 => "Secretario",
        4 => "Preceptor",
        5 => "Docente",
        6 => "Alumno"
    ];
    return $roles[$id] ?? "Desconocido";
    }

    // 🧁 Respuesta JSON
    echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $total,
    "recordsFiltered" => $filtrados,
    "data" => $data
    ]);