<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("estudiante,tutor");

$mensaje = "";
$anuncios = null;
$grado_id = null;
$rol = $_SESSION['rol'] ?? '';
$usuario_id = intval($_SESSION['usuario_id'] ?? 0);

if ($rol === 'estudiante' && $usuario_id > 0) {
    $stmt_grado = $conexion->prepare("SELECT grado_id FROM estudiantes WHERE usuario_id = ? LIMIT 1");
    $stmt_grado->bind_param("i", $usuario_id);
    $stmt_grado->execute();
    $res_grado = $stmt_grado->get_result();
    if ($res_grado && $res_grado->num_rows > 0) {
        $grado_id = intval($res_grado->fetch_assoc()['grado_id']);
    }
} elseif ($rol === 'tutor') {
    $grado_id = intval($_SESSION['estudiante_grado_id'] ?? 0);
}

if ($grado_id > 0) {
    $stmt_anuncios = $conexion->prepare("
        SELECT * FROM anuncios
        WHERE para_todos = 1 OR grado_id = ?
        ORDER BY fecha_publicacion DESC, id DESC
    ");
    $stmt_anuncios->bind_param("i", $grado_id);
    $stmt_anuncios->execute();
    $anuncios = $stmt_anuncios->get_result();
} else {
    $sql_anuncios = "SELECT * FROM anuncios WHERE para_todos = 1 ORDER BY fecha_publicacion DESC, id DESC";
    $anuncios = $conexion->query($sql_anuncios);
}

if (!$anuncios) $mensaje = "Error al cargar los anuncios.";
?>
<?php
$page_title = "Mis anuncios - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>
<style>
        .anuncios-contenedor {
            display: grid;
            gap: 20px;
        }

        .anuncio-card {
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

        .anuncio-card::after {
            content: "";
            position: absolute;
            width: 100px;
            height: 100px;
            border-radius: 30px;
            background: rgba(26, 45, 66, 0.06);
            right: -30px;
            top: -30px;
            transform: rotate(18deg);
        }

        .anuncio-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 28px 70px rgba(26, 45, 66, 0.25);
        }

        .anuncio-card h2 {
            color: var(--azul-oscuro);
            margin-bottom: 12px;
            position: relative;
            z-index: 2;
        }

        .anuncio-card p {
            color: var(--azul-medio);
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }

        .anuncio-info {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
            position: relative;
            z-index: 2;
        }

        .badge {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            background: var(--azul-oscuro);
            color: var(--gris-suave);
        }

        .badge.fecha {
            background: var(--gris-suave);
            color: var(--azul-oscuro);
        }

        .badge.numero {
            background: linear-gradient(135deg, var(--azul-oscuro), var(--azul-claro));
            color: var(--gris-suave);
        }

        .sin-anuncios {
            background: rgba(255,255,255,0.86);
            padding: 30px;
            border-radius: 26px;
            text-align: center;
            box-shadow: 0 18px 45px rgba(26, 45, 66, 0.14);
        }

        .mensaje-anuncio {
            background: rgba(160, 30, 30, 0.13);
            color: #7a1111;
            padding: 15px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: 800;
        }

        .btn-ver-anuncio {
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

        .btn-ver-anuncio:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(18,41,84,0.30);
        }
    </style>

<div class="layout">

    <?php require_once 'includes/navbar.php'; ?>
<main class="contenido">

        <section class="header">
            <h1>Mis anuncios</h1>
            <p>Anuncios publicados por la profesora para la comunidad académica.</p>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-anuncio">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php } ?>

        <section class="anuncios-contenedor">

            <?php if ($anuncios != null && $anuncios->num_rows > 0) { ?>

                <?php $numero_anuncio = 1; ?>

                <?php while ($anuncio = $anuncios->fetch_assoc()) { ?>

                    <div class="anuncio-card">

                        <div class="anuncio-info">

                            <span class="badge numero">
                                Anuncio #<?php echo $numero_anuncio++; ?>
                            </span>

                            <?php if ($anuncio['para_todos'] == 1) { ?>
                                <span class="badge">Para todos</span>
                            <?php } else { ?>
                                <span class="badge">Anuncio académico</span>
                            <?php } ?>

                            <span class="badge fecha">
                                <?php echo htmlspecialchars($anuncio['fecha_publicacion']); ?>
                            </span>

                        </div>

                        <h2><?php echo htmlspecialchars($anuncio['titulo']); ?></h2>

                        <p>
                            <?php echo nl2br(htmlspecialchars(mb_substr($anuncio['contenido'], 0, 150))); ?>
                            <?php if (mb_strlen($anuncio['contenido']) > 150) echo "..."; ?>
                        </p>

                        <a class="btn-ver-anuncio" href="detalle_anuncio.php?id=<?php echo $anuncio['id']; ?>">
                             Ver anuncio completo
                        </a>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="sin-anuncios">
                    <h2>No tienes anuncios disponibles todavía.</h2>
                    <p>Cuando la profesora publique un anuncio, aparecerá aquí.</p>
                </div>

            <?php } ?>

        </section>

    </main>

</div>

<?php require_once 'includes/footer.php'; ?>




