<?php
/**
 * Script de débogage pour vérifier la table des absences
 */

defined('ABSPATH') or die('Accès direct non autorisé');

// Vérifier les droits d'administration
if (!current_user_can('manage_options')) {
    wp_die('Accès non autorisé');
}

// Récupérer le préfixe de la base de données
global $wpdb;
$table_name = $wpdb->prefix . 'ib_employee_absences';

echo "<h2>Vérification de la table des absences : {$table_name}</h2>";

// Vérifier si la table existe
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;

if (!$table_exists) {
    echo "<div style='color: red;'>ERREUR : La table {$table_name} n'existe pas dans la base de données.</div>";
    exit;
}

echo "<div style='color: green;'>✓ La table {$table_name} existe.</div>";

// Afficher la structure de la table
$columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");

echo "<h3>Structure de la table :</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";

foreach ($columns as $column) {
    echo "<tr>";
    echo "<td>{$column->Field}</td>";
    echo "<td>{$column->Type}</td>";
    echo "<td>{$column->Null}</td>";
    echo "<td>{$column->Key}</td>";
    echo "<td>" . ($column->Default ?? 'NULL') . "</td>";
    echo "<td>{$column->Extra}</td>";
    echo "</tr>";
}
echo "</table>";

// Afficher les 10 dernières entrées
$absences = $wpdb->get_results("
    SELECT a.*, e.name as employee_name 
    FROM {$table_name} a
    LEFT JOIN {$wpdb->prefix}ib_employees e ON a.employee_id = e.id
    ORDER BY a.start_date DESC 
    LIMIT 10
");

echo "<h3>Dernières absences enregistrées :</h3>";
if (empty($absences)) {
    echo "<p>Aucune absence trouvée dans la base de données.</p>";
} else {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Employé</th><th>Début</th><th>Fin</th><th>Type</th><th>Statut</th><th>Créé par</th><th>Créé le</th></tr>";
    
    foreach ($absences as $absence) {
        echo "<tr>";
        echo "<td>{$absence->id}</td>";
        echo "<td>{$absence->employee_name} (ID: {$absence->employee_id})</td>";
        echo "<td>{$absence->start_date}</td>";
        echo "<td>{$absence->end_date}</td>";
        echo "<td>{$absence->type}</td>";
        echo "<td>{$absence->status}</td>";
        echo "<td>{$absence->created_by}</td>";
        echo "<td>{$absence->created_at}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Vérifier les logs d'erreurs
echo "<h3>Logs d'erreurs récents :</h3>";
$error_log = ini_get('error_log');
if (file_exists($error_log)) {
    $logs = file($error_log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $recent_logs = array_slice($logs, -20); // 20 dernières lignes
    echo "<pre>".implode("\n", $recent_logs)."</pre>";
} else {
    echo "<p>Aucun fichier de log trouvé à : {$error_log}</p>";
}

// Vérifier les requêtes SQL récentes
echo "<h3>Dernières requêtes SQL :</h3>";
if (defined('SAVEQUERIES') && SAVEQUERIES) {
    global $wpdb;
    echo "<pre>";
    print_r($wpdb->queries);
    echo "</pre>";
} else {
    echo "<p>Le débogage des requêtes SQL n'est pas activé. Ajoutez ces lignes dans votre wp-config.php :</p>";
    echo "<pre>
    define('WP_DEBUG', true);
    define('WP_DEBUG_LOG', true);
    define('SAVEQUERIES', true);
    </pre>";
}
?>
