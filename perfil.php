<?php
// Perfil del usuario: permite cambiar nombre, email y contraseña
require_once 'config/auth.php';
require_once 'config/config.php';

$pageTitle = 'Mi Perfil';
$msgOk     = '';
$msgError  = '';

// Cargo los datos actuales para rellenar el formulario
$stmt = $conexion->prepare(
    "SELECT id_usuario, nombre, email, rol, fecha_alta FROM usuarios WHERE id_usuario = :id"
);
$stmt->execute([':id' => $_SESSION['id_usuario']]);
$usuario = $stmt->fetch();

// Si enviaron el formulario de datos personales lo proceso
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar_datos') {
    $nombre   = trim($_POST['nombre']  ?? '');
    $email    = trim($_POST['email']   ?? '');

    if ($nombre === '' || $email === '') {
        $msgError = 'Nombre y email son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msgError = 'El formato del email no es válido.';
    } else {
        // Me aseguro de que el email nuevo no lo tenga otro usuario
        $chk = $conexion->prepare(
            "SELECT id_usuario FROM usuarios WHERE email = :email AND id_usuario != :id"
        );
        $chk->execute([':email' => $email, ':id' => $_SESSION['id_usuario']]);

        if ($chk->rowCount() > 0) {
            $msgError = 'Ese email ya está en uso por otro usuario.';
        } else {
            $upd = $conexion->prepare(
                "UPDATE usuarios SET nombre = :nombre, email = :email WHERE id_usuario = :id"
            );
            $upd->execute([
                ':nombre' => $nombre,
                ':email'  => $email,
                ':id'     => $_SESSION['id_usuario']
            ]);
            $_SESSION['nombre_usuario'] = $nombre;
            $msgOk = '✅ Datos actualizados correctamente.';
            // Recargar datos
            $stmt->execute([':id' => $_SESSION['id_usuario']]);
            $usuario = $stmt->fetch();
        }
    }
}

// Si enviaron el formulario de cambio de contraseña
if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_pass') {
    $passActual  = $_POST['pass_actual']     ?? '';
    $passNueva   = $_POST['password_nuevo']  ?? '';
    $passRepite  = $_POST['password_repetir'] ?? '';

    if ($passActual === '' || $passNueva === '' || $passRepite === '') {
        $msgError = 'Todos los campos de contraseña son obligatorios.';
    } elseif (strlen($passNueva) < 6) {
        $msgError = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($passNueva !== $passRepite) {
        $msgError = 'Las contraseñas nuevas no coinciden.';
    } else {
        // Compruebo que la contraseña actual que puso es correcta
        $chk = $conexion->prepare(
            "SELECT password FROM usuarios WHERE id_usuario = :id"
        );
        $chk->execute([':id' => $_SESSION['id_usuario']]);
        $row = $chk->fetch();

        if (!password_verify($passActual, $row['password'])) {
            $msgError = 'La contraseña actual no es correcta.';
        } else {
            $hash = password_hash($passNueva, PASSWORD_DEFAULT);
            $upd = $conexion->prepare(
                "UPDATE usuarios SET password = :pass WHERE id_usuario = :id"
            );
            $upd->execute([':pass' => $hash, ':id' => $_SESSION['id_usuario']]);
            $msgOk = '✅ Contraseña cambiada correctamente.';
        }
    }
}

include 'includes/header.php';
?>

<div class="row mb-3">
    <div class="col">
        <h2 class="fw-bold"><i class="bi bi-person-circle me-2"></i>Mi Perfil</h2>
        <p class="text-muted">Gestiona tus datos personales y contraseña</p>
    </div>
</div>

<?php if ($msgOk): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msgOk) ?></div>
<?php endif; ?>
<?php if ($msgError): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($msgError) ?></div>
<?php endif; ?>

<div class="row g-4">

    <!-- DATOS PERSONALES -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil-fill me-1"></i> Datos Personales
            </div>
            <div class="card-body">
                <form id="formPerfil" action="perfil.php" method="POST"
                      onsubmit="return validarFormulario('formPerfil')">
                    <input type="hidden" name="accion" value="actualizar_datos">

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" id="nombre" name="nombre"
                               class="form-control"
                               value="<?= htmlspecialchars($usuario['nombre']) ?>"
                               data-requerido="true">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email"
                               class="form-control"
                               value="<?= htmlspecialchars($usuario['email']) ?>"
                               data-requerido="true">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <input type="text" class="form-control"
                               value="<?= ucfirst($usuario['rol']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Miembro desde</label>
                        <input type="text" class="form-control"
                               value="<?= date('d/m/Y', strtotime($usuario['fecha_alta'])) ?>" disabled>
                    </div>

                    <button type="submit" class="btn btn-f1">
                        <i class="bi bi-save-fill me-1"></i> Guardar cambios
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- CAMBIAR CONTRASEÑA -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lock-fill me-1"></i> Cambiar Contraseña
            </div>
            <div class="card-body">
                <form id="formPass" action="perfil.php" method="POST"
                      onsubmit="return validarFormulario('formPass')">
                    <input type="hidden" name="accion" value="cambiar_pass">

                    <div class="mb-3">
                        <label for="pass_actual" class="form-label">Contraseña actual</label>
                        <input type="password" id="pass_actual" name="pass_actual"
                               class="form-control"
                               placeholder="Tu contraseña actual"
                               data-requerido="true">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="password_nuevo" class="form-label">Nueva contraseña</label>
                        <input type="password" id="password_nuevo" name="password_nuevo"
                               class="form-control"
                               placeholder="Mínimo 6 caracteres"
                               data-requerido="true" data-minlen="6">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-4">
                        <label for="password_repetir" class="form-label">Repetir nueva contraseña</label>
                        <input type="password" id="password_repetir" name="password_repetir"
                               class="form-control"
                               placeholder="Repite la nueva contraseña"
                               data-requerido="true">
                        <div class="invalid-feedback"></div>
                    </div>

                    <button type="submit" class="btn btn-f1">
                        <i class="bi bi-key-fill me-1"></i> Cambiar contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
