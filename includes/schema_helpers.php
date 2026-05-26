<?php

function generarCodigoAccesoGrado(mysqli $conexion, int $longitud = 8): string
{
    $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $max = strlen($alfabeto) - 1;

    for ($intento = 0; $intento < 30; $intento++) {
        $codigo = '';

        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $alfabeto[random_int(0, $max)];
        }

        $stmt = $conexion->prepare("SELECT id FROM grados WHERE codigo_acceso = ? LIMIT 1");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            return $codigo;
        }
    }

    throw new RuntimeException("No se pudo generar un codigo de acceso unico.");
}

function asegurarCodigoAccesoGrados(mysqli $conexion): void
{
    $columna = $conexion->query("SHOW COLUMNS FROM grados LIKE 'codigo_acceso'");

    if (!$columna) {
        throw new RuntimeException("No se pudo revisar la tabla grados: " . $conexion->error);
    }

    if ($columna->num_rows === 0) {
        $conexion->query("ALTER TABLE grados ADD COLUMN codigo_acceso VARCHAR(20) NULL");
    }

    $pendientes = $conexion->query("SELECT id FROM grados WHERE codigo_acceso IS NULL OR codigo_acceso = ''");

    if ($pendientes) {
        while ($grado = $pendientes->fetch_assoc()) {
            $codigo = generarCodigoAccesoGrado($conexion);
            $stmt = $conexion->prepare("UPDATE grados SET codigo_acceso = ? WHERE id = ?");
            $stmt->bind_param("si", $codigo, $grado['id']);
            $stmt->execute();
        }
    }

    $indice = $conexion->query("SHOW INDEX FROM grados WHERE Column_name = 'codigo_acceso' AND Non_unique = 0");

    if ($indice && $indice->num_rows === 0) {
        try {
            $conexion->query("ALTER TABLE grados ADD UNIQUE KEY codigo_acceso_unico (codigo_acceso)");
        } catch (mysqli_sql_exception $e) {
            error_log("No se pudo crear indice unico para grados.codigo_acceso: " . $e->getMessage());
        }
    }
}

?>
