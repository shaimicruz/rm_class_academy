<?php
require_once "conexion.php";

$nombre = "Profesora";
$apellido = "Principal";
$correo = "profesora@rmclass.com";
$clave = password_hash("123456", PASSWORD_DEFAULT);
$telefono = "";
$estado = "activo";

$sqlRol = "SELECT id FROM roles WHERE nombre = 'admin' LIMIT 1";
$resultadoRol = $conexion->query($sqlRol);

if ($resultadoRol->num_rows == 0) {
    die("Error: no existe el rol admin en la tabla roles.");
}

$rol = $resultadoRol->fetch_assoc();
$rol_id = $rol['id'];

$verificar = $conexion->prepare("SELECT id FROM usuarios WHERE rol_id = ? LIMIT 1");
$verificar->bind_param("i", $rol_id);
$verificar->execute();
$resultado = $verificar->get_result();

if ($resultado->num_rows > 0) {
    echo "<h2>Ya existe una profesora/admin registrada.</h2>";
    echo "<p>No se creó otra cuenta porque solo debe existir una profesora.</p>";
    exit();
}

$sql = "INSERT INTO usuarios (nombre, apellido, correo, clave, telefono, rol_id, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssssis", $nombre, $apellido, $correo, $clave, $telefono, $rol_id, $estado);

if ($stmt->execute()) {
    echo "<h2>Profesora creada correctamente</h2>";
    echo "<p><strong>Correo:</strong> profesora@rmclass.com</p>";
    echo "<p><strong>Contraseña:</strong> 123456</p>";
    echo "<p style='color:red;'><strong>Ahora borra el archivo crear_profesora.php por seguridad.</strong></p>";
} else {
    echo "Error al crear profesora: " . $conexion->error;
}
?>