<?php
// Script de mise à jour de la table ib_clients pour ajouter les colonnes manquantes
require_once dirname(__FILE__) . '/../../../wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'ib_clients';

$alter = [];
// Vérifie si la colonne notes existe
$col_notes = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'notes'");
if (empty($col_notes)) {
    $alter[] = "ADD COLUMN notes TEXT NULL";
}
// Vérifie si la colonne tags existe
$col_tags = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'tags'");
if (empty($col_tags)) {
    $alter[] = "ADD COLUMN tags VARCHAR(255) NULL";
}
if ($alter) {
    $sql = "ALTER TABLE $table " . implode(", ", $alter) . ";";
    $wpdb->query($sql);
    echo '<b style="color:green">Colonnes ajoutées avec succès !</b><br>';
    echo '<pre>' . esc_html($sql) . '</pre>';
} else {
    echo '<b style="color:blue">La table possède déjà les colonnes notes et tags.</b>';
}
