<?php
// Gestion des employés
if (!defined('ABSPATH')) exit;

class IB_Employees {
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_employees");
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_employees WHERE id = %d", $id));
    }

    public static function add($name, $email, $phone = '', $specialty = '', $role = '', $created_at = null) {
        global $wpdb;
        $data = [
            'name' => sanitize_text_field($name),
            'email' => sanitize_email($email),
            'phone' => sanitize_text_field($phone),
            'specialty' => sanitize_text_field($specialty),
            'role' => sanitize_text_field($role),
            'created_at' => $created_at ? $created_at : current_time('mysql'),
        ];
        $wpdb->insert("{$wpdb->prefix}ib_employees", $data);
        return $wpdb->insert_id;
    }

    public static function update($id, $name, $email, $phone = '', $specialty = '', $role = '', $created_at = null) {
        global $wpdb;
        $update_data = [
            'name' => sanitize_text_field($name),
            'email' => sanitize_email($email),
            'phone' => sanitize_text_field($phone),
            'specialty' => sanitize_text_field($specialty),
            'role' => sanitize_text_field($role),
        ];
        
        if ($created_at) {
            $update_data['created_at'] = $created_at;
        }
        
        $wpdb->update("{$wpdb->prefix}ib_employees",
            $update_data,
            ['id' => intval($id)]
        );
    }

    public static function delete($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}ib_employees", ['id' => intval($id)]);
    }

    public static function get_top_employees($limit = 5) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT e.*, 
            COUNT(b.id) as booking_count,
            COALESCE(AVG(f.rating), 0) as satisfaction
            FROM {$wpdb->prefix}ib_employees e 
            LEFT JOIN {$wpdb->prefix}ib_bookings b ON e.id = b.employee_id 
            LEFT JOIN {$wpdb->prefix}ib_feedback f ON b.id = f.booking_id
            GROUP BY e.id 
            ORDER BY booking_count DESC 
            LIMIT %d",
            $limit
        ));
    }

    // Ajoute d'autres méthodes si besoin
}

// Fin du fichier, ne rien ajouter après cette ligne pour éviter toute sortie parasite.
