<?php
// migracion_codigos.php — Ejecutar UNA sola vez para agregar codigo_acceso a la tabla grados
require_once 'conexion.php';

echo "<pre>";

// 1. Agregar columna codigo_acceso si no existe
$result = $conexion->query("SHOW COLUMNS FROM grados LIKE 'codigo_acceso'");
if ($result->num_rows === 0) {
    if ($conexion->query("ALTER TABLE grados ADD codigo_acceso VARCHAR(20) UNIQUE NULL")) {
        echo "✅ Columna 'codigo_acceso' agregada a la tabla 'grados'.\n";
    } else {
        echo "❌ Error al agregar columna: " . $conexion->error . "\n";
    }
} else {
    echo "ℹ️ La columna 'codigo_acceso' ya existe.\n";
}

// 2. Generar códigos para grados existentes que no tienen uno
$grados = $conexion->query("SELECT id FROM grados WHERE codigo_acceso IS NULL OR codigo_acceso = ''");
if ($grados && $grados->num_rows > 0) {
    while ($g = $grados->fetch_assoc()) {
        $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $upd = $conexion->prepare("UPDATE grados SET codigo_acceso = ? WHERE id = ?");
        $upd->bind_param("si", $codigo, $g['id']);
        $upd->execute();
        echo "✅ Código generado para grado ID {$g['id']}: {$codigo}\n";
    }
} else {
    echo "ℹ️ Todos los grados ya tienen código.\n";
}

echo "\n✅ Migración completada. <a href='grados.php'>Ir a Grados</a> — Puedes eliminar este archivo.";
echo "</pre>";
?>
