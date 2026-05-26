<?php

require_once "config/conexion.php";

$nombre = "Profesora";
$apellido = "Admin";
$correo = "admin@rmclass.com";
$contrasena = password_hash("123456", PASSWORD_DEFAULT);
$telefono = "8090000000";
$id_rol = 1;

try {
    $sql = "INSERT INTO usuarios 
            (id_rol, nombre, apellido, correo, contrasena, telefono)
            VALUES 
            (:id_rol, :nombre, :apellido, :correo, :contrasena, :telefono)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":id_rol" => $id_rol,
        ":nombre" => $nombre,
        ":apellido" => $apellido,
        ":correo" => $correo,
        ":contrasena" => $contrasena,
        ":telefono" => $telefono
    ]);

    echo "<h2>Usuario administrador creado correctamente.</h2>";
    echo "<p>Correo: admin@rmclass.com</p>";
    echo "<p>Contraseña: 123456</p>";
    echo "<a href='login.php'>Ir al login</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
