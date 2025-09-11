<?php
/**
 * Script de vérification de la table des clients
 */

// Désactiver l'affichage des erreurs pour éviter les problèmes d'en-têtes
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclure le fichier wp-load.php en utilisant le chemin absolu
$wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
if (!file_exists($wp_load_path)) {
    die('Erreur: Impossible de trouver wp-load.php');
}

require_once($wp_load_path);

// Vérifier que WordPress est chargé
if (!function_exists('wp')) {
    die('Erreur: WordPress n\'est pas chargé correctement');
}

global $wpdb;

// Vérifier si la table des clients existe
$table_name = $wpdb->prefix . 'ib_clients';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

echo "<h1>Vérification de la table des clients</h1>";

echo "<h2>État de la table</h2>";
echo "<p>La table <strong>$table_name</strong> " . ($table_exists ? "<span style='color:green;'>existe</span>" : "<span style='color:red;'>n'existe pas</span>") . ".</p>";

if ($table_exists) {
    // Vérifier la structure de la table
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    
    echo "<h2>Structure de la table</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Valeur par défaut</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column->Field . "</td>";
        echo "<td>" . $column->Type . "</td>";
        echo "<td>" . $column->Null . "</td>";
        echo "<td>" . $column->Key . "</td>";
        echo "<td>" . ($column->Default ?: 'NULL') . "</td>";
        echo "<td>" . $column->Extra . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Compter le nombre d'entrées
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "<p>Nombre de clients dans la table : <strong>" . intval($count) . "</strong></p>";
    
    // Afficher les 5 premiers clients (s'il y en a)
    if ($count > 0) {
        echo "<h2>Exemple de clients (5 premiers)</h2>";
        $clients = $wpdb->get_results("SELECT * FROM $table_name LIMIT 5");
        
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<th>" . $column->Field . "</th>";
        }
        echo "</tr>";
        
        foreach ($clients as $client) {
            echo "<tr>";
            foreach ($columns as $column) {
                $field = $column->Field;
                echo "<td>" . (isset($client->$field) ? esc_html($client->$field) : '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<h2>Création de la table</h2>";
    echo "<p>La table n'existe pas. Voulez-vous la créer ?</p>";
    echo "<form method='post' action=''>";
    echo "<input type='hidden' name='create_table' value='1'>";
    echo "<input type='submit' value='Créer la table des clients'>";
    echo "</form>";
    
    if (isset($_POST['create_table'])) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) DEFAULT '',
            phone varchar(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY phone (phone)
        ) " . $wpdb->get_charset_collate() . ";";
        
        dbDelta($sql);
        
        echo "<p style='color:green;'>La table a été créée avec succès. <a href=''>Actualiser la page</a>.</p>";
    }
}

echo "<h2>Erreurs éventuelles</h2>";
echo "<pre>";
print_r($wpdb->last_error ?: "Aucune erreur détectée");
echo "</pre>";

// Vérifier si le nonce est correctement généré
$nonce = wp_create_nonce('ib_booking_nonce');
echo "<h2>Vérification du nonce</h2>";
echo "<p>Nonce généré : <code>$nonce</code></p>";
echo "<p>Nonce valide : " . (wp_verify_nonce($nonce, 'ib_booking_nonce') ? "<span style='color:green;'>Oui</span>" : "<span style='color:red;'>Non</span>") . "</p>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>
