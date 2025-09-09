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
            // Afficher le message de confirmation avec un style minimaliste Planity
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Réservation annulée</title>';
            echo '<style>
                body { background: #fafafa; font-family: "Segoe UI", Arial, sans-serif; margin:0; }
                .notif-annul { max-width: 420px; margin: 60px auto; background: #fff; border-radius: 14px; box-shadow: 0 2px 16px #0001; padding: 36px 28px; text-align: center; }
                .notif-icon { display:inline-flex; align-items:center; justify-content:center; width:60px; height:60px; border-radius:50%; background:#fff; border:2px solid #dc3545; color:#dc3545; font-size:2.2em; margin-bottom:18px; }
                .notif-title { color: #222; font-size: 1.7em; font-weight: 600; margin-bottom: 12px; letter-spacing: -1px; }
                .notif-text { color: #222; font-size: 1.08em; margin-bottom: 14px; line-height:1.5; }
                .notif-btn { display:inline-block; margin-top:22px; padding:11px 28px; background:#dc3545; color:#fff; border-radius:7px; font-weight:500; text-decoration:none; font-size:1em; border:none; cursor:pointer; transition:background 0.2s; }
                .notif-btn:hover { background:#b71c1c; }
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
