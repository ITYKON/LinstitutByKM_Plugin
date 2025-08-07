<?php
// admin/page-employee-dashboard.php
if (!defined('ABSPATH')) exit;
$current_user = wp_get_current_user();
require_once plugin_dir_path(__FILE__) . '../includes/class-bookings.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-clients.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-services.php';
$bookings = array_filter(IB_Bookings::get_all(), function($b) use ($current_user) { return $b->employee_id == $current_user->ID; });
$clients = IB_Clients::get_all();
$services = IB_Services::get_all();
?>
<div class="ib-employee-dashboard">
    <h1>Mon Dashboard Employé</h1>
    <h2>Mes rendez-vous</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr><th>Date</th><th>Heure</th><th>Client</th><th>Service</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($bookings as $b): ?>
            <tr>
                <td><?php echo esc_html($b->date); ?></td>
                <td><?php echo esc_html($b->time); ?></td>
                <td><?php echo esc_html($b->client_name); ?></td>
                <td><?php $s = array_filter($services, function($s) use ($b) { return $s->id == $b->service_id; }); $s = reset($s); echo $s ? esc_html($s->name) : ''; ?></td>
                <td><!-- Actions à venir --></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2>Mes clients</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th></tr></thead>
        <tbody>
        <?php foreach($clients as $c): if(!empty(array_filter($bookings, function($b) use ($c) { return $b->client_email == $c->email; }))) : ?>
            <tr>
                <td><?php echo esc_html($c->name); ?></td>
                <td><?php echo esc_html($c->email); ?></td>
                <td><?php echo esc_html($c->phone); ?></td>
            </tr>
        <?php endif; endforeach; ?>
        </tbody>
    </table>
    <!-- Planning à venir -->
</div>
