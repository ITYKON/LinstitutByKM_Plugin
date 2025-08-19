<?php
// Script d'import des clients historiques depuis les réservations
// Usage : accès direct par un administrateur connecté

define('WP_USE_THEMES', false);
require_once dirname(__FILE__) . '/../../../wp-load.php';

if (!current_user_can('manage_options')) {
    wp_die('Accès refusé.');
}

require_once plugin_dir_path(__FILE__) . '/includes/class-clients.php';
require_once plugin_dir_path(__FILE__) . '/includes/class-bookings.php';

global $wpdb;
$table_bookings = $wpdb->prefix . 'ib_bookings';
$table_clients = $wpdb->prefix . 'ib_clients';

// Récupérer tous les clients uniques (par email) dans les réservations
$bookings = $wpdb->get_results("SELECT DISTINCT client_email, client_name, client_phone FROM $table_bookings WHERE client_email != ''");

$imported = 0;
$skipped = 0;
foreach ($bookings as $b) {
    $email = trim(strtolower($b->client_email));
    if (!$email) continue;
    $existing = IB_Clients::get_by_email($email);
    if ($existing) {
        $skipped++;
        continue;
    }
    // Ajout du client
    IB_Clients::add($b->client_name, $email, $b->client_phone);
    $imported++;
}

echo '<h2>Import terminé</h2>';
echo '<p>Clients importés : <b>' . $imported . '</b></p>';
echo '<p>Clients déjà existants ignorés : <b>' . $skipped . '</b></p>';
echo '<a href="' . admin_url('admin.php?page=institut-booking-clients') . '">Voir la page Clients</a>';
