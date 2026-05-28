<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$mensaje = "";

// Verificar ID
if (!isset($_GET['id'])) {
    header("Location: anuncios.php");
    exit();
}

$id = $_GET['id'];

// Buscar anuncio actual
$sql = "SELECT * FROM anuncios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: anuncios.php");
    exit();
}

$anuncio = $resultado->fetch_assoc();

// Buscar grados
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
    } else {
        return "Grado " . $grado['id'];
    }
}

// Actualizar anuncio
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $tipo_destino = $_POST['tipo_destino'];

    $grado_id = null;
    $para_todos = 0;

    if ($tipo_destino == "todos") {
        $para_todos = 1;
        $grado_id = null;
    } else {
        $para_todos = 0;

        if (!empty($_POST['grado_id'])) {
            $grado_id = $_POST['grado_id'];
        } else {
            $mensaje = "Debes seleccionar un grado.";
        }
    }

    if ($mensaje == "") {
        $sql_update = "UPDATE anuncios 
                       SET titulo = ?, contenido = ?, grado_id = ?, para_todos = ?
                       WHERE id = ?";

        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param("ssiii", $titulo, $contenido, $grado_id, $para_todos, $id);

        if ($stmt_update->execute()) {
            header("Location: anuncios.php");
            exit();
        } else {
            $mensaje = "Error al actualizar el anuncio.";
        }
    }
}

$tipo_actual = ($anuncio['para_todos'] == 1) ? "todos" : "grado";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar anuncio - R.M CLASS ACADEMY</title>
    <link rel="stylesheet" href="css_dashboard.css?v=<?php echo time(); ?>">

    <script>
        function mostrarGrados() {
            let tipo = document.getElementById("tipo_destino").value;
            let campoGrado = document.getElementById("campo_grado");

            if (tipo === "grado") {
                campoGrado.style.display = "block";
            } else {
                campoGrado.style.display = "none";
            }
        }

        window.onload = mostrarGrados;
    </script>
</head>
<body>

<div class="layout">

    <?php require_once 'includes/navbar.php'; ?>
<main class="contenido">

        <section class="header">
            <h1>Editar anuncio</h1>
            <p>Actualiza el contenido, destino o grado del anuncio.</p>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-error-admin">
                <?php echo $mensaje; ?>
            </div>
        <?php } ?>

        <section class="formulario-admin">

            <div class="tabla-header">
                <h2>Datos del anuncio</h2>
                <a href="anuncios.php" class="btn-volver">Volver</a>
            </div>

            <form action="editar_anuncio.php?id=<?php echo $id; ?>" method="POST">

                <div class="grupo-form">
                    <label>Título del anuncio</label>
                    <input 
                        type="text" 
                        name="titulo" 
                        value="<?php echo htmlspecialchars($anuncio['titulo']); ?>" 
                        required
                    >
                </div>

                <div class="grupo-form">
                    <label>Contenido</label>
                    <textarea name="contenido" required><?php echo htmlspecialchars($anuncio['contenido']); ?></textarea>
                </div>

                <div class="grupo-form">
                    <label>¿Para quién es el anuncio?</label>
                    <select name="tipo_destino" id="tipo_destino" onchange="mostrarGrados()" required>
                        <option value="todos" <?php if ($tipo_actual == "todos") echo "selected"; ?>>
                            Para todos los estudiantes
                        </option>

                        <option value="grado" <?php if ($tipo_actual == "grado") echo "selected"; ?>>
                            Para un grado específico
                        </option>
                    </select>
                </div>

                <div class="grupo-form" id="campo_grado">
                    <label>Grado</label>
                    <select name="grado_id">
                        <option value="">Seleccione un grado</option>

                        <?php foreach ($grados as $grado) { ?>
                            <option value="<?php echo $grado['id']; ?>"
                                <?php if ($anuncio['grado_id'] == $grado['id']) echo "selected"; ?>>
                                <?php echo nombreGrado($grado); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn-guardar">
                    Actualizar anuncio
                </button>

            </form>

        </section>

    </main>

</div>

</body>
</html>

