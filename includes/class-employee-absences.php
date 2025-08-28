<?php
// Gestion des absences des employés
if (!defined('ABSPATH')) exit;

class IB_Employee_Absences {
    
    /**
     * Récupérer toutes les absences
     */
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("
            SELECT a.*, e.name as employee_name 
            FROM {$wpdb->prefix}ib_employee_absences a
            LEFT JOIN {$wpdb->prefix}ib_employees e ON a.employee_id = e.id
            ORDER BY a.start_date DESC
        ");
    }

    /**
     * Récupérer une absence par ID
     */
    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("
            SELECT a.*, e.name as employee_name 
            FROM {$wpdb->prefix}ib_employee_absences a
            LEFT JOIN {$wpdb->prefix}ib_employees e ON a.employee_id = e.id
            WHERE a.id = %d
        ", $id));
    }

    /**
     * Récupérer les absences d'un employé
     */
    public static function get_by_employee($employee_id, $start_date = null, $end_date = null) {
        global $wpdb;
        
        $where_clause = "WHERE a.employee_id = %d";
        $params = [$employee_id];
        
        if ($start_date && $end_date) {
            $where_clause .= " AND ((a.start_date BETWEEN %s AND %s) OR (a.end_date BETWEEN %s AND %s) OR (a.start_date <= %s AND a.end_date >= %s))";
            $params = array_merge($params, [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
        }
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT a.*, e.name as employee_name 
            FROM {$wpdb->prefix}ib_employee_absences a
            LEFT JOIN {$wpdb->prefix}ib_employees e ON a.employee_id = e.id
            {$where_clause}
            ORDER BY a.start_date ASC
        ", $params));
    }

    /**
     * Récupérer les absences pour une période donnée
     */
    public static function get_by_date_range($start_date, $end_date, $employee_id = null) {
        global $wpdb;
        
        $where_clause = "WHERE ((a.start_date BETWEEN %s AND %s) OR (a.end_date BETWEEN %s AND %s) OR (a.start_date <= %s AND a.end_date >= %s))";
        $params = [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date];
        
        if ($employee_id) {
            $where_clause .= " AND a.employee_id = %d";
            $params[] = $employee_id;
        }
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT a.*, e.name as employee_name 
            FROM {$wpdb->prefix}ib_employee_absences a
            LEFT JOIN {$wpdb->prefix}ib_employees e ON a.employee_id = e.id
            {$where_clause}
            ORDER BY a.start_date ASC
        ", $params));
    }

    /**
     * Ajouter une nouvelle absence
     */
    public static function add($employee_id, $start_date, $end_date, $type = 'absence', $reason = '', $status = 'approved') {
        global $wpdb;
        
        // Vérifier les conflits avec les absences existantes
        if (self::has_conflict($employee_id, $start_date, $end_date)) {
            return false;
        }
        
        $data = [
            'employee_id' => intval($employee_id),
            'start_date' => sanitize_text_field($start_date),
            'end_date' => sanitize_text_field($end_date),
            'type' => sanitize_text_field($type),
            'reason' => sanitize_textarea_field($reason),
            'status' => sanitize_text_field($status),
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert("{$wpdb->prefix}ib_employee_absences", $data);
        
        if ($result === false) {
            return false;
        }
        
        return $wpdb->insert_id;
    }

    /**
     * Mettre à jour une absence
     */
    public static function update($id, $employee_id, $start_date, $end_date, $type = 'absence', $reason = '', $status = 'approved') {
        global $wpdb;
        
        // Vérifier les conflits avec les autres absences (exclure l'absence actuelle)
        if (self::has_conflict($employee_id, $start_date, $end_date, $id)) {
            return false;
        }
        
        $data = [
            'employee_id' => intval($employee_id),
            'start_date' => sanitize_text_field($start_date),
            'end_date' => sanitize_text_field($end_date),
            'type' => sanitize_text_field($type),
            'reason' => sanitize_textarea_field($reason),
            'status' => sanitize_text_field($status),
            'updated_at' => current_time('mysql')
        ];
        
        return $wpdb->update(
            "{$wpdb->prefix}ib_employee_absences",
            $data,
            ['id' => intval($id)]
        );
    }

    /**
     * Supprimer une absence
     */
    public static function delete($id) {
        global $wpdb;
        return $wpdb->delete("{$wpdb->prefix}ib_employee_absences", ['id' => intval($id)]);
    }

    /**
     * Vérifier s'il y a un conflit avec une période d'absence existante
     */
    public static function has_conflict($employee_id, $start_date, $end_date, $exclude_id = null) {
        global $wpdb;

        $where_clause = "WHERE employee_id = %d AND status = 'approved' AND ((start_date BETWEEN %s AND %s) OR (end_date BETWEEN %s AND %s) OR (start_date <= %s AND end_date >= %s))";
        $params = [$employee_id, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date];

        if ($exclude_id) {
            $where_clause .= " AND id != %d";
            $params[] = $exclude_id;
        }

        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}ib_employee_absences
            {$where_clause}
        ", $params));

        return $count > 0;
    }

    /**
     * Vérifier si un employé est absent à une date donnée
     */
    public static function is_employee_absent($employee_id, $date) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}ib_employee_absences 
            WHERE employee_id = %d AND %s BETWEEN start_date AND end_date AND status = 'approved'
        ", $employee_id, $date));
        
        return $count > 0;
    }

    /**
     * Récupérer les types d'absence disponibles
     */
    public static function get_absence_types() {
        return [
            'absence' => 'Absence',
            'conge' => 'Congé payé',
            'maladie' => 'Congé maladie',
            'formation' => 'Formation',
            'personnel' => 'Congé personnel',
            'maternite' => 'Congé maternité',
            'paternite' => 'Congé paternité'
        ];
    }

    /**
     * Récupérer les statuts d'absence disponibles
     */
    public static function get_absence_statuses() {
        return [
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Refusé'
        ];
    }

    /**
     * Récupérer les statistiques d'absence pour un employé
     */
    public static function get_employee_stats($employee_id, $year = null) {
        global $wpdb;
        
        if (!$year) {
            $year = date('Y');
        }
        
        $start_date = $year . '-01-01';
        $end_date = $year . '-12-31';
        
        return $wpdb->get_row($wpdb->prepare("
            SELECT 
                COUNT(*) as total_absences,
                SUM(DATEDIFF(end_date, start_date) + 1) as total_days,
                SUM(CASE WHEN type = 'conge' THEN DATEDIFF(end_date, start_date) + 1 ELSE 0 END) as conge_days,
                SUM(CASE WHEN type = 'maladie' THEN DATEDIFF(end_date, start_date) + 1 ELSE 0 END) as maladie_days
            FROM {$wpdb->prefix}ib_employee_absences 
            WHERE employee_id = %d 
            AND status = 'approved'
            AND ((start_date BETWEEN %s AND %s) OR (end_date BETWEEN %s AND %s))
        ", $employee_id, $start_date, $end_date, $start_date, $end_date));
    }
}
