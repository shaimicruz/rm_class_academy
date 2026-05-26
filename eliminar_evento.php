<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");


if (!isset($_GET['id'])) {
    header("Location: calendario.php");
    exit();
}

$id = $_GET['id'];

$sql = "SELECT * FROM calendario WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: calendario.php");
    exit();
}

// Eliminar evento
$sql_delete = "DELETE FROM calendario WHERE id = ?";
$stmt_delete = $conexion->prepare($sql_delete);
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    header("Location: calendario.php");
    exit();
} else {
    echo "Error al eliminar el evento.";
}
?>