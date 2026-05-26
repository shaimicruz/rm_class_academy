<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("estudiante,tutor");

$mensaje = "";
$grado_id = null;
$nombre_grado = "";
$tareas = null;

// Limpia el nombre de la tabla por seguridad
function limpiarTabla($tabla) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);
}

// Verificar si una columna existe en una tabla
function columnaExiste($conexion, $tabla, $columna) {
    $tabla = limpiarTabla($tabla);

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
function buscarColumna($conexion, $tabla, $columnas) {
    foreach ($columnas as $columna) {
        if (columnaExiste($conexion, $tabla, $columna)) {
            return $columna;
        }
    }

    return null;
}

// Buscar dato dentro de la sesión
function buscarSesion($llaves) {
    foreach ($llaves as $llave) {
        if (isset($_SESSION[$llave]) && $_SESSION[$llave] != "") {
            return $_SESSION[$llave];
        }
    }

    return null;
}

// Nombre del grado
function obtenerNombreGrado($grado) {
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
$grado_id = buscarSesion(["grado_id", "id_grado", "estudiante_grado_id"]);

// 2. Si no está en la sesión, buscarlo en la tabla estudiantes
if ($grado_id == null) {

    $id_usuario = buscarSesion(["usuario_id", "id_usuario", "user_id", "id"]);
    $correo_usuario = buscarSesion(["correo", "email", "usuario_email"]);

    $columna_grado = buscarColumna($conexion, "estudiantes", ["grado_id", "id_grado"]);

    if ($columna_grado != null) {

        $columna_usuario = buscarColumna($conexion, "estudiantes", ["usuario_id", "id_usuario", "user_id", "id_user"]);

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
            $columna_correo = buscarColumna($conexion, "estudiantes", ["correo", "email"]);

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

        if ($grado_id == null && $id_usuario != null && columnaExiste($conexion, "estudiantes", "id")) {
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
        $nombre_grado = obtenerNombreGrado($grado);
    }
}

// Buscar tareas del grado del estudiante
if ($grado_id != null) {
    $sql_tareas = "SELECT * FROM tareas 
                   WHERE grado_id = ? 
                   ORDER BY fecha_entrega ASC, fecha_creacion DESC";

    $stmt_tareas = $conexion->prepare($sql_tareas);
    $stmt_tareas->bind_param("i", $grado_id);
    $stmt_tareas->execute();
    $tareas = $stmt_tareas->get_result();
} else {
    $mensaje = "No se pudo identificar el grado del estudiante.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis tareas - R.M CLASS ACADEMY</title>
    <link rel="stylesheet" href="css_dashboard.css?v=<?php echo time(); ?>">

    <style>
        .mensaje-tareas {
            background: rgba(160, 30, 30, 0.13);
            color: #7a1111;
            padding: 15px 17px;
            border-radius: 16px;
            margin-bottom: 22px;
            font-weight: 900;
        }

        .tarea-info {
            margin-top: 14px;
            color: var(--azul-medio);
            line-height: 1.6;
        }

        .tarea-fecha {
            margin-top: 14px;
            font-weight: 900;
            color: #7a1111;
            background: rgba(160, 30, 30, 0.10);
            padding: 9px 12px;
            border-radius: 12px;
            display: inline-block;
        }

        .btn-ver-tarea {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--azul-oscuro), #1e4a8f);
            color: white;
            text-decoration: none;
            padding: 11px 20px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 14px;
            margin-top: 18px;
            box-shadow: 0 8px 20px rgba(18,41,84,0.20);
            transition: all 0.3s ease;
        }

        .btn-ver-tarea:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(18,41,84,0.30);
        }
    </style>
</head>

<body>

<div class="layout">

    <?php require_once 'includes/navbar.php'; ?>
<main class="contenido">

        <section class="header">
            <h1>Mis tareas</h1>

            <?php if ($nombre_grado != "") { ?>
                <p>Tareas asignadas para: <?php echo htmlspecialchars($nombre_grado); ?>.</p>
            <?php } else { ?>
                <p>Aquí podrás ver las tareas publicadas por la profesora.</p>
            <?php } ?>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-tareas">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php } ?>

        <section class="cards">

            <?php if ($tareas != null && $tareas->num_rows > 0) { ?>

                <?php while ($tarea = $tareas->fetch_assoc()) { ?>

                    <div class="card">
                        <div class="icono"></div>

                        <h3><?php echo htmlspecialchars($tarea['titulo']); ?></h3>

                        <p class="tarea-fecha">
                             Fecha de entrega: <?php echo htmlspecialchars($tarea['fecha_entrega']); ?>
                        </p>

                        <p class="tarea-info">
                            <?php echo nl2br(htmlspecialchars(mb_substr($tarea['descripcion'], 0, 120))); ?>
                            <?php if (mb_strlen($tarea['descripcion']) > 120) echo "..."; ?>
                        </p>

                        <a class="btn-ver-tarea" href="detalle_tarea.php?id=<?php echo $tarea['id']; ?>">
                             Ver tarea completa
                        </a>
                    </div>

                <?php } ?>

            <?php } else { ?>

                <?php if ($mensaje == "") { ?>
                    <div class="card">
                        <div class="icono"></div>
                        <h3>No tienes tareas asignadas todavía</h3>
                        <p>Cuando la profesora publique una tarea para tu grado, aparecerá aquí.</p>
                    </div>
                <?php } ?>

            <?php } ?>

        </section>

    </main>

</div>

</body>
</html>





