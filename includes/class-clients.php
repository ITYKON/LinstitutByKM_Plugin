<?php
// Classe pour la gestion des clients
if (!defined('ABSPATH')) exit;

class IB_Clients {
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_clients ORDER BY created_at DESC");
    }
    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_clients WHERE id = %d", $id));
    }
    public static function add($name, $email, $phone, $notes = '', $tags = '') {
        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}ib_clients", [
            'name' => sanitize_text_field($name),
            'email' => sanitize_email($email),
            'phone' => sanitize_text_field($phone),
            'notes' => sanitize_textarea_field($notes),
            'tags' => sanitize_text_field($tags),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);
    }
    // Ajout : retourne l'ID du client ajouté
    public static function add_and_return_id($name, $email, $phone, $notes = '', $tags = '') {
        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}ib_clients", [
            'name' => sanitize_text_field($name),
            'email' => sanitize_email($email),
            'phone' => sanitize_text_field($phone),
            'notes' => sanitize_textarea_field($notes),
            'tags' => sanitize_text_field($tags),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);
        return $wpdb->insert_id;
    }
    public static function update($id, $name, $email, $phone, $notes = '', $tags = '') {
        global $wpdb;
        $wpdb->update("{$wpdb->prefix}ib_clients",
            [
                'name' => sanitize_text_field($name),
                'email' => sanitize_email($email),
                'phone' => sanitize_text_field($phone),
                'notes' => sanitize_textarea_field($notes),
                'tags' => sanitize_text_field($tags)
            ],
            ['id' => (int)$id]
        );
    }
    public static function delete($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}ib_clients", ['id' => (int)$id]);
    }
    public static function get_bookings($client_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT b.*, s.name as service_name FROM {$wpdb->prefix}ib_bookings b LEFT JOIN {$wpdb->prefix}ib_services s ON b.service_id = s.id WHERE b.client_id = %d ORDER BY b.date DESC", $client_id));
    }
    public static function count_active() {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ib_clients");
    }
    // Chercher un client par email
    public static function get_by_email($email) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_clients WHERE email = %s", $email));
    }
    public static function get_by_phone($phone) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_clients WHERE phone = %s", $phone));
    }
    public static function get_all_with_total_price() {
        global $wpdb;
        $bookings_count_join = "LEFT JOIN {$wpdb->prefix}ib_bookings b ON b.client_email = c.email AND (b.status = 'confirmee' OR b.status = 'complete')";
        return $wpdb->get_results("
            SELECT c.*, COALESCE(SUM(b.price),0) as total_price
            FROM {$wpdb->prefix}ib_clients c
            $bookings_count_join
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
    }
}
