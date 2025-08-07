<?php
// Classe pour la gestion des logs/audit trail
if (!defined('ABSPATH')) exit;

class IB_Logs {
    public static function add($user_id, $action, $context = '') {
        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}ib_logs", [
            'user_id' => intval($user_id),
            'action' => sanitize_text_field($action),
            'context' => sanitize_textarea_field($context),
            'created_at' => current_time('mysql')
        ]);
    }
    public static function get_all($limit = 100) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT l.*, u.display_name FROM {$wpdb->prefix}ib_logs l LEFT JOIN {$wpdb->prefix}users u ON l.user_id = u.ID ORDER BY l.created_at DESC LIMIT %d", $limit));
    }
}
