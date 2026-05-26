<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$mensaje = "";
$carpeta = "uploads/materiales/";

if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

if (!isset($_GET['id'])) {
    header("Location: materiales.php");
    exit();
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM materiales WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: materiales.php");
    exit();
}

$material = $resultado->fetch_assoc();

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
    $archivo_nombre = $material['archivo'];

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

    $sql_update = "UPDATE materiales
                   SET titulo = ?, descripcion = ?, grado_id = ?, archivo = ?
                   WHERE id = ?";

    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("ssisi", $titulo, $descripcion, $grado_id, $archivo_nombre, $id);

    if ($stmt_update->execute()) {
        header("Location: materiales.php");
        exit();
    }

    $mensaje = "Error al actualizar el material.";
}

$page_title = "Editar material - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Editar material</h1>
            <p>Actualiza el contenido, grado asignado y archivo publicado.</p>
        </section>

        <section class="formulario-admin">
            <div class="tabla-header">
                <h2>Datos del material</h2>
                <a href="materiales.php" class="btn-volver">Volver</a>
            </div>

            <?php if ($mensaje != "") { ?>
                <div class="mensaje-error-admin"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php } ?>

            <form action="editar_material.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                <label>Titulo del material</label>
                <input type="text" name="titulo" value="<?php echo htmlspecialchars($material['titulo']); ?>" required>

                <label>Descripcion</label>
                <textarea name="descripcion" required><?php echo htmlspecialchars($material['descripcion']); ?></textarea>

                <label>Grado</label>
                <select name="grado_id" required>
                    <option value="">Seleccione un grado</option>
                    <?php foreach ($grados as $grado) { ?>
                        <option value="<?php echo $grado['id']; ?>" <?php if ($grado['id'] == $material['grado_id']) echo "selected"; ?>>
                            <?php echo htmlspecialchars(nombreGrado($grado)); ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Archivo actual</label>
                <?php if (!empty($material['archivo'])) { ?>
                    <a class="archivo-link abrir-archivo-modal" href="uploads/materiales/<?php echo rawurlencode($material['archivo']); ?>">
                        Ver archivo actual
                    </a>
                <?php } else { ?>
                    <p>No tiene archivo subido.</p>
                <?php } ?>

                <label>Subir nuevo archivo o imagen</label>
                <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">

                <button type="submit" class="btn-guardar">Actualizar material</button>
            </form>
        </section>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
