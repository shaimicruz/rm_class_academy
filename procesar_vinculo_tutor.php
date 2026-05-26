<?php
session_start();
require_once "conexion.php";
require_once "auth.php";

protegerPagina("admin");

$tutor_id = intval($_POST['tutor_id'] ?? 0);
$estudiante_id = intval($_POST['estudiante_id'] ?? 0);

if ($tutor_id <= 0 || $estudiante_id <= 0) {
    header("Location: tutores.php?error=vinculo");
    exit();
}

$stmt_t = $conexion->prepare("SELECT id FROM tutores WHERE id = ? LIMIT 1");
$stmt_t->bind_param("i", $tutor_id);
$stmt_t->execute();
if ($stmt_t->get_result()->num_rows === 0) {
    header("Location: tutores.php?error=vinculo");
    exit();
}

$stmt_e = $conexion->prepare("SELECT id FROM estudiantes WHERE id = ? LIMIT 1");
$stmt_e->bind_param("i", $estudiante_id);
$stmt_e->execute();
if ($stmt_e->get_result()->num_rows === 0) {
    header("Location: tutores.php?error=vinculo");
    exit();
}

$stmt = $conexion->prepare("UPDATE tutores SET estudiante_id = ? WHERE id = ? LIMIT 1");
$stmt->bind_param("ii", $estudiante_id, $tutor_id);
if ($stmt->execute()) {
    header("Location: tutores.php?exito=vinculo");
    exit();
}

header("Location: tutores.php?error=db");
exit();
?>

