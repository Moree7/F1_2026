<?php
// CRUD de carreras 
require_once 'config/auth.php';
require_once 'config/config.php';

$pageTitle = 'Carreras';
$msgOk = $msgError = '';

// Listas que necesito para los desplegables del formulario
$circuitos = $conexion->query("SELECT id_circuito, nombre, pais FROM circuitos ORDER BY nombre")->fetchAll();
$pilotos   = $conexion->query("SELECT id_piloto, nombre, codigo FROM pilotos ORDER BY nombre")->fetchAll();

// Insertar carrera nueva
// Solo el admin puede modificar datos
if (isset($_POST['accion']) || isset($_GET['borrar']) || isset($_GET['editar'])) {
    solo_admin();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    $stmt = $conexion->prepare(
        "INSERT INTO carreras (nombre_gp, id_circuito, fecha, num_vueltas, estado, condicion_pista, vuelta_rapida, id_piloto_vr)
         VALUES (:nombre_gp, :id_circuito, :fecha, :num_vueltas, :estado, :condicion_pista, :vuelta_rapida, :id_piloto_vr)"
    );
    $stmt->execute([
        ':nombre_gp'       => trim($_POST['nombre_gp']),
        ':id_circuito'     => (int)$_POST['id_circuito'],
        ':fecha'           => $_POST['fecha'],
        ':num_vueltas'     => (int)$_POST['num_vueltas'],
        ':estado'          => $_POST['estado'],
        ':condicion_pista' => $_POST['condicion_pista'],
        ':vuelta_rapida'   => trim($_POST['vuelta_rapida']),
        ':id_piloto_vr'    => !empty($_POST['id_piloto_vr']) ? (int)$_POST['id_piloto_vr'] : null,
    ]);
    $msgOk = 'Carrera añadida correctamente.';
}

// Actualizar carrera
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $stmt = $conexion->prepare(
        "UPDATE carreras SET nombre_gp=:nombre_gp, id_circuito=:id_circuito, fecha=:fecha,
         num_vueltas=:num_vueltas, estado=:estado, condicion_pista=:condicion_pista,
         vuelta_rapida=:vuelta_rapida, id_piloto_vr=:id_piloto_vr
         WHERE id_carrera=:id"
    );
    $stmt->execute([
        ':nombre_gp'       => trim($_POST['nombre_gp']),
        ':id_circuito'     => (int)$_POST['id_circuito'],
        ':fecha'           => $_POST['fecha'],
        ':num_vueltas'     => (int)$_POST['num_vueltas'],
        ':estado'          => $_POST['estado'],
        ':condicion_pista' => $_POST['condicion_pista'],
        ':vuelta_rapida'   => trim($_POST['vuelta_rapida']),
        ':id_piloto_vr'    => !empty($_POST['id_piloto_vr']) ? (int)$_POST['id_piloto_vr'] : null,
        ':id'              => (int)$_POST['id_carrera'],
    ]);
    $msgOk = 'Carrera actualizada correctamente.';
}

// Borrar carrera
if (isset($_GET['borrar'])) {
    $stmt = $conexion->prepare("DELETE FROM carreras WHERE id_carrera = :id");
    $stmt->execute([':id' => (int)$_GET['borrar']]);
    $msgOk = 'Carrera eliminada.';
}

// Si viene ?editar cargo esa carrera
$carreraEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $conexion->prepare("SELECT * FROM carreras WHERE id_carrera = :id");
    $stmt->execute([':id' => (int)$_GET['editar']]);
    $carreraEditar = $stmt->fetch();
}

// Saco todas las carreras con JOIN al circuito para mostrar nombre y país
$carreras = $conexion->query(
    "SELECT c.*, ci.nombre AS circuito, ci.pais
     FROM carreras c
     JOIN circuitos ci ON c.id_circuito = ci.id_circuito
     ORDER BY c.fecha ASC"
)->fetchAll();

include 'includes/header.php';

$esEdicion = ($carreraEditar !== null);
$d = $carreraEditar ?? [
    'id_carrera'=>'','nombre_gp'=>'','id_circuito'=>'','fecha'=>'',
    'num_vueltas'=>'','estado'=>'Programada','condicion_pista'=>'Desconocido',
    'vuelta_rapida'=>'','id_piloto_vr'=>''
];

// Asigno icono y color de badge según el estado de la carrera
$estadoIconos = [
    'Programada' => ['🗓️', 'bg-secondary'],
    'En curso'   => ['🟢', 'bg-success'],
    'Finalizada' => ['🏁', 'bg-primary'],
    'Cancelada'  => ['❌', 'bg-danger'],
];
?>

<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="fw-bold"><i class="bi bi-flag-fill me-2"></i>Carreras – Temporada 2026</h2>
    </div>
    <?php if (es_admin()): ?>
        <div class="col-auto">
        <button class="btn btn-f1" data-bs-toggle="modal" data-bs-target="#modalCarrera">
            <i class="bi bi-plus-circle-fill me-1"></i> Nueva Carrera
        </button>
    </div>
        <?php endif; ?>
</div>

<?php if ($msgOk): ?><div class="alert alert-success"><?= htmlspecialchars($msgOk) ?></div><?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-dark-f1 table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Gran Premio</th>
                    <th>Circuito</th>
                    <th>País</th>
                    <th>Fecha</th>
                    <th>Vueltas</th>
                    <th>Estado</th>
                    <th>Pista</th>
                    <th>V.Rápida</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($carreras as $c): ?>
            <?php [$icon, $badgeClass] = $estadoIconos[$c['estado']] ?? ['❓','bg-secondary']; ?>
            <tr>
                <td><?= $c['id_carrera'] ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($c['nombre_gp']) ?></td>
                <td><?= htmlspecialchars($c['circuito']) ?></td>
                <td><?= htmlspecialchars($c['pais']) ?></td>
                <td><?= date('d/m/Y', strtotime($c['fecha'])) ?></td>
                <td><?= $c['num_vueltas'] ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= $icon ?> <?= $c['estado'] ?></span></td>
                <td>
                    <?php
                    $pistaClases = [
                        'Seco'        => 'pista-seco',
                        'Mojado'      => 'pista-mojado',
                        'Mixto'       => 'pista-mixto',
                        'Desconocido' => 'pista-desc',
                    ];
                    $cls = $pistaClases[$c['condicion_pista']] ?? 'pista-desc';
                    ?>
                    <span class="<?= $cls ?> fw-semibold"><?= $c['condicion_pista'] ?></span>
                </td>
                <td><code><?= htmlspecialchars($c['vuelta_rapida'] ?? '-') ?></code></td>
                <?php if (es_admin()): ?>
                    <td>
                    <a href="carreras.php?editar=<?= $c['id_carrera'] ?>"
                       class="btn btn-sm btn-outline-warning me-1">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <a href="carreras.php?borrar=<?= $c['id_carrera'] ?>"
                       class="btn btn-sm btn-outline-danger btn-borrar">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                </td>
                    <?php else: ?>
                    <td><span class="badge bg-secondary">Solo lectura</span></td>
                    <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- MODAL CARRERA -->
<div class="modal fade <?= $esEdicion ? 'show' : '' ?>"
     id="modalCarrera" tabindex="-1"
     style="<?= $esEdicion ? 'display:block;' : '' ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:#1a1a1a; color:#f0f0f0; border:1px solid #e8002d;">
            <div class="modal-header" style="border-bottom:1px solid #333;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-flag-fill me-1"></i>
                    <?= $esEdicion ? 'Editar Carrera' : 'Nueva Carrera' ?>
                </h5>
                <a href="carreras.php" class="btn-close btn-close-white"></a>
            </div>
            <div class="modal-body">
                <form id="formCarrera" action="carreras.php" method="POST"
                      onsubmit="return validarFormulario('formCarrera')">
                    <input type="hidden" name="accion"     value="<?= $esEdicion ? 'actualizar' : 'insertar' ?>">
                    <input type="hidden" name="id_carrera" value="<?= $d['id_carrera'] ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre del Gran Premio *</label>
                            <input type="text" name="nombre_gp" class="form-control"
                                   value="<?= htmlspecialchars($d['nombre_gp']) ?>"
                                   data-requerido="true"
                                   placeholder="Gran Premio de España 2026">
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- Circuito: desplegable con todos los circuitos -->
                        <div class="col-md-6">
                            <label class="form-label">Circuito *</label>
                            <select name="id_circuito" class="form-select" data-requerido="true">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($circuitos as $ci): ?>
                                <option value="<?= $ci['id_circuito'] ?>"
                                    <?= ($d['id_circuito']==$ci['id_circuito'])?'selected':'' ?>>
                                    <?= htmlspecialchars($ci['nombre']) ?> (<?= $ci['pais'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha *</label>
                            <input type="date" name="fecha" class="form-control"
                                   value="<?= $d['fecha'] ?>" data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vueltas *</label>
                            <input type="number" name="num_vueltas" class="form-control"
                                   value="<?= $d['num_vueltas'] ?>"
                                   min="1" data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- Estado: select con los posibles estados -->
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <?php foreach (['Programada','En curso','Finalizada','Cancelada'] as $est): ?>
                                <option value="<?= $est ?>" <?= ($d['estado']===$est)?'selected':'' ?>><?= $est ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Condición de pista: radio porque son pocos valores -->
                        <div class="col-md-6">
                            <label class="form-label d-block">Condición de la pista</label>
                            <?php foreach (['Seco','Mojado','Mixto','Desconocido'] as $cp): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="condicion_pista"
                                       id="pista<?= $cp ?>" value="<?= $cp ?>"
                                       <?= ($d['condicion_pista']===$cp)?'checked':'' ?>>
                                <label class="form-check-label" for="pista<?= $cp ?>"><?= $cp ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vuelta rápida (tiempo)</label>
                            <input type="text" name="vuelta_rapida" class="form-control"
                                   value="<?= htmlspecialchars($d['vuelta_rapida']) ?>"
                                   placeholder="1:16.330">
                        </div>
                        <!-- Piloto que hizo la vuelta rápida: puede estar vacío -->
                        <div class="col-md-6">
                            <label class="form-label">Piloto vuelta rápida</label>
                            <select name="id_piloto_vr" class="form-select">
                                <option value="">-- Ninguno --</option>
                                <?php foreach ($pilotos as $p): ?>
                                <option value="<?= $p['id_piloto'] ?>"
                                    <?= ($d['id_piloto_vr']==$p['id_piloto'])?'selected':'' ?>>
                                    <?= htmlspecialchars($p['codigo']) ?> – <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <?php if ($esEdicion): ?>
                            <button type="button" class="btn btn-f1"
                                    onclick="confirmarEdicion(event, 'formCarrera')">
                                <i class="bi bi-save-fill me-1"></i> Guardar
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-f1">
                                <i class="bi bi-plus-circle-fill me-1"></i> Añadir
                            </button>
                        <?php endif; ?>
                        <a href="carreras.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($esEdicion): ?><div class="modal-backdrop fade show"></div><?php endif; ?>

<?php include 'includes/footer.php'; ?>
