<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$mensaje = "";
$carpeta = "uploads/tareas/";

if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

if (!isset($_GET['id'])) {
    header("Location: tarea.php");
    exit();
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM tareas WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: tarea.php");
    exit();
}

$tarea = $resultado->fetch_assoc();

$grados = [];
$sql_grados = "SELECT * FROM grados ORDER BY id ASC";
$result_grados = $conexion->query($sql_grados);

while ($fila = $result_grados->fetch_assoc()) {
    $grados[] = $fila;
}

function nombreGrado($grado) {
    if (isset($grado['nombre_grado'])) {
        return $grado['nombre_grado'];
    } elseif (isset($grado['nombre'])) {
        return $grado['nombre'];
    } elseif (isset($grado['grado'])) {
        return $grado['grado'];
    }

    return "Grado " . $grado['id'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $grado_id = intval($_POST['grado_id']);
    $fecha_entrega = $_POST['fecha_entrega'];
    $archivo_nombre = $tarea['archivo'];

    if (!empty($_FILES['archivo']['name'])) {
        $nombre_original = $_FILES['archivo']['name'];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array($extension, $permitidos, true)) {
            $mensaje = "Tipo de archivo no permitido. Solo imagenes, PDF, Word y Excel.";
        } else {
        $nuevo_archivo = time() . "_" . rand(1000, 9999) . "." . $extension;
        $ruta_destino = $carpeta . $nuevo_archivo;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
            if (!empty($archivo_nombre) && file_exists($carpeta . $archivo_nombre)) {
                unlink($carpeta . $archivo_nombre);
            }

            $archivo_nombre = $nuevo_archivo;
        }
        }
    }

    $sql_update = "UPDATE tareas
                   SET titulo = ?, descripcion = ?, grado_id = ?, fecha_entrega = ?, archivo = ?
                   WHERE id = ?";

    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("ssissi", $titulo, $descripcion, $grado_id, $fecha_entrega, $archivo_nombre, $id);

    if ($stmt_update->execute()) {
        header("Location: tarea.php");
        exit();
    }

    $mensaje = "Error al actualizar la tarea.";
}

$page_title = "Editar tarea - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Editar tarea</h1>
            <p>Actualiza la informacion, la fecha de entrega y el archivo publicado.</p>
        </section>

        <section class="formulario-admin">
            <div class="tabla-header">
                <h2>Datos de la tarea</h2>
                <a href="tarea.php" class="btn-volver">Volver</a>
            </div>

            <?php if ($mensaje != "") { ?>
                <div class="mensaje-error-admin"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php } ?>

            <form action="editar_tarea.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                <label>Titulo de la tarea</label>
                <input type="text" name="titulo" value="<?php echo htmlspecialchars($tarea['titulo']); ?>" required>

                <label>Descripcion</label>
                <textarea name="descripcion" required><?php echo htmlspecialchars($tarea['descripcion']); ?></textarea>

                <label>Grado</label>
                <select name="grado_id" required>
                    <option value="">Seleccione un grado</option>
                    <?php foreach ($grados as $grado) { ?>
                        <option value="<?php echo $grado['id']; ?>" <?php if ($grado['id'] == $tarea['grado_id']) echo "selected"; ?>>
                            <?php echo htmlspecialchars(nombreGrado($grado)); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Fecha de entrega</label>
                <input type="date" name="fecha_entrega" value="<?php echo htmlspecialchars($tarea['fecha_entrega']); ?>" required>

                <label>Archivo actual</label>
                <?php if (!empty($tarea['archivo'])) { ?>
                    <a class="archivo-link abrir-archivo-modal" href="uploads/tareas/<?php echo rawurlencode($tarea['archivo']); ?>">
                        Ver archivo actual
                    </a>
                <?php } else { ?>
                    <p>No tiene archivo subido.</p>
                <?php } ?>

                <label>Subir nuevo archivo o imagen</label>
                <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">

                <button type="submit" class="btn-guardar">Actualizar tarea</button>
            </form>
        </section>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
