<?php
/**
 * Script de test pour l'envoi des rappels
 * 
 * Utilisation : 
 * 1. Remplacez XXXX par l'ID d'une réservation confirmée existante
 * 2. Accédez à : https://votresite.com/wp-content/plugins/linstitutbykm_plugin/test-reminder.php
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Vérifier les capacités utilisateur
if (!current_user_can('manage_options')) {
    wp_die('Accès non autorisé. Vous devez être administrateur pour accéder à cette page.');
}

// Fonction d'aide pour afficher les résultats de test
function test_reminder_output($message, $is_error = false) {
    $style = $is_error ? 'color: red;' : 'color: green;';
    echo "<p style='margin: 5px 0; padding: 5px; border-left: 3px solid " . ($is_error ? 'red' : 'green') . ";'>";
    echo "<strong>" . ($is_error ? 'ERREUR : ' : 'SUCCÈS : ') . "</strong>" . $message;
    echo "</p>";
}

// Vérifier si on a un ID de réservation
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id > 0) {
    // Inclure WordPress
    require_once('../../../wp-load.php');
    
    // Vérifier que la classe existe
    if (!class_exists('IB_Notifications')) {
        require_once(plugin_dir_path(__FILE__) . 'includes/notifications.php');
    }
    
    echo "<h2>Test d'envoi de rappel pour la réservation #" . $booking_id . "</h2>";
    
    // Vérifier que la réservation existe
    global $wpdb;
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ib_bookings WHERE id = %d",
        $booking_id
    ));
    
    if (!$booking) {
        test_reminder_output("Aucune réservation trouvée avec l'ID " . $booking_id, true);
        exit;
    }
    
    test_reminder_output("Réservation #" . $booking->id . " trouvée (Client: " . $booking->client_name . ")");
    
    // Vérifier le statut
    if ($booking->status !== 'confirmee') {
        test_reminder_output("La réservation n'est pas confirmée (statut: " . $booking->status . ")", true);
    } else {
        test_reminder_output("La réservation est bien confirmée");
    }
    
    // Tester l'envoi du rappel
    test_reminder_output("Tentative d'envoi du rappel...");
    $result = IB_Notifications::send_reminder($booking_id);
    
    if ($result) {
        test_reminder_output("Le rappel a été envoyé avec succès à " . $booking->client_email);
    } else {
        test_reminder_output("Échec de l'envoi du rappel. Vérifiez les logs pour plus de détails.", true);
    }
    
} else {
    // Afficher un formulaire pour entrer l'ID de réservation
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Test d'envoi de rappel</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            .container { max-width: 800px; margin: 0 auto; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="number"] { padding: 8px; width: 100px; }
            button { padding: 8px 15px; background: #0073aa; color: white; border: none; cursor: pointer; }
            button:hover { background: #005177; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Test d'envoi de rappel</h1>
            <p>Ce script permet de tester manuellement l'envoi d'un rappel pour une réservation.</p>
            
            <form method="get" action="">
                <div class="form-group">
                    <label for="booking_id">ID de la réservation :</label>
                    <input type="number" id="booking_id" name="booking_id" required min="1">
                </div>
                <button type="submit">Tester l'envoi du rappel</button>
            </form>
            
            <div style="margin-top: 30px; padding: 15px; background: #f5f5f5; border-radius: 5px;">
                <h3>Comment utiliser :</h3>
                <ol>
                    <li>Entrez l'ID d'une réservation confirmée existante</li>
                    <li>Cliquez sur "Tester l'envoi du rappel"</li>
                    <li>Vérifiez la boîte mail du client et les logs pour confirmer l'envoi</li>
                </ol>
                
                <h3>Réservations récentes :</h3>
                <?php
                // Afficher les 5 dernières réservations pour faciliter les tests
                global $wpdb;
                $recent_bookings = $wpdb->get_results(
                    "SELECT id, client_name, client_email, date, status 
                    FROM {$wpdb->prefix}ib_bookings 
                    ORDER BY id DESC 
                    LIMIT 5"
                );
                
                if ($recent_bookings) {
                    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
                    echo "<tr><th>ID</th><th>Client</th><th>Email</th><th>Date</th><th>Statut</th></tr>";
                    foreach ($recent_bookings as $b) {
                        echo "<tr>";
                        echo "<td>" . $b->id . "</td>";
                        echo "<td>" . esc_html($b->client_name) . "</td>";
                        echo "<td>" . esc_html($b->client_email) . "</td>";
                        echo "<td>" . $b->date . "</td>";
                        echo "<td>" . $b->status . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>Aucune réservation trouvée.</p>";
                }
                ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}
