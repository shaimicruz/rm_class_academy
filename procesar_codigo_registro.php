<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['correo_verificacion'])) {
    header("Location: index.php");
    exit();
}

$codigo = trim($_POST['codigo'] ?? "");
$correo = $_SESSION['correo_verificacion'];

$sql = "SELECT id, estado FROM usuarios WHERE correo = ? AND codigo_verificacion = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $correo, $codigo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    header("Location: verificar_codigo_registro.php?error=codigo");
    exit();
}

$usuario = $resultado->fetch_assoc();
$usuario_id = intval($usuario['id']);
$estado_actual = $usuario['estado'] ?? '';

// Marca email verificado.
$col = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'email_verificado'");
if ($col && $col->num_rows === 0) {
    $conexion->query("ALTER TABLE usuarios ADD COLUMN email_verificado TINYINT(1) NOT NULL DEFAULT 0");
}

$stmt_up = $conexion->prepare("UPDATE usuarios SET codigo_verificacion = NULL, email_verificado = 1 WHERE id = ? LIMIT 1");
$stmt_up->bind_param("i", $usuario_id);
$stmt_up->execute();

// Cambia estado a activo si estaba esperando verificación.
if ($estado_actual === 'pendiente_verificacion') {
    $conexion->query("UPDATE usuarios SET estado = 'activo' WHERE id = " . $usuario_id);
}

unset($_SESSION['correo_verificacion']);
header("Location: index.php?registro=ok");
exit();

?>

