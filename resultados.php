<?php
// CRUD de resultados de carrera
// Pestaña Finalizadas: resultados reales
// Pestaña Pendientes: carreras sin resultados con los 22 pilotos
require_once 'config/auth.php';
require_once 'config/config.php';

$pageTitle = 'Resultados';
$msgOk = $msgError = '';

// Listas para los selects del formulario
$carreras = $conexion->query(
    "SELECT id_carrera, nombre_gp, fecha FROM carreras ORDER BY fecha ASC"
)->fetchAll();

$pilotos = $conexion->query(
    "SELECT p.id_piloto, p.nombre, p.codigo, e.nombre AS escuderia
     FROM pilotos p JOIN escuderias e ON p.id_escuderia = e.id_escuderia
     ORDER BY p.nombre"
)->fetchAll();

// Insertar resultado nuevo
// Solo el admin puede modificar datos
if (isset($_POST['accion']) || isset($_GET['borrar']) || isset($_GET['editar'])) {
    solo_admin();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    try {
        $stmt = $conexion->prepare(
            "INSERT INTO resultados (id_carrera, id_piloto, posicion, puntos, vueltas, tiempo_total, estado_carrera, pit_stops)
             VALUES (:id_carrera, :id_piloto, :posicion, :puntos, :vueltas, :tiempo_total, :estado_carrera, :pit_stops)"
        );
        $stmt->execute([
            ':id_carrera'    => (int)$_POST['id_carrera'],
            ':id_piloto'     => (int)$_POST['id_piloto'],
            ':posicion'      => (int)$_POST['posicion'],
            ':puntos'        => floatval($_POST['puntos']),
            ':vueltas'       => (int)$_POST['vueltas'],
            ':tiempo_total'  => trim($_POST['tiempo_total']),
            ':estado_carrera'=> $_POST['estado_carrera'],
            ':pit_stops'     => (int)$_POST['pit_stops'],
        ]);
        $msgOk = 'Resultado añadido correctamente.';
    } catch (\PDOException $e) {
        $msgError = 'Error: ese piloto ya tiene resultado en esta carrera.';
    }
}

// Actualizar resultado
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $stmt = $conexion->prepare(
        "UPDATE resultados SET id_carrera=:id_carrera, id_piloto=:id_piloto,
         posicion=:posicion, puntos=:puntos, vueltas=:vueltas,
         tiempo_total=:tiempo_total, estado_carrera=:estado_carrera, pit_stops=:pit_stops
         WHERE id_resultado=:id"
    );
    $stmt->execute([
        ':id_carrera'    => (int)$_POST['id_carrera'],
        ':id_piloto'     => (int)$_POST['id_piloto'],
        ':posicion'      => (int)$_POST['posicion'],
        ':puntos'        => floatval($_POST['puntos']),
        ':vueltas'       => (int)$_POST['vueltas'],
        ':tiempo_total'  => trim($_POST['tiempo_total']),
        ':estado_carrera'=> $_POST['estado_carrera'],
        ':pit_stops'     => (int)$_POST['pit_stops'],
        ':id'            => (int)$_POST['id_resultado'],
    ]);
    $msgOk = 'Resultado actualizado.';
}

// Borrar resultado
if (isset($_GET['borrar'])) {
    $stmt = $conexion->prepare("DELETE FROM resultados WHERE id_resultado = :id");
    $stmt->execute([':id' => (int)$_GET['borrar']]);
    $msgOk = 'Resultado eliminado.';
}

// Cargo el resultado a editar si viene el parámetro
$resultadoEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $conexion->prepare("SELECT * FROM resultados WHERE id_resultado = :id");
    $stmt->execute([':id' => (int)$_GET['editar']]);
    $resultadoEditar = $stmt->fetch();
}

// Pestaña activa (finalizadas por defecto)
$tab = $_GET['tab'] ?? 'finalizadas';
$filtroCarrera = (int)($_GET['carrera'] ?? 0);

// --- RESULTADOS FINALIZADOS ---
// Carreras que ya tienen al menos un resultado
if ($filtroCarrera > 0) {
    $stmtR = $conexion->prepare(
        "SELECT r.*, p.nombre AS piloto, p.codigo, e.nombre AS escuderia, c.nombre_gp
         FROM resultados r
         JOIN pilotos p    ON r.id_piloto  = p.id_piloto
         JOIN escuderias e ON p.id_escuderia = e.id_escuderia
         JOIN carreras c   ON r.id_carrera = c.id_carrera
         WHERE r.id_carrera = :id
         ORDER BY r.posicion ASC"
    );
    $stmtR->execute([':id' => $filtroCarrera]);
} else {
    $stmtR = $conexion->query(
        "SELECT r.*, p.nombre AS piloto, p.codigo, e.nombre AS escuderia, c.nombre_gp
         FROM resultados r
         JOIN pilotos p    ON r.id_piloto  = p.id_piloto
         JOIN escuderias e ON p.id_escuderia = e.id_escuderia
         JOIN carreras c   ON r.id_carrera = c.id_carrera
         ORDER BY c.fecha DESC, r.posicion ASC
         LIMIT 100"
    );
}
$resultados = $stmtR->fetchAll();

// Solo las carreras que tienen resultados (para el filtro de la pestaña finalizadas)
$carrerasConResultados = $conexion->query(
    "SELECT DISTINCT c.id_carrera, c.nombre_gp, c.fecha
     FROM carreras c
     INNER JOIN resultados r ON r.id_carrera = c.id_carrera
     ORDER BY c.fecha ASC"
)->fetchAll();

// --- CARRERAS PENDIENTES ---
// Carreras programadas o en curso sin ningún resultado todavía
$carrerasPendientes = $conexion->query(
    "SELECT c.id_carrera, c.nombre_gp, c.fecha, c.estado,
            ci.nombre AS circuito, ci.pais
     FROM carreras c
     JOIN circuitos ci ON c.id_circuito = ci.id_circuito
     WHERE c.estado IN ('Programada','En curso')
       AND c.id_carrera NOT IN (SELECT DISTINCT id_carrera FROM resultados)
     ORDER BY c.fecha ASC"
)->fetchAll();

// Para la pestaña pendientes: si se filtra por una carrera concreta, mostrar sus 22 pilotos
$filtroCarreraPendiente = (int)($_GET['carrera_p'] ?? 0);
$pilotosPendientes = [];
if ($filtroCarreraPendiente > 0) {
    $pilotosPendientes = $conexion->query(
        "SELECT p.id_piloto, p.nombre, p.codigo, e.nombre AS escuderia
         FROM pilotos p
         JOIN escuderias e ON p.id_escuderia = e.id_escuderia
         WHERE p.id_piloto NOT IN (
             SELECT id_piloto FROM resultados WHERE id_carrera = $filtroCarreraPendiente
         )
         ORDER BY e.nombre, p.nombre"
    )->fetchAll();
}

include 'includes/header.php';

$esEdicion = ($resultadoEditar !== null);
$d = $resultadoEditar ?? [
    'id_resultado'=>'','id_carrera'=>'','id_piloto'=>'',
    'posicion'=>'','puntos'=>'','vueltas'=>'','tiempo_total'=>'',
    'estado_carrera'=>'Terminó','pit_stops'=>1
];
?>

<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="fw-bold"><i class="bi bi-trophy-fill me-2"></i>Resultados</h2>
    </div>
    <?php if (es_admin()): ?>
        <div class="col-auto">
        <button class="btn btn-f1" data-bs-toggle="modal" data-bs-target="#modalResultado">
            <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Resultado
        </button>
    </div>
        <?php endif; ?>
</div>

<?php if ($msgOk):   ?><div class="alert alert-success"><?= htmlspecialchars($msgOk) ?></div><?php endif; ?>
<?php if ($msgError): ?><div class="alert alert-danger"><?= htmlspecialchars($msgError) ?></div><?php endif; ?>

<!-- Pestañas -->
<ul class="nav nav-tabs mb-3" id="tabResultados">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'finalizadas' ? 'active' : '' ?>"
           href="resultados.php?tab=finalizadas">
            🏁 Finalizadas
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'pendientes' ? 'active' : '' ?>"
           href="resultados.php?tab=pendientes">
            🗓️ Pendientes
        </a>
    </li>
</ul>

<?php if ($tab === 'finalizadas'): ?>
<!-- ============================================================ -->
<!-- PESTAÑA FINALIZADAS                                          -->
<!-- ============================================================ -->

<?php if (!empty($carrerasConResultados)): ?>
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="tab" value="finalizadas">
            <div class="col-md-7">
                <label class="form-label mb-1 small" style="color:var(--muted)">Filtrar por carrera</label>
                <select name="carrera" class="form-select form-select-sm">
                    <option value="0">Todas las finalizadas</option>
                    <?php foreach ($carrerasConResultados as $c): ?>
                    <option value="<?= $c['id_carrera'] ?>"
                        <?= ($filtroCarrera == $c['id_carrera']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['nombre_gp']) ?>
                        (<?= date('d/m/Y', strtotime($c['fecha'])) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-f1">Filtrar</button>
                <?php if ($filtroCarrera): ?>
                    <a href="resultados.php?tab=finalizadas" class="btn btn-sm btn-outline-secondary ms-1">Ver todas</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-dark-f1 table-hover mb-0">
            <thead>
                <tr>
                    <th>Pos.</th>
                    <th>Piloto</th>
                    <th>Escudería</th>
                    <th>Gran Premio</th>
                    <th>Tiempo</th>
                    <th class="text-end">Pts</th>
                    <th>Vueltas</th>
                    <th>Estado</th>
                    <th>Pit</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($resultados as $r): ?>
            <tr>
                <td>
                    <?php if ($r['posicion'] == 1): ?>
                        <span class="badge badge-posicion-1">🥇 1</span>
                    <?php elseif ($r['posicion'] == 2): ?>
                        <span class="badge badge-posicion-2">🥈 2</span>
                    <?php elseif ($r['posicion'] == 3): ?>
                        <span class="badge badge-posicion-3">🥉 3</span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?= $r['posicion'] ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge bg-secondary me-1"><?= htmlspecialchars($r['codigo']) ?></span>
                    <?= htmlspecialchars($r['piloto']) ?>
                </td>
                <td style="color:var(--muted)"><?= htmlspecialchars($r['escuderia']) ?></td>
                <td><?= htmlspecialchars($r['nombre_gp']) ?></td>
                <td><code><?= htmlspecialchars($r['tiempo_total'] ?? '-') ?></code></td>
                <td class="text-end fw-bold <?= $r['puntos'] > 0 ? 'text-warning' : 'text-muted' ?>">
                    <?= $r['puntos'] ?>
                </td>
                <td><?= $r['vueltas'] ?></td>
                <td>
                    <?php $badgeEstado = match($r['estado_carrera']) {
                        'Terminó'        => 'bg-success',
                        'Abandono'       => 'bg-danger',
                        'No clasificado' => 'bg-secondary',
                        'Descalificado'  => 'bg-warning text-dark',
                        default          => 'bg-secondary',
                    }; ?>
                    <span class="badge <?= $badgeEstado ?>"><?= $r['estado_carrera'] ?></span>
                </td>
                <td><?= $r['pit_stops'] ?></td>
                <?php if (es_admin()): ?>
                    <td>
                    <a href="resultados.php?editar=<?= $r['id_resultado'] ?>&tab=finalizadas<?= $filtroCarrera ? "&carrera=$filtroCarrera" : '' ?>"
                       class="btn btn-sm btn-outline-warning me-1">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <a href="resultados.php?borrar=<?= $r['id_resultado'] ?>&tab=finalizadas"
                       class="btn btn-sm btn-outline-danger btn-borrar">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                </td>
                    <?php else: ?>
                    <td><span class="badge bg-secondary">Solo lectura</span></td>
                    <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($resultados)): ?>
                <tr><td colspan="10" class="text-center text-muted py-4">
                    No hay resultados registrados aún.
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ============================================================ -->
<!-- PESTAÑA PENDIENTES                                           -->
<!-- ============================================================ -->

<div class="row g-3">

    <!-- Lista de carreras pendientes -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-calendar-event me-1"></i> Carreras sin resultados
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                <?php foreach ($carrerasPendientes as $cp): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center"
                        style="background:<?= $filtroCarreraPendiente == $cp['id_carrera'] ? '#2a2d3a' : 'transparent' ?>;
                               border-color:#2e3240; color:#e8eaf0;">
                        <div>
                            <a href="resultados.php?tab=pendientes&carrera_p=<?= $cp['id_carrera'] ?>"
                               class="text-decoration-none" style="color:inherit">
                                <span class="fw-semibold"><?= htmlspecialchars($cp['nombre_gp']) ?></span>
                            </a>
                            <br>
                            <small style="color:var(--muted)">
                                📍 <?= htmlspecialchars($cp['circuito']) ?>
                                · <?= date('d M Y', strtotime($cp['fecha'])) ?>
                            </small>
                        </div>
                        <span class="badge <?= $cp['estado'] === 'En curso' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $cp['estado'] ?>
                        </span>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($carrerasPendientes)): ?>
                    <li class="list-group-item text-muted" style="background:transparent; border-color:#2e3240">
                        Todas las carreras tienen resultados.
                    </li>
                <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Pilotos de la carrera seleccionada -->
    <div class="col-md-8">
        <?php if ($filtroCarreraPendiente > 0): ?>
            <?php
            // Buscar datos de la carrera seleccionada
            $stmtCP = $conexion->prepare(
                "SELECT c.nombre_gp, c.fecha, ci.nombre AS circuito, ci.pais
                 FROM carreras c JOIN circuitos ci ON c.id_circuito = ci.id_circuito
                 WHERE c.id_carrera = :id"
            );
            $stmtCP->execute([':id' => $filtroCarreraPendiente]);
            $carreraSeleccionada = $stmtCP->fetch();
            ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-flag-fill me-1" style="color:var(--rojo)"></i>
                        <?= htmlspecialchars($carreraSeleccionada['nombre_gp']) ?>
                        <small style="color:var(--muted)">
                            — <?= htmlspecialchars($carreraSeleccionada['circuito']) ?>,
                            <?= htmlspecialchars($carreraSeleccionada['pais']) ?>
                            · <?= date('d/m/Y', strtotime($carreraSeleccionada['fecha'])) ?>
                        </small>
                    </span>
                    <span class="badge bg-secondary"><?= count($pilotosPendientes) ?> pilotos sin resultado</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-dark-f1 mb-0">
                        <thead>
                            <tr>
                                <th>Piloto</th>
                                <th>Escudería</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pilotosPendientes as $p): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary me-1"><?= htmlspecialchars($p['codigo']) ?></span>
                                <?= htmlspecialchars($p['nombre']) ?>
                            </td>
                            <td style="color:var(--muted)"><?= htmlspecialchars($p['escuderia']) ?></td>
                            <td><span class="badge bg-warning text-dark">⏳ Pendiente</span></td>
                            <td>
                                <!-- Abre el modal preseleccionando esta carrera y piloto -->
                                <button class="btn btn-sm btn-f1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalResultado"
                                        data-carrera="<?= $filtroCarreraPendiente ?>"
                                        data-piloto="<?= $p['id_piloto'] ?>"
                                        onclick="preseleccionarModal(this)">
                                    <i class="bi bi-plus-fill"></i> Añadir
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pilotosPendientes)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">
                                Todos los pilotos tienen resultado en este GP.
                            </td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5" style="color:var(--muted)">
                    <i class="bi bi-arrow-left-circle fs-1 mb-3 d-block"></i>
                    Selecciona una carrera de la lista para ver los pilotos pendientes de resultado.
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
<?php endif; ?>

<!-- Modal añadir / editar resultado -->
<div class="modal fade <?= $esEdicion ? 'show' : '' ?>"
     id="modalResultado" tabindex="-1"
     style="<?= $esEdicion ? 'display:block;' : '' ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:#1a1a1a; color:#f0f0f0; border:1px solid #e8002d;">
            <div class="modal-header" style="border-bottom:1px solid #333;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-trophy-fill me-1"></i>
                    <?= $esEdicion ? 'Editar Resultado' : 'Nuevo Resultado' ?>
                </h5>
                <a href="resultados.php?tab=<?= $tab ?>" class="btn-close btn-close-white"></a>
            </div>
            <div class="modal-body">
                <form id="formResultado" action="resultados.php" method="POST"
                      onsubmit="return validarFormulario('formResultado')">
                    <input type="hidden" name="accion"       value="<?= $esEdicion ? 'actualizar' : 'insertar' ?>">
                    <input type="hidden" name="id_resultado" value="<?= $d['id_resultado'] ?>">

                    <div class="row g-3">
                        <!-- SELECT carrera -->
                        <div class="col-md-6">
                            <label class="form-label">Gran Premio *</label>
                            <select name="id_carrera" id="sel_carrera" class="form-select" data-requerido="true">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($carreras as $c): ?>
                                <option value="<?= $c['id_carrera'] ?>"
                                    <?= ($d['id_carrera'] == $c['id_carrera']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre_gp']) ?>
                                    (<?= date('d/m/Y', strtotime($c['fecha'])) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- SELECT piloto -->
                        <div class="col-md-6">
                            <label class="form-label">Piloto *</label>
                            <select name="id_piloto" id="sel_piloto" class="form-select" data-requerido="true">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($pilotos as $p): ?>
                                <option value="<?= $p['id_piloto'] ?>"
                                    <?= ($d['id_piloto'] == $p['id_piloto']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['codigo']) ?> – <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Posición *</label>
                            <input type="number" name="posicion" class="form-control"
                                   value="<?= $d['posicion'] ?>" min="1" max="22" data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Puntos</label>
                            <input type="number" name="puntos" class="form-control"
                                   value="<?= $d['puntos'] ?>" step="0.5" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vueltas</label>
                            <input type="number" name="vueltas" class="form-control"
                                   value="<?= $d['vueltas'] ?>" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pit Stops</label>
                            <input type="number" name="pit_stops" class="form-control"
                                   value="<?= $d['pit_stops'] ?>" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tiempo / Diferencia</label>
                            <input type="text" name="tiempo_total" class="form-control"
                                   value="<?= htmlspecialchars($d['tiempo_total']) ?>"
                                   placeholder="1:31:23.456 o +4.234">
                        </div>
                        <!-- RADIO estado -->
                        <div class="col-md-6">
                            <label class="form-label d-block">Estado del piloto</label>
                            <?php foreach (['Terminó','Abandono','No clasificado','Descalificado'] as $est): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="estado_carrera"
                                       id="est<?= str_replace(' ','',$est) ?>" value="<?= $est ?>"
                                       <?= ($d['estado_carrera'] === $est) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="est<?= str_replace(' ','',$est) ?>">
                                    <?= $est ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <?php if ($esEdicion): ?>
                            <button type="button" class="btn btn-f1"
                                    onclick="confirmarEdicion(event, 'formResultado')">
                                <i class="bi bi-save-fill me-1"></i> Guardar
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-f1">
                                <i class="bi bi-plus-circle-fill me-1"></i> Añadir
                            </button>
                        <?php endif; ?>
                        <a href="resultados.php?tab=<?= $tab ?>" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($esEdicion): ?><div class="modal-backdrop fade show"></div><?php endif; ?>

<script>
// Cuando se hace clic en "+ Añadir" desde la pestaña pendientes,
// preselecciono la carrera y el piloto en el modal
function preseleccionarModal(btn) {
    var idCarrera = btn.getAttribute('data-carrera');
    var idPiloto  = btn.getAttribute('data-piloto');
    if (idCarrera) document.getElementById('sel_carrera').value = idCarrera;
    if (idPiloto)  document.getElementById('sel_piloto').value  = idPiloto;
}
</script>

<?php include 'includes/footer.php'; ?>
