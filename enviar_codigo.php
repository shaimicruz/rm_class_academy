<?php
session_start();
require_once "conexion.php";
require_once "includes/brevo_mailer.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$correo = trim($_POST['correo'] ?? "");
$modo_envio = trim($_POST['modo_envio'] ?? "brevo"); // brevo | txt

$sql = "SELECT id, nombre FROM usuarios WHERE correo = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    header("Location: recuperar_clave.php?error=correo");
    exit();
}

$usuario = $resultado->fetch_assoc();
$codigo = sprintf("%06d", mt_rand(1, 999999));

$sql_update = "UPDATE usuarios SET codigo_verificacion = ? WHERE id = ? LIMIT 1";
$stmt_update = $conexion->prepare($sql_update);
$stmt_update->bind_param("si", $codigo, $usuario['id']);
$stmt_update->execute();

// Modo demostración: guardar código local en TXT (útil para presentar en la escuela sin depender del correo).
/*
if ($modo_envio === "txt") {
    // Guardar en el archivo existente que ya usas en el proyecto.
    // Nota: En hosting compartido puede no existir permiso de escritura; si falla, cae al modo Brevo.
    $ok_txt = @file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . "ultimo_codigo_recuperacion.txt", $codigo);
    if ($ok_txt !== false) {
        $_SESSION['correo_recuperacion'] = $correo;
        header("Location: verificar_codigo.php?demo=1");
        exit();
    }
    // Si no se pudo escribir, intentamos con correo real.
}
*/

$subject = "Código de recuperación - R.M CLASS ACADEMY";
$htmlContent = '
<div style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <div style="background-color: #0f172a; padding: 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">R.M CLASS ACADEMY</h1>
        </div>
        <div style="padding: 30px; text-align: center;">
            <h2 style="color: #333333; font-size: 20px; margin-bottom: 20px;">Código de Verificación</h2>
            <p style="color: #555555; font-size: 16px; margin-bottom: 30px;">Hola <strong>' . htmlspecialchars($usuario['nombre'] ?? '') . '</strong>,<br>Tu código para recuperar el acceso es:</p>
            <div style="display: inline-block; background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 15px 30px; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e293b;">
                ' . $codigo . '
            </div>
            <p style="color: #777777; font-size: 14px; margin-top: 30px;">Si no solicitaste este código, puedes ignorar este mensaje de forma segura.</p>
        </div>
        <div style="background-color: #f8fafc; padding: 15px; text-align: center; border-top: 1px solid #e2e8f0;">
            <p style="color: #94a3b8; font-size: 12px; margin: 0;">&copy; ' . date("Y") . ' R.M Class Academy. Todos los derechos reservados.</p>
        </div>
    </div>
</div>
';

$enviado = brevoSendEmail($correo, $usuario['nombre'] ?? '', $subject, $htmlContent);
if (!$enviado) {
    header("Location: recuperar_clave.php?error=envio");
    exit();
}

$_SESSION['correo_recuperacion'] = $correo;
header("Location: verificar_codigo.php");
exit();

?>

