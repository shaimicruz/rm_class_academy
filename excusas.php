<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$busqueda_curso = "";
$filtro_dia = "todas";

if (isset($_GET['curso'])) {
    $busqueda_curso = trim($_GET['curso']);
}

if (isset($_GET['dia'])) {
    $filtro_dia = $_GET['dia'];
}

$condiciones = [];
$parametros = [];
$tipos = "";

if ($busqueda_curso != "") {
    $condiciones[] = "excusas.curso_estudiante LIKE ?";
    $parametros[] = "%" . $busqueda_curso . "%";
    $tipos .= "s";
}

if ($filtro_dia == "hoy") {
    $condiciones[] = "DATE(excusas.fecha_envio) = CURDATE()";
} elseif ($filtro_dia == "ayer") {
    $condiciones[] = "DATE(excusas.fecha_envio) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
} elseif ($filtro_dia == "semana") {
    $condiciones[] = "excusas.fecha_envio >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filtro_dia == "antiguas") {
    $condiciones[] = "excusas.fecha_envio < DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

$sql = "SELECT 
            excusas.*,
            usuarios.nombre AS tutor_nombre,
            usuarios.apellido AS tutor_apellido,
            usuarios.correo AS tutor_correo
        FROM excusas
        INNER JOIN usuarios ON excusas.tutor_usuario_id = usuarios.id";

if (count($condiciones) > 0) {
    $sql .= " WHERE " . implode(" AND ", $condiciones);
}

$sql .= " ORDER BY excusas.fecha_envio DESC";

if (count($parametros) > 0) {
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($tipos, ...$parametros);
    $stmt->execute();
    $excusas = $stmt->get_result();
} else {
    $excusas = $conexion->query($sql);
}

function claseEstado($estado) {
    if ($estado == "Aprobada") {
        return "estado activo";
    } elseif ($estado == "Rechazada") {
        return "estado inactivo";
    } else {
        return "estado pendiente";
    }
}

function textoEstado($estado) {
    if ($estado == "Aprobada") {
        return "Aprobada";
    } elseif ($estado == "Rechazada") {
        return "No aprobada";
    } else {
        return "Pendiente / En revisión";
    }
}

function clasificarDia($fecha_envio) {
    $fecha = date("Y-m-d", strtotime($fecha_envio));
    $hoy = date("Y-m-d");
    $ayer = date("Y-m-d", strtotime("-1 day"));

    if ($fecha == $hoy) {
        return "Hoy";
    } elseif ($fecha == $ayer) {
        return "Ayer";
    } elseif (strtotime($fecha_envio) >= strtotime("-7 days")) {
        return "Últimos 7 días";
    } else {
        return "Antigua";
    }
}

function claseClasificacion($fecha_envio) {
    $clasificacion = clasificarDia($fecha_envio);

    if ($clasificacion == "Hoy") {
        return "clasificacion hoy";
    } elseif ($clasificacion == "Ayer") {
        return "clasificacion ayer";
    } elseif ($clasificacion == "Últimos 7 días") {
        return "clasificacion semana";
    } else {
        return "clasificacion antigua";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Excusas - R.M CLASS ACADEMY</title>
    <link rel="stylesheet" href="css_dashboard.css?v=<?php echo time(); ?>">

    <style>
        .estado.pendiente {
            background: rgba(138,101,0,0.16);
            color: #8a6500;
        }

        .acciones-excusa {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-revisar {
            background: var(--azul-oscuro);
            color: white;
            padding: 9px 14px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 900;
            display: inline-block;
            transition: 0.3s;
        }

        .btn-revisar:hover {
            background: var(--azul-medio);
            transform: translateY(-3px);
        }

        .busqueda-excusas {
            background: rgba(255,255,255,0.84);
            border: 1px solid rgba(255,255,255,0.72);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 24px;
            border-radius: 24px;
            box-shadow: 0 18px 45px rgba(26, 45, 66, 0.14);
            margin-bottom: 26px;
        }

        .busqueda-excusas h2 {
            color: var(--azul-oscuro);
            margin-bottom: 8px;
        }

        .busqueda-excusas p {
            color: var(--azul-medio);
            margin-bottom: 18px;
        }

        .busqueda-fila {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .busqueda-fila input,
        .busqueda-fila select {
            flex: 1;
            min-width: 220px;
            padding: 14px 16px;
            border-radius: 15px;
            border: 2px solid rgba(46,65,86,0.12);
            outline: none;
            font-size: 15px;
            color: var(--azul-oscuro);
            background: white;
        }

        .busqueda-fila input:focus,
        .busqueda-fila select:focus {
            border-color: var(--azul-medio);
            box-shadow: 0 0 0 5px rgba(62,92,118,0.15);
        }

        .busqueda-fila button {
            background: var(--azul-oscuro);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 15px;
            font-weight: 900;
            cursor: pointer;
            transition: 0.3s;
        }

        .busqueda-fila button:hover {
            background: var(--azul-medio);
            transform: translateY(-3px);
        }

        .btn-limpiar-busqueda {
            background: var(--gris-suave);
            color: var(--azul-oscuro);
            padding: 14px 18px;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 900;
            transition: 0.3s;
        }

        .btn-limpiar-busqueda:hover {
            background: var(--gris-claro);
            transform: translateY(-3px);
        }

        .clasificacion {
            font-weight: 900;
            padding: 7px 12px;
            border-radius: 999px;
            display: inline-block;
            font-size: 13px;
        }

        .clasificacion.hoy {
            background: rgba(30,120,75,0.15);
            color: #145f3d;
        }

        .clasificacion.ayer {
            background: rgba(46,65,86,0.14);
            color: var(--azul-oscuro);
        }

        .clasificacion.semana {
            background: rgba(138,101,0,0.13);
            color: #8a6500;
        }

        .clasificacion.antigua {
            background: rgba(160,30,30,0.13);
            color: #7a1111;
        }

        @media (max-width: 700px) {
            .busqueda-fila {
                flex-direction: column;
                align-items: stretch;
            }

            .busqueda-fila input,
            .busqueda-fila select,
            .busqueda-fila button,
            .btn-limpiar-busqueda {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="layout">

    <?php require_once 'includes/navbar.php'; ?>
<main class="contenido">

        <section class="header">
            <h1>Gestión de excusas</h1>
            <p>
                Desde este panel la profesora puede revisar las excusas enviadas por los tutores,
                buscar por curso, clasificarlas por fecha, ver evidencias y responder solicitudes.
            </p>
        </section>

        <section class="busqueda-excusas">
            <h2>Buscar y clasificar excusas</h2>
            <p>Filtra las excusas por curso o por fecha de envio: hoy, ayer, ultimos 7 dias o antiguas.</p>

            <form action="excusas.php" method="GET" class="busqueda-fila">
                <input 
                    type="text" 
                    name="curso" 
                    placeholder="Ejemplo: 4to A, 5to B, 6to..."
                    value="<?php echo htmlspecialchars($busqueda_curso); ?>"
                >

                <select name="dia">
                    <option value="todas" <?php if ($filtro_dia == "todas") echo "selected"; ?>>Todas</option>
                    <option value="hoy" <?php if ($filtro_dia == "hoy") echo "selected"; ?>>Hoy</option>
                    <option value="ayer" <?php if ($filtro_dia == "ayer") echo "selected"; ?>>Ayer</option>
                    <option value="semana" <?php if ($filtro_dia == "semana") echo "selected"; ?>>ultimos 7 dias</option>
                    <option value="antiguas" <?php if ($filtro_dia == "antiguas") echo "selected"; ?>>Antiguas</option>
                </select>

                <button type="submit">Filtrar</button>

                <a href="excusas.php" class="btn-limpiar-busqueda">
                    Limpiar
                </a>
            </form>
        </section>

        <section class="tabla-contenedor">

              <div class="tabla-header">
                  <h2>Excusas recibidas</h2>
              </div>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Estudiante</th>
                            <th>Curso</th>
                            <th>Fecha ausencia</th>
                            <th>Motivo</th>
                            <th>Tutor</th>
                            <th>Estado</th>
                            <th>Clasificación</th>
                            <th>Enviada</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($excusas && $excusas->num_rows > 0) { ?>
                            <?php $contador = 1; ?>

                            <?php while ($excusa = $excusas->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $contador++; ?></td>

                                    <td><?php echo htmlspecialchars($excusa['nombre_estudiante']); ?></td>

                                    <td>
                                        <?php 
                                            echo !empty($excusa['curso_estudiante']) 
                                                ? htmlspecialchars($excusa['curso_estudiante']) 
                                                : "No especificado"; 
                                        ?>
                                    </td>

                                    <td><?php echo date("d/m/Y", strtotime($excusa['fecha_ausencia'])); ?></td>

                                    <td><?php echo htmlspecialchars($excusa['motivo']); ?></td>

                                    <td>
                                        <?php 
                                            echo htmlspecialchars($excusa['tutor_nombre'] . " " . $excusa['tutor_apellido']); 
                                        ?>
                                        <br>
                                        <small><?php echo htmlspecialchars($excusa['tutor_correo']); ?></small>
                                    </td>

                                    <td>
                                        <span class="<?php echo claseEstado($excusa['estado']); ?>">
                                            <?php echo textoEstado($excusa['estado']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="<?php echo claseClasificacion($excusa['fecha_envio']); ?>">
                                            <?php echo clasificarDia($excusa['fecha_envio']); ?>
                                        </span>
                                    </td>

                                    <td><?php echo date("d/m/Y h:i A", strtotime($excusa['fecha_envio'])); ?></td>

                                    <td>
                                        <div class="acciones-excusa">
                                            <a href="ver_excusa.php?id=<?php echo $excusa['id']; ?>" class="btn-revisar">
                                                Revisar / Responder
                                            </a>

                                            <a href="eliminar_excusa.php?id=<?php echo $excusa['id']; ?>" 
                                               class="btn-eliminar"
                                               onclick="return confirm('¿Seguro que quieres eliminar esta excusa?');">
                                                Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>
                            <tr>
                                <td colspan="10" class="sin-datos">
                                    No hay excusas disponibles o no hay resultados para ese filtro.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </section>

    </main>

</div>

</body>
</html>

