<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$mensaje = "";

// Guardar evento
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
        $grado_id = !empty($_POST['grado_id']) ? $_POST['grado_id'] : null;
    }

    if ($tipo_destino == "grado" && $grado_id == null) {
        $mensaje = "Debes seleccionar un grado.";
    } else {
        $sql = "INSERT INTO calendario 
                (titulo, descripcion, fecha_evento, hora_evento, lugar, grado_id, para_todos)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param(
            "sssssii",
            $titulo,
            $descripcion,
            $fecha_evento,
            $hora_evento,
            $lugar,
            $grado_id,
            $para_todos
        );

        if ($stmt->execute()) {
            $mensaje = "Evento creado correctamente.";
        } else {
            $mensaje = "Error al crear el evento.";
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

$grados_por_id = [];

foreach ($grados as $g) {
    $grados_por_id[$g['id']] = nombreGrado($g);
}

// Buscar eventos
$sql_eventos = "SELECT * FROM calendario ORDER BY fecha_evento ASC, hora_evento ASC, id DESC";
$result_eventos = $conexion->query($sql_eventos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario - R.M CLASS ACADEMY</title>
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
    </script>
</head>

<body>

<div class="layout">

    <?php require_once 'includes/navbar.php'; ?>
<main class="contenido">

        <section class="header">
            <h1>Gestión del Calendario</h1>
            <p>Crea, revisa, edita y elimina eventos académicos.</p>
        </section>

        <?php if ($mensaje != "") { ?>
            <div class="<?php echo ($mensaje == 'Evento creado correctamente.') ? 'mensaje-exito-admin' : 'mensaje-error-admin'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php } ?>

        <section class="formulario-admin">

            <h2>Crear nuevo evento</h2>

            <form action="calendario.php" method="POST">

                <div class="grupo-form">
                    <label>Título del evento</label>
                    <input type="text" name="titulo" required>
                </div>

                <div class="grupo-form">
                    <label>Descripción</label>
                    <textarea name="descripcion" required></textarea>
                </div>

                <div class="fila-form">
                    <div class="grupo-form">
                        <label>Fecha del evento</label>
                        <input type="date" name="fecha_evento" required>
                    </div>

                    <div class="grupo-form">
                        <label>Hora del evento</label>
                        <input type="time" name="hora_evento">
                    </div>
                </div>

                <div class="grupo-form">
                    <label>Lugar</label>
                    <input type="text" name="lugar" placeholder="Ejemplo: Aula 2, salón principal, virtual...">
                </div>

                <div class="grupo-form">
                    <label>Ã‚¿Para quién es el evento?</label>
                    <select name="tipo_destino" id="tipo_destino" onchange="mostrarGrados()" required>
                        <option value="todos">Para todos los estudiantes</option>
                        <option value="grado">Para un grado específico</option>
                    </select>
                </div>

                <div class="grupo-form" id="campo_grado" style="display:none;">
                    <label>Grado</label>
                    <select name="grado_id">
                        <option value="">Seleccione un grado</option>

                        <?php foreach ($grados as $grado) { ?>
                            <option value="<?php echo $grado['id']; ?>">
                                <?php echo nombreGrado($grado); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn-guardar">
                    Guardar evento
                </button>

            </form>

        </section>

        <section class="tabla-contenedor">

            <div class="tabla-header">
                <h2>Eventos registrados</h2>
            </div>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Título</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Lugar</th>
                            <th>Destino</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result_eventos && $result_eventos->num_rows > 0) { ?>
                            <?php $contador = 1; ?>

                            <?php while ($evento = $result_eventos->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo $contador++; ?></td>

                                    <td><?php echo htmlspecialchars($evento['titulo']); ?></td>

                                    <td><?php echo $evento['fecha_evento']; ?></td>

                                    <td>
                                        <?php 
                                            echo !empty($evento['hora_evento']) 
                                                ? htmlspecialchars($evento['hora_evento']) 
                                                : "Sin hora"; 
                                        ?>
                                    </td>

                                    <td>
                                        <?php 
                                            echo !empty($evento['lugar']) 
                                                ? htmlspecialchars($evento['lugar']) 
                                                : "No especificado"; 
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                            if ($evento['para_todos'] == 1) {
                                                echo "Todos";
                                            } else {
                                                echo isset($grados_por_id[$evento['grado_id']]) 
                                                    ? $grados_por_id[$evento['grado_id']] 
                                                    : "Grado no especificado";
                                            }
                                        ?>
                                    </td>

                                    <td>
                                        <a href="editar_evento.php?id=<?php echo $evento['id']; ?>" class="btn-editar">
                                            Editar
                                        </a>

                                        <a href="eliminar_evento.php?id=<?php echo $evento['id']; ?>" 
                                           class="btn-eliminar"
                                           onclick="return confirm('Ã‚¿Seguro que quieres eliminar este evento?');">
                                            Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>
                            <tr>
                                <td colspan="7" class="sin-datos">
                                    Todavía no hay eventos registrados.
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </section>

    </main>

</div>

</body>
</html>

