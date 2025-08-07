<?php
// Cron pour rappels automatiques (structure de base)
if (!defined('ABSPATH')) exit;

add_action('ib_cron_reminders', 'ib_send_reminders');
function ib_send_reminders() {
    if (!get_option('ib_reminder_enable')) return;
    global $wpdb;
    $now = current_time('Y-m-d');
    $reminder_time = get_option('ib_reminder_time', '09:00');
    $bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_bookings WHERE date = %s", $now));
    foreach ($bookings as $booking) {
        // Email
        IB_Email::send_confirmation($booking->client_email, 'Rappel de RDV', 'Rappel : RDV aujourd\'hui à '.date('H:i', strtotime($booking->start_time)).' pour '.$booking->client_name);
        // SMS (si module SMS actif)
        // Push
        if (get_option('ib_push_enable')) {
            require_once plugin_dir_path(__FILE__) . '/class-push.php';
            IB_Push::send($booking->employee_id, 'Rappel RDV', 'RDV aujourd\'hui à '.date('H:i', strtotime($booking->start_time)).' avec '.$booking->client_name);
        }
        // WhatsApp
        if (get_option('ib_whatsapp_enable')) {
            require_once plugin_dir_path(__FILE__) . '/class-whatsapp.php';
            IB_WhatsApp::send($booking->client_phone, 'Rappel : RDV aujourd\'hui à '.date('H:i', strtotime($booking->start_time)));
        }
    }
}
// Planification du cron à l'heure définie
if (!wp_next_scheduled('ib_cron_reminders')) {
    wp_schedule_event(strtotime(date('Y-m-d').' '.get_option('ib_reminder_time', '09:00')), 'daily', 'ib_cron_reminders');
}
