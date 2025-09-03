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
            '{cancel_token}' => isset($context['cancel_token']) ? $context['cancel_token'] : '',
            '{booking_id}' => isset($context['booking_id']) ? $context['booking_id'] : (isset($context['id']) ? $context['id'] : ''),
            '{client_email}' => isset($context['client_email']) ? $context['client_email'] : '',
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
        $cancel_token = isset($placeholders['{cancel_token}']) ? $placeholders['{cancel_token}'] : '';
        $booking_id = isset($placeholders['{booking_id}']) ? $placeholders['{booking_id}'] : '';
        $client_email = isset($placeholders['{client_email}']) ? $placeholders['{client_email}'] : '';
        $token = '';
        $cancel_url = '';
        if (!empty($booking_id) && !empty($client_email)) {
            if (defined('AUTH_KEY')) {
                $token = md5($booking_id . $client_email . AUTH_KEY);
                $cancel_url = site_url('/wp-content/plugins/LinstitutByKM_Plugin/annuler-reservation-handler.php?booking_id=' . urlencode($booking_id) . '&token=' . urlencode($token));
            }
        }

        $cancel_section = '';
        if ($cancel_url && $type !== 'cancel') {
            $cancel_section = "<div class='cancel-section' style='color:#111 !important;'>
                <p style='margin: 0 0 15px 0; font-size: 15px; color: #111 !important; font-weight: 600;'>
                    <svg style='vertical-align: middle; margin-right: 8px;' width='18' height='18' fill='none' stroke='#dc3545' stroke-width='2' viewBox='0 0 24 24'>
                        <path d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'/>
                    </svg>
                    Besoin d'annuler ?
                </p>
                <p style='margin: 0 0 15px 0; font-size: 14px; color: #111 !important; line-height: 1.5;'>
                    Si vous souhaitez annuler votre réservation, veuillez cliquer sur le bouton ci-dessous. Ce lien est personnel et ne doit pas être partagé.
                </p>
                <a href='{$cancel_url}' class='cancel-btn'>Annuler cette réservation</a>
                <p style='margin: 10px 0 0 0; font-size: 12px; color: #111 !important;'>Vous recevrez une confirmation par email une fois l'annulation effectuée.</p>
            </div>";
        }

        if ($type !== 'cancel') {
            // Template de confirmation
            return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Confirmation de réservation</title>
    <style>
        :root { color-scheme: light dark; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f5f5f5 !important; margin: 0; padding: 20px; -webkit-text-size-adjust: 100%; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff !important; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); border: 1px solid #e1e5e9; border-radius: 12px; }
        .header { background: #A8977B !important; padding: 2rem; text-align: center; color: white; }
        .header-icon { width: 60px; height: 60px; background: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .content { padding: 2rem; background-color: #ffffff !important; }
        .content p { color: #2d3748 !important; font-size: 16px; line-height: 1.6; margin: 0 0 1rem; }
        .booking-card { background: #f8f9fa; border-radius: 8px; padding: 1.5rem; margin: 1.5rem 0; }
        .service-info h3 { margin: 0 0 0.5rem 0; color: #1a202c; }
        .detail-item { display: flex; align-items: center; margin: 0.75rem 0; }
        .detail-item svg { margin-right: 0.75rem; }
        .cancel-section { margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-left: 4px solid #dc3545; border-radius: 4px; }
        .cancel-btn { display: inline-block; padding: 10px 20px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500; text-align: center; margin-top: 10px; }
        @media (prefers-color-scheme: dark) { body { background-color: #0f0f0f !important; color: #f7fafc !important; } .container, .content { background-color: #1a202c !important; border-color: #4a5568 !important; } .content p, .service-info h3 { color: #e2e8f0 !important; } .booking-card { background: #2d3748; } strong { color: #ffffff !important; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="#A8977B">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                </svg>
            </div>
            <h1>Réservation confirmée</h1>
            <p>Votre rendez-vous est officiellement validé</p>
        </div>
        <div class="content">
            <p>Bonjour <strong>{$client}</strong>,</p>
            <p>Nous avons le plaisir de vous confirmer votre réservation. Voici les détails :</p>
            <div class="booking-card">
                <div class="service-info">
                    <h3>{$service}</h3>
                    <p>avec {$employee}</p>
                </div>
                <div class="detail-item">
                    <svg width="16" height="16" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span>{$date}</span>
                </div>
                <div class="detail-item">
                    <svg width="16" height="16" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12,6 12,12 16,14"/>
                    </svg>
                    <span>{$time}</span>
                </div>
            </div>
            <p>N'hésitez pas à nous contacter si vous avez des questions ou des demandes particulières.</p>
            {$cancel_section}
            <p>À très bientôt,<br><strong>{$company}</strong></p>
        </div>
        <div style='padding: 1.5rem; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb;'>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
HTML;
        } else {
            // Template d'annulation
            return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Annulation de réservation</title>
    <style>
        :root { color-scheme: light dark; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f5f5f5 !important; margin: 0; padding: 20px; -webkit-text-size-adjust: 100%; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff !important; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); border: 1px solid #e1e5e9; }
        .header { background: #A8977B !important; padding: 2rem; text-align: center; }
        .header-icon { background: #ffffff !important; width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 1rem; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); }
        .header h1 { color: #ffffff !important; margin: 0; font-size: 24px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .content { padding: 2rem; background-color: #ffffff !important; }
        .content p { color: #2d3748 !important; font-size: 16px; line-height: 1.6; margin: 0 0 1rem; }
        @media (prefers-color-scheme: dark) { body { background-color: #0f0f0f !important; color: #f7fafc !important; } .container { background-color: #1a202c !important; border: 1px solid #4a5568 !important; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important; } .content { background-color: #1a202c !important; } .content p { color: #e2e8f0 !important; } strong { color: #ffffff !important; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">
                <svg width="32" height="32" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <h1>Réservation annulée</h1>
        </div>
        <div class="content">
            <p>Bonjour <strong>{$client}</strong>,</p>
            <p>Votre réservation pour <strong>{$service}</strong> le <strong>{$date}</strong> à <strong>{$time}</strong> a été annulée.</p>
            <p>N'hésitez pas à nous contacter si vous avez des questions ou pour toute information complémentaire.</p>
            <p>Cordialement,<br><strong>{$company}</strong></p>
        </div>
        <div style='padding: 1.5rem; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb;'>
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
HTML;
        }
    }

}
