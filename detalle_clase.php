<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("estudiante");

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ver_clases.php");
    exit;
}

$id_clase = intval($_GET["id"]);
$usuario_id = intval($_SESSION["usuario_id"] ?? 0);

$stmt_est = $conexion->prepare("SELECT grado_id FROM estudiantes WHERE usuario_id = ? LIMIT 1");
$stmt_est->bind_param("i", $usuario_id);
$stmt_est->execute();
$res_est = $stmt_est->get_result();

if ($res_est->num_rows === 0) {
    header("Location: ver_clases.php");
    exit;
}

$grado_id_estudiante = intval($res_est->fetch_assoc()["grado_id"]);

$stmt = $conexion->prepare("
    SELECT clases.*, grados.nombre AS nombre_grado
    FROM clases
    LEFT JOIN grados ON clases.grado_id = grados.id
    WHERE clases.id = ? AND clases.grado_id = ? LIMIT 1
");
$stmt->bind_param("ii", $id_clase, $grado_id_estudiante);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: ver_clases.php");
    exit;
}

$clase = $resultado->fetch_assoc();
$extension = "";
$archivo_url = "";
if (!empty($clase["archivo"])) {
    $extension = strtolower(pathinfo($clase["archivo"], PATHINFO_EXTENSION));
    $archivo_url = "uploads_clases/" . rawurlencode($clase["archivo"]);
}

$page_title = htmlspecialchars($clase["titulo"]) . " - R.M CLASS ACADEMY";
require_once "includes/header.php";
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
        font-size: 28px;
        font-weight: 900;
        color: var(--azul-oscuro);
        margin-bottom: 18px;
        line-height: 1.3;
    }
    .detalle-meta {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }
    .badge-meta {
        display: inline-block;
        padding: 7px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
    }
    .badge-grado {
        background: rgba(18, 41, 84, 0.10);
        color: var(--azul-oscuro);
    }
    .badge-fecha {
        background: rgba(173, 131, 60, 0.15);
        color: #7a5500;
    }
    .detalle-descripcion {
        font-size: 16px;
        color: #444;
        line-height: 1.8;
        white-space: pre-wrap;
        margin-bottom: 30px;
        padding: 24px;
        background: rgba(18, 41, 84, 0.04);
        border-radius: 16px;
        border-left: 4px solid var(--azul-oscuro);
    }
    .detalle-archivo h3 {
        font-size: 17px;
        font-weight: 800;
        color: var(--azul-oscuro);
        margin-bottom: 16px;
    }
    .archivo-imagen {
        max-width: 100%;
        border-radius: 16px;
        box-shadow: var(--sombra-media);
        display: block;
        margin-bottom: 16px;
    }
    .archivo-embed {
        width: 100%;
        height: 600px;
        border-radius: 16px;
        border: none;
        box-shadow: var(--sombra-media);
        display: block;
        margin-bottom: 16px;
    }
    .btn-descargar {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--azul-oscuro), #1e4a8f);
        color: white;
        text-decoration: none;
        padding: 13px 24px;
        border-radius: 14px;
        font-weight: 800;
        font-size: 15px;
        box-shadow: 0 8px 20px rgba(18,41,84,0.22);
        transition: all 0.3s ease;
        margin-top: 10px;
    }
    .btn-volver-clase {
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
    .separador {
        border: none;
        border-top: 2px solid rgba(18, 41, 84, 0.08);
        margin: 28px 0;
    }
</style>

<div class="layout">
    <?php require_once "includes/navbar.php"; ?>

    <main class="contenido">
        <a href="ver_clases.php" class="btn-volver-clase">Volver a mis clases</a>

        <div class="detalle-card">
            <h1 class="detalle-titulo"><?php echo htmlspecialchars($clase["titulo"]); ?></h1>

            <div class="detalle-meta">
                <?php if (!empty($clase["nombre_grado"])): ?>
                    <span class="badge-meta badge-grado"><?php echo htmlspecialchars($clase["nombre_grado"]); ?></span>
                <?php endif; ?>
                <span class="badge-meta badge-fecha"><?php echo htmlspecialchars($clase["fecha"]); ?></span>
            </div>

            <hr class="separador">

            <p class="detalle-descripcion"><?php echo htmlspecialchars($clase["descripcion"]); ?></p>

            <?php if (!empty($clase["archivo"])): ?>
                <div class="detalle-archivo">
                    <h3>Archivo adjunto</h3>

                    <?php if (in_array($extension, ["jpg", "jpeg", "png", "gif", "webp"], true)): ?>
                        <img src="<?php echo $archivo_url; ?>" alt="Archivo de la clase" class="archivo-imagen">
                        <a href="<?php echo $archivo_url; ?>" download class="btn-descargar">Descargar imagen</a>
                    <?php elseif ($extension === "pdf"): ?>
                        <embed src="<?php echo $archivo_url; ?>" type="application/pdf" class="archivo-embed">
                        <a href="<?php echo $archivo_url; ?>" download class="btn-descargar">Descargar PDF</a>
                    <?php else: ?>
                        <a href="<?php echo $archivo_url; ?>" download class="btn-descargar">
                            Descargar archivo (<?php echo strtoupper($extension); ?>)
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once "includes/footer.php"; ?>
