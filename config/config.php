<?php
// Constantes globales que uso en toda la aplicación
// BASE_URL se calcula automáticamente para que funcione
// con cualquier nombre de carpeta en htdocs

define('APP_NAME', 'F1 2026 Manager');

// Detecta automáticamente el nombre de la carpeta del proyecto
// Funciona con F1_2026, F1_2026-main, o cualquier otro nombre
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$carpeta   = explode('/', trim($scriptDir, '/'))[0];
define('BASE_URL', '/' . $carpeta . '/');

// API gratuita de Open-Meteo para el clima de los circuitos (no necesita clave)
define('WEATHER_API', 'https://api.open-meteo.com/v1/forecast');
?>
