<?php
session_start();
require_once "conexion.php";
require_once "auth.php";

protegerPagina("admin");

$accion = $_REQUEST['accion'] ?? '';

function claveSegura(string $clave): bool
{
    return strlen($clave) >= 8
        && preg_match('/[A-Z]/', $clave)
        && preg_match('/[a-z]/', $clave)
        && preg_match('/[0-9]/', $clave)
        && preg_match('/[\W_]/', $clave);
}

if ($accion == 'crear') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $clave = $_POST['clave'];

    // Check if email exists
    $sql_check = "SELECT id FROM usuarios WHERE correo = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: profesores.php?error=correo_existe");
        exit();
    }

    if (!claveSegura($clave)) {
        header("Location: profesores.php?error=clave");
        exit();
    }

    $hash = password_hash($clave, PASSWORD_DEFAULT);
    $rol = 'admin';
    $estado = 'activo';

    $sql = "INSERT INTO usuarios (nombre, correo, clave, rol, estado) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $correo, $hash, $rol, $estado);

    if ($stmt->execute()) {
        header("Location: profesores.php?exito=creado");
    } else {
        header("Location: profesores.php?error=db");
    }
    exit();

} elseif ($accion == 'editar') {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $clave = $_POST['clave'];

    // Check if email exists for other users
    $sql_check = "SELECT id FROM usuarios WHERE correo = ? AND id != ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("si", $correo, $id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: profesores.php?error=correo_existe");
        exit();
    }

    if (!empty($clave)) {
        if (!claveSegura($clave)) {
            header("Location: profesores.php?error=clave");
            exit();
        }
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombre = ?, correo = ?, clave = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $correo, $hash, $id);
    } else {
        $sql = "UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $correo, $id);
    }

    if ($stmt->execute()) {
        header("Location: profesores.php?exito=editado");
    } else {
        header("Location: profesores.php?error=db");
    }
    exit();

} elseif ($accion == 'eliminar') {
    $id = intval($_GET['id']);
    
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: profesores.php?exito=eliminado");
    } else {
        header("Location: profesores.php?error=db");
    }
    exit();

} else {
    header("Location: profesores.php");
    exit();
}
?>
