<?php
// CRUD de neumáticos Pirelli 2026
require_once 'config/auth.php';
require_once 'config/config.php';

$pageTitle = 'Neumáticos';
$msgOk = $msgError = '';

// Añadir neumático nuevo
// Solo el admin puede modificar datos
if (isset($_POST['accion']) || isset($_GET['borrar']) || isset($_GET['editar'])) {
    solo_admin();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    $stmt = $conexion->prepare(
        "INSERT INTO neumaticos (compuesto, codigo, banda_rodadura, condicion, adherencia, durabilidad)
         VALUES (:compuesto, :codigo, :banda_rodadura, :condicion, :adherencia, :durabilidad)"
    );
    $stmt->execute([
        ':compuesto'      => trim($_POST['compuesto']),
        ':codigo'         => strtoupper(trim($_POST['codigo'])),
        ':banda_rodadura' => $_POST['banda_rodadura'],
        ':condicion'      => $_POST['condicion'],
        ':adherencia'     => $_POST['adherencia'],
        ':durabilidad'    => $_POST['durabilidad'],
    ]);
    $msgOk = 'Neumático añadido correctamente.';
}

// Actualizar neumático
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $stmt = $conexion->prepare(
        "UPDATE neumaticos SET compuesto=:compuesto, codigo=:codigo, banda_rodadura=:banda_rodadura,
         condicion=:condicion, adherencia=:adherencia, durabilidad=:durabilidad
         WHERE id_neumatico=:id"
    );
    $stmt->execute([
        ':compuesto'      => trim($_POST['compuesto']),
        ':codigo'         => strtoupper(trim($_POST['codigo'])),
        ':banda_rodadura' => $_POST['banda_rodadura'],
        ':condicion'      => $_POST['condicion'],
        ':adherencia'     => $_POST['adherencia'],
        ':durabilidad'    => $_POST['durabilidad'],
        ':id'             => (int)$_POST['id_neumatico'],
    ]);
    $msgOk = 'Neumático actualizado correctamente.';
}

// Borrar neumático
if (isset($_GET['borrar'])) {
    $stmt = $conexion->prepare("DELETE FROM neumaticos WHERE id_neumatico = :id");
    $stmt->execute([':id' => (int)$_GET['borrar']]);
    $msgOk = 'Neumático eliminado.';
}

// Cargo el neumático a editar si viene el parámetro
$neuEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $conexion->prepare("SELECT * FROM neumaticos WHERE id_neumatico = :id");
    $stmt->execute([':id' => (int)$_GET['editar']]);
    $neuEditar = $stmt->fetch();
}

$neumaticos = $conexion->query("SELECT * FROM neumaticos ORDER BY id_neumatico")->fetchAll();

include 'includes/header.php';

$esEdicion = ($neuEditar !== null);
$d = $neuEditar ?? [
    'id_neumatico'=>'','compuesto'=>'','codigo'=>'',
    'banda_rodadura'=>'Liso','condicion'=>'Seco',
    'adherencia'=>'Media','durabilidad'=>'Media'
];
?>

<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="fw-bold">🏎️ Neumáticos – Temporada 2026</h2>
        <p class="text-muted">Compuestos Pirelli para la temporada 2026</p>
    </div>
    <?php if (es_admin()): ?>
        <div class="col-auto">
        <button class="btn btn-f1" data-bs-toggle="modal" data-bs-target="#modalNeu">
            <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Neumático
        </button>
    </div>
        <?php endif; ?>
</div>

<?php if ($msgOk):   ?><div class="alert alert-success"><?= htmlspecialchars($msgOk) ?></div><?php endif; ?>
<?php if ($msgError): ?><div class="alert alert-danger"><?= htmlspecialchars($msgError) ?></div><?php endif; ?>

<!-- NEUMÁTICOS -->
<?php
// Defino icono y clase CSS según el compuesto para las tarjetas
$neuInfo = [
    'Duro'        => ['clase' => 'neu-duro',        'icono' => 'H'],
    'Medio'       => ['clase' => 'neu-medio',       'icono' => 'M'],
    'Blando'      => ['clase' => 'neu-blando',      'icono' => 'S'],
    'Intermedio'  => ['clase' => 'neu-intermedio',  'icono' => 'I'],
    'De lluvia'   => ['clase' => 'neu-lluvia',      'icono' => 'W'],
];
$nivelColor = [
    'Alta'  => '#2fa84f',
    'Media' => '#e0a020',
    'Baja'  => '#cc3333',
    'N/A'   => '#555b6e',
];
?>
<div class="row g-3">
<?php foreach ($neumaticos as $n):
    $info = $neuInfo[$n['compuesto']] ?? ['clase'=>'bg-secondary','icono'=>$n['codigo']];
?>
    <div class="col-md-6 col-lg-4">
        <div class="neu-card">
            <div class="neu-top">
                <div class="neu-circulo <?= $info['clase'] ?>"><?= $info['icono'] ?></div>
                <div>
                    <div class="neu-nombre"><?= htmlspecialchars($n['compuesto']) ?></div>
                    <div class="neu-cond">
                        <?= $n['condicion']==='Seco' ? '☀️ Seco' : '🌧️ Mojado' ?>
                        &nbsp;·&nbsp; <?= $n['banda_rodadura'] ?>
                    </div>
                </div>
            </div>
            <div class="neu-stats">
                <div class="neu-stat">
                    <div class="ns-label">Adherencia</div>
                    <div class="ns-val" style="color:<?= $nivelColor[$n['adherencia']] ?? '#fff' ?>">
                        <?= $n['adherencia'] ?>
                    </div>
                </div>
                <div class="neu-stat">
                    <div class="ns-label">Durabilidad</div>
                    <div class="ns-val" style="color:<?= $nivelColor[$n['durabilidad']] ?? '#fff' ?>">
                        <?= $n['durabilidad'] ?>
                    </div>
                </div>
                <div class="neu-stat">
                    <div class="ns-label">Código</div>
                    <div class="ns-val" style="color:#fff;font-size:1.1rem;"><?= htmlspecialchars($n['codigo']) ?></div>
                </div>
            </div>
            <div class="neu-footer">
                <?php if (es_admin()): ?>
                <a href="neumaticos.php?editar=<?= $n['id_neumatico'] ?>" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil-fill"></i> Editar
                </a>
                <a href="neumaticos.php?borrar=<?= $n['id_neumatico'] ?>" class="btn btn-sm btn-outline-danger btn-borrar">
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

<!-- MODAL AÑADIR / EDITAR -->
<div class="modal fade <?= $esEdicion ? 'show' : '' ?>"
     id="modalNeu" tabindex="-1"
     style="<?= $esEdicion ? 'display:block;' : '' ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:#1a1a1a; color:#f0f0f0; border:1px solid #e8002d;">
            <div class="modal-header" style="border-bottom:1px solid #333;">
                <h5 class="modal-title fw-bold">
                    🏎️ <?= $esEdicion ? 'Editar Neumático' : 'Nuevo Neumático' ?>
                </h5>
                <a href="neumaticos.php" class="btn-close btn-close-white"></a>
            </div>
            <div class="modal-body">
                <form id="formNeu" action="neumaticos.php" method="POST"
                      onsubmit="return validarFormulario('formNeu')">
                    <input type="hidden" name="accion"       value="<?= $esEdicion ? 'actualizar' : 'insertar' ?>">
                    <input type="hidden" name="id_neumatico" value="<?= $d['id_neumatico'] ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del compuesto *</label>
                            <input type="text" name="compuesto" class="form-control"
                                   value="<?= htmlspecialchars($d['compuesto']) ?>"
                                   data-requerido="true" placeholder="Ej: Blando">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Código (1 letra) *</label>
                            <input type="text" name="codigo" class="form-control text-uppercase text-center fw-bold"
                                   value="<?= htmlspecialchars($d['codigo']) ?>"
                                   maxlength="1" data-requerido="true" placeholder="S">
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Banda: radio porque solo hay Liso o Canales -->
                        <div class="col-md-6">
                            <label class="form-label d-block">Banda de rodadura *</label>
                            <?php foreach (['Liso','Canales'] as $br): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="banda_rodadura"
                                       id="banda<?= $br ?>" value="<?= $br ?>"
                                       <?= ($d['banda_rodadura']===$br)?'checked':'' ?>>
                                <label class="form-check-label" for="banda<?= $br ?>"><?= $br ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Condición: radio Seco/Mojado -->
                        <div class="col-md-6">
                            <label class="form-label d-block">Condición de uso *</label>
                            <?php foreach (['Seco','Mojado'] as $cond): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="condicion"
                                       id="cond<?= $cond ?>" value="<?= $cond ?>"
                                       <?= ($d['condicion']===$cond)?'checked':'' ?>>
                                <label class="form-check-label" for="cond<?= $cond ?>">
                                    <?= $cond === 'Seco' ? '☀️ Seco' : '🌧️ Mojado' ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Adherencia y durabilidad: SELECT con niveles -->
                        <div class="col-md-6">
                            <label class="form-label">Adherencia</label>
                            <select name="adherencia" class="form-select">
                                <?php foreach (['Baja','Media','Alta','N/A'] as $adh): ?>
                                <option value="<?= $adh ?>" <?= ($d['adherencia']===$adh)?'selected':'' ?>><?= $adh ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Durabilidad -->
                        <div class="col-md-6">
                            <label class="form-label">Durabilidad</label>
                            <select name="durabilidad" class="form-select">
                                <?php foreach (['Baja','Media','Alta','N/A'] as $dur): ?>
                                <option value="<?= $dur ?>" <?= ($d['durabilidad']===$dur)?'selected':'' ?>><?= $dur ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <?php if ($esEdicion): ?>
                            <button type="button" class="btn btn-f1"
                                    onclick="confirmarEdicion(event, 'formNeu')">
                                <i class="bi bi-save-fill me-1"></i> Guardar cambios
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-f1">
                                <i class="bi bi-plus-circle-fill me-1"></i> Añadir neumático
                            </button>
                        <?php endif; ?>
                        <a href="neumaticos.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($esEdicion): ?><div class="modal-backdrop fade show"></div><?php endif; ?>

<?php include 'includes/footer.php'; ?>
