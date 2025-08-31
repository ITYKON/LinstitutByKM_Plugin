<?php
/**
 * Script pour lister toutes les tables de la base de données
 */

// Vérifier si on est dans WordPress
if (!defined('ABSPATH')) {
    // Si on n'est pas dans WordPress, on définit les constantes nécessaires
    $wp_load_path = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once($wp_load_path);
    } else {
        die('Impossible de charger WordPress');
    }
}

// Vérifier les droits d'administration
if (!current_user_can('manage_options')) {
    wp_die('Accès non autorisé');
}

// Récupérer le préfixe de la base de données
global $wpdb;

// Récupérer toutes les tables de la base de données
$tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);

// Afficher les résultats
echo "<h1>Liste des tables de la base de données</h1>";
echo "<p>Base de données : <strong>" . DB_NAME . "</strong></p>";

echo "<h3>Tables trouvées (" . count($tables) . ") :</h3>";
echo "<ul>";

foreach ($tables as $table) {
    $table_name = $table[0];
    $row_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$table_name}`");
    $table_size = $wpdb->get_row("SHOW TABLE STATUS LIKE '{$table_name}'", ARRAY_A);
    $size = isset($table_size['Data_length']) ? $this->formatBytes($table_size['Data_length']) : 'N/A';
    
    // Vérifier si c'est une table du plugin
    $is_plugin_table = (strpos($table_name, $wpdb->prefix . 'ib_') === 0);
    $row_style = $is_plugin_table ? 'color: #0073aa; font-weight: bold;' : '';
    
    echo "<li style='margin-bottom: 5px; {$row_style}'>";
    echo "<strong>{$table_name}</strong> - ";
    echo "Lignes: {$row_count} - ";
    echo "Taille: {$size}";
    
    // Afficher des informations supplémentaires pour les tables du plugin
    if ($is_plugin_table) {
        $columns = $wpdb->get_results("SHOW COLUMNS FROM `{$table_name}`", ARRAY_A);
        $column_names = array_column($columns, 'Field');
        echo "<br>Colonnes: " . implode(', ', $column_names);
    }
    
    echo "</li>";
}

echo "</ul>";

// Fonction utilitaire pour formater la taille des tables
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Afficher les tables manquantes du plugin
$required_tables = [
    'ib_employee_absences',
    'ib_employees',
    'ib_services',
    'ib_bookings',
    'ib_categories',
    'ib_extras',
    'ib_logs'
];

$missing_tables = [];
foreach ($required_tables as $table) {
    $full_table_name = $wpdb->prefix . $table;
    if (!in_array([$full_table_name], $tables)) {
        $missing_tables[] = $full_table_name;
    }
}

if (!empty($missing_tables)) {
    echo "<h3 style='color: red;'>Tables manquantes du plugin :</h3>";
    echo "<ul>";
    foreach ($missing_tables as $table) {
        echo "<li>{$table}</li>";
    }
    echo "</ul>";
}
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
    h1, h2, h3 {
        color: #23282d;
    }
    ul {
        list-style-type: none;
        padding: 0;
    }
    li {
        padding: 8px;
        border-bottom: 1px solid #eee;
    }
    .notice {
        background: #f8f8f8;
        border-left: 4px solid #0073aa;
        padding: 10px 15px;
        margin: 15px 0;
    }
</style>
