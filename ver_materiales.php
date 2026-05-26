<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("estudiante");

$mensaje = "";
$grado_id = null;
$nombre_grado = "";
$materiales = null;

// Limpia el nombre de la tabla por seguridad
function limpiarTablaMaterial($tabla) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
}

// Verificar si una columna existe en una tabla
function columnaExisteMaterial($conexion, $tabla, $columna) {
    $tabla = limpiarTablaMaterial($tabla);

    $sql = "SHOW COLUMNS FROM `$tabla`";
    $resultado = $conexion->query($sql);

    if (!$resultado) {
        return false;
    }

    while ($fila = $resultado->fetch_assoc()) {
        if ($fila['Field'] == $columna) {
            return true;
        }
    }

    return false;
}

// Buscar una columna posible dentro de una tabla
function buscarColumnaMaterial($conexion, $tabla, $columnas) {
    foreach ($columnas as $columna) {
        if (columnaExisteMaterial($conexion, $tabla, $columna)) {
            return $columna;
        }
    }

    return null;
}

// Buscar dato dentro de la sesión
function buscarSesionMaterial($llaves) {
    foreach ($llaves as $llave) {
        if (isset($_SESSION[$llave]) && $_SESSION[$llave] != "") {
            return $_SESSION[$llave];
        }
    }

    return null;
}

// Obtener nombre del grado
function obtenerNombreGradoMaterial($grado) {
    if (isset($grado['nombre_grado'])) {
        return $grado['nombre_grado'];
    } elseif (isset($grado['nombre'])) {
        return $grado['nombre'];
    } elseif (isset($grado['grado'])) {
        return $grado['grado'];
    } else {
        return "Grado " . $grado['id'];
    }
}

// 1. Buscar el grado directamente desde la sesión
$grado_id = buscarSesionMaterial(["grado_id", "id_grado"]);

// 2. Si no está en la sesión, buscarlo en la tabla estudiantes
if ($grado_id == null) {

    $id_usuario = buscarSesionMaterial(["usuario_id", "id_usuario", "user_id", "id"]);
    $correo_usuario = buscarSesionMaterial(["correo", "email", "usuario_email"]);

    $columna_grado = buscarColumnaMaterial($conexion, "estudiantes", ["grado_id", "id_grado"]);

    if ($columna_grado != null) {

        $columna_usuario = buscarColumnaMaterial($conexion, "estudiantes", ["usuario_id", "id_usuario", "user_id", "id_user"]);

        if ($id_usuario != null && $columna_usuario != null) {
            $sql_estudiante = "SELECT `$columna_grado` FROM estudiantes WHERE `$columna_usuario` = ? LIMIT 1";
            $stmt_estudiante = $conexion->prepare($sql_estudiante);
            $stmt_estudiante->bind_param("i", $id_usuario);
            $stmt_estudiante->execute();
            $resultado_estudiante = $stmt_estudiante->get_result();

            if ($resultado_estudiante->num_rows > 0) {
                $estudiante = $resultado_estudiante->fetch_assoc();
                $grado_id = $estudiante[$columna_grado];
            }
        }

        if ($grado_id == null && $correo_usuario != null) {
            $columna_correo = buscarColumnaMaterial($conexion, "estudiantes", ["correo", "email"]);

            if ($columna_correo != null) {
                $sql_estudiante = "SELECT `$columna_grado` FROM estudiantes WHERE `$columna_correo` = ? LIMIT 1";
                $stmt_estudiante = $conexion->prepare($sql_estudiante);
                $stmt_estudiante->bind_param("s", $correo_usuario);
                $stmt_estudiante->execute();
                $resultado_estudiante = $stmt_estudiante->get_result();

                if ($resultado_estudiante->num_rows > 0) {
                    $estudiante = $resultado_estudiante->fetch_assoc();
                    $grado_id = $estudiante[$columna_grado];
                }
            }
        }

        if ($grado_id == null && $id_usuario != null && columnaExisteMaterial($conexion, "estudiantes", "id")) {
            $sql_estudiante = "SELECT `$columna_grado` FROM estudiantes WHERE id = ? LIMIT 1";
            $stmt_estudiante = $conexion->prepare($sql_estudiante);
            $stmt_estudiante->bind_param("i", $id_usuario);
            $stmt_estudiante->execute();
            $resultado_estudiante = $stmt_estudiante->get_result();

            if ($resultado_estudiante->num_rows > 0) {
                $estudiante = $resultado_estudiante->fetch_assoc();
                $grado_id = $estudiante[$columna_grado];
            }
        }
    }
}

// Buscar nombre del grado
if ($grado_id != null) {
    $sql_grado = "SELECT * FROM grados WHERE id = ? LIMIT 1";
    $stmt_grado = $conexion->prepare($sql_grado);
    $stmt_grado->bind_param("i", $grado_id);
    $stmt_grado->execute();
    $resultado_grado = $stmt_grado->get_result();

    if ($resultado_grado->num_rows > 0) {
        $grado = $resultado_grado->fetch_assoc();
        $nombre_grado = obtenerNombreGradoMaterial($grado);
    }
}

// Buscar materiales del grado del estudiante
if ($grado_id != null) {
    $sql_materiales = "SELECT * FROM materiales 
                       WHERE grado_id = ? 
                       ORDER BY fecha_publicacion DESC, id DESC";

    $stmt_materiales = $conexion->prepare($sql_materiales);
    $stmt_materiales->bind_param("i", $grado_id);
    $stmt_materiales->execute();
    $materiales = $stmt_materiales->get_result();
} else {
    $mensaje = "No se pudo identificar el grado del estudiante.";
}
?>
<?php
$page_title = "Mis materiales - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>
<style>
        .mensaje-materiales {
            background: rgba(160, 30, 30, 0.13);
            color: #7a1111;
            padding: 15px 17px;
            border-radius: 16px;
            margin-bottom: 22px;
            font-weight: 900;
        }

        .material-info {
            margin-top: 14px;
            color: var(--azul-medio);
            line-height: 1.6;
        }

        .material-fecha {
            margin-top: 14px;
            font-weight: 900;
            color: var(--azul-oscuro);
        }
    </style>

<div class="layout">

    <?php require_once 'includes/navbar.php'; ?>
<main class="contenido">

        <section class="header">
            <h1>Mis materiales</h1>

            <?php if ($nombre_grado != "") { ?>
                <p>Materiales disponibles para: <?php echo htmlspecialchars($nombre_grado); ?>.</p>
            <?php } else { ?>
                <p>Aquí podrás ver los materiales publicados por la profesora.</p>
            <?php } ?>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-materiales">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php } ?>

        <section class="cards">

            <?php if ($materiales != null && $materiales->num_rows > 0) { ?>

                <?php while ($material = $materiales->fetch_assoc()) { ?>

                    <div class="card">
                        <div class="icono"></div>

                        <h3><?php echo htmlspecialchars($material['titulo']); ?></h3>

                        <p class="material-fecha">
                            Publicado: <?php echo htmlspecialchars($material['fecha_publicacion']); ?>
                        </p>

                        <p class="material-info">
                            <?php echo nl2br(htmlspecialchars($material['descripcion'])); ?>
                        </p>

                        <?php if (!empty($material['archivo'])) { ?>
                            <br>
                            <a class="student-file abrir-archivo-modal"
                               href="uploads/materiales/<?php echo rawurlencode($material['archivo']); ?>">
                                Ver archivo
                            </a>
                        <?php } ?>
                    </div>

                <?php } ?>

            <?php } else { ?>

                <?php if ($mensaje == "") { ?>
                    <div class="card">
                        <div class="icono"></div>
                        <h3>No tienes materiales disponibles todavía</h3>
                        <p>Cuando la profesora publique materiales para tu grado, aparecerán aquí.</p>
                    </div>
                <?php } ?>

            <?php } ?>

        </section>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>



