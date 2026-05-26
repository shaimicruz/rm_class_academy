<?php
// migrar_codigos_clases.php
// Ejecutar UNA SOLA VEZ para agregar columna código e inscripciones
require_once "conexion.php";

$pasos = [];

// 1. Agregar columna codigo a clases (si no existe)
$check = $conexion->query("SHOW COLUMNS FROM clases LIKE 'codigo'");
if ($check->num_rows === 0) {
    if ($conexion->query("ALTER TABLE clases ADD COLUMN codigo VARCHAR(10) NULL")) {
        $pasos[] = "✔ Columna 'codigo' agregada a tabla clases.";
    } else {
        $pasos[] = "✖ Error agregando columna codigo: " . $conexion->error;
    }
} else {
    $pasos[] = "ℹ Columna 'codigo' ya existe en clases.";
}

// 2. Generar códigos para clases que no tienen
$clases_sin_codigo = $conexion->query("SELECT id FROM clases WHERE codigo IS NULL OR codigo = ''");
$generados = 0;
if ($clases_sin_codigo && $clases_sin_codigo->num_rows > 0) {
    while ($c = $clases_sin_codigo->fetch_assoc()) {
        do {
            $codigo = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
            $existe = $conexion->query("SELECT id FROM clases WHERE codigo = '$codigo' LIMIT 1");
        } while ($existe->num_rows > 0);

        $conexion->query("UPDATE clases SET codigo = '$codigo' WHERE id = " . $c['id']);
        $generados++;
    }
    $pasos[] = "✔ Códigos generados para $generados clases existentes.";
}

// 3. Crear tabla inscripciones_clases
$check2 = $conexion->query("SHOW TABLES LIKE 'inscripciones_clases'");
if ($check2->num_rows === 0) {
    $sql_tabla = "CREATE TABLE inscripciones_clases (
        id INT AUTO_INCREMENT PRIMARY KEY,
        estudiante_id INT NOT NULL,
        clase_id INT NOT NULL,
        fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unico (estudiante_id, clase_id)
    )";
    if ($conexion->query($sql_tabla)) {
        $pasos[] = "✔ Tabla 'inscripciones_clases' creada correctamente.";
    } else {
        $pasos[] = "✖ Error creando tabla: " . $conexion->error;
    }
} else {
    $pasos[] = "ℹ Tabla 'inscripciones_clases' ya existe.";
}

// 4. Hacer código único (agregar unique si no tiene)
$check3 = $conexion->query("SHOW INDEX FROM clases WHERE Key_name = 'codigo_unico'");
if ($check3->num_rows === 0) {
    $conexion->query("ALTER TABLE clases ADD UNIQUE KEY codigo_unico (codigo)");
    $pasos[] = "✔ Índice UNIQUE agregado a clases.codigo.";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración - R.M CLASS ACADEMY</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f4f8; padding: 40px; }
        .card { background: white; border-radius: 12px; padding: 30px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #122954; margin-bottom: 20px; }
        .paso { padding: 10px 14px; margin: 8px 0; border-radius: 8px; background: #f6f7ec; font-size: 15px; }
        .paso.ok { background: #e8f5e9; color: #2e7d32; }
        .paso.err { background: #ffebee; color: #c62828; }
        .paso.info { background: #e3f2fd; color: #1565c0; }
        a { display: inline-block; margin-top: 20px; background: #122954; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <h1>🔧 Migración de base de datos</h1>
    <?php foreach ($pasos as $p): 
        $clase = strpos($p, '✔') !== false ? 'ok' : (strpos($p, '✖') !== false ? 'err' : 'info');
    ?>
        <div class="paso <?php echo $clase; ?>"><?php echo $p; ?></div>
    <?php endforeach; ?>
    <a href="admin_dashboard.php">← Volver al panel</a>
</div>
</body>
</html>
