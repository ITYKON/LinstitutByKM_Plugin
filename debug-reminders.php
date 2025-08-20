<?php
// Vérifier les droits d'administration
if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

// Récupérer la configuration
echo '<h2>Configuration des rappels</h2>';

// Options de base
$options = [
    'ib_reminder_enable' => 'Rappels activés',
    'ib_reminder_time' => 'Heure d\'envoi',
    'ib_push_enable' => 'Notifications push activées',
    'ib_whatsapp_enable' => 'WhatsApp activé'
];

echo '<table border="1" cellpadding="5">';
echo '<tr><th>Option</th><th>Valeur</th></tr>';

foreach ($options as $option => $label) {
    $value = get_option($option, 'Non défini');
    echo "<tr><td>$label</td><td>" . esc_html($value) . "</td></tr>";
}

echo '</table>';

// Vérifier le prochain rappel programmé
$next = wp_next_scheduled('ib_cron_reminders');
echo '<p>Prochain rappel programmé : ' . ($next ? date('d/m/Y H:i:s', $next) : 'Aucun') . '</p>';

// Vérifier les réservations d'aujourd'hui
try {
    global $wpdb;
    $today = current_time('Y-m-d');
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ib_bookings WHERE date = %s ORDER BY start_time ASC",
        $today
    ));

    if ($bookings) {
        echo '<h3>Réservations aujourd\'hui ('.count($bookings).')</h3>';
        echo '<ul>';
        foreach ($bookings as $b) {
            echo "<li>{$b->client_name} - {$b->service_name} - {$b->start_time}</li>";
        }
        echo '</ul>';
    } else {
        echo '<p>Aucune réservation aujourd\'hui</p>';
    }
} catch (Exception $e) {
    echo '<p>Erreur: ' . esc_html($e->getMessage()) . '</p>';
}
