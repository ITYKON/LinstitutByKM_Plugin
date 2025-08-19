<?php
// Page réservée à la réceptionniste pour la gestion des rendez-vous
if (!function_exists('institut_booking_receptionist_page')) {
function institut_booking_receptionist_page() {
    $bookings = IB_Bookings::get_all();
    $services = IB_Services::get_all();
    $employees = IB_Employees::get_all();
    echo '<div class="ib-admin-main">';
    echo '<div class="ib-admin-header"><h1>Réceptionniste</h1></div>';
    echo '<div class="wrap"><h1>Interface Réceptionniste</h1>';
    if (isset($_GET['added'])) {
        echo '<div class="updated"><p>Réservation ajoutée !</p></div>';
    }
    if (isset($_GET['deleted'])) {
        echo '<div class="updated"><p>Réservation annulée !</p></div>';
    }
    echo '<h2>Ajouter une réservation</h2>';
    echo '<form method="post"><select name="service_id">';
    foreach ($services as $service) {
        echo '<option value="'.$service->id.'">'.$service->name.'</option>';
    }
    echo '</select> <select name="employee_id">';
    foreach ($employees as $employee) {
        echo '<option value="'.$employee->id.'">'.$employee->name.'</option>';
    }
    echo '</select> <input name="client_name" placeholder="Nom client" required> <input name="client_email" type="email" placeholder="Email client" required> <input name="date" type="date" required> <input name="time" type="time" required> <button type="submit" name="add_booking">Ajouter</button></form>';
    echo '<h2>Réservations</h2>';
    echo '<table class="widefat"><tr><th>Service</th><th>Employé</th><th>Client</th><th>Email</th><th>Date</th><th>Heure</th><th>Action</th></tr>';
    foreach ($bookings as $booking) {
        $service = IB_Services::get_by_id($booking->service_id);
        $employee = IB_Employees::get_by_id($booking->employee_id);
        echo '<tr>';
        echo '<td>'.$service->name.'</td>';
        echo '<td>'.$employee->name.'</td>';
        echo '<td>'.$booking->client_name.'</td>';
        echo '<td>'.$booking->client_email.'</td>';
        echo '<td>'.$booking->date.'</td>';
        echo '<td>'.$booking->time.'</td>';
        echo '<td><a href="?page=institut-booking-receptionist&delete='.$booking->id.'" onclick="return confirm(\'Annuler cette réservation ?\')">Annuler</a></td>';
        echo '</tr>';
    }
    echo '</table></div>';
    echo '</div>';

    if (isset($_POST['add_booking'])) {
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        $start_time = $date && $time ? $date . ' ' . $time . ':00' : '';
        IB_Bookings::add([
            'service_id' => intval($_POST['service_id']),
            'employee_id' => intval($_POST['employee_id']),
            'client_name' => sanitize_text_field($_POST['client_name']),
            'client_email' => sanitize_email($_POST['client_email']),
            'date' => $date,
            'start_time' => $start_time,
        ]);
        echo '<div class="updated"><p>Réservation ajoutée !</p></div>';
    }
}
}
