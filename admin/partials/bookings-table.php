<?php
/**
 * Template partiel pour afficher le tableau des réservations
 * 
 * @var array $bookings Tableau des réservations à afficher
 * @var string $sort Colonne de tri actuelle
 * @var string $order Ordre de tri actuel (ASC/DESC)
 */

global $wpdb;
$services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_services");
$employees = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_employees");
?>

<table class="ib-table-bookings" style="width:100%;background:#fff;border-radius:14px;box-shadow:0 2px 16px #e9aebc22;margin-bottom:2em;">
  <thead style="background:#fbeff2;">
    <tr>
      <th style="color:#e9aebc;cursor:pointer;" data-sort="client">Client <span class="sort-arrow"></span></th>
      <th style="cursor:pointer;" data-sort="email">Email <span class="sort-arrow"></span></th>
      <th style="cursor:pointer;" data-sort="phone">Téléphone</th>
      <th style="cursor:pointer;" data-sort="service">Service <span class="sort-arrow"></span></th>
      <th style="cursor:pointer;" data-sort="employee">Employé <span class="sort-arrow"></span></th>
      <th style="cursor:pointer;" data-sort="date">Date & Heure <span class="sort-arrow"></span></th>
      <th style="cursor:pointer;" data-sort="status">Statut <span class="sort-arrow"></span></th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($bookings)): ?>
      <tr>
        <td colspan="8" style="text-align:center;padding:2em;color:#888;">Aucune réservation trouvée.</td>
      </tr>
    <?php else: ?>
      <?php foreach ($bookings as $booking): 
        $service = array_filter($services, function($s) use ($booking) {
          return $s->id == $booking->service_id;
        });
        $service = reset($service);
        
        $employee = array_filter($employees, function($e) use ($booking) {
          return $e->id == $booking->employee_id;
        });
        $employee = reset($employee);
        
        $status_class = 'ib-status-' . strtolower($booking->status);
        $status_label = ucfirst($booking->status);
        
        // Formater la date et l'heure
        $date = new DateTime($booking->date);
        $formatted_date = $date->format('d/m/Y H:i');
      ?>
        <tr>
          <td data-srv-id="<?php echo $booking->service_id; ?>"><?php echo esc_html($booking->client_name); ?></td>
          <td><?php echo esc_html($booking->client_email); ?></td>
          <td><?php echo esc_html($booking->client_phone); ?></td>
          <td><?php echo $service ? esc_html($service->name) : 'N/A'; ?></td>
          <td><?php echo $employee ? esc_html($employee->name) : 'N/A'; ?></td>
          <td><?php echo esc_html($formatted_date); ?></td>
          <td>
            <span class="ib-status-badge <?php echo esc_attr($status_class); ?>">
              <?php echo esc_html($status_label); ?>
            </span>
          </td>
          <td style="white-space:nowrap;">
            <a href="?page=institut-booking-bookings&action=edit&id=<?php echo $booking->id; ?>" class="button button-small">
              <span class="dashicons dashicons-edit" style="line-height:1.5;"></span>
            </a>
            <a href="?page=institut-booking-bookings&action=delete&id=<?php echo $booking->id; ?>" 
               class="button button-small" 
               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?');">
              <span class="dashicons dashicons-trash" style="line-height:1.5;color:#e74c3c;"></span>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>
