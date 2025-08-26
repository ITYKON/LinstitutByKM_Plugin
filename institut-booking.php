<?php
require_once plugin_dir_path(__FILE__) . 'includes/api-rest.php';
/**
 * Plugin Name: Booking-plugin-KM
 * Description: Un plugin de réservation  avec praticiennes, services et agenda.
 * Version: 2.0
 * Author: Ykon
 */

if (!defined('ABSPATH')) exit;

// Définition des constantes
define('IB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IB_PLUGIN_URL', plugin_dir_url(__FILE__));

// Chargement des helpers de base
require_once IB_PLUGIN_DIR . 'includes/helpers.php';

// Chargement des classes principales
require_once IB_PLUGIN_DIR . 'includes/class-migrations.php';
require_once IB_PLUGIN_DIR . 'includes/class-services.php';
require_once IB_PLUGIN_DIR . 'includes/class-employees.php';
require_once IB_PLUGIN_DIR . 'includes/class-bookings.php';
require_once IB_PLUGIN_DIR . 'includes/class-clients.php';
require_once IB_PLUGIN_DIR . 'includes/class-extras.php';
require_once IB_PLUGIN_DIR . 'includes/class-coupons.php';
require_once IB_PLUGIN_DIR . 'includes/class-feedback.php';
require_once IB_PLUGIN_DIR . 'includes/class-logs.php';
require_once IB_PLUGIN_DIR . 'includes/class-settings.php';
require_once IB_PLUGIN_DIR . 'includes/class-push.php';
require_once IB_PLUGIN_DIR . 'includes/class-whatsapp.php';
require_once IB_PLUGIN_DIR . 'includes/class-calendar.php';
require_once IB_PLUGIN_DIR . 'includes/class-categories.php';
require_once IB_PLUGIN_DIR . 'includes/class-availability.php';

// Chargement des fichiers d'installation et de rôles
require_once IB_PLUGIN_DIR . 'includes/install.php';
require_once IB_PLUGIN_DIR . 'includes/roles.php';

// Chargement des fichiers de notification
require_once IB_PLUGIN_DIR . 'includes/notifications.php';
require_once IB_PLUGIN_DIR . 'includes/sms.php';
require_once IB_PLUGIN_DIR . 'includes/ajax-notifications-enhanced.php';
require_once IB_PLUGIN_DIR . 'includes/notifications-refonte-integration.php';

// Chargement des fichiers admin UNIQUEMENT dans les callbacks de menu (voir plus bas)

// Enregistrement des menus admin
function ib_admin_menu() {
    // SUPPRESSION de la vérification de droits
    // if (!current_user_can('administrator')) {
    //     return;
    // }

    add_menu_page(
        __('Institut Booking', 'institut-booking'),
        __('Institut Booking', 'institut-booking'),
        'read', // Capacité minimale
        'institut-booking',
        'institut_booking_fullpage',
        'dashicons-calendar-alt',
        30
    );

    // Sous-menus
    add_submenu_page(
        'institut-booking',
        __('Dashboard', 'institut-booking'),
        __('Dashboard', 'institut-booking'),
        'read',
        'institut-booking',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Services', 'institut-booking'),
        __('Services', 'institut-booking'),
        'read',
        'institut-booking-services',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking-services',
        __('Catégories', 'institut-booking'),
        __('Catégories', 'institut-booking'),
        'read',
        'institut-booking-categories',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Praticiennes', 'institut-booking'),
        __('Praticiennes', 'institut-booking'),
        'read',
        'institut-booking-employees',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Réservations', 'institut-booking'),
        __('Réservations', 'institut-booking'),
        'read',
        'institut-booking-bookings',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Clients', 'institut-booking'),
        __('Clients', 'institut-booking'),
        'read',
        'institut-booking-clients',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Notifications', 'institut-booking'),
        __('Notifications', 'institut-booking'),
        'read',
        'institut-booking-notifications',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('SMS', 'institut-booking'),
        __('SMS', 'institut-booking'),
        'read',
        'institut-booking-sms',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Synchronisation Calendrier', 'institut-booking'),
        __('Synchronisation Calendrier', 'institut-booking'),
        'read',
        'institut-booking-calendar-sync',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Paramètres', 'institut-booking'),
        __('Paramètres', 'institut-booking'),
        'read',
        'institut-booking-settings',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Agenda', 'institut-booking'),
        __('Agenda', 'institut-booking'),
        'read',
        'institut-booking-calendar',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Coupons', 'institut-booking'),
        __('Coupons', 'institut-booking'),
        'read',
        'institut-booking-coupons',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Extras', 'institut-booking'),
        __('Extras', 'institut-booking'),
        'read',
        'institut-booking-extras',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Analytics', 'institut-booking'),
        __('Analytics', 'institut-booking'),
        'read',
        'institut-booking-analytics',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Logs', 'institut-booking'),
        __('Logs', 'institut-booking'),
        'read',
        'institut-booking-logs',
        'institut_booking_fullpage'
    );

    add_submenu_page(
        'institut-booking',
        __('Avis', 'institut-booking'),
        __('Avis', 'institut-booking'),
        'read',
        'institut-booking-feedback',
        'institut_booking_fullpage'
    );
}
add_action('admin_menu', 'ib_admin_menu');

// Enregistrement des assets admin consolidés
function ib_admin_assets($hook) {
    // Charger les styles uniquement sur les pages du plugin
    if (strpos($hook, 'institut-booking') !== false) {
        // Enregistrement des styles
        wp_enqueue_style('ib-admin-style', IB_PLUGIN_URL . 'assets/css/admin-style.css', [], '1.0');
        wp_enqueue_style('dashicons');
        wp_enqueue_style('wp-color-picker');
        
        // Scripts spécifiques aux pages du plugin
        wp_enqueue_script('ib-pdf-ticket-fix', IB_PLUGIN_URL . 'assets/js/pdf-ticket-fix.js', [], '1.0-' . time(), true);
    }
    
    // Localisation des variables AJAX pour le script de notification
    wp_localize_script('ib-ultra-notifications', 'ib_notif_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ib_notifications_nonce'),
        'admin_nonce' => wp_create_nonce('ib_admin_nonce')
    ));
    
    // Localisation des variables AJAX pour le script admin
    wp_localize_script('ib-admin-script', 'IBAdminVars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ib_notif_bell'),
        'admin_nonce' => wp_create_nonce('ib_admin_nonce')
    ));
    
    // Ajout des dépendances pour les datepickers et colorpickers
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('wp-color-picker');
}
add_action('admin_enqueue_scripts', 'ib_admin_assets');

// Shortcode du formulaire de réservation
function ib_booking_form_shortcode() {
    ob_start();
    include IB_PLUGIN_DIR . 'partials/booking-form.php';
    return ob_get_clean();
}
add_shortcode('institut_booking_form', 'ib_booking_form_shortcode');

// Activation du plugin
register_activation_hook(__FILE__, 'ib_install_plugin');

// Run migrations on plugin activation
register_activation_hook(__FILE__, function() {
    require_once IB_PLUGIN_DIR . 'includes/class-migrations.php';
    IB_Migrations::run();
});

// Run migrations on admin init to catch updates
add_action('admin_init', function() {
    if (!is_admin()) return;
    require_once IB_PLUGIN_DIR . 'includes/class-migrations.php';
    IB_Migrations::run();
});

// Action AJAX pour les créneaux disponibles
add_action('wp_ajax_get_available_slots', 'handle_get_available_slots');
add_action('wp_ajax_nopriv_get_available_slots', 'handle_get_available_slots');

function handle_get_available_slots() {
    check_ajax_referer('ib_nonce', 'nonce');
    
    $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
    
    if (!$employee_id || !$service_id || !$date) {
        wp_send_json_error(['message' => 'Paramètres manquants']);
        return;
    }
    
    $slots = IB_Availability::get_available_slots($employee_id, $service_id, $date);
    
    wp_send_json_success($slots);
}

// Traitement des actions extras (WordPress-compliant)
add_action('admin_init', function() {
    if (!is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'institut-booking-extras') return;
    if (isset($_POST['ib_add_extra'])) {
        IB_Extras::add($_POST['service_id'], $_POST['name'], $_POST['price'], $_POST['duration']);
        wp_redirect(admin_url('admin.php?page=institut-booking-extras&added=1'));
        exit;
    }
    if (isset($_GET['delete'])) {
        IB_Extras::delete((int)$_GET['delete']);
        wp_redirect(admin_url('admin.php?page=institut-booking-extras&deleted=1'));
        exit;
    }
    if (isset($_POST['ib_update_extra'])) {
        IB_Extras::update($_POST['id'], $_POST['service_id'], $_POST['name'], $_POST['price'], $_POST['duration']);
        wp_redirect(admin_url('admin.php?page=institut-booking-extras&updated=1'));
        exit;
    }
});

// Traitement des actions coupons (WordPress-compliant)
add_action('admin_init', function() {
    if (!is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'institut-booking-coupons') return;
    if (isset($_POST['ib_add_coupon'])) {
        IB_Coupons::add($_POST['code'], $_POST['discount'], $_POST['type'], $_POST['usage_limit'], $_POST['valid_from'], $_POST['valid_to']);
        wp_redirect(admin_url('admin.php?page=institut-booking-coupons&added=1'));
        exit;
    }
    if (isset($_GET['delete'])) {
        IB_Coupons::delete((int)$_GET['delete']);
        wp_redirect(admin_url('admin.php?page=institut-booking-coupons&deleted=1'));
        exit;
    }
    if (isset($_POST['ib_update_coupon'])) {
        IB_Coupons::update($_POST['id'], $_POST['code'], $_POST['discount'], $_POST['type'], $_POST['usage_limit'], $_POST['valid_from'], $_POST['valid_to']);
        wp_redirect(admin_url('admin.php?page=institut-booking-coupons&updated=1'));
        exit;
    }
});

// Traitement des actions catégories (WordPress-compliant)
add_action('admin_init', function() {
    if (!is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'institut-booking-categories') return;
    global $wpdb;
    if (isset($_POST['ib_add_category'])) {
        IB_Categories::add($_POST['name'], $_POST['color'], $_POST['icon']);
        wp_redirect(admin_url('admin.php?page=institut-booking-categories&added=1'));
        exit;
    }
    if (isset($_GET['delete'])) {
        IB_Categories::delete((int)$_GET['delete']);
        wp_redirect(admin_url('admin.php?page=institut-booking-categories&deleted=1'));
        exit;
    }
    if (isset($_POST['ib_update_category'])) {
        IB_Categories::update($_POST['id'], $_POST['name'], $_POST['color'], $_POST['icon']);
        wp_redirect(admin_url('admin.php?page=institut-booking-categories&updated=1'));
        exit;
    }
});



// Traitement des actions notifications (WordPress-compliant)
add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'institut-booking-notifications') return;
    if (isset($_POST['save_notifications'])) {
        update_option('ib_notify_client_confirm', wp_kses_post($_POST['notify_client_confirm']));
        update_option('ib_notify_client_cancel', wp_kses_post($_POST['notify_client_cancel']));
        update_option('ib_notify_admin_confirm', wp_kses_post($_POST['notify_admin_confirm']));
        update_option('ib_notify_admin_cancel', wp_kses_post($_POST['notify_admin_cancel']));
        update_option('ib_notify_recept_confirm', wp_kses_post($_POST['notify_recept_confirm']));
        update_option('ib_notify_recept_cancel', wp_kses_post($_POST['notify_recept_cancel']));
        update_option('ib_notify_reminder', wp_kses_post($_POST['notify_reminder']));
        wp_redirect(admin_url('admin.php?page=institut-booking-notifications&saved=1'));
        exit;
    }
});

// Traitement des actions notifications avancées (WordPress-compliant)
add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'institut-booking-notifications-advanced') return;
    if (isset($_POST['ib_save_notifications_advanced'])) {
        update_option('ib_push_enable', isset($_POST['ib_push_enable']) ? 1 : 0);
        update_option('ib_whatsapp_enable', isset($_POST['ib_whatsapp_enable']) ? 1 : 0);
        update_option('ib_whatsapp_token', sanitize_text_field($_POST['ib_whatsapp_token']));
        update_option('ib_reminder_enable', isset($_POST['ib_reminder_enable']) ? 1 : 0);
        update_option('ib_reminder_time', sanitize_text_field($_POST['ib_reminder_time']));
        wp_redirect(admin_url('admin.php?page=institut-booking-notifications-advanced&saved=1'));
        exit;
    }
});

// Traitement des actions SMS (WordPress-compliant)
add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'institut-booking-sms') return;
    if (isset($_POST['save_sms'])) {
        update_option('ib_twilio_sid', sanitize_text_field($_POST['twilio_sid']));
        update_option('ib_twilio_token', sanitize_text_field($_POST['twilio_token']));
        update_option('ib_twilio_from', sanitize_text_field($_POST['twilio_from']));
        wp_redirect(admin_url('admin.php?page=institut-booking-sms&saved=1'));
        exit;
    }
});

// Traitement des actions synchronisation calendrier (WordPress-compliant)
add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'institut-booking-calendar-sync') return;
    if (isset($_POST['ib_save_calendar_sync'])) {
        update_option('ib_gcal_client_id', sanitize_text_field($_POST['ib_gcal_client_id']));
        update_option('ib_gcal_client_secret', sanitize_text_field($_POST['ib_gcal_client_secret']));
        update_option('ib_outlook_client_id', sanitize_text_field($_POST['ib_outlook_client_id']));
        update_option('ib_outlook_client_secret', sanitize_text_field($_POST['ib_outlook_client_secret']));
        wp_redirect(admin_url('admin.php?page=institut-booking-calendar-sync&saved=1'));
        exit;
    }
});

// Traitement des actions feedback (WordPress-compliant)
add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'ib-feedback') return;
    if (isset($_GET['delete'])) {
        IB_Feedback::delete((int)$_GET['delete']);
        wp_redirect(admin_url('admin.php?page=ib-feedback&deleted=1'));
        exit;
    }
    if (isset($_GET['moderate'])) {
        IB_Feedback::moderate((int)$_GET['moderate'], $_GET['status']);
        wp_redirect(admin_url('admin.php?page=ib-feedback&moderated=1'));
        exit;
    }
});

// Traitement des actions réceptionniste (WordPress-compliant)
add_action('admin_init', function() {
    if (!isset($_GET['page']) || $_GET['page'] !== 'institut-booking-receptionist') return;
    if (isset($_POST['add_booking'])) {
        IB_Bookings::add([
            'service_id' => $_POST['service_id'],
            'employee_id' => $_POST['employee_id'],
            'client_name' => $_POST['client_name'],
            'client_email' => $_POST['client_email'],
            'date' => $_POST['date'],
            'time' => $_POST['time'],
        ]);
        wp_redirect(admin_url('admin.php?page=institut-booking-receptionist&added=1'));
        exit;
    }
    if (isset($_GET['delete'])) {
        IB_Bookings::delete($_GET['delete']);
        wp_redirect(admin_url('admin.php?page=institut-booking-receptionist&deleted=1'));
        exit;
    }
});

// Enqueue scripts and styles for the booking form on the frontend
function ib_enqueue_booking_form_assets() {
    // Only enqueue on pages where the shortcode is present (optional: optimize if needed)
    wp_enqueue_style('ib-frontend-style', IB_PLUGIN_URL . 'assets/css/admin-style.css', [], '1.0');

    // Plus besoin de CSS pour le sélecteur de téléphone - champ simple maintenant

    // DÉSACTIVÉ - Script admin qui cause des conflits avec les notifications
    // wp_enqueue_script('ib-frontend-script', IB_PLUGIN_URL . 'assets/js/admin-script.js', ['jquery'], time(), true);
    wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
    wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], null);

    // SCRIPT FIX PDF POUR LES TICKETS (frontend)
    wp_enqueue_script('ib-pdf-ticket-fix-frontend', IB_PLUGIN_URL . 'assets/js/pdf-ticket-fix.js', [], '1.0-' . time(), true);
    
    // Inject ajaxurl and nonce for frontend
    wp_localize_script('ib-frontend-script', 'ib_booking_form_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ib_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'ib_enqueue_booking_form_assets');

// ROUTER WEB APP
function institut_booking_fullpage() {
    // Autoriser administrateurs, réceptionnistes, praticiennes et employés
    $user = wp_get_current_user();
    $allowed_roles = ['administrator', 'receptionist', 'ib_employee', 'employee'];
    $has_access = false;
    foreach ($allowed_roles as $role) {
        if (in_array($role, (array) $user->roles)) {
            $has_access = true;
            break;
        }
    }
    // Vérifie aussi la capacité personnalisée (pour évolutivité)
    if (!$has_access && !current_user_can('ib_full_access')) {
        wp_die(__('Accès refusé. Vous devez être administrateur, réceptionniste, praticienne ou employé pour accéder à cette page.', 'institut-booking'));
    }

    // Masquer le menu admin WP et notifications
    echo '<style>
        #adminmenumain, #wpadminbar, #wpfooter, .update-nag, .notice, .error, .updated { display: none !important; }
        #wpcontent, #wpbody-content { margin-left: 0 !important; }
        body.wp-admin { background: #f4f6fb; }
    </style>';

    // Récupérer le slug de la page actuelle
    $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'institut-booking';
    $route = str_replace('institut-booking-', '', $current_page);
    if ($route === 'institut-booking') {
        $route = 'dashboard';
    }

    // Capture le contenu de la page dans un buffer
    ob_start();
    switch ($route) {
        case 'dashboard':
            include IB_PLUGIN_DIR . 'admin/page-dashboard.php';
            break;
        case 'services':
            include IB_PLUGIN_DIR . 'admin/page-services.php';
            break;
        case 'employees':
            include IB_PLUGIN_DIR . 'admin/page-employees.php';
            break;
        case 'bookings':
            include IB_PLUGIN_DIR . 'admin/page-bookings.php';
            break;
        case 'clients':
            include IB_PLUGIN_DIR . 'admin/page-clients.php';
            break;
        case 'extras':
            include IB_PLUGIN_DIR . 'admin/page-extras.php';
            break;
        case 'coupons':
            include IB_PLUGIN_DIR . 'admin/page-coupons.php';
            break;
        case 'categories':
            include IB_PLUGIN_DIR . 'admin/page-categories.php';
            break;
        case 'notifications':
            include IB_PLUGIN_DIR . 'admin/page-notifications.php';
            break;
        case 'notifications-advanced':
            include IB_PLUGIN_DIR . 'admin/page-notifications-advanced.php';
            break;
        case 'sms':
            include IB_PLUGIN_DIR . 'admin/page-sms.php';
            break;
        case 'calendar-sync':
            include IB_PLUGIN_DIR . 'admin/page-calendar-sync.php';
            break;
        case 'settings':
            echo "<!-- DEBUG: Route settings détectée -->";
            // Inclure le fichier et exécuter directement le contenu
            ob_start();
            include IB_PLUGIN_DIR . 'admin/page-settings.php';
            $settings_content = ob_get_clean();
            echo $settings_content;
            break;
        case 'calendar':
            include IB_PLUGIN_DIR . 'admin/page-calendar.php';
            break;
        case 'feedback':
            include IB_PLUGIN_DIR . 'admin/page-feedback.php';
            break;
        case 'analytics':
            include IB_PLUGIN_DIR . 'admin/page-analytics.php';
            break;
        case 'logs':
            include IB_PLUGIN_DIR . 'admin/page-logs.php';
            break;
        default:
            echo '<h2>Page introuvable</h2>';
    }
    $GLOBALS['ib_page_content'] = ob_get_clean();
    include IB_PLUGIN_DIR . 'admin/layout.php';
}


// Fin du fichier, ne rien ajouter après cette ligne pour éviter toute sortie parasite.


add_action('wp_ajax_add_booking', 'handle_add_booking');
add_action('wp_ajax_nopriv_add_booking', 'handle_add_booking');

function handle_add_booking() {
    check_ajax_referer('ib_nonce', 'nonce');
    $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
    $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
    $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
    $slot = isset($_POST['slot']) ? sanitize_text_field($_POST['slot']) : '';
    $firstname = isset($_POST['firstname']) ? sanitize_text_field($_POST['firstname']) : '';
    $lastname = isset($_POST['lastname']) ? sanitize_text_field($_POST['lastname']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    if (!$service_id || !$employee_id || !$date || !$slot || !$firstname || !$lastname || !$phone) {
        wp_send_json_error(['message' => 'Paramètres manquants']);
        return;
    }
    global $wpdb;
    $table = $wpdb->prefix . 'ib_bookings';
    $start_time = $date . ' ' . $slot . ':00';
    // Contrôle anti-doublon
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE service_id = %d AND employee_id = %d AND date = %s AND start_time = %s AND client_email = %s",
        $service_id, $employee_id, $date, $start_time, $email
    ));
    if ($exists > 0) {
        wp_send_json_error(['message' => 'Réservation déjà enregistrée pour ce créneau.']);
        return;
    }

    // VÉRIFICATION CRITIQUE : Contrôle des conflits de créneaux
    require_once plugin_dir_path(__FILE__) . '/includes/class-bookings.php';
    $conflict = IB_Bookings::has_conflict($employee_id, $date, $slot);
    if ($conflict) {
        wp_send_json_error(['message' => 'Ce créneau est déjà réservé pour cette praticienne. Veuillez choisir un autre créneau.']);
        return;
    }

    // Récupérer le prix du service
    $service = $wpdb->get_row($wpdb->prepare("SELECT price, name FROM {$wpdb->prefix}ib_services WHERE id = %d", $service_id));
    $service_price = $service ? $service->price : 0;
    // Chercher ou créer le client
    $client = $wpdb->get_row($wpdb->prepare("SELECT id FROM {$wpdb->prefix}ib_clients WHERE email = %s", $email));
    if (!$client) {
        $wpdb->insert("{$wpdb->prefix}ib_clients", [
            'name' => $firstname . ' ' . $lastname,
            'email' => $email,
            'phone' => $phone,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);
        $client_id = $wpdb->insert_id;
    } else {
        $client_id = $client->id;
    }
    $wpdb->insert($table, [
        'service_id' => $service_id,
        'employee_id' => $employee_id,
        'client_id' => $client_id,
        'date' => $date,
        'start_time' => $start_time,
        'client_name' => $firstname . ' ' . $lastname,
        'client_email' => $email,
        'client_phone' => $phone,
        'created_at' => current_time('mysql'),
        'price' => $service_price,
    ]);
    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'Erreur lors de l\'enregistrement : ' . $wpdb->last_error]);
        return;
    }
    // Notification admin et envoi d'email
    if ($wpdb->insert_id) {
        $booking_id = $wpdb->insert_id;
        $employee = $wpdb->get_row($wpdb->prepare("SELECT name FROM {$wpdb->prefix}ib_employees WHERE id = %d", $employee_id));

        // Create notification for admin (using 'admin' string as target)
        $msg = $firstname . ' ' . $lastname . ' a réservé ' . ($service ? $service->name : '') . ' le ' . $date . ' (' . ($employee ? $employee->name : '') . ')';
        // Lien vers la liste complète des réservations sur le site de production
        $link = 'https://linstitutbykm.com/wp-admin/admin.php?page=institut-booking-bookings';
        if (function_exists('ib_add_notification')) {
            ib_add_notification('reservation', $msg, 'admin', $link, 'unread');
        }

        // ENVOI EMAIL DE REMERCIEMENT UNIQUEMENT (Thank You)
        // L'email de confirmation sera envoyé uniquement quand la réservation sera validée dans le back office
        
        // ENVOI EMAIL DE REMERCIEMENT (Thank You)
        require_once plugin_dir_path(__FILE__) . '/includes/notifications.php';
        IB_Notifications::send_thank_you($booking_id);
    }
    wp_send_json_success(['message' => 'Réservation enregistrée !', 'booking_id' => $wpdb->insert_id]);
}


// === Endpoints AJAX pour la cloche de notifications premium (scroll infini, recherche, suppression, tout marquer comme lu) ===
add_action('wp_ajax_ib_get_notifications', 'ib_get_notifications');
function ib_get_notifications() {
    // Remove nonce check for now to allow both nonce types
    check_ajax_referer('ib_notif_bell', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    $user_id = get_current_user_id();
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $limit = isset($_POST['limit']) ? max(1, intval($_POST['limit'])) : 10;
    $offset = ($page - 1) * $limit;
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    
    // Debug: Log the table name and user ID
    error_log('[IB Notifications] Table: ' . $table . ', User ID: ' . $user_id);
    
    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
    if (!$table_exists) {
        error_log('[IB Notifications] Table does not exist: ' . $table);
        wp_send_json_success([
            'recent' => [],
            'unread_count' => 0
        ]);
        return;
    }
    
    // Only target admin notifications
    if ($query) {
        $sql = "SELECT * FROM $table WHERE target = %s AND (message LIKE %s) ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $rows = $wpdb->get_results($wpdb->prepare($sql, 'admin', '%' . $wpdb->esc_like($query) . '%', $limit, $offset));
    } else {
        $sql = "SELECT * FROM $table WHERE target = %s ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $rows = $wpdb->get_results($wpdb->prepare($sql, 'admin', $limit, $offset));
    }
    
    // Debug: Log the SQL query and results count
    error_log('[IB Notifications] SQL: ' . $wpdb->last_query);
    error_log('[IB Notifications] Found ' . count($rows) . ' notifications');
    
    // Get unread count
    $unread_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE target = %s AND status = 'unread'",
        'admin'
    ));
    
    $data = [];
    foreach ($rows as $row) {
        $data[] = [
            'id'      => $row->id,
            'type'    => $row->type,
            'message' => $row->message,
            'status'  => $row->status,
            'created_at' => $row->created_at,
            'date'    => date_i18n('d/m/Y H:i', strtotime($row->created_at)),
            'link'    => $row->link,
            'avatar'  => '', // à personnaliser si besoin
        ];
    }
    
    error_log('[IB Notifications] Returning data: ' . json_encode($data));
    
    wp_send_json_success([
        'recent' => $data,
        'unread_count' => intval($unread_count)
    ]);
}

add_action('wp_ajax_ib_mark_all_notifications_read', 'ib_mark_all_notifications_read');
function ib_mark_all_notifications_read() {
    // Remove nonce check for now
    // check_ajax_referer('ib_notif_bell', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    $user_id = get_current_user_id();
    // Update only admin notifications
    $wpdb->query($wpdb->prepare(
        "UPDATE $table SET status = 'read' WHERE target = %s AND status = 'unread'",
        'admin'
    ));
    wp_send_json_success();
}

add_action('wp_ajax_ib_mark_notification_read', 'ib_mark_notification_read');
function ib_mark_notification_read() {
    // Remove nonce check for now
    // check_ajax_referer('ib_notif_bell', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    $user_id = get_current_user_id();
    $notif_id = intval($_POST['id']);
    // Update only admin notifications
    $wpdb->query($wpdb->prepare(
        "UPDATE $table SET status = 'read' WHERE id = %d AND target = %s",
        $notif_id, 'admin'
    ));
    wp_send_json_success();
}

add_action('wp_ajax_ib_delete_notification', 'ib_delete_notification');
function ib_delete_notification() {
    // Remove nonce check for now
    // check_ajax_referer('ib_notif_bell', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    $user_id = get_current_user_id();
    $notif_id = intval($_POST['id']);
    // Delete only admin notifications
    $wpdb->query($wpdb->prepare(
        "DELETE FROM $table WHERE id = %d AND target = %s",
        $notif_id, 'admin'
    ));
    wp_send_json_success();
}

// DÉSACTIVÉ - Cause des conflits avec le script final
// add_action('admin_enqueue_scripts', function($hook) {
//     wp_enqueue_script(
//         'ib-admin-script',
//         IB_PLUGIN_URL . 'assets/js/admin-script.js',
//         array(),
//         '1.0',
//         true
//     );
//
//     wp_localize_script('ib-admin-script', 'ib_admin_vars', array(
//         'ajaxurl' => admin_url('admin-ajax.php')
//     ));
//
//     wp_localize_script('ib-admin-script', 'IBNotifBell', array(
//         'ajaxurl' => admin_url('admin-ajax.php'),
//         'nonce' => wp_create_nonce('ib_notif_bell')
//     ));
// });
