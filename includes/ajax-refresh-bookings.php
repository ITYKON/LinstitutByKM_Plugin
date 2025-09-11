<?php
/**
 * AJAX handler for refreshing bookings table
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add AJAX action for refreshing bookings
add_action('wp_ajax_ib_refresh_bookings', 'ib_handle_refresh_bookings');

/**
 * Handle AJAX request for refreshing bookings
 */
function ib_handle_refresh_bookings() {
    // Verify nonce
    check_ajax_referer('ib_booking_nonce', 'nonce');
    
    // Check user capabilities
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Accès non autorisé', 403);
        wp_die();
    }
    
    // Get request parameters
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'date';
    $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'desc';
    $filters = isset($_POST['filters']) ? (array)$_POST['filters'] : array();
    
    // Validate and sanitize filters
    $sanitized_filters = array();
    $allowed_filters = array('status', 'employee', 'service', 'date', 'search');
    
    foreach ($filters as $key => $value) {
        if (in_array($key, $allowed_filters)) {
            $sanitized_filters[$key] = sanitize_text_field($value);
        }
    }
    
    // Get bookings with filters and pagination
    $bookings = IB_Booking_Manager::get_instance()->get_bookings(
        array_merge(
            $sanitized_filters,
            array(
                'page' => $page,
                'per_page' => 20, // Adjust as needed
                'orderby' => $sort,
                'order' => $order
            )
        )
    );
    
    // Start output buffering to capture the table HTML
    ob_start();
    
    if (!empty($bookings)) {
        foreach ($bookings as $booking) {
            // Include the booking row template
            include plugin_dir_path(__FILE__) . '../admin/partials/bookings-table-row.php';
        }
    } else {
        echo '<tr><td colspan="9" style="text-align:center;">Aucune réservation trouvée</td></tr>';
    }
    
    $html = ob_get_clean();
    
    // Return the HTML
    wp_send_json_success(array(
        'html' => $html,
        'count' => count($bookings)
    ));
    
    wp_die();
}
