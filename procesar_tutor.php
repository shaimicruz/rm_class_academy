<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

if (isset($_GET['accion']) && isset($_GET['id'])) {
    $accion = $_GET['accion'];
    $usuario_id = intval($_GET['id']);

    if ($accion == 'aceptar') {
        $sql = "UPDATE usuarios SET estado = 'activo' WHERE id = ? AND rol_id = (SELECT id FROM roles WHERE nombre = 'tutor')";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
    } elseif ($accion == 'rechazar') {
        // En lugar de eliminar, podríamos poner inactivo o eliminarlo. El plan dice rechazar (eliminar).
        $sql = "DELETE FROM usuarios WHERE id = ? AND rol_id = (SELECT id FROM roles WHERE nombre = 'tutor')";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
    }
}

header("Location: tutores.php");
exit();
?>
