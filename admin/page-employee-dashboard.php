<?php
// admin/page-employee-dashboard.php
if (!defined('ABSPATH')) exit;
if (!function_exists('wp_get_current_user')) {
    require_once(ABSPATH . 'wp-includes/pluggable.php');
}
$current_user = wp_get_current_user();
if (!in_array('ib_employee', (array) $current_user->roles)) {
    wp_die(__('Accès non autorisé', 'institut-booking'));
}

echo '<div class="ib-admin-main">';
echo '<div class="ib-admin-header"><h1>' . esc_html(__('Tableau de bord employé', 'institut-booking')) . '</h1></div>';

// Récupérer les rendez-vous du jour
$today = date('Y-m-d');
$bookings = IB_Bookings::get_by_employee($current_user->ID, $today);

echo '<div class="ib-admin-content">';
echo '<h2>' . esc_html(__('Mes rendez-vous aujourd\'hui', 'institut-booking')) . '</h2>';

if (empty($bookings)) {
    echo '<p>' . esc_html(__('Aucun rendez-vous aujourd\'hui', 'institut-booking')) . '</p>';
} else {
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr>';
    echo '<th>' . esc_html(__('Heure', 'institut-booking')) . '</th>';
    echo '<th>' . esc_html(__('Client', 'institut-booking')) . '</th>';
    echo '<th>' . esc_html(__('Service', 'institut-booking')) . '</th>';
    echo '<th>' . esc_html(__('Statut', 'institut-booking')) . '</th>';
    echo '<th>' . esc_html(__('Actions', 'institut-booking')) . '</th>';
    echo '</tr></thead><tbody>';

    foreach ($bookings as $booking) {
        $client = IB_Clients::get_by_id($booking->client_id);
        $service = IB_Services::get_by_id($booking->service_id);

        echo '<tr>';
        echo '<td>' . esc_html(date('H:i', strtotime($booking->start_time))) . '</td>';
        echo '<td>' . esc_html($client->name) . '</td>';
        echo '<td>' . esc_html($service->name) . '</td>';
        echo '<td>' . esc_html($booking->status) . '</td>';
        echo '<td>';
        echo '<a href="?page=institut-booking-bookings&action=edit&id=' . esc_attr($booking->id) . '" class="button button-small">' . esc_html(__('Voir', 'institut-booking')) . '</a>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

echo '</div>'; // .ib-admin-content
echo '</div>'; // .ib-admin-main
