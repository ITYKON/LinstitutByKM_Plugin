<?php
// Script pour ajouter la colonne 'status' à la table ib_bookings si manquante
require_once dirname(__FILE__) . '/includes/install.php';
global $wpdb;
$table = $wpdb->prefix . 'ib_bookings';
$col = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'status'");
if (empty($col)) {
    $wpdb->query("ALTER TABLE $table ADD COLUMN status varchar(20) NOT NULL DEFAULT 'en_attente'");
    echo "Colonne 'status' ajoutée à $table.\n";
} else {
    echo "Colonne 'status' déjà présente.\n";
}
