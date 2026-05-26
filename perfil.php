<?php
require_once "auth.php";
require_once "conexion.php";

protegerPagina("admin,estudiante,tutor");

$usuario_id = intval($_SESSION['usuario_id'] ?? 0);
if ($usuario_id <= 0) {
    header("Location: index.php");
    exit();
}

$check_foto = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
if ($check_foto && $check_foto->num_rows === 0) {
    $conexion->query("ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL");
}

$mensaje = "";
$tipo_mensaje = "";

$stmt_usuario = $conexion->prepare("SELECT nombre, apellido, correo, telefono, foto_perfil FROM usuarios WHERE id = ? LIMIT 1");
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$res_usuario = $stmt_usuario->get_result();

if ($res_usuario->num_rows === 0) {
    header("Location: logout.php");
    exit();
}

$usuario = $res_usuario->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $apellido = trim($_POST["apellido"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $foto_actual = $usuario["foto_perfil"] ?? null;

    if ($nombre === "" || $correo === "") {
        $mensaje = "Nombre y correo son obligatorios.";
        $tipo_mensaje = "error";
    } else {
        $stmt_correo = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ? AND id <> ? LIMIT 1");
        $stmt_correo->bind_param("si", $correo, $usuario_id);
        $stmt_correo->execute();
        if ($stmt_correo->get_result()->num_rows > 0) {
            $mensaje = "Ese correo ya esta en uso.";
            $tipo_mensaje = "error";
        }
    }

    if ($mensaje === "" && isset($_FILES["foto_perfil"]) && intval($_FILES["foto_perfil"]["error"]) === 0) {
        $ext = strtolower(pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION));
        $permitidas = ["jpg", "jpeg", "png", "webp"];
        if (!in_array($ext, $permitidas, true)) {
            $mensaje = "Formato de foto no valido. Usa JPG, PNG o WEBP.";
            $tipo_mensaje = "error";
        } else {
            $dir = "uploads/perfiles/";
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $nuevo = "perfil_" . $usuario_id . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $dir . $nuevo)) {
                if (!empty($foto_actual) && file_exists($dir . $foto_actual)) {
                    unlink($dir . $foto_actual);
                }
                $foto_actual = $nuevo;
            }
        }
    }

    if ($mensaje === "") {
        $stmt_update = $conexion->prepare("
            UPDATE usuarios
            SET nombre = ?, apellido = ?, correo = ?, telefono = ?, foto_perfil = ?
            WHERE id = ?
        ");
        $stmt_update->bind_param("sssssi", $nombre, $apellido, $correo, $telefono, $foto_actual, $usuario_id);
        if ($stmt_update->execute()) {
            $_SESSION["nombre"] = $nombre;
            $_SESSION["correo"] = $correo;
            $mensaje = "Perfil actualizado correctamente.";
            $tipo_mensaje = "exito";
            $usuario["nombre"] = $nombre;
            $usuario["apellido"] = $apellido;
            $usuario["correo"] = $correo;
            $usuario["telefono"] = $telefono;
            $usuario["foto_perfil"] = $foto_actual;
        } else {
            $mensaje = "No se pudo actualizar el perfil.";
            $tipo_mensaje = "error";
        }
    }
}

$page_title = "Mi perfil - R.M CLASS ACADEMY";
require_once "includes/header.php";
?>

<div class="layout">
    <?php require_once "includes/navbar.php"; ?>

    <main class="contenido">
        <section class="header">
            <h1>Mi perfil</h1>
            <p>Actualiza tus datos personales y tu foto.</p>
        </section>

        <?php if ($mensaje !== ""): ?>
            <div class="mensaje-<?php echo $tipo_mensaje === "exito" ? "exito" : "error"; ?>-admin">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <section class="formulario-admin">
            <div style="display:flex; gap:20px; flex-wrap:wrap; align-items:flex-start;">
                <div style="min-width:180px; text-align:center;">
                    <?php if (!empty($usuario["foto_perfil"])): ?>
                        <img src="uploads/perfiles/<?php echo rawurlencode($usuario["foto_perfil"]); ?>" alt="Foto de perfil" style="width:160px;height:160px;border-radius:8px;object-fit:cover;border:1px solid var(--color-border);">
                    <?php else: ?>
                        <div style="width:160px;height:160px;border-radius:8px;background:var(--color-bg-muted);display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--color-primary);border:1px solid var(--color-border);">
                            SIN FOTO
                        </div>
                    <?php endif; ?>
                </div>

                <form action="perfil.php" method="POST" enctype="multipart/form-data" style="flex:1;min-width:280px;">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required value="<?php echo htmlspecialchars($usuario["nombre"] ?? ""); ?>">

                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($usuario["apellido"] ?? ""); ?>">

                    <label>Correo</label>
                    <input type="email" name="correo" required value="<?php echo htmlspecialchars($usuario["correo"] ?? ""); ?>">

                    <label>Telefono</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario["telefono"] ?? ""); ?>">

                    <label>Foto de perfil</label>
                    <input type="file" name="foto_perfil" accept=".jpg,.jpeg,.png,.webp">

                    <button type="submit" class="btn-guardar">Guardar cambios</button>
                </form>
            </div>
        </section>
    </main>
</div>

<?php require_once "includes/footer.php"; ?>
