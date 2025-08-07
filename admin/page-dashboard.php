<?php
// admin/page-dashboard.php
if (!defined('ABSPATH')) exit;
?>
<div class="ib-admin-main">
  <div class="ib-dashboard-header" style="display:flex;align-items:center;gap:1em;margin-bottom:2em;">
    <span class="dashicons dashicons-chart-bar" style="font-size:2.2em;color:#e9aebc;"></span>
    <h1 style="font-size:2.1em;font-weight:800;color:#22223b;letter-spacing:-1px;">Tableau de bord</h1>
  </div>
  <div class="ib-dashboard-stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5em;margin-bottom:2.5em;">
    <div class="ib-stat-card premium">
      <div class="ib-stat-icon"><span class="dashicons dashicons-calendar-alt"></span></div>
      <div class="ib-stat-label">Réservations aujourd'hui</div>
      <div class="ib-stat-value"><?php echo method_exists('IB_Bookings', 'count_today') ? IB_Bookings::count_today() : '-'; ?></div>
    </div>
    <div class="ib-stat-card premium">
      <div class="ib-stat-icon"><span class="dashicons dashicons-admin-users"></span></div>
      <div class="ib-stat-label">Clients actifs</div>
      <div class="ib-stat-value"><?php global $wpdb; echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ib_clients"); ?></div>
    </div>
    <div class="ib-stat-card premium">
      <div class="ib-stat-icon"><span class="dashicons dashicons-groups"></span></div>
      <div class="ib-stat-label">Employés actifs</div>
      <div class="ib-stat-value"><?php global $wpdb; echo (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ib_employees"); ?></div>
    </div>
    <!-- Préparation pour d'autres stats (taux d'occupation, etc.) -->
  </div>
  <div class="ib-dashboard-actions" style="margin-bottom:2.5em;display:flex;gap:1em;flex-wrap:wrap;">
    <a href="admin.php?page=institut-booking-bookings" class="ib-btn accent"><span class="dashicons dashicons-plus"></span> Nouvelle réservation</a>
    <a href="admin.php?page=institut-booking-calendar" class="ib-btn"><span class="dashicons dashicons-calendar"></span> Voir le planning</a>
  </div>
  <div class="ib-admin-section" style="margin-bottom:2.5rem;">
    <h2 class="ib-section-title"><span class="dashicons dashicons-star-filled"></span> Top Services</h2>
    <div class="ib-table-responsive">
      <table class="ib-table-modern">
        <thead>
          <tr>
            <th>Service</th>
            <th>Réservations</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $top_services = IB_Services::get_top_services();
          foreach ($top_services as $service) {
            echo '<tr>';
            echo '<td><span class="ib-badge ib-badge-service">' . esc_html($service->name) . '</span></td>';
            echo '<td>' . esc_html($service->booking_count) . '</td>';
            echo '</tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="ib-admin-section" style="margin-bottom:2.5rem;">
    <h2 class="ib-section-title"><span class="dashicons dashicons-groups"></span> Top Employés</h2>
    <div class="ib-table-responsive">
      <table class="ib-table-modern">
        <thead>
          <tr>
            <th>Employé</th>
            <th>Réservations</th>
            <th>Satisfaction</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $top_employees = IB_Employees::get_top_employees();
          foreach ($top_employees as $employee) {
            echo '<tr>';
            echo '<td><span class="ib-badge ib-badge-employee">' . esc_html($employee->name) . '</span></td>';
            echo '<td>' . esc_html($employee->booking_count) . '</td>';
            echo '<td><span class="ib-badge ib-badge-satisfaction">' . esc_html($employee->satisfaction) . '%</span></td>';
            echo '</tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="ib-admin-section" style="margin-bottom:2.5rem;">
    <h2 class="ib-section-title"><span class="dashicons dashicons-clock"></span> Dernières réservations</h2>
    <div class="ib-table-responsive">
      <table class="ib-table-modern">
        <thead>
          <tr>
            <th>Client</th>
            <th>Service</th>
            <th>Date</th>
            <th>Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $recent_bookings = IB_Bookings::get_recent();
          foreach ($recent_bookings as $booking) {
            $client = IB_Clients::get_by_id($booking->client_id);
            $service = IB_Services::get_by_id($booking->service_id);
            $status = esc_html($booking->status);
            $status_class = 'ib-badge-status ib-badge-status-' . strtolower($status);
            echo '<tr>';
            echo '<td>' . ($client ? esc_html($client->name) : '-') . '</td>';
            echo '<td>' . ($service ? esc_html($service->name) : '-') . '</td>';
            echo '<td>' . esc_html(date('d/m/Y H:i', strtotime($booking->start_time))) . '</td>';
            echo '<td><span class="' . $status_class . '">' . $status . '</span></td>';
            echo '</tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<style>
.ib-dashboard-header {
  background: #fff;
  border-radius: 22px;
  padding: 2em 2em 1.2em 2em;
  box-shadow: 0 4px 24px #e9aebc33;
  border: 1.5px solid #fbeff3;
}
.ib-dashboard-stats-grid { margin-bottom:2.5em; }
.ib-stat-card.premium {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 24px #e9aebc22;
  padding: 2em 1.5em 1.5em 1.5em;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.7em;
  min-height: 120px;
  position: relative;
  border: 1.5px solid #fbeff3;
}
.ib-stat-card .ib-stat-icon { font-size: 2em; color: #e9aebc; margin-bottom: 0.2em; }
.ib-stat-card .ib-stat-label { color: #bfa2c7; font-weight: 600; font-size: 1.08em; }
.ib-stat-card .ib-stat-value { font-size: 2.1em; font-weight: 800; color: #b95c8a; letter-spacing: -1px; }
.ib-dashboard-actions .ib-btn {
  border-radius: 14px;
  font-size: 1.1em;
  font-weight: 700;
  padding: 0.8em 2em;
  background: #e9aebc;
  color: #fff;
  border: none;
  box-shadow: 0 2px 8px #e9aebc22;
  transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
  display: inline-flex;
  align-items: center;
  gap: 0.5em;
  text-decoration: none;
}
.ib-dashboard-actions .ib-btn:hover {
  background: #b95c8a;
  color: #fff;
  box-shadow: 0 4px 16px #e9aebc33;
  transform: translateY(-2px) scale(1.03);
}
.ib-section-title {
  font-size: 1.25rem;
  color: #b95c8a;
  margin-bottom: 1em;
  display: flex;
  align-items: center;
  gap: 0.5em;
  font-weight: 700;
}
.ib-table-responsive { overflow-x: auto; }
.ib-table-modern {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 12px #e9aebc11;
  font-size: 1.04em;
  margin-bottom: 0;
  border: 1.5px solid #fbeff3;
}
.ib-table-modern th, .ib-table-modern td { padding: 1em 0.7em; box-sizing: border-box; }
.ib-table-modern thead th {
  background: #fbeff3;
  color: #b95c8a;
  font-weight: 700;
  border-bottom: 2px solid #e9aebc33;
}
.ib-table-modern tbody tr { background: #fff; transition: background 0.15s; }
.ib-table-modern tbody tr:nth-child(even) { background: #fbeff3; }
.ib-badge, .ib-badge-service, .ib-badge-employee, .ib-badge-satisfaction {
  background: #fbeff3 !important;
  color: #b95c8a !important;
  border: 1.5px solid #e9aebc !important;
  box-shadow: 0 2px 8px #e9aebc11;
}
.ib-badge-service { background: #fbeff3; color: #b95c8a; }
.ib-badge-employee { background: #e9aebc22; color: #bfa2c7; }
.ib-badge-satisfaction {
  background: #e9fbe7 !important;
  color: #4caf50 !important;
  border: none !important;
}
.ib-badge-status {
  border-radius: 10px;
  padding: 0.2em 0.9em;
  font-size: 0.98em;
  font-weight: 700;
}
.ib-badge-status-confirmed { background: #e9fbe7; color: #4caf50; }
.ib-badge-status-pending { background: #fffbe7; color: #e9a800; }
.ib-badge-status-cancelled { background: #fde8e8; color: #e87171; }
.ib-badge-status-completed { background: #e9fbe7; color: #4caf50; }
@media (max-width: 900px) {
  .ib-dashboard-header { padding: 1.2em 0.7em 1em 0.7em; border-radius: 16px; }
  .ib-section-title { font-size: 1.1em; }
  .ib-stat-card.premium { padding: 1.2em 0.7em 1em 0.7em; border-radius: 12px; }
}
</style>