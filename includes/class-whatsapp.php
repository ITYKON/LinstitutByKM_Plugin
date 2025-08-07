<?php
// Classe pour notifications WhatsApp (structure de base)
if (!defined('ABSPATH')) exit;

class IB_WhatsApp {
    public static function send($phone, $message) {
        // À compléter avec l'intégration API WhatsApp (ex: Twilio, WATI, etc.)
        // Ici, on log juste pour la démo
        error_log("[WHATSAPP] To: $phone | $message");
    }
}
