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
        function evaluarRequisitos(clave) {
            return {
                longitud: /.{8,}/.test(clave),
                mayuscula: /[A-Z]/.test(clave),
                minuscula: /[a-z]/.test(clave),
                numero: /[0-9]/.test(clave),
                especial: /[\\W_]/.test(clave),
            };
        }

        function actualizarUIRequisitos() {
            const clave = document.getElementById("nueva_clave").value || "";
            const cont = document.getElementById("requisitos_clave_nueva");
            const req = evaluarRequisitos(clave);

            if (clave.length > 0) cont.classList.add("mostrar");
            else cont.classList.remove("mostrar");

            const map = [
                ["req_longitud_nueva", req.longitud, "Mínimo 8 caracteres"],
                ["req_mayuscula_nueva", req.mayuscula, "Una mayúscula"],
                ["req_minuscula_nueva", req.minuscula, "Una minúscula"],
                ["req_numero_nueva", req.numero, "Un número"],
                ["req_especial_nueva", req.especial, "Un carácter especial (@$!%*?&)"],
            ];

            map.forEach(([id, ok, label]) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.toggle("req-valido", !!ok);
                el.classList.toggle("req-invalido", !ok);
                el.textContent = (ok ? "OK - " : "NO - ") + label;
            });
        }

        function actualizarCoincidencia() {
            const clave = document.getElementById("nueva_clave").value || "";
            const conf = document.getElementById("confirmar_clave").value || "";
            const el = document.getElementById("req_confirm_nueva");
            if (!el) return;
            const ok = conf.length > 0 && conf === clave;
            el.classList.toggle("req-valido", ok);
            el.classList.toggle("req-invalido", !ok);
            el.textContent = (ok ? "OK - " : "NO - ") + "Las contraseñas coinciden";
        }

        function validarClave(event) {
            const clave = document.getElementById("nueva_clave").value || "";
            const claveConfirm = document.getElementById("confirmar_clave").value || "";
            const req = evaluarRequisitos(clave);
            const esSegura = req.longitud && req.mayuscula && req.minuscula && req.numero && req.especial;

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

        document.addEventListener("DOMContentLoaded", function() {
            const clave = document.getElementById("nueva_clave");
            const conf = document.getElementById("confirmar_clave");
            if (clave) clave.addEventListener("input", function() { actualizarUIRequisitos(); actualizarCoincidencia(); });
            if (conf) conf.addEventListener("input", actualizarCoincidencia);

            document.addEventListener("click", function(e) {
                const btn = e.target.closest(".toggle-password");
                if (!btn) return;
                const id = btn.getAttribute("data-target");
                const input = document.getElementById(id);
                if (!input) return;
                const mostrando = input.getAttribute("type") === "text";
                input.setAttribute("type", mostrando ? "password" : "text");
                btn.textContent = mostrando ? "Mostrar" : "Ocultar";
            });
        });
    </script>
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

            <form class="formulario activo" action="procesar_nueva_clave.php" method="POST" onsubmit="validarClave(event)">
                <h2>Ingresa tu nueva clave</h2>
                <p>Usa 8+ caracteres e incluye mayúscula, minúscula, número y carácter especial.</p>

                <div class="grupo">
                    <label for="nueva_clave">Nueva Contraseña</label>
                    <div class="password-field">
                        <input type="password" id="nueva_clave" name="nueva_clave" required autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="nueva_clave" aria-label="Mostrar contraseña">Mostrar</button>
                    </div>
                    <div class="requisitos-clave" id="requisitos_clave_nueva" aria-live="polite">
                        <div id="req_longitud_nueva" class="req-invalido">NO - Mínimo 8 caracteres</div>
                        <div id="req_mayuscula_nueva" class="req-invalido">NO - Una mayúscula</div>
                        <div id="req_minuscula_nueva" class="req-invalido">NO - Una minúscula</div>
                        <div id="req_numero_nueva" class="req-invalido">NO - Un número</div>
                        <div id="req_especial_nueva" class="req-invalido">NO - Un carácter especial (@$!%*?&)</div>
                    </div>
                </div>

                <div class="grupo">
                    <label for="confirmar_clave">Confirmar Contraseña</label>
                    <div class="password-field">
                        <input type="password" id="confirmar_clave" name="confirmar_clave" required autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="confirmar_clave" aria-label="Mostrar contraseña">Mostrar</button>
                    </div>
                    <div class="requisitos-clave mostrar" aria-live="polite">
                        <div id="req_confirm_nueva" class="req-invalido">NO - Las contraseñas coinciden</div>
                    </div>
                </div>

                <button type="submit" class="btn-principal">Guardar Contraseña</button>
            </form>
        </section>
    </main>
</body>
</html>

