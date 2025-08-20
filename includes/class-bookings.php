<?php
// Réservations
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '/class-email.php';

// Fonction centrale pour ajouter une notification interne et envoyer un email
if (!function_exists('ib_add_notification')) {
    function ib_add_notification($type, $message, $target, $link = '', $status = 'unread') {
        global $wpdb;
        
        // Si c'est une notification de réservation confirmée, on ne l'ajoute pas
        if ($type === 'booking_confirmed' || $type === 'booking_completed') {
            // Supprimer les notifications existantes pour cette réservation
            if (preg_match('/Réservation #(\d+)/', $message, $matches)) {
                $booking_id = $matches[1];
                $wpdb->delete($wpdb->prefix . 'ib_notifications', [
                    'type' => 'booking_new',
                    'message' => ['LIKE' => '%Réservation #' . $booking_id . '%']
                ], ['%s', '%s']);
            }
            return;
        }
        
        // Pour les nouvelles réservations, on vérifie si elle n'est pas déjà confirmée
        if (($type === 'booking_new' || $type === 'reservation') && preg_match('/Réservation #(\d+)/', $message, $matches)) {
            $booking_id = $matches[1];
            $booking = $wpdb->get_row($wpdb->prepare("SELECT status FROM {$wpdb->prefix}ib_bookings WHERE id = %d", $booking_id));
            
            // Si la réservation est déjà confirmée, on ne crée pas de notification
            if ($booking && in_array($booking->status, ['confirmed', 'completed'])) {
                return;
            }
            
            // Forcer le type à 'reservation' pour assurer la compatibilité avec le système de notifications AJAX
            $type = 'reservation';
            
            // Lien vers la page principale des réservations (liste complète) sur le site de production
            $link = 'https://linstitutbykm.com/wp-admin/admin.php?page=institut-booking-bookings';
        }
        
        $wpdb->insert($wpdb->prefix . 'ib_notifications', [
            'type' => $type,
            'message' => $message,
            'target' => $target,
            'status' => $status,
            'link' => $link,
            'created_at' => current_time('mysql'),
        ]);
               
    }
}

class IB_Bookings {
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_bookings ORDER BY created_at DESC");
    }

    public static function add($data) {
        global $wpdb;
        $client_phone = isset($data['client_phone']) ? $data['client_phone'] : '';
        // Sécurité : vérifier que l'employé réalise bien le service
        if (!IB_Services::employee_can_do_service($data['employee_id'], $data['service_id'])) {
            error_log('[IB_BOOKING] Tentative de réservation avec employé non autorisé pour ce service');
            return false;
        }

        // VÉRIFICATION CRITIQUE : Contrôle des conflits de créneaux
        $start_time_parts = explode(' ', $data['start_time']);
        $date = isset($start_time_parts[0]) ? $start_time_parts[0] : $data['date'];
        $time = isset($start_time_parts[1]) ? substr($start_time_parts[1], 0, 5) : ''; // Format HH:MM

        if ($time && self::has_conflict($data['employee_id'], $date, $time)) {
            error_log('[IB_BOOKING] Conflit détecté pour employee_id=' . $data['employee_id'] . ', date=' . $date . ', time=' . $time);
            return false;
        }

        $service = IB_Services::get_by_id($data['service_id']);
        $service_price = $service ? $service->price : 0;
        // Si un prix est passé explicitement, on l'utilise, sinon on prend le prix du service
        $final_price = isset($data['price']) ? floatval($data['price']) : $service_price;
        $wpdb->insert("{$wpdb->prefix}ib_bookings", [
            'service_id' => intval($data['service_id']),
            'employee_id' => intval($data['employee_id']),
            'client_id' => isset($data['client_id']) ? intval($data['client_id']) : 0,
            'client_name' => sanitize_text_field($data['client_name']),
            'client_email' => sanitize_email($data['client_email']),
            'client_phone' => sanitize_text_field($client_phone),
            'date' => sanitize_text_field($data['date']),
            'start_time' => sanitize_text_field($data['start_time']),
            'extras' => isset($data['extras']) ? (is_array($data['extras']) ? maybe_serialize($data['extras']) : $data['extras']) : null,
            'status' => isset($data['status']) ? $data['status'] : 'en_attente',
            'created_at' => current_time('mysql'),
            'price' => $final_price,
        ]);
        $employee = IB_Employees::get_by_id($data['employee_id']);
        $admin_id = 1;
        $result = $wpdb->insert_id; // Récupérer l'ID de la réservation insérée
        $message = 'Nouvelle réservation #' . $result . ' : ' . esc_html($service ? $service->name : 'Service') . ' pour ' . esc_html($data['client_name']) . ' le ' . esc_html($data['date']) . ' (' . esc_html($employee ? $employee->name : 'Employé') . ')';
        $link = admin_url('admin.php?page=institut-booking-bookings');
        ib_add_notification('reservation', $message, 'admin', $link, 'unread');
        // Envoi uniquement du mail de remerciement à la création
        // L'email de confirmation sera envoyé quand le statut passera à "confirmée"
        // Notifications avancées
        if (get_option('ib_push_enable')) {
            require_once plugin_dir_path(__FILE__) . '/class-push.php';
            if ($employee && isset($employee->id)) IB_Push::send($employee->id, 'Nouvelle réservation', 'Nouveau RDV avec '.$data['client_name'].' le '.$data['date'].' à '.$data['start_time'].' pour '.$service->name);
        }
        if (get_option('ib_whatsapp_enable')) {
            require_once plugin_dir_path(__FILE__) . '/class-whatsapp.php';
            if ($employee && isset($employee->phone)) IB_WhatsApp::send($employee->phone, 'Nouveau RDV avec '.$data['client_name'].' le '.$data['date'].' à '.$data['start_time'].' pour '.$service->name);
        }
        // Synchronisation calendrier
        require_once plugin_dir_path(__FILE__) . '/calendar-sync.php';
        IB_CalendarSync::add_event($data);
        $result = $wpdb->insert_id;
        // Générer une notification interne si succès
        if ($result) {
            require_once __DIR__ . '/notifications.php';
            $client = isset($data['client_name']) ? $data['client_name'] : 'Client';
            $service = isset($data['service_id']) ? $data['service_id'] : '';
            $date = isset($data['date']) ? $data['date'] : '';
            // Récupérer le nom du service si possible
            $service_name = '';
            if ($service) {
                require_once __DIR__ . '/class-services.php';
                $s = IB_Services::get_by_id($service);
                if ($s && isset($s->name)) $service_name = $s->name;
            }
            $msg = "$client a réservé $service_name le $date.";
            $link = admin_url('admin.php?page=institut-booking-bookings');
            // Utiliser 'reservation' comme type pour la compatibilité avec le système de notifications AJAX
            ib_add_notification('reservation', $msg, 'admin', $link, 'unread');
        }
        // Envoi de l'email de remerciement au client
        if ($result) {
            require_once plugin_dir_path(__FILE__) . '/notifications.php';
            IB_Notifications::send_thank_you($result);
        }
        
        // Gestion du client et du bookings_count
        require_once plugin_dir_path(__FILE__) . '/class-clients.php';
        $client = IB_Clients::get_by_email($data['client_email']);
        if (!$client && !empty($client_phone)) {
            $client = IB_Clients::get_by_phone($client_phone);
        }
        // On considère une réservation "active" pour bookings_count si confirmee OU complete
        $is_active = (isset($data['status']) && in_array($data['status'], ['confirmee','complete']));
        if ($client) {
            if ($is_active) {
                global $wpdb;
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$wpdb->prefix}ib_clients SET bookings_count = IFNULL(bookings_count,0)+1 WHERE id = %d",
                    $client->id
                ));
            }
        } else {
            $count = $is_active ? 1 : 0;
            global $wpdb;
            $wpdb->insert("{$wpdb->prefix}ib_clients", [
                'name' => sanitize_text_field($data['client_name']),
                'email' => sanitize_email($data['client_email']),
                'phone' => sanitize_text_field($client_phone),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
                'bookings_count' => $count
            ]);
        }
        return $result;
    }

    public static function update($id, $data) {
        global $wpdb;
        // On ne met à jour que les champs fournis dans $data
        $fields = [];
        $allowed = ['service_id','employee_id','client_name','client_email','client_phone','date','start_time','extras','status','price'];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                if ($key === 'service_id' || $key === 'employee_id') {
                    $fields[$key] = intval($data[$key]);
                } elseif ($key === 'extras') {
                    $fields[$key] = is_array($data[$key]) ? maybe_serialize($data[$key]) : $data[$key];
                } elseif ($key === 'client_email') {
                    $fields[$key] = sanitize_email($data[$key]);
                } elseif ($key === 'price') {
                    $fields[$key] = floatval($data[$key]);
                } else {
                    $fields[$key] = sanitize_text_field($data[$key]);
                }
            }
        }
        // Si le service_id est modifié et qu'aucun prix n'est explicitement fourni, mettre à jour le prix auto
        if (isset($fields['service_id']) && !isset($data['price'])) {
            $service = IB_Services::get_by_id($fields['service_id']);
            $fields['price'] = $service ? $service->price : 0;
        }
        // Récupérer l'ancien statut pour détecter le changement
        $booking = self::get_by_id($id);
        $old_status = $booking ? $booking->status : '';
        $new_status = isset($fields['status']) ? $fields['status'] : $old_status;

        // VÉRIFICATION CRITIQUE : Contrôle des conflits si date/heure/employé modifiés
        if (isset($fields['date']) || isset($fields['start_time']) || isset($fields['employee_id'])) {
            $check_employee = isset($fields['employee_id']) ? $fields['employee_id'] : $booking->employee_id;
            $check_date = isset($fields['date']) ? $fields['date'] : $booking->date;
            $check_time = isset($fields['start_time']) ? $fields['start_time'] : $booking->start_time;

            // Extraire l'heure du start_time si c'est un datetime
            if (strpos($check_time, ' ') !== false) {
                $time_parts = explode(' ', $check_time);
                $check_time = isset($time_parts[1]) ? substr($time_parts[1], 0, 5) : '';
            }

            // Vérifier les conflits en excluant la réservation courante
            $conflict_rows = $wpdb->get_results($wpdb->prepare(
                "SELECT start_time, service_id FROM {$wpdb->prefix}ib_bookings WHERE employee_id = %d AND date = %s AND id != %d",
                $check_employee, $check_date, $id
            ));

            $check_service_id = isset($fields['service_id']) ? $fields['service_id'] : $booking->service_id;
            $service = IB_Services::get_by_id($check_service_id);
            $duration = $service && isset($service->duration) ? intval($service->duration) : 30;

            $start = strtotime($check_date . ' ' . $check_time);
            $end = $start + $duration * 60;

            foreach ($conflict_rows as $row) {
                $other_start = strtotime($row->start_time);
                $other_service = IB_Services::get_by_id($row->service_id);
                $other_duration = $other_service && isset($other_service->duration) ? intval($other_service->duration) : 30;
                $other_end = $other_start + $other_duration * 60;

                if ($start < $other_end && $end > $other_start) {
                    error_log('[IB_BOOKING] Conflit détecté lors de la mise à jour - booking_id=' . $id);
                    return false; // Retourner false en cas de conflit
                }
            }
        }

        if (!empty($fields)) {
            $wpdb->update("{$wpdb->prefix}ib_bookings", $fields, ['id' => intval($id)]);
        }
        // Générer une notification si le statut a changé
        if ($booking && isset($fields['status']) && $fields['status'] !== $old_status) {
            $service = IB_Services::get_by_id($booking->service_id);
            $employee = IB_Employees::get_by_id($booking->employee_id);
            $link = admin_url('admin.php?page=institut-booking-bookings');
            if ($fields['status'] === 'confirmee') {
                $message = 'Réservation confirmée : ' . esc_html($service ? $service->name : 'Service') . ' pour ' . esc_html($booking->client_name) . ' le ' . esc_html($booking->date) . ' (' . esc_html($employee ? $employee->name : 'Employé') . ')';
                
                // Supprimer les notifications existantes pour cette réservation
                global $wpdb;
                $wpdb->delete(
                    $wpdb->prefix . 'ib_notifications',
                    [
                        'type' => 'reservation',
                        'message' => ['LIKE' => '%Réservation #' . $booking->id . '%']
                    ],
                    ['%s', '%s']
                );
                
                // Ne pas ajouter de nouvelle notification pour les confirmations
                // ib_add_notification('booking_confirmed', $message, 'admin', $link, 'unread');
                
                // Envoi d'un email de confirmation au client
                IB_Email::send_auto('confirm', [
                    'service' => $service ? $service->name : '',
                    'date' => $booking->date,
                    'time' => $booking->start_time,
                    'client' => $booking->client_name,
                    'client_email' => $booking->client_email,
                    'employee' => $employee ? $employee->name : '',
                ]);
            } elseif ($fields['status'] === 'annulee') {
                $message = 'Réservation annulée : ' . esc_html($service ? $service->name : 'Service') . ' pour ' . esc_html($booking->client_name) . ' le ' . esc_html($booking->date) . ' (' . esc_html($employee ? $employee->name : 'Employé') . ')';
                ib_add_notification('booking_cancelled', $message, 'admin', $link, 'unread');
            } elseif ($fields['status'] === 'en_attente') {
                $message = 'Réservation remise en attente : ' . esc_html($service ? $service->name : 'Service') . ' pour ' . esc_html($booking->client_name) . ' le ' . esc_html($booking->date) . ' (' . esc_html($employee ? $employee->name : 'Employé') . ')';
                ib_add_notification('booking_pending', $message, 'admin', $link, 'unread');
            } elseif ($fields['status'] === 'complete') {
                $message = 'Réservation complétée : ' . esc_html($service ? $service->name : 'Service') . ' pour ' . esc_html($booking->client_name) . ' le ' . esc_html($booking->date) . ' (' . esc_html($employee ? $employee->name : 'Employé') . ')';
                ib_add_notification('booking_completed', $message, 'admin', $link, 'unread');
            } elseif ($fields['status'] === 'no_show') {
                $message = 'No show : ' . esc_html($service ? $service->name : 'Service') . ' pour ' . esc_html($booking->client_name) . ' le ' . esc_html($booking->date) . ' (' . esc_html($employee ? $employee->name : 'Employé') . ')';
                ib_add_notification('booking_no_show', $message, 'admin', $link, 'unread');
            }
        }
    }

    public static function delete($id) {
        global $wpdb;
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_bookings WHERE id = %d", $id));
        $wpdb->delete("{$wpdb->prefix}ib_bookings", ['id' => intval($id)]);
        if ($booking) {
            $service = IB_Services::get_by_id($booking->service_id);
            $employee = IB_Employees::get_by_id($booking->employee_id);
            IB_Email::send_auto('cancel', [
                'service' => $service ? $service->name : '',
                'date' => $booking->date,
                'time' => $booking->start_time,
                'client' => $booking->client_name,
                'client_email' => $booking->client_email,
                'employee' => $employee ? $employee->name : '',
            ]);
        }
    }

    public static function has_conflict($employee_id, $date, $start_time, $duration = null) {
        global $wpdb;
        // Récupérer la durée du service si non fournie
        if ($duration === null) {
            $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
            $service = $service_id ? IB_Services::get_by_id($service_id) : null;
            $duration = $service && isset($service->duration) ? intval($service->duration) : 30;
        }
        $start = strtotime($date . ' ' . $start_time);
        $end = $start + $duration * 60;
        // Chercher tout rendez-vous qui chevauche cette plage pour cet employé
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT start_time, service_id FROM {$wpdb->prefix}ib_bookings WHERE employee_id = %d AND date = %s",
            $employee_id, $date
        ));
        foreach ($rows as $row) {
            $other_start = strtotime($row->start_time);
            $other_service = IB_Services::get_by_id($row->service_id);
            $other_duration = $other_service && isset($other_service->duration) ? intval($other_service->duration) : 30;
            $other_end = $other_start + $other_duration * 60;
            // Chevauchement strict
            if ($start < $other_end && $end > $other_start) {
                return true;
            }
        }
        return false;
    }
    public static function count_today() {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ib_bookings 
            WHERE DATE(start_time) = %s",
            current_time('Y-m-d')
        ));
    }
    public static function get_today_revenue() {
        global $wpdb;
        return (float) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(price), 0) FROM {$wpdb->prefix}ib_bookings 
            WHERE DATE(start_time) = %s AND status != 'cancelled'",
            current_time('Y-m-d')
        ));
    }
    public static function get_recent($limit = 10) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ib_bookings 
            ORDER BY start_time DESC 
            LIMIT %d",
            $limit
        ));
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_bookings WHERE id = %d", $id));
    }
    // Ajoute update, delete, check_conflit etc.
}

function ib_booking_has_conflict($employee_id, $date, $time) {
    // On passe la durée du service si possible
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $service = $service_id ? IB_Services::get_by_id($service_id) : null;
    $duration = $service && isset($service->duration) ? intval($service->duration) : 30;
    return IB_Bookings::has_conflict($employee_id, $date, $time, $duration);
}

add_action('wp_ajax_ib_update_booking_event', function() {
    if (!current_user_can('manage_options') && !current_user_can('ib_manage_bookings')) {
        wp_send_json(['success' => false, 'message' => __('Accès refusé', 'institut-booking')], 403);
    }
    $id = intval($_POST['id']);
    $date = sanitize_text_field($_POST['date']);
    $time = sanitize_text_field($_POST['time']);
    $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : null;
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : null;
    $extras = isset($_POST['extras']) ? $_POST['extras'] : null;

    $booking = IB_Bookings::get_by_id($id);
    if (!$booking) {
        wp_send_json(['success' => false, 'message' => __('Réservation introuvable', 'institut-booking')], 404);
    }
    // Vérifier conflit
    $check_employee = $employee_id ? $employee_id : $booking->employee_id;
    $check_service = $service_id ? $service_id : $booking->service_id;
    $duration = null;
    if ($check_service) {
        $service = IB_Services::get_by_id($check_service);
        $duration = $service && isset($service->duration) ? intval($service->duration) : 30;
    }
    // Exclure la réservation courante du conflit
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, start_time, service_id FROM {$wpdb->prefix}ib_bookings WHERE employee_id = %d AND date = %s AND id != %d",
        $check_employee, $date, $id
    ));
    $start = strtotime($date . ' ' . $time);
    $end = $start + $duration * 60;
    foreach ($rows as $row) {
        $other_start = strtotime($row->start_time);
        $other_service = IB_Services::get_by_id($row->service_id);
        $other_duration = $other_service && isset($other_service->duration) ? intval($other_service->duration) : 30;
        $other_end = $other_start + $other_duration * 60;
        if ($start < $other_end && $end > $other_start) {
            wp_send_json(['success' => false, 'message' => __('Conflit avec une autre réservation.', 'institut-booking')], 409);
        }
    }
    // Mettre à jour la réservation
    $update_data = [
        'date' => $date,
        'start_time' => $time
    ];
    if ($employee_id) $update_data['employee_id'] = $employee_id;
    if ($service_id) $update_data['service_id'] = $service_id;
    if ($extras !== null) $update_data['extras'] = is_array($extras) ? maybe_serialize($extras) : $extras;
    $wpdb->update("{$wpdb->prefix}ib_bookings", $update_data, ['id' => $id]);
    // Notification email (optionnel)
    $booking = IB_Bookings::get_by_id($id);
    if ($booking) {
        $service = IB_Services::get_by_id($booking->service_id);
        $employee = IB_Employees::get_by_id($booking->employee_id);
        $subject = __('Votre réservation a été modifiée', 'institut-booking');
        $msg_client = sprintf(__('Bonjour %s,\nVotre réservation pour le service : %s a été déplacée au %s à %s.', 'institut-booking'), $booking->client_name, $service->name, $booking->date, $booking->start_time);
        IB_Email::send_update($booking->client_email, $subject, $msg_client);
        if ($employee) {
            $msg_emp = sprintf(__('La réservation de %s pour %s a été déplacée au %s à %s.', 'institut-booking'), $booking->client_name, $service->name, $booking->date, $booking->start_time);
            IB_Email::send_update($employee->email, $subject, $msg_emp);
        }
    }
    wp_send_json(['success' => true]);
});

add_action('wp_ajax_ib_delete_booking_event', function() {
    if (!current_user_can('manage_options') && !current_user_can('ib_manage_bookings')) {
        wp_send_json(['success' => false, 'message' => __('Accès refusé', 'institut-booking')], 403);
    }
    $id = intval($_POST['id']);
    $booking = IB_Bookings::get_by_id($id);
    if (!$booking) {
        wp_send_json(['success' => false, 'message' => __('Réservation introuvable', 'institut-booking')], 404);
    }
    IB_Bookings::delete($id);
    wp_send_json(['success' => true]);
});

// Rappel SMS automatique (à déclencher via cron ou manuellement)
add_action('ib_daily_sms_reminder', function() {
    global $wpdb;
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_bookings WHERE date = %s", $tomorrow));
    foreach ($bookings as $b) {
        if (!empty($b->client_email)) {
            $service = IB_Services::get_by_id($b->service_id);
            $msg = 'Rappel : votre rendez-vous pour ' . $service->name . ' est prévu le ' . $b->date . ' à ' . $b->start_time . '.';
            if (function_exists('ib_send_sms')) {
                // Ici, il faudrait stocker le numéro de téléphone du client dans la table booking pour un vrai envoi SMS
                // ib_send_sms($b->client_phone, $msg);
            }
        }
    }
});

// Script de migration pour remplir start_time à partir de date + time si start_time est vide
function ib_migrate_start_time_from_time() {
    global $wpdb;
    $table = $wpdb->prefix . 'ib_bookings';
    $rows = $wpdb->get_results("SELECT id, date, time, start_time FROM $table WHERE (start_time IS NULL OR start_time = '0000-00-00 00:00:00') AND time IS NOT NULL AND time != ''");
    foreach ($rows as $row) {
        $start_time = $row->date . ' ' . $row->time . ':00';
        $wpdb->update($table, ['start_time' => $start_time], ['id' => $row->id]);
    }
}
add_action('admin_init', 'ib_migrate_start_time_from_time');

// Fin du fichier, ne rien ajouter après cette ligne pour éviter toute sortie parasite.
