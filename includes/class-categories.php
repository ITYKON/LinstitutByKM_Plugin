<?php
// Classe pour la gestion des catégories de services
if (!defined('ABSPATH')) exit;

class IB_Categories {
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_categories ORDER BY name ASC");
    }
    public static function add($name, $color, $icon) {
        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}ib_categories", [
            'name' => sanitize_text_field($name),
            'color' => sanitize_hex_color($color),
            'icon' => sanitize_text_field($icon)
        ]);
    }
    public static function delete($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}ib_categories", ['id' => (int)$id]);
    }
    public static function update($id, $name, $color, $icon) {
        global $wpdb;
        $wpdb->update("{$wpdb->prefix}ib_categories",
            [
                'name' => sanitize_text_field($name),
                'color' => sanitize_hex_color($color),
                'icon' => sanitize_text_field($icon)
            ],
            ['id' => (int)$id]
        );
    }
}
