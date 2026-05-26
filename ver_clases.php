<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("estudiante");

$usuario_id = intval($_SESSION["usuario_id"] ?? 0);
$grado_id = null;

$stmt_est = $conexion->prepare("SELECT grado_id FROM estudiantes WHERE usuario_id = ? LIMIT 1");
$stmt_est->bind_param("i", $usuario_id);
$stmt_est->execute();
$res_est = $stmt_est->get_result();
if ($res_est->num_rows > 0) {
    $grado_id = intval($res_est->fetch_assoc()["grado_id"]);
}

$clases = null;
if ($grado_id > 0) {
    $stmt = $conexion->prepare("
        SELECT clases.*, grados.nombre AS grado
        FROM clases
        LEFT JOIN grados ON clases.grado_id = grados.id
        WHERE clases.grado_id = ?
        ORDER BY clases.fecha DESC, clases.id DESC
    ");
    $stmt->bind_param("i", $grado_id);
    $stmt->execute();
    $clases = $stmt->get_result();
}

$page_title = "Mis clases - R.M CLASS ACADEMY";
require_once "includes/header.php";
?>

<style>
    .btn-ver-clase {
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
    .btn-ver-clase:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(18,41,84,0.30);
    }
    .clase-fecha {
        font-size: 13px;
        color: #888;
        margin-top: 8px;
    }
    .clase-grado {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(18,41,84,0.08);
        color: var(--azul-oscuro);
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        margin-top: 10px;
    }
</style>

<div class="layout">
    <?php require_once "includes/navbar.php"; ?>

    <main class="contenido">
        <section class="header">
            <h1>Mis clases</h1>
            <p>Aqui puedes ver las clases publicadas para tu curso/grado.</p>
        </section>

        <section class="cards">
            <?php if ($clases && $clases->num_rows > 0): ?>
                <?php while ($clase = $clases->fetch_assoc()): ?>
                    <div class="card">
                        <div class="icono"></div>
                        <h3><?php echo htmlspecialchars($clase["titulo"]); ?></h3>

                        <span class="clase-grado"><?php echo htmlspecialchars($clase["grado"] ?? "-"); ?></span>

                        <p style="margin-top:12px; color:#555; line-height:1.6; font-size:14px;">
                            <?php echo htmlspecialchars(mb_substr($clase["descripcion"], 0, 100)); ?>
                            <?php if (mb_strlen($clase["descripcion"]) > 100) echo "..."; ?>
                        </p>

                        <p class="clase-fecha"><?php echo htmlspecialchars($clase["fecha"]); ?></p>

                        <a class="btn-ver-clase" href="detalle_clase.php?id=<?php echo intval($clase["id"]); ?>">
                            Ver clase
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card" style="grid-column: 1/-1; text-align:center;">
                    <div class="icono"></div>
                    <h3>Aun no tienes clases</h3>
                    <p>No hay clases publicadas para tu curso/grado por el momento.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php require_once "includes/footer.php"; ?>
