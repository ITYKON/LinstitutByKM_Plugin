<?php
// Rappels automatiques 24h avant le RDV
if (!defined('ABSPATH')) exit;

// Activer les rappels par défaut
update_option('ib_reminder_enable', '1');
update_option('ib_reminder_time', '09:00');

/**
 * Vérifier et ajouter la colonne reminder_sent si nécessaire
 */
function ib_check_reminder_column() {
    global $wpdb;
    $wpdb->query("ALTER TABLE {$wpdb->prefix}ib_bookings ADD COLUMN IF NOT EXISTS reminder_sent TINYINT(1) DEFAULT 0");
}
add_action('plugins_loaded', 'ib_check_reminder_column');

/**
 * Envoi des rappels automatiques
 */
function ib_send_reminders() {
    if (!get_option('ib_reminder_enable')) return;

    global $wpdb;
    $now = current_time('mysql');
    $tomorrow = date('Y-m-d', strtotime('+1 day', strtotime($now)));

    // Récupère les rendez-vous qui ont lieu demain et sans rappel envoyé
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ib_bookings 
         WHERE date = %s 
         AND (reminder_sent IS NULL OR reminder_sent = 0)",
        $tomorrow
    ));

    if (empty($bookings)) return;

    foreach ($bookings as $booking) {
        // Date/heure formatées
        $booking_time = strtotime("$booking->date $booking->start_time");
        $formatted_date = date_i18n('l d F Y', $booking_time);
        $formatted_time = date_i18n('H:i', $booking_time);

        // Service
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT name FROM {$wpdb->prefix}ib_services WHERE id = %d",
            $booking->service_id
        ));
        $service_name = $service ? $service->name : 'Service';

        // Email
        $subject = "Rappel : Votre rendez-vous demain à $formatted_time";
        $message  = "Bonjour $booking->client_name,\n\n";
        $message .= "Nous vous rappelons votre rendez-vous pour $service_name :\n";
        $message .= " Date : $formatted_date\n";
        $message .= " Heure : $formatted_time\n\n";
        $message .= "Lieu : " . get_bloginfo('name') . "\n";
        $message .= "\nPour toute modification, merci de nous contacter au plus tôt.\n\n";
        $message .= "Cordialement,\n" . get_bloginfo('name');

        IB_Email::send_confirmation($booking->client_email, $subject, $message);

        // Marquer comme envoyé
        $wpdb->update(
            "{$wpdb->prefix}ib_bookings",
            array('reminder_sent' => 1),
            array('id' => $booking->id),
            array('%d'),
            array('%d')
        );

        // Notification push
        if (get_option('ib_push_enable')) {
            if (file_exists(plugin_dir_path(__FILE__) . '/class-push.php')) {
                require_once plugin_dir_path(__FILE__) . '/class-push.php';
                if (class_exists('IB_Push')) {
                    IB_Push::send(
                        $booking->employee_id,
                        'Rappel RDV demain',
                        "RDV demain à $formatted_time avec $booking->client_name"
                    );
                }
            }
        }

        // WhatsApp
        if (get_option('ib_whatsapp_enable') && !empty($booking->client_phone)) {
            if (file_exists(plugin_dir_path(__FILE__) . '/class-whatsapp.php')) {
                require_once plugin_dir_path(__FILE__) . '/class-whatsapp.php';
                if (class_exists('IB_WhatsApp')) {
                    $whatsapp_msg = "Rappel : Vous avez un RDV demain à $formatted_time pour $service_name. " . 
                                    "Pour toute modification, merci de nous contacter.";
                    IB_WhatsApp::send($booking->client_phone, $whatsapp_msg);
                }
            }
        }
    }
}
add_action('ib_cron_reminders', 'ib_send_reminders');

/**
 * Planification du cron à 9h du matin
 */
if (!wp_next_scheduled('ib_cron_reminders')) {
    wp_schedule_event(
        strtotime('09:00:00'), 
        'daily', 
        'ib_cron_reminders'
    );
}
