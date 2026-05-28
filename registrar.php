<?php
session_start();
require_once "conexion.php";
require_once "includes/schema_helpers.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit();
}

$nombre = trim($_POST['nombre'] ?? "");
$apellido = "";
$correo = trim($_POST['correo'] ?? "");
$clave = trim($_POST['clave'] ?? "");
$rol_nombre = trim($_POST['rol'] ?? "");
$telefono = "";
$estado = "activo";

if ($rol_nombre !== "estudiante" && $rol_nombre !== "tutor") {
    header("Location: index.php?error=1");
    exit();
}

// Validación estricta de contraseña.
$clave_ok = strlen($clave) >= 8
    && preg_match('/[A-Z]/', $clave)
    && preg_match('/[a-z]/', $clave)
    && preg_match('/[0-9]/', $clave)
    && preg_match('/[\W_]/', $clave);
if (!$clave_ok) {
    header("Location: index.php?error_clave=1");
    exit();
}

$estudiante_id_asignado = null;
$grado_id_asignado = null;

if ($rol_nombre === "estudiante") {
    try {
        asegurarCodigoAccesoGrados($conexion);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        header("Location: index.php?error_codigo=invalido");
        exit();
    }

    // Código de acceso al grado: opcional al registrarse.
    $codigo_acceso = strtoupper(trim($_POST['codigo_acceso'] ?? ''));
    if ($codigo_acceso !== "") {
        $sqlGrado = $conexion->prepare("SELECT id FROM grados WHERE codigo_acceso = ? LIMIT 1");
        $sqlGrado->bind_param("s", $codigo_acceso);
        $sqlGrado->execute();
        $resGrado = $sqlGrado->get_result();
        if ($resGrado->num_rows == 0) {
            header("Location: index.php?error_codigo=invalido");
            exit();
        }
        $gradoRow = $resGrado->fetch_assoc();
        $grado_id_asignado = intval($gradoRow['id']);
    }
}

if ($rol_nombre === "tutor") {
    $estado = "pendiente";
    $matricula = trim($_POST['matricula_estudiante'] ?? '');

    $sqlEst = $conexion->prepare("SELECT id FROM estudiantes WHERE matricula = ? LIMIT 1");
    $sqlEst->bind_param("s", $matricula);
    $sqlEst->execute();
    $resEst = $sqlEst->get_result();

    if ($resEst->num_rows == 0) {
        header("Location: index.php?error_matricula=1");
        exit();
    }
    $estRow = $resEst->fetch_assoc();
    $estudiante_id_asignado = intval($estRow['id']);
}

// Verificar si el correo ya existe.
$verificar = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ? LIMIT 1");
$verificar->bind_param("s", $correo);
$verificar->execute();
if ($verificar->get_result()->num_rows > 0) {
    header("Location: index.php?correo=existe");
    exit();
}

// Buscar el rol en la tabla roles.
$sqlRol = $conexion->prepare("SELECT id FROM roles WHERE nombre = ? LIMIT 1");
$sqlRol->bind_param("s", $rol_nombre);
$sqlRol->execute();
$resultadoRol = $sqlRol->get_result();
if ($resultadoRol->num_rows == 0) {
    die("Error: el rol seleccionado no existe en la base de datos.");
}

$rol = $resultadoRol->fetch_assoc();
$rol_id = intval($rol['id']);
$clave_segura = password_hash($clave, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, apellido, correo, clave, telefono, rol_id, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssssis", $nombre, $apellido, $correo, $clave_segura, $telefono, $rol_id, $estado);

if (!$stmt->execute()) {
    echo "Error al registrar usuario: " . $conexion->error;
    exit();
}

$usuario_id = intval($stmt->insert_id);

if ($rol_nombre === "estudiante") {
    $matricula = "EST-" . str_pad((string)$usuario_id, 4, "0", STR_PAD_LEFT);

    if ($grado_id_asignado !== null) {
        $insertEstudiante = $conexion->prepare("INSERT INTO estudiantes (usuario_id, grado_id, matricula) VALUES (?, ?, ?)");
        $insertEstudiante->bind_param("iis", $usuario_id, $grado_id_asignado, $matricula);
    } else {
        $insertEstudiante = $conexion->prepare("INSERT INTO estudiantes (usuario_id, matricula) VALUES (?, ?)");
        $insertEstudiante->bind_param("is", $usuario_id, $matricula);
    }
    $insertEstudiante->execute();
}

if ($rol_nombre === "tutor") {
    $parentesco = "Pendiente";
    $insertTutor = $conexion->prepare("INSERT INTO tutores (usuario_id, parentesco, estudiante_id) VALUES (?, ?, ?)");
    $insertTutor->bind_param("isi", $usuario_id, $parentesco, $estudiante_id_asignado);
    $insertTutor->execute();
}

header("Location: index.php?registro=ok");
exit();

?>

