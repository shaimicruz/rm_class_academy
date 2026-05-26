<?php
$dir = __DIR__;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$replacements = [
    // Azules viejos a Azul Marino Oficial
    '#122954' => '#122954',
    '#122954' => '#122954',
    '#122954' => '#122954',
    '#122954' => '#122954',
    '#122954' => '#122954',
    '#AD833C' => '#AD833C', // Hover azul a dorado
    
    // Verdes y Naranjas a Dorado
    '#AD833C' => '#AD833C',
    '#AD833C' => '#AD833C',
    
    // Fondos claros a Crema o Blanco
    '#F6F7EC' => '#F6F7EC',
    '#ffffff' => '#ffffff',
    '#F6F7EC' => '#F6F7EC', // Mensaje éxito
    '#122954' => '#122954', // Texto éxito
    
    // Otros grises
    '#122954' => '#122954', // Botón volver a azul marino
    
    // Estudiante layout unificado
    'css_dashboard.css' => 'css_dashboard.css',
    'layout' => 'layout',
    'sidebar' => 'sidebar',
    'logo' => 'logo',
    'menu' => 'menu',
    'contenido' => 'contenido',
    'cards' => 'cards',
    'card' => 'card',
    'header' => 'header',
    'btn-principal' => 'btn-principal'
];

$count = 0;

foreach ($files as $file) {
    if ($file->isFile()) {
        $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
        if ($ext === 'php' || $ext === 'css') {
            $content = file_get_contents($file->getPathname());
            $new_content = $content;
            
            foreach ($replacements as $old => $new) {
                // Para evitar reemplazar colores ya correctos que son similares
                if ($old !== $new) {
                    $new_content = str_ireplace($old, $new, $new_content);
                }
            }
            
            if ($new_content !== $content) {
                file_put_contents($file->getPathname(), $new_content);
                $count++;
            }
        }
    }
}

echo "Archivos modificados: " . $count;
?>
