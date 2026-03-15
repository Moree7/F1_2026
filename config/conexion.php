<?php
// Conexión a la base de datos PDO

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "f1_2026";

try {
    $conexion = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass
    );
    // Que lance excepciones si hay algún error SQL
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Así no tengo que poner FETCH_ASSOC en cada consulta
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<div class='alert alert-danger m-3'>Error de conexión: " . $e->getMessage() . "</div>");
}

// Arranco la sesión solo si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
