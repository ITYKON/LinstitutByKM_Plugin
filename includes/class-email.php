<?php
// Gestion des emails de notification
if (!defined('ABSPATH')) exit;

class IB_Email {
    public static function send_confirmation($to, $subject, $message) {
        wp_mail($to, $subject, $message);
    }

    public static function send_update($to, $subject, $message) {
        wp_mail($to, $subject, $message);
    }

    public static function send_auto($type, $context) {
        $company = get_bloginfo('name');
        
        // Améliorer le formatage des dates
        $formatted_date = isset($context['date']) ? date('d-m-Y', strtotime($context['date'])) : '';
        $formatted_time = isset($context['time']) ? date('H:i', strtotime($context['time'])) : '';
        
        $placeholders = [
            '{service}' => $context['service'],
            '{service_name}' => $context['service'], // Support both formats
            '{date}' => $formatted_date,
            '{time}' => $formatted_time,
            '{client}' => $context['client'],
            '{client_name}' => $context['client'], // Support both formats
            '{employee}' => $context['employee'],
            '{employee_name}' => $context['employee'], // Support both formats
            '{company}' => $company,
            '{extras}' => isset($context['extras']) ? $context['extras'] : '',
            '{recept_name}' => 'Réceptionniste',
            '{admin_name}' => 'Admin',
        ];
        
        $subject = ($type === 'confirm') ? 'Confirmation de réservation' : 'Annulation de réservation';
        
        // Add HTML headers
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Client - Toujours utiliser le template moderne
        if (!empty($context['client_email'])) {
            // Forcer l'utilisation du template moderne
            $body_client = self::get_modern_template($type, $placeholders);
            wp_mail($context['client_email'], $subject, $body_client, $headers);
        }
        
        // Récupérer les templates pour admin et réceptionnistes
        $templates = [
            'admin_confirm' => get_option('ib_notify_admin_confirm'),
            'admin_cancel' => get_option('ib_notify_admin_cancel'),
            'recept_confirm' => get_option('ib_notify_recept_confirm'),
            'recept_cancel' => get_option('ib_notify_recept_cancel'),
        ];
        
        // Admin
        $admin_email = get_option('admin_email');
        if ($admin_email && !empty($templates['admin_' . $type])) {
            $body_admin = strtr($templates['admin_' . $type], $placeholders);
            wp_mail($admin_email, $subject, $body_admin, $headers);
        }
        
        // Réceptionniste (tous les users avec le rôle)
        if (!empty($templates['recept_' . $type])) {
            $body_reception = strtr($templates['recept_' . $type], $placeholders);
            $users = get_users(['role' => 'receptionist']);
            foreach ($users as $user) {
                wp_mail($user->user_email, $subject, $body_reception, $headers);
            }
        }
    }

    /**
     * Template d'email moderne style Planity - Compatible mode sombre
     */
    public static function get_modern_template($type, $placeholders) {
        $company = $placeholders['{company}'];
        $client = $placeholders['{client}'];
        $service = $placeholders['{service}'];
        $date = $placeholders['{date}'];
        $time = $placeholders['{time}'];
        $employee = $placeholders['{employee}'];

        if ($type === 'confirm') {
            return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name='color-scheme' content='light dark'>
    <meta name='supported-color-schemes' content='light dark'>
    <title>Confirmation de réservation</title>
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
            background: #A8977B !important; 
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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
            color: #e2e8f0 !important;
            margin: 0.5rem 0 0;
            font-size: 16px;
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
        .confirmation-text {
            font-size: 18px !important;
            margin-bottom: 1.5rem !important;
            color: #000000 !important;
        }
        
        /* Forcer la couleur noire en mode sombre */
        @media (prefers-color-scheme: dark) {
            .confirmation-text {
                color: #000000 !important;
            }
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

        /* Support amélioré pour le mode sombre - Approche hybride */
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
            .header h1 {
                color: #f7fafc !important;
            }
            .header p {
                color: #cbd5e0 !important;
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
            <h1>Réservation confirmée</h1>
            <p>Votre rendez-vous est officiellement validé</p>
        </div>
        <div class='content'>
            <p>Bonjour <strong>{$client}</strong>,</p>
            <p>Nous avons le plaisir de vous confirmer votre réservation. Voici les détails :</p>
            <div class='booking-card'>
                <div class='service-info'>
                    <h3>{$service}</h3>
                    <p>avec {$employee}</p>
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
            <p>N'hésitez pas à nous contacter si vous avez des questions ou des demandes particulières.</p>
            <p>À très bientôt,<br><strong>{$company}</strong></p>
        </div>
        <div class='footer'>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
";
        } else {
            // Template d'annulation - Compatible mode sombre
            return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name='color-scheme' content='light dark'>
    <meta name='supported-color-schemes' content='light dark'>
    <title>Annulation de réservation</title>
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
            background: #A8977B !important;
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        .header h1 {
            color: #ffffff !important;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
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
            .header-icon {
                background: #4a5568 !important;
            }
            .content p {
                color: #e2e8f0 !important;
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
        [data-ogsc] .content p {
            color: #e2e8f0 !important;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <div class='header-icon'>
                <svg width='32' height='32' fill='none' stroke='#dc2626' stroke-width='2' viewBox='0 0 24 24'>
                    <circle cx='12' cy='12' r='10'/>
                    <line x1='15' y1='9' x2='9' y2='15'/>
                    <line x1='9' y1='9' x2='15' y2='15'/>
                </svg>
            </div>
            <h1>Réservation annulée</h1>
        </div>
        <div class='content'>
            <p>Bonjour <strong>{$client}</strong>,</p>
            <p>Votre réservation pour <strong>{$service}</strong> le <strong>{$date}</strong> à <strong>{$time}</strong> a été annulée.</p>
            <p>Cordialement,<br><strong>{$company}</strong></p>
        </div>
    </div>
</body>
</html>";
        }
    }
}
