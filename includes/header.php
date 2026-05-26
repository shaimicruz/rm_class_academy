<?php
// includes/header.php
// Iniciamos la sesión si no se ha iniciado ya
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Variables por defecto si no están definidas en la página
$page_title = $page_title ?? 'R.M CLASS ACADEMY';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Fuentes Modernas: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- CSS Global -->
    <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
    
    <!-- Opcional: CSS específico de la página -->
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>?v=<?php echo time(); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
