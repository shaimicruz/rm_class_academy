<?php
require_once "auth.php";

protegerPagina("estudiante");

header("Location: ver_clases.php");
exit();
?>
