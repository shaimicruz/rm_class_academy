<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['rol'] == 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    } elseif ($_SESSION['rol'] == 'estudiante') {
        header("Location: estudiante_dashboard.php");
        exit();
    } elseif ($_SESSION['rol'] == 'tutor') {
        header("Location: tutor_dashboard.php");
        exit();
    }
}
?>

<?php require_once 'includes/header.php'; ?>

    <main class="contenedor-principal">

        <section class="panel-formulario">

            <div class="logo-area-minimalista">
                <a href="index.php" aria-label="Ir al inicio">
                    <img class="logo-imagen" src="assets/logo.png" alt="R.M Class Academy">
                </a>
                <p class="sr-only">R.M CLASS ACADEMY</p>
            </div>

            <div class="tabs">
                <button type="button" class="tab-btn activo" onclick="mostrarFormulario('login', this)">
                    Iniciar sesión
                </button>

                <button type="button" class="tab-btn" onclick="mostrarFormulario('registro', this)">
                    Registrarse
                </button>
            </div>

            <?php if (isset($_GET['error'])) { ?>
                <div class="mensaje-error">
                    Correo o contraseña incorrectos.
                </div>
            <?php } ?>

            <?php if (isset($_GET['registro']) && $_GET['registro'] == 'ok') { ?>
                <div class="mensaje-exito">
                    Registro realizado correctamente. Ya puedes iniciar sesión.
                </div>
            <?php } ?>

            <?php if (isset($_GET['correo']) && $_GET['correo'] == 'existe') { ?>
                <div class="mensaje-error">
                    Ese correo ya está registrado.
                </div>
            <?php } ?>

            <?php if (isset($_GET['error_matricula']) && $_GET['error_matricula'] == '1') { ?>
                <div class="mensaje-error">La matrícula del estudiante no es válida o no existe.</div>
            <?php } ?>
            <?php if (isset($_GET['error_pendiente']) && $_GET['error_pendiente'] == '1') { ?>
                <div class="mensaje-error">Tu cuenta está pendiente de aprobación por la administración.</div>
            <?php } ?>
            <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'clave_actualizada') { ?>
                <div class="mensaje-exito">Contraseña actualizada exitosamente. Ahora puedes iniciar sesión.</div>
            <?php } ?>
            <?php if (isset($_GET['error_codigo'])) { ?>
                <div class="mensaje-error">
                    <?php if ($_GET['error_codigo'] == 'invalido') echo "El código de acceso al grado no es válido."; ?>
                    <?php if ($_GET['error_codigo'] == 'vacio') echo "Debes ingresar el código de acceso de tu grado."; ?>
                </div>
            <?php } ?>

            <!-- LOGIN -->
            <form class="formulario activo" id="login" action="procesar_login.php" method="POST">
                <h2>Bienvenido</h2>
                <p>Accede con tu correo y contraseña.</p>

                <div class="grupo">
                    <label for="correo_login">Correo electrónico</label>
                    <input 
                        type="email" 
                        id="correo_login"
                        name="correo" 
                        placeholder="ejemplo@correo.com" 
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="grupo">
                    <label for="clave_login">Contraseña</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="clave_login"
                            name="clave" 
                            placeholder="Escribe tu contraseña" 
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="clave_login" aria-label="Mostrar contraseña">Mostrar</button>
                    </div>
                </div>

                <button type="submit" class="btn-principal">Entrar al sistema</button>

                <div class="nota" style="margin-top: 15px;">
                    <a href="recuperar_clave.php" style="color: var(--azul-oscuro); font-weight: 600; text-decoration: none;">¿Olvidaste tu contraseña?</a>
                </div>
            </form>

            <form class="formulario" id="registro" action="registrar.php" method="POST">
                <h2>Crear cuenta</h2>
                <p>Regístrate como estudiante o tutor.</p>

                <div class="grupo">
                    <label for="nombre_registro">Nombre completo</label>
                    <input 
                        type="text" 
                        id="nombre_registro"
                        name="nombre" 
                        placeholder="Escribe tu nombre completo" 
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="grupo">
                    <label for="correo_registro">Correo electrónico</label>
                    <input 
                        type="email" 
                        id="correo_registro"
                        name="correo" 
                        placeholder="ejemplo@correo.com" 
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="grupo">
                    <label for="clave_registro">Contraseña</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="clave_registro"
                            name="clave" 
                            placeholder="Crea una contraseña segura" 
                            autocomplete="new-password"
                            required
                            oninput="validarClave()"
                        >
                        <button type="button" class="toggle-password" data-target="clave_registro" aria-label="Mostrar contraseña">Mostrar</button>
                    </div>
                    <div class="requisitos-clave" id="requisitos_clave">
                        <span id="req_longitud" class="req-invalido" data-label="Mínimo 8 caracteres">NO - Mínimo 8 caracteres</span><br>
                        <span id="req_mayuscula" class="req-invalido" data-label="Una mayúscula">NO - Una mayúscula</span><br>
                        <span id="req_minuscula" class="req-invalido" data-label="Una minúscula">NO - Una minúscula</span><br>
                        <span id="req_numero" class="req-invalido" data-label="Un número">NO - Un número</span><br>
                        <span id="req_especial" class="req-invalido" data-label="Un carácter especial (@$!%*?&)">NO - Un carácter especial (@$!%*?&)</span>
                    </div>
                </div>

                <div class="grupo">
                    <label for="rol_registro">Tipo de usuario</label>
                    <select id="rol_registro" name="rol" required onchange="mostrarCampoMatricula()">
                        <option value="">Selecciona una opción</option>
                        <option value="estudiante">Estudiante</option>
                        <option value="tutor">Tutor</option>
                    </select>
                </div>

                <div class="grupo" id="grupo_codigo_acceso" style="display: none;">
                    <label for="codigo_acceso_registro">Codigo de acceso del curso/grado</label>
                    <input
                        type="text"
                        id="codigo_acceso_registro"
                        name="codigo_acceso"
                        placeholder="Ej. A7K9P2QX"
                        maxlength="20"
                        autocomplete="off"
                    >
                </div>

                <div class="grupo" id="grupo_matricula" style="display: none;">
                    <label for="matricula_registro">Matrícula del Estudiante Asignado</label>
                    <input 
                        type="text" 
                        id="matricula_registro"
                        name="matricula_estudiante" 
                        placeholder="Ej. EST-0001" 
                    >
                </div>

                <button type="submit" class="btn-principal">Crear mi cuenta</button>
            </form>

        </section>

    </main>

    <a href="#" class="scroll-to-top" id="scrollToTop" title="Volver arriba">â†‘</a>

    <script>
        function mostrarFormulario(id, boton) {
            document.querySelectorAll('.formulario').forEach(form => {
                form.classList.remove('activo');
            });

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('activo');
            });

            document.getElementById(id).classList.add('activo');
            boton.classList.add('activo');
        }

        function mostrarCampoMatricula() {
            const rol = document.getElementById('rol_registro').value;
            const grupoMatricula = document.getElementById('grupo_matricula');
            const inputMatricula = document.getElementById('matricula_registro');
            const grupoCodigo = document.getElementById('grupo_codigo_acceso');
            const inputCodigo = document.getElementById('codigo_acceso_registro');

            if (rol === 'tutor') {
                grupoMatricula.style.display = 'block';
                inputMatricula.required = true;
            } else {
                grupoMatricula.style.display = 'none';
                inputMatricula.required = false;
            }

            if (rol === 'estudiante') {
                grupoCodigo.style.display = 'block';
                inputCodigo.required = false;
            } else {
                grupoCodigo.style.display = 'none';
                inputCodigo.required = false;
                inputCodigo.value = '';
            }
        }

        document.getElementById('codigo_acceso_registro').addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });

        // Validación de contraseña en tiempo real
        function validarClave() {
            const clave = document.getElementById('clave_registro').value;
            const reqContainer = document.getElementById('requisitos_clave');
            
            if (clave.length > 0) {
                reqContainer.classList.add('mostrar');
            } else {
                reqContainer.classList.remove('mostrar');
            }

            const requisitos = [
                { id: 'req_longitud', regex: /.{8,}/ },
                { id: 'req_mayuscula', regex: /[A-Z]/ },
                { id: 'req_minuscula', regex: /[a-z]/ },
                { id: 'req_numero', regex: /[0-9]/ },
                { id: 'req_especial', regex: /[\W_]/ }
            ];

            requisitos.forEach(req => {
                const elemento = document.getElementById(req.id);
                const ok = req.regex.test(clave);
                const label = elemento?.dataset?.label || '';
                if (ok) {
                    elemento.classList.remove('req-invalido');
                    elemento.classList.add('req-valido');
                } else {
                    elemento.classList.remove('req-valido');
                    elemento.classList.add('req-invalido');
                }
                if (label) {
                    elemento.textContent = (ok ? 'OK - ' : 'NO - ') + label;
                }
            });
        }

        // Scroll to top functionality
        window.addEventListener('scroll', function() {
            const scrollBtn = document.getElementById('scrollToTop');
            if (window.scrollY > 300) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });

        document.getElementById('scrollToTop').addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.toggle-password');
            if (!btn) return;
            const id = btn.getAttribute('data-target');
            const input = document.getElementById(id);
            if (!input) return;

            const mostrando = input.getAttribute('type') === 'text';
            input.setAttribute('type', mostrando ? 'password' : 'text');
            btn.textContent = mostrando ? 'Mostrar' : 'Ocultar';
        });
    </script>
<?php require_once 'includes/footer.php'; ?>



