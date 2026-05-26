<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("estudiante,tutor");

// Obtener ID del anuncio
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ver_anuncios.php");
    exit;
}

$id_anuncio = (int)$_GET['id'];

// Buscar el anuncio
$stmt = $conexion->prepare("SELECT * FROM anuncios WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id_anuncio);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: ver_anuncios.php");
    exit;
}

$anuncio = $resultado->fetch_assoc();
$rol = $_SESSION['rol'] ?? '';
$usuario_id = intval($_SESSION['usuario_id'] ?? 0);

$grado_contexto = 0;
if ($rol === 'estudiante' && $usuario_id > 0) {
    $stmt_ctx = $conexion->prepare("SELECT grado_id FROM estudiantes WHERE usuario_id = ? LIMIT 1");
    $stmt_ctx->bind_param("i", $usuario_id);
    $stmt_ctx->execute();
    $res_ctx = $stmt_ctx->get_result();
    if ($res_ctx && $res_ctx->num_rows > 0) {
        $grado_contexto = intval($res_ctx->fetch_assoc()['grado_id']);
    }
} elseif ($rol === 'tutor') {
    $grado_contexto = intval($_SESSION['estudiante_grado_id'] ?? 0);
}

if (intval($anuncio['para_todos']) !== 1) {
    $grado_anuncio = intval($anuncio['grado_id'] ?? 0);
    if ($grado_contexto <= 0 || $grado_contexto !== $grado_anuncio) {
        header("Location: ver_anuncios.php");
        exit;
    }
}

// Obtener nombre del grado si aplica
$nombre_grado = "";
if ($anuncio['para_todos'] != 1 && !empty($anuncio['grado_id'])) {
    $stmt_g = $conexion->prepare("SELECT * FROM grados WHERE id = ? LIMIT 1");
    $stmt_g->bind_param("i", $anuncio['grado_id']);
    $stmt_g->execute();
    $res_g = $stmt_g->get_result();
    if ($res_g->num_rows > 0) {
        $grado = $res_g->fetch_assoc();
        if (isset($grado['nombre_grado'])) $nombre_grado = $grado['nombre_grado'];
        elseif (isset($grado['nombre'])) $nombre_grado = $grado['nombre'];
        elseif (isset($grado['grado'])) $nombre_grado = $grado['grado'];
        else $nombre_grado = "Grado " . $grado['id'];
    }
}

$page_title = htmlspecialchars($anuncio['titulo']) . " - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<style>
    .detalle-card {
        background: var(--blanco);
        border-radius: 24px;
        box-shadow: var(--sombra-suave);
        padding: 44px;
        margin-bottom: 30px;
        animation: subirSuave 0.5s ease both;
    }

    .detalle-titulo {
        font-size: 30px;
        font-weight: 900;
        color: var(--azul-oscuro);
        margin-bottom: 20px;
        line-height: 1.3;
    }

    .detalle-meta {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }

    .badge-meta {
        display: inline-block;
        padding: 7px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
    }

    .badge-todos {
        background: rgba(18, 41, 84, 0.10);
        color: var(--azul-oscuro);
    }

    .badge-grado {
        background: rgba(173, 131, 60, 0.15);
        color: #7a5500;
    }

    .badge-fecha {
        background: rgba(18, 41, 84, 0.06);
        color: #555;
    }

    .detalle-contenido {
        font-size: 16px;
        color: #3a3a3a;
        line-height: 1.85;
        white-space: pre-wrap;
        padding: 28px;
        background: rgba(18, 41, 84, 0.04);
        border-radius: 16px;
        border-left: 5px solid var(--azul-oscuro);
    }

    .separador {
        border: none;
        border-top: 2px solid rgba(18, 41, 84, 0.08);
        margin: 28px 0;
    }

    .btn-volver-anuncio {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(18, 41, 84, 0.08);
        color: var(--azul-oscuro);
        text-decoration: none;
        padding: 12px 20px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s ease;
        margin-bottom: 24px;
    }

    .btn-volver-anuncio:hover {
        background: rgba(18, 41, 84, 0.14);
        transform: translateX(-4px);
    }

    .anuncio-icon {
        font-size: 50px;
        margin-bottom: 16px;
        display: block;
    }
</style>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">

        <a href="ver_anuncios.php" class="btn-volver-anuncio">
            ← Volver a anuncios
        </a>

        <div class="detalle-card">
            <span class="anuncio-icon">📢</span>

            <h1 class="detalle-titulo"><?php echo htmlspecialchars($anuncio['titulo']); ?></h1>

            <div class="detalle-meta">
                <?php if ($anuncio['para_todos'] == 1) { ?>
                    <span class="badge-meta badge-todos">🌍 Para todos</span>
                <?php } else { ?>
                    <span class="badge-meta badge-grado">📚 <?php echo htmlspecialchars($nombre_grado ?: "Anuncio académico"); ?></span>
                <?php } ?>

                <span class="badge-meta badge-fecha">
                    📅 <?php echo htmlspecialchars($anuncio['fecha_publicacion']); ?>
                </span>
            </div>

            <hr class="separador">

            <p class="detalle-contenido"><?php echo htmlspecialchars($anuncio['contenido']); ?></p>
        </div>

    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
