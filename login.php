<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($correo) || empty($password)) {
        $error = "Por favor, complete todos los campos.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT u.id, u.nombre, u.dni, u.correo, u.clave, u.rol_id, r.nombre AS rol 
                                   FROM usuarios u 
                                   JOIN roles r ON u.rol_id = r.id 
                                   WHERE u.correo = :correo");
            $stmt->execute(['correo' => $correo]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['clave'])) {
                $_SESSION['user_id'] = $user['id']; // Nueva arquitectura
                $_SESSION['usuario_id'] = $user['id']; // Legacy
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['correo'] = $user['correo'];
                $_SESSION['dni'] = $user['dni'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['rol_id'] = $user['rol_id'];
                
                header("Location: index.php?page=dashboard");
                exit;
            } else {
                $error = "Correo o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $error = "Error del sistema. Intente más tarde: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sintek-Unity</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #050505; /* Black */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-card {
            background: #0f172a; /* Very Dark Blue */
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            text-align: center;
            border: 1px solid #1e293b;
        }
        .login-card img {
            width: 80px;
            margin-bottom: 1.5rem;
        }
        .login-title {
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 0.5rem;
        }
        .login-subtitle {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        .form-control {
            background: #1e293b;
            border: 1px solid #334155;
            color: #f8fafc;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            background: #1e293b;
            color: #f8fafc;
            border-color: #3b82f6; /* Blue 500 */
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #eab308); /* Blue to Yellow gradient */
            color: #000; /* Contrast text */
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 700;
            width: 100%;
            transition: opacity 0.2s;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .alert {
            border-radius: 8px;
            font-size: 0.9rem;
            padding: 0.75rem;
        }
        .text-muted {
            color: #94a3b8 !important;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="assets/img/logo.png?v=<?php echo time(); ?>" alt="Sintek-Unity">
        <h2 class="login-title">Bienvenido</h2>
        <p class="login-subtitle">Ingresa tus credenciales para acceder</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="text-start">
                <label class="form-label text-muted fw-semibold small">Correo Electrónico o DNI</label>
                <input type="text" name="correo" class="form-control" required autofocus placeholder="ej. admin@unity.com o 12345678">
            </div>
            
            <div class="text-start mb-4">
                <label class="form-label text-muted fw-semibold small">Contraseña</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>