<?php
/**
 * Gestion des vérifications de notifications en temps réel
 */

// Ajouter l'action AJAX pour vérifier les nouvelles notifications
add_action('wp_ajax_ib_check_new_notifications', 'ib_ajax_check_new_notifications');
add_action('wp_ajax_nopriv_ib_check_new_notifications', 'ib_ajax_check_new_notifications');

/**
 * Vérifie les nouvelles notifications depuis la dernière vérification
 */
function ib_ajax_check_new_notifications() {
    // Vérifier le nonce de sécurité
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ib_admin_nonce')) {
        wp_send_json_error('Erreur de sécurité');
        return;
    }

    // Vérifier les permissions utilisateur
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'ib_notifications';
    
    // Récupérer le timestamp de la dernière vérification (ou 0 si non défini)
    $last_check = isset($_POST['last_check']) ? intval($_POST['last_check']) : 0;
    
    // Compter le nombre total de notifications non lues
    $unread_count = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name 
             WHERE status = 'unread' AND user_id = %d",
            get_current_user_id()
        )
    );
    
    // Vérifier s'il y a de nouvelles notifications depuis la dernière vérification
    $has_new = false;
    $new_notifications = [];
    
    if ($last_check > 0) {
        $new_notifications = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name 
                 WHERE created_at > FROM_UNIXTIME(%d) 
                 AND user_id = %d 
                 ORDER BY created_at DESC 
                 LIMIT 10",
                $last_check,
                get_current_user_id()
            )
        );
        
        $has_new = !empty($new_notifications);
    } else {
        // Si c'est la première vérification, on considère qu'il n'y a pas de nouvelles notifications
        $has_new = false;
    }
    
    // Préparer la réponse
    $response = [
        'unread_count' => intval($unread_count),
        'has_new' => $has_new,
        'last_checked' => time(),
        'new_notifications' => $new_notifications
    ];
    
    wp_send_json_success($response);
}
