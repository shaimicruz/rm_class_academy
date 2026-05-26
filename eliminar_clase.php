<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin");

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $buscar = $conexion->prepare("SELECT archivo FROM clases WHERE id = ?");
    $buscar->bind_param("i", $id);
    $buscar->execute();

    $resultado = $buscar->get_result();

    if ($resultado->num_rows > 0) {

        $clase = $resultado->fetch_assoc();

        if (!empty($clase['archivo'])) {
            $ruta = "uploads_clases/" . $clase['archivo'];

            if (file_exists($ruta)) {
                unlink($ruta);
            }
        }

        $eliminar = $conexion->prepare("DELETE FROM clases WHERE id = ?");
        $eliminar->bind_param("i", $id);
        $eliminar->execute();
    }
}

header("Location: clases.php");
exit();
?>