<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$mensaje = "";
$tipo_mensaje = "";

// Verificar ID
if (!isset($_GET['id'])) {
    header("Location: excusas.php");
    exit();
}

$id = $_GET['id'];

// Actualizar estado de la excusa y enviar respuesta al tutor
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $estado = $_POST['estado'];
    $respuesta_admin = trim($_POST['respuesta_admin']);

    $sql_update = "UPDATE excusas 
                   SET estado = ?, respuesta_admin = ?, fecha_revision = NOW()
                   WHERE id = ?";

    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("ssi", $estado, $respuesta_admin, $id);

    if ($stmt_update->execute()) {
        $mensaje = "La respuesta fue enviada correctamente al tutor.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al enviar la respuesta.";
        $tipo_mensaje = "error";
    }
}

// Buscar excusa con datos del tutor
$sql = "SELECT 
            excusas.*,
            usuarios.nombre AS tutor_nombre,
            usuarios.apellido AS tutor_apellido,
            usuarios.correo AS tutor_correo,
            usuarios.telefono AS tutor_telefono
        FROM excusas
        INNER JOIN usuarios ON excusas.tutor_usuario_id = usuarios.id
        WHERE excusas.id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: excusas.php");
    exit();
}

$excusa = $resultado->fetch_assoc();

function claseEstadoDetalle($estado) {
    if ($estado == "Aprobada") {
        return "estado-detalle aprobada";
    } elseif ($estado == "Rechazada") {
        return "estado-detalle rechazada";
    } else {
        return "estado-detalle pendiente";
    }
}

function textoEstadoDetalle($estado) {
    if ($estado == "Aprobada") {
        return "Aprobada";
    } elseif ($estado == "Rechazada") {
        return "No aprobada";
    } else {
        return "Pendiente / En revisiÃ³n";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Revisar excusa - R.M CLASS ACADEMY</title>
    <link rel="stylesheet" href="css_dashboard.css?v=<?php echo time(); ?>">

    <style>
        .detalle-grid {
            display: grid;
            grid-template-columns: 1fr 0.9fr;
            gap: 24px;
            align-items: start;
        }

        .detalle-panel {
            background: rgba(255,255,255,0.84);
            border: 1px solid rgba(255,255,255,0.72);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 28px;
            border-radius: 26px;
            box-shadow: 0 18px 45px rgba(26, 45, 66, 0.14);
            margin-bottom: 24px;
        }

        .detalle-panel h2 {
            color: var(--azul-oscuro);
            margin-bottom: 18px;
            font-size: 25px;
        }

        .detalle-item {
            margin-bottom: 18px;
        }

        .detalle-item label {
            display: block;
            font-weight: 900;
            color: var(--azul-oscuro);
            margin-bottom: 6px;
            font-size: 14px;
        }

        .detalle-item p {
            color: var(--azul-medio);
            line-height: 1.6;
            background: rgba(212,216,221,0.45);
            padding: 13px 15px;
            border-radius: 14px;
        }

        .estado-detalle {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 900;
            font-size: 14px;
        }

        .estado-detalle.pendiente {
            background: rgba(138,101,0,0.16);
            color: #8a6500;
        }

        .estado-detalle.aprobada {
            background: rgba(30,120,75,0.16);
            color: #145f3d;
        }

        .estado-detalle.rechazada {
            background: rgba(160,30,30,0.16);
            color: #7a1111;
        }

        .archivo-evidencia-admin {
            display: inline-block;
            background: var(--azul-oscuro);
            color: white;
            padding: 12px 16px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 900;
            transition: 0.3s;
        }

        .archivo-evidencia-admin:hover {
            background: var(--azul-medio);
            transform: translateY(-3px);
        }

        .mensaje-detalle {
            padding: 15px 17px;
            border-radius: 16px;
            margin-bottom: 22px;
            font-weight: 900;
        }

        .mensaje-detalle.exito {
            background: rgba(30,120,75,0.15);
            color: #145f3d;
            border: 1px solid rgba(20,95,61,0.12);
        }

        .mensaje-detalle.error {
            background: rgba(160,30,30,0.13);
            color: #7a1111;
            border: 1px solid rgba(122,17,17,0.12);
        }

        .form-revision select,
        .form-revision textarea {
            width: 100%;
            padding: 14px 16px;
            border-radius: 15px;
            border: 2px solid rgba(46,65,86,0.12);
            outline: none;
            background: white;
            color: var(--azul-oscuro);
            font-size: 15px;
            margin-top: 7px;
            transition: 0.3s;
        }

        .form-revision textarea {
            min-height: 150px;
            resize: vertical;
        }

        .form-revision select:focus,
        .form-revision textarea:focus {
            border-color: var(--azul-medio);
            box-shadow: 0 0 0 5px rgba(62,92,118,0.15);
        }

        .form-revision label {
            display: block;
            font-weight: 900;
            color: var(--azul-oscuro);
            margin-bottom: 8px;
        }

        .form-revision .grupo-form {
            margin-bottom: 18px;
        }

        .aviso-respuesta {
            background: rgba(46,65,86,0.08);
            border-left: 5px solid var(--azul-oscuro);
            padding: 14px 16px;
            border-radius: 14px;
            color: var(--azul-medio);
            line-height: 1.5;
            margin-bottom: 18px;
            font-weight: 600;
        }

        @media (max-width: 950px) {
            .detalle-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="layout">

    <?php require_once 'includes/navbar.php'; ?>
<main class="contenido">

        <section class="header">
            <h1>Revisar excusa</h1>
            <p>Consulta los detalles enviados por el tutor, define el resultado y envÃ­a una respuesta oficial.</p>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-detalle <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php } ?>

        <div class="detalle-grid">

            <section class="detalle-panel">
                <h2>InformaciÃ³n de la excusa</h2>

                <div class="detalle-item">
                    <label>Estado actual</label>
                    <span class="<?php echo claseEstadoDetalle($excusa['estado']); ?>">
                        <?php echo textoEstadoDetalle($excusa['estado']); ?>
                    </span>
                </div>

                <div class="detalle-item">
                    <label>Estudiante</label>
                    <p><?php echo htmlspecialchars($excusa['nombre_estudiante']); ?></p>
                </div>

                <div class="detalle-item">
                    <label>Curso o grado</label>
                    <p>
                        <?php 
                            echo !empty($excusa['curso_estudiante']) 
                                ? htmlspecialchars($excusa['curso_estudiante']) 
                                : "No especificado"; 
                        ?>
                    </p>
                </div>

                <div class="detalle-item">
                    <label>Fecha de ausencia</label>
                    <p><?php echo date("d/m/Y", strtotime($excusa['fecha_ausencia'])); ?></p>
                </div>

                <div class="detalle-item">
                    <label>Motivo</label>
                    <p><?php echo htmlspecialchars($excusa['motivo']); ?></p>
                </div>

                <div class="detalle-item">
                    <label>DescripciÃ³n</label>
                    <p><?php echo nl2br(htmlspecialchars($excusa['descripcion'])); ?></p>
                </div>

                <div class="detalle-item">
                    <label>Evidencia</label>

                    <?php if (!empty($excusa['evidencia'])) { ?>
                        <a class="archivo-evidencia-admin" 
                           href="uploads/excusas/<?php echo rawurlencode($excusa['evidencia']); ?>" 
                           target="_blank">
                            Ver evidencia
                        </a>
                    <?php } else { ?>
                        <p>No se adjuntÃ³ evidencia.</p>
                    <?php } ?>
                </div>
            </section>

            <section class="detalle-panel">
                <h2>Datos del tutor</h2>

                <div class="detalle-item">
                    <label>Nombre del tutor</label>
                    <p>
                        <?php 
                            echo htmlspecialchars($excusa['tutor_nombre'] . " " . $excusa['tutor_apellido']); 
                        ?>
                    </p>
                </div>

                <div class="detalle-item">
                    <label>Correo</label>
                    <p><?php echo htmlspecialchars($excusa['tutor_correo']); ?></p>
                </div>

                <div class="detalle-item">
                    <label>TelÃ©fono</label>
                    <p>
                        <?php 
                            echo !empty($excusa['tutor_telefono']) 
                                ? htmlspecialchars($excusa['tutor_telefono']) 
                                : "No registrado"; 
                        ?>
                    </p>
                </div>

                <div class="detalle-item">
                    <label>Fecha de envÃ­o</label>
                    <p><?php echo date("d/m/Y h:i A", strtotime($excusa['fecha_envio'])); ?></p>
                </div>

                <?php if (!empty($excusa['fecha_revision'])) { ?>
                    <div class="detalle-item">
                        <label>Fecha de revisiÃ³n</label>
                        <p><?php echo date("d/m/Y h:i A", strtotime($excusa['fecha_revision'])); ?></p>
                    </div>
                <?php } ?>

                <hr style="margin: 22px 0; border: none; border-top: 1px solid rgba(46,65,86,0.15);">

                <h2>Responder al tutor</h2>

                <div class="aviso-respuesta">
                    Esta respuesta serÃ¡ visible para el tutor en su panel de excusas. 
                    Selecciona si la excusa queda aprobada, no aprobada o pendiente en revisiÃ³n.
                </div>

                <form action="ver_excusa.php?id=<?php echo $id; ?>" method="POST" class="form-revision">

                    <div class="grupo-form">
                        <label>Resultado de la excusa</label>
                        <select name="estado" required>
                            <option value="Pendiente" <?php if ($excusa['estado'] == "Pendiente") echo "selected"; ?>>
                                Pendiente / En revisiÃ³n
                            </option>

                            <option value="Aprobada" <?php if ($excusa['estado'] == "Aprobada") echo "selected"; ?>>
                                Aprobada
                            </option>

                            <option value="Rechazada" <?php if ($excusa['estado'] == "Rechazada") echo "selected"; ?>>
                                No aprobada
                            </option>
                        </select>
                    </div>

                    <div class="grupo-form">
                        <label>Respuesta para el tutor</label>
                        <textarea name="respuesta_admin" placeholder="Escriba aquÃ­ la respuesta oficial para el tutor..."><?php echo htmlspecialchars($excusa['respuesta_admin'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn-guardar">
                        Enviar respuesta al tutor
                    </button>

                    <a href="excusas.php" class="btn-volver" style="margin-left: 8px;">
                        Volver
                    </a>

                </form>

            </section>

        </div>

    </main>

</div>

</body>
</html>
