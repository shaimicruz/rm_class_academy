<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$mensaje = "";

// Verificar ID
if (!isset($_GET['id'])) {
    header("Location: calendario.php");
    exit();
}

$id = $_GET['id'];

// Buscar evento actual
$sql = "SELECT * FROM calendario WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: calendario.php");
    exit();
}

$evento = $resultado->fetch_assoc();

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

// Actualizar evento
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_evento = $_POST['fecha_evento'];
    $hora_evento = !empty($_POST['hora_evento']) ? $_POST['hora_evento'] : null;
    $lugar = trim($_POST['lugar']);
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
        $sql_update = "UPDATE calendario 
                       SET titulo = ?, descripcion = ?, fecha_evento = ?, hora_evento = ?, lugar = ?, grado_id = ?, para_todos = ?
                       WHERE id = ?";

        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param(
            "sssssiii",
            $titulo,
            $descripcion,
            $fecha_evento,
            $hora_evento,
            $lugar,
            $grado_id,
            $para_todos,
            $id
        );

        if ($stmt_update->execute()) {
            header("Location: calendario.php");
            exit();
        } else {
            $mensaje = "Error al actualizar el evento.";
        }
    }
}

$tipo_actual = ($evento['para_todos'] == 1) ? "todos" : "grado";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar evento - R.M CLASS ACADEMY</title>
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
            <h1>Editar evento</h1>
            <p>Modifica los datos del evento académico.</p>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="mensaje-error-admin">
                <?php echo $mensaje; ?>
            </div>
        <?php } ?>

        <section class="formulario-admin">

            <div class="tabla-header">
                <h2>Datos del evento</h2>
                <a href="calendario.php" class="btn-volver">Volver</a>
            </div>

            <form action="editar_evento.php?id=<?php echo $id; ?>" method="POST">

                <div class="grupo-form">
                    <label>Título del evento</label>
                    <input 
                        type="text" 
                        name="titulo" 
                        value="<?php echo htmlspecialchars($evento['titulo']); ?>" 
                        required
                    >
                </div>

                <div class="grupo-form">
                    <label>Descripción</label>
                    <textarea name="descripcion" required><?php echo htmlspecialchars($evento['descripcion']); ?></textarea>
                </div>

                <div class="fila-form">
                    <div class="grupo-form">
                        <label>Fecha del evento</label>
                        <input 
                            type="date" 
                            name="fecha_evento" 
                            value="<?php echo $evento['fecha_evento']; ?>" 
                            required
                        >
                    </div>

                    <div class="grupo-form">
                        <label>Hora del evento</label>
                        <input 
                            type="time" 
                            name="hora_evento" 
                            value="<?php echo !empty($evento['hora_evento']) ? htmlspecialchars($evento['hora_evento']) : ''; ?>"
                        >
                    </div>
                </div>

                <div class="grupo-form">
                    <label>Lugar</label>
                    <input 
                        type="text" 
                        name="lugar" 
                        value="<?php echo htmlspecialchars($evento['lugar']); ?>"
                        placeholder="Ejemplo: Aula 2, salón principal, virtual..."
                    >
                </div>

                <div class="grupo-form">
                    <label>Ã‚¿Para quién es el evento?</label>
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
                                <?php if ($evento['grado_id'] == $grado['id']) echo "selected"; ?>>
                                <?php echo nombreGrado($grado); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn-guardar">
                    Actualizar evento
                </button>

            </form>

        </section>

    </main>

</div>

</body>
</html>

