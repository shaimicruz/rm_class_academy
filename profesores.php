<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$session_user_id = intval($_SESSION['usuario_id'] ?? 0);
$mensaje_exito = "";
$mensaje_error = "";

if (isset($_GET['exito'])) {
    if ($_GET['exito'] === 'creado') $mensaje_exito = "Usuario registrado exitosamente.";
    if ($_GET['exito'] === 'eliminado') $mensaje_exito = "Usuario eliminado del sistema.";
    if ($_GET['exito'] === 'editado') $mensaje_exito = "Datos del usuario actualizados.";
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'correo_existe') $mensaje_error = "El correo ya está registrado para otro usuario.";
    if ($_GET['error'] === 'db') $mensaje_error = "Ocurrió un error en la base de datos.";
    if ($_GET['error'] === 'clave') $mensaje_error = "La contraseña no es válida o no coincide.";
}

// Lista Admin + Profesores (gestión del personal).
$sql = "
    SELECT u.id, u.nombre, u.correo, u.estado, r.nombre AS rol
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
    WHERE r.nombre IN ('admin', 'profesor')
    ORDER BY r.nombre DESC, u.nombre ASC
";
$resultado = $conexion->query($sql);

$page_title = "Administradores y Profesores - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>
<style>
    .modal { display:none; position:fixed; inset:0; background:rgba(18,41,84,.5); backdrop-filter:blur(5px); z-index:1000; justify-content:center; align-items:center; }
    .modal.activo { display:flex; }
    .modal-content { background:var(--color-bg-surface); padding:30px; border-radius:var(--radius-lg); width:100%; max-width:520px; box-shadow:var(--shadow-lg); }
    .close-btn { float:right; cursor:pointer; font-size:24px; font-weight:bold; color:var(--color-error); }
</style>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Gestión de administradores y profesores</h1>
            <p>Crea cuentas del personal o cambia su rol (admin/profesor).</p>
        </section>

        <?php if ($mensaje_exito) echo "<div class='mensaje-exito-admin'>" . htmlspecialchars($mensaje_exito) . "</div>"; ?>
        <?php if ($mensaje_error) echo "<div class='mensaje-error-admin'>" . htmlspecialchars($mensaje_error) . "</div>"; ?>

        <section class="tabla-contenedor">
            <div class="tabla-header">
                <h2>Lista del personal</h2>
                <button class="btn-guardar" onclick="abrirModal('modalCrear')">+ Nuevo</button>
            </div>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado && $resultado->num_rows > 0) { ?>
                            <?php while ($u = $resultado->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($u['correo']); ?></td>
                                    <td>
                                        <span class="estado <?php echo $u['rol'] === 'admin' ? 'activo' : 'inactivo'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($u['rol'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="estado <?php echo $u['estado'] === 'activo' ? 'activo' : 'inactivo'; ?>">
                                            <?php echo htmlspecialchars(ucfirst($u['estado'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-editar" onclick='editarUsuario(<?php echo json_encode($u); ?>)'>Editar</button>
                                        <?php if (intval($u['id']) !== $session_user_id) { ?>
                                            <a href="procesar_profesor.php?accion=eliminar&id=<?php echo intval($u['id']); ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">Eliminar</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="5" class="sin-datos">No hay usuarios del personal registrados.</td></tr>
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
        <h2>Crear usuario del personal</h2>
        <form action="procesar_profesor.php" method="POST">
            <input type="hidden" name="accion" value="crear">
            <div class="grupo-form"><label>Nombre completo</label><input type="text" name="nombre" required></div>
            <div class="grupo-form"><label>Correo electrónico</label><input type="email" name="correo" required></div>
            <div class="grupo-form">
                <label>Rol</label>
                <select name="rol" required>
                    <option value="profesor">Profesor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="grupo-form"><label>Contraseña</label><input type="password" name="clave" required minlength="8"></div>
            <div class="grupo-form"><label>Confirmar contraseña</label><input type="password" name="clave_confirm" required minlength="8"></div>
            <button type="submit" class="btn-guardar" style="width:100%">Guardar</button>
        </form>
    </div>
</div>

<div class="modal" id="modalEditar">
    <div class="modal-content">
        <span class="close-btn" onclick="cerrarModal('modalEditar')">&times;</span>
        <h2>Editar usuario</h2>
        <form action="procesar_profesor.php" method="POST">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div class="grupo-form"><label>Nombre completo</label><input type="text" name="nombre" id="edit_nombre" required></div>
            <div class="grupo-form"><label>Correo electrónico</label><input type="email" name="correo" id="edit_correo" required></div>
            <div class="grupo-form">
                <label>Rol</label>
                <select name="rol" id="edit_rol" required>
                    <option value="profesor">Profesor</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="grupo-form"><label>Nueva contraseña (opcional)</label><input type="password" name="clave" minlength="8" placeholder="Dejar en blanco para mantener"></div>
            <div class="grupo-form"><label>Confirmar nueva contraseña</label><input type="password" name="clave_confirm" minlength="8" placeholder="Repite la contraseña nueva"></div>
            <button type="submit" class="btn-guardar" style="width:100%">Actualizar</button>
        </form>
    </div>
</div>

<script>
    function abrirModal(id) { document.getElementById(id).classList.add('activo'); }
    function cerrarModal(id) { document.getElementById(id).classList.remove('activo'); }
    function editarUsuario(u) {
        document.getElementById('edit_id').value = u.id;
        document.getElementById('edit_nombre').value = u.nombre;
        document.getElementById('edit_correo').value = u.correo;
        document.getElementById('edit_rol').value = u.rol;
        abrirModal('modalEditar');
    }
</script>

<?php require_once 'includes/footer.php'; ?>

