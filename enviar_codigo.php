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

$subject = "Código de recuperación - R.M CLASS ACADEMY";
$message = "Hola " . ($usuario['nombre'] ?? '') . ",\n\nTu código de recuperación es: " . $codigo . "\n\nSi no solicitaste esto, ignora este correo.";

$enviado = brevoSendEmail($correo, $usuario['nombre'] ?? '', $subject, $message);
if (!$enviado) {
    header("Location: recuperar_clave.php?error=envio");
    exit();
}

$_SESSION['correo_recuperacion'] = $correo;
header("Location: verificar_codigo.php");
exit();

?>

