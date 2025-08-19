<?php
// Gestion des services
if (!defined('ABSPATH')) exit;

class IB_Services {
    public static function get_all() {
        global $wpdb;
        // On récupère aussi le nom de la catégorie
        $sql = "SELECT s.*, c.name as category_name, c.id as category_id
                FROM {$wpdb->prefix}ib_services s
                LEFT JOIN {$wpdb->prefix}ib_categories c ON s.category_id = c.id";
        return $wpdb->get_results($sql);
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_services WHERE id = %d", $id));
    }

    public static function add($name, $duration, $price, $image = null, $category_id = null, $variable_price = 0, $min_price = null, $max_price = null) {
        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}ib_services", [
            'name' => sanitize_text_field($name),
            'duration' => intval($duration),
            'price' => floatval($price),
            'image' => $image ? esc_url_raw($image) : null,
            'category_id' => $category_id ? intval($category_id) : null,
            'variable_price' => intval($variable_price),
            'min_price' => $min_price !== null ? floatval($min_price) : null,
            'max_price' => $max_price !== null ? floatval($max_price) : null,
        ]);
        return $wpdb->insert_id;
    }

    public static function update($id, $name, $duration, $price, $image = null, $category_id = null, $variable_price = 0, $min_price = null, $max_price = null) {
        global $wpdb;
        $data = [
            'name' => sanitize_text_field($name),
            'duration' => intval($duration),
            'price' => floatval($price),
            'variable_price' => intval($variable_price),
            'min_price' => $min_price !== null ? floatval($min_price) : null,
            'max_price' => $max_price !== null ? floatval($max_price) : null,
        ];
        if ($image) $data['image'] = esc_url_raw($image);
        if ($category_id !== null) $data['category_id'] = intval($category_id);
        $wpdb->update("{$wpdb->prefix}ib_services", $data, ['id' => intval($id)]);
    }

    public static function delete($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}ib_services", ['id' => intval($id)]);
    }

    public static function get_top_services($limit = 5) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, COUNT(b.id) as booking_count 
            FROM {$wpdb->prefix}ib_services s 
            LEFT JOIN {$wpdb->prefix}ib_bookings b ON s.id = b.service_id 
            GROUP BY s.id 
            ORDER BY booking_count DESC 
            LIMIT %d",
            $limit
        ));
    }

    public static function get_revenue_by_service($service_id) {
        global $wpdb;
        $revenue = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(price) FROM {$wpdb->prefix}ib_bookings WHERE service_id = %d",
            $service_id
        ));
        return $revenue ? floatval($revenue) : 0;
    }

    // Vérifie si un employé réalise un service donné
    public static function employee_can_do_service($employee_id, $service_id) {
        global $wpdb;
        $service = self::get_by_id($service_id);
        if (!$service) return false;
        // Si la colonne employees existe et est un JSON/CSV d'IDs
        if (isset($service->employees)) {
            $list = is_array($service->employees) ? $service->employees : json_decode($service->employees, true);
            if (!is_array($list)) {
                $list = explode(',', $service->employees);
            }
            return in_array($employee_id, array_map('intval', $list));
        }
        // Sinon, tout employé peut réaliser le service (fallback)
        return true;
    }

    // Retourne la liste des IDs employés pour un service (fallback si pas de colonne employees)
    public static function get_employee_ids($service_id) {
        global $wpdb;
        $service = self::get_by_id($service_id);
        if (!$service) return [];
        if (isset($service->employees)) {
            $list = is_array($service->employees) ? $service->employees : json_decode($service->employees, true);
            if (!is_array($list)) {
                $list = explode(',', $service->employees);
            }
            return array_map('intval', $list);
        }
        // Si pas de mapping, retourne tous les employés (fallback)
        $employees = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}ib_employees");
        return array_map(function($e){return (int)$e->id;}, $employees);
    }

    // Ajoute d'autres méthodes si besoin
}

// Fin du fichier, ne rien ajouter après cette ligne pour éviter toute sortie parasite.
