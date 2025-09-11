<?php
/**
 * AJAX handler for client lookup by phone number in admin booking form
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add AJAX action for admin-ajax.php
add_action('wp_ajax_lookup_client', 'ib_lookup_client_by_phone');

/**
 * Handle AJAX request to look up client by phone number
 */
function ib_lookup_client_by_phone() {
    // Check if this is an AJAX request
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        wp_send_json_error(['message' => 'Méthode non autorisée'], 403);
        wp_die();
    }

    // Verify nonce
    check_ajax_referer('booking_lookup_nonce', 'nonce');

    // Check user capabilities
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Accès refusé. Droits insuffisants.'], 403);
        wp_die();
    }

    // Get and sanitize phone number
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    
    if (empty($phone)) {
        wp_send_json_error(['message' => 'Veuillez entrer un numéro de téléphone.']);
        wp_die();
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'clients';
    
    // Check if clients table exists, if not fall back to users table
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    
    if ($table_exists) {
        // Search in wp_clients table
        $query = $wpdb->prepare(
            "SELECT name, firstname, email FROM $table_name WHERE phone = %s LIMIT 1",
            $phone
        );
    } else {
        // Fallback to wp_users table if clients table doesn't exist
        $query = $wpdb->prepare(
            "SELECT u.display_name as name, '' as firstname, u.user_email as email 
             FROM {$wpdb->users} u 
             INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id 
             WHERE um.meta_key = 'billing_phone' AND um.meta_value = %s 
             LIMIT 1",
            $phone
        );
    }
    
    $client = $wpdb->get_row($query, ARRAY_A);
    
    if ($client) {
        // Découper le champ 'name' pour obtenir prénom et nom
        if (empty($client['firstname']) && !empty($client['name'])) {
            $name = trim($client['name']);
            $name_parts = preg_split('/\s+/', $name);
            if (count($name_parts) === 1) {
                $client['firstname'] = $name_parts[0];
                $client['name'] = '';
            } else {
                $client['firstname'] = $name_parts[0];
                $client['name'] = implode(' ', array_slice($name_parts, 1));
            }
        }
        
        wp_send_json_success([
            'name' => $client['name'] ?? '',
            'firstname' => $client['firstname'] ?? '',
            'email' => $client['email'] ?? ''
        ]);
    } else {
        wp_send_json_error([
            'message' => 'Cette cliente n\'existe pas, veuillez remplir ses informations.'
        ]);
    }
    
    wp_die();
}
