<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$session_user_id = intval($_SESSION['usuario_id'] ?? 0);
$mensaje_exito = "";
$mensaje_error = "";

if (isset($_GET['exito'])) {
    if ($_GET['exito'] === 'creado') $mensaje_exito = "Profesor registrado exitosamente.";
    if ($_GET['exito'] === 'eliminado') $mensaje_exito = "Profesor eliminado del sistema.";
    if ($_GET['exito'] === 'editado') $mensaje_exito = "Datos del profesor actualizados.";
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'correo_existe') $mensaje_error = "El correo ya está registrado para otro usuario.";
    if ($_GET['error'] === 'db') $mensaje_error = "Ocurrió un error en la base de datos.";
    if ($_GET['error'] === 'clave') $mensaje_error = "Las contraseñas no coinciden.";
}

$sql = "SELECT usuarios.* FROM usuarios JOIN roles ON usuarios.rol_id = roles.id WHERE roles.nombre = 'admin' ORDER BY usuarios.nombre ASC";
$resultado = $conexion->query($sql);

$page_title = "Profesores - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>
<style>
    .modal { display:none; position:fixed; inset:0; background:rgba(18,41,84,.5); backdrop-filter:blur(5px); z-index:1000; justify-content:center; align-items:center; }
    .modal.activo { display:flex; }
    .modal-content { background:var(--color-bg-surface); padding:30px; border-radius:var(--radius-lg); width:100%; max-width:500px; box-shadow:var(--shadow-lg); }
    .close-btn { float:right; cursor:pointer; font-size:24px; font-weight:bold; color:var(--color-error); }
</style>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Gestión de Profesores</h1>
            <p>Añade, edita o elimina a otros profesores de la plataforma.</p>
        </section>

        <?php if ($mensaje_exito) echo "<div class='mensaje-exito-admin'>" . htmlspecialchars($mensaje_exito) . "</div>"; ?>
        <?php if ($mensaje_error) echo "<div class='mensaje-error-admin'>" . htmlspecialchars($mensaje_error) . "</div>"; ?>

        <section class="tabla-contenedor">
            <div class="tabla-header">
                <h2>Lista de Profesores</h2>
                <button class="btn-guardar" onclick="abrirModal('modalCrear')">+ Nuevo Profesor</button>
            </div>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado && $resultado->num_rows > 0) { ?>
                            <?php while ($prof = $resultado->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prof['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($prof['correo']); ?></td>
                                    <td>
                                        <span class="estado <?php echo $prof['estado'] === 'activo' ? 'activo' : 'inactivo'; ?>">
                                            <?php echo ucfirst(htmlspecialchars($prof['estado'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-editar" onclick='editarProfesor(<?php echo json_encode($prof); ?>)'>Editar</button>
                                        <?php if (intval($prof['id']) !== $session_user_id) { ?>
                                            <a href="procesar_profesor.php?accion=eliminar&id=<?php echo intval($prof['id']); ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar este profesor?')">Eliminar</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="4" class="sin-datos">No hay profesores registrados.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<div class="modal" id="modalCrear">
    <div class="modal-content">
        <span class="close-btn" onclick="cerrarModal('modalCrear')">&times;</span>
        <h2>Crear Profesor</h2>
        <form action="procesar_profesor.php" method="POST">
            <input type="hidden" name="accion" value="crear">
            <div class="grupo-form"><label>Nombre Completo</label><input type="text" name="nombre" required></div>
            <div class="grupo-form"><label>Correo Electrónico</label><input type="email" name="correo" required></div>
            <div class="grupo-form"><label>Contraseña</label><input type="password" name="clave" required minlength="8"></div>
            <button type="submit" class="btn-guardar" style="width:100%">Guardar</button>
        </form>
    </div>
</div>

<div class="modal" id="modalEditar">
    <div class="modal-content">
        <span class="close-btn" onclick="cerrarModal('modalEditar')">&times;</span>
        <h2>Editar Profesor</h2>
        <form action="procesar_profesor.php" method="POST">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div class="grupo-form"><label>Nombre Completo</label><input type="text" name="nombre" id="edit_nombre" required></div>
            <div class="grupo-form"><label>Correo Electrónico</label><input type="email" name="correo" id="edit_correo" required></div>
            <div class="grupo-form"><label>Nueva Contraseña (Opcional)</label><input type="password" name="clave" minlength="8" placeholder="Dejar en blanco para mantener actual"></div>
            <button type="submit" class="btn-guardar" style="width:100%">Actualizar</button>
        </form>
    </div>
</div>

<script>
    function abrirModal(id) { document.getElementById(id).classList.add('activo'); }
    function cerrarModal(id) { document.getElementById(id).classList.remove('activo'); }
    function editarProfesor(prof) {
        document.getElementById('edit_id').value = prof.id;
        document.getElementById('edit_nombre').value = prof.nombre;
        document.getElementById('edit_correo').value = prof.correo;
        abrirModal('modalEditar');
    }
</script>

<?php require_once 'includes/footer.php'; ?>
