<?php
// Cabecera común: DOCTYPE, Bootstrap, navbar y apertura del main
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?><?= APP_NAME ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark f1-navbar">
    <div class="container-fluid">

        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>home.php">
            <img src="<?= BASE_URL ?>assets/img/f1-logo.png"
                 onerror="this.style.display='none'"
                 height="30" alt="F1 logo">
            <span>🏎️ <?= APP_NAME ?></span>
        </a>

        <!-- Botón hamburguesa para móvil -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <?php if (isset($_SESSION['id_usuario'])): ?>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>pilotos.php">
                        <i class="bi bi-person-fill"></i> Pilotos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>escuderias.php">
                        <i class="bi bi-shield-fill"></i> Escuderías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>circuitos.php">
                        <i class="bi bi-map-fill"></i> Circuitos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>carreras.php">
                        <i class="bi bi-flag-fill"></i> Carreras
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>resultados.php">
                        <i class="bi bi-trophy-fill"></i> Resultados
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>neumaticos.php">
                        🏎️ Neumáticos
                    </a>
                </li>
            </ul>

            <!-- Menú desplegable con nombre del usuario -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1"
                       href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="fw-semibold">
                            <?= htmlspecialchars($_SESSION['nombre_usuario']) ?>
                        </span>
                        <?php if (($_SESSION['rol'] ?? '') === 'admin'): ?>
                            <span class="badge bg-danger" style="font-size:0.65rem">ADMIN</span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-dark-f1">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>perfil.php">
                                <i class="bi bi-gear-fill me-1"></i> Mi perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container-fluid py-4 px-4">
