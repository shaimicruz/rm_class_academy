<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    $sql = "SELECT usuarios.*, roles.nombre AS rol
            FROM usuarios
            INNER JOIN roles ON usuarios.rol_id = roles.id
            WHERE usuarios.correo = ?
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {

        $usuario = $resultado->fetch_assoc();

        // Bloquea inicio de sesión si el correo no está verificado (si existe la columna).
        $col = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'email_verificado'");
        if ($col && $col->num_rows === 1) {
            $emailVer = intval($usuario['email_verificado'] ?? 0);
            if ($emailVer !== 1) {
                $_SESSION['correo_verificacion'] = $usuario['correo'];
                header("Location: verificar_codigo_registro.php?pendiente=1");
                exit();
            }
        }

        if ($usuario['estado'] == 'pendiente') {
            header("Location: index.php?error_pendiente=1");
            exit();
        } elseif ($usuario['estado'] != 'activo') {
            header("Location: index.php?error=1");
            exit();
        }

        if (password_verify($clave, $usuario['clave'])) {

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['rol'] = $usuario['rol'];

            if ($usuario['rol'] == 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            }

            if ($usuario['rol'] == 'estudiante') {
                header("Location: estudiante_dashboard.php");
                exit();
            }

            if ($usuario['rol'] == 'tutor') {
                $sql_tutor = "SELECT t.estudiante_id, e.grado_id 
                              FROM tutores t 
                              LEFT JOIN estudiantes e ON t.estudiante_id = e.id 
                              WHERE t.usuario_id = ?";
                $stmt_t = $conexion->prepare($sql_tutor);
                $stmt_t->bind_param("i", $usuario['id']);
                $stmt_t->execute();
                $res_t = $stmt_t->get_result();
                if ($row_t = $res_t->fetch_assoc()) {
                    $_SESSION['estudiante_id'] = $row_t['estudiante_id'];
                    $_SESSION['estudiante_grado_id'] = $row_t['grado_id'];
                }

                header("Location: tutor_dashboard.php");
                exit();
            }

        } else {
            header("Location: index.php?error=1");
            exit();
        }

    } else {
        header("Location: index.php?error=1");
        exit();
    }

} else {
    header("Location: index.php");
    exit();
}
?>
