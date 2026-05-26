<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

// Verificar si llegó el ID
if (!isset($_GET['id'])) {
    header("Location: anuncios.php");
    exit();
}

$id = $_GET['id'];

// Verificar si el anuncio existe
$sql = "SELECT * FROM anuncios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: anuncios.php");
    exit();
}

// Eliminar anuncio
$sql_delete = "DELETE FROM anuncios WHERE id = ?";
$stmt_delete = $conexion->prepare($sql_delete);
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    header("Location: anuncios.php");
    exit();
} else {
    echo "Error al eliminar el anuncio.";
}
?>