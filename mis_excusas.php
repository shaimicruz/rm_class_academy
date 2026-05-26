<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("estudiante,tutor");

$mensaje = "";
$tipo_mensaje = "";
$carpeta = "uploads/excusas/";

if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$tutor_usuario_id = intval($_SESSION['usuario_id'] ?? 0);
if ($tutor_usuario_id <= 0) {
    die("Error: No se encontró el ID del usuario en la sesión.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_estudiante = trim($_POST['nombre_estudiante'] ?? '');
    $curso_estudiante = trim($_POST['curso_estudiante'] ?? '');
    $fecha_ausencia = $_POST['fecha_ausencia'] ?? '';
    $motivo = trim($_POST['motivo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre_estudiante === '' || $fecha_ausencia === '' || $motivo === '' || $descripcion === '') {
        $mensaje = "Completa los campos obligatorios.";
        $tipo_mensaje = "error";
    }

    $evidencia_nombre = "";
    if ($mensaje === "" && !empty($_FILES['evidencia']['name'])) {
        $nombre_original = $_FILES['evidencia']['name'];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

        $extensiones_permitidas = ["jpg", "jpeg", "png", "pdf", "doc", "docx", "xls", "xlsx"];

        if (in_array($extension, $extensiones_permitidas, true)) {
            $evidencia_nombre = time() . "_" . rand(1000, 9999) . "." . $extension;
            $ruta_destino = $carpeta . $evidencia_nombre;

            if (!move_uploaded_file($_FILES['evidencia']['tmp_name'], $ruta_destino)) {
                $mensaje = "No se pudo subir la evidencia.";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "Solo se permiten imágenes, PDF, Word y Excel.";
            $tipo_mensaje = "error";
        }
    }

    if ($mensaje === "") {
        $sql = "INSERT INTO excusas
                (tutor_usuario_id, nombre_estudiante, curso_estudiante, fecha_ausencia, motivo, descripcion, evidencia)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "issssss",
            $tutor_usuario_id,
            $nombre_estudiante,
            $curso_estudiante,
            $fecha_ausencia,
            $motivo,
            $descripcion,
            $evidencia_nombre
        );

        if ($stmt->execute()) {
            $mensaje = "Excusa enviada correctamente. La profesora podrá revisarla.";
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al enviar la excusa.";
            $tipo_mensaje = "error";
        }
    }
}

$sql_excusas = "SELECT * FROM excusas WHERE tutor_usuario_id = ? ORDER BY fecha_envio DESC";
$stmt_excusas = $conexion->prepare($sql_excusas);
$stmt_excusas->bind_param("i", $tutor_usuario_id);
$stmt_excusas->execute();
$excusas = $stmt_excusas->get_result();

function claseEstado(string $estado): string
{
    if ($estado === "Aprobada") return "badge-aprobada";
    if ($estado === "Rechazada") return "badge-rechazada";
    return "badge-pendiente";
}

function textoEstadoTutor(string $estado): string
{
    if ($estado === "Aprobada") return "Aprobada";
    if ($estado === "Rechazada") return "No aprobada";
    return "Pendiente / En revisión";
}

$page_title = "Excusas - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<style>
    .grid-excusas {
        display: grid;
        grid-template-columns: 0.95fr 1.05fr;
        gap: 18px;
        align-items: start;
    }

    .excusa-item {
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        background: var(--color-bg-surface);
        box-shadow: var(--shadow-sm);
        padding: 18px;
    }

    .excusa-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }

    .excusa-top h3 {
        margin: 0 0 4px;
        font-size: 16px;
    }

    .excusa-meta {
        color: var(--color-text-muted);
        font-size: 12px;
        font-weight: 650;
    }

    .badge-aprobada { color: var(--color-success); background: var(--color-success-bg); border-color: rgba(36,117,77,.18); }
    .badge-rechazada { color: var(--color-error); background: var(--color-error-bg); border-color: rgba(179,38,30,.18); }
    .badge-pendiente { color: var(--color-warning); background: var(--color-warning-bg); border-color: rgba(155,109,32,.2); }

    .excusa-detalle {
        margin-top: 10px;
        color: var(--color-text-muted);
        line-height: 1.6;
    }

    .respuesta-box {
        margin-top: 12px;
        padding: 14px;
        border-radius: var(--radius-md);
        background: var(--color-bg-muted);
        border-left: 4px solid var(--color-primary);
        color: var(--color-text-main);
    }

    @media (max-width: 1050px) {
        .grid-excusas { grid-template-columns: 1fr; }
    }
</style>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Gestión de excusas</h1>
            <p>Envía una excusa con evidencia y revisa el estado y la respuesta de la profesora.</p>
        </section>

        <?php if ($mensaje !== "") { ?>
            <div class="<?php echo $tipo_mensaje === 'exito' ? 'mensaje-exito-admin' : 'mensaje-error-admin'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php } ?>

        <section class="grid-excusas">
            <section class="formulario-admin">
                <h2>Enviar nueva excusa</h2>
                <form action="mis_excusas.php" method="POST" enctype="multipart/form-data">
                    <div class="grupo-form">
                        <label>Nombre del estudiante</label>
                        <input type="text" name="nombre_estudiante" placeholder="Nombre completo del estudiante" required>
                    </div>

                    <div class="fila-form">
                        <div class="grupo-form">
                            <label>Curso o grado (opcional)</label>
                            <input type="text" name="curso_estudiante" placeholder="Ejemplo: 4to A">
                        </div>
                        <div class="grupo-form">
                            <label>Fecha de ausencia</label>
                            <input type="date" name="fecha_ausencia" required>
                        </div>
                    </div>

                    <div class="grupo-form">
                        <label>Motivo</label>
                        <select name="motivo" required>
                            <option value="">Seleccione un motivo</option>
                            <option value="Salud">Salud</option>
                            <option value="Cita médica">Cita médica</option>
                            <option value="Situación familiar">Situación familiar</option>
                            <option value="Transporte">Transporte</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="grupo-form">
                        <label>Descripción</label>
                        <textarea name="descripcion" placeholder="Explique brevemente la razón de la ausencia..." required></textarea>
                    </div>

                    <div class="grupo-form">
                        <label>Evidencia (opcional)</label>
                        <input type="file" name="evidencia" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                    </div>

                    <button type="submit" class="btn-guardar" style="width:100%;">Enviar excusa</button>
                </form>
            </section>

            <section class="tabla-contenedor">
                <div class="tabla-header">
                    <h2>Mis excusas enviadas</h2>
                </div>

                <?php if ($excusas && $excusas->num_rows > 0) { ?>
                    <div style="display:grid; gap:12px;">
                        <?php while ($excusa = $excusas->fetch_assoc()) { ?>
                            <div class="excusa-item">
                                <div class="excusa-top">
                                    <div>
                                        <h3><?php echo htmlspecialchars($excusa['nombre_estudiante']); ?></h3>
                                        <div class="excusa-meta">
                                            Ausencia: <?php echo date("d/m/Y", strtotime($excusa['fecha_ausencia'])); ?>
                                            | Enviada: <?php echo date("d/m/Y h:i A", strtotime($excusa['fecha_envio'])); ?>
                                        </div>
                                    </div>

                                    <span class="badge <?php echo claseEstado($excusa['estado']); ?>">
                                        <?php echo textoEstadoTutor($excusa['estado']); ?>
                                    </span>
                                </div>

                                <div class="excusa-detalle">
                                    <strong>Motivo:</strong> <?php echo htmlspecialchars($excusa['motivo']); ?><br>
                                    <?php echo nl2br(htmlspecialchars($excusa['descripcion'])); ?>
                                </div>

                                <?php if (!empty($excusa['evidencia'])) { ?>
                                    <div style="margin-top:12px;">
                                        <a class="archivo-link abrir-archivo-modal" href="uploads/excusas/<?php echo rawurlencode($excusa['evidencia']); ?>">
                                            Ver evidencia
                                        </a>
                                    </div>
                                <?php } ?>

                                <?php if ($excusa['estado'] !== "Pendiente" || !empty($excusa['respuesta_admin'])) { ?>
                                    <div class="respuesta-box">
                                        <strong>Respuesta oficial:</strong><br>
                                        <strong>Resultado:</strong> <?php echo textoEstadoTutor($excusa['estado']); ?>
                                        <?php if (!empty($excusa['fecha_revision'])) { ?>
                                            <br><strong>Fecha de revisión:</strong> <?php echo date("d/m/Y h:i A", strtotime($excusa['fecha_revision'])); ?>
                                        <?php } ?>
                                        <?php if (!empty($excusa['respuesta_admin'])) { ?>
                                            <br><br><?php echo nl2br(htmlspecialchars($excusa['respuesta_admin'])); ?>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="sin-datos">Todavía no has enviado excusas.</div>
                <?php } ?>
            </section>
        </section>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>

