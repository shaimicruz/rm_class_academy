<?php
session_start();
include 'conexion.php';
include 'auth.php';

$carpeta = "uploads/tareas/";

// Verificar si llegó el ID
if (!isset($_GET['id'])) {
    header("Location: tarea.php");
    exit();
}

$id = $_GET['id'];

// Buscar la tarea para saber si tiene archivo
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

// Borrar archivo si existe
if (!empty($tarea['archivo'])) {
    $ruta_archivo = $carpeta . $tarea['archivo'];

    if (file_exists($ruta_archivo)) {
        unlink($ruta_archivo);
    }
}

// Eliminar tarea de la base de datos
$sql_delete = "DELETE FROM tareas WHERE id = ?";
$stmt_delete = $conexion->prepare($sql_delete);
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    header("Location: tarea.php");
    exit();
} else {
    echo "Error al eliminar la tarea.";
}
?>