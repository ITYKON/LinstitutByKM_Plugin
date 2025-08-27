<?php
// admin/page-archives.php
if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . '../includes/class-bookings.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-services.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-employees.php';

// Restauration si demandé
if (isset($_GET['restore_archive'])) {
  $archive_id = intval($_GET['restore_archive']);
  if (IB_Bookings::restore_from_archive($archive_id)) {
    echo '<div class="notice notice-success is-dismissible"><p>Réservation restaurée avec succès.</p></div>';
  } else {
    echo '<div class="notice notice-error is-dismissible"><p>Erreur lors de la restauration.</p></div>';
  }
}

// Récupérer les archives
global $wpdb;
$archives = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_bookings_archives ORDER BY archived_at DESC");

// Forcer le chargement du CSS intl-tel-input depuis le CDN officiel dans l'admin
add_action('admin_head', function() {
  echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/css/intlTelInput.min.css" />';
}, 1);
?>

<div class="ib-clients-page" style="background:#f6f7fa;min-height:100vh;padding:0;margin:0;">
  <div class="ib-clients-content">
    <div class="ib-admin-header" style="display:flex;align-items:center;justify-content:space-between;">
      <h1 style="color:#e9aebc;font-size:2.2rem;font-weight:800;letter-spacing:-1px;">Archives</h1>
      
    </div>
    <div class="ib-admin-content">
      <div id="ib-add-client-form" style="display:none;max-width:540px;margin-bottom:2em;background:#fff;padding:2em 2em 1em 2em;border-radius:14px;box-shadow:0 2px 16px #e9aebc22;">
        <h2 style="font-size:1.1rem;color:#e9aebc;font-weight:700;margin-bottom:0.7em;">Ajouter une cliente</h2>
        <form method="post" style="display:flex;gap:1.2em;flex-wrap:wrap;align-items:end;">
          <div class="ib-form-group">
            <input class="ib-input" id="add-client-name" name="name" placeholder=" " required>
            <label class="ib-label" for="add-client-name">Nom</label>
          </div>
          <div class="ib-form-group">
            <input class="ib-input" id="add-client-email" name="email" type="email" placeholder=" " required>
            <label class="ib-label" for="add-client-email">Email</label>
          </div>
          <div class="ib-form-group">
            <input class="ib-input" id="add-client-phone" name="phone" type="tel" placeholder=" " required>
            <label class="ib-label" for="add-client-phone">Téléphone</label>
          </div>
          <div class="ib-form-group">
            <textarea class="ib-input" id="add-client-notes" name="notes" rows="2" placeholder=" "></textarea>
            <label class="ib-label" for="add-client-notes">Notes</label>
          </div>
          <div class="ib-form-group">
            <input class="ib-input" id="add-client-tags" name="tags" type="text" placeholder="VIP, fidèle, etc.">
            <label class="ib-label" for="add-client-tags">Tags</label>
          </div>
          <div style="flex:1;min-width:120px;">
            <button class="ib-btn accent" type="submit" name="add_client">Ajouter</button>
            <button type="button" class="ib-btn cancel" style="margin-left:1em;" onclick="document.getElementById('ib-add-client-form').style.display='none';">Annuler</button>
          </div>
        </form>
      </div>
     
      
      <div style="overflow-x:auto;">
        <table class="ib-table-clients" style="width:100%;background:#fff;border-radius:14px;box-shadow:0 2px 16px #e9aebc22;margin-bottom:2em;">
          <thead style="background:#fbeff2;">
            <tr>
              <th style="color:#e9aebc;">Nom</th>
              <th>Email</th>
              <th>Service</th>
              <th>Date</th>
              <th>Statut</th>
              <th>Archivée le</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($archives as $ar): ?>
            <tr>
              <td style="font-weight:600;color:#e9aebc;"> <?php echo esc_html($ar->client_name); ?> </td>
              <td><?php echo esc_html($ar->client_email); ?></td>
              <td><?php $service = IB_Services::get_by_id($ar->service_id); echo $service ? esc_html($service->name) : '-'; ?></td>
              <td><?php echo esc_html($ar->date); ?></td>
              <td><?php echo esc_html($ar->status); ?></td>
              <td><?php echo esc_html($ar->archived_at); ?></td>
              <td>
                <a href="<?php echo admin_url('admin.php?page=institut-booking-archives&restore_archive=' . $ar->id); ?>" class="button">Restaurer</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    
    </div>
  </div>
</div>
<style>
.ib-clients-content {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 4px 24px #e9aebc33;
  padding: 2.2rem 2.2rem 1.5rem 2.2rem;
  margin: 2.2rem auto 0 auto;
  max-width: 1100px;
}
.ib-clients-content h1 {
  font-size: 2.2rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  color: #e9aebc;
  letter-spacing: -1px;
}
.ib-clients-content .ib-btn.accent {
  background: #e9aebc;
  color: #fff;
  border: none;
  border-radius: 14px;
  padding: 0.7em 1.5em;
  font-size: 1.1em;
  font-weight: 700;
  margin-right: 0.5em;
  margin-bottom: 0.5em;
  box-shadow: 0 2px 8px #e9aebc33;
  transition: background 0.18s, color 0.18s, transform 0.12s;
}
.ib-clients-content .ib-btn.accent:hover {
  background: #d48ca6;
  color: #fff;
  transform: translateY(-2px) scale(1.04);
}
.ib-clients-content .ib-btn.cancel {
  background: #f1f5f9;
  color: #e9aebc;
  border: 1.5px solid #e9aebc;
  border-radius: 14px;
  padding: 0.7em 1.5em;
  font-size: 1.1em;
  font-weight: 700;
  transition: background 0.18s, color 0.18s, transform 0.12s;
}
.ib-clients-content .ib-btn.cancel:hover {
  background: #e9aebc22;
  color: #e9aebc;
  transform: translateY(-2px) scale(1.04);
}
.ib-clients-content .ib-icon-btn.edit svg {
  stroke: #e9aebc;
}
.ib-clients-content .ib-icon-btn.delete svg {
  stroke: #f8b4b4;
}
.ib-clients-content .ib-icon-btn.edit:hover {
  background: #fbeff2;
}
.ib-clients-content .ib-icon-btn.delete:hover {
  background: #fbeaea;
}
.ib-clients-content .ib-icon-btn:focus {
  outline: 2px solid #e9aebc;
}
.ib-clients-content .ib-action-btns {
  display: flex;
  gap: 0.5em;
  align-items: center;
}
.ib-table-clients th, .ib-table-clients td {
  padding: 0.7em 0.5em;
  text-align: left;
}
.ib-table-clients th {
  background: #fbeff2;
  font-weight: 700;
  color: #e9aebc;
  position: sticky;
  top: 0;
  z-index: 2;
}
.ib-table-clients tr:hover {
  background: #fbeff2;
}
@media (max-width: 900px) {
  .ib-clients-content {
    padding: 1.2rem 0.5rem;
    max-width: 98vw;
  }
  .ib-table-clients th, .ib-table-clients td {
    font-size: 0.98em;
    padding: 0.4em 0.3em;
  }
}
@media (max-width: 600px) {
  .ib-table-clients, .ib-table-clients thead, .ib-table-clients tbody, .ib-table-clients tr, .ib-table-clients th, .ib-table-clients td {
    display: block;
    width: 100%;
  }
  .ib-table-clients tr {
    margin-bottom: 1.2em;
    border-radius: 10px;
    box-shadow: 0 2px 8px #e9aebc22;
    background: #fff;
  }
}
.ib-form-group {
  position: relative;
  margin-bottom: 1.5em;
}
.ib-label {
  position: absolute;
  left: 1.1em;
  top: 1.1em;
  color: #bfa2c7;
  font-size: 1em;
  pointer-events: none;
  background: transparent;
  transition: 0.18s;
  padding: 0 0.2em;
  z-index: 2;
}
.ib-input:focus + .ib-label,
.ib-input:not(:placeholder-shown) + .ib-label,
textarea:focus + .ib-label,
textarea:not(:placeholder-shown) + .ib-label {
  top: -0.7em;
  left: 0.9em;
  font-size: 0.92em;
  color: #e9aebc;
  background: #fff;
  padding: 0 0.3em;
}
.ib-input:focus, textarea:focus {
  border: 2px solid #e9aebc;
  box-shadow: 0 0 0 3px #e9aebc33;
  background: #fff;
}
</style>
