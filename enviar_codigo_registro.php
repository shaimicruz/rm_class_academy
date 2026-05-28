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
$htmlContent = '
<div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div style="background-color: #0f172a; padding: 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">R.M CLASS ACADEMY</h1>
        </div>
        <div style="padding: 30px; text-align: center;">
            <h2 style="color: #333333; font-size: 20px; margin-bottom: 20px;">Activación de Cuenta</h2>
            <p style="color: #555555; font-size: 16px; margin-bottom: 30px;">Hola <strong>' . htmlspecialchars($usuario['nombre'] ?? '') . '</strong>,<br>Tu código para activar tu cuenta es:</p>
            <div style="display: inline-block; background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 15px 30px; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e293b;">
                ' . $codigo . '
            </div>
            <p style="color: #777777; font-size: 14px; margin-top: 30px;">Ingresa este código para activar tu cuenta en la plataforma.</p>
        </div>
        <div style="background-color: #f8fafc; padding: 15px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="color: #94a3b8; font-size: 12px; margin: 0;">&copy; ' . date("Y") . ' R.M Class Academy. Todos los derechos reservados.</p>
        </div>
    </div>
</div>
';

$enviado = brevoSendEmail($correo, $usuario['nombre'] ?? '', $subject, $htmlContent);
if (!$enviado) {
    header("Location: verificar_codigo_registro.php?error=envio");
    exit();
}

header("Location: verificar_codigo_registro.php?reenviado=1");
exit();

?>

