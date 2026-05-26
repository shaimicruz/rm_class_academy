<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("estudiante,tutor");

// Obtener ID de la tarea
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ver_tarea.php");
    exit;
}

$id_tarea = (int)$_GET['id'];

// Buscar la tarea
$stmt = $conexion->prepare("SELECT * FROM tareas WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id_tarea);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: ver_tarea.php");
    exit;
}

$tarea = $resultado->fetch_assoc();

// Obtener nombre del grado
$nombre_grado = "";
if (!empty($tarea['grado_id'])) {
    $stmt_g = $conexion->prepare("SELECT * FROM grados WHERE id = ? LIMIT 1");
    $stmt_g->bind_param("i", $tarea['grado_id']);
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

// Detectar extensión del archivo
$extension = "";
$archivo_url = "";
if (!empty($tarea['archivo'])) {
    $extension = strtolower(pathinfo($tarea['archivo'], PATHINFO_EXTENSION));
    $archivo_url = "uploads/tareas/" . rawurlencode($tarea['archivo']);
}

$page_title = htmlspecialchars($tarea['titulo']) . " - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<style>
    .detalle-card {
        background: var(--blanco);
        border-radius: 24px;
        box-shadow: var(--sombra-suave);
        padding: 40px;
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

    .badge-entrega {
        background: rgba(160, 30, 30, 0.12);
        color: #7a1111;
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

    .detalle-archivo {
        margin-top: 20px;
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

    .btn-descargar:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(18,41,84,0.30);
    }

    .btn-volver-tarea {
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

    .btn-volver-tarea:hover {
        background: rgba(18, 41, 84, 0.14);
        transform: translateX(-4px);
    }

    .separador {
        border: none;
        border-top: 2px solid rgba(18, 41, 84, 0.08);
        margin: 28px 0;
    }
</style>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">

        <a href="ver_tarea.php" class="btn-volver-tarea">
            ← Volver a mis tareas
        </a>

        <div class="detalle-card">
            <h1 class="detalle-titulo"><?php echo htmlspecialchars($tarea['titulo']); ?></h1>

            <div class="detalle-meta">
                <?php if ($nombre_grado != "") { ?>
                    <span class="badge-meta badge-grado">📚 <?php echo htmlspecialchars($nombre_grado); ?></span>
                <?php } ?>

                <?php if (!empty($tarea['fecha_entrega'])) { ?>
                    <span class="badge-meta badge-entrega">⏰ Entrega: <?php echo htmlspecialchars($tarea['fecha_entrega']); ?></span>
                <?php } ?>

                <?php if (!empty($tarea['fecha_creacion'])) { ?>
                    <span class="badge-meta badge-fecha">📅 Publicada: <?php echo htmlspecialchars($tarea['fecha_creacion']); ?></span>
                <?php } ?>
            </div>

            <hr class="separador">

            <p class="detalle-descripcion"><?php echo htmlspecialchars($tarea['descripcion']); ?></p>

            <?php if (!empty($tarea['archivo'])) { ?>
                <div class="detalle-archivo">
                    <h3>📎 Archivo adjunto</h3>

                    <?php if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) { ?>
                        <img src="<?php echo $archivo_url; ?>" alt="Archivo de la tarea" class="archivo-imagen">
                        <a href="<?php echo $archivo_url; ?>" download class="btn-descargar">
                            ⬇ Descargar imagen
                        </a>

                    <?php } elseif ($extension === 'pdf') { ?>
                        <embed src="<?php echo $archivo_url; ?>" type="application/pdf" class="archivo-embed">
                        <a href="<?php echo $archivo_url; ?>" download class="btn-descargar">
                            ⬇ Descargar PDF
                        </a>

                    <?php } else { ?>
                        <a href="<?php echo $archivo_url; ?>" download class="btn-descargar">
                            ⬇ Descargar archivo (<?php echo strtoupper($extension); ?>)
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
