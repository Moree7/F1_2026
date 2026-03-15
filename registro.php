<?php
// Formulario de registro de nuevos usuarios
require_once 'config/conexion.php';
require_once 'config/config.php';

if (isset($_SESSION['id_usuario'])) {
    header("Location: home.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']            ?? '');
    $email    = trim($_POST['email']             ?? '');
    $password = trim($_POST['password']          ?? '');
    $confirma = trim($_POST['password_repetir']  ?? '');

    // Valido también en servidor por si alguien desactiva el JS
    if ($nombre === '' || $email === '' || $password === '') {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirma) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Compruebo que el email no esté ya registrado
        $check = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
        $check->execute([':email' => $email]);

        if ($check->rowCount() > 0) {
            $error = 'Ese email ya está registrado.';
        } else {
            // Cifro la contraseña con bcrypt antes de guardarla
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare(
                "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :pass, 'usuario')"
            );
            $stmt->execute([
                ':nombre' => $nombre,
                ':email'  => $email,
                ':pass'   => $hash
            ]);
            $success = 'Usuario registrado correctamente. Ya puedes iniciar sesión.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="auth-wrapper">

    <!-- Panel izquierdo -->
    <div class="auth-left">
        <div class="auth-logo-big">F1<br><span>2026</span></div>
        <p class="auth-tagline">Race Management System</p>
        <div class="auth-deco">26</div>
    </div>

    <!-- Panel derecho -->
    <div class="auth-right">
        <div class="auth-box">

            <h2 class="auth-title">Crear cuenta</h2>
            <p class="auth-subtitle">Rellena los datos para registrarte</p>

            <?php if ($error): ?>
                <div class="auth-alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="auth-alert auth-alert-success">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <?= htmlspecialchars($success) ?>
                    <a href="login.php" style="color:#70dd99;font-weight:600;" class="ms-1">Ir al login</a>
                </div>
            <?php endif; ?>

            <form id="formRegistro" action="registro.php" method="POST"
                  onsubmit="return validarFormulario('formRegistro')">

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre"
                           class="form-control" placeholder="Tu nombre"
                           data-requerido="true"
                           value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email"
                           class="form-control" placeholder="tu@email.com"
                           data-requerido="true"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password"
                           class="form-control" placeholder="Mínimo 6 caracteres"
                           data-requerido="true" data-minlen="6">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-4">
                    <label for="password_repetir" class="form-label">Repetir contraseña</label>
                    <input type="password" id="password_repetir" name="password_repetir"
                           class="form-control" placeholder="Repite la contraseña"
                           data-requerido="true">
                    <div class="invalid-feedback"></div>
                </div>

                <button type="submit" class="btn btn-f1 btn-f1-block mb-3">
                    <i class="bi bi-person-plus-fill me-1"></i> Registrarse
                </button>
            </form>

            <hr class="auth-divider">
            <p class="auth-footer-text">
                ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
            </p>

        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="assets/js/validaciones.js"></script>
</body>
</html>
