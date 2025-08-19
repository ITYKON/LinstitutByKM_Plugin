<?php
// admin/page-clients.php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../includes/class-clients.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-logs.php';

// Traitement ajout client
if (isset($_POST['add_client'])) {
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
    $tags = isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '';
    IB_Clients::add($name, $email, $phone, $notes, $tags);
    IB_Logs::add(get_current_user_id(), 'ajout_client', json_encode(['name' => $name, 'email' => $email]));
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Client ajouté avec succès.</p></div>';
}

// Traitement édition client
if (isset($_POST['update_client'])) {
    $id = intval($_POST['client_id']);
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
    $tags = isset($_POST['tags']) ? sanitize_text_field($_POST['tags']) : '';
    IB_Clients::update($id, $name, $email, $phone, $notes, $tags);
    IB_Logs::add(get_current_user_id(), 'modif_client', json_encode(['client_id' => $id, 'name' => $name]));
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Client modifié avec succès.</p></div>';
}

// Traitement suppression client
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    IB_Clients::delete((int)$_GET['id']);
    IB_Logs::add(get_current_user_id(), 'suppression_client', json_encode(['client_id' => $_GET['id']]));
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Client supprimé avec succès.</p></div>';
}

$clients = IB_Clients::get_all_with_total_price();
$edit_client = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_client = IB_Clients::get_by_id((int)$_GET['id']);
}
?>
<div class="ib-clients-page" style="background:#f6f7fa;min-height:100vh;padding:0;margin:0;">
  <div class="ib-clients-content">
    <div class="ib-admin-header" style="display:flex;align-items:center;justify-content:space-between;">
      <h1 style="color:#e9aebc;font-size:2.2rem;font-weight:800;letter-spacing:-1px;">Clients</h1>
      <button class="ib-btn accent" onclick="document.getElementById('ib-add-client-form').style.display='block';">+ Ajouter un client</button>
    </div>
    <div class="ib-admin-content">
      <div id="ib-add-client-form" style="display:none;max-width:540px;margin-bottom:2em;background:#fff;padding:2em 2em 1em 2em;border-radius:14px;box-shadow:0 2px 16px #e9aebc22;">
        <h2 style="font-size:1.1rem;color:#e9aebc;font-weight:700;margin-bottom:0.7em;">Ajouter un client</h2>
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
      <?php if ($edit_client): ?>
        <!-- Modal édition client modernisée -->
        <div id="ib-modal-bg-client" class="ib-modal-bg" onclick="window.location.href='admin.php?page=institut-booking-clients'"></div>
        <div id="ib-modal-edit-client" class="ib-modal" role="dialog" aria-modal="true" aria-labelledby="ib-edit-client-title">
          <div class="ib-form-title" style="color:#e9aebc;"><i class="dashicons dashicons-admin-users"></i> <span id="ib-edit-client-title">Modifier le client</span></div>
          <div class="ib-form-summary">
            <div class="ib-summary-info">
              <div class="ib-summary-title" style="color:#e9aebc;"><?php echo esc_html($edit_client->name); ?></div>
              <div>Email : <b><?php echo esc_html($edit_client->email); ?></b></div>
              <div>Téléphone : <b><?php echo esc_html($edit_client->phone); ?></b></div>
            </div>
          </div>
          <form method="post" autocomplete="off">
            <input type="hidden" name="client_id" value="<?php echo $edit_client->id; ?>">
            <div class="ib-form-grid">
              <div class="ib-form-group">
                <input class="ib-input" id="ib-edit-client-name" name="name" value="<?php echo esc_attr($edit_client->name); ?>" required>
                <label class="ib-label" for="ib-edit-client-name">Nom</label>
              </div>
              <div class="ib-form-group">
                <input class="ib-input" id="ib-edit-client-email" name="email" type="email" value="<?php echo esc_attr($edit_client->email); ?>" required>
                <label class="ib-label" for="ib-edit-client-email">Email</label>
              </div>
              <div class="ib-form-group">
                <input class="ib-input" id="ib-edit-client-phone" name="phone" type="tel" value="<?php echo esc_attr($edit_client->phone); ?>" required>
                <label class="ib-label" for="ib-edit-client-phone">Téléphone</label>
              </div>
              <div class="ib-form-group">
                <textarea class="ib-input" id="ib-edit-client-notes" name="notes" rows="2"><?php echo esc_textarea($edit_client->notes); ?></textarea>
                <label class="ib-label" for="ib-edit-client-notes">Notes</label>
              </div>
              <div class="ib-form-group">
                <input class="ib-input" id="ib-edit-client-tags" name="tags" type="text" value="<?php echo esc_attr($edit_client->tags); ?>">
                <label class="ib-label" for="ib-edit-client-tags">Tags</label>
              </div>
            </div>
            <div class="ib-form-separator"></div>
            <div style="margin-top:1em;display:flex;gap:1.5em;justify-content:flex-end;">
              <button class="ib-btn accent" type="submit" name="update_client">Enregistrer</button>
              <a href="admin.php?page=institut-booking-clients" class="ib-btn cancel">Annuler</a>
            </div>
          </form>
        </div>
        <script>document.body.style.overflow = 'hidden';document.getElementById('ib-modal-bg-client').onclick = function(){document.body.style.overflow = '';window.location.href='admin.php?page=institut-booking-clients';};</script>
      <?php endif; ?>
      <?php if (empty($clients)): ?>
        <div style="padding:2em;text-align:center;color:#888;">Aucun client trouvé.</div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="ib-table-clients" style="width:100%;background:#fff;border-radius:14px;box-shadow:0 2px 16px #e9aebc22;margin-bottom:2em;">
          <thead style="background:#fbeff2;">
            <tr>
              <th style="color:#e9aebc;">Nom</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>Notes</th>
              <th>Tags</th>
              <th>Nb réservations</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($clients as $client): ?>
            <tr>
              <td style="font-weight:600;color:#e9aebc;"> <?php echo esc_html($client->name); ?> </td>
              <td><?php echo esc_html($client->email); ?></td>
              <td><?php echo esc_html($client->phone); ?></td>
              <td><?php echo !empty($client->notes) ? esc_html($client->notes) : '-'; ?></td>
              <td><?php echo !empty($client->tags) ? esc_html($client->tags) : '-'; ?></td>
              <td style="font-weight:700;color:#b48ecb;text-align:center;">
                <?php echo isset($client->bookings_count) ? (int)$client->bookings_count : 0; ?>
              </td>
              <td class="ib-action-btns" style="white-space:nowrap;display:flex;gap:0.5em;align-items:center;">
                <a href="admin.php?page=institut-booking-clients&action=edit&id=<?php echo $client->id; ?>" class="ib-icon-btn edit" title="Éditer">
                  <svg width="20" height="20" fill="none" stroke="#e9aebc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19.5 3 21l1.5-4L16.5 3.5z"/></svg>
                </a>
                <a href="admin.php?page=institut-booking-clients&action=delete&id=<?php echo $client->id; ?>" class="ib-icon-btn delete" title="Supprimer" onclick="return confirm('Supprimer ce client ?')">
                  <svg width="20" height="20" fill="none" stroke="#f8b4b4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
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
