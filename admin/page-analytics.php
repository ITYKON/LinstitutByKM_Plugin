<?php
// admin/page-analytics.php
if (!defined('ABSPATH')) exit;

global $wpdb;
$bookings_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ib_bookings");
$employees_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ib_employees");
$services_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ib_services");
$revenue = $wpdb->get_var("SELECT SUM(price) FROM {$wpdb->prefix}ib_bookings WHERE status = 'confirmed'");

// Gestion des valeurs NULL
$bookings_count = $bookings_count ?: 0;
$employees_count = $employees_count ?: 0;
$services_count = $services_count ?: 0;
$revenue = $revenue ?: 0;
?>
<div class="ib-analytics-main">
  <div class="ib-analytics-header" style="display:flex;align-items:center;gap:1em;margin-bottom:2em;">
    <span class="dashicons dashicons-chart-bar" style="font-size:2.2em;color:#e9aebc;"></span>
    <h1 style="font-size:2.1em;font-weight:800;color:#22223b;letter-spacing:-1px;">Analytics</h1>
  </div>
  <div class="ib-analytics-cards-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5em;margin-bottom:2.5em;">
    <div class="ib-card premium">
      <div class="ib-card-icon"><span class="dashicons dashicons-calendar-alt"></span></div>
      <div class="ib-card-label">Réservations</div>
      <div class="ib-card-value"><?php echo esc_html($bookings_count); ?></div>
    </div>
    <div class="ib-card premium">
      <div class="ib-card-icon"><span class="dashicons dashicons-groups"></span></div>
      <div class="ib-card-label">Employés</div>
      <div class="ib-card-value"><?php echo esc_html($employees_count); ?></div>
    </div>
    <div class="ib-card premium">
      <div class="ib-card-icon"><span class="dashicons dashicons-admin-tools"></span></div>
      <div class="ib-card-label">Services</div>
      <div class="ib-card-value"><?php echo esc_html($services_count); ?></div>
    </div>
    <div class="ib-card premium">
      <div class="ib-card-icon"><span class="dashicons dashicons-chart-line"></span></div>
      <div class="ib-card-label">Chiffre d'affaires</div>
      <div class="ib-card-value">€<?php echo number_format($revenue, 2, ',', ' '); ?></div>
    </div>
  </div>
  <div class="ib-analytics-filters" style="margin-bottom:2em;display:flex;gap:1em;align-items:center;flex-wrap:wrap;">
    <label for="ib-analytics-period" style="font-weight:600;color:#bfa2c7;">Période :</label>
    <select id="ib-analytics-period" style="border-radius:12px;padding:0.5em 1em;font-size:1.08em;">
      <option value="12">12 derniers mois</option>
      <option value="6">6 derniers mois</option>
      <option value="3">3 derniers mois</option>
      <option value="1">Ce mois</option>
    </select>
    <button class="ib-btn accent" id="ib-analytics-export"><span class="dashicons dashicons-download"></span> Export CSV</button>
  </div>
  <div class="ib-analytics-charts" style="display:grid;grid-template-columns:1fr 1fr;gap:2em;margin-bottom:2.5em;">
    <div class="ib-chart-card">
      <h3 class="ib-chart-title"><span class="dashicons dashicons-chart-line"></span> Réservations par mois</h3>
      <canvas id="ib-bookings-chart" height="80"></canvas>
    </div>
    <div class="ib-chart-card">
      <h3 class="ib-chart-title"><span class="dashicons dashicons-chart-area"></span> Chiffre d'affaires par mois</h3>
      <canvas id="ib-revenue-chart" height="80"></canvas>
    </div>
  </div>
  <div class="ib-analytics-tops" style="display:grid;grid-template-columns:1fr 1fr;gap:2em;margin-bottom:2.5em;">
    <div class="ib-top-card">
      <h3 class="ib-top-title"><span class="dashicons dashicons-star-filled"></span> Top Services</h3>
      <div id="ib-top-services"></div>
    </div>
    <div class="ib-top-card">
      <h3 class="ib-top-title"><span class="dashicons dashicons-groups"></span> Top Employés</h3>
      <div id="ib-top-employees"></div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
jQuery(document).ready(function($){
  function loadAnalytics(period) {
    $.post(ajaxurl, {action: 'ib_get_analytics_data', period: period}, function(data){
      // Bookings chart
      new Chart(document.getElementById('ib-bookings-chart'), {
        type: 'line',
        data: {
          labels: data.months,
          datasets: [{
            label: 'Réservations',
            data: data.bookings,
            backgroundColor: 'rgba(54, 162, 235, 0.18)',
            borderColor: '#e9aebc',
            borderWidth: 3,
            pointBackgroundColor: '#e9aebc',
            tension: 0.3,
            fill: true
          }]
        },
        options: {responsive: true, plugins: {legend: {display: false}}}
      });
      // Revenue chart
      new Chart(document.getElementById('ib-revenue-chart'), {
        type: 'bar',
        data: {
          labels: data.months,
          datasets: [{
            label: "Chiffre d'affaires (€)",
            data: data.revenues,
            backgroundColor: 'rgba(255, 206, 86, 0.18)',
            borderColor: '#e9aebc',
            borderWidth: 2
          }]
        },
        options: {responsive: true, plugins: {legend: {display: false}}}
      });
      // Top services/employés
      let topServices = '<ol class="ib-top-list">'; data.top_services.forEach((s,i) => { topServices += `<li><span class='ib-badge ib-badge-rank'>#${i+1}</span> <span class='ib-badge ib-badge-service'>${s.name}</span> <b>(${s.total})</b></li>`; }); topServices += '</ol>';
      let topEmployees = '<ol class="ib-top-list">'; data.top_employees.forEach((e,i) => { topEmployees += `<li><span class='ib-badge ib-badge-rank'>#${i+1}</span> <span class='ib-badge ib-badge-employee'>${e.name}</span> <b>(${e.total})</b></li>`; }); topEmployees += '</ol>';
      $('#ib-top-services').html(topServices);
      $('#ib-top-employees').html(topEmployees);
    });
  }
  loadAnalytics($('#ib-analytics-period').val());
  $('#ib-analytics-period').on('change', function(){
    loadAnalytics($(this).val());
  });
  $('#ib-analytics-export').on('click', function(e){
    e.preventDefault();
    // TODO: Export CSV (à implémenter)
    alert('Export CSV à venir !');
  });
});
</script>
<style>
.ib-analytics-main {padding: 30px;}
.ib-analytics-header { background: linear-gradient(90deg,#fbeff3 80%,#e9aebc 100%); border-radius: 22px; padding: 2em 2em 1.2em 2em; box-shadow: 0 4px 24px #e9aebc33; }
.ib-analytics-cards-grid { margin-bottom:2.5em; }
.ib-card.premium { background: #fff; border-radius: 18px; box-shadow: 0 4px 24px #e9aebc22; padding: 2em 1.5em 1.5em 1.5em; display: flex; flex-direction: column; align-items: flex-start; gap: 0.7em; min-height: 120px; position: relative; }
.ib-card .ib-card-icon { font-size: 2em; color: #e9aebc; margin-bottom: 0.2em; }
.ib-card .ib-card-label { color: #bfa2c7; font-weight: 600; font-size: 1.08em; }
.ib-card .ib-card-value { font-size: 2.1em; font-weight: 800; color: #22223b; letter-spacing: -1px; }
.ib-analytics-filters .ib-btn { border-radius: 14px; font-size: 1.1em; font-weight: 700; padding: 0.7em 2em; background: linear-gradient(90deg,#e9aebc 80%,#fbeff3 100%); color: #fff; border: none; box-shadow: 0 2px 8px #e9aebc22; transition: background 0.18s, box-shadow 0.18s, transform 0.12s; display: inline-flex; align-items: center; gap: 0.5em; text-decoration: none; }
.ib-analytics-filters .ib-btn:hover { background: linear-gradient(90deg,#e38ca6 80%,#e9aebc 100%); box-shadow: 0 4px 16px #e9aebc33; transform: translateY(-2px) scale(1.03); }
.ib-analytics-charts { margin-bottom:2.5em; }
.ib-chart-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #e9aebc11; padding: 1.5em; }
.ib-chart-title { font-size: 1.1em; color: #bfa2c7; font-weight: 700; margin-bottom: 1em; display: flex; align-items: center; gap: 0.5em; }
.ib-top-card { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #e9aebc11; padding: 1.5em; }
.ib-top-title { font-size: 1.1em; color: #bfa2c7; font-weight: 700; margin-bottom: 1em; display: flex; align-items: center; gap: 0.5em; }
.ib-top-list { list-style:none; padding:0; margin:0; }
.ib-top-list li { display:flex;align-items:center;gap:0.7em;margin-bottom:0.5em; }
.ib-badge { display: inline-block; border-radius: 12px; padding: 0.3em 1em; font-size: 1em; font-weight: 600; background: #fbeff3; color: #e38ca6; margin-right: 0.5em; }
.ib-badge-service { background: #e9aebc22; color: #e38ca6; }
.ib-badge-employee { background: #bfa2c722; color: #bfa2c7; }
.ib-badge-rank { background: #e9aebc; color: #fff; margin-right:0.3em; }
@media (max-width: 900px) { .ib-analytics-header { padding: 1.2em 0.7em 1em 0.7em; border-radius: 16px; } .ib-card.premium { padding: 1.2em 0.7em 1em 0.7em; border-radius: 12px; } .ib-chart-card, .ib-top-card { padding: 1em; border-radius: 10px; } .ib-analytics-charts, .ib-analytics-tops { grid-template-columns:1fr; gap:1em; } }
</style>
