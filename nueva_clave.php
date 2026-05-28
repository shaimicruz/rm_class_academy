<?php
session_start();

if (!isset($_SESSION['codigo_verificado']) || $_SESSION['codigo_verificado'] !== true) {
    header("Location: recuperar_clave.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña - R.M CLASS ACADEMY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stayle.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <script>
        function validarClave(event) {
            const clave = document.getElementById("nueva_clave").value;
            const claveConfirm = document.getElementById("confirmar_clave").value;

            const esSegura =
                clave.length >= 8 &&
                /[A-Z]/.test(clave) &&
                /[a-z]/.test(clave) &&
                /[0-9]/.test(clave) &&
                /[^A-Za-z0-9]/.test(clave);

            if (!esSegura) {
                alert("La contraseña debe tener 8+ caracteres e incluir mayúscula, minúscula, número y carácter especial.");
                event.preventDefault();
                return false;
            }

            if (clave !== claveConfirm) {
                alert("Las contraseñas no coinciden.");
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <main class="contenedor-principal">
        <section class="panel-formulario">
            <div class="logo-area-minimalista">
                <a href="index.php" aria-label="Ir al inicio">
                    <span class="logo-imagen logo-marca" aria-hidden="true">RM</span>
                </a>
                <h1>R.M CLASS ACADEMY</h1>
                <p>Establecer nueva contraseña</p>
            </div>

            <form class="formulario activo" action="procesar_nueva_clave.php" method="POST" onsubmit="validarClave(event)">
                <h2>Ingresa tu nueva clave</h2>
                <p>Usa 8+ caracteres e incluye mayúscula, minúscula, número y carácter especial.</p>

                <div class="grupo">
                    <label for="nueva_clave">Nueva Contraseña</label>
                    <input type="password" id="nueva_clave" name="nueva_clave" required>
                </div>

                <div class="grupo">
                    <label for="confirmar_clave">Confirmar Contraseña</label>
                    <input type="password" id="confirmar_clave" name="confirmar_clave" required>
                </div>

                <button type="submit" class="btn-principal">Guardar Contraseña</button>
            </form>
        </section>
    </main>
</body>
</html>

