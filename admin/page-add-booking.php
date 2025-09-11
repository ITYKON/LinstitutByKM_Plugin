<?php
/**
 * Admin page for adding a new booking
 * 
 * @package LinstitutByKM_Plugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue our admin scripts and styles
function ib_enqueue_booking_admin_scripts($hook) {
    if ('toplevel_page_ib-add-booking' !== $hook) {
        return;
    }
    
    // Enqueue jQuery (should be already included by WordPress)
    wp_enqueue_script('jquery');
    
    // Enqueue our admin booking script
    wp_enqueue_script(
        'ib-admin-booking',
        plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin-booking.js',
        array('jquery'),
        filemtime(plugin_dir_path(dirname(__FILE__)) . 'assets/js/admin-booking.js'),
        true
    );
    
    // Localize the script with the AJAX URL and nonce
    wp_localize_script(
        'ib-admin-booking',
        'bookingAjax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('booking_lookup_nonce')
        )
    );
    
    // Add some basic styles
    wp_add_inline_style('wp-admin', '
        .form-table th {
            width: 200px;
        }
        .required {
            color: #dc3232;
        }
        .loading {
            background: #f1f1f1 url(' . admin_url('images/spinner.gif') . ') no-repeat right center !important;
        }
    ');
    
    // Debug output
    add_action('admin_footer', function() {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            console.log('Debug - Script chargé sur la page :', window.location.href);
            console.log('Debug - bookingAjax défini :', typeof bookingAjax !== 'undefined');
            if (typeof bookingAjax !== 'undefined') {
                console.log('Debug - bookingAjax.ajaxurl :', bookingAjax.ajaxurl);
                console.log('Debug - bookingAjax.nonce :', bookingAjax.nonce);
            }
            
            // Vérifier si jQuery est correctement chargé
            console.log('Debug - jQuery version :', $.fn.jquery);
            
            // Tester l'écouteur d'événements
            if ($('#client_phone').length) {
                console.log('Debug - Champ client_phone trouvé dans le DOM');
                
                // Tester un gestionnaire d'événement simple
                $('#client_phone').on('keydown.test', function(e) {
                    console.log('Debug - Événement keydown sur client_phone', e.which);
                });
            } else {
                console.error('Debug - ERREUR: Champ client_phone NON trouvé dans le DOM');
                console.log('Debug - Contenu du formulaire :', $('form').html());
            }
        });
        </script>
        <?php
    });
}
add_action('admin_enqueue_scripts', 'ib_enqueue_booking_admin_scripts');

// Add the admin menu item
function ib_add_booking_admin_menu() {
    add_menu_page(
        __('Ajouter une réservation', 'institut-booking'), // Page title
        __('Ajouter une réservation', 'institut-booking'), // Menu title
        'edit_posts', // Capability required
        'ib-add-booking', // Menu slug
        'ib_render_booking_page', // Callback function
        'dashicons-calendar-alt', // Icon
        30 // Position
    );
}
add_action('admin_menu', 'ib_add_booking_admin_menu');

// Render the booking page
function ib_render_booking_page() {
    // Check user capabilities
    if (!current_user_can('edit_posts')) {
        wp_die(__('Vous n\'avez pas les droits nécessaires pour accéder à cette page.', 'institut-booking'));
    }
    
    // Debug information
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Ajouter une réservation', 'institut-booking') . '</h1>';
    
    // Check if script is enqueued
    if (!wp_script_is('ib-admin-booking', 'enqueued')) {
        echo '<div class="notice notice-error"><p>Le script ib-admin-booking n\'est pas chargé.</p></div>';
        // Try to enqueue it manually
        wp_enqueue_script('ib-admin-booking');
    } else {
        echo '<div class="notice notice-success"><p>Le script ib-admin-booking est correctement chargé.</p></div>';
    }
    
    // Check if jQuery is loaded
    if (!wp_script_is('jquery', 'enqueued')) {
        echo '<div class="notice notice-error"><p>jQuery n\'est pas chargé.</p></div>';
        wp_enqueue_script('jquery');
    }
    
    // Check if bookingAjax is defined
    echo '<div class="notice notice-info"><p>Vérification de bookingAjax : ' . 
         (defined('DOING_AJAX') && DOING_AJAX ? 'DOING_AJAX est défini' : 'DOING_AJAX n\'est pas défini') . 
         '</p></div>';
    
    // Include the template
    include_once plugin_dir_path(dirname(__FILE__)) . 'templates/admin-booking-form.php';
    
    echo '</div>'; // Close wrap div
}

// Handle form submission
function ib_handle_booking_submission() {
    // Verify nonce
    if (!isset($_POST['booking_nonce']) || !wp_verify_nonce($_POST['booking_nonce'], 'booking_form_nonce')) {
        wp_die(__('Erreur de sécurité. Veuillez réessayer.', 'institut-booking'));
    }
    
    // Check user capabilities
    if (!current_user_can('edit_posts')) {
        wp_die(__('Accès refusé. Droits insuffisants.', 'institut-booking'));
    }
    
    // Process the form data here
    // This is where you would save the booking to your database
    
    // For now, just show a success message
    add_settings_error(
        'ib_booking_messages',
        'booking_created',
        __('La réservation a été enregistrée avec succès !', 'institut-booking'),
        'updated'
    );
    
    // Clear the form
    $_POST = array();
}

// Check if form was submitted
if (isset($_POST['submit']) && isset($_POST['booking_nonce'])) {
    ib_handle_booking_submission();
}
