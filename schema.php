<?php
require 'conexion.php';

$tables = [
    'roles',
    'usuarios',
    'grados',
    'estudiantes',
    'tutores',
    'clases',
    'materiales',
    'tareas',
    'anuncios',
    'calendario',
    'eventos_calendario',
    'excusas',
    'estados_excusa',
    'inscripciones_clases',
    'calificaciones',
];

foreach ($tables as $table) {
    try {
        $result = $conexion->query("SHOW CREATE TABLE `$table`");

        if (!$result) {
            continue;
        }

        $row = $result->fetch_assoc();
        echo $row['Create Table'] . "\n\n";
    } catch (mysqli_sql_exception $e) {
        echo "-- Tabla no disponible: $table\n\n";
    }
}
?>
