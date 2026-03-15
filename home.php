<?php
// Página principal del panel
require_once 'config/auth.php';
require_once 'config/config.php';

$pageTitle = 'Inicio';

// Contadores para las tarjetas del dashboard
$numPilotos    = $conexion->query("SELECT COUNT(*) FROM pilotos")->fetchColumn();
$numEscuderias = $conexion->query("SELECT COUNT(*) FROM escuderias")->fetchColumn();
$numCircuitos  = $conexion->query("SELECT COUNT(*) FROM circuitos")->fetchColumn();
$numCarreras   = $conexion->query("SELECT COUNT(*) FROM carreras")->fetchColumn();
$numNeumaticos = $conexion->query("SELECT COUNT(*) FROM neumaticos")->fetchColumn();

// Próximas 5 carreras programadas o en curso
$stmtProx = $conexion->query(
    "SELECT c.nombre_gp, c.fecha, c.estado, ci.nombre AS circuito, ci.pais
     FROM carreras c
     JOIN circuitos ci ON c.id_circuito = ci.id_circuito
     WHERE c.estado IN ('Programada','En curso')
     ORDER BY c.fecha ASC
     LIMIT 5"
);
$proximasCarreras = $stmtProx->fetchAll();

// Clasificación de pilotos sumando puntos de resultados
$topPilotos = $conexion->query(
    "SELECT p.nombre, p.codigo, e.nombre AS escuderia,
            COALESCE(SUM(r.puntos),0) AS total_puntos
     FROM pilotos p
     JOIN escuderias e ON p.id_escuderia = e.id_escuderia
     LEFT JOIN resultados r ON r.id_piloto = p.id_piloto
     GROUP BY p.id_piloto
     ORDER BY total_puntos DESC, p.nombre ASC"
)->fetchAll();

// Clasificación de constructores (suma puntos de sus dos pilotos)
$topConstructores = $conexion->query(
    "SELECT e.nombre, e.motor,
            COALESCE(SUM(r.puntos),0) AS total_puntos
     FROM escuderias e
     LEFT JOIN pilotos p  ON p.id_escuderia = e.id_escuderia
     LEFT JOIN resultados r ON r.id_piloto  = p.id_piloto
     GROUP BY e.id_escuderia
     ORDER BY total_puntos DESC, e.nombre ASC"
)->fetchAll();

include 'includes/header.php';
?>
<?php if (!empty($_SESSION['error_permiso'])): ?>
<div class="alert alert-danger">
    <i class="bi bi-shield-lock-fill me-1"></i>
    <?= htmlspecialchars($_SESSION['error_permiso']) ?>
    <?php unset($_SESSION['error_permiso']); ?>
</div>
<?php endif; ?>


<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold">
            🏁 Bienvenido, <?= htmlspecialchars($_SESSION['nombre_usuario']) ?>
        </h2>
        <p style="color:var(--muted)">Panel de gestión de la Temporada F1 2026</p>
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row g-3 mb-4">
    <?php
    $stats = [
        ['icon'=>'bi-person-fill',  'num'=>$numPilotos,    'label'=>'Pilotos',    'link'=>'pilotos.php'],
        ['icon'=>'bi-shield-fill',  'num'=>$numEscuderias, 'label'=>'Escuderías', 'link'=>'escuderias.php'],
        ['icon'=>'bi-map-fill',     'num'=>$numCircuitos,  'label'=>'Circuitos',  'link'=>'circuitos.php'],
        ['icon'=>'bi-flag-fill',    'num'=>$numCarreras,   'label'=>'Carreras',   'link'=>'carreras.php'],
        ['icon'=>'bi-circle-fill',  'num'=>$numNeumaticos, 'label'=>'Neumáticos', 'link'=>'neumaticos.php'],
    ];
    foreach ($stats as $s): ?>
    <div class="col-6 col-md-3 col-lg-2">
        <a href="<?= BASE_URL . $s['link'] ?>" class="text-decoration-none">
            <div class="card stat-card h-100 p-3 text-center">
                <i class="bi <?= $s['icon'] ?> fs-3 mb-2" style="color:var(--rojo)"></i>
                <div class="stat-number"><?= $s['num'] ?></div>
                <div class="stat-label"><?= $s['label'] ?></div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">

    <!-- Próximas carreras -->
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-calendar-event-fill me-1"></i> Próximas Carreras
            </div>
            <div class="card-body p-0">
                <?php if (empty($proximasCarreras)): ?>
                    <p class="p-3 text-muted">No hay carreras programadas.</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($proximasCarreras as $c): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center"
                        style="background:transparent; border-color:#2e3240; color:#e8eaf0;">
                        <div>
                            <span class="fw-semibold"><?= htmlspecialchars($c['nombre_gp']) ?></span>
                            <br>
                            <small style="color:var(--muted)">
                                📍 <?= htmlspecialchars($c['circuito']) ?>, <?= htmlspecialchars($c['pais']) ?>
                            </small>
                        </div>
                        <span class="badge bg-danger"><?= date('d M', strtotime($c['fecha'])) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Clasificación de pilotos -->
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-person-fill me-1"></i> Mundial de Pilotos
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-dark-f1 mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Piloto</th>
                            <th>Escudería</th>
                            <th class="text-end">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($topPilotos as $i => $p): ?>
                        <tr>
                            <td>
                                <?php if ($i === 0): ?>
                                    <span class="badge badge-posicion-1">🥇</span>
                                <?php elseif ($i === 1): ?>
                                    <span class="badge badge-posicion-2">🥈</span>
                                <?php elseif ($i === 2): ?>
                                    <span class="badge badge-posicion-3">🥉</span>
                                <?php else: ?>
                                    <span class="text-muted small"><?= $i + 1 ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary me-1"><?= htmlspecialchars($p['codigo']) ?></span>
                                <span class="<?= $p['total_puntos'] == 0 ? 'text-muted' : '' ?>">
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </span>
                            </td>
                            <td class="small" style="color:var(--muted)"><?= htmlspecialchars($p['escuderia']) ?></td>
                            <td class="text-end fw-bold <?= $p['total_puntos'] > 0 ? 'text-warning' : 'text-muted' ?>">
                                <?= $p['total_puntos'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Clasificación de constructores -->
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-shield-fill me-1"></i> Mundial de Constructores
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-dark-f1 mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Escudería</th>
                            <th>Motor</th>
                            <th class="text-end">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($topConstructores as $i => $e): ?>
                        <tr>
                            <td>
                                <?php if ($i === 0): ?>
                                    <span class="badge badge-posicion-1">🥇</span>
                                <?php elseif ($i === 1): ?>
                                    <span class="badge badge-posicion-2">🥈</span>
                                <?php elseif ($i === 2): ?>
                                    <span class="badge badge-posicion-3">🥉</span>
                                <?php else: ?>
                                    <span class="text-muted small"><?= $i + 1 ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold <?= $e['total_puntos'] == 0 ? 'text-muted' : '' ?>">
                                <?= htmlspecialchars($e['nombre']) ?>
                            </td>
                            <td class="small" style="color:var(--muted)"><?= htmlspecialchars($e['motor']) ?></td>
                            <td class="text-end fw-bold <?= $e['total_puntos'] > 0 ? 'text-warning' : 'text-muted' ?>">
                                <?= $e['total_puntos'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
