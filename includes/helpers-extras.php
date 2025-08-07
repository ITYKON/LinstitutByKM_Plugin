<?php
// Récupère les extras pour un service (helper pour le front)
function institut_booking_get_extras($service_id) {
    if (!class_exists('IB_Extras')) require_once plugin_dir_path(__FILE__) . '../includes/class-extras.php';
    return IB_Extras::get_by_service($service_id);
}
