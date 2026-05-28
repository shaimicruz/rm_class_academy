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

function rolId(mysqli $conexion, string $nombreRol): ?int
{
    $stmt = $conexion->prepare("SELECT id FROM roles WHERE nombre = ? LIMIT 1");
    if (!$stmt) return null;
    $stmt->bind_param("s", $nombreRol);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows !== 1) return null;
    $row = $res->fetch_assoc();
    return intval($row['id']);
}

if ($accion === 'crear') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave = (string)($_POST['clave'] ?? '');
    $clave_confirm = (string)($_POST['clave_confirm'] ?? '');
    $rol_objetivo = trim($_POST['rol'] ?? 'profesor'); // profesor | admin

    if ($rol_objetivo !== 'profesor' && $rol_objetivo !== 'admin') {
        $rol_objetivo = 'profesor';
    }

    // Check if email exists
    $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ? LIMIT 1");
    $stmt_check->bind_param("s", $correo);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: profesores.php?error=correo_existe");
        exit();
    }

    if ($clave !== $clave_confirm || !claveSegura($clave)) {
        header("Location: profesores.php?error=clave");
        exit();
    }

    $rol_id = rolId($conexion, $rol_objetivo);
    if (!$rol_id) {
        header("Location: profesores.php?error=db");
        exit();
    }

    $hash = password_hash($clave, PASSWORD_DEFAULT);
    $estado = 'activo';
    $apellido = '';
    $telefono = '';

    $sql = "INSERT INTO usuarios (nombre, apellido, correo, clave, telefono, rol_id, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssis", $nombre, $apellido, $correo, $hash, $telefono, $rol_id, $estado);

    if ($stmt->execute()) {
        header("Location: profesores.php?exito=creado");
    } else {
        header("Location: profesores.php?error=db");
    }
    exit();
}

if ($accion === 'editar') {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave = (string)($_POST['clave'] ?? '');
    $clave_confirm = (string)($_POST['clave_confirm'] ?? '');
    $rol_objetivo = trim($_POST['rol'] ?? 'profesor'); // profesor | admin

    if ($rol_objetivo !== 'profesor' && $rol_objetivo !== 'admin') {
        $rol_objetivo = 'profesor';
    }

    // Check if email exists for other users
    $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ? LIMIT 1");
    $stmt_check->bind_param("si", $correo, $id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        header("Location: profesores.php?error=correo_existe");
        exit();
    }

    $rol_id = rolId($conexion, $rol_objetivo);
    if (!$rol_id) {
        header("Location: profesores.php?error=db");
        exit();
    }

    if (!empty($clave)) {
        if ($clave !== $clave_confirm || !claveSegura($clave)) {
            header("Location: profesores.php?error=clave");
            exit();
        }
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET nombre = ?, correo = ?, clave = ?, rol_id = ? WHERE id = ? LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssii", $nombre, $correo, $hash, $rol_id, $id);
    } else {
        $sql = "UPDATE usuarios SET nombre = ?, correo = ?, rol_id = ? WHERE id = ? LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssii", $nombre, $correo, $rol_id, $id);
    }

    if ($stmt->execute()) {
        header("Location: profesores.php?exito=editado");
    } else {
        header("Location: profesores.php?error=db");
    }
    exit();
}

if ($accion === 'eliminar') {
    $id = intval($_GET['id'] ?? 0);
    $session_user_id = intval($_SESSION['usuario_id'] ?? 0);

    if ($id <= 0 || $id === $session_user_id) {
        header("Location: profesores.php?error=db");
        exit();
    }

    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: profesores.php?exito=eliminado");
    } else {
        header("Location: profesores.php?error=db");
    }
    exit();
}

header("Location: profesores.php");
exit();

?>

