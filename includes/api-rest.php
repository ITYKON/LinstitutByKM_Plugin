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

// Endpoint AJAX pour détecter les conflits de réservations
add_action('wp_ajax_ib_detect_booking_conflicts', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes', 403);
    }
    
    if (!wp_verify_nonce($_POST['nonce'], 'ib_detect_conflicts')) {
        wp_send_json_error('Nonce invalide', 403);
    }
    
    global $wpdb;
    $table_bookings = $wpdb->prefix . 'ib_bookings';
    $table_services = $wpdb->prefix . 'ib_services';
    $table_employees = $wpdb->prefix . 'ib_employees';
    
    // Requête pour détecter les conflits
    $conflicts_query = "
        SELECT 
            b1.id as booking1_id,
            b1.client_name as client1_name,
            b1.start_time as start1,
            b1.end_time as end1,
            b1.employee_id as employee1_id,
            b1.service_id as service1_id,
            b1.status as status1,
            b2.id as booking2_id,
            b2.client_name as client2_name,
            b2.start_time as start2,
            b2.end_time as end2,
            b2.employee_id as employee2_id,
            b2.service_id as service2_id,
            b2.status as status2,
            s1.name as service1_name,
            s2.name as service2_name,
            e1.name as employee1_name,
            e2.name as employee2_name
        FROM {$table_bookings} b1
        INNER JOIN {$table_bookings} b2 ON (
            b1.id < b2.id 
            AND b1.employee_id = b2.employee_id
            AND b1.status != 'cancelled'
            AND b2.status != 'cancelled'
            AND (
                (b1.start_time < b2.end_time AND b1.end_time > b2.start_time)
                OR (b2.start_time < b1.end_time AND b2.end_time > b1.start_time)
            )
        )
        LEFT JOIN {$table_services} s1 ON b1.service_id = s1.id
        LEFT JOIN {$table_services} s2 ON b2.service_id = s2.id
        LEFT JOIN {$table_employees} e1 ON b1.employee_id = e1.id
        LEFT JOIN {$table_employees} e2 ON b2.employee_id = e2.id
        ORDER BY b1.start_time ASC, b2.start_time ASC
    ";
    
    $conflicts = $wpdb->get_results($conflicts_query);
    
    // Formater les données pour l'affichage
    $formatted_conflicts = [];
    foreach ($conflicts as $conflict) {
        $formatted_conflicts[] = [
            'booking1_id' => $conflict->booking1_id,
            'client1_name' => $conflict->client1_name,
            'start1' => date('d/m/Y H:i', strtotime($conflict->start1)),
            'end1' => date('d/m/Y H:i', strtotime($conflict->end1)),
            'service1_name' => $conflict->service1_name ?: 'Service #' . $conflict->service1_id,
            'employee1_name' => $conflict->employee1_name ?: 'Employé #' . $conflict->employee1_id,
            'status1' => $conflict->status1,
            'booking2_id' => $conflict->booking2_id,
            'client2_name' => $conflict->client2_name,
            'start2' => date('d/m/Y H:i', strtotime($conflict->start2)),
            'end2' => date('d/m/Y H:i', strtotime($conflict->end2)),
            'service2_name' => $conflict->service2_name ?: 'Service #' . $conflict->service2_id,
            'employee2_name' => $conflict->employee2_name ?: 'Employé #' . $conflict->employee2_id,
            'status2' => $conflict->status2
        ];
    }
    
    wp_send_json_success($formatted_conflicts);
});

// Endpoint AJAX pour corriger tous les conflits
add_action('wp_ajax_ib_fix_all_conflicts', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes', 403);
    }
    
    if (!wp_verify_nonce($_POST['nonce'], 'ib_fix_conflicts')) {
        wp_send_json_error('Nonce invalide', 403);
    }
    
    global $wpdb;
    $table_bookings = $wpdb->prefix . 'ib_bookings';
    
    // Détecter les conflits
    $conflicts_query = "
        SELECT 
            b1.id as booking1_id,
            b2.id as booking2_id
        FROM {$table_bookings} b1
        INNER JOIN {$table_bookings} b2 ON (
            b1.id < b2.id 
            AND b1.employee_id = b2.employee_id
            AND b1.status != 'cancelled'
            AND b2.status != 'cancelled'
            AND (
                (b1.start_time < b2.end_time AND b1.end_time > b2.start_time)
                OR (b2.start_time < b1.end_time AND b2.end_time > b1.start_time)
            )
        )
        ORDER BY b1.start_time ASC, b2.start_time ASC
    ";
    
    $conflicts = $wpdb->get_results($conflicts_query);
    $fixed_count = 0;
    
    foreach ($conflicts as $conflict) {
        // Supprimer la réservation la plus récente (booking2)
        $result = $wpdb->delete(
            $table_bookings,
            array('id' => $conflict->booking2_id),
            array('%d')
        );
        
        if ($result !== false) {
            $fixed_count++;
        }
    }
    
    wp_send_json_success($fixed_count . ' conflit(s) corrigé(s)');
});

// Endpoint AJAX pour corriger un conflit individuel
add_action('wp_ajax_ib_fix_single_conflict', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes', 403);
    }
    
    if (!wp_verify_nonce($_POST['nonce'], 'ib_fix_conflicts')) {
        wp_send_json_error('Nonce invalide', 403);
    }
    
    $booking1_id = intval($_POST['booking1_id']);
    $booking2_id = intval($_POST['booking2_id']);
    
    if (!$booking1_id || !$booking2_id) {
        wp_send_json_error('IDs de réservation invalides');
    }
    
    global $wpdb;
    $table_bookings = $wpdb->prefix . 'ib_bookings';
    
    // Supprimer la réservation la plus récente (booking2)
    $result = $wpdb->delete(
        $table_bookings,
        array('id' => $booking2_id),
        array('%d')
    );
    
    if ($result !== false) {
        wp_send_json_success('Conflit corrigé avec succès');
    } else {
        wp_send_json_error('Erreur lors de la suppression de la réservation');
    }
});

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
