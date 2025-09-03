<?php
// Handler direct d'annulation de réservation pour LinstitutByKM_Plugin
if (!defined('ABSPATH')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
}

global $wpdb;

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

if ($booking_id && $token) {
    $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_bookings WHERE id = %d", $booking_id));
    if ($booking) {
        $expected_token = md5($booking->id . $booking->client_email . AUTH_KEY);
        if (hash_equals($expected_token, $token)) {
            // Annuler la réservation
            $wpdb->update("{$wpdb->prefix}ib_bookings", ['status' => 'annulee'], ['id' => $booking_id]);
            // Afficher le message de confirmation avec un design moderne
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Réservation annulée</title>';
            echo '<style>
                body { background: #f7f7f7; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
                .notif-annul { max-width: 480px; margin: 60px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 24px #0002; padding: 40px 32px; text-align: center; }
                .notif-icon { display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border-radius:50%; background:#dc3545; color:#fff; font-size:2.5em; margin-bottom:20px; }
                .notif-title { color: #A8977B; font-size: 2em; font-weight: 700; margin-bottom: 10px; }
                .notif-text { color: #222; font-size: 1.15em; margin-bottom: 10px; }
                .notif-btn { display:inline-block; margin-top:20px; padding:12px 32px; background:#A8977B; color:#fff; border-radius:8px; font-weight:600; text-decoration:none; font-size:1em; border:none; cursor:pointer; }
            </style></head><body>';
            echo '<div class="notif-annul">';
            echo '<div class="notif-icon">&#10006;</div>';
            echo '<div class="notif-title">Votre réservation est annulée</div>';
            echo '<div class="notif-text">Nous avons bien pris en compte votre demande.<br>Si vous souhaitez reprendre rendez-vous, n’hésitez pas à nous contacter.</div>';
            echo '<a href="' . site_url() . '" class="notif-btn">Retour à l’accueil</a>';
            echo '</div>';
            echo '</body></html>';
            exit;
        } else {
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erreur</title></head><body style="font-family:sans-serif;text-align:center;padding:40px;">';
            echo '<h2 style="color:#A8977B;">Lien invalide</h2>';
            echo '<p>Le lien d’annulation n’est pas valide ou a expiré.</p>';
            echo '</body></html>';
            exit;
        }
    }
}
