<?php
require_once "auth.php";
require_once "conexion.php";
protegerPagina("admin");

$tot_estudiantes = intval(($conexion->query("SELECT COUNT(*) total FROM estudiantes")->fetch_assoc()['total'] ?? 0));
$tot_tutores = intval(($conexion->query("SELECT COUNT(*) total FROM tutores")->fetch_assoc()['total'] ?? 0));
$tot_grados = intval(($conexion->query("SELECT COUNT(*) total FROM grados")->fetch_assoc()['total'] ?? 0));
$tot_clases = intval(($conexion->query("SELECT COUNT(*) total FROM clases")->fetch_assoc()['total'] ?? 0));
$tot_excusas_pendientes = intval(($conexion->query("SELECT COUNT(*) total FROM excusas WHERE estado = 'Pendiente'")->fetch_assoc()['total'] ?? 0));

$page_title = "Panel Profesora - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Panel de administración</h1>
            <p>Bienvenida, <?php echo htmlspecialchars($_SESSION['nombre']); ?>. Aquí tienes el estado general del aula y accesos rápidos.</p>
        </section>

        <section class="cards" style="margin-bottom:18px;">
            <div class="card"><h3><?php echo $tot_estudiantes; ?></h3><p>Estudiantes registrados</p></div>
            <div class="card"><h3><?php echo $tot_tutores; ?></h3><p>Tutores registrados</p></div>
            <div class="card"><h3><?php echo $tot_grados; ?></h3><p>Cursos/Grados activos</p></div>
            <div class="card"><h3><?php echo $tot_clases; ?></h3><p>Clases publicadas</p></div>
        </section>

        <section class="tabla-contenedor">
            <div class="tabla-header"><h2>Acciones prioritarias</h2></div>
            <div class="cards">
                <a href="excusas.php" class="card">
                    <h3>Excusas pendientes</h3>
                    <p>Tienes <?php echo $tot_excusas_pendientes; ?> por revisar.</p>
                </a>
                <a href="clases.php" class="card">
                    <h3>Publicar clase</h3>
                    <p>Crea una clase y asígnala al grado correspondiente.</p>
                </a>
                <a href="anuncios.php" class="card">
                    <h3>Enviar anuncio</h3>
                    <p>Publica anuncios generales o por grado.</p>
                </a>
                <a href="grados.php" class="card">
                    <h3>Gestionar grados</h3>
                    <p>Administra nombre y código de acceso por grado.</p>
                </a>
            </div>
        </section>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
