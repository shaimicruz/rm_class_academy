<?php
require_once "auth.php";
require_once "conexion.php";
protegerPagina("tutor");

$usuario_id = intval($_SESSION['usuario_id'] ?? 0);
$estudiante_nombre = "Sin estudiante asignado";
$estudiante_grado = "Sin grado";
$grado_id = intval($_SESSION['estudiante_grado_id'] ?? 0);
$estudiante_id = intval($_SESSION['estudiante_id'] ?? 0);

if ($estudiante_id > 0) {
    $stmt_est = $conexion->prepare("
        SELECT u.nombre, u.apellido, g.nombre AS grado, e.grado_id
        FROM estudiantes e
        INNER JOIN usuarios u ON e.usuario_id = u.id
        LEFT JOIN grados g ON e.grado_id = g.id
        WHERE e.id = ? LIMIT 1
    ");
    $stmt_est->bind_param("i", $estudiante_id);
    $stmt_est->execute();
    $res_est = $stmt_est->get_result();
    if ($res_est && $res_est->num_rows > 0) {
        $row = $res_est->fetch_assoc();
        $estudiante_nombre = trim(($row['nombre'] ?? '') . " " . ($row['apellido'] ?? ''));
        $estudiante_grado = $row['grado'] ?? "Sin grado";
        $grado_id = intval($row['grado_id'] ?? $grado_id);
    }
}

$tot_tareas = 0;
$tot_materiales = 0;
$tot_anuncios = 0;
$tot_excusas = 0;

if ($grado_id > 0) {
    $stmt = $conexion->prepare("SELECT COUNT(*) total FROM tareas WHERE grado_id = ?");
    $stmt->bind_param("i", $grado_id); $stmt->execute(); $tot_tareas = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt = $conexion->prepare("SELECT COUNT(*) total FROM materiales WHERE grado_id = ?");
    $stmt->bind_param("i", $grado_id); $stmt->execute(); $tot_materiales = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt = $conexion->prepare("SELECT COUNT(*) total FROM anuncios WHERE para_todos = 1 OR grado_id = ?");
    $stmt->bind_param("i", $grado_id); $stmt->execute(); $tot_anuncios = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);
}

$stmt = $conexion->prepare("SELECT COUNT(*) total FROM excusas WHERE tutor_usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$tot_excusas = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);

$page_title = "Panel Tutor - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Panel del tutor</h1>
            <p>Seguimiento de <strong><?php echo htmlspecialchars($estudiante_nombre); ?></strong> (<?php echo htmlspecialchars($estudiante_grado); ?>).</p>
        </section>

        <section class="cards" style="margin-bottom:18px;">
            <div class="card"><h3><?php echo $tot_tareas; ?></h3><p>Tareas del estudiante</p></div>
            <div class="card"><h3><?php echo $tot_materiales; ?></h3><p>Materiales del grado</p></div>
            <div class="card"><h3><?php echo $tot_anuncios; ?></h3><p>Anuncios disponibles</p></div>
            <div class="card"><h3><?php echo $tot_excusas; ?></h3><p>Excusas enviadas</p></div>
        </section>

        <section class="cards">
            <a href="ver_tarea.php" class="card"><h3>Tareas</h3><p>Consultar actividades y fechas de entrega.</p></a>
            <a href="ver_anuncios.php" class="card"><h3>Anuncios</h3><p>Comunicados generales y del grado.</p></a>
            <a href="ver_calendario.php" class="card"><h3>Calendario</h3><p>Eventos y fechas importantes del centro.</p></a>
            <a href="mis_excusas.php" class="card"><h3>Excusas</h3><p>Enviar y dar seguimiento a excusas.</p></a>
        </section>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
