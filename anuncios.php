<?php
session_start();
include 'conexion.php';
include 'auth.php';

$mensaje = "";

// Guardar anuncio
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $tipo_destino = $_POST['tipo_destino'];

    $grado_id = null;
    $para_todos = 0;

    if ($tipo_destino == "todos") {
        $para_todos = 1;
    } else {
        $grado_id = $_POST['grado_id'];
        $para_todos = 0;
    }

    $sql = "INSERT INTO anuncios (titulo, contenido, grado_id, para_todos) 
            VALUES (?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssii", $titulo, $contenido, $grado_id, $para_todos);

    if ($stmt->execute()) {
        $mensaje = "✔ Anuncio creado correctamente.";
        $mensaje_tipo = "exito";
    } else {
        $mensaje = "✖ Error al crear el anuncio.";
        $mensaje_tipo = "error";
    }
}

// Buscar grados
$grados = [];
$sql_grados = "SELECT * FROM grados ORDER BY id ASC";
$result_grados = $conexion->query($sql_grados);

while ($fila = $result_grados->fetch_assoc()) {
    $grados[] = $fila;
}

// Función para mostrar nombre del grado
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

// Buscar anuncios
$sql_anuncios = "SELECT * FROM anuncios ORDER BY fecha_publicacion DESC";
$result_anuncios = $conexion->query($sql_anuncios);

$page_title = "Gestión de Anuncios - R.M CLASS ACADEMY";
require_once 'includes/header.php';
?>

<div class="layout">
    <?php require_once 'includes/navbar.php'; ?>

    <main class="contenido">
        <section class="header">
            <h1>Gestión de Anuncios</h1>
            <p>Crea y administra los anuncios para tus alumnos y tutores.</p>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-exito<?php echo isset($mensaje_tipo) && $mensaje_tipo == 'error' ? ' mensaje-error-admin' : '-admin'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php } ?>

        <section class="formulario-admin">
            <h2>Crear nuevo anuncio</h2>
            <form action="anuncios.php" method="POST">
                <div class="grupo-form">
                    <label>Título del anuncio</label>
                    <input type="text" name="titulo" required placeholder="Escribe el título del anuncio">
                </div>

                <div class="grupo-form">
                    <label>Contenido</label>
                    <textarea name="contenido" required placeholder="Escribe el contenido del anuncio..."></textarea>
                </div>

                <div class="grupo-form">
                    <label>¿Para quién es el anuncio?</label>
                    <select name="tipo_destino" id="tipo_destino" onchange="mostrarGrados()" required>
                        <option value="todos">Para todos los estudiantes</option>
                        <option value="grado">Para un grado específico</option>
                    </select>
                </div>

                <div id="campo_grado" class="grupo-form" style="display:none;">
                    <label>Grado</label>
                    <select name="grado_id">
                        <option value="">Seleccione un grado</option>
                        <?php foreach ($grados as $grado) { ?>
                            <option value="<?php echo $grado['id']; ?>">
                                <?php echo htmlspecialchars(nombreGrado($grado)); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn-guardar">Guardar anuncio</button>
            </form>
        </section>

        <section class="tabla-contenedor">
            <div class="tabla-header">
                <h2>Listado de anuncios</h2>
            </div>
            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Contenido</th>
                            <th>Destino</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($anuncio = $result_anuncios->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $anuncio['id']; ?></td>
                                <td><?php echo htmlspecialchars($anuncio['titulo']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars(substr($anuncio['contenido'], 0, 100)); ?>
                                    <?php if (strlen($anuncio['contenido']) > 100) echo "..."; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($anuncio['para_todos'] == 1) {
                                        echo "<span class='estado activo'>Todos</span>";
                                    } else {
                                        $nombre_g = isset($grados_por_id[$anuncio['grado_id']]) 
                                            ? $grados_por_id[$anuncio['grado_id']] 
                                            : "Sin grado";
                                        echo htmlspecialchars($nombre_g);
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($anuncio['fecha_publicacion']); ?></td>
                                <td>
                                    <a class="btn-editar" style="margin-right:5px;" href="editar_anuncio.php?id=<?php echo $anuncio['id']; ?>">
                                        Editar
                                    </a>
                                    <a class="btn-eliminar"
                                       href="eliminar_anuncio.php?id=<?php echo $anuncio['id']; ?>"
                                       onclick="return confirm('¿Seguro que quieres eliminar este anuncio?');">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<script>
    function mostrarGrados() {
        let tipo = document.getElementById("tipo_destino").value;
        let campoGrado = document.getElementById("campo_grado");
        campoGrado.style.display = (tipo === "grado") ? "block" : "none";
    }
</script>

<?php require_once 'includes/footer.php'; ?>