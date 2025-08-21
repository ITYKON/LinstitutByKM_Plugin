<?php
/**
 * Script pour ajouter la colonne working_days à la table des employés
 */

// Vérifier si on est dans le contexte WordPress
if (!defined('ABSPATH')) {
    // Si non, charger WordPress
    require_once('c:\\Users\\Ordi\\Local Sites\\test-plugin\\app\\public\\wp-load.php');
}

global $wpdb;
$table_name = $wpdb->prefix . 'ib_employees';

// Vérifier si la colonne existe déjà
$column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'working_days'");

if (empty($column_exists)) {
    // Ajouter la colonne working_days de type LONGTEXT
    $sql = "ALTER TABLE $table_name ADD working_days LONGTEXT NULL COMMENT 'Jours de travail au format JSON'";
    $result = $wpdb->query($sql);
    
    if ($result === false) {
        echo "Erreur lors de l'ajout de la colonne working_days: " . $wpdb->last_error;
    } else {
        // Mettre à jour les enregistrements existants avec une valeur par défaut
        $default_days = json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET working_days = %s WHERE working_days IS NULL",
            $default_days
        ));
        
        echo "La colonne working_days a été ajoutée avec succès à la table $table_name.\n";
        echo "Tous les employés ont été mis à jour avec les jours de travail par défaut.\n";
    }
} else {
    echo "La colonne working_days existe déjà dans la table $table_name.\n";
}

// Afficher la structure de la table pour vérification
echo "\nStructure de la table $table_name :\n";
$columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
foreach ($columns as $column) {
    echo "- {$column->Field} : {$column->Type}\n";}

// Afficher un échantillon de données
echo "\nExemple de données (3 premiers employés) :\n";
$employees = $wpdb->get_results("SELECT id, name, working_days FROM $table_name LIMIT 3");
foreach ($employees as $employee) {
    echo "ID: {$employee->id}, Nom: {$employee->name}, Jours: {$employee->working_days}\n";}
