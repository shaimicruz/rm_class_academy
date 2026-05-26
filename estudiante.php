<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

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
        ORDER BY estudiantes.id DESC";

$resultado = $conexion->query($sql);

$grados = $conexion->query("SELECT id, nombre FROM grados ORDER BY id ASC");
$grados_lista = [];
if ($grados) {
    while ($g = $grados->fetch_assoc()) {
        $grados_lista[] = $g;
    }
}

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
                                        <form action="procesar_estudiante.php" method="POST" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                            <input type="hidden" name="accion" value="asignar_grado">
                                            <input type="hidden" name="estudiante_id" value="<?php echo intval($fila['estudiante_id']); ?>">
                                            <select name="grado_id" required style="min-width:220px;">
                                                <option value="">Seleccione...</option>
                                                <?php foreach ($grados_lista as $g) { ?>
                                                    <option value="<?php echo intval($g['id']); ?>" <?php echo intval($fila['grado_id']) === intval($g['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($g['nombre']); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <button type="submit" class="btn-pequeno btn-editar">Guardar</button>
                                        </form>
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

<?php require_once 'includes/footer.php'; ?>
