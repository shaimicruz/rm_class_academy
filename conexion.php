<?php

$servidor = "127.0.0.1";
$usuario = "root";
$clave = "";
$base_datos = "rm_class_academy";
$puerto = 3307;

$conexion = new mysqli(
    $servidor,
    $usuario,
    $clave,
    $base_datos,
    $puerto
);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

?>
