<?php
// Página de login
// Si ya hay sesión activa redirige directo al home
require_once 'config/conexion.php';
require_once 'config/config.php';


if (isset($_SESSION['id_usuario'])) {
    header("Location: home.php");
    exit();
}

$error = '';

// Solo proceso el POST cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Por favor, completa todos los campos.';
    } else {
        // Busco el usuario en la BD por email
        $stmt = $conexion->prepare(
            "SELECT id_usuario, nombre, password, rol FROM usuarios WHERE email = :email AND activo = 1"
        );
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Guardo los datos que voy a necesitar en otras páginas
            $_SESSION['id_usuario']    = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre'];
            $_SESSION['rol']           = $usuario['rol'];

            // Si marcó "recordarme" guardo el email en cookie 7 días
            if (isset($_POST['recordar'])) {
                setcookie('f1_email', $email, time() + (7 * 24 * 3600), '/');
            }

            // Actualizo la fecha del último acceso
            $upd = $conexion->prepare(
                "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = :id"
            );
            $upd->execute([':id' => $usuario['id_usuario']]);

            header("Location: home.php");
            exit();
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}

// Si existe la cookie relleno el campo email automáticamente
$emailGuardado = $_COOKIE['f1_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="auth-wrapper">

    <!-- Panel izquierdo -->
    <div class="auth-left">
        <div class="auth-logo-big">F1<br><span>2026</span></div>
        <div class="auth-deco">26</div>
    </div>

    <!-- Panel derecho -->
    <div class="auth-right">
        <div class="auth-box">

            <h2 class="auth-title">Bienvenido</h2>
            <p class="auth-subtitle">Inicia sesión para continuar</p>

            <?php if ($error): ?>
                <div class="auth-alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form id="formLogin" action="login.php" method="POST"
                  onsubmit="return validarFormulario('formLogin')">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email"
                           class="form-control"
                           placeholder="tu@email.com"
                           value="<?= htmlspecialchars($emailGuardado) ?>"
                           data-requerido="true"
                           autocomplete="email">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password"
                           class="form-control"
                           placeholder="••••••••"
                           data-requerido="true"
                           data-minlen="6"
                           autocomplete="current-password">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-4">
                    <input type="checkbox" class="form-check-input me-2" id="recordar" name="recordar"
                           <?= $emailGuardado ? 'checked' : '' ?>>
                    <label class="form-check-label" for="recordar">Recordarme</label>
                </div>

                <button type="submit" class="btn btn-f1 btn-f1-block mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                </button>
            </form>

            <hr class="auth-divider">
            <p class="auth-footer-text">
                ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
            </p>

        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="assets/js/validaciones.js"></script>
</body>
</html>
