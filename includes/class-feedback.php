<?php
// Classe pour la gestion des feedback/avis client
if (!defined('ABSPATH')) exit;

class IB_Feedback {
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT f.*, c.name as client_name, s.name as service_name FROM {$wpdb->prefix}ib_feedback f LEFT JOIN {$wpdb->prefix}ib_clients c ON f.client_id = c.id LEFT JOIN {$wpdb->prefix}ib_services s ON f.service_id = s.id ORDER BY f.created_at DESC");
    }
    public static function add($booking_id, $client_id, $service_id, $rating, $comment) {
        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}ib_feedback", [
            'booking_id' => intval($booking_id),
            'client_id' => intval($client_id),
            'service_id' => intval($service_id),
            'rating' => intval($rating),
            'comment' => sanitize_textarea_field($comment),
            'created_at' => current_time('mysql')
        ]);
    }
    public static function delete($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}ib_feedback", ['id' => (int)$id]);
    }
    public static function moderate($id, $status) {
        global $wpdb;
        $wpdb->update("{$wpdb->prefix}ib_feedback", ['status' => $status], ['id' => (int)$id]);
    }
}
