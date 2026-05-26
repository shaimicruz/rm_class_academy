<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);

    $sql = "SELECT id, nombre FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        $codigo = sprintf("%06d", mt_rand(1, 999999));

        $sql_update = "UPDATE usuarios SET codigo_verificacion = ? WHERE id = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param("si", $codigo, $usuario['id']);
        $stmt_update->execute();

        // Enviar correo (Reemplazar con PHPMailer / SendGrid / Brevo para producción)
        $to = $correo;
        $subject = "Código de Recuperación - R.M CLASS ACADEMY";
        $message = "Hola " . $usuario['nombre'] . ",\n\nTu código de recuperación es: " . $codigo . "\n\nSi no solicitaste esto, ignora este correo.";
        $headers = "From: noreply@rmclassacademy.com";

        // Intentar enviar (En XAMPP local mail() puede fallar, así que lo manejamos)
        @mail($to, $subject, $message, $headers);
        
        // Para propósitos de prueba local, escribimos el código en un log
        file_put_contents("ultimo_codigo_recuperacion.txt", "Correo: $correo | Código: $codigo");

        $_SESSION['correo_recuperacion'] = $correo;
        header("Location: verificar_codigo.php");
        exit();

    } else {
        header("Location: recuperar_clave.php?error=correo");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
