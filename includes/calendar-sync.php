<?php
// Structure d'intégration Google Calendar/Outlook (squelette, à compléter avec les SDK/API)
if (!defined('ABSPATH')) exit;

class IB_CalendarSync {
    public static function add_event($booking) {
        // À compléter : utiliser les tokens stockés pour appeler l'API Google/Outlook
        // Exemple :
        // $client = new Google_Client(); ...
        // $service = new Google_Service_Calendar($client);
        // $event = new Google_Service_Calendar_Event([...]);
        // $service->events->insert('primary', $event);
        error_log('[CALENDAR] Ajouter événement : '.json_encode($booking));
    }
    public static function remove_event($booking) {
        // À compléter : suppression via API
        error_log('[CALENDAR] Supprimer événement : '.json_encode($booking));
    }
}
