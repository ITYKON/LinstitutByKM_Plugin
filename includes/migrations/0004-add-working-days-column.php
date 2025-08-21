<?php
/**
 * Migration pour ajouter la colonne working_days à la table des employés
 */

function ib_add_working_days_column() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ib_employees';
    
    // Vérifier si la colonne existe déjà
    $column = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = %s 
        AND TABLE_NAME = %s 
        AND COLUMN_NAME = 'working_days'",
        DB_NAME, $table_name
    ));
    
    if (empty($column)) {
        // Ajouter la colonne working_days de type LONGTEXT
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN working_days LONGTEXT NULL DEFAULT NULL");
        
        // Mettre à jour les enregistrements existants avec une valeur par défaut
        $default_days = json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET working_days = %s",
            $default_days
        ));
        
        error_log('Colonne working_days ajoutée avec succès à la table ' . $table_name);
    }
}

// Exécuter la migration
ib_add_working_days_column();
