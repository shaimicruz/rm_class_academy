<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function normalizarRol($rol) {
    $rol = strtolower(trim($rol));

    if ($rol == "admin" || $rol == "profesor" || $rol == "profesora") {
        return "admin";
    }

    if ($rol == "estudiante") {
        return "estudiante";
    }

    if ($rol == "tutor") {
        return "tutor";
    }

    return $rol;
}

function protegerPagina($rolNecesario) {

    if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['id_usuario'])) {
        header("Location: index.php");
        exit();
    }

    if (!isset($_SESSION['rol'])) {
        header("Location: index.php");
        exit();
    }

    $rolUsuario = normalizarRol($_SESSION['rol']);

    if (is_array($rolNecesario)) {
        $rolesPermitidos = array_map('normalizarRol', $rolNecesario);
    } else {
        $rolesPermitidos = array_map('normalizarRol', explode(',', $rolNecesario));
    }

    if (in_array($rolUsuario, $rolesPermitidos)) {
        return;
    }

    if ($rolUsuario == "admin") {
        header("Location: admin_dashboard.php");
        exit();
    }

    if ($rolUsuario == "estudiante") {
        header("Location: estudiante_dashboard.php");
        exit();
    }

    if ($rolUsuario == "tutor") {
        header("Location: tutor_dashboard.php");
        exit();
    }

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit();
}

?>