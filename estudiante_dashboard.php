<?php
require_once "auth.php";
require_once "conexion.php";
protegerPagina("estudiante");

$usuario_id = intval($_SESSION['usuario_id'] ?? 0);
$grado_id = 0;
$nombre_grado = "Sin grado";

$stmt_grado = $conexion->prepare("SELECT e.grado_id, g.nombre FROM estudiantes e LEFT JOIN grados g ON g.id = e.grado_id WHERE e.usuario_id = ? LIMIT 1");
$stmt_grado->bind_param("i", $usuario_id);
$stmt_grado->execute();
$res_grado = $stmt_grado->get_result();
if ($res_grado && $res_grado->num_rows > 0) {
    $row = $res_grado->fetch_assoc();
    $grado_id = intval($row['grado_id']);
    if (!empty($row['nombre'])) $nombre_grado = $row['nombre'];
}

$tot_clases = 0;
$tot_tareas = 0;
$tot_materiales = 0;
$tot_anuncios = 0;

if ($grado_id > 0) {
    $stmt = $conexion->prepare("SELECT COUNT(*) total FROM clases WHERE grado_id = ?");
    $stmt->bind_param("i", $grado_id); $stmt->execute(); $tot_clases = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt = $conexion->prepare("SELECT COUNT(*) total FROM tareas WHERE grado_id = ?");
    $stmt->bind_param("i", $grado_id); $stmt->execute(); $tot_tareas = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt = $conexion->prepare("SELECT COUNT(*) total FROM materiales WHERE grado_id = ?");
    $stmt->bind_param("i", $grado_id); $stmt->execute(); $tot_materiales = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt = $conexion->prepare("SELECT COUNT(*) total FROM anuncios WHERE para_todos = 1 OR grado_id = ?");
    $stmt->bind_param("i", $grado_id); $stmt->execute(); $tot_anuncios = intval($stmt->get_result()->fetch_assoc()['total'] ?? 0);
} else {
    $tot_anuncios = intval(($conexion->query("SELECT COUNT(*) total FROM anuncios WHERE para_todos = 1")->fetch_assoc()['total'] ?? 0));
}

$page_title = "Panel Estudiante - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
            <p>Tu aula actual: <strong><?php echo htmlspecialchars($nombre_grado); ?></strong>. Aquí tienes tu resumen académico.</p>
        </section>

        <section class="cards" style="margin-bottom:18px;">
            <div class="card"><h3><?php echo $tot_clases; ?></h3><p>Clases de tu grado</p></div>
            <div class="card"><h3><?php echo $tot_materiales; ?></h3><p>Materiales disponibles</p></div>
            <div class="card"><h3><?php echo $tot_tareas; ?></h3><p>Tareas asignadas</p></div>
            <div class="card"><h3><?php echo $tot_anuncios; ?></h3><p>Anuncios para ti</p></div>
        </section>

        <section class="cards">
            <a href="ver_clases.php" class="card"><h3>Mis clases</h3><p>Revisa clases publicadas para tu grado.</p></a>
            <a href="ver_materiales.php" class="card"><h3>Materiales</h3><p>Consulta archivos de estudio de tu grado.</p></a>
            <a href="ver_tarea.php" class="card"><h3>Tareas</h3><p>Verifica entregas y actividades pendientes.</p></a>
            <a href="ver_anuncios.php" class="card"><h3>Anuncios</h3><p>Comunicados generales y de tu grado.</p></a>
        </section>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
