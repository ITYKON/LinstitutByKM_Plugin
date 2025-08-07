<?php
// admin/page-coupons.php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../includes/class-coupons.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-logs.php';
$coupons = IB_Coupons::get_all();
$edit_coupon = null;

// Traitement ajout coupon
if (isset($_POST['add_coupon'])) {
    error_log('DEBUG: POST add_coupon reçu');
    $code = sanitize_text_field($_POST['code']);
    $discount = floatval($_POST['discount']);
    $type = sanitize_text_field($_POST['type']);
    $usage_limit = intval($_POST['usage_limit']);
    $valid_from = sanitize_text_field($_POST['valid_from']);
    $valid_to = sanitize_text_field($_POST['valid_to']);
    
    error_log('DEBUG: Données coupon - Code: ' . $code . ', Discount: ' . $discount . ', Type: ' . $type);
    
    IB_Coupons::add($code, $discount, $type, $usage_limit, $valid_from, $valid_to);
    IB_Logs::add(get_current_user_id(), 'ajout_coupon', json_encode(['code' => $code, 'discount' => $discount]));
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Coupon ajouté avec succès.</p></div>';
}
// Traitement édition coupon
if (isset($_POST['update_coupon'])) {
    error_log('DEBUG: POST update_coupon reçu');
    $id = intval($_POST['id']);
    $code = sanitize_text_field($_POST['code']);
    $discount = floatval($_POST['discount']);
    $type = sanitize_text_field($_POST['type']);
    $usage_limit = intval($_POST['usage_limit']);
    $valid_from = sanitize_text_field($_POST['valid_from']);
    $valid_to = sanitize_text_field($_POST['valid_to']);
    
    IB_Coupons::update($id, $code, $discount, $type, $usage_limit, $valid_from, $valid_to);
    IB_Logs::add(get_current_user_id(), 'modif_coupon', json_encode(['coupon_id' => $id, 'code' => $code]));
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Coupon modifié avec succès.</p></div>';
}
// Traitement suppression coupon
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    IB_Coupons::delete((int)$_GET['id']);
    IB_Logs::add(get_current_user_id(), 'suppression_coupon', json_encode(['coupon_id' => $_GET['id']]));
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Coupon supprimé avec succès.</p></div>';
}

if (isset($_GET['edit'])) {
    $edit_coupon = IB_Coupons::get_by_id((int)$_GET['edit']);
}

// Inclure la sidebar et commencer la structure admin
// SUPPRIME cette ligne :
// include_once IB_PLUGIN_DIR . 'admin/sidebar.php';
echo '<div class="ib-admin-main">';
?>
<style>
body, .ib-admin-main {
  background: #fbeff3 !important;
  font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
}
.ib-coupons-title {
  display: flex;
  align-items: center;
  gap: 0.7em;
  font-size: 2.1em;
  font-weight: 800;
  color: #22223b;
  margin-bottom: 1.7em;
  letter-spacing: -1px;
}
.ib-coupons-title .dashicons {
  color: #e9aebc;
  font-size: 1.2em;
}
.ib-coupons-card {
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 8px 32px #e9aebc22, 0 1.5px 6px #bfa2c733;
  padding: 2em 2em 1.5em 2em;
  margin-bottom: 2.5em;
  overflow-x: auto;
}
.ib-btn-add-coupon {
  background: #e9aebc;
  color: #fff;
  border: none;
  border-radius: 16px;
  padding: 1em 2.2em;
  font-weight: 700;
  font-size: 1.13em;
  box-shadow: 0 4px 16px #e9aebc22;
  cursor: pointer;
  transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
  margin-bottom: 1.5em;
  display: inline-flex;
  align-items: center;
  gap: 0.5em;
}
.ib-btn-add-coupon:hover {
  background: #e38ca6;
  box-shadow: 0 8px 24px #e9aebc33;
  transform: translateY(-2px) scale(1.03);
}
.ib-coupons-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 1.04em;
  background: transparent;
  box-sizing: border-box;
  margin: 0;
}
.ib-coupons-table th, .ib-coupons-table td {
  padding: 1em 0.7em;
  box-sizing: border-box;
}
.ib-coupons-table thead th {
  background: #fff;
  color: #bfa2c7;
  font-weight: 700;
  border-bottom: 2px solid #fbeff3;
}
.ib-coupons-table tbody tr {
  background: #fff;
  transition: background 0.15s;
}
.ib-coupons-table tbody tr:nth-child(even) {
  background: #fbeff3;
}
.ib-coupons-table td {
  padding: 1em 0.7em;
  color: #22223b;
  font-weight: 500;
  vertical-align: middle;
}
.ib-coupons-table .ib-btn-edit {
  background: #e9aebc;
  color: #fff;
  border: none;
  border-radius: 14px;
  padding: 0.6em 1.3em;
  font-weight: 600;
  font-size: 1em;
  margin-right: 0.5em;
  box-shadow: 0 2px 8px #e9aebc22;
  cursor: pointer;
  transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
}
.ib-coupons-table .ib-btn-edit:hover {
  background: #e38ca6;
  box-shadow: 0 4px 12px #e9aebc33;
  transform: translateY(-1px);
}
.ib-coupons-table .ib-btn-delete {
  background: #fde8e8;
  color: #e87171;
  border: none;
  border-radius: 14px;
  padding: 0.6em 1.3em;
  font-weight: 600;
  font-size: 1em;
  box-shadow: 0 2px 8px #e9aebc22;
  cursor: pointer;
  transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
}
.ib-coupons-table .ib-btn-delete:hover {
  background: #fca5a5;
  color: #fff;
  box-shadow: 0 4px 12px #e9aebc33;
  transform: translateY(-1px);
}
@media (max-width: 900px) {
  .ib-coupons-card {
    padding: 1.2em 0.7em 1em 0.7em;
    border-radius: 16px;
  }
  .ib-coupons-title {
    font-size: 1.3em;
    margin-bottom: 1em;
  }
}
.ib-modal-bg {
  position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(30,40,60,0.18); z-index: 1000; display: none;
}
.ib-toast {
  border-radius: 18px;
  padding: 1.2em 2em 1.2em 1.7em;
  font-size: 1.13em;
  margin-bottom: 1.5em;
  box-shadow: 0 4px 24px #e9aebc33, 0 1.5px 6px #bfa2c733;
  background: #fff;
  color: #22223b;
  border: 2px solid #e9aebc;
  display: flex;
  align-items: center;
  gap: 1em;
  min-width: 220px;
  max-width: 340px;
  font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
  position: fixed;
  top: 32px;
  right: 32px;
  z-index: 9999;
  opacity: 0;
  transform: translateY(-20px) scale(0.98);
  animation: ib-toast-in 0.35s cubic-bezier(.4,1.4,.6,1) forwards, ib-toast-out 0.35s 3.1s cubic-bezier(.4,1.4,.6,1) forwards;
}
.ib-toast.success {
  background: linear-gradient(90deg, #fbeff3 80%, #e9aebc 100%);
  color: #22223b;
  border-color: #e9aebc;
}
.ib-toast.error {
  background: linear-gradient(90deg, #fff3f3 80%, #fca5a5 100%);
  color: #e87171;
  border-color: #fca5a5;
}
.ib-toast .ib-toast-icon {
  font-size: 1.5em;
  display: flex;
  align-items: center;
}
@keyframes ib-toast-in {
  to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes ib-toast-out {
  to { opacity: 0; transform: translateY(-20px) scale(0.98); }
}
.ib-modal-coupon {
  background: #fff;
  border-radius: 32px;
  box-shadow: 0 12px 48px #e9aebc33, 0 2px 8px #bfa2c733;
  padding: 3em 2.5em 2em 2.5em;
  max-width: 440px;
  width: 97vw;
  max-height: 92vh;
  overflow-y: auto;
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%,-50%);
  z-index: 1001;
}
.ib-modal-coupon form {
  display: flex;
  flex-direction: column;
  gap: 1.5em;
}
.ib-modal-coupon label {
  color: #bfa2c7;
  font-weight: 600;
  margin-bottom: 0.2em;
  font-size: 1.08em;
}
.ib-modal-coupon .ib-input, .ib-modal-coupon select {
  border-radius: 18px;
  border: 1.8px solid #e9aebc;
  padding: 1.1em 1.3em;
  font-size: 1.13em;
  background: rgba(255,255,255,0.92);
  color: #22223b;
  font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
  font-weight: 400;
  box-shadow: 0 2px 12px #e9aebc11;
  transition: all 0.22s;
}
.ib-modal-coupon .ib-input:focus, .ib-modal-coupon select:focus {
  border-color: #e9aebc;
  outline: none;
  box-shadow: 0 0 0 4px #fbeff3;
  background: #fff;
}
.ib-modal-coupon .ib-btn.accent {
  background: linear-gradient(90deg, #e9aebc 80%, #fbeff3 100%);
  color: #fff;
  border: none;
  border-radius: 18px;
  padding: 1em 2.5em;
  font-weight: 700;
  font-size: 1.18em;
  box-shadow: 0 4px 24px #e9aebc22;
  cursor: pointer;
  transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
  display: flex;
  align-items: center;
  gap: 0.7em;
}
.ib-modal-coupon .ib-btn.accent:hover {
  background: linear-gradient(90deg, #e38ca6 80%, #e9aebc 100%);
  box-shadow: 0 8px 32px #e9aebc33;
  transform: translateY(-2px) scale(1.03);
}
.ib-modal-coupon .ib-btn-delete {
  background: #fde8e8;
  color: #e87171;
  border: none;
  border-radius: 18px;
  padding: 1em 2.5em;
  font-weight: 700;
  font-size: 1.13em;
  box-shadow: 0 2px 8px #e9aebc22;
  cursor: pointer;
  transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
  margin-left: 0.7em;
  display: flex;
  align-items: center;
  gap: 0.7em;
}
.ib-modal-coupon .ib-btn-delete:hover {
  background: #fca5a5;
  color: #fff;
  box-shadow: 0 4px 12px #e9aebc33;
  transform: translateY(-1px);
}
@media (max-width: 600px) {
  .ib-modal-coupon { padding: 1.2em 0.5em 1em 0.5em; border-radius: 18px; }
}
</style>

<div class="ib-coupons-title"><span class="dashicons dashicons-tickets-alt"></span> Coupons</div>

<div class="ib-modal-bg" id="ib-modal-bg-coupon"></div>
<div class="ib-modal-coupon" id="ib-modal-coupon" style="display:none;">
  <button class="ib-modal-close" type="button" onclick="closeCouponModal()">&times;</button>
  <form method="post" id="ib-coupon-form-modal">
    <div style="font-size:1.18em;font-weight:700;margin-bottom:0.7em;display:flex;align-items:center;gap:0.5em;">
      <span class="dashicons dashicons-tickets-alt"></span>
      <span id="ib-coupon-modal-title">Ajouter un coupon</span>
    </div>
    <input type="hidden" name="id" id="ib-coupon-id" value="">
    <div>
      <label>Code</label>
      <input class="ib-input" name="code" id="ib-coupon-code" required>
    </div>
    <div>
      <label>Type</label>
      <select class="ib-input" name="type" id="ib-coupon-type" required>
        <option value="percent">Pourcentage (%)</option>
        <option value="fixed">Montant fixe (DA)</option>
      </select>
    </div>
    <div>
      <label>Valeur</label>
      <div style="display:flex;align-items:center;gap:0.5em;">
        <input class="ib-input" name="discount" id="ib-coupon-discount" type="number" step="1" min="1" required style="flex:1;">
        <span id="ib-coupon-unit" style="color:#bfa2c7;font-weight:600;display:none;">DA</span>
      </div>
    </div>
    <div>
      <label>Limite d'utilisations</label>
      <input class="ib-input" name="usage_limit" id="ib-coupon-usage" type="number" min="1" required>
    </div>
    <div style="display:flex;gap:1em;">
      <div style="flex:1;">
        <label>Valide du</label>
        <input class="ib-input" name="valid_from" id="ib-coupon-from" type="date" required>
      </div>
      <div style="flex:1;">
        <label>Valide au</label>
        <input class="ib-input" name="valid_to" id="ib-coupon-to" type="date" required>
      </div>
    </div>
    <div style="margin-top:2em;display:flex;gap:1em;justify-content:flex-end;">
      <button class="ib-btn accent" type="submit" name="add_coupon"><span class="dashicons dashicons-yes"></span> <span id="ib-coupon-submit-label">Ajouter</span></button>
      <button type="button" class="ib-btn-delete" onclick="closeCouponModal()"><span class="dashicons dashicons-no"></span> Annuler</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Ouvre la modale (ajout ou édition)
  function openCouponModal(edit, data) {
    document.getElementById('ib-modal-bg-coupon').style.display = 'block';
    document.getElementById('ib-modal-coupon').style.display = 'block';
    document.body.style.overflow = 'hidden';
    var submitBtn = document.querySelector('#ib-coupon-form-modal button[type="submit"]');
    if(edit && data) {
      document.getElementById('ib-coupon-modal-title').textContent = 'Éditer le coupon';
      document.getElementById('ib-coupon-submit-label').textContent = 'Enregistrer';
      document.getElementById('ib-coupon-id').value = data.id;
      document.getElementById('ib-coupon-code').value = data.code;
      document.getElementById('ib-coupon-type').value = data.type;
      document.getElementById('ib-coupon-discount').value = parseInt(data.value);
      document.getElementById('ib-coupon-usage').value = parseInt(data.usage_limit);
      document.getElementById('ib-coupon-from').value = data.start_date;
      document.getElementById('ib-coupon-to').value = data.end_date;
      submitBtn.setAttribute('name', 'update_coupon');
      updateCouponUnit();
    } else {
      document.getElementById('ib-coupon-modal-title').textContent = 'Ajouter un coupon';
      document.getElementById('ib-coupon-submit-label').textContent = 'Ajouter';
      document.getElementById('ib-coupon-id').value = '';
      document.getElementById('ib-coupon-code').value = '';
      document.getElementById('ib-coupon-type').value = 'percent';
      document.getElementById('ib-coupon-discount').value = '';
      document.getElementById('ib-coupon-usage').value = '';
      document.getElementById('ib-coupon-from').value = '';
      document.getElementById('ib-coupon-to').value = '';
      submitBtn.setAttribute('name', 'add_coupon');
      updateCouponUnit();
    }
  }
  window.closeCouponModal = function() {
    document.getElementById('ib-modal-bg-coupon').style.display = 'none';
    document.getElementById('ib-modal-coupon').style.display = 'none';
    document.body.style.overflow = '';
  };
  document.getElementById('ib-modal-bg-coupon').onclick = window.closeCouponModal;
  document.querySelectorAll('.ib-btn-add-coupon').forEach(btn => {
    btn.onclick = function(e) { e.preventDefault(); openCouponModal(false); };
  });
  document.querySelectorAll('.ib-btn-edit').forEach(btn => {
    btn.onclick = function(e) {
      e.preventDefault();
      const row = this.closest('tr');
      openCouponModal(true, {
        id: this.dataset.id,
        code: row.children[0].textContent.trim(),
        type: row.children[1].textContent.trim(),
        value: row.children[2].textContent.trim(),
        usage_limit: row.children[3].textContent.split('/')[1].trim(),
        start_date: row.children[4].textContent.trim(),
        end_date: row.children[5].textContent.trim()
      });
    };
  });

  // TOAST amélioré
  function showToast(msg, type = 'success') {
    let toast = document.createElement('div');
    toast.className = 'ib-toast ' + (type === 'error' ? 'error' : 'success');
    toast.innerHTML = `<span class='ib-toast-icon'>${type === 'error' ? '❌' : '✔️'}</span> <span>${msg}</span>`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.remove(); }, 3500);
  }

  // AJAX submit modale
  document.getElementById('ib-coupon-form-modal').onsubmit = function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="dashicons dashicons-update"></span> Enregistrement...';
    const formData = new FormData(form);
    let action = submitBtn.getAttribute('name') === 'update_coupon' ? 'ib_update_coupon' : 'ib_add_coupon';
    formData.append('action', action);
    fetch(ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
    .then(r => r.json())
    .then(res => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
      if (res.success && res.data && res.data.row) {
        // Ajout ou édition
        let tbody = document.querySelector('.ib-coupons-table tbody');
        let temp = document.createElement('tbody');
        temp.innerHTML = res.data.row;
        let newRow = temp.firstElementChild;
        if (action === 'ib_add_coupon') {
          tbody.prepend(newRow);
        } else {
          let oldRow = tbody.querySelector('tr[data-id="' + form.elements['id'].value + '"]');
          if (oldRow) oldRow.replaceWith(newRow);
        }
        closeCouponModal();
        showToast(res.data.message, 'success');
        bindCouponActions();
      } else {
        showToast(res.data && res.data.message ? res.data.message : 'Erreur AJAX', 'error');
      }
    })
    .catch(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
      showToast('Erreur AJAX', 'error');
    });
  };

  // Suppression AJAX
  function bindCouponActions() {
    document.querySelectorAll('.ib-btn-edit').forEach(btn => {
      btn.onclick = function(e) {
        e.preventDefault();
        const row = this.closest('tr');
        openCouponModal(true, {
          id: this.dataset.id,
          code: row.children[0].textContent.trim(),
          type: row.children[1].textContent.trim(),
          value: row.children[2].textContent.trim(),
          usage_limit: row.children[3].textContent.split('/')[1].trim(),
          start_date: row.children[4].textContent.trim(),
          end_date: row.children[5].textContent.trim()
        });
      };
    });
    document.querySelectorAll('.ib-btn-delete').forEach(btn => {
      btn.onclick = function(e) {
        e.preventDefault();
        if (!confirm('Supprimer ce coupon ?')) return;
        let id = this.dataset.id;
        fetch(ajaxurl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'action=ib_delete_coupon&id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(res => {
          if (res.success) {
            let row = this.closest('tr');
            if (row) row.remove();
            showToast(res.data.message, 'success');
          } else {
            showToast(res.data && res.data.message ? res.data.message : 'Erreur AJAX', 'error');
          }
        })
        .catch(() => showToast('Erreur AJAX', 'error'));
      };
    });
  }
  bindCouponActions();

  // Affichage dynamique de l'unité et du step selon le type
  function updateCouponUnit() {
    var type = document.getElementById('ib-coupon-type').value;
    var discount = document.getElementById('ib-coupon-discount');
    var unit = document.getElementById('ib-coupon-unit');
    if (type === 'fixed') {
      discount.setAttribute('step', '1');
      discount.setAttribute('min', '1');
      unit.style.display = '';
    } else {
      discount.setAttribute('step', '1');
      discount.removeAttribute('min');
      unit.style.display = 'none';
    }
  }
  document.getElementById('ib-coupon-type').addEventListener('change', updateCouponUnit);
  updateCouponUnit();
});
</script>

<!-- Card stylisée -->
<div class="ib-coupons-card">
    <a href="#" class="ib-btn-add-coupon"><span class="dashicons dashicons-plus"></span> Ajouter un coupon</a>
    <table class="ib-coupons-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Valeur</th>
                    <th>Utilisations</th>
                    <th>Valide du</th>
                    <th>Valide au</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($coupons as $coupon): ?>
            <tr data-id="<?php echo $coupon->id; ?>">
                    <td><?php echo esc_html($coupon->code); ?></td>
                    <td><?php echo esc_html($coupon->type); ?></td>
                <td>
                    <?php if ($coupon->type === 'fixed') {
                        echo intval($coupon->discount) . ' DA';
                    } else {
                        echo intval($coupon->discount) . '%';
                    } ?>
                </td>
                <td><?php echo esc_html($coupon->usage_count ?? 0) . ' / ' . esc_html($coupon->usage_limit); ?></td>
                <td><?php echo esc_html($coupon->valid_from); ?></td>
                <td><?php echo esc_html($coupon->valid_to); ?></td>
                <td>
                    <a href="#" class="ib-btn-edit" title="Éditer" data-id="<?php echo $coupon->id; ?>"><span class="dashicons dashicons-edit"></span></a>
                    <a href="#" class="ib-btn-delete" title="Supprimer" data-id="<?php echo $coupon->id; ?>"><span class="dashicons dashicons-trash"></span></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php
echo '</div>'; // Fermeture de ib-admin-main
?>
