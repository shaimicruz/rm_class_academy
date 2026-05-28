<?php
session_start();

if (!isset($_SESSION['correo_verificacion'])) {
    header("Location: index.php");
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
                    <img class="logo-imagen" src="assets/logo.png" alt="R.M CLASS ACADEMY">
                </a>
                <p class="sr-only">R.M CLASS ACADEMY</p>
            </div>

            <?php if (isset($_GET['pendiente'])) { ?>
                <div class="mensaje-error">Debes verificar tu correo antes de iniciar sesión.</div>
            <?php } ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'codigo') { ?>
                <div class="mensaje-error">El código ingresado es incorrecto. Inténtalo de nuevo.</div>
            <?php } ?>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'envio') { ?>
                <div class="mensaje-error">No se pudo enviar el correo. Inténtalo de nuevo.</div>
            <?php } ?>
            <?php if (isset($_GET['reenviado'])) { ?>
                <div class="mensaje-exito">Código reenviado. Revisa tu correo.</div>
            <?php } ?>

            <form class="formulario activo" action="procesar_codigo_registro.php" method="POST">
                <h2>Verifica tu cuenta</h2>
                <p>Enviamos un código de 6 dígitos a <strong><?php echo htmlspecialchars($_SESSION['correo_verificacion']); ?></strong>.</p>

                <div class="grupo">
                    <label for="codigo">Código de 6 dígitos</label>
                    <input type="text" id="codigo" name="codigo" placeholder="Ej: 123456" maxlength="6" required style="text-align:center;font-size:20px;letter-spacing:5px;">
                </div>

                <button type="submit" class="btn-principal">Verificar</button>

                <div class="nota" style="margin-top:20px;">
                    <a href="enviar_codigo_registro.php" style="color: var(--azul-oscuro); font-weight: 600; text-decoration: none;">Reenviar código</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>

