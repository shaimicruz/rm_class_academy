<?php
session_start();
require_once "conexion.php";
require_once "auth.php";

protegerPagina("admin");

$accion = $_POST['accion'] ?? '';

if ($accion === 'asignar_grado') {
    $estudiante_id = intval($_POST['estudiante_id'] ?? 0);
    $grado_id = intval($_POST['grado_id'] ?? 0);

    if ($estudiante_id <= 0 || $grado_id <= 0) {
        header("Location: estudiante.php?error=grado");
        exit();
    }

    $stmt_g = $conexion->prepare("SELECT id FROM grados WHERE id = ? LIMIT 1");
    $stmt_g->bind_param("i", $grado_id);
    $stmt_g->execute();
    if ($stmt_g->get_result()->num_rows === 0) {
        header("Location: estudiante.php?error=grado");
        exit();
    }

    $stmt = $conexion->prepare("UPDATE estudiantes SET grado_id = ? WHERE id = ? LIMIT 1");
    $stmt->bind_param("ii", $grado_id, $estudiante_id);
    if ($stmt->execute()) {
        header("Location: estudiante.php?exito=grado");
        exit();
    }

    header("Location: estudiante.php?error=db");
    exit();
}

header("Location: estudiante.php");
exit();
?>

