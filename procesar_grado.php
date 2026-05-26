<?php
session_start();
require_once "conexion.php";
require_once "auth.php";
require_once "includes/schema_helpers.php";

protegerPagina("admin");

$accion = $_REQUEST['accion'] ?? '';

try {
    asegurarCodigoAccesoGrados($conexion);
} catch (Throwable $e) {
    error_log($e->getMessage());
    header("Location: grados.php?error=migracion");
    exit();
}

if ($accion == 'crear') {
    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre === '') {
        header("Location: grados.php?error=nombre");
        exit();
    }

    $codigo = generarCodigoAccesoGrado($conexion);

    $sql = "INSERT INTO grados (nombre, codigo_acceso) VALUES (?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $nombre, $codigo);

    if ($stmt->execute()) {
        header("Location: grados.php?exito=creado");
    } else {
        header("Location: grados.php?error=db");
    }
    exit();

} elseif ($accion == 'editar') {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');

    if ($id <= 0 || $nombre === '') {
        header("Location: grados.php?error=nombre");
        exit();
    }

    $sql = "UPDATE grados SET nombre = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $nombre, $id);

    if ($stmt->execute()) {
        header("Location: grados.php?exito=editado");
    } else {
        header("Location: grados.php?error=db");
    }
    exit();

} elseif ($accion == 'regenerar') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        header("Location: grados.php?error=db");
        exit();
    }

    $codigo = generarCodigoAccesoGrado($conexion);

    $sql = "UPDATE grados SET codigo_acceso = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $codigo, $id);

    if ($stmt->execute()) {
        header("Location: grados.php?exito=regenerado");
    } else {
        header("Location: grados.php?error=db");
    }
    exit();

} elseif ($accion == 'eliminar') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        header("Location: grados.php?error=db");
        exit();
    }
    
    $sql_check = "SELECT id FROM estudiantes WHERE grado_id = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: grados.php?error=en_uso");
        exit();
    }

    $sql = "DELETE FROM grados WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: grados.php?exito=eliminado");
    } else {
        header("Location: grados.php?error=db");
    }
    exit();

} else {
    header("Location: grados.php");
    exit();
}
?>
