<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$grados = $conexion->query("SELECT id, nombre FROM grados ORDER BY id ASC");
$grados_lista = [];
if ($grados) {
    while ($g = $grados->fetch_assoc()) {
        $grados_lista[] = $g;
    }
}

$filtro_grado = isset($_GET['grado']) ? intval($_GET['grado']) : 0;
$where = "";
if ($filtro_grado > 0) {
    $where = "WHERE estudiantes.grado_id = " . $filtro_grado;
} elseif ($filtro_grado === -1) {
    $where = "WHERE estudiantes.grado_id IS NULL";
}

$sql = "SELECT 
            estudiantes.id AS estudiante_id,
            estudiantes.grado_id,
            estudiantes.matricula,
            grados.nombre AS grado_nombre,
            usuarios.nombre,
            usuarios.apellido,
            usuarios.correo,
            usuarios.telefono
        FROM estudiantes
        INNER JOIN usuarios ON estudiantes.usuario_id = usuarios.id
        LEFT JOIN grados ON grados.id = estudiantes.grado_id
        $where
        ORDER BY estudiantes.id DESC";

$resultado = $conexion->query($sql);

$page_title = "Estudiantes - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Estudiantes registrados</h1>
            <p>Asigna o cambia el grado de cada estudiante para controlar qué contenido ve.</p>
        </section>

        <section class="tabla-contenedor">
            <div class="tabla-header">
                <h2>Lista de estudiantes</h2>
                <form method="GET" action="estudiante.php" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <label style="font-weight:800; color:var(--color-primary);">Filtrar por grado</label>
                    <select name="grado" onchange="this.form.submit()" style="min-width:240px;">
                        <option value="0" <?php echo $filtro_grado === 0 ? 'selected' : ''; ?>>Todos</option>
                        <option value="-1" <?php echo $filtro_grado === -1 ? 'selected' : ''; ?>>Sin grado</option>
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
                            <th>Nombre completo</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Matrícula</th>
                            <th>Grado</th>
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
                                    <td><?php echo htmlspecialchars($fila['matricula'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($fila['grado_nombre'] ?? 'Sin grado'); ?></td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn-pequeno btn-editar"
                                            onclick="abrirEditarGrado(<?php echo intval($fila['estudiante_id']); ?>, <?php echo $fila['grado_id'] === null ? 'null' : intval($fila['grado_id']); ?>)"
                                        >Editar</button>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7" class="sin-datos" style="text-align: center; padding: 20px;">
                                    Todavía no hay estudiantes registrados.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<div class="modal" id="modalEditarGrado" aria-hidden="true">
    <div class="modal-content">
        <span class="close-btn" onclick="cerrarEditarGrado()">&times;</span>
        <h2>Asignar grado</h2>
        <form action="procesar_estudiante.php" method="POST">
            <input type="hidden" name="accion" value="asignar_grado">
            <input type="hidden" name="estudiante_id" id="edit_estudiante_id" value="">
            <div class="grupo-form">
                <label>Grado</label>
                <select name="grado_id" id="edit_grado_id" required>
                    <option value="0">Sin grado</option>
                    <?php foreach ($grados_lista as $g) { ?>
                        <option value="<?php echo intval($g['id']); ?>"><?php echo htmlspecialchars($g['nombre']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn-guardar" style="width:100%;">Guardar</button>
        </form>
    </div>
</div>

<script>
    function abrirEditarGrado(estudianteId, gradoId) {
        document.getElementById('edit_estudiante_id').value = estudianteId;
        const sel = document.getElementById('edit_grado_id');
        sel.value = (gradoId === null) ? "0" : String(gradoId);
        const modal = document.getElementById('modalEditarGrado');
        modal.classList.add('activo');
        modal.setAttribute('aria-hidden', 'false');
    }
    function cerrarEditarGrado() {
        const modal = document.getElementById('modalEditarGrado');
        modal.classList.remove('activo');
        modal.setAttribute('aria-hidden', 'true');
    }
</script>

<?php require_once 'includes/footer.php'; ?>

