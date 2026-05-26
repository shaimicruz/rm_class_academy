<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("estudiante,tutor");

$mensaje = "";

// Buscamos todos los eventos publicados
// Así evitamos problemas con estudiantes que tengan el grado como "Pendiente".
$sql_eventos = "SELECT * FROM calendario 
                ORDER BY fecha_evento ASC, hora_evento ASC, id DESC";

$eventos = $conexion->query($sql_eventos);

if (!$eventos) {
    $mensaje = "Error al cargar los eventos del calendario.";
}

function formatearFecha($fecha) {
    if (empty($fecha)) {
        return "Sin fecha";
    }

    return date("d/m/Y", strtotime($fecha));
}

function formatearHora($hora) {
    if (empty($hora)) {
        return "Sin hora";
    }

    return date("h:i A", strtotime($hora));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi calendario - R.M CLASS ACADEMY</title>
    <link rel="stylesheet" href="css_dashboard.css?v=<?php echo time(); ?>">

    <style>
        .calendario-contenedor {
            display: grid;
            gap: 20px;
        }

        .evento-card {
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(255, 255, 255, 0.70);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 28px;
            border-radius: 26px;
            box-shadow: 0 18px 45px rgba(26, 45, 66, 0.14);
            transition: all 0.32s ease;
            position: relative;
            overflow: hidden;
        }

        .evento-card::after {
            content: "";
            position: absolute;
            width: 105px;
            height: 105px;
            border-radius: 30px;
            background: rgba(26, 45, 66, 0.06);
            right: -30px;
            top: -30px;
            transform: rotate(18deg);
        }

        .evento-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 28px 70px rgba(26, 45, 66, 0.25);
        }

        .evento-info {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            position: relative;
            z-index: 2;
        }

        .badge-evento {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            background: var(--azul-oscuro);
            color: var(--gris-suave);
        }

        .badge-evento.fecha {
            background: linear-gradient(135deg, var(--azul-oscuro), var(--azul-claro));
            color: var(--gris-suave);
        }

        .badge-evento.hora {
            background: var(--gris-suave);
            color: var(--azul-oscuro);
        }

        .evento-card h2 {
            color: var(--azul-oscuro);
            margin-bottom: 12px;
            position: relative;
            z-index: 2;
        }

        .evento-card p {
            color: var(--azul-medio);
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }

        .evento-lugar {
            margin-top: 12px;
            font-weight: 800;
            color: var(--azul-oscuro);
            position: relative;
            z-index: 2;
        }

        .sin-eventos {
            background: rgba(255,255,255,0.86);
            padding: 30px;
            border-radius: 26px;
            text-align: center;
            box-shadow: 0 18px 45px rgba(26, 45, 66, 0.14);
        }

        .mensaje-calendario {
            background: rgba(160, 30, 30, 0.13);
            color: #7a1111;
            padding: 15px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: 800;
        }
    </style>
</head>

<body>

<div class="layout">

    <?php require_once 'includes/navbar.php'; ?>
<main class="contenido">

        <section class="header">
            <h1>Mi calendario</h1>
            <p>Consulta los eventos académicos, actividades y fechas importantes publicadas por la profesora.</p>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-calendario">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php } ?>

        <section class="calendario-contenedor">

            <?php if ($eventos != null && $eventos->num_rows > 0) { ?>

                <?php $numero_evento = 1; ?>

                <?php while ($evento = $eventos->fetch_assoc()) { ?>

                    <div class="evento-card">

                        <div class="evento-info">
                            <span class="badge-evento">
                                Evento #<?php echo $numero_evento++; ?>
                            </span>

                            <span class="badge-evento fecha">
                                <?php echo formatearFecha($evento['fecha_evento']); ?>
                            </span>

                            <span class="badge-evento hora">
                                <?php echo formatearHora($evento['hora_evento']); ?>
                            </span>

                            <?php if ($evento['para_todos'] == 1) { ?>
                                <span class="badge-evento">Para todos</span>
                            <?php } else { ?>
                                <span class="badge-evento">Evento académico</span>
                            <?php } ?>
                        </div>

                        <h2><?php echo htmlspecialchars($evento['titulo']); ?></h2>

                        <p>
                            <?php echo nl2br(htmlspecialchars($evento['descripcion'])); ?>
                        </p>

                        <div class="evento-lugar">
                            Lugar:
                            <?php 
                                echo !empty($evento['lugar']) 
                                    ? htmlspecialchars($evento['lugar']) 
                                    : "No especificado"; 
                            ?>
                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="sin-eventos">
                    <h2>No hay eventos disponibles todavía.</h2>
                    <p>Cuando la profesora publique un evento, aparecerá aquí.</p>
                </div>

            <?php } ?>

        </section>

    </main>

</div>

</body>
</html>




