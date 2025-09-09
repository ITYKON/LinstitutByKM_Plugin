<?php
/**
 * Script de correction pour ajouter la colonne cancel_token
 * Accès : /wp-content/plugins/LinstitutByKM_Plugin/fix-db-column.php
 */

define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Vérifier les droits administrateur
if (!current_user_can('manage_options')) {
    wp_die('Accès refusé. Droits administrateur requis.');
}

// Désactiver la mise en cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

?><!DOCTYPE html>
<html>
<head>
    <title>Correction Base de Données</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Correction de la base de données</h1>
    <?php
    global $wpdb;
    $table_name = $wpdb->prefix . 'ib_bookings';
    
    // Vérifier si la table existe
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    
    if (!$table_exists) {
        echo "<p class='error'>Erreur : La table $table_name n'existe pas !</p>";
    } else {
        echo "<p>Table trouvée : <strong>$table_name</strong></p>";
        
        // Vérifier si la colonne existe déjà
        $column_exists = $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'cancel_token'");
        
        if ($column_exists) {
            echo "<p class='success'>✓ La colonne cancel_token existe déjà.</p>";
        } else {
            // Essayer d'ajouter la colonne
            $result = $wpdb->query("
                ALTER TABLE $table_name 
                ADD COLUMN `cancel_token` VARCHAR(64) NULL DEFAULT NULL,
                ADD INDEX `cancel_token` (`cancel_token`)
            ") !== false;
            
            if ($result) {
                echo "<p class='success'>✓ La colonne cancel_token a été ajoutée avec succès !</p>";
            } else {
                echo "<p class='error'>Erreur lors de l'ajout de la colonne : " . $wpdb->last_error . "</p>";
            }
        }
        
        // Afficher la structure de la table
        echo "<h3>Structure de la table :</h3>";
        $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
        if ($columns) {
            echo "<pre>";
            foreach ($columns as $column) {
                echo str_pad($column->Field, 20) . " | " . str_pad($column->Type, 20) . " | ";
                if ($column->Key === 'PRI') echo 'PRIMARY KEY';
                elseif ($column->Key === 'MUL') echo 'INDEX';
                echo "\n";
            }
            echo "</pre>";
        }
    }
    ?>
    
    <p><a href="<?php echo admin_url(); ?>">← Retour à l'administration</a></p>
    
    <hr>
    <small>
        <strong>Note :</strong> Pour des raisons de sécurité, supprimez ce fichier après utilisation.
    </small>
</body>
</html>
