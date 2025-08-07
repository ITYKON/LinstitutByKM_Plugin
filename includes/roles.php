<?php
if (!defined('ABSPATH')) exit;

/**
 * Création des rôles personnalisés
 */
function ib_create_roles() {
    // Rôle employé (classique)
    add_role('employee', __('Employé', 'institut-booking'), [
        'read' => true,
        'ib_manage_bookings' => true,
        'ib_manage_clients' => true,
        'ib_manage_services' => true,
        'ib_manage_employees' => true,
        'ib_manage_extras' => true,
        'ib_manage_coupons' => true,
        'ib_view_reports' => true,
        'ib_manage_settings' => true,
        'ib_full_access' => true // Accès complet au plugin
    ]);
    // Rôle praticienne
    add_role('ib_employee', __('Praticienne', 'institut-booking'), [
        'read' => true,
        'ib_view_own_bookings' => true,
        'ib_manage_own_availability' => true,
        'ib_view_own_schedule' => true,
        'ib_full_access' => true // Accès complet au plugin
    ]);

    // Rôle réceptionniste
    add_role('receptionist', __('Réceptionniste', 'institut-booking'), [
        'read' => true,
        'ib_manage_bookings' => true,
        'ib_manage_clients' => true,
        'ib_manage_services' => true,
        'ib_manage_employees' => true,
        'ib_manage_extras' => true,
        'ib_manage_coupons' => true,
        'ib_view_reports' => true,
        'ib_manage_settings' => true,
        'ib_full_access' => true // Accès complet au plugin
    ]);

    // Ajout des capacités à l'administrateur
    $admin = get_role('administrator');
    if ($admin) {
        $admin->add_cap('ib_manage_bookings');
        $admin->add_cap('ib_manage_clients');
        $admin->add_cap('ib_manage_services');
        $admin->add_cap('ib_manage_employees');
        $admin->add_cap('ib_manage_extras');
        $admin->add_cap('ib_manage_coupons');
        $admin->add_cap('ib_view_reports');
        $admin->add_cap('ib_manage_settings');
        $admin->add_cap('ib_view_own_bookings');
        $admin->add_cap('ib_manage_own_availability');
        $admin->add_cap('ib_view_own_schedule');
    }
}

/**
 * Suppression des rôles personnalisés
 */
function ib_remove_roles() {
    remove_role('ib_employee');
    remove_role('receptionist');

    // Suppression des capacités de l'administrateur
    $admin = get_role('administrator');
    if ($admin) {
        $admin->remove_cap('ib_manage_bookings');
        $admin->remove_cap('ib_manage_clients');
        $admin->remove_cap('ib_manage_services');
        $admin->remove_cap('ib_manage_employees');
        $admin->remove_cap('ib_manage_extras');
        $admin->remove_cap('ib_manage_coupons');
        $admin->remove_cap('ib_view_reports');
        $admin->remove_cap('ib_manage_settings');
        $admin->remove_cap('ib_view_own_bookings');
        $admin->remove_cap('ib_manage_own_availability');
        $admin->remove_cap('ib_view_own_schedule');
    }
}

/**
 * Ajoute la capacité ib_full_access aux rôles existants si besoin (upgrade)
 */
function ib_upgrade_roles_full_access() {
    $roles = ['ib_employee', 'receptionist', 'employee'];
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role && !$role->has_cap('ib_full_access')) {
            $role->add_cap('ib_full_access');
        }
    }
}
add_action('init', 'ib_upgrade_roles_full_access');
