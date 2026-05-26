<?php
session_start();
include 'conexion.php';
include 'auth.php';

$mensaje = "";
$carpeta = "uploads/materiales/";

// Crear carpeta si no existe
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

// Guardar material
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $grado_id = $_POST['grado_id'];

    $archivo_nombre = "";
    $upload_ok = true;

    if (!empty($_FILES['archivo']['name'])) {
        $nombre_original = $_FILES['archivo']['name'];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

        if (!in_array($extension, $permitidos)) {
            $mensaje = "Error: Solo se permiten imagenes, PDF, Word y Excel.";
            $upload_ok = false;
        } else {
            $archivo_nombre = time() . "_" . rand(1000, 9999) . "." . $extension;
            $ruta_destino = $carpeta . $archivo_nombre;
            move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino);
        }
    }

    if ($upload_ok) {
        $sql = "INSERT INTO materiales (titulo, descripcion, grado_id, archivo) 
                VALUES (?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssis", $titulo, $descripcion, $grado_id, $archivo_nombre);

        if ($stmt->execute()) {
            $mensaje = "Material creado correctamente.";
        } else {
            $mensaje = "Error al crear el material.";
        }
    }
}

// Buscar grados
$grados = [];
$sql_grados = "SELECT * FROM grados ORDER BY id ASC";
$result_grados = $conexion->query($sql_grados);

while ($fila = $result_grados->fetch_assoc()) {
    $grados[] = $fila;
}

// Mostrar nombre del grado
function nombreGrado($grado) {
    if (isset($grado['nombre_grado'])) {
        return $grado['nombre_grado'];
    } elseif (isset($grado['nombre'])) {
        return $grado['nombre'];
    } elseif (isset($grado['grado'])) {
        return $grado['grado'];
    } else {
        return "Grado " . $grado['id'];
    }
}

// Crear arreglo de grados por ID
$grados_por_id = [];
foreach ($grados as $g) {
    $grados_por_id[$g['id']] = nombreGrado($g);
}

// Buscar materiales
$sql_materiales = "SELECT * FROM materiales ORDER BY fecha_publicacion DESC";
$result_materiales = $conexion->query($sql_materiales);

$page_title = "Gestión de Materiales - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Gestión de Materiales</h1>
            <p>Sube y gestiona los recursos y documentos de estudio para tus alumnos.</p>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-exito"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php } ?>

        <section class="formulario-admin">
            <h2>Crear nuevo material</h2>
            <form action="materiales.php" method="POST" enctype="multipart/form-data">
                <div class="grupo-form">
                    <label>Título del material</label>
                    <input type="text" name="titulo" required>
                </div>
                
                <div class="grupo-form">
                    <label>Descripción</label>
                    <textarea name="descripcion" required></textarea>
                </div>

                <div class="grupo-form">
                    <label>Grado</label>
                    <select name="grado_id" required>
                        <option value="">Seleccione un grado</option>
                        <?php foreach ($grados as $grado) { ?>
                            <option value="<?php echo $grado['id']; ?>">
                                <?php echo htmlspecialchars(nombreGrado($grado)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="grupo-form">
                    <label>Archivo o imagen</label>
                    <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                </div>

                <button type="submit" class="btn-guardar">Guardar material</button>
            </form>
        </section>

        <section class="tabla-contenedor">
            <div class="tabla-header">
                <h2>Listado de materiales</h2>
            </div>
            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Grado</th>
                            <th>Fecha</th>
                            <th>Archivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_materiales->num_rows > 0) { ?>
                            <?php while ($material = $result_materiales->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $material['id']; ?></td>
                                    <td><?php echo htmlspecialchars($material['titulo']); ?></td>
                                    <td>
                                        <?php
                                        echo isset($grados_por_id[$material['grado_id']]) 
                                            ? htmlspecialchars($grados_por_id[$material['grado_id']]) 
                                            : "Sin grado";
                                        ?>
                                    </td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($material['fecha_publicacion'])); ?></td>
                                    <td>
                                        <?php if (!empty($material['archivo'])) { ?>
                                            <a class="archivo-link abrir-archivo-modal" href="uploads/materiales/<?php echo htmlspecialchars($material['archivo']); ?>">Ver</a>
                                        <?php } else { ?>
                                            <span style="color:#888;">N/A</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <a class="btn-editar" style="margin-right:5px;" href="editar_material.php?id=<?php echo $material['id']; ?>">Editar</a>
                                        <a class="btn-eliminar" href="eliminar_material.php?id=<?php echo $material['id']; ?>" onclick="return confirm('¿Seguro que quieres eliminar este material?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="6" class="sin-datos">No hay materiales publicados.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
