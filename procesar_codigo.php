<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['correo_recuperacion'])) {
    $codigo = trim($_POST['codigo']);
    $correo = $_SESSION['correo_recuperacion'];

    $sql = "SELECT id FROM usuarios WHERE correo = ? AND codigo_verificacion = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $correo, $codigo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $usuario = $resultado->fetch_assoc();
        $_SESSION['id_recuperacion'] = $usuario['id'];
        $_SESSION['codigo_verificado'] = true;
        
        // Limpiamos el código por seguridad para que no se re-use
        $sql_update = "UPDATE usuarios SET codigo_verificacion = NULL WHERE id = ?";
        $stmt_update = $conexion->prepare($sql_update);
        $stmt_update->bind_param("i", $usuario['id']);
        $stmt_update->execute();

        header("Location: nueva_clave.php");
        exit();
    } else {
        header("Location: verificar_codigo.php?error=codigo");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
