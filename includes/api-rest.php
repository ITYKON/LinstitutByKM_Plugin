<?php
error_log('[IB_DEBUG] api-rest.php chargé');
// API RESTful de base pour le plugin
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function() {
    register_rest_route('institut-booking/v1', '/bookings', [
        'methods' => 'GET',
        'callback' => function($request) {
            if (!current_user_can('manage_options')) return new WP_Error('forbidden', 'Accès refusé', ['status' => 403]);
            require_once plugin_dir_path(__FILE__) . '/class-bookings.php';
            $bookings = IB_Bookings::get_all();
            return rest_ensure_response($bookings);
        },
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('institut-booking/v1', '/calendar-events', [
        'methods' => 'GET',
        'callback' => function($request) {
            global $wpdb;
            $bookings = $wpdb->get_results("SELECT b.*, s.name as service_name, s.duration as service_duration, e.name as employee_name, e.id as employee_id FROM {$wpdb->prefix}ib_bookings b LEFT JOIN {$wpdb->prefix}ib_services s ON b.service_id = s.id LEFT JOIN {$wpdb->prefix}ib_employees e ON b.employee_id = e.id", ARRAY_A);
            // Palette pastel par employé (exemple)
            $employee_colors = [
                1 => '#F8BBD0', // Sophie
                2 => '#B2DFDB', // Emma
                3 => '#C5CAE9', // Clara
                4 => '#FFE0B2', // Julie
                5 => '#D1C4E9', // Autre
            ];
            $events = array_map(function($row) use ($employee_colors) {
                $color = isset($employee_colors[$row['employee_id']]) ? $employee_colors[$row['employee_id']] : '#F8BBD0';
                return [
                    'id' => $row['id'],
                    'title' => $row['service_name'],
                    'employee' => $row['employee_name'],
                    'client' => $row['client_name'],
                    'start' => $row['start_time'],
                    'color' => $color,
                    'duration' => $row['service_duration'] ?? 60
                ];
            }, $bookings);
            return rest_ensure_response($events);
        },
        'permission_callback' => '__return_true', // public
    ]);
    register_rest_route('institut-booking/v1', '/calendar-filters', [
        'methods' => 'GET',
        'callback' => function($request) {
            require_once plugin_dir_path(__FILE__) . '/class-employees.php';
            require_once plugin_dir_path(__FILE__) . '/class-services.php';
            require_once plugin_dir_path(__FILE__) . '/class-categories.php';
            $employees = array_map(function($e) {
                return [
                    'id' => $e->id,
                    'name' => $e->name,
                    'color' => null // Optionnel, à calculer côté JS si besoin
                ];
            }, IB_Employees::get_all());
            $services = array_map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'category_id' => isset($s->category_id) ? $s->category_id : null,
                    'category_name' => isset($s->category_name) ? $s->category_name : null
                ];
            }, IB_Services::get_all());
            $categories = array_map(function($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name
                ];
            }, IB_Categories::get_all());
            return rest_ensure_response([
                'employees' => $employees,
                'services' => $services,
                'categories' => $categories
            ]);
        },
        'permission_callback' => '__return_true',
    ]);
    // Ajoute d'autres endpoints (clients, services, etc.)
});

add_action('wp_ajax_ib_get_slots', 'ib_get_slots');
add_action('wp_ajax_nopriv_ib_get_slots', 'ib_get_slots');
function ib_get_slots() {
    file_put_contents(__DIR__.'/debug_ajax.txt', print_r($_POST, true));
    $employee_id = intval($_POST['employee_id']);
    $service_id = intval($_POST['service_id']);
    $date = sanitize_text_field($_POST['date']);

    // --- LOGIQUE RÉELLE DE DISPONIBILITÉ ---
    // Exemple : horaires de travail de l'employé (à remplacer par ta logique)
    $work_hours = ['09:00','10:00','11:00','14:00','15:00','16:00'];

    // Récupère les réservations existantes pour cet employé, ce service, ce jour
    global $wpdb;
    $booked = $wpdb->get_col($wpdb->prepare(
        "SELECT time FROM {$wpdb->prefix}ib_bookings WHERE employee_id=%d AND service_id=%d AND date=%s",
        $employee_id, $service_id, $date
    ));
    // Filtre les créneaux déjà réservés
    $available = array_values(array_diff($work_hours, $booked));

    wp_send_json_success($available);
    wp_die();
}

// Note: Notification handlers are now in the main plugin file

// Endpoint AJAX pour vérifier les conflits de créneau (ajout réservation admin)
add_action('wp_ajax_ib_check_booking_conflict', function() {
    error_log('[IB_DEBUG] POST=' . print_r($_POST, true)); // LOG DEBUG
    // Suppression de la vérification des droits pour debug universel
    $service_id = intval($_POST['service_id'] ?? 0);
    $employee_id = intval($_POST['employee_id'] ?? 0);
    $date = sanitize_text_field($_POST['date'] ?? '');
    $time = sanitize_text_field($_POST['time'] ?? '');
    error_log('[IB_DEBUG] Vérif conflit : service_id=' . $service_id . ', employee_id=' . $employee_id . ', date=' . $date . ', time=' . $time);
    if (!$service_id || !$employee_id || !$date || !$time) {
        error_log('[IB_DEBUG] Paramètres manquants');
        wp_send_json(['success' => false, 'conflict' => false, 'message' => 'Paramètres manquants']);
    }
    require_once plugin_dir_path(__FILE__) . '/class-bookings.php';
    $conflict = IB_Bookings::has_conflict($employee_id, $date, $time);
    error_log('[IB_DEBUG] Résultat has_conflict=' . ($conflict ? 'OUI' : 'NON'));
    wp_send_json(['success' => true, 'conflict' => $conflict]);
});

// L'ancienne détection/correction des conflits via l'UI a été retirée.

// Les endpoints de correction en masse/individuelle des conflits ont été retirés.

add_action('wp_ajax_get_available_days', 'ib_get_available_days');
add_action('wp_ajax_nopriv_get_available_days', 'ib_get_available_days');
function ib_get_available_days() {
    // Debug
    error_log('🔍 ib_get_available_days appelée avec POST: ' . print_r($_POST, true));

    $employee_id = intval($_POST['employee_id']);
    $service_id = intval($_POST['service_id']);
    $year = intval($_POST['year']);
    $month = intval($_POST['month']); // 1-12

    error_log("🔍 Paramètres: employee_id=$employee_id, service_id=$service_id, year=$year, month=$month");

    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $result = [];

    for ($d = 1; $d <= $days_in_month; $d++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $slots = IB_Availability::get_available_slots($employee_id, $service_id, $date);

        error_log("🔍 Date $date: " . count($slots) . " créneaux trouvés");

        if (!empty($slots)) {
            $result[$date] = true;
        }
    }

    error_log('✅ Résultat final: ' . print_r($result, true));
    wp_send_json_success($result);
}
