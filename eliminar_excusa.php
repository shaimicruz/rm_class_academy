<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

$carpeta = "uploads/excusas/";

// Verificar si llegó el ID
if (!isset($_GET['id'])) {
    header("Location: excusas.php");
    exit();
}

$id = $_GET['id'];

// Buscar la excusa para revisar si tiene evidencia
$sql = "SELECT * FROM excusas WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: excusas.php");
    exit();
}

$excusa = $resultado->fetch_assoc();

// Eliminar evidencia si existe
if (!empty($excusa['evidencia'])) {
    $ruta_archivo = $carpeta . $excusa['evidencia'];

    if (file_exists($ruta_archivo)) {
        unlink($ruta_archivo);
    }
}

// Eliminar excusa de la base de datos
$sql_delete = "DELETE FROM excusas WHERE id = ?";
$stmt_delete = $conexion->prepare($sql_delete);
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    header("Location: excusas.php");
    exit();
} else {
    echo "Error al eliminar la excusa.";
}
?>