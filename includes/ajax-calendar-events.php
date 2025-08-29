<?php
/**
 * AJAX handler for FullCalendar event fetching with filters
 */
add_action('wp_ajax_ibk_get_events', 'ibk_get_events_callback');
add_action('wp_ajax_nopriv_ibk_get_events', 'ibk_get_events_callback');

function ibk_get_events_callback() {
    // Security check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ibk_get_events')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ib_bookings';
    $query = "SELECT * FROM $table WHERE 1=1";
    $params = [];

    // Date range (for all views)
    if (!empty($_POST['start'])) {
        $query .= " AND start >= %s";
        $params[] = $_POST['start'];
    }
    if (!empty($_POST['end'])) {
        $query .= " AND end <= %s";
        $params[] = $_POST['end'];
    }

    // Filters
    if (!empty($_POST['employee_id'])) {
        $query .= " AND employee_id = %d";
        $params[] = intval($_POST['employee_id']);
    }
    if (!empty($_POST['service_id'])) {
        $query .= " AND service_id = %d";
        $params[] = intval($_POST['service_id']);
    }
    if (!empty($_POST['category_id'])) {
        $query .= " AND category_id = %d";
        $params[] = intval($_POST['category_id']);
    }
    if (!empty($_POST['status'])) {
        $query .= " AND status = %s";
        $params[] = sanitize_text_field($_POST['status']);
    }

    // Prepare and execute
    if (!empty($params)) {
        $query = $wpdb->prepare($query, $params);
    }
    $results = $wpdb->get_results($query);

    // Format for FullCalendar
    $events = [];
    foreach ($results as $row) {
        $events[] = [
            'id' => $row->id,
            'title' => $row->title,
            'start' => $row->start,
            'end' => $row->end,
            'allDay' => (bool)$row->allDay,
            'backgroundColor' => $row->backgroundColor,
            'textColor' => $row->textColor,
            'borderColor' => $row->borderColor,
            'extendedProps' => [
                'service_id' => $row->service_id,
                'employee_id' => $row->employee_id,
                'category_id' => $row->category_id,
                'status' => $row->status,
                'notes' => $row->notes,
                'service' => $row->service,
                'employee' => $row->employee,
            ],
        ];
    }
    wp_send_json_success($events);
}
