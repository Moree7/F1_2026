<?php
// Protege las páginas que requieren login
// Si no hay sesión activa manda al login

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Comprueba si el usuario logueado es administrador
// Lo uso en todas las páginas para mostrar u ocultar botones de edición
function es_admin(): bool {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

// Bloquea cualquier POST o acción destructiva si el usuario no es admin
// Redirige de vuelta con un mensaje de error en la sesión
function solo_admin(): void {
    if (!es_admin()) {
        $_SESSION['error_permiso'] = 'No tienes permisos para realizar esta acción.';
        header("Location: " . BASE_URL . "home.php");
        exit();
    }
}
?>
