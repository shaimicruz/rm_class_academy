<?php
session_start();
include 'conexion.php';
include 'auth.php';

$mensaje = "";

// Crear carpeta automáticamente si no existe
$carpeta = "uploads/tareas/";

if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

// Guardar tarea
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $grado_id = $_POST['grado_id'];
    $fecha_entrega = $_POST['fecha_entrega'];

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
        $sql = "INSERT INTO tareas (titulo, descripcion, grado_id, fecha_entrega, archivo) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssiss", $titulo, $descripcion, $grado_id, $fecha_entrega, $archivo_nombre);

        if ($stmt->execute()) {
            $mensaje = "Tarea creada correctamente.";
        } else {
            $mensaje = "Error al crear la tarea.";
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

// Función para mostrar el nombre del grado aunque el campo tenga otro nombre
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

// Crear arreglo de grados por id
$grados_por_id = [];
foreach ($grados as $g) {
    $grados_por_id[$g['id']] = nombreGrado($g);
}

// Buscar tareas
$sql_tareas = "SELECT * FROM tareas ORDER BY fecha_creacion DESC";
$result_tareas = $conexion->query($sql_tareas);

$page_title = "Gestión de Tareas - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Gestión de Tareas</h1>
            <p>Sube y gestiona las tareas y actividades para tus alumnos.</p>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-exito"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php } ?>

        <section class="formulario-admin">
            <h2>Crear nueva tarea</h2>
            <form action="tarea.php" method="POST" enctype="multipart/form-data">
                <div class="grupo-form">
                    <label>Título de la tarea</label>
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
                    <label>Fecha de entrega</label>
                    <input type="date" name="fecha_entrega" required>
                </div>

                <div class="grupo-form">
                    <label>Archivo o imagen opcional</label>
                    <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                </div>

                <button type="submit" class="btn-guardar">Guardar tarea</button>
            </form>
        </section>

        <section class="tabla-contenedor">
            <div class="tabla-header">
                <h2>Listado de tareas</h2>
            </div>
            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Grado</th>
                            <th>Fecha de entrega</th>
                            <th>Archivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_tareas->num_rows > 0) { ?>
                            <?php while ($tarea = $result_tareas->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $tarea['id']; ?></td>
                                    <td><?php echo htmlspecialchars($tarea['titulo']); ?></td>
                                    <td>
                                        <?php
                                        echo isset($grados_por_id[$tarea['grado_id']]) 
                                            ? htmlspecialchars($grados_por_id[$tarea['grado_id']]) 
                                            : "Sin grado";
                                        ?>
                                    </td>
                                    <td><?php echo date("d/m/Y", strtotime($tarea['fecha_entrega'])); ?></td>
                                    <td>
                                        <?php if (!empty($tarea['archivo'])) { ?>
                                            <a class="archivo-link abrir-archivo-modal" href="uploads/tareas/<?php echo htmlspecialchars($tarea['archivo']); ?>">Ver</a>
                                        <?php } else { ?>
                                            <span style="color:#888;">N/A</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <a class="btn-editar" style="margin-right:5px;" href="editar_tarea.php?id=<?php echo $tarea['id']; ?>">Editar</a>
                                        <a class="btn-eliminar" href="eliminar_tarea.php?id=<?php echo $tarea['id']; ?>" onclick="return confirm('¿Seguro que quieres eliminar esta tarea?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="6" class="sin-datos">No hay tareas publicadas.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
