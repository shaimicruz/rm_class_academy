<?php
session_start();

if (!isset($_SESSION['correo_recuperacion'])) {
    header("Location: recuperar_clave.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar Código - R.M CLASS ACADEMY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stayle.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <main class="contenedor-principal">
        <section class="panel-formulario">
            <div class="logo-area-minimalista">
                <a href="index.php" aria-label="Ir al inicio">
                    <span class="logo-imagen logo-marca" aria-hidden="true">RM</span>
                </a>
                <h1>R.M CLASS ACADEMY</h1>
                <p>Verificación de Código</p>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'codigo') { ?>
                <div class="mensaje-error">
                    El código ingresado es incorrecto. Inténtalo de nuevo.
                </div>
            <?php } ?>

            <form class="formulario activo" action="procesar_codigo.php" method="POST">
                <h2>Ingresa tu código</h2>
                <p>Hemos enviado un código de 6 dígitos a <strong><?php echo htmlspecialchars($_SESSION['correo_recuperacion']); ?></strong>.</p>


                <div class="grupo">
                    <label for="codigo">Código de 6 dígitos</label>
                    <input type="text" id="codigo" name="codigo" placeholder="Ej: 123456" maxlength="6" required style="text-align: center; font-size: 20px; letter-spacing: 5px;">
                </div>

                <button type="submit" class="btn-principal">Verificar código</button>
                
                <div class="nota" style="margin-top: 20px;">
                    <a href="recuperar_clave.php" style="color: var(--azul-oscuro); font-weight: 600; text-decoration: none;">Cambiar correo</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
