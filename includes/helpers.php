<?php
// Fonctions utilitaires communes
if (!defined('ABSPATH')) exit;

/**
 * Fonctions utilitaires globales
 */

/**
 * Vérifie si l'utilisateur actuel est une praticienne
 */
function ib_is_employee() {
    if (!function_exists('wp_get_current_user')) {
        require_once(ABSPATH . 'wp-includes/pluggable.php');
    }
    $user = wp_get_current_user();
    return in_array('ib_employee', (array) $user->roles);
}

/**
 * Vérifie si l'utilisateur actuel est une réceptionniste
 */
function ib_is_receptionist() {
    if (!function_exists('wp_get_current_user')) {
        require_once(ABSPATH . 'wp-includes/pluggable.php');
    }
    $user = wp_get_current_user();
    return in_array('receptionist', (array) $user->roles);
}

/**
 * Formate un prix
 */
function ib_format_price($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

/**
 * Formate une durée en minutes en format lisible
 */
function ib_format_duration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($hours > 0) {
        return sprintf(_n('%d heure', '%d heures', $hours, 'institut-booking'), $hours) . 
               ($mins > 0 ? ' ' . sprintf(_n('%d minute', '%d minutes', $mins, 'institut-booking'), $mins) : '');
    }
    
    return sprintf(_n('%d minute', '%d minutes', $mins, 'institut-booking'), $mins);
}

/**
 * Vérifie si une date est disponible pour un service et un employé
 */
function ib_is_date_available($date, $service_id, $employee_id) {
    global $wpdb;
    
    $service = IB_Services::get_by_id($service_id);
    if (!$service) return false;
    
    $start_time = strtotime($date);
    $end_time = $start_time + ($service->duration * 60);
    
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ib_bookings 
        WHERE employee_id = %d 
        AND date = %s 
        AND status != 'cancelled'
        AND (
            (start_time <= %s AND end_time > %s)
            OR (start_time < %s AND end_time >= %s)
            OR (start_time >= %s AND end_time <= %s)
        )",
        $employee_id,
        date('Y-m-d', $start_time),
        date('Y-m-d H:i:s', $start_time),
        date('Y-m-d H:i:s', $start_time),
        date('Y-m-d H:i:s', $end_time),
        date('Y-m-d H:i:s', $end_time),
        date('Y-m-d H:i:s', $start_time),
        date('Y-m-d H:i:s', $end_time)
    ));
    
    return empty($bookings);
}

/**
 * Génère un token unique
 */
function ib_generate_token() {
    return wp_generate_password(32, false);
}

/**
 * Vérifie si un token est valide
 */
function ib_verify_token($token) {
    global $wpdb;
    
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}ib_tokens WHERE token = %s AND expires > NOW()",
        $token
    ));
    
    return $result > 0;
}

/**
 * Nettoie un numéro de téléphone
 */
function ib_clean_phone($phone) {
    return preg_replace('/[^0-9+]/', '', $phone);
}

/**
 * Vérifie si un numéro de téléphone est valide
 */
function ib_is_valid_phone($phone) {
    return preg_match('/^\+?[0-9]{10,15}$/', $phone);
}

/**
 * Vérifie si une adresse email est valide
 */
function ib_is_valid_email($email) {
    return is_email($email);
}

/**
 * Log une action
 */
function ib_log($action, $context = []) {
    if (!function_exists('wp_get_current_user')) {
        require_once(ABSPATH . 'wp-includes/pluggable.php');
    }
    
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $user_name = $user->display_name;
    
    IB_Logs::add($user_id, $action, json_encode($context));
}

// Fonctions utilitaires communes
function ib_format_date($date) {
    return date('d/m/Y', strtotime($date));
}

// AJAX : données analytics dynamiques
add_action('wp_ajax_ib_get_analytics_data', 'ib_get_analytics_data');
function ib_get_analytics_data() {
    global $wpdb;
    $months = [];
    $bookings = [];
    $revenues = [];
    for ($i = 1; $i <= 12; $i++) {
        $month = sprintf('%02d', $i);
        $months[] = date_i18n('M', mktime(0,0,0,$i,1));
        $bookings[] = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}ib_bookings WHERE MONTH(date) = %d", $i));
        $revenues[] = (float)$wpdb->get_var($wpdb->prepare("SELECT SUM(price) FROM {$wpdb->prefix}ib_bookings WHERE status = 'confirmed' AND MONTH(date) = %d", $i));
    }
    // Top services
    $top_services = $wpdb->get_results("SELECT s.name, COUNT(b.id) as total FROM {$wpdb->prefix}ib_bookings b JOIN {$wpdb->prefix}ib_services s ON b.service_id = s.id GROUP BY s.id ORDER BY total DESC LIMIT 5");
    // Top employés
    $top_employees = $wpdb->get_results("SELECT e.name, COUNT(b.id) as total FROM {$wpdb->prefix}ib_bookings b JOIN {$wpdb->prefix}ib_employees e ON b.employee_id = e.id GROUP BY e.id ORDER BY total DESC LIMIT 5");
    wp_send_json([
        'months' => $months,
        'bookings' => $bookings,
        'revenues' => $revenues,
        'top_services' => $top_services,
        'top_employees' => $top_employees
    ]);
}

// AJAX pour récupérer les extras d'un service
add_action('wp_ajax_ib_get_extras', 'ib_ajax_get_extras');
add_action('wp_ajax_nopriv_ib_get_extras', 'ib_ajax_get_extras');
function ib_ajax_get_extras() {
    require_once plugin_dir_path(__FILE__) . '/class-extras.php';
    $service_id = intval($_GET['service_id']);
    $extras = IB_Extras::get_by_service($service_id);
    wp_send_json($extras);
}
// AJAX : Créneaux horaires disponibles pour un service/employé/date
add_action('wp_ajax_ib_get_time_slots', 'ib_ajax_get_time_slots');
add_action('wp_ajax_nopriv_ib_get_time_slots', 'ib_ajax_get_time_slots');
function ib_ajax_get_time_slots() {
    // Fix: Use $_POST instead of $_GET to match the JavaScript request
    $service_id = intval($_POST['service_id'] ?? 0);
    $employee_id = intval($_POST['employee_id'] ?? 0);
    $date = sanitize_text_field($_POST['date'] ?? '');
    error_log('[IB_DEBUG] Params reçus : service_id=' . $service_id . ', employee_id=' . $employee_id . ', date=' . $date);
    
    if (!$service_id || !$employee_id || !$date) {
        error_log('[IB_DEBUG] Params manquants, réponse vide');
        wp_send_json_error(['message' => 'Paramètres manquants']);
        return;
    }
    
    // Load required classes
    require_once plugin_dir_path(__FILE__) . '/class-services.php';
    require_once plugin_dir_path(__FILE__) . '/class-bookings.php';
    
    $service = IB_Services::get_by_id($service_id);
    if (!$service) {
        error_log('[IB_DEBUG] Service non trouvé, réponse vide');
        wp_send_json_error(['message' => 'Service non trouvé']);
        return;
    }
    // Plage horaire d'ouverture configurable (back-office)
    $opening = get_option('ib_opening_time', '09:00');
    $closing = get_option('ib_closing_time', '17:00');
    $duration = intval($service->duration) > 0 ? intval($service->duration) : 30; // durée du service en minutes
    
    $start = strtotime($date . ' ' . $opening);
    $end = strtotime($date . ' ' . $closing);
    
    if (!$start || !$end) {
        error_log('[IB_DEBUG] Erreur de format de date');
        wp_send_json_error(['message' => 'Erreur de format de date']);
        return;
    }
    
    $slots = [];
    for ($t = $start; $t <= $end - $duration * 60; $t += $duration * 60) {
        $slot_time = date('H:i', $t);
        // Vérifier si le créneau est déjà réservé
        $conflict = IB_Bookings::has_conflict($employee_id, $date, $slot_time);
        if (!$conflict) { // Only return available slots
            $slots[] = $slot_time;
        }
    }
    
    error_log('[IB_DEBUG] Créneaux disponibles générés : ' . json_encode($slots));
    wp_send_json_success($slots);
}

if (!function_exists('institut_booking_get_employees')) {
    function institut_booking_get_employees() {
        if (!class_exists('IB_Employees')) {
            require_once plugin_dir_path(__FILE__) . '/class-employees.php';
        }
        return IB_Employees::get_all();
    }
}

// AJAX : suppression d'un feedback
add_action('wp_ajax_ib_delete_feedback', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Accès refusé']);
    }
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        wp_send_json_error(['message' => 'ID manquant']);
    }
    require_once plugin_dir_path(__FILE__) . '/class-feedback.php';
    IB_Feedback::delete($id);
    wp_send_json_success();
});

// AJAX : envoi de message SMS/WhatsApp
add_action('wp_ajax_ib_send_message', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Accès refusé']);
    }
    $client_id = intval($_POST['client_id'] ?? 0);
    $type = sanitize_text_field($_POST['type'] ?? 'sms');
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    
    if (!$client_id || !$message) {
        wp_send_json_error(['message' => 'Données manquantes']);
    }
    
    require_once plugin_dir_path(__FILE__) . '/class-clients.php';
    $client = IB_Clients::get_by_id($client_id);
    if (!$client) {
        wp_send_json_error(['message' => 'Client non trouvé']);
    }
    
    // Récupérer la configuration Twilio
    $twilio_sid = get_option('ib_twilio_sid');
    $twilio_token = get_option('ib_twilio_token');
    $twilio_from = get_option('ib_twilio_from');
    
    if (!$twilio_sid || !$twilio_token || !$twilio_from) {
        wp_send_json_error(['message' => 'Configuration Twilio manquante. Veuillez configurer Twilio dans les paramètres SMS.']);
    }
    
    // Nettoyer le numéro de téléphone
    $phone = ib_clean_phone($client->phone);
    if (!ib_is_valid_phone($phone)) {
        wp_send_json_error(['message' => 'Numéro de téléphone invalide pour le client.']);
    }
    
    $result = false;
    $error_message = '';
    
    try {
        if ($type === 'whatsapp') {
            // Vérifier si on utilise le mode gratuit
            $use_free = get_option('ib_whatsapp_free_mode', false);
            
            if ($use_free) {
                // Mode gratuit : WhatsApp Web ou API gratuite
                $result = send_whatsapp_free($phone, $message);
                if (!$result['success']) {
                    // Essayer l'API gratuite en fallback
                    $result = send_whatsapp_api_free($phone, $message);
                }
            } else {
                // Mode payant : Twilio
                if (!$twilio_sid || !$twilio_token || !$twilio_from) {
                    wp_send_json_error(['message' => 'Configuration Twilio manquante. Activez le mode gratuit ou configurez Twilio.']);
                }
                $result = send_whatsapp_twilio($phone, $message, $twilio_sid, $twilio_token, $twilio_from);
            }
        } else {
            // Envoi SMS via Twilio
            if (!$twilio_sid || !$twilio_token || !$twilio_from) {
                wp_send_json_error(['message' => 'Configuration Twilio manquante pour les SMS.']);
            }
            $result = send_sms_twilio($phone, $message, $twilio_sid, $twilio_token, $twilio_from);
        }
        
        if ($result === true || (is_array($result) && $result['success'])) {
            // Log de l'action
            ib_log('message_sent', [
                'client_id' => $client_id,
                'client_name' => $client->name,
                'type' => $type,
                'message' => substr($message, 0, 100),
                'phone' => $phone,
                'method' => $use_free ? 'free' : 'twilio'
            ]);
            wp_send_json_success(['message' => 'Message ' . $type . ' envoyé avec succès !']);
        } else {
            $error_msg = is_array($result) ? $result['error'] : 'Erreur lors de l\'envoi';
            wp_send_json_error(['message' => 'Erreur lors de l\'envoi du message ' . $type . ' : ' . $error_msg]);
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Erreur : ' . $e->getMessage()]);
    }
});

// Fonction pour envoyer WhatsApp via Twilio
function send_whatsapp_twilio($to, $message, $sid, $token, $from) {
    // Format WhatsApp : whatsapp:+NUMERO
    $whatsapp_to = 'whatsapp:' . $to;
    $whatsapp_from = 'whatsapp:' . $from;
    
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
    
    $data = [
        'To' => $whatsapp_to,
        'From' => $whatsapp_from,
        'Body' => $message
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 201) {
        $result = json_decode($response, true);
        return isset($result['sid']);
    }
    
    return false;
}

// Fonction pour envoyer SMS via Twilio
function send_sms_twilio($to, $message, $sid, $token, $from) {
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
    
    $data = [
        'To' => $to,
        'From' => $from,
        'Body' => $message
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 201) {
        $result = json_decode($response, true);
        return isset($result['sid']);
    }
    
    return false;
}

// AJAX : sauvegarde configuration SMS
add_action('wp_ajax_ib_save_sms_config', function() {
    check_ajax_referer('ib_save_sms_config', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permissions insuffisantes']);
    }
    
    try {
        // Configuration SMS
        $sms_enabled = isset($_POST['sms_enabled']) ? sanitize_text_field($_POST['sms_enabled']) : '0';
        $sms_api_key = isset($_POST['sms_api_key']) ? sanitize_text_field($_POST['sms_api_key']) : '';
        $sms_sender = isset($_POST['sms_sender']) ? sanitize_text_field($_POST['sms_sender']) : '';
        
        update_option('ib_sms_enabled', $sms_enabled);
        update_option('ib_sms_api_key', $sms_api_key);
        update_option('ib_sms_sender', $sms_sender);
        
        // Configuration WhatsApp
        $whatsapp_free_mode = isset($_POST['whatsapp_free_mode']) ? sanitize_text_field($_POST['whatsapp_free_mode']) : '0';
        update_option('ib_whatsapp_free_mode', $whatsapp_free_mode);
        
        if ($whatsapp_free_mode === '0') {
            // Mode Twilio
            $twilio_sid = isset($_POST['twilio_sid']) ? sanitize_text_field($_POST['twilio_sid']) : '';
            $twilio_token = isset($_POST['twilio_token']) ? sanitize_text_field($_POST['twilio_token']) : '';
            $twilio_whatsapp_from = isset($_POST['twilio_whatsapp_from']) ? sanitize_text_field($_POST['twilio_whatsapp_from']) : '';
            
            update_option('ib_twilio_sid', $twilio_sid);
            update_option('ib_twilio_token', $twilio_token);
            update_option('ib_twilio_whatsapp_from', $twilio_whatsapp_from);
        }
        
        wp_send_json_success(['message' => 'Configuration sauvegardée avec succès']);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()]);
    }
});

// AJAX : export logs CSV
add_action('wp_ajax_ib_export_logs', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé');
    }
    
    require_once plugin_dir_path(__FILE__) . '/class-logs.php';
    $logs = IB_Logs::get_all();
    
    // Générer CSV
    $filename = 'logs_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    
    // En-têtes
    fputcsv($output, ['Utilisateur', 'Action', 'Contexte', 'IP', 'Date']);
    
    // Données
    foreach($logs as $log) {
        fputcsv($output, [
            $log->user_name,
            $log->action,
            $log->context,
            $log->ip,
            $log->created_at
        ]);
    }
    
    fclose($output);
    exit;
});

// AJAX : test de configuration Twilio
add_action('wp_ajax_ib_test_twilio_config', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Accès refusé']);
    }
    
    $twilio_sid = get_option('ib_twilio_sid');
    $twilio_token = get_option('ib_twilio_token');
    $twilio_from = get_option('ib_twilio_from');
    
    if (!$twilio_sid || !$twilio_token || !$twilio_from) {
        wp_send_json_error(['message' => 'Configuration Twilio incomplète. Veuillez remplir tous les champs.']);
    }
    
    // Test simple de l'API Twilio (récupération des informations du compte)
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$twilio_sid}.json";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$twilio_sid}:{$twilio_token}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $result = json_decode($response, true);
        if (isset($result['status']) && $result['status'] === 'active') {
            wp_send_json_success(['message' => 'Configuration Twilio valide ! Compte actif.']);
        } else {
            wp_send_json_error(['message' => 'Compte Twilio inactif ou suspendu.']);
        }
    } else {
        wp_send_json_error(['message' => 'Erreur de connexion à Twilio. Vérifiez vos identifiants.']);
    }
});

// Fonction pour envoyer WhatsApp gratuit via WhatsApp Web
function send_whatsapp_free($to, $message) {
    // Cette fonction nécessite Selenium WebDriver
    // Installation : composer require php-webdriver/webdriver
    
    try {
        // Vérifier si Selenium est disponible
        if (!class_exists('Facebook\WebDriver\Remote\RemoteWebDriver')) {
            return ['success' => false, 'error' => 'Selenium WebDriver non installé'];
        }
        
        // Configuration du navigateur
        $host = 'http://localhost:4444/wd/hub'; // Selenium Server
        $capabilities = Facebook\WebDriver\Remote\DesiredCapabilities::chrome();
        $driver = Facebook\WebDriver\Remote\RemoteWebDriver::create($host, $capabilities);
        
        // Ouvrir WhatsApp Web
        $driver->get('https://web.whatsapp.com/');
        
        // Attendre que WhatsApp Web soit chargé
        $driver->wait(30)->until(
            Facebook\WebDriver\WebDriverExpectedCondition::presenceOfElementLocated(
                Facebook\WebDriver\WebDriverBy::cssSelector('div[data-testid="chat-list"]')
            )
        );
        
        // Construire l'URL WhatsApp avec le message
        $encoded_message = urlencode($message);
        $whatsapp_url = "https://wa.me/{$to}?text={$encoded_message}";
        
        // Ouvrir le chat
        $driver->get($whatsapp_url);
        
        // Attendre et cliquer sur "Continuer vers WhatsApp"
        $driver->wait(10)->until(
            Facebook\WebDriver\WebDriverExpectedCondition::elementToBeClickable(
                Facebook\WebDriver\WebDriverBy::cssSelector('a[data-testid="action-button"]')
            )
        )->click();
        
        // Attendre que WhatsApp Web s'ouvre
        sleep(3);
        
        // Cliquer sur "Envoyer"
        $send_button = $driver->findElement(
            Facebook\WebDriver\WebDriverBy::cssSelector('span[data-testid="send"]')
        );
        $send_button->click();
        
        $driver->quit();
        return ['success' => true, 'message' => 'Message envoyé via WhatsApp Web'];
        
    } catch (Exception $e) {
        if (isset($driver)) {
            $driver->quit();
        }
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Fonction pour envoyer WhatsApp via API gratuite (alternative)
function send_whatsapp_api_free($to, $message) {
    // Utilisation d'APIs gratuites alternatives
    $apis = [
        'https://api.whatsapp.com/send',
        'https://wa.me/api/send',
        'https://whatsapp-api.free.beeceptor.com/send'
    ];
    
    foreach ($apis as $api_url) {
        try {
            $data = [
                'phone' => $to,
                'message' => $message,
                'type' => 'text'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if (isset($result['success']) && $result['success']) {
                    return ['success' => true, 'message' => 'Message envoyé via API gratuite'];
                }
            }
        } catch (Exception $e) {
            continue; // Essayer l'API suivante
        }
    }
    
    return ['success' => false, 'error' => 'Aucune API gratuite disponible'];
}

// Handler AJAX pour l'installation de Selenium
add_action('wp_ajax_ib_install_selenium', function() {
    check_ajax_referer('ib_install_selenium', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permissions insuffisantes']);
    }
    
    try {
        // Vérifier si Composer est disponible
        $composer_path = exec('which composer');
        if (!$composer_path) {
            wp_send_json_error(['message' => 'Composer n\'est pas installé sur le serveur']);
        }
        
        // Installer Selenium WebDriver via Composer
        $plugin_dir = plugin_dir_path(__FILE__);
        $output = shell_exec("cd {$plugin_dir} && composer require php-webdriver/webdriver 2>&1");
        
        if (strpos($output, 'error') !== false || strpos($output, 'failed') !== false) {
            wp_send_json_error(['message' => 'Erreur lors de l\'installation : ' . $output]);
        }
        
        // Vérifier l'installation
        if (class_exists('Facebook\WebDriver\Remote\RemoteWebDriver')) {
            wp_send_json_success(['message' => 'Selenium WebDriver installé avec succès']);
        } else {
            wp_send_json_error(['message' => 'Installation terminée mais classe non trouvée']);
        }
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Erreur : ' . $e->getMessage()]);
    }
});
