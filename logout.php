<?php
// Cierra la sesión del usuario y lo manda al login
require_once 'config/conexion.php';

// Borro la cookie de recordarme si existe
if (isset($_COOKIE['f1_email'])) {
    setcookie('f1_email', '', time() - 3600, '/');
}

// Destruyo la sesión y vacío los datos
$_SESSION = [];
session_destroy();

header("Location: login.php");
exit();
?>
