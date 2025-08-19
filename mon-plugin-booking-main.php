<?php
/*
Plugin Name: Booking-plugin-KM
Description: Plugin de réservation institut.
Version: 2.0
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/api-rest.php';

// Enqueue des scripts et styles
add_action('admin_enqueue_scripts', 'ib_enqueue_admin_scripts');
function ib_enqueue_admin_scripts($hook) {
    // Enqueue le CSS de sélection de notifications
    wp_enqueue_style(
        'ib-notif-selection',
        plugins_url('assets/css/ib-notif-selection.css', __FILE__),
        array(),
        '1.0.0'
    );
    
    // Enqueue le script de sélection de notifications
    wp_enqueue_script(
        'ib-notif-selection',
        plugins_url('assets/js/notifications-selection.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );
    
    // Variables globales pour les scripts
    wp_localize_script('ib-notif-selection', 'IBNotifBell', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ib_notifications_nonce')
    ));
    
    // Enqueue le script admin principal
    wp_enqueue_script(
        'ib-admin-script',
        plugins_url('assets/js/admin-script.js', __FILE__),
        array('jquery', 'ib-notif-selection'),
        '1.0.0',
        true
    );
    
    // Variables globales pour les scripts
    wp_localize_script('ib-admin-script', 'ib_admin_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
    
    // Enqueue le script de notifications ultra-simple
    wp_enqueue_script(
        'ib-ultra-notifications',
        plugins_url('assets/js/ultra-simple-notification.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );
    
    // Variables globales pour le script de notifications
    wp_localize_script('ib-ultra-notifications', 'ib_notif_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ib_notifications_nonce'),
        'strings' => array(
            'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cette notification ?',
            'error_occurred' => 'Une erreur est survenue. Veuillez réessayer.',
            'notification_deleted' => 'Notification supprimée',
            'all_marked_read' => 'Toutes les notifications ont été marquées comme lues',
            'no_notifications' => 'Aucune notification',
            'loading' => 'Chargement...'
        )
    ));
}

// Add AJAX handlers for notifications
add_action('wp_ajax_ib_get_notifications', 'ib_ajax_get_notifications');
add_action('wp_ajax_ib_mark_notification_read', 'ib_ajax_mark_notification_read');
add_action('wp_ajax_ib_mark_all_notifications_read', 'ib_ajax_mark_all_notifications_read');
add_action('wp_ajax_ib_mark_notifications_read', 'ib_ajax_mark_notifications_read');
add_action('wp_ajax_ib_delete_notifications', 'ib_ajax_delete_notifications');

function ib_ajax_get_notifications() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ib_notifications_nonce')) {
        wp_die('Security check failed');
    }
    
    // Include notifications class if not loaded
    if (!class_exists('IB_Notifications')) {
        require_once plugin_dir_path(__FILE__) . 'includes/notifications.php';
    }
    
    $recent = IB_Notifications::get_recent('admin', 15);
    $unread_count = count(IB_Notifications::get_unread('admin'));
    
    wp_send_json_success([
        'recent' => $recent,
        'unread_count' => $unread_count
    ]);
}

function ib_ajax_mark_notification_read() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ib_notifications_nonce')) {
        wp_die('Security check failed');
    }
    
    $id = intval($_POST['id']);
    
    // Include notifications class if not loaded
    if (!class_exists('IB_Notifications')) {
        require_once plugin_dir_path(__FILE__) . 'includes/notifications.php';
    }
    
    IB_Notifications::mark_as_read($id);
    wp_send_json_success();
}

function ib_ajax_mark_all_notifications_read() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ib_notifications_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Include notifications class if not loaded
    if (!class_exists('IB_Notifications')) {
        require_once plugin_dir_path(__FILE__) . 'includes/notifications.php';
    }
    
    IB_Notifications::mark_all_as_read('admin');
    wp_send_json_success();
}

/**
 * Marquer plusieurs notifications comme lues
 */
function ib_ajax_mark_notifications_read() {
    // Vérifier le nonce
    check_ajax_referer('ib_notifications_nonce', 'nonce');
    
    // Vérifier les permissions utilisateur
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }
    
    // Récupérer les IDs des notifications
    $ids = isset($_POST['ids']) ? array_map('intval', (array) $_POST['ids']) : array();
    
    if (empty($ids)) {
        wp_send_json_error('Aucune notification sélectionnée');
    }
    
    // Inclure la classe de notifications si elle n'est pas chargée
    if (!class_exists('IB_Notifications')) {
        require_once plugin_dir_path(__FILE__) . 'includes/notifications.php';
    }
    
    // Marquer chaque notification comme lue
    $marked = 0;
    foreach ($ids as $id) {
        if (IB_Notifications::mark_as_read($id)) {
            $marked++;
        }
    }
    
    wp_send_json_success(array(
        'message' => sprintf(_n('%d notification marquée comme lue', '%d notifications marquées comme lues', $marked, 'ib-booking'), $marked),
        'count' => $marked
    ));
}

/**
 * Supprimer plusieurs notifications
 */
function ib_ajax_delete_notifications() {
    // Vérifier le nonce
    check_ajax_referer('ib_notifications_nonce', 'nonce');
    
    // Vérifier les permissions utilisateur
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
    }
    
    // Récupérer les IDs des notifications
    $ids = isset($_POST['ids']) ? array_map('intval', (array) $_POST['ids']) : array();
    
    if (empty($ids)) {
        wp_send_json_error('Aucune notification sélectionnée');
    }
    
    // Inclure la classe de notifications si elle n'est pas chargée
    if (!class_exists('IB_Notifications')) {
        require_once plugin_dir_path(__FILE__) . 'includes/notifications.php';
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'ib_notifications';
    
    // Supprimer chaque notification
    $deleted = 0;
    foreach ($ids as $id) {
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($result !== false) {
            $deleted++;
        }
    }
    
    // Mettre à jour le compteur de notifications non lues
    $unread_count = 0;
    if (class_exists('IB_Notifications')) {
        $unread_count = count(IB_Notifications::get_unread('admin'));
    }
    
    wp_send_json_success(array(
        'message' => sprintf(_n('%d notification supprimée', '%d notifications supprimées', $deleted, 'ib-booking'), $deleted),
        'deleted' => $deleted,
        'unread_count' => $unread_count
    ));
}
