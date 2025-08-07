<?php

class IB_Booking_Form {
    private $services;
    private $employees;

    public function __construct() {
        $this->services = IB_Services::get_all();
        $this->employees = IB_Employees::get_all();

        // Ajout du champ employee_ids à chaque service
        foreach ($this->services as &$service) {
            $service->employee_ids = IB_Service_Employees::get_employees_for_service($service->id);
        }
        unset($service);
    }

    // Fonction de récupération des créneaux disponibles
    public static function get_available_slots($employee_id, $service_id, $date) {
        return IB_Availability::get_available_slots($employee_id, $service_id, $date);
    }

    // Fonction pour vérifier si une date est valide (jour ouvré)
    public static function is_valid_date($date) {
        $day = strtolower(date('l', strtotime($date)));
        return IB_Availability::is_day_open($day);
    }

    // Fonction pour obtenir la prochaine date disponible
    public static function get_next_available_date($employee_id, $service_id, $start_date = null) {
        return IB_Availability::get_next_available_date($employee_id, $service_id, $start_date);
    }

    // Récupération des données pour le formulaire
    public function get_booking_data() {
        return [
            'services' => $this->services,
            'employees' => $this->employees
        ];
    }
}
