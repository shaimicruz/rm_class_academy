<?php
session_start();
require_once "conexion.php";
require_once "includes/schema_helpers.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre    = trim($_POST['nombre']);
    $apellido  = "";
    $correo    = trim($_POST['correo']);
    $clave     = trim($_POST['clave']);
    $rol_nombre = trim($_POST['rol']);
    $telefono  = "";
    $estado    = "activo";

    // Solo permitimos registrar estudiante o tutor
    if ($rol_nombre != "estudiante" && $rol_nombre != "tutor") {
        header("Location: index.php?error=1");
        exit();
    }

    // Validación estricta de contraseña
    if (strlen($clave) < 8 || !preg_match('/[A-Z]/', $clave) || !preg_match('/[a-z]/', $clave) || !preg_match('/[0-9]/', $clave) || !preg_match('/[\W_]/', $clave)) {
        header("Location: index.php?error_clave=1");
        exit();
    }

    // Variables de soporte según el rol
    $estudiante_id_asignado = null;
    $grado_id_asignado = null;

    if ($rol_nombre == "estudiante") {
        try {
            asegurarCodigoAccesoGrados($conexion);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            header("Location: index.php?error_codigo=invalido");
            exit();
        }
    }

    if ($rol_nombre == "estudiante") {
        // Validar código de acceso al grado
        $codigo_acceso = strtoupper(trim($_POST['codigo_acceso'] ?? ''));
        if (empty($codigo_acceso)) {
            header("Location: index.php?error_codigo=vacio");
            exit();
        }
        $sqlGrado = $conexion->prepare("SELECT id FROM grados WHERE codigo_acceso = ? LIMIT 1");
        $sqlGrado->bind_param("s", $codigo_acceso);
        $sqlGrado->execute();
        $resGrado = $sqlGrado->get_result();
        if ($resGrado->num_rows == 0) {
            header("Location: index.php?error_codigo=invalido");
            exit();
        }
        $gradoRow = $resGrado->fetch_assoc();
        $grado_id_asignado = $gradoRow['id'];
    }

    if ($rol_nombre == "tutor") {
        $estado = "pendiente";
        $matricula = trim($_POST['matricula_estudiante'] ?? '');
        
        $sqlEst = $conexion->prepare("SELECT id FROM estudiantes WHERE matricula = ?");
        $sqlEst->bind_param("s", $matricula);
        $sqlEst->execute();
        $resEst = $sqlEst->get_result();
        
        if ($resEst->num_rows == 0) {
            header("Location: index.php?error_matricula=1");
            exit();
        }
        $estRow = $resEst->fetch_assoc();
        $estudiante_id_asignado = $estRow['id'];
    }

    // Verificar si el correo ya existe
    $verificar = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $verificar->bind_param("s", $correo);
    $verificar->execute();
    if ($verificar->get_result()->num_rows > 0) {
        header("Location: index.php?correo=existe");
        exit();
    }

    // Buscar el rol en la tabla roles
    $sqlRol = $conexion->prepare("SELECT id FROM roles WHERE nombre = ? LIMIT 1");
    $sqlRol->bind_param("s", $rol_nombre);
    $sqlRol->execute();
    $resultadoRol = $sqlRol->get_result();

    if ($resultadoRol->num_rows == 0) {
        die("Error: el rol seleccionado no existe en la base de datos.");
    }

    $rol = $resultadoRol->fetch_assoc();
    $rol_id = $rol['id'];

    $clave_segura = password_hash($clave, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nombre, apellido, correo, clave, telefono, rol_id, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssis", $nombre, $apellido, $correo, $clave_segura, $telefono, $rol_id, $estado);

    if ($stmt->execute()) {
        $usuario_id = $stmt->insert_id;

        if ($rol_nombre == "estudiante") {
            $matricula = "EST-" . str_pad($usuario_id, 4, "0", STR_PAD_LEFT);
            $insertEstudiante = $conexion->prepare("INSERT INTO estudiantes (usuario_id, grado_id, matricula) VALUES (?, ?, ?)");
            $insertEstudiante->bind_param("iis", $usuario_id, $grado_id_asignado, $matricula);
            $insertEstudiante->execute();
        }

        if ($rol_nombre == "tutor") {
            $parentesco = "Pendiente";
            $insertTutor = $conexion->prepare("INSERT INTO tutores (usuario_id, parentesco, estudiante_id) VALUES (?, ?, ?)");
            $insertTutor->bind_param("isi", $usuario_id, $parentesco, $estudiante_id_asignado);
            $insertTutor->execute();
        }

        header("Location: index.php?registro=ok");
        exit();

    } else {
        echo "Error al registrar usuario: " . $conexion->error;
    }

} else {
    header("Location: index.php");
    exit();
}
?>
