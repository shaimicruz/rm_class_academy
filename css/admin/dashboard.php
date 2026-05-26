<?php
require_once "../config/auth.php";
protegerPagina("admin");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Profesora - R.M CLASS ACADEMY</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <div class="logo"><span>R.M</span> CLASS</div>

        <nav class="menu">
            <a href="#">Inicio</a>
            <a href="#">Estudiantes</a>
            <a href="#">Tutores</a>
            <a href="#">Clases</a>
            <a href="#">Materiales</a>
            <a href="#">Tareas</a>
            <a href="#">Anuncios</a>
            <a href="#">Excusas</a>
            <a href="../logout.php" class="logout">Cerrar sesión</a>
        </nav>
    </aside>

    <main class="contenido">
        <section class="header">
            <h1>Panel de la Profesora</h1>
            <p>Bienvenida, <?php echo $_SESSION['nombre']; ?>. Desde aquí podrás controlar toda el aula virtual.</p>
        </section>

        <section class="cards">
            <div class="card">
                <h3>Estudiantes</h3>
                <p>Gestionar estudiantes registrados.</p>
            </div>

            <div class="card">
                <h3>Clases</h3>
                <p>Crear y revisar clases del día.</p>
            </div>

            <div class="card">
                <h3>Tareas</h3>
                <p>Publicar tareas y actividades.</p>
            </div>

            <div class="card">
                <h3>Excusas</h3>
                <p>Revisar excusas enviadas.</p>
            </div>
        </section>
    </main>

</div>

</body>
</html>