<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$mensaje = "";
$mensaje_tipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = trim($_POST["titulo"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $fecha = $_POST["fecha"] ?? "";
    $grado_id = intval($_POST["grado_id"] ?? 0);
    $profesor_id = intval($_SESSION["usuario_id"] ?? 0);
    $archivo_nombre = null;

    if ($titulo === "" || $descripcion === "" || $fecha === "" || $grado_id <= 0 || $profesor_id <= 0) {
        $mensaje = "Completa todos los campos obligatorios.";
        $mensaje_tipo = "error";
    }

    if ($mensaje === "" && isset($_FILES["archivo"]) && intval($_FILES["archivo"]["error"]) === 0) {
        $carpeta = "uploads_clases/";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombre_original = $_FILES["archivo"]["name"];
        $archivo_temporal = $_FILES["archivo"]["tmp_name"];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        $extensiones_permitidas = ["jpg", "jpeg", "png", "pdf", "doc", "docx", "xls", "xlsx"];

        if (!in_array($extension, $extensiones_permitidas, true)) {
            $mensaje = "Tipo de archivo no permitido.";
            $mensaje_tipo = "error";
        } else {
            $archivo_nombre = time() . "_" . rand(1000, 9999) . "." . $extension;
            if (!move_uploaded_file($archivo_temporal, $carpeta . $archivo_nombre)) {
                $mensaje = "Error al subir el archivo.";
                $mensaje_tipo = "error";
            }
        }
    }

    if ($mensaje === "") {
        $sql = "INSERT INTO clases (titulo, descripcion, fecha, profesor_id, grado_id, archivo)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssiis", $titulo, $descripcion, $fecha, $profesor_id, $grado_id, $archivo_nombre);

        if ($stmt->execute()) {
            $mensaje = "Clase creada correctamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "Error al crear la clase.";
            $mensaje_tipo = "error";
        }
    }
}

$grados = $conexion->query("SELECT * FROM grados ORDER BY id ASC");
$clases = $conexion->query("
    SELECT clases.id, clases.titulo, clases.descripcion, clases.fecha, clases.archivo, grados.nombre AS grado
    FROM clases
    LEFT JOIN grados ON clases.grado_id = grados.id
    ORDER BY clases.fecha DESC
");

$page_title = "Gestion de Clases - R.M CLASS ACADEMY";
require_once "includes/header.php";
?>

<div class="layout">
    <?php require_once "includes/navbar.php"; ?>

    <main class="contenido">
        <section class="header">
            <h1>Gestion de clases</h1>
            <p>Crea clases y asignalas al curso/grado correspondiente.</p>
        </section>

        <?php if (!empty($mensaje)) { ?>
            <div class="mensaje-<?php echo $mensaje_tipo === "exito" ? "exito" : "error"; ?>-admin">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php } ?>

        <section class="formulario-admin">
            <h2>Crear nueva clase</h2>
            <form action="clases.php" method="POST" enctype="multipart/form-data">
                <div class="grupo-form">
                    <label>Titulo de la clase</label>
                    <input type="text" name="titulo" required>
                </div>
                <div class="grupo-form">
                    <label>Descripcion</label>
                    <textarea name="descripcion" required></textarea>
                </div>
                <div class="grupo-form">
                    <label>Subir documento o foto (opcional)</label>
                    <input type="file" name="archivo" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
                </div>
                <div class="fila-form">
                    <div class="grupo-form">
                        <label>Fecha</label>
                        <input type="date" name="fecha" required>
                    </div>
                    <div class="grupo-form">
                        <label>Grado</label>
                        <select name="grado_id" required>
                            <?php if ($grados): ?>
                                <?php while ($grado = $grados->fetch_assoc()): ?>
                                    <option value="<?php echo intval($grado["id"]); ?>">
                                        <?php echo htmlspecialchars($grado["nombre"]); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-guardar">Guardar clase</button>
            </form>
        </section>

        <section class="tabla-contenedor">
            <div class="tabla-header">
                <h2>Clases publicadas</h2>
            </div>
            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Titulo</th>
                            <th>Descripcion</th>
                            <th>Archivo</th>
                            <th>Fecha</th>
                            <th>Grado</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($clases && $clases->num_rows > 0): ?>
                            <?php $contador = 1; ?>
                            <?php while ($clase = $clases->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $contador++; ?></td>
                                    <td><?php echo htmlspecialchars($clase["titulo"]); ?></td>
                                    <td><?php echo htmlspecialchars(mb_substr($clase["descripcion"], 0, 60)) . (mb_strlen($clase["descripcion"]) > 60 ? "..." : ""); ?></td>
                                    <td>
                                        <?php if (!empty($clase["archivo"])): ?>
                                            <a class="archivo-link abrir-archivo-modal" href="uploads_clases/<?php echo htmlspecialchars($clase["archivo"]); ?>" data-tipo="archivo">Ver</a>
                                        <?php else: ?>
                                            <span style="color:#aaa;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($clase["fecha"]); ?></td>
                                    <td><?php echo htmlspecialchars($clase["grado"] ?? "-"); ?></td>
                                    <td>
                                        <a href="eliminar_clase.php?id=<?php echo intval($clase["id"]); ?>" class="btn-eliminar" onclick="return confirm('Seguro que deseas eliminar esta clase?');">
                                            Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="sin-datos">Todavia no hay clases publicadas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<?php require_once "includes/footer.php"; ?>
