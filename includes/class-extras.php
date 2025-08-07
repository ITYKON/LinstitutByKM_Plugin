<?php
// Classe pour la gestion des extras/options de service
if (!defined('ABSPATH')) exit;

class IB_Extras {
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT e.*, s.name as service_name FROM {$wpdb->prefix}ib_extras e LEFT JOIN {$wpdb->prefix}ib_services s ON e.service_id = s.id ORDER BY e.service_id, e.name ASC");
    }
    public static function get_by_service($service_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_extras WHERE service_id = %d ORDER BY name ASC", $service_id));
    }
    public static function add($service_id, $name, $price, $duration) {
        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}ib_extras", [
            'service_id' => intval($service_id),
            'name' => sanitize_text_field($name),
            'price' => floatval($price),
            'duration' => intval($duration)
        ]);
    }
    public static function update($id, $service_id, $name, $price, $duration) {
        global $wpdb;
        $wpdb->update("{$wpdb->prefix}ib_extras",
            [
                'service_id' => intval($service_id),
                'name' => sanitize_text_field($name),
                'price' => floatval($price),
                'duration' => intval($duration)
            ],
            ['id' => (int)$id]
        );
    }
    public static function delete($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}ib_extras", ['id' => (int)$id]);
    }
}
