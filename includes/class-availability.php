<?php
if (!defined('ABSPATH')) exit;

class IB_Availability {
    public static function get_available_slots($employee_id, $service_id, $date) {
        global $wpdb;

        error_log("🔍 get_available_slots: employee_id=$employee_id, service_id=$service_id, date=$date");

        // Récupérer la durée du service
        $service = IB_Services::get_by_id($service_id);
        if (!$service || !isset($service->duration)) {
            error_log("❌ Service non trouvé ou durée manquante pour service_id=$service_id");
            return [];
        }
        $duration = intval($service->duration);
        error_log("✅ Service trouvé: durée=$duration minutes");

        // Récupérer le jour de la semaine
        $day = strtolower(date('l', strtotime($date)));
        error_log("🔍 Jour de la semaine: $day");

        // Vérifier si l'employé travaille ce jour-là
        require_once plugin_dir_path(__FILE__) . '/class-employees.php';

        if (!IB_Employees::works_on_day($employee_id, $day)) {
            error_log("❌ L'employé $employee_id ne travaille pas le $day");
            return [];
        }
        error_log("✅ L'employé $employee_id travaille le $day");

        // Vérifier si l'employé est absent ce jour-là
        require_once plugin_dir_path(__FILE__) . '/class-employee-absences.php';

        if (IB_Employee_Absences::is_employee_absent($employee_id, $date)) {
            error_log("❌ L'employé $employee_id est absent le $date");
            return [];
        }
        error_log("✅ L'employé $employee_id n'est pas absent le $date");

        // Récupérer les horaires d'ouverture dynamiques
        $opening = get_option('ib_opening_time', '09:00');
        $closing = get_option('ib_closing_time', '17:00');
        $opening_hours = [
            'start' => $opening,
            'end' => $closing
        ];
        error_log("🔍 Horaires d'ouverture: " . print_r($opening_hours, true));

        // Vérifier si le jour est ouvert (optionnel : ajouter gestion jours off/specials ici)
        if (!$opening_hours['start'] || !$opening_hours['end']) {
            error_log("❌ Horaires d'ouverture manquants");
            return [];
        }

        // Calculer les heures d'ouverture pour ce jour
        $start_time = strtotime($date . ' ' . $opening_hours['start']);
        $end_time = strtotime($date . ' ' . $opening_hours['end']);

        error_log("🔍 Heures calculées: start_time=" . date('Y-m-d H:i', $start_time) . ", end_time=" . date('Y-m-d H:i', $end_time));

        // Créer un tableau des créneaux optimisés selon la durée du service
        $slots = self::generate_optimized_slots($start_time, $end_time, $duration, $employee_id, $date);

        error_log("🔍 Créneaux optimisés générés (durée: {$duration}min): " . print_r($slots, true));

        error_log("🔍 Créneaux générés avant filtrage: " . print_r($slots, true));

        // Filtrage strict des créneaux passés si la date est aujourd'hui
        if ($date === date('Y-m-d')) {
            $now = strtotime(current_time('H:i'));
            $filtered = [];
            foreach ($slots as $slot) {
                // $slot est une chaîne de caractères (ex: "09:00")
                if (preg_match('/^\d{2}:\d{2}$/', $slot)) {
                    $slot_time = strtotime($date . ' ' . $slot);
                    if ($slot_time > $now) {
                        $filtered[] = $slot;
                    }
                }
            }
            $slots = $filtered;
            error_log("🔍 Créneaux après filtrage (aujourd'hui): " . print_r($slots, true));
        }

        // FILTRAGE CRITIQUE : Éliminer les créneaux en conflit pour cet employé
        require_once plugin_dir_path(__FILE__) . '/class-bookings.php';
        $available_slots = [];
        foreach ($slots as $slot) {
            $conflict = IB_Bookings::has_conflict($employee_id, $date, $slot);
            if (!$conflict) {
                $available_slots[] = $slot;
            } else {
                error_log("🔍 Créneau $slot exclu (conflit pour employee_id=$employee_id)");
            }
        }

        error_log("✅ Créneaux finaux retournés (après filtrage conflits): " . print_r($available_slots, true));
        return $available_slots;
    }

    public static function get_opening_hours($day) {
        // Retourne les horaires dynamiques pour n'importe quel jour
        $opening = get_option('ib_opening_time', '09:00');
        $closing = get_option('ib_closing_time', '17:00');
        return [
            'start' => $opening,
            'end' => $closing
        ];
    }

    public static function is_day_open($day) {
        // Ici tu peux ajouter la logique pour jours off/specials si besoin
        $opening = get_option('ib_opening_time', '09:00');
        $closing = get_option('ib_closing_time', '17:00');
        return !empty($opening) && !empty($closing);
    }

    public static function get_next_available_date($employee_id, $service_id, $start_date = null) {
        if (!$start_date) {
            $start_date = current_time('Y-m-d');
        }

        $date = strtotime($start_date);
        $max_days = 30; // Limite à 30 jours maximum
        
        for ($i = 0; $i < $max_days; $i++) {
            $current_date = date('Y-m-d', $date);
            $day = strtolower(date('l', $date));

            if (self::is_day_open($day)) {
                // Vérifier si l'employé n'est pas absent ce jour-là
                require_once plugin_dir_path(__FILE__) . '/class-employee-absences.php';
                if (!IB_Employee_Absences::is_employee_absent($employee_id, $current_date)) {
                    $slots = self::get_available_slots($employee_id, $service_id, $current_date);
                    if (!empty($slots)) {
                        return $current_date;
                    }
                }
            }
            $date = strtotime('+1 day', $date);
        }
        return false;
    }

    /**
     * Génère des créneaux optimisés pour maximiser le nombre de rendez-vous
     * en tenant compte des réservations existantes
     */
    private static function generate_optimized_slots($start_time, $end_time, $duration, $employee_id, $date) {
        $slots = [];
        $current_time = $start_time;

        // Récupérer toutes les réservations existantes pour cet employé ce jour-là
        require_once plugin_dir_path(__FILE__) . '/class-bookings.php';
        require_once plugin_dir_path(__FILE__) . '/class-services.php';
        global $wpdb;
        $existing_bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT start_time, service_id FROM {$wpdb->prefix}ib_bookings
             WHERE employee_id = %d AND date = %s AND status NOT IN ('cancelled', 'annulee')
             ORDER BY start_time ASC",
            $employee_id, $date
        ));

        // Convertir les réservations en plages occupées
        $occupied_periods = [];
        foreach ($existing_bookings as $booking) {
            $booking_start = strtotime($booking->start_time);
            $service = IB_Services::get_by_id($booking->service_id);
            $booking_duration = $service && isset($service->duration) ? intval($service->duration) : 30;
            $booking_end = $booking_start + ($booking_duration * 60);

            $occupied_periods[] = [
                'start' => $booking_start,
                'end' => $booking_end
            ];
        }

        error_log("🔍 Périodes occupées: " . print_r($occupied_periods, true));

        // Générer les créneaux optimisés en évitant les conflits
        while ($current_time + ($duration * 60) <= $end_time) {
            $slot_start = $current_time;
            $slot_end = $current_time + ($duration * 60);

            // Vérifier si ce créneau entre en conflit avec une réservation existante
            $has_conflict = false;
            foreach ($occupied_periods as $period) {
                if ($slot_start < $period['end'] && $slot_end > $period['start']) {
                    $has_conflict = true;
                    // Avancer au-delà de cette réservation pour le prochain créneau
                    $current_time = $period['end'];
                    break;
                }
            }

            if (!$has_conflict) {
                $time = date('H:i', $slot_start);
                $slots[] = $time;
                // Avancer du temps exact de la durée du service pour maximiser les créneaux
                $current_time += $duration * 60;
            }
        }

        return $slots;
    }
}
