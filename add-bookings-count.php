<?php
// Ajoute la colonne bookings_count à la table ib_clients si elle n'existe pas
require_once dirname(__FILE__) . '/../../../wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'ib_clients';
$col = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'bookings_count'");
if (empty($col)) {
    $wpdb->query("ALTER TABLE $table ADD COLUMN bookings_count INT NOT NULL DEFAULT 1");
    echo '<b style="color:green">Colonne bookings_count ajoutée !</b>';
} else {
    echo '<b style="color:blue">La colonne bookings_count existe déjà.</b>';
}
