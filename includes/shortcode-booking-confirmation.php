<?php
/**
 * Shortcode pour afficher la confirmation de réservation
 * Utilisation: [booking_confirmation]
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class IB_Booking_Confirmation_Shortcode {
    
    public static function init() {
        add_shortcode('booking_confirmation', [__CLASS__, 'render_shortcode']);
        add_action('init', [__CLASS__, 'maybe_handle_confirmation']);
    }
    
    /**
     * Gère l'affichage de la confirmation
     */
    public static function render_shortcode() {
        // Vérifier si nous avons un ID de réservation
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        
        if (!$booking_id) {
            return '<div class="booking-confirmation-error">Aucune réservation spécifiée.</div>';
        }
        
        // Récupérer les détails de la réservation
        global $wpdb;
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT b.*, s.name as service_name, e.name as employee_name 
             FROM {$wpdb->prefix}ib_bookings b
             LEFT JOIN {$wpdb->prefix}ib_services s ON b.service_id = s.id
             LEFT JOIN {$wpdb->prefix}ib_employees e ON b.employee_id = e.id
             WHERE b.id = %d", 
            $booking_id
        ));
        
        if (!$booking) {
            return '<div class="booking-confirmation-error">Réservation non trouvée.</div>';
        }
        
        // Formater la date et l'heure
        $date = new DateTime($booking->date);
        $formatted_date = $date->format('d/m/Y');
        $start_time = new DateTime($booking->start_time);
        $formatted_time = $start_time->format('H:i');
        
        // Construire l'HTML de confirmation
        ob_start();
        ?>
        <div class="booking-confirmation-container" style="max-width: 600px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div class="confirmation-header" style="text-align: center; margin-bottom: 2rem;">
                <div class="confirmation-icon" style="width: 64px; height: 64px; background: #4CAF50; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h1 style="color: #333; margin: 0 0 0.5rem 0;">Réservation confirmée !</h1>
                <p style="color: #666; margin: 0;">Votre rendez-vous a bien été enregistré.</p>
            </div>
            
            <div class="booking-details" style="background: #f9f9f9; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
                <h2 style="color: #333; font-size: 1.25rem; margin-top: 0; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #eee;">Détails de la réservation</h2>
                
                <div class="detail-row" style="display: flex; margin-bottom: 0.75rem;">
                    <span class="detail-label" style="flex: 1; color: #666; font-weight: 500;">N° de réservation :</span>
                    <span class="detail-value" style="flex: 2; color: #333;">#<?php echo esc_html($booking->id); ?></span>
                </div>
                
                <div class="detail-row" style="display: flex; margin-bottom: 0.75rem;">
                    <span class="detail-label" style="flex: 1; color: #666; font-weight: 500;">Service :</span>
                    <span class="detail-value" style="flex: 2; color: #333;"><?php echo esc_html($booking->service_name); ?></span>
                </div>
                
                <div class="detail-row" style="display: flex; margin-bottom: 0.75rem;">
                    <span class="detail-label" style="flex: 1; color: #666; font-weight: 500;">Praticien(ne) :</span>
                    <span class="detail-value" style="flex: 2; color: #333;"><?php echo esc_html($booking->employee_name); ?></span>
                </div>
                
                <div class="detail-row" style="display: flex; margin-bottom: 0.75rem;">
                    <span class="detail-label" style="flex: 1; color: #666; font-weight: 500;">Date :</span>
                    <span class="detail-value" style="flex: 2; color: #333;"><?php echo esc_html($formatted_date); ?></span>
                </div>
                
                <div class="detail-row" style="display: flex; margin-bottom: 0.75rem;">
                    <span class="detail-label" style="flex: 1; color: #666; font-weight: 500;">Heure :</span>
                    <span class="detail-value" style="flex: 2; color: #333;"><?php echo esc_html($formatted_time); ?></span>
                </div>
                
                <?php if (!empty($booking->price)) : ?>
                <div class="detail-row" style="display: flex; margin-bottom: 0.75rem;">
                    <span class="detail-label" style="flex: 1; color: #666; font-weight: 500;">Prix :</span>
                    <span class="detail-value" style="flex: 2; color: #333; font-weight: 600;">
                        <?php echo number_format(floatval($booking->price), 2, ',', ' '); ?> DA
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="customer-details" style="background: #f9f9f9; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem;">
                <h2 style="color: #333; font-size: 1.25rem; margin-top: 0; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #eee;">Vos informations</h2>
                
                <div class="detail-row" style="display: flex; margin-bottom: 0.75rem;">
                    <span class="detail-label" style="flex: 1; color: #666; font-weight: 500;">Nom :</span>
                    <span class="detail-value" style="flex: 2; color: #333;"><?php echo esc_html($booking->client_name); ?></span>
                </div>
                
                <?php if (!empty($booking->client_email)) : ?>
                <div class="detail-row" style="display: flex; margin-bottom: 0.75rem;">
                    <span class="detail-label" style="flex: 1; color: #666; font-weight: 500;">Email :</span>
                    <span class="detail-value" style="flex: 2; color: #333;"><?php echo esc_html($booking->client_email); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($booking->client_phone)) : ?>
                <div class="detail-row" style="display: flex; margin-bottom: 0.75rem;">
                    <span class="detail-label" style="flex: 1; color: #666; font-weight: 500;">Téléphone :</span>
                    <span class="detail-value" style="flex: 2; color: #333;"><?php echo esc_html($booking->client_phone); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="confirmation-actions" style="text-align: center;">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="button" style="display: inline-block; background: #4CAF50; color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 4px; font-weight: 500; transition: background-color 0.3s;">
                    Retour à l'accueil
                </a>
                
                <?php if (!empty($booking->client_email)) : ?>
                <button id="send-email-again" style="margin-left: 1rem; background: #f0f0f0; color: #333; border: 1px solid #ddd; padding: 0.75rem 1.5rem; border-radius: 4px; font-weight: 500; cursor: pointer; transition: background-color 0.3s;">
                    Renvoyer la confirmation par email
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#send-email-again').on('click', function() {
                var button = $(this);
                var originalText = button.text();
                
                button.prop('disabled', true).text('Envoi en cours...');
                
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'ib_send_booking_confirmation',
                        booking_id: <?php echo $booking_id; ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Email de confirmation envoyé avec succès !');
                        } else {
                            alert('Erreur lors de l\'envoi de l\'email : ' + (response.data || 'Erreur inconnue'));
                        }
                    },
                    error: function() {
                        alert('Erreur de connexion. Veuillez réessayer.');
                    },
                    complete: function() {
                        button.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Gère la logique de confirmation de réservation
     */
    public static function maybe_handle_confirmation() {
        if (isset($_GET['booking_confirmation']) && !empty($_GET['booking_confirmation'])) {
            $booking_id = intval($_GET['booking_confirmation']);
            
            // Rediriger vers la page avec le shortcode et l'ID de réservation
            $redirect_url = add_query_arg('booking_id', $booking_id, home_url('/confirmation'));
            wp_redirect($redirect_url);
            exit;
        }
    }
}

// Initialiser le shortcode
IB_Booking_Confirmation_Shortcode::init();

// Ajouter un endpoint AJAX pour renvoyer la confirmation par email
add_action('wp_ajax_ib_send_booking_confirmation', 'ib_send_booking_confirmation_callback');
add_action('wp_ajax_nopriv_ib_send_booking_confirmation', 'ib_send_booking_confirmation_callback');

function ib_send_booking_confirmation_callback() {
    $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
    
    if (!$booking_id) {
        wp_send_json_error('ID de réservation manquant');
        return;
    }
    
    // Inclure la classe de notification si elle n'est pas déjà chargée
    if (!class_exists('IB_Notifications')) {
        require_once plugin_dir_path(__FILE__) . 'notifications.php';
    }
    
    // Envoyer l'email de confirmation
    $result = IB_Notifications::send_booking_confirmation($booking_id);
    
    if ($result) {
        wp_send_json_success('Email de confirmation envoyé avec succès');
    } else {
        wp_send_json_error('Erreur lors de l\'envoi de l\'email de confirmation');
    }
}
