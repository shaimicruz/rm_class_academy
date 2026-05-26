<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_recuperacion']) && $_SESSION['codigo_verificado'] === true) {
    $nueva_clave = $_POST['nueva_clave'];
    $confirmar_clave = $_POST['confirmar_clave'];
    $id_usuario = $_SESSION['id_recuperacion'];

    $es_segura = strlen($nueva_clave) >= 8
        && preg_match('/[A-Z]/', $nueva_clave)
        && preg_match('/[a-z]/', $nueva_clave)
        && preg_match('/[0-9]/', $nueva_clave)
        && preg_match('/[\W_]/', $nueva_clave);

    if ($es_segura && $nueva_clave === $confirmar_clave) {
        $hash_clave = password_hash($nueva_clave, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios SET clave = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $hash_clave, $id_usuario);
        
        if ($stmt->execute()) {
            // Limpiar variables de sesión de recuperación
            unset($_SESSION['correo_recuperacion']);
            unset($_SESSION['id_recuperacion']);
            unset($_SESSION['codigo_verificado']);

            header("Location: index.php?mensaje=clave_actualizada");
            exit();
        } else {
            // Error al actualizar
            header("Location: nueva_clave.php?error=db");
            exit();
        }
    } else {
        header("Location: nueva_clave.php?error=invalida");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
