<?php
// Script de suppression des fichiers temporaires d'import
$files = [
    __DIR__ . '/import-services-demo.php',
    // Ajoutez ici d'autres fichiers temporaires si besoin
];

foreach ($files as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo basename($file) . " supprimé.<br>\n";
    }
}
echo 'Nettoyage terminé.';
