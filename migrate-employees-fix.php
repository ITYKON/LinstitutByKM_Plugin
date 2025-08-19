<?php
require_once('../../../wp-load.php');
global $wpdb;
$table = $wpdb->prefix . 'ib_employees';
$wpdb->query("ALTER TABLE $table ADD COLUMN specialty VARCHAR(255) DEFAULT NULL, ADD COLUMN role VARCHAR(100) DEFAULT NULL;");
echo 'Colonnes specialty et role ajoutées !'; 