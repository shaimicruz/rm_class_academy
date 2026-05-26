<?php
session_start();
include 'conexion.php';
include 'auth.php';

$carpeta = "uploads/materiales/";

// Verificar si llegó el ID
if (!isset($_GET['id'])) {
    header("Location: materiales.php");
    exit();
}

$id = $_GET['id'];

// Buscar el material para saber si tiene archivo
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

// Borrar archivo si existe
if (!empty($material['archivo'])) {
    $ruta_archivo = $carpeta . $material['archivo'];

    if (file_exists($ruta_archivo)) {
        unlink($ruta_archivo);
    }
}

// Eliminar material de la base de datos
$sql_delete = "DELETE FROM materiales WHERE id = ?";
$stmt_delete = $conexion->prepare($sql_delete);
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    header("Location: materiales.php");
    exit();
} else {
    echo "Error al eliminar el material.";
}
?>