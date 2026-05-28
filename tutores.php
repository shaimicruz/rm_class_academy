<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$grados = $conexion->query("SELECT id, nombre FROM grados ORDER BY id ASC");
$grados_lista = [];
if ($grados) {
    while ($g = $grados->fetch_assoc()) $grados_lista[] = $g;
}

$filtro_grado = isset($_GET['grado']) ? intval($_GET['grado']) : 0;
$where = "";
if ($filtro_grado > 0) {
    $where = "WHERE e.grado_id = " . $filtro_grado;
} elseif ($filtro_grado === -1) {
    $where = "WHERE e.grado_id IS NULL";
} elseif ($filtro_grado === -2) {
    $where = "WHERE t.estudiante_id IS NULL";
}

$sql = "SELECT
            t.id AS tutor_id,
            t.parentesco,
            u.id AS usuario_id,
            u.nombre,
            u.apellido,
            u.correo,
            u.telefono,
            u.estado,
            e.id AS estudiante_id,
            e.matricula,
            eu.nombre AS estudiante_nombre,
            eu.apellido AS estudiante_apellido,
            g.nombre AS grado_nombre
        FROM tutores t
        INNER JOIN usuarios u ON t.usuario_id = u.id
        LEFT JOIN estudiantes e ON t.estudiante_id = e.id
        LEFT JOIN usuarios eu ON e.usuario_id = eu.id
        LEFT JOIN grados g ON e.grado_id = g.id
        $where
        ORDER BY t.id DESC";

$resultado = $conexion->query($sql);

$estudiantes_lista = [];
$res_est = $conexion->query("
    SELECT e.id AS estudiante_id, e.matricula, u.nombre, u.apellido, g.nombre AS grado_nombre
    FROM estudiantes e
    INNER JOIN usuarios u ON u.id = e.usuario_id
    LEFT JOIN grados g ON g.id = e.grado_id
    ORDER BY u.nombre ASC, u.apellido ASC
");
if ($res_est) {
    while ($row = $res_est->fetch_assoc()) $estudiantes_lista[] = $row;
}

$page_title = "Tutores - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Tutores registrados</h1>
            <p>Gestiona aprobación y vínculo del tutor con su estudiante.</p>
        </section>

        <section class="tabla-contenedor">
            <div class="tabla-header">
                <h2>Lista de tutores</h2>
                <form method="GET" action="tutores.php" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <label style="font-weight:800; color:var(--color-primary);">Filtrar por grado</label>
                    <select name="grado" onchange="this.form.submit()" style="min-width:260px;">
                        <option value="0" <?php echo $filtro_grado === 0 ? 'selected' : ''; ?>>Todos</option>
                        <option value="-2" <?php echo $filtro_grado === -2 ? 'selected' : ''; ?>>Sin estudiante asignado</option>
                        <option value="-1" <?php echo $filtro_grado === -1 ? 'selected' : ''; ?>>Estudiante sin grado</option>
                        <?php foreach ($grados_lista as $g) { ?>
                            <option value="<?php echo intval($g['id']); ?>" <?php echo $filtro_grado === intval($g['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['nombre']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </form>
            </div>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tutor</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Estudiante</th>
                            <th>Grado</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado && $resultado->num_rows > 0) { ?>
                            <?php $contador = 1; ?>
                            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $contador++; ?></td>
                                    <td><?php echo htmlspecialchars($fila['nombre'] . " " . $fila['apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                                    <td><?php echo !empty($fila['telefono']) ? htmlspecialchars($fila['telefono']) : "No registrado"; ?></td>
                                    <td>
                                        <?php
                                        if (!empty($fila['estudiante_nombre'])) {
                                            echo htmlspecialchars($fila['estudiante_nombre'] . " " . $fila['estudiante_apellido']) . " <br><small>(" . htmlspecialchars($fila['matricula']) . ")</small>";
                                        } else {
                                            echo "No asignado";
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($fila['grado_nombre'] ?? "Sin grado"); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo htmlspecialchars($fila['estado']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($fila['estado'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($fila['estado'] == 'pendiente') { ?>
                                            <a href="procesar_tutor.php?accion=aceptar&id=<?php echo intval($fila['usuario_id']); ?>" class="btn-pequeno btn-exito" onclick="return confirm('¿Seguro que deseas aceptar a este tutor?');">Aceptar</a>
                                            <a href="procesar_tutor.php?accion=rechazar&id=<?php echo intval($fila['usuario_id']); ?>" class="btn-pequeno btn-peligro" onclick="return confirm('¿Seguro que deseas rechazar (eliminar) a este tutor?');">Rechazar</a>
                                        <?php } ?>
                                        <button
                                            type="button"
                                            class="btn-pequeno btn-editar"
                                            onclick="abrirVinculo(<?php echo intval($fila['tutor_id']); ?>, <?php echo $fila['estudiante_id'] === null ? 'null' : intval($fila['estudiante_id']); ?>)"
                                        >Editar vínculo</button>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8" class="sin-datos">Todavía no hay tutores registrados.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<div class="modal" id="modalVinculoTutor" aria-hidden="true">
    <div class="modal-content">
        <span class="close-btn" onclick="cerrarVinculo()">&times;</span>
        <h2>Vincular tutor con estudiante</h2>
        <form action="procesar_vinculo_tutor.php" method="POST">
            <input type="hidden" name="tutor_id" id="vinc_tutor_id" value="">
            <div class="grupo-form">
                <label>Estudiante</label>
                <select name="estudiante_id" id="vinc_estudiante_id" required>
                    <option value="">Seleccionar estudiante...</option>
                    <?php foreach ($estudiantes_lista as $e) { ?>
                        <option value="<?php echo intval($e['estudiante_id']); ?>">
                            <?php echo htmlspecialchars(($e['matricula'] ?? '') . ' - ' . ($e['nombre'] ?? '') . ' ' . ($e['apellido'] ?? '') . ' (' . ($e['grado_nombre'] ?? 'Sin grado') . ')'); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn-guardar" style="width:100%;">Guardar</button>
        </form>
    </div>
</div>

<script>
    function abrirVinculo(tutorId, estudianteId) {
        document.getElementById('vinc_tutor_id').value = tutorId;
        const sel = document.getElementById('vinc_estudiante_id');
        sel.value = (estudianteId === null) ? "" : String(estudianteId);
        const modal = document.getElementById('modalVinculoTutor');
        modal.classList.add('activo');
        modal.setAttribute('aria-hidden', 'false');
    }
    function cerrarVinculo() {
        const modal = document.getElementById('modalVinculoTutor');
        modal.classList.remove('activo');
        modal.setAttribute('aria-hidden', 'true');
    }
</script>

<?php require_once 'includes/footer.php'; ?>

