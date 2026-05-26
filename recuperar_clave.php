<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - R.M CLASS ACADEMY</title>
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
                <p>Recuperación de Contraseña</p>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'correo') { ?>
                <div class="mensaje-error">
                    El correo ingresado no está registrado en el sistema.
                </div>
            <?php } ?>

            <?php if (isset($_GET['error']) && $_GET['error'] == 'envio') { ?>
                <div class="mensaje-error">
                    Hubo un problema enviando el correo. Inténtalo de nuevo más tarde.
                </div>
            <?php } ?>

            <form class="formulario activo" action="enviar_codigo.php" method="POST">
                <h2>¿Olvidaste tu contraseña?</h2>
                <p>Ingresa tu correo electrónico y te enviaremos un código de 6 dígitos para recuperar tu acceso.</p>

                <div class="grupo">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
                </div>

                <button type="submit" class="btn-principal">Enviar código</button>
                
                <div class="nota" style="margin-top: 20px;">
                    <a href="index.php" style="color: var(--azul-oscuro); font-weight: 600; text-decoration: none;">Volver al inicio</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
