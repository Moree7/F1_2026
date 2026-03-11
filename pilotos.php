<?php
// CRUD de pilotos de la temporada 2026
require_once 'config/auth.php';
require_once 'config/config.php';

$pageTitle = 'Pilotos';
$msgOk     = '';
$msgError  = '';

// Necesito las escuderías para el desplegable del formulario
$escuderias = $conexion->query("SELECT id_escuderia, nombre FROM escuderias ORDER BY nombre")->fetchAll();

// Inserto un piloto nuevo si viene el POST
// Solo el admin puede modificar datos
if (isset($_POST['accion']) || isset($_GET['borrar']) || isset($_GET['editar'])) {
    solo_admin();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    $stmt = $conexion->prepare(
        "INSERT INTO pilotos (nombre, codigo, numero, nacionalidad, fecha_nac,
         id_escuderia, campeonatos, victorias, poles)
         VALUES (:nombre, :codigo, :numero, :nacionalidad, :fecha_nac,
         :id_escuderia, :campeonatos, :victorias, :poles)"
    );
    $stmt->execute([
        ':nombre'       => trim($_POST['nombre']),
        ':codigo'       => strtoupper(trim($_POST['codigo'])),
        ':numero'       => (int)$_POST['numero'],
        ':nacionalidad' => trim($_POST['nacionalidad']),
        ':fecha_nac'    => $_POST['fecha_nac'],
        ':id_escuderia' => (int)$_POST['id_escuderia'],
        ':campeonatos'  => (int)$_POST['campeonatos'],
        ':victorias'    => (int)$_POST['victorias'],
        ':poles'        => (int)$_POST['poles'],
    ]);
    $msgOk = 'Piloto añadido correctamente.';
}

// Actualizo los datos si está editando
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $stmt = $conexion->prepare(
        "UPDATE pilotos SET nombre=:nombre, codigo=:codigo, numero=:numero,
         nacionalidad=:nacionalidad, fecha_nac=:fecha_nac, id_escuderia=:id_escuderia,
         campeonatos=:campeonatos, victorias=:victorias, poles=:poles
         WHERE id_piloto=:id"
    );
    $stmt->execute([
        ':nombre'       => trim($_POST['nombre']),
        ':codigo'       => strtoupper(trim($_POST['codigo'])),
        ':numero'       => (int)$_POST['numero'],
        ':nacionalidad' => trim($_POST['nacionalidad']),
        ':fecha_nac'    => $_POST['fecha_nac'],
        ':id_escuderia' => (int)$_POST['id_escuderia'],
        ':campeonatos'  => (int)$_POST['campeonatos'],
        ':victorias'    => (int)$_POST['victorias'],
        ':poles'        => (int)$_POST['poles'],
        ':id'           => (int)$_POST['id_piloto'],
    ]);
    $msgOk = 'Piloto actualizado correctamente.';
}

// Borro el piloto que me pasan por GET
if (isset($_GET['borrar'])) {
    $stmt = $conexion->prepare("DELETE FROM pilotos WHERE id_piloto = :id");
    $stmt->execute([':id' => (int)$_GET['borrar']]);
    $msgOk = 'Piloto eliminado correctamente.';
}

// Si viene ?editar=X cargo ese piloto para rellenar el formulario
$pilotoEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $conexion->prepare("SELECT * FROM pilotos WHERE id_piloto = :id");
    $stmt->execute([':id' => (int)$_GET['editar']]);
    $pilotoEditar = $stmt->fetch();
}

// Saco todos los pilotos con JOIN para mostrar el nombre de la escudería
$pilotos = $conexion->query(
    "SELECT p.*, e.nombre AS escuderia
     FROM pilotos p
     JOIN escuderias e ON p.id_escuderia = e.id_escuderia
     ORDER BY e.nombre, p.nombre"
)->fetchAll();

include 'includes/header.php';
?>

<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="fw-bold"><i class="bi bi-person-fill me-2"></i>Pilotos</h2>
    </div>
    <?php if (es_admin()): ?>
        <div class="col-auto">
        <button class="btn btn-f1" data-bs-toggle="modal" data-bs-target="#modalPiloto">
            <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Piloto
        </button>
    </div>
        <?php endif; ?>
</div>

<?php if ($msgOk):   ?><div class="alert alert-success"><?= htmlspecialchars($msgOk) ?></div><?php endif; ?>
<?php if ($msgError): ?><div class="alert alert-danger"><?= htmlspecialchars($msgError) ?></div><?php endif; ?>

<!-- TABLA DE PILOTOS -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-dark-f1 table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Nº</th>
                    <th>Nacionalidad</th>
                    <th>Escudería</th>
                    <th>🏆</th>
                    <th>Victorias</th>
                    <th>Poles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pilotos as $p): ?>
            <tr>
                <td><?= $p['id_piloto'] ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($p['codigo']) ?></span></td>
                <td class="fw-semibold"><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= $p['numero'] ?></td>
                <td><?= htmlspecialchars($p['nacionalidad']) ?></td>
                <td><?= htmlspecialchars($p['escuderia']) ?></td>
                <td><?= $p['campeonatos'] > 0 ? str_repeat('🏆', $p['campeonatos']) : '-' ?></td>
                <td><?= $p['victorias'] ?></td>
                <td><?= $p['poles'] ?></td>
                <?php if (es_admin()): ?>
                    <td>
                    <a href="pilotos.php?editar=<?= $p['id_piloto'] ?>"
                       class="btn btn-sm btn-outline-warning me-1"
                       title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <a href="pilotos.php?borrar=<?= $p['id_piloto'] ?>"
                       class="btn btn-sm btn-outline-danger btn-borrar"
                       title="Eliminar">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                </td>
                    <?php else: ?>
                    <td><span class="badge bg-secondary">Solo lectura</span></td>
                    <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($pilotos)): ?>
                <tr><td colspan="11" class="text-center text-muted py-4">No hay pilotos registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- MODAL AÑADIR / EDITAR PILOTO -->
<?php
// Decido si el modal es de nuevo o de edición
$esEdicion   = ($pilotoEditar !== null);
$modalTitulo = $esEdicion ? 'Editar Piloto' : 'Nuevo Piloto';
$accion      = $esEdicion ? 'actualizar' : 'insertar';
$d           = $pilotoEditar ?? [
    'id_piloto'=>'','nombre'=>'','codigo'=>'','numero'=>'',
    'nacionalidad'=>'','fecha_nac'=>'','id_escuderia'=>'',
    'campeonatos'=>0,'victorias'=>0,'poles'=>0
];
?>

<div class="modal fade <?= $esEdicion ? 'show' : '' ?>"
     id="modalPiloto"
     tabindex="-1"
     style="<?= $esEdicion ? 'display:block;' : '' ?>"
     aria-modal="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:#1a1a1a; color:#f0f0f0; border:1px solid #e8002d;">

            <div class="modal-header" style="border-bottom:1px solid #333;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-fill me-1"></i> <?= $modalTitulo ?>
                </h5>
                <a href="pilotos.php" class="btn-close btn-close-white"></a>
            </div>

            <div class="modal-body">
                <form id="formPiloto" action="pilotos.php" method="POST"
                      onsubmit="return validarFormulario('formPiloto')">
                    <input type="hidden" name="accion"    value="<?= $accion ?>">
                    <input type="hidden" name="id_piloto" value="<?= $d['id_piloto'] ?>">

                    <div class="row g-3">
                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= htmlspecialchars($d['nombre']) ?>"
                                   data-requerido="true" placeholder="Max Verstappen">
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- Código -->
                        <div class="col-md-3">
                            <label class="form-label">Código (3 letras) *</label>
                            <input type="text" name="codigo" class="form-control text-uppercase"
                                   value="<?= htmlspecialchars($d['codigo']) ?>"
                                   maxlength="3" data-requerido="true" placeholder="VER">
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- Número -->
                        <div class="col-md-3">
                            <label class="form-label">Número *</label>
                            <input type="number" name="numero" class="form-control"
                                   value="<?= $d['numero'] ?>"
                                   min="1" max="99" data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- Nacionalidad -->
                        <div class="col-md-4">
                            <label class="form-label">Nacionalidad *</label>
                            <input type="text" name="nacionalidad" class="form-control"
                                   value="<?= htmlspecialchars($d['nacionalidad']) ?>"
                                   data-requerido="true" placeholder="Holandés">
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- Fecha de nacimiento -->
                        <div class="col-md-4">
                            <label class="form-label">Fecha de nacimiento *</label>
                            <input type="date" name="fecha_nac" class="form-control"
                                   value="<?= $d['fecha_nac'] ?>"
                                   data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- Escudería (SELECT) -->
                        <div class="col-md-4">
                            <label class="form-label">Escudería *</label>
                            <select name="id_escuderia" class="form-select" data-requerido="true">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($escuderias as $esc): ?>
                                <option value="<?= $esc['id_escuderia'] ?>"
                                    <?= ($d['id_escuderia'] == $esc['id_escuderia']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($esc['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <!-- Campeonatos (radiobuttons) -->
                        <div class="col-md-4">
                            <label class="form-label">Campeonatos WDC</label>
                            <div class="d-flex flex-wrap gap-3 mt-1">
                                <?php for ($c = 0; $c <= 7; $c++): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                           name="campeonatos" id="camp_<?= $c ?>"
                                           value="<?= $c ?>"
                                           <?= ((int)$d['campeonatos'] === $c) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="camp_<?= $c ?>">
                                        <?= $c === 0 ? '0' : str_repeat('🏆', $c) ?>
                                    </label>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <!-- Victorias -->
                        <div class="col-md-4">
                            <label class="form-label">Victorias</label>
                            <input type="number" name="victorias" class="form-control"
                                   value="<?= $d['victorias'] ?>" min="0">
                        </div>
                        <!-- Poles -->
                        <div class="col-md-4">
                            <label class="form-label">Poles</label>
                            <input type="number" name="poles" class="form-control"
                                   value="<?= $d['poles'] ?>" min="0">
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <?php if ($esEdicion): ?>
                            <button type="button" class="btn btn-f1"
                                    onclick="confirmarEdicion(event, 'formPiloto')">
                                <i class="bi bi-save-fill me-1"></i> Guardar cambios
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-f1">
                                <i class="bi bi-plus-circle-fill me-1"></i> Añadir piloto
                            </button>
                        <?php endif; ?>
                        <a href="pilotos.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($esEdicion): ?>
<div class="modal-backdrop fade show"></div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
