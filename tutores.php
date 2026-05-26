<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$sql = "SELECT 
            tutores.id AS tutor_id,
            tutores.parentesco,
            usuarios.id AS usuario_id,
            usuarios.nombre,
            usuarios.apellido,
            usuarios.correo,
            usuarios.telefono,
            usuarios.estado,
            estudiantes.matricula,
            estudiante_usuario.nombre AS estudiante_nombre,
            estudiante_usuario.apellido AS estudiante_apellido
        FROM tutores
        INNER JOIN usuarios ON tutores.usuario_id = usuarios.id
        LEFT JOIN estudiantes ON tutores.estudiante_id = estudiantes.id
        LEFT JOIN usuarios AS estudiante_usuario ON estudiantes.usuario_id = estudiante_usuario.id
        ORDER BY tutores.id DESC";

$resultado = $conexion->query($sql);

$estudiantes_lista = [];
$res_est = $conexion->query("
    SELECT e.id AS estudiante_id, e.matricula, u.nombre, u.apellido
    FROM estudiantes e
    INNER JOIN usuarios u ON u.id = e.usuario_id
    ORDER BY u.nombre ASC, u.apellido ASC
");
if ($res_est) {
    while ($row = $res_est->fetch_assoc()) {
        $estudiantes_lista[] = $row;
    }
}

$page_title = "Tutores - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">

    <?php require_once 'includes/navbar.php'; ?>
<main class="contenido">

        <section class="header">
            <h1>Tutores registrados</h1>
            <p>Lista general de tutores que tienen cuenta en la plataforma.</p>
        </section>

        <section class="tabla-contenedor">

            <div class="tabla-header">
                <h2>Lista de tutores</h2>
            </div>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nombre completo</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Estudiante Asignado</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                            <th>Vincular</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($resultado && $resultado->num_rows > 0) { ?>
                            <?php $contador = 1; ?>
                            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $contador++; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($fila['nombre'] . " " . $fila['apellido']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                                     <td>
                                        <?php 
                                            echo !empty($fila['telefono']) 
                                                ? htmlspecialchars($fila['telefono']) 
                                                : "No registrado"; 
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if (!empty($fila['estudiante_nombre'])) {
                                                echo htmlspecialchars($fila['estudiante_nombre'] . " " . $fila['estudiante_apellido']) . " <br><small>(" . htmlspecialchars($fila['matricula']) . ")</small>";
                                            } else {
                                                echo "No asignado";
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo htmlspecialchars($fila['estado']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($fila['estado'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($fila['estado'] == 'pendiente') { ?>
                                            <a href="procesar_tutor.php?accion=aceptar&id=<?php echo $fila['usuario_id']; ?>" class="btn-pequeno btn-exito" onclick="return confirm('¿Seguro que deseas aceptar a este tutor?');">Aceptar</a>
                                            <a href="procesar_tutor.php?accion=rechazar&id=<?php echo $fila['usuario_id']; ?>" class="btn-pequeno btn-peligro" onclick="return confirm('¿Seguro que deseas rechazar (eliminar) a este tutor?');">Rechazar</a>
                                        <?php } else { ?>
                                            <span class="text-muted">Sin acciones</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <form action="procesar_vinculo_tutor.php" method="POST" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                            <input type="hidden" name="tutor_id" value="<?php echo intval($fila['tutor_id']); ?>">
                                            <select name="estudiante_id" required style="min-width:220px;">
                                                <option value="">Seleccionar estudiante...</option>
                                                <?php foreach ($estudiantes_lista as $e) { ?>
                                                    <option value="<?php echo intval($e['estudiante_id']); ?>">
                                                        <?php echo htmlspecialchars(($e['matricula'] ?? '') . ' - ' . ($e['nombre'] ?? '') . ' ' . ($e['apellido'] ?? '')); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <button type="submit" class="btn-pequeno btn-editar">Vincular</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8" class="sin-datos">
                                    Todavía no hay tutores registrados.
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


