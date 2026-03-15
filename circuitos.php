<?php
// CRUD de circuitos + llamada a la API de clima Open-Meteo
require_once 'config/auth.php';
require_once 'config/config.php';

$pageTitle = 'Circuitos';
$msgOk = $msgError = '';

// Añadir circuito nuevo
// Solo el admin puede modificar datos
if (isset($_POST['accion']) || isset($_GET['borrar']) || isset($_GET['editar'])) {
    solo_admin();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    $stmt = $conexion->prepare(
        "INSERT INTO circuitos (nombre, pais, ciudad, longitud_km, num_curvas,
         tipo_circuito, lat, lon, record_vuelta)
         VALUES (:nombre, :pais, :ciudad, :longitud_km, :num_curvas,
         :tipo_circuito, :lat, :lon, :record_vuelta)"
    );
    $stmt->execute([
        ':nombre'        => trim($_POST['nombre']),
        ':pais'          => trim($_POST['pais']),
        ':ciudad'        => trim($_POST['ciudad']),
        ':longitud_km'   => floatval($_POST['longitud_km']),
        ':num_curvas'    => (int)$_POST['num_curvas'],
        ':tipo_circuito' => $_POST['tipo_circuito'],
        ':lat'           => floatval($_POST['lat']),
        ':lon'           => floatval($_POST['lon']),
        ':record_vuelta' => trim($_POST['record_vuelta']),
    ]);
    $msgOk = 'Circuito añadido correctamente.';
}

// Guardar cambios de un circuito
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    $stmt = $conexion->prepare(
        "UPDATE circuitos SET nombre=:nombre, pais=:pais, ciudad=:ciudad,
         longitud_km=:longitud_km, num_curvas=:num_curvas, tipo_circuito=:tipo_circuito,
         lat=:lat, lon=:lon, record_vuelta=:record_vuelta
         WHERE id_circuito=:id"
    );
    $stmt->execute([
        ':nombre'        => trim($_POST['nombre']),
        ':pais'          => trim($_POST['pais']),
        ':ciudad'        => trim($_POST['ciudad']),
        ':longitud_km'   => floatval($_POST['longitud_km']),
        ':num_curvas'    => (int)$_POST['num_curvas'],
        ':tipo_circuito' => $_POST['tipo_circuito'],
        ':lat'           => floatval($_POST['lat']),
        ':lon'           => floatval($_POST['lon']),
        ':record_vuelta' => trim($_POST['record_vuelta']),
        ':id'            => (int)$_POST['id_circuito'],
    ]);
    $msgOk = 'Circuito actualizado correctamente.';
}

// Eliminar circuito
if (isset($_GET['borrar'])) {
    $stmt = $conexion->prepare("DELETE FROM circuitos WHERE id_circuito = :id");
    $stmt->execute([':id' => (int)$_GET['borrar']]);
    $msgOk = 'Circuito eliminado.';
}

// Si viene ?editar cargo ese circuito
$circuitoEditar = null;
if (isset($_GET['editar'])) {
    $stmt = $conexion->prepare("SELECT * FROM circuitos WHERE id_circuito = :id");
    $stmt->execute([':id' => (int)$_GET['editar']]);
    $circuitoEditar = $stmt->fetch();
}

// Si viene ?clima hago la llamada a Open-Meteo con lat/lon del circuito
$climaData  = null;
$climaError = '';
if (isset($_GET['clima'])) {
    $idClima = (int)$_GET['clima'];
    $stmtC   = $conexion->prepare("SELECT nombre, lat, lon FROM circuitos WHERE id_circuito = :id");
    $stmtC->execute([':id' => $idClima]);
    $circuito = $stmtC->fetch();

    if ($circuito) {
        // Monto la URL con las variables que necesito
        $url = WEATHER_API
             . "?latitude={$circuito['lat']}"
             . "&longitude={$circuito['lon']}"
             . "&current=temperature_2m,relative_humidity_2m,precipitation,rain,"
             . "wind_speed_10m,wind_direction_10m,weather_code"
             . "&wind_speed_unit=kmh"
             . "&timezone=auto";

        $respuesta = @file_get_contents($url);

        if ($respuesta !== false) {
            $json = json_decode($respuesta, true);
            if (isset($json['current'])) {
                $c = $json['current'];
                $wc = (int)($c['weather_code'] ?? 0);

                // Interpretar WMO weather code
                $condicion = interpretarCodigoClima($wc);

                $climaData = [
                    'circuito'    => $circuito['nombre'],
                    'temperatura' => $c['temperature_2m']      ?? '--',
                    'humedad'     => $c['relative_humidity_2m'] ?? '--',
                    'lluvia'      => $c['rain']                 ?? 0,
                    'viento'      => $c['wind_speed_10m']       ?? '--',
                    'direccion'   => $c['wind_direction_10m']   ?? '--',
                    'condicion'   => $condicion['texto'],
                    'icono'       => $condicion['icono'],
                    'clase_pista' => $condicion['clase_pista'],
                ];
            } else {
                $climaError = 'No se pudieron obtener datos del clima.';
            }
        } else {
            $climaError = 'No se pudo conectar con la API de clima. Comprueba la conexión a internet.';
        }
    }
}

// Convierte el código numérico WMO de Open-Meteo en texto e icono
function interpretarCodigoClima(int $code): array {
    if ($code === 0 || $code === 1) {
        return ['texto' => 'Despejado', 'icono' => '☀️', 'clase_pista' => 'seco'];
    } elseif ($code <= 3) {
        return ['texto' => 'Parcialmente nublado', 'icono' => '⛅', 'clase_pista' => 'seco'];
    } elseif ($code <= 49) {
        return ['texto' => 'Niebla / Bruma', 'icono' => '🌫️', 'clase_pista' => 'mixto'];
    } elseif ($code <= 59) {
        return ['texto' => 'Llovizna', 'icono' => '🌦️', 'clase_pista' => 'mojado'];
    } elseif ($code <= 69) {
        return ['texto' => 'Lluvia', 'icono' => '🌧️', 'clase_pista' => 'mojado'];
    } elseif ($code <= 79) {
        return ['texto' => 'Nieve / Aguanieve', 'icono' => '🌨️', 'clase_pista' => 'mojado'];
    } elseif ($code <= 84) {
        return ['texto' => 'Chubascos', 'icono' => '⛈️', 'clase_pista' => 'mojado'];
    } elseif ($code <= 99) {
        return ['texto' => 'Tormenta eléctrica', 'icono' => '⛈️', 'clase_pista' => 'mojado'];
    }
    return ['texto' => 'Desconocido', 'icono' => '❓', 'clase_pista' => 'desc'];
}

$circuitos = $conexion->query("SELECT * FROM circuitos ORDER BY nombre")->fetchAll();

include 'includes/header.php';

$esEdicion = ($circuitoEditar !== null);
$d = $circuitoEditar ?? [
    'id_circuito'=>'','nombre'=>'','pais'=>'','ciudad'=>'',
    'longitud_km'=>'','num_curvas'=>'','tipo_circuito'=>'Autódromo',
    'lat'=>'','lon'=>'','record_vuelta'=>''
];
?>

<div class="row mb-3 align-items-center">
    <div class="col">
        <h2 class="fw-bold"><i class="bi bi-map-fill me-2"></i>Circuitos</h2>
    </div>
    <?php if (es_admin()): ?>
        <div class="col-auto">
        <button class="btn btn-f1" data-bs-toggle="modal" data-bs-target="#modalCircuito">
            <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Circuito
        </button>
    </div>
        <?php endif; ?>
</div>

<?php if ($msgOk):   ?><div class="alert alert-success"><?= htmlspecialchars($msgOk) ?></div><?php endif; ?>

<!-- WIDGET CLIMA -->
<?php if ($climaData): ?>
<div class="weather-card p-3 mb-4">
    <div class="row align-items-center">
        <div class="col-auto">
            <div class="weather-icon"><?= $climaData['icono'] ?></div>
        </div>
        <div class="col">
            <h5 class="fw-bold mb-1">
                Condición Climática – <?= htmlspecialchars($climaData['circuito']) ?>
            </h5>
            <p class="mb-0 fs-5"><?= htmlspecialchars($climaData['condicion']) ?></p>
            <small style="color:var(--muted)">
                🌡️ <?= $climaData['temperatura'] ?>°C &nbsp;|&nbsp;
                💧 Humedad: <?= $climaData['humedad'] ?>% &nbsp;|&nbsp;
                🌬️ Viento: <?= $climaData['viento'] ?> km/h &nbsp;|&nbsp;
                🌧️ Lluvia: <?= $climaData['lluvia'] ?> mm
            </small>
        </div>
        <div class="col-auto text-center">
            <div class="fw-bold mb-1">Estado de la pista</div>
            <?php
            $clasePista = $climaData['clase_pista'];
            $badgePista = match($clasePista) {
                'seco'   => ['bg-warning text-dark', '🟡 SECO'], // IMPLEMENTAR COLORES
                'mojado' => ['bg-info text-dark',    '🔵 MOJADO'],
                'mixto'  => ['bg-success',            '🟢 MIXTO'],
                default  => ['bg-secondary',          '⚪ DESCONOCIDO'],
            };
            ?>
            <span class="badge fs-6 <?= $badgePista[0] ?>"><?= $badgePista[1] ?></span>
        </div>
    </div>
</div>
<?php elseif ($climaError): ?>
<div class="alert alert-warning"><?= htmlspecialchars($climaError) ?></div>
<?php endif; ?>

<!-- TABLA -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-dark-f1 table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Circuito</th>
                    <th>País</th>
                    <th>Ciudad</th>
                    <th>Longitud</th>
                    <th>Curvas</th>
                    <th>Tipo</th>
                    <th>Récord vuelta</th>
                    <th>Clima</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($circuitos as $c): ?>
            <tr>
                <td><?= $c['id_circuito'] ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= htmlspecialchars($c['pais']) ?></td>
                <td><?= htmlspecialchars($c['ciudad']) ?></td>
                <td><?= $c['longitud_km'] ?> km</td>
                <td><?= $c['num_curvas'] ?></td>
                <td><span class="badge bg-secondary"><?= $c['tipo_circuito'] ?></span></td>
                <td><code><?= htmlspecialchars($c['record_vuelta'] ?? '-') ?></code></td>
                <td>
                    <!-- Este botón recarga la página con el clima del circuito -->
                    <a href="circuitos.php?clima=<?= $c['id_circuito'] ?>#clima"
                       class="btn btn-sm btn-outline-info" title="Ver clima actual">
                        🌤️
                    </a>
                </td>
                <?php if (es_admin()): ?>
                    <td>
                    <a href="circuitos.php?editar=<?= $c['id_circuito'] ?>"
                       class="btn btn-sm btn-outline-warning me-1">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <a href="circuitos.php?borrar=<?= $c['id_circuito'] ?>"
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

<!-- MODAL CIRCUITO -->
<div class="modal fade <?= $esEdicion ? 'show' : '' ?>"
     id="modalCircuito" tabindex="-1"
     style="<?= $esEdicion ? 'display:block;' : '' ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:#1a1a1a; color:#f0f0f0; border:1px solid #e8002d;">
            <div class="modal-header" style="border-bottom:1px solid #333;">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-map-fill me-1"></i>
                    <?= $esEdicion ? 'Editar Circuito' : 'Nuevo Circuito' ?>
                </h5>
                <a href="circuitos.php" class="btn-close btn-close-white"></a>
            </div>
            <div class="modal-body">
                <form id="formCircuito" action="circuitos.php" method="POST"
                      onsubmit="return validarFormulario('formCircuito')">
                    <input type="hidden" name="accion"       value="<?= $esEdicion ? 'actualizar' : 'insertar' ?>">
                    <input type="hidden" name="id_circuito"  value="<?= $d['id_circuito'] ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre del circuito *</label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= htmlspecialchars($d['nombre']) ?>"
                                   data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">País *</label>
                            <input type="text" name="pais" class="form-control"
                                   value="<?= htmlspecialchars($d['pais']) ?>"
                                   data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ciudad *</label>
                            <input type="text" name="ciudad" class="form-control"
                                   value="<?= htmlspecialchars($d['ciudad']) ?>"
                                   data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Longitud (km) *</label>
                            <input type="number" name="longitud_km" class="form-control"
                                   value="<?= $d['longitud_km'] ?>"
                                   step="0.001" min="0" data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Número de curvas *</label>
                            <input type="number" name="num_curvas" class="form-control"
                                   value="<?= $d['num_curvas'] ?>"
                                   min="1" data-requerido="true">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Récord vuelta</label>
                            <input type="text" name="record_vuelta" class="form-control"
                                   value="<?= htmlspecialchars($d['record_vuelta']) ?>"
                                   placeholder="1:30.000">
                        </div>
                        <!-- Tipo circuito como SELECT -->
                        <div class="col-md-6">
                            <label class="form-label">Tipo de circuito *</label>
                            <select name="tipo_circuito" class="form-select" data-requerido="true">
                                <?php foreach (['Autódromo','Callejero','Híbrido','Rutero','Aeródromo'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($d['tipo_circuito']===$t)?'selected':'' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Latitud (para clima) *</label>
                            <input type="number" name="lat" class="form-control"
                                   value="<?= $d['lat'] ?>"
                                   step="0.000001" data-requerido="true"
                                   placeholder="41.570000">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitud geográfica (para clima) *</label>
                            <input type="number" name="lon" class="form-control"
                                   value="<?= $d['lon'] ?>"
                                   step="0.000001" data-requerido="true"
                                   placeholder="2.260000">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <?php if ($esEdicion): ?>
                            <button type="button" class="btn btn-f1"
                                    onclick="confirmarEdicion(event, 'formCircuito')">
                                <i class="bi bi-save-fill me-1"></i> Guardar
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-f1">
                                <i class="bi bi-plus-circle-fill me-1"></i> Añadir
                            </button>
                        <?php endif; ?>
                        <a href="circuitos.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($esEdicion): ?><div class="modal-backdrop fade show"></div><?php endif; ?>

<?php include 'includes/footer.php'; ?>
