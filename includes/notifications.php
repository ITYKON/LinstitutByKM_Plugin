<?php
if (!defined('ABSPATH')) exit;

/**
 * Gestion des notifications
 */
class IB_Notifications {
    /**
     * Remplace les variables dans un template d'email
     */
    private static function replace_vars($template, $vars) {
        // Supporte {client_name}, {company}, {service}, {service_name}, {date}, {time}, {employee_name}, etc.
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        // Compatibilité : remplace aussi {client} par {client_name} et {service} par {service_name}
        if (isset($vars['client_name'])) {
            $template = str_replace('{client}', $vars['client_name'], $template);
        }
        if (isset($vars['service_name'])) {
            $template = str_replace('{service}', $vars['service_name'], $template);
        }
        return $template;
    }
    /**
     * Envoie un email de remerciement après réservation
     */
    public static function send_thank_you($booking_id) {
        global $wpdb;
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_bookings WHERE id = %d", $booking_id));
        if (!$booking) return false;
        require_once plugin_dir_path(__FILE__) . '/class-services.php';
        $service = IB_Services::get_by_id($booking->service_id);
        $company = get_bloginfo('name');
        $client_name = isset($booking->client_name) && trim($booking->client_name) ? $booking->client_name : 'Client';
        $service_name = $service && isset($service->name) ? $service->name : 'Service';
        $client_email = isset($booking->client_email) && is_email($booking->client_email) ? $booking->client_email : '';
        $admin_email = get_option('admin_email');
        if (!empty($client_email)) {
            $subject = 'Merci pour votre réservation !';

            // Utiliser le template moderne style Planity
            require_once plugin_dir_path(__FILE__) . '/class-email.php';
            $placeholders = [
                '{client}' => $client_name,
                '{client_name}' => $client_name,
                '{service}' => $service_name,
                '{service_name}' => $service_name,
                '{company}' => $company,
                '{date}' => date('d-m-Y', strtotime($booking->date)),
                '{time}' => date('H:i', strtotime($booking->start_time)),
                '{employee}' => ''
            ];

            // Récupérer l'employé
            require_once plugin_dir_path(__FILE__) . '/class-employees.php';
            $employee = IB_Employees::get_by_id($booking->employee_id);
            if ($employee) {
                $placeholders['{employee}'] = $employee->name;
            }

            $message = self::get_thank_you_template($placeholders);

            // Log PHP
            error_log('[IB Booking] Tentative envoi mail de remerciement au client: ' . $client_email . ', booking_id: ' . intval($booking_id));
            $sent = self::send_email($client_email, $subject, $message);
            // Notification admin (succès ou échec)
            if ($sent) {
                self::add('email', 'Mail de remerciement envoyé au client (' . esc_html($client_email) . ') pour la réservation ' . intval($booking_id), 'admin');
                error_log('[IB Booking] Mail de remerciement envoyé au client: ' . $client_email);
            } else {
                self::add('email', 'Échec envoi mail de remerciement au client (' . esc_html($client_email) . ') pour la réservation ' . intval($booking_id), 'admin');
                error_log('[IB Booking] Échec envoi mail de remerciement au client: ' . $client_email);
                // Prévenir l'admin si l'envoi échoue
                $admin_subject = '[IB Booking] Erreur envoi mail de remerciement';
                $admin_message = 'Le mail de remerciement n\'a pas pu être envoyé au client (ID réservation : ' . intval($booking_id) . ', email : ' . esc_html($client_email) . ').';
                self::send_email($admin_email, $admin_subject, $admin_message);
            }
        } else {
            self::add('email', 'Impossible d\'envoyer le mail de remerciement : email client absent pour la réservation ' . intval($booking_id), 'admin');
            error_log('[IB Booking] Email client absent pour le mail de remerciement, booking_id: ' . intval($booking_id));
            // Email client absent, prévenir l'admin
            $admin_subject = '[IB Booking] Erreur : pas d\'email client pour le remerciement';
            $admin_message = 'Impossible d\'envoyer le mail de remerciement au client (ID réservation : ' . intval($booking_id) . ').';
            self::send_email($admin_email, $admin_subject, $admin_message);
        }
    }
    /**
     * Template d'email de remerciement moderne style Planity
     */
    public static function get_thank_you_template($placeholders) {
        $company = $placeholders['{company}'];
        $client = $placeholders['{client}'];
        $service = $placeholders['{service}'];
        $date = $placeholders['{date}'];
        $time = $placeholders['{time}'];
        $employee = $placeholders['{employee}'];

        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name='color-scheme' content='light dark'>
    <meta name='supported-color-schemes' content='light dark'>
    <title>Merci pour votre réservation</title>
    <style>
        :root {
            color-scheme: light dark;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f5f5f5 !important;
            margin: 0;
            padding: 20px;
            -webkit-text-size-adjust: 100%;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff !important;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid #e1e5e9;
        }

        .header { 
            background: linear-gradient(135deg, #A8977B 0%, #8a7a5d 100%) !important; 
            padding: 2rem; 
            text-align: center; 
        }
        .header-icon { 
            background: #ffffff !important; 
            width: 60px; 
            height: 60px; 
            border-radius: 50%; 
            margin: 0 auto 1rem; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header-icon svg {
            width: 32px;
            height: 32px;
            fill: #A8977B;
        }
        .header h1 { 
            color: #ffffff !important; 
            margin: 0; 
            font-size: 24px; 
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            color: #ffffff !important;
            margin: 0.5rem 0 0;
            font-size: 16px;
            opacity: 0.95;
        }
        .content {
            padding: 2rem;
            background-color: #ffffff !important;
        }
        .content p {
            color: #2d3748 !important;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 1rem;
        }
        .booking-card {
            background: #f7fafc !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        .service-info h3 {
            color: #2d3748 !important;
            margin: 0 0 0.5rem;
            font-size: 18px;
            font-weight: 600;
        }
        .service-info p {
            color: #4a5568 !important;
            margin: 0;
            font-size: 14px;
        }
        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .detail-item span {
            color: #2d3748 !important;
            font-size: 14px;
            font-weight: 500;
            margin-left: 0.5rem;
        }
        .next-step { 
            background: #f5f1e9 !important; 
            border: 2px solid #e0d9cc !important; 
            border-radius: 8px; 
            padding: 1rem; 
            margin: 1.5rem 0;
        }
        .next-step p { 
            color: #856404 !important; 
            font-size: 14px; 
            margin: 0; 
            font-weight: 500; 
        }
        .footer {
            background: #f7fafc !important;
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid #e2e8f0 !important;
        }
        .footer p {
            color: #4a5568 !important;
            font-size: 14px;
            margin: 0;
        }
        strong {
            color: #1a202c !important;
        }

        /* Support amélioré pour le mode sombre */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #0f0f0f !important;
                color: #f7fafc !important;
            }
            .container {
                background-color: #1a202c !important;
                border: 1px solid #4a5568 !important;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important;
            }
            .content {
                background-color: #1a202c !important;
            }
            .header {
                background: linear-gradient(135deg, #A8977B 0%, #8a7a5d 100%) !important;
            }
            .header-icon {
                background: #4a5568 !important;
            }
            .header h1,
            .header p {
                color: #e0e0e0 !important;
            }
            .content p {
                color: #e2e8f0 !important;
            }
            .booking-card {
                background: #2d3748 !important;
                border: 2px solid #4a5568 !important;
            }
            .service-info h3 {
                color: #f7fafc !important;
            }
            .service-info p {
                color: #cbd5e0 !important;
            }
            .detail-item span {
                color: #e2e8f0 !important;
            }
            .next-step {
                background: #333 !important;
                border: 2px solid #555 !important;
            }
            .next-step p {
                color: #e0e0e0 !important;
            }
            .footer {
                background: #2d3748 !important;
                border-color: #4a5568 !important;
            }
            .footer p {
                color: #cbd5e0 !important;
            }
            strong {
                color: #ffffff !important;
            }
        }

        /* Fallback pour clients email qui ne supportent pas les media queries */
        [data-ogsc] body {
            background-color: #1a202c !important;
            color: #f7fafc !important;
        }
        [data-ogsc] .container {
            background-color: #1a202c !important;
            border: 1px solid #4a5568 !important;
        }
        [data-ogsc] .content {
            background-color: #1a202c !important;
        }
        [data-ogsc] .content p,
        [data-ogsc] .service-info h3,
        [data-ogsc] .detail-item span,
        [data-ogsc] .confirmation-text {
            color: #000000 !important;
        }
        .confirmation-text {
            font-size: 18px !important;
            margin-bottom: 1.5rem !important;
            color: #000000 !important;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <div class='header-icon' style='width: 60px; height: 60px; background: #A8977B; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;'>
                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='24' height='24' fill='#ffffff'>
                    <path d='M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z'/>
                </svg>
            </div>
            <h1>Merci pour votre réservation !</h1>
            <p>Votre demande a été reçue avec succès</p>
        </div>
        <div class='content'>
            <p>Bonjour <strong>{$client}</strong>,</p>
            <p>Nous avons bien reçu votre demande de réservation et nous vous en remercions ! Voici un récapitulatif :</p>
            <div class='booking-card'>
                <div class='service-info'>
                    <h3>{$service}</h3>" . ($employee ? "<p>avec {$employee}</p>" : "") . "
                </div>
                <div class='detail-item'>
                    <svg width='16' height='16' fill='none' stroke='#6b7280' stroke-width='2' viewBox='0 0 24 24'>
                        <rect x='3' y='4' width='18' height='18' rx='2' ry='2'/>
                        <line x1='16' y1='2' x2='16' y2='6'/>
                        <line x1='8' y1='2' x2='8' y2='6'/>
                        <line x1='3' y1='10' x2='21' y2='10'/>
                    </svg>
                    <span>{$date}</span>
                </div>
                <div class='detail-item'>
                    <svg width='16' height='16' fill='none' stroke='#6b7280' stroke-width='2' viewBox='0 0 24 24'>
                        <circle cx='12' cy='12' r='10'/>
                        <polyline points='12,6 12,12 16,14'/>
                    </svg>
                    <span>{$time}</span>
                </div>
            </div>
            <div class='next-step'>
                <p>⏳ <strong>Prochaine étape :</strong> Vous recevrez une confirmation définitive très prochainement de notre part.</p>
            </div>
            <p>Si vous avez des questions ou souhaitez modifier votre réservation, n'hésitez pas à nous contacter.</p>
            <p>À très bientôt,<br><strong>L'équipe {$company}</strong></p>
        </div>
        <div class='footer'>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
";
    }

    /**
     * Envoie une notification par email
     */
    public static function send_email($to, $subject, $message) {
        $headers = array('Content-Type: text/html; charset=UTF-8');
        return wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Envoie une notification SMS
     */
    public static function send_sms($to, $message) {
        // À implémenter avec un service SMS
        return false;
    }

    /**
     * Envoie une notification push
     */
    public static function send_push($user_id, $title, $message) {
        // À implémenter avec un service push
        return false;
    }

    /**
     * Envoie une notification WhatsApp
     */
    public static function send_whatsapp($to, $message) {
        // À implémenter avec l'API WhatsApp
        return false;
    }

    /**
     * Envoie une notification de rappel
     */
    public static function send_reminder($booking_id) {
        $booking = IB_Bookings::get_by_id($booking_id);
        if (!$booking) return false;

        $client = IB_Clients::get_by_id($booking->client_id);
        $service = IB_Services::get_by_id($booking->service_id);
        $employee = IB_Employees::get_by_id($booking->employee_id);

        // Email au client (si email présent)
        if (!empty($client->email) && is_email($client->email)) {
            $subject = sprintf(__('Rappel : Rendez-vous %s', 'institut-booking'), $service->name);
            $template = "Bonjour {client_name},<br><br>Ceci est un rappel pour votre rendez-vous :<br><br>Service : {service_name}<br>Date : {date}<br>Heure : {time}<br>Praticienne : {employee_name}<br><br>Cordialement,<br>{company}";
            $vars = [
                'client_name' => $client->name,
                'service_name' => $service->name,
                'date' => date_i18n(get_option('date_format'), strtotime($booking->start_time)),
                'time' => date_i18n(get_option('time_format'), strtotime($booking->start_time)),
                'employee_name' => $employee->name,
                'company' => get_bloginfo('name')
            ];
            $message = self::replace_vars($template, $vars);
            self::send_email($client->email, $subject, $message);
        } else {
            // Fallback : prévenir l'admin si pas d'email client
            $admin_email = get_option('admin_email');
            $subject = __('Erreur : Pas d\'email client pour le rappel', 'institut-booking');
            $message = 'Impossible d\'envoyer le rappel au client (ID réservation : ' . intval($booking_id) . ').';
            self::send_email($admin_email, $subject, $message);
        }

        // SMS
        if ($client->phone) {
            $sms_message = sprintf(
                __('Rappel RDV : %s le %s à %s avec %s', 'institut-booking'),
                $service->name,
                date_i18n(get_option('date_format'), strtotime($booking->start_time)),
                date_i18n(get_option('time_format'), strtotime($booking->start_time)),
                $employee->name
            );
            self::send_sms($client->phone, $sms_message);
        }

        // Push
        if ($client->push_token) {
            self::send_push($client->id, $subject, $message);
        }

        // WhatsApp
        if ($client->whatsapp) {
            self::send_whatsapp($client->whatsapp, $message);
        }

        return true;
    }

    /**
     * Envoie une notification de confirmation
     */
    public static function send_confirmation($booking_id) {
        $booking = IB_Bookings::get_by_id($booking_id);
        if (!$booking) return false;

        // Try to get client from booking data first, then from clients table
        $client_email = isset($booking->client_email) && is_email($booking->client_email) ? $booking->client_email : '';
        $client_name = isset($booking->client_name) ? $booking->client_name : '';
        
        // If not found in booking, try clients table
        if (empty($client_email) || empty($client_name)) {
            $client = IB_Clients::get_by_id($booking->client_id);
            if ($client) {
                $client_email = $client_email ?: $client->email;
                $client_name = $client_name ?: $client->name;
            }
        }
        
        $service = IB_Services::get_by_id($booking->service_id);
        $employee = IB_Employees::get_by_id($booking->employee_id);

        // Email au client (si email présent)
        if (!empty($client_email) && is_email($client_email)) {
            $subject = sprintf(__('Confirmation : Rendez-vous %s', 'institut-booking'), $service ? $service->name : 'Service');
            
            // Utiliser le template moderne comme pour l'email de remerciement
            require_once plugin_dir_path(__FILE__) . '/class-email.php';
            $placeholders = [
                '{client}' => $client_name ?: 'Client',
                '{client_name}' => $client_name ?: 'Client',
                '{service}' => $service ? $service->name : 'Service',
                '{service_name}' => $service ? $service->name : 'Service',
                '{company}' => get_bloginfo('name'),
                '{date}' => date('d-m-Y', strtotime($booking->start_time)),
                '{time}' => date('H:i', strtotime($booking->start_time)),
                '{employee}' => $employee ? $employee->name : ''
            ];
            
            $message = IB_Email::get_modern_template('confirm', $placeholders);
            self::send_email($client_email, $subject, $message);
        } else {
            // Fallback : prévenir l'admin si pas d'email client
            $admin_email = get_option('admin_email');
            $subject = __('Erreur : Pas d\'email client pour la confirmation', 'institut-booking');
            $message = 'Impossible d\'envoyer la confirmation au client (ID réservation : ' . intval($booking_id) . ').';
            self::send_email($admin_email, $subject, $message);
        }

        // SMS
        if ($client->phone) {
            $sms_message = sprintf(
                __('RDV confirmé : %s le %s à %s avec %s', 'institut-booking'),
                $service->name,
                date_i18n(get_option('date_format'), strtotime($booking->start_time)),
                date_i18n(get_option('time_format'), strtotime($booking->start_time)),
                $employee->name
            );
            self::send_sms($client->phone, $sms_message);
        }

        // Push
        if ($client->push_token) {
            self::send_push($client->id, $subject, $message);
        }

        // WhatsApp
        if ($client->whatsapp) {
            self::send_whatsapp($client->whatsapp, $message);
        }

        return true;
    }

    /**
     * Envoie une notification d'annulation
     */
    public static function send_cancellation($booking_id) {
        $booking = IB_Bookings::get_by_id($booking_id);
        if (!$booking) return false;

        $client = IB_Clients::get_by_id($booking->client_id);
        $service = IB_Services::get_by_id($booking->service_id);
        $employee = IB_Employees::get_by_id($booking->employee_id);

        // Email au client (si email présent)
        if (!empty($client->email) && is_email($client->email)) {
            $subject = sprintf(__('Annulation : Rendez-vous %s', 'institut-booking'), $service->name);
            $template = "Bonjour {client_name},<br><br>Votre rendez-vous pour le service {service_name} le {date} à {time} a été annulé.<br><br>Cordialement,<br>L'équipe de {company}";
            $vars = [
                'client_name' => $client->name,
                'service_name' => $service->name,
                'date' => date_i18n(get_option('date_format'), strtotime($booking->start_time)),
                'time' => date_i18n(get_option('time_format'), strtotime($booking->start_time)),
                'company' => get_bloginfo('name')
            ];
            $message = self::replace_vars($template, $vars);
            self::send_email($client->email, $subject, $message);
        } else {
            // Fallback : prévenir l'admin si pas d'email client
            $admin_email = get_option('admin_email');
            $subject = __('Erreur : Pas d\'email client pour l\'annulation', 'institut-booking');
            $message = 'Impossible d\'envoyer l\'annulation au client (ID réservation : ' . intval($booking_id) . ').';
            self::send_email($admin_email, $subject, $message);
        }

        // SMS
        if ($client->phone) {
            $sms_message = sprintf(
                __('RDV annulé : %s le %s à %s avec %s', 'institut-booking'),
                $service->name,
                date_i18n(get_option('date_format'), strtotime($booking->start_time)),
                date_i18n(get_option('time_format'), strtotime($booking->start_time)),
                $employee->name
            );
            self::send_sms($client->phone, $sms_message);
        }

        // Push
        if ($client->push_token) {
            self::send_push($client->id, $subject, $message);
        }

        // WhatsApp
        if ($client->whatsapp) {
            self::send_whatsapp($client->whatsapp, $message);
        }

        return true;
    }

    // Ajouter une notification
    public static function add($type, $message, $target = 'admin', $link = null) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'ib_notifications', [
            'type' => sanitize_text_field($type),
            'message' => sanitize_textarea_field($message),
            'target' => sanitize_text_field($target),
            'status' => 'unread',
            'link' => $link ? esc_url_raw($link) : null,
            'created_at' => current_time('mysql'),
        ]);
    }

    // Récupérer les notifications non lues (pour la cloche)
    public static function get_unread($target = 'admin', $limit = 10) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ib_notifications WHERE target = %s AND status = 'unread' ORDER BY created_at DESC LIMIT %d",
            $target, $limit
        ));
    }

    // Récupérer les notifications récentes (lues + non lues)
    public static function get_recent($target = 'admin', $limit = 15, $search = '') {
        global $wpdb;
        
        // Si c'est pour l'affichage dans le panneau (target = 'admin'), on filtre sur le type 'booking_new'
        $is_for_notification_panel = ($target === 'admin' && empty($search));
        
        $sql = "SELECT * FROM {$wpdb->prefix}ib_notifications WHERE target = %s";
        $params = [$target];
        
        // Pour le panneau de notifications, on ne veut que les notifications de nouvelles réservations
        if ($is_for_notification_panel) {
            $sql .= " AND type = 'booking_new'";
            error_log('[IB Booking] get_recent - Filtrage sur le type booking_new activé');
        }
        
        if (!empty($search)) {
            $sql .= " AND (type LIKE %s OR message LIKE %s OR status LIKE %s OR created_at LIKE %s)";
            $like = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT %d";
        $params[] = $limit;
        
        // Log de la requête SQL et des paramètres
        error_log('[IB Booking] get_recent - Préparation de la requête : ' . $sql);
        error_log('[IB Booking] get_recent - Paramètres : ' . print_r($params, true));
        
        $results = $wpdb->get_results($wpdb->prepare($sql, ...$params));
        
        // Log pour le débogage
        error_log('[IB Booking] get_recent - Requête exécutée : ' . $wpdb->last_query);
        error_log(sprintf(
            '[IB Booking] get_recent - %d résultats trouvés pour target="%s" et search="%s"', 
            count($results), 
            $target,
            $search
        ));
        
        if (empty($results)) {
            // Si aucun résultat, vérifier s'il y a des notifications dans la table
            $total_notifications = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ib_notifications");
            error_log("[IB Booking] get_recent - Aucun résultat. Total des notifications dans la table : " . $total_notifications);
            
            if ($total_notifications > 0) {
                // Afficher les 5 dernières notifications pour le débogage
                $sample_notifications = $wpdb->get_results("SELECT id, type, message, created_at FROM {$wpdb->prefix}ib_notifications ORDER BY created_at DESC LIMIT 5");
                error_log('[IB Booking] get_recent - Exemple de notifications dans la table : ' . print_r($sample_notifications, true));
            }
        } else {
            // Log des premiers caractères du message de la première notification pour vérification
            $first_msg = isset($results[0]->message) ? substr($results[0]->message, 0, 100) . '...' : 'Aucun message';
            error_log('[IB Booking] get_recent - Premier message : ' . $first_msg);
        }
        
        return $results;
    }

    // Marquer une notification comme lue
    public static function mark_as_read($id) {
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'ib_notifications', ['status' => 'read'], ['id' => intval($id)]);
    }

    // Marquer toutes les notifications comme lues pour un utilisateur
    public static function mark_all_as_read($target = 'admin') {
        global $wpdb;
        $wpdb->update($wpdb->prefix . 'ib_notifications', ['status' => 'read'], ['target' => $target, 'status' => 'unread']);
    }
} 