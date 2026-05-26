<?php
require_once "conexion.php";

$sql1 = "ALTER TABLE usuarios MODIFY COLUMN estado ENUM('activo', 'inactivo', 'pendiente') DEFAULT 'activo'";
if ($conexion->query($sql1) === TRUE) {
    echo "usuarios.estado alterado.\n";
} else {
    echo "Error alterando usuarios.estado: " . $conexion->error . "\n";
}

$sql2 = "ALTER TABLE tutores ADD COLUMN estudiante_id INT(11) DEFAULT NULL";
if ($conexion->query($sql2) === TRUE) {
    echo "tutores.estudiante_id agregado.\n";
    $sql3 = "ALTER TABLE tutores ADD CONSTRAINT fk_tutor_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE SET NULL ON UPDATE CASCADE";
    if ($conexion->query($sql3) === TRUE) {
        echo "Foreign key agregada a tutores.\n";
    } else {
        echo "Error agregando FK a tutores: " . $conexion->error . "\n";
    }
} else {
    echo "Error agregando estudiante_id: " . $conexion->error . "\n";
}

$conexion->close();
?>
