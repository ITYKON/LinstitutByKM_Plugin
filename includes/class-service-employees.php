<?php
// Table de liaison services-employés
if (!defined('ABSPATH')) exit;

class IB_Service_Employees {
    public static function add($service_id, $employee_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ib_service_employees';
        $wpdb->insert($table, [
            'service_id' => intval($service_id),
            'employee_id' => intval($employee_id),
        ]);
    }
    public static function get_employees_for_service($service_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ib_service_employees';
        return $wpdb->get_col($wpdb->prepare("SELECT employee_id FROM $table WHERE service_id = %d", $service_id));
    }
    public static function set_employees_for_service($service_id, $employee_ids) {
        global $wpdb;
        $table = $wpdb->prefix . 'ib_service_employees';
        $wpdb->delete($table, ['service_id' => intval($service_id)]);
        foreach ($employee_ids as $eid) {
            $wpdb->insert($table, [
                'service_id' => intval($service_id),
                'employee_id' => intval($eid),
            ]);
        }
    }
    public static function get_services_for_employee($employee_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'ib_service_employees';
        return $wpdb->get_col($wpdb->prepare("SELECT service_id FROM $table WHERE employee_id = %d", $employee_id));
    }
}
