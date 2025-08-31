<?php
/**
 * Script pour vérifier la table des absences
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Vérifier les capacités utilisateur
if (!current_user_can('manage_options')) {
    wp_die('Accès non autorisé. Vous devez être administrateur pour accéder à cette page.');
}

// Récupérer le préfixe de la base de données
global $wpdb;
$table_name = $wpdb->prefix . 'ib_employee_absences';

// Vérifier si la table existe
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;

// Afficher les résultats
echo "<h1>Vérification de la table des absences</h1>";
echo "<p>Table: <strong>{$table_name}</strong></p>";

if (!$table_exists) {
    echo "<div style='color: red; font-weight: bold;'>ERREUR: La table n'existe pas dans la base de données.</div>";
    
    // Afficher la requête de création
    echo "<h3>Pour créer la table, exécutez cette requête SQL :</h3>";
    echo "<pre>" . 
         "CREATE TABLE IF NOT EXISTS {$table_name} (\n" .
         "    id bigint(20) NOT NULL AUTO_INCREMENT,\n" .
         "    employee_id bigint(20) NOT NULL,\n" .
         "    start_date date NOT NULL,\n" .
         "    end_date date NOT NULL,\n" .
         "    type varchar(50) NOT NULL DEFAULT 'absence',\n" .
         "    reason text,\n" .
         "    status varchar(20) NOT NULL DEFAULT 'approved',\n" .
         "    created_by bigint(20) NOT NULL,\n" .
         "    created_at datetime NOT NULL,\n" .
         "    updated_at datetime NOT NULL,\n" .
         "    PRIMARY KEY  (id),\n" .
         "    KEY employee_id (employee_id),\n" .
         "    KEY start_date (start_date),\n" .
         "    KEY end_date (end_date)\n" .
         ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;" .
         "</pre>";
    
    exit;
}

// Afficher la structure de la table
echo "<h3>Structure de la table :</h3>";
$columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}");

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
echo "<h3>Dernières absences enregistrées :</h3>";
$absences = $wpdb->get_results("
    SELECT a.*, e.name as employee_name 
    FROM {$table_name} a
    LEFT JOIN {$wpdb->prefix}ib_employees e ON a.employee_id = e.id
    ORDER BY a.start_date DESC 
    LIMIT 10
");

if (empty($absences)) {
    echo "<p>Aucune absence trouvée dans la base de données.</p>";
} else {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Employé</th><th>Début</th><th>Fin</th><th>Type</th><th>Statut</th><th>Créé par</th><th>Créé le</th></tr>";
    
    foreach ($absences as $absence) {
        echo "<tr>";
        echo "<td>{$absence->id}</td>";
        echo "<td>" . esc_html($absence->employee_name) . " (ID: {$absence->employee_id})" . "</td>";
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
?>
