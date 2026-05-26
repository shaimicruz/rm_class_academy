<?php
require_once "auth.php";
require_once "conexion.php";
require_once "includes/schema_helpers.php";
protegerPagina("admin");

$mensaje_exito = "";
$mensaje_error = "";

if (isset($_GET['exito'])) {
    if ($_GET['exito'] == 'creado')     $mensaje_exito = "Curso/Grado registrado exitosamente.";
    if ($_GET['exito'] == 'eliminado')  $mensaje_exito = "Curso/Grado eliminado del sistema.";
    if ($_GET['exito'] == 'editado')    $mensaje_exito = "Nombre del curso/grado actualizado.";
    if ($_GET['exito'] == 'regenerado') $mensaje_exito = "Código de acceso regenerado correctamente.";
}

if (isset($_GET['error'])) {
    if ($_GET['error'] == 'db')     $mensaje_error = "Ocurrió un error en la base de datos.";
    if ($_GET['error'] == 'en_uso') $mensaje_error = "No se puede eliminar el curso porque tiene estudiantes asignados.";
    if ($_GET['error'] == 'migracion') $mensaje_error = "No se pudo preparar la columna de codigos de acceso.";
    if ($_GET['error'] == 'nombre') $mensaje_error = "Escribe un nombre valido para el curso/grado.";
}

try {
    asegurarCodigoAccesoGrados($conexion);
} catch (Throwable $e) {
    error_log($e->getMessage());
    $mensaje_error = "No se pudo preparar la columna de codigos de acceso.";
}

$sql = "SELECT * FROM grados ORDER BY nombre ASC";
$resultado = $conexion->query($sql);

$page_title = "Cursos y Grados - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>
<style>
    .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(18,41,84,0.5); backdrop-filter:blur(5px); z-index:1000; justify-content:center; align-items:center; }
    .modal.activo { display:flex; }
    .modal-content { background:var(--color-bg-surface); padding:35px; border-radius:var(--radius-lg); width:100%; max-width:500px; box-shadow:var(--shadow-lg); animation:subirSuave 0.3s ease; }
    .close-btn { float:right; cursor:pointer; font-size:24px; font-weight:bold; color:var(--color-error); line-height:1; }
    .codigo-badge { display:inline-block; background:var(--color-primary); color:#fff; font-family:monospace; font-size:16px; font-weight:700; letter-spacing:3px; padding:8px 18px; border-radius:var(--radius-sm); }
    .btn-regenerar { background:rgba(173,131,60,0.12); color:var(--color-accent); border:none; padding:7px 14px; border-radius:var(--radius-sm); font-weight:600; font-size:13px; cursor:pointer; }
    .btn-regenerar:hover { background:var(--color-accent); color:#fff; }
</style>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>
    <main class="contenido">
        <section class="header">
            <h1>Gestión de Cursos / Grados</h1>
            <p>Administra los cursos disponibles. Comparte el <strong>Código de Acceso</strong> con los estudiantes para que puedan unirse al registrarse.</p>
        </section>

        <?php if ($mensaje_exito) echo "<div class='mensaje-exito'>$mensaje_exito</div>"; ?>
        <?php if ($mensaje_error) echo "<div class='mensaje-error'>$mensaje_error</div>"; ?>

        <section class="tabla-contenedor">
            <div class="tabla-header">
                <h2>Lista de Grados</h2>
                <button class="btn-guardar" onclick="abrirModal('modalCrear')">+ Nuevo Grado</button>
            </div>
            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Grado</th>
                            <th>Código de Acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado && $resultado->num_rows > 0): ?>
                            <?php while ($grado = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $grado['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($grado['nombre']); ?></strong></td>
                                    <td>
                                        <?php if (!empty($grado['codigo_acceso'])): ?>
                                            <span class="codigo-badge"><?php echo htmlspecialchars($grado['codigo_acceso']); ?></span>
                                            <a href="procesar_grado.php?accion=regenerar&id=<?php echo $grado['id']; ?>" class="btn-regenerar" style="margin-left:8px;" onclick="return confirm('¿Regenerar código? Los estudiantes con el código anterior no podrán usarlo.')">↻</a>
                                        <?php else: ?>
                                            <a href="procesar_grado.php?accion=regenerar&id=<?php echo $grado['id']; ?>" class="btn-regenerar">Generar código</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn-editar" style="margin-right:5px;" onclick='editarGrado(<?php echo json_encode($grado); ?>)'>Editar</button>
                                        <a href="procesar_grado.php?accion=eliminar&id=<?php echo $grado['id']; ?>" class="btn-eliminar" onclick="return confirm('¿Eliminar este grado? Solo es posible si no tiene estudiantes asignados.')">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="sin-datos" style="text-align:center;padding:20px;">No hay grados registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<!-- Modal Crear -->
<div class="modal" id="modalCrear">
    <div class="modal-content">
        <span class="close-btn" onclick="cerrarModal('modalCrear')">&times;</span>
        <h2 style="margin-bottom:20px;">Crear Nuevo Grado</h2>
        <p style="color:var(--color-text-muted);font-size:14px;margin-bottom:20px;">El código de acceso se genera automáticamente.</p>
        <form action="procesar_grado.php" method="POST">
            <input type="hidden" name="accion" value="crear">
            <div class="grupo-form">
                <label>Nombre del Grado (Ej: Quinto A, Sexto B)</label>
                <input type="text" name="nombre" required placeholder="Ej: Primer Grado A">
            </div>
            <button type="submit" class="btn-guardar" style="width:100%;margin-top:10px;">Crear Grado</button>
        </form>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal" id="modalEditar">
    <div class="modal-content">
        <span class="close-btn" onclick="cerrarModal('modalEditar')">&times;</span>
        <h2 style="margin-bottom:20px;">Editar Grado</h2>
        <form action="procesar_grado.php" method="POST">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="edit_id">
            <div class="grupo-form">
                <label>Nombre del Grado</label>
                <input type="text" name="nombre" id="edit_nombre" required>
            </div>
            <button type="submit" class="btn-guardar" style="width:100%;margin-top:10px;">Actualizar</button>
        </form>
    </div>
</div>

<script>
    function abrirModal(id) { document.getElementById(id).classList.add('activo'); }
    function cerrarModal(id) { document.getElementById(id).classList.remove('activo'); }
    function editarGrado(grado) {
        document.getElementById('edit_id').value = grado.id;
        document.getElementById('edit_nombre').value = grado.nombre;
        abrirModal('modalEditar');
    }
    document.querySelectorAll('.modal').forEach(m => m.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('activo');
    }));
</script>

<?php require_once 'includes/footer.php'; ?>
