<?php
/**
 * Script pour vérifier les notifications dans la base de données
 */

define('WP_USE_THEMES', false);

// Chemin vers wp-load.php - ajustez selon votre installation
$wp_load_path = dirname(__FILE__) . '/../../../../../wp-load.php';

if (!file_exists($wp_load_path)) {
    // Essayer un autre chemin alternatif
    $wp_load_path = dirname(__FILE__) . '/../../../../../../../wp-load.php';
    
    if (!file_exists($wp_load_path)) {
        die('Erreur: Impossible de trouver wp-load.php. Veuillez vérifier le chemin.');
    }
}

require_once($wp_load_path);

global $wpdb;
$table = $wpdb->prefix . 'ib_notifications';

// Récupérer les 5 dernières notifications
$notifications = $wpdb->get_results("SELECT id, type, message, link, created_at FROM $table ORDER BY created_at DESC LIMIT 5");

echo "<h2>Dernières notifications dans la base de données</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ID</th><th>Type</th><th>Message</th><th>Lien</th><th>Créée le</th></tr>";

foreach ($notifications as $notif) {
    echo "<tr>";
    echo "<td>" . esc_html($notif->id) . "</td>";
    echo "<td>" . esc_html($notif->type) . "</td>";
    echo "<td>" . esc_html(substr($notif->message, 0, 50)) . (strlen($notif->message) > 50 ? '...' : '') . "</td>";
    echo "<td>" . esc_html($notif->link) . "</td>";
    echo "<td>" . esc_html($notif->created_at) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Vérifier si des notifications pointent vers des liens d'édition
$edition_links = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table WHERE link LIKE %s",
    '%action=edit%'
));

echo "<h3>Statistiques des liens</h3>";
echo "<p>Nombre de notifications pointant vers des pages d'édition: " . (int)$edition_links . "</p>";

// Vérifier si des notifications sont du type 'reservation'
$reservation_links = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table WHERE type = %s",
    'reservation'
));

echo "<p>Nombre total de notifications de type 'reservation': " . (int)$reservation_links . "</p>";

// Vérifier les liens des notifications de type 'reservation'
$reservation_notifications = $wpdb->get_results($wpdb->prepare(
    "SELECT id, link FROM $table WHERE type = %s ORDER BY created_at DESC LIMIT 5",
    'reservation'
));

echo "<h3>Dernières notifications de type 'reservation'</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ID</th><th>Lien</th></tr>";

foreach ($reservation_notifications as $notif) {
    echo "<tr>";
    echo "<td>" . esc_html($notif->id) . "</td>";
    echo "<td>" . esc_html($notif->link) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Vérifier si des notifications sont en double pour la même réservation
$duplicates = $wpdb->get_results(
    "SELECT message, COUNT(*) as count 
     FROM $table 
     WHERE type = 'reservation' 
     GROUP BY message 
     HAVING COUNT(*) > 1 
     ORDER BY count DESC"
);

if (!empty($duplicates)) {
    echo "<h3>Notifications en double détectées</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Message</th><th>Occurrences</th></tr>";
    
    foreach ($duplicates as $dup) {
        echo "<tr>";
        echo "<td>" . esc_html(substr($dup->message, 0, 50)) . (strlen($dup->message) > 50 ? '...' : '') . "</td>";
        echo "<td>" . (int)$dup->count . "</td>";
        echo "</tr>";
    }
    
    echo "</table>
    <p>Conseil : Vous pouvez nettoyer les doublons avec cette requête SQL :
    <pre>DELETE n1 FROM $table n1
    INNER JOIN $table n2 
    WHERE n1.id < n2.id 
    AND n1.message = n2.message 
    AND n1.type = 'reservation';</pre>
    </p>";
}

// Vérifier les notifications non lues
$unread_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM $table WHERE status = %s AND type = %s",
    'unread', 'reservation'
));

echo "<h3>Statut de lecture</h3>";
echo "<p>Nombre de notifications non lues de type 'reservation': " . (int)$unread_count . "</p>";

// Lien pour nettoyer les notifications
$cleanup_url = add_query_arg([
    'cleanup_notifications' => '1',
    '_wpnonce' => wp_create_nonce('cleanup_notifications')
], admin_url('admin.php?page=institut-booking-settings'));

echo "<p><a href='" . esc_url($cleanup_url) . "' class='button button-primary'>Nettoyer les notifications</a> (nécessite d'être connecté en tant qu'administrateur)</p>";

// Nettoyage si demandé
if (isset($_GET['cleanup_notifications']) && current_user_can('manage_options') && wp_verify_nonce($_GET['_wpnonce'], 'cleanup_notifications')) {
    // Supprimer les notifications en double (en gardant la plus récente)
    $wpdb->query("
        DELETE n1 FROM $table n1
        INNER JOIN $table n2 
        WHERE n1.id < n2.id 
        AND n1.message = n2.message 
        AND n1.type = 'reservation'
    ");
    
    // Mettre à jour les liens vers la page de liste des réservations si nécessaire
    $wpdb->update(
        $table,
        ['link' => admin_url('admin.php?page=institut-booking-bookings')],
        [
            'type' => 'reservation',
            'link' => admin_url('admin.php?page=institut-booking-bookings&action=edit&id=%')
        ],
        ['%s'],
        ['%s', '%s']
    );
    
    echo '<div class="notice notice-success"><p>Nettoyage des notifications effectué avec succès !</p></div>';
}
?>

<style>
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif; margin: 20px; }
    h2 { color: #1d2327; margin-top: 30px; }
    h3 { color: #2271b1; margin-top: 25px; }
    table { border-collapse: collapse; margin: 15px 0; }
    th { background: #f0f0f1; text-align: left; }
    td, th { padding: 8px 12px; border: 1px solid #c3c4c7; }
    .button { display: inline-block; padding: 8px 16px; background: #2271b1; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
    .button:hover { background: #135e96; color: white; }
    pre { background: #f6f7f7; padding: 10px; border: 1px solid #dcdcde; border-radius: 4px; overflow-x: auto; }
</style>
