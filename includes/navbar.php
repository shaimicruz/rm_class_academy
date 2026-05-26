<?php
// includes/navbar.php
$rol = $_SESSION['rol'] ?? '';
$pagina_actual = basename($_SERVER['PHP_SELF']);
$usuario_id = intval($_SESSION['usuario_id'] ?? 0);
$nombre_usuario = trim((string)($_SESSION['nombre'] ?? 'Usuario'));
$foto_perfil = '';
$classroom_label = 'Classroom general';

function esActivo($paginas, $actual) {
    $paginas = is_array($paginas) ? $paginas : [$paginas];
    return in_array($actual, $paginas, true) ? 'activo' : '';
}

if (isset($conexion) && $usuario_id > 0) {
    $stmt_user = $conexion->prepare("SELECT foto_perfil FROM usuarios WHERE id = ? LIMIT 1");
    if ($stmt_user) {
        $stmt_user->bind_param("i", $usuario_id);
        $stmt_user->execute();
        $res_user = $stmt_user->get_result();
        if ($res_user && $res_user->num_rows > 0) {
            $row_user = $res_user->fetch_assoc();
            $foto_perfil = trim((string)($row_user['foto_perfil'] ?? ''));
        }
    }
}

if ($rol === 'estudiante' && isset($conexion) && $usuario_id > 0) {
    $stmt_grado = $conexion->prepare("
        SELECT g.nombre
        FROM estudiantes e
        LEFT JOIN grados g ON g.id = e.grado_id
        WHERE e.usuario_id = ? LIMIT 1
    ");
    if ($stmt_grado) {
        $stmt_grado->bind_param("i", $usuario_id);
        $stmt_grado->execute();
        $res_grado = $stmt_grado->get_result();
        if ($res_grado && $res_grado->num_rows > 0) {
            $row_grado = $res_grado->fetch_assoc();
            if (!empty($row_grado['nombre'])) {
                $classroom_label = $row_grado['nombre'];
            }
        }
    }
} elseif ($rol === 'tutor' && isset($conexion) && $usuario_id > 0) {
    $stmt_grado = $conexion->prepare("
        SELECT g.nombre
        FROM tutores t
        LEFT JOIN estudiantes e ON e.id = t.estudiante_id
        LEFT JOIN grados g ON g.id = e.grado_id
        WHERE t.usuario_id = ? LIMIT 1
    ");
    if ($stmt_grado) {
        $stmt_grado->bind_param("i", $usuario_id);
        $stmt_grado->execute();
        $res_grado = $stmt_grado->get_result();
        if ($res_grado && $res_grado->num_rows > 0) {
            $row_grado = $res_grado->fetch_assoc();
            if (!empty($row_grado['nombre'])) {
                $classroom_label = $row_grado['nombre'];
            }
        }
    }
} elseif ($rol === 'admin') {
    $classroom_label = 'Administración';
}
?>

<aside class="sidebar">
    <a class="logo logo-only" href="<?php echo $rol === 'admin' ? 'admin_dashboard.php' : ($rol === 'tutor' ? 'tutor_dashboard.php' : 'estudiante_dashboard.php'); ?>" aria-label="Inicio">
        <img src="assets/logo.png" alt="" class="logo-img">
    </a>
    <div class="user-box">
        <?php if ($foto_perfil !== ''): ?>
            <img class="user-avatar" src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto de perfil">
        <?php else: ?>
            <div class="user-avatar user-avatar-fallback"><?php echo strtoupper(substr($nombre_usuario, 0, 1)); ?></div>
        <?php endif; ?>
        <div class="user-meta">
            <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong>
            <small><?php echo htmlspecialchars(ucfirst($rol)); ?></small>
            <small>Grado: <?php echo htmlspecialchars($classroom_label); ?></small>
        </div>
    </div>

    <nav class="menu">
        <?php if ($rol === 'admin'): ?>
            <a href="admin_dashboard.php" class="<?php echo esActivo('admin_dashboard.php', $pagina_actual); ?>">Inicio</a>
            <a href="profesores.php" class="<?php echo esActivo(['profesores.php', 'crear_profesora.php'], $pagina_actual); ?>">Profesores</a>
            <a href="estudiante.php" class="<?php echo esActivo('estudiante.php', $pagina_actual); ?>">Estudiantes</a>
            <a href="tutores.php" class="<?php echo esActivo('tutores.php', $pagina_actual); ?>">Tutores</a>
            <a href="grados.php" class="<?php echo esActivo(['grados.php', 'procesar_grado.php'], $pagina_actual); ?>">Cursos / Grados</a>
            <a href="clases.php" class="<?php echo esActivo(['clases.php', 'editar_clase.php', 'detalle_clase.php'], $pagina_actual); ?>">Clases</a>
            <a href="materiales.php" class="<?php echo esActivo(['materiales.php', 'editar_material.php'], $pagina_actual); ?>">Materiales</a>
            <a href="tarea.php" class="<?php echo esActivo(['tarea.php', 'editar_tarea.php'], $pagina_actual); ?>">Tareas</a>
            <a href="anuncios.php" class="<?php echo esActivo(['anuncios.php', 'editar_anuncio.php', 'detalle_anuncio.php'], $pagina_actual); ?>">Anuncios</a>
            <a href="calendario.php" class="<?php echo esActivo(['calendario.php', 'editar_evento.php'], $pagina_actual); ?>">Calendario</a>
            <a href="excusas.php" class="<?php echo esActivo(['excusas.php', 'ver_excusa.php'], $pagina_actual); ?>">Excusas</a>
            <a href="perfil.php" class="<?php echo esActivo('perfil.php', $pagina_actual); ?>">Mi perfil</a>
        <?php elseif ($rol === 'estudiante'): ?>
            <a href="estudiante_dashboard.php" class="<?php echo esActivo('estudiante_dashboard.php', $pagina_actual); ?>">Inicio</a>
            <a href="ver_clases.php" class="<?php echo esActivo(['ver_clases.php', 'detalle_clase.php', 'unirse_clase.php'], $pagina_actual); ?>">Mis clases</a>
            <a href="ver_materiales.php" class="<?php echo esActivo(['ver_materiales.php', 'detalle_material.php'], $pagina_actual); ?>">Materiales</a>
            <a href="ver_tarea.php" class="<?php echo esActivo(['ver_tarea.php', 'detalle_tarea.php'], $pagina_actual); ?>">Tareas</a>
            <a href="ver_anuncios.php" class="<?php echo esActivo(['ver_anuncios.php', 'detalle_anuncio.php'], $pagina_actual); ?>">Anuncios</a>
            <a href="ver_calendario.php" class="<?php echo esActivo('ver_calendario.php', $pagina_actual); ?>">Calendario</a>
            <a href="perfil.php" class="<?php echo esActivo('perfil.php', $pagina_actual); ?>">Mi perfil</a>
        <?php elseif ($rol === 'tutor'): ?>
            <a href="tutor_dashboard.php" class="<?php echo esActivo('tutor_dashboard.php', $pagina_actual); ?>">Inicio</a>
            <a href="ver_tarea.php" class="<?php echo esActivo(['ver_tarea.php', 'detalle_tarea.php'], $pagina_actual); ?>">Tareas</a>
            <a href="ver_calendario.php" class="<?php echo esActivo('ver_calendario.php', $pagina_actual); ?>">Calendario</a>
            <a href="ver_anuncios.php" class="<?php echo esActivo(['ver_anuncios.php', 'detalle_anuncio.php'], $pagina_actual); ?>">Anuncios</a>
            <a href="mis_excusas.php" class="<?php echo esActivo('mis_excusas.php', $pagina_actual); ?>">Excusas</a>
            <a href="perfil.php" class="<?php echo esActivo('perfil.php', $pagina_actual); ?>">Mi perfil</a>
        <?php endif; ?>

        <?php if ($rol !== ''): ?>
            <a href="logout.php" class="logout">Cerrar sesión</a>
        <?php endif; ?>
    </nav>
</aside>
