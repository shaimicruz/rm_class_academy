<?php
session_start();
require_once "conexion.php";
require_once "includes/brevo_mailer.php";

if (!isset($_SESSION['correo_verificacion'])) {
    header("Location: index.php");
    exit();
}

$correo = $_SESSION['correo_verificacion'];

$sql = "SELECT id, nombre FROM usuarios WHERE correo = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();
if ($resultado->num_rows !== 1) {
    header("Location: index.php");
    exit();
}

$usuario = $resultado->fetch_assoc();
$codigo = sprintf("%06d", mt_rand(1, 999999));

$stmt_update = $conexion->prepare("UPDATE usuarios SET codigo_verificacion = ? WHERE id = ?");
$stmt_update->bind_param("si", $codigo, $usuario['id']);
$stmt_update->execute();

$subject = "Código de verificación - R.M CLASS ACADEMY";
$message = "Hola " . ($usuario['nombre'] ?? '') . ",\n\nTu código de verificación es: " . $codigo . "\n\nIngresa este código para activar tu cuenta.";

$enviado = brevoSendEmail($correo, $usuario['nombre'] ?? '', $subject, $message);
if (!$enviado) {
    header("Location: verificar_codigo_registro.php?error=envio");
    exit();
}

header("Location: verificar_codigo_registro.php?reenviado=1");
exit();

?>

