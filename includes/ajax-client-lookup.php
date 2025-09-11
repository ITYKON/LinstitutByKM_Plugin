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

    try {
        // Get and sanitize phone number
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        
        if (empty($phone)) {
            throw new Exception('Veuillez entrer un numéro de téléphone.');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'clients';
        
        // Check if clients table exists, if not fall back to users table
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if ($table_exists) {
            // Search in wp_clients table
            $query = $wpdb->prepare(
                "SELECT name, firstname, email, phone FROM $table_name WHERE phone = %s LIMIT 1",
                $phone
            );
        } else {
            // Fallback to wp_users table if clients table doesn't exist
            $query = $wpdb->prepare(
                "SELECT u.display_name as name, '' as firstname, u.user_email as email, '' as phone 
                 FROM {$wpdb->users} u 
                 INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id 
                 WHERE um.meta_key = 'billing_phone' AND um.meta_value = %s 
                 LIMIT 1",
                $phone
            );
        }
        
        $client = $wpdb->get_row($query, ARRAY_A);
        
        if ($client) {
            // Format the name if it's a single field
            if (empty($client['firstname']) && !empty($client['name'])) {
                $name_parts = explode(' ', $client['name'], 2);
                $client['firstname'] = $name_parts[0];
                $client['name'] = $name_parts[1] ?? '';
            }
            
            wp_send_json([
                'success' => true,
                'name' => $client['name'] ?? '',
                'firstname' => $client['firstname'] ?? '',
                'email' => $client['email'] ?? ''
            ]);
        } else {
            wp_send_json([
                'success' => false,
                'message' => 'Cette cliente n\'existe pas, veuillez remplir ses informations.'
            ]);
        }
    } catch (Exception $e) {
        wp_send_json([
            'success' => false,
            'message' => 'Erreur lors de la recherche : ' . $e->getMessage()
        ]);
    }
    
    wp_die();
}

// Add the AJAX URL to the page
add_action('admin_enqueue_scripts', 'ib_add_client_lookup_ajax_url');
function ib_add_client_lookup_ajax_url() {
    wp_localize_script('jquery', 'ib_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('booking_lookup_nonce')
    ]);
}
