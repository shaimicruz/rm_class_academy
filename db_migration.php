<?php
require_once "conexion.php";

$sql1 = "ALTER TABLE usuarios ADD COLUMN verificado TINYINT(1) DEFAULT 0;";
$sql2 = "ALTER TABLE usuarios ADD COLUMN codigo_verificacion VARCHAR(6) NULL;";

if ($conexion->query($sql1) === TRUE) {
    echo "Columna verificado agregada.\n";
} else {
    echo "Error agregando verificado: " . $conexion->error . "\n";
}

if ($conexion->query($sql2) === TRUE) {
    echo "Columna codigo_verificacion agregada.\n";
} else {
    echo "Error agregando codigo_verificacion: " . $conexion->error . "\n";
}

$conexion->close();
?>
