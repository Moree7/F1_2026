<?php
// CRUD de escuderías
require_once 'config/auth.php';
require_once 'config/config.php';

$pageTitle = 'Escuderías';
$msgOk = $msgError = '';

// Nueva escudería
// Solo el admin puede modificar datos
if (isset($_POST['accion']) || isset($_GET['borrar']) || isset($_GET['editar'])) {
    solo_admin();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    $stmt = $conexion->prepare(
        "INSERT INTO escuderias (nombre, nacionalidad, motor, director, temporadas_f1, activa)
         VALUES (:nombre, :nacionalidad, :motor, :director, :temporadas_f1, :activa)"
    );
    $stmt->execute([
        ':nombre'        => trim($_POST['nombre']),
        ':nacionalidad'  => trim($_POST['nacionalidad']),
        ':motor'         => trim($_POST['motor']),
        ':director'      => trim($_POST['director']),
        ':temporadas_f1' => (int)$_POST['temporadas_f1'],
        ':activa'        => $_POST['activa'],
    ]);
    $msgOk = 'Escudería añadida correctamente.';
}

// Actualizar escudería existente
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $stmt = $conexion->prepare(
        "UPDATE escuderias SET nombre=:nombre, nacionalidad=:nacionalidad, motor=:motor,
         director=:director, temporadas_f1=:temporadas_f1, activa=:activa
         WHERE id_escuderia=:id"
    );
    $stmt->execute([
        ':nombre'        => trim($_POST['nombre']),
        ':nacionalidad'  => trim($_POST['nacionalidad']),
        ':motor'         => trim($_POST['motor']),
        ':director'      => trim($_POST['director']),
        ':temporadas_f1' => (int)$_POST['temporadas_f1'],
        ':activa'        => $_POST['activa'],
        ':id'            => (int)$_POST['id_escuderia'],
    ]);
    $msgOk = 'Escudería actualizada correctamente.';
}

// Borrar escudería
if (isset($_GET['borrar'])) {
    $stmt = $conexion->prepare("DELETE FROM escuderias WHERE id_escuderia = :id");
    $stmt->execute([':id' => (int)$_GET['borrar']]);
    $msgOk = 'Escudería eliminada.';
}

// Cargo la escudería a editar si viene el parámetro
$escuderiaEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $conexion->prepare("SELECT * FROM escuderias WHERE id_escuderia = :id");
    $stmt->execute([':id' => (int)$_GET['editar']]);
    $escuderiaEditar = $stmt->fetch();
}

$escuderias = $conexion->query("SELECT * FROM escuderias ORDER BY nombre")->fetchAll();

include 'includes/header.php';

$esEdicion = ($escuderiaEditar !== null);
$d = $escuderiaEditar ?? [
    'id_escuderia'=>'','nombre'=>'','nacionalidad'=>'','motor'=>'',
    'director'=>'','temporadas_f1'=>0,'activa'=>'Si'
];
?>

<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="fw-bold"><i class="bi bi-shield-fill me-2"></i>Escuderías</h2>
    </div>
    <?php if (es_admin()): ?>
        <div class="col-auto">
        <button class="btn btn-f1" data-bs-toggle="modal" data-bs-target="#modalEscuderia">
            <i class="bi bi-plus-circle-fill me-1"></i> Nueva Escudería
        </button>
    </div>
        <?php endif; ?>
</div>

<?php if ($msgOk):   ?><div class="alert alert-success"><?= htmlspecialchars($msgOk) ?></div><?php endif; ?>
<?php if ($msgError): ?><div class="alert alert-danger"><?= htmlspecialchars($msgError) ?></div><?php endif; ?>

<div class="row g-3">
<?php foreach ($escuderias as $e): ?>
    <div class="col-md-6 col-lg-4">
        <div class="esc-card h-100">
            <div class="esc-header">
                <h5 class="esc-nombre"><?= htmlspecialchars($e['nombre']) ?></h5>
                <span class="<?= $e['activa']==='Si' ? 'badge-activa' : 'badge-inactiva' ?>">
                    <?= $e['activa']==='Si' ? 'Activa' : 'Inactiva' ?>
                </span>
            </div>
            <div class="esc-body">
                <div class="esc-row">
                    <i class="bi bi-globe2"></i>
                    <span><?= htmlspecialchars($e['nacionalidad']) ?></span>
                </div>
                <div class="esc-row">
                    <i class="bi bi-gear-fill"></i>
                    <span>Motor <?= htmlspecialchars($e['motor']) ?></span>
                </div>
                <div class="esc-row">
                    <i class="bi bi-person-fill"></i>
                    <span><?= htmlspecialchars($e['director']) ?></span>
                </div>
                <div class="esc-row">
                    <i class="bi bi-calendar3"></i>
                    <span><?= $e['temporadas_f1'] ?> temporadas en F1</span>
                </div>
            </div>
            <div class="esc-footer">
                <?php if (es_admin()): ?>
                <a href="escuderias.php?editar=<?= $e['id_escuderia'] ?>"
                   class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil-fill"></i> Editar
                </a>
                <a href="escuderias.php?borrar=<?= $e['id_escuderia'] ?>"
                   class="btn btn-sm btn-outline-danger btn-borrar">
                    <i class="bi bi-trash-fill"></i> Borrar
                </a>
                <?php else: ?>
                <span class="badge bg-secondary">Solo lectura</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- MODAL -->
<div class="modal fade <?= $esEdicion ? 'show' : '' ?>"
     id="modalEscuderia" tabindex="-1"
     style="<?= $esEdicion ? 'display:block;' : '' ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:#1a1a1a; color:#f0f0f0; border:1px solid #e8002d;">
            <div class="modal-header" style="border-bottom:1px solid #333;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-shield-fill me-1"></i>
                    <?= $esEdicion ? 'Editar Escudería' : 'Nueva Escudería' ?>
                </h5>
                <a href="escuderias.php" class="btn-close btn-close-white"></a>
            </div>
            <div class="modal-body">
                <form id="formEscuderia" action="escuderias.php" method="POST"
                      onsubmit="return validarFormulario('formEscuderia')">
                    <input type="hidden" name="accion"       value="<?= $esEdicion ? 'actualizar' : 'insertar' ?>">
                    <input type="hidden" name="id_escuderia" value="<?= $d['id_escuderia'] ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= htmlspecialchars($d['nombre']) ?>"
                                   data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nacionalidad *</label>
                            <input type="text" name="nacionalidad" class="form-control"
                                   value="<?= htmlspecialchars($d['nacionalidad']) ?>"
                                   data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <!-- Motor como SELECT -->
                            <label class="form-label">Motor *</label>
                            <select name="motor" class="form-select" data-requerido="true">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach (['Mercedes','Ferrari','Honda RBPT','Renault','Audi'] as $m): ?>
                                <option value="<?= $m ?>" <?= ($d['motor']===$m)?'selected':'' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Director *</label>
                            <input type="text" name="director" class="form-control"
                                   value="<?= htmlspecialchars($d['director']) ?>"
                                   data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Temporadas en F1</label>
                            <input type="number" name="temporadas_f1" class="form-control"
                                   value="<?= $d['temporadas_f1'] ?>" min="0">
                        </div>
                        <!-- Estado activa como RADIO -->
                        <div class="col-12">
                            <label class="form-label d-block">Estado *</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="activa"
                                       id="activaSi" value="Si"
                                       <?= ($d['activa']==='Si')?'checked':'' ?>>
                                <label class="form-check-label" for="activaSi">Activa</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="activa"
                                       id="activaNo" value="No"
                                       <?= ($d['activa']==='No')?'checked':'' ?>>
                                <label class="form-check-label" for="activaNo">Inactiva</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <?php if ($esEdicion): ?>
                            <button type="button" class="btn btn-f1"
                                    onclick="confirmarEdicion(event, 'formEscuderia')">
                                <i class="bi bi-save-fill me-1"></i> Guardar
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-f1">
                                <i class="bi bi-plus-circle-fill me-1"></i> Añadir
                            </button>
                        <?php endif; ?>
                        <a href="escuderias.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($esEdicion): ?><div class="modal-backdrop fade show"></div><?php endif; ?>

<?php include 'includes/footer.php'; ?>
