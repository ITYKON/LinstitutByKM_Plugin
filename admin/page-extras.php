<?php
// admin/page-extras.php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../includes/class-extras.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-services.php';

$services = IB_Services::get_all();
$extras = IB_Extras::get_all();

// Traitement ajout extra
if (isset($_POST['add_extra'])) {
    $service_id = intval($_POST['service_id']);
    $name = sanitize_text_field($_POST['name']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);
    IB_Extras::add($service_id, $name, $price, $duration);
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Extra ajouté avec succès.</p></div>';
}

// Traitement édition extra
if (isset($_POST['update_extra'])) {
    $id = intval($_POST['extra_id']);
    $service_id = intval($_POST['service_id']);
    $name = sanitize_text_field($_POST['name']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);
    IB_Extras::update($id, $service_id, $name, $price, $duration);
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Extra modifié avec succès.</p></div>';
}

// Traitement suppression extra
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    IB_Extras::delete((int)$_GET['id']);
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Extra supprimé avec succès.</p></div>';
}

// Préparation des données pour l'édition
$edit_extra = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    foreach($extras as $ex) if($ex->id == $_GET['id']) $edit_extra = $ex;
}
?>
<div class="ib-admin-main">
  <div class="ib-extras-header" style="display:flex;align-items:center;gap:1em;margin-bottom:2em;">
    <span class="dashicons dashicons-plus-alt" style="font-size:2.2em;color:#e9aebc;"></span>
    <h1 style="font-size:2.1em;font-weight:800;color:#22223b;letter-spacing:-1px;">Extras</h1>
    <button class="ib-btn accent" id="ib-btn-add-extra" style="margin-left:auto;"><span class="dashicons dashicons-plus"></span> Ajouter un extra</button>
  </div>
  <div class="ib-extras-filters" style="margin-bottom:2em;display:flex;gap:1em;align-items:center;flex-wrap:wrap;">
    <label for="ib-extras-service-filter" style="font-weight:600;color:#bfa2c7;">Filtrer par service :</label>
    <select id="ib-extras-service-filter" style="border-radius:12px;padding:0.5em 1em;font-size:1.08em;">
      <option value="">Tous</option>
      <?php foreach($services as $s): ?>
        <option value="<?php echo $s->id; ?>"><?php echo esc_html($s->name); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div id="ib-extras-list" class="ib-extras-cards-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:2rem;">
    <?php if (empty($extras)): ?>
      <div style="padding:2em;text-align:center;color:#888;">Aucun extra trouvé.</div>
    <?php else: ?>
      <?php foreach($extras as $extra): ?>
      <div class="ib-card-extra" data-service="<?php echo $extra->service_id; ?>" style="background:#fff;border-radius:18px;box-shadow:0 4px 24px #e9aebc22;padding:1.5rem 1.2rem 1.2rem 1.2rem;display:flex;flex-direction:column;gap:0.7rem;position:relative;">
        <div style="font-size:1.12rem;font-weight:700;color:#22223b;margin-bottom:0.2rem;"> <?php echo esc_html($extra->name); ?> </div>
        <div class="ib-badge ib-badge-service">Service : <?php echo esc_html($extra->service_name ?? ''); ?></div>
        <div class="ib-badge ib-badge-price">Prix : <?php echo number_format($extra->price, 2, ',', ' '); ?> €</div>
        <div class="ib-badge ib-badge-duration">Durée : <?php echo esc_html($extra->duration); ?> min</div>
        <div style="margin-top:1em;display:flex;gap:0.7em;">
          <a href="#" class="ib-btn-edit-extra ib-btn" data-id="<?php echo $extra->id; ?>"><span class="dashicons dashicons-edit"></span> Éditer</a>
          <a href="#" class="ib-btn-delete-extra ib-btn danger" data-id="<?php echo $extra->id; ?>"><span class="dashicons dashicons-trash"></span> Supprimer</a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <!-- Modale d'ajout/édition -->
  <div class="ib-modal-bg" id="ib-modal-bg-extra" style="display:none;"></div>
  <div class="ib-modal-extra" id="ib-modal-extra" style="display:none;">
    <button class="ib-modal-close" type="button" onclick="closeExtraModal()">&times;</button>
    <form method="post" id="ib-extra-form-modal" autocomplete="off">
      <div style="font-size:1.18em;font-weight:700;margin-bottom:0.7em;display:flex;align-items:center;gap:0.5em;">
        <span class="dashicons dashicons-plus-alt"></span>
        <span id="ib-extra-modal-title">Ajouter un extra</span>
      </div>
      <input type="hidden" name="id" id="ib-extra-id" value="">
      <div style="display:flex;gap:1em;flex-wrap:wrap;">
        <div style="flex:2;min-width:180px;">
          <label>Nom</label>
          <input class="ib-input" name="name" id="ib-extra-name" required>
        </div>
        <div style="flex:1;min-width:120px;">
          <label>Prix</label>
          <input class="ib-input" name="price" id="ib-extra-price" type="number" step="0.01" required>
        </div>
        <div style="flex:1;min-width:120px;">
          <label>Durée (min)</label>
          <input class="ib-input" name="duration" id="ib-extra-duration" type="number" required>
        </div>
        <div style="flex:1;min-width:120px;">
          <label>Service</label>
          <select class="ib-input" name="service_id" id="ib-extra-service" required>
            <option value="">Choisir</option>
            <?php foreach($services as $s): ?>
              <option value="<?php echo $s->id; ?>"><?php echo esc_html($s->name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="margin-top:2em;display:flex;gap:1em;justify-content:flex-end;">
        <button class="ib-btn accent" type="submit" name="add_extra"><span class="dashicons dashicons-yes"></span> <span id="ib-extra-submit-label">Ajouter</span></button>
        <button type="button" class="ib-btn cancel" onclick="closeExtraModal()"><span class="dashicons dashicons-no"></span> Annuler</button>
      </div>
    </form>
  </div>
</div>
<script>
// Ouvre la modale (ajout ou édition)
function openExtraModal(edit, data) {
  document.getElementById('ib-modal-bg-extra').style.display = 'block';
  document.getElementById('ib-modal-extra').style.display = 'block';
  document.body.style.overflow = 'hidden';
  var submitBtn = document.querySelector('#ib-extra-form-modal button[type="submit"]');
  if(edit && data) {
    document.getElementById('ib-extra-modal-title').textContent = 'Éditer l\'extra';
    document.getElementById('ib-extra-submit-label').textContent = 'Enregistrer';
    document.getElementById('ib-extra-id').value = data.id;
    document.getElementById('ib-extra-name').value = data.name;
    document.getElementById('ib-extra-price').value = data.price;
    document.getElementById('ib-extra-duration').value = data.duration;
    document.getElementById('ib-extra-service').value = data.service_id;
    submitBtn.setAttribute('name', 'update_extra');
  } else {
    document.getElementById('ib-extra-modal-title').textContent = 'Ajouter un extra';
    document.getElementById('ib-extra-submit-label').textContent = 'Ajouter';
    document.getElementById('ib-extra-id').value = '';
    document.getElementById('ib-extra-name').value = '';
    document.getElementById('ib-extra-price').value = '';
    document.getElementById('ib-extra-duration').value = '';
    document.getElementById('ib-extra-service').value = '';
    submitBtn.setAttribute('name', 'add_extra');
  }
}
function closeExtraModal() {
  document.getElementById('ib-modal-bg-extra').style.display = 'none';
  document.getElementById('ib-modal-extra').style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('ib-modal-bg-extra').onclick = closeExtraModal;
document.getElementById('ib-btn-add-extra').onclick = function(e) { e.preventDefault(); openExtraModal(false); };
document.querySelectorAll('.ib-btn-edit-extra').forEach(btn => {
  btn.onclick = function(e) {
    e.preventDefault();
    const card = this.closest('.ib-card-extra');
    openExtraModal(true, {
      id: this.dataset.id,
      name: card.querySelector('div').textContent.trim(),
      price: card.querySelector('.ib-badge-price').textContent.replace(/[^\d,.]/g, '').replace(',', '.'),
      duration: card.querySelector('.ib-badge-duration').textContent.replace(/[^\d]/g, ''),
      service_id: card.querySelector('.ib-badge-service').textContent.replace(/[^\d]/g, '')
    });
  };
});
document.querySelectorAll('.ib-btn-delete-extra').forEach(btn => {
  btn.onclick = function(e) {
    e.preventDefault();
    if (!confirm('Supprimer cet extra ?')) return;
    let id = this.dataset.id;
    fetch(ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'action=ib_delete_extra&id=' + encodeURIComponent(id)
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        this.closest('.ib-card-extra').remove();
        showToast(res.data.message, 'success');
      } else {
        showToast(res.data && res.data.message ? res.data.message : 'Erreur AJAX', 'error');
      }
    })
    .catch(() => showToast('Erreur AJAX', 'error'));
  };
});
// Toast premium
function showToast(msg, type = 'success') {
  let toast = document.createElement('div');
  toast.className = 'ib-toast ' + (type === 'error' ? 'error' : 'success');
  toast.innerHTML = `<span class='ib-toast-icon'>${type === 'error' ? '❌' : '✔️'}</span> <span>${msg}</span>`;
  document.body.appendChild(toast);
  setTimeout(() => { toast.remove(); }, 3500);
}
// AJAX submit modale
  document.getElementById('ib-extra-form-modal').onsubmit = function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="dashicons dashicons-update"></span> Enregistrement...';
    const formData = new FormData(form);
    let action = submitBtn.getAttribute('name') === 'update_extra' ? 'ib_update_extra' : 'ib_add_extra';
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
      if (res.success && res.data && res.data.card) {
        // Ajout ou édition
        if (action === 'ib_add_extra') {
          document.getElementById('ib-extras-list').insertAdjacentHTML('afterbegin', res.data.card);
        } else {
          let oldCard = document.querySelector('.ib-card-extra[data-id="' + form.elements['id'].value + '"]');
          if (oldCard) oldCard.outerHTML = res.data.card;
        }
        closeExtraModal();
        showToast(res.data.message, 'success');
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
// Filtres par service
  document.getElementById('ib-extras-service-filter').onchange = function() {
    let val = this.value;
    document.querySelectorAll('.ib-card-extra').forEach(card => {
      card.style.display = (!val || card.getAttribute('data-service') === val) ? '' : 'none';
    });
  };
</script>
<style>
.ib-extras-header { background: linear-gradient(90deg,#fbeff3 80%,#e9aebc 100%); border-radius: 22px; padding: 2em 2em 1.2em 2em; box-shadow: 0 4px 24px #e9aebc33; }
.ib-btn.accent { background: linear-gradient(90deg,#e9aebc 80%,#fbeff3 100%); color: #fff; border: none; border-radius: 14px; padding: 0.8em 2em; font-size: 1.1em; font-weight: 700; box-shadow: 0 2px 8px #e9aebc22; transition: background 0.18s, box-shadow 0.18s, transform 0.12s; display: inline-flex; align-items: center; gap: 0.5em; text-decoration: none; }
.ib-btn.accent:hover { background: linear-gradient(90deg,#e38ca6 80%,#e9aebc 100%); box-shadow: 0 4px 16px #e9aebc33; transform: translateY(-2px) scale(1.03); }
.ib-btn.danger { background: #fde8e8; color: #e87171; border: none; border-radius: 14px; }
.ib-btn.danger:hover { background: #fca5a5; color: #fff; }
.ib-badge { display: inline-block; border-radius: 12px; padding: 0.3em 1em; font-size: 1em; font-weight: 600; background: #fbeff3; color: #e38ca6; margin-right: 0.5em; }
.ib-badge-service { background: #e9aebc22; color: #e38ca6; }
.ib-badge-price { background: #e9fbe7; color: #4caf50; }
.ib-badge-duration { background: #bfa2c722; color: #bfa2c7; }
.ib-modal-bg { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(30,40,60,0.18); z-index: 1000; display: none; }
.ib-modal-extra { background: #fff; border-radius: 32px; box-shadow: 0 12px 48px #e9aebc33, 0 2px 8px #bfa2c733; padding: 3em 2.5em 2em 2.5em; max-width: 440px; width: 97vw; max-height: 92vh; overflow-y: auto; display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: 1001; }
.ib-modal-close { position: absolute; top: 1.2em; right: 1.2em; background: none; border: none; font-size: 2em; color: #e9aebc; cursor: pointer; }
.ib-toast { border-radius: 18px; padding: 1.2em 2em 1.2em 1.7em; font-size: 1.13em; margin-bottom: 1.5em; box-shadow: 0 4px 24px #e9aebc33, 0 1.5px 6px #bfa2c733; background: #fff; color: #22223b; border: 2px solid #e9aebc; display: flex; align-items: center; gap: 1em; min-width: 220px; max-width: 340px; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; position: fixed; top: 32px; right: 32px; z-index: 9999; opacity: 0; transform: translateY(-20px) scale(0.98); animation: ib-toast-in 0.35s cubic-bezier(.4,1.4,.6,1) forwards, ib-toast-out 0.35s 3.1s cubic-bezier(.4,1.4,.6,1) forwards; }
.ib-toast.success { background: linear-gradient(90deg, #fbeff3 80%, #e9aebc 100%); color: #22223b; border-color: #e9aebc; }
.ib-toast.error { background: linear-gradient(90deg, #fff3f3 80%, #fca5a5 100%); color: #e87171; border-color: #fca5a5; }
.ib-toast .ib-toast-icon { font-size: 1.5em; display: flex; align-items: center; }
@keyframes ib-toast-in { to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes ib-toast-out { to { opacity: 0; transform: translateY(-20px) scale(0.98); } }
@media (max-width: 900px) { .ib-extras-header { padding: 1.2em 0.7em 1em 0.7em; border-radius: 16px; } .ib-card-extra { padding: 1.2em 0.7em 1em 0.7em; border-radius: 12px; } .ib-modal-extra { padding: 1.2em 0.5em 1em 0.5em; border-radius: 18px; } }
</style>
