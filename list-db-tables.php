<?php
/**
 * Script pour lister les tables de la base de données du plugin
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Vérifier les capacités utilisateur
if (!current_user_can('manage_options')) {
    wp_die('Accès non autorisé. Vous devez être administrateur pour accéder à cette page.');
}

global $wpdb;

// Récupérer toutes les tables
$tables = $wpdb->get_col("SHOW TABLES");

// Filtrer les tables du plugin
$plugin_tables = array_filter($tables, function($table) use ($wpdb) {
    return strpos($table, $wpdb->prefix . 'ib_') === 0;
});

// Afficher les résultats
echo "<h1>Tables de la base de données</h1>";

// Tables du plugin
echo "<h2>Tables du plugin (" . count($plugin_tables) . ")</h2>";
if (count($plugin_tables) > 0) {
    echo "<ul>";
    foreach ($plugin_tables as $table) {
        $row_count = $wpdb->get_var("SELECT COUNT(*) FROM `$table`");
        echo "<li><strong>$table</strong> - $row_count lignes</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Aucune table du plugin trouvée.</p>";
}

// Toutes les tables
echo "<h2>Toutes les tables (" . count($tables) . ")</h2>";
echo "<ul>";
foreach ($tables as $table) {
    $row_count = $wpdb->get_var("SELECT COUNT(*) FROM `$table`");
    echo "<li><strong>$table</strong> - $row_count lignes</li>";
}
echo "</ul>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    h1, h2 {
        color: #333;
    }
    ul {
        list-style-type: none;
        padding: 0;
    }
    li {
        padding: 8px;
        border-bottom: 1px solid #eee;
    }
</style>
