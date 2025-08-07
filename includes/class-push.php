<?php
// Classe pour notifications push web/app (structure de base)
if (!defined('ABSPATH')) exit;

class IB_Push {
    public static function send($user_id, $title, $message, $url = '') {
        // À compléter avec l'intégration push (OneSignal, Firebase, etc.)
        // Exemple : stocker la notif en base, ou appeler une API push
        // Ici, on log juste pour la démo
        error_log("[PUSH] To: $user_id | $title | $message | $url");
    }
}
