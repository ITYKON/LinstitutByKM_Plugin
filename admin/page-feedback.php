<?php
// admin/page-feedback.php
if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . '../includes/class-feedback.php';
$feedbacks = IB_Feedback::get_all();

// Calcul stats
$notes = array_map(function($f){return (int)$f->rating;}, $feedbacks);
$nb = count($notes);
$moyenne = $nb ? round(array_sum($notes)/$nb,2) : 0;
$repartition = array_count_values($notes);
for($i=1;$i<=5;$i++) if(!isset($repartition[$i])) $repartition[$i]=0;
ksort($repartition);
?>
<div class="ib-feedback-main">
  <div class="ib-feedback-header" style="display:flex;align-items:center;gap:1em;margin-bottom:2em;">
    <span class="dashicons dashicons-star-filled" style="font-size:2.2em;color:#e9aebc;"></span>
    <h1 style="font-size:2.1em;font-weight:800;color:#22223b;letter-spacing:-1px;">Avis & Feedback</h1>
  </div>
  <div class="ib-feedback-stats-cards" style="display:flex;gap:2em;margin-bottom:2.5em;flex-wrap:wrap;">
    <div class="ib-card premium" style="min-width:180px;">
      <div class="ib-card-label">Moyenne</div>
      <div class="ib-card-value" style="font-size:2.3em;"><span class="ib-badge-note ib-badge-note-<?php echo round($moyenne); ?>"><?php echo $moyenne; ?></span> / 5</div>
    </div>
    <div class="ib-card premium" style="min-width:180px;">
      <div class="ib-card-label">Nombre d'avis</div>
      <div class="ib-card-value" style="font-size:2.3em;"><?php echo $nb; ?></div>
    </div>
    <div class="ib-card premium" style="min-width:220px;">
      <div class="ib-card-label">Répartition</div>
      <div style="display:flex;gap:0.5em;align-items:end;">
        <?php foreach($repartition as $note=>$count): ?>
          <span class="ib-badge-note ib-badge-note-<?php echo $note; ?>"><?php echo $note; ?></span>
          <span style="font-size:1.1em;font-weight:600;color:#bfa2c7;"><?php echo $count; ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="ib-feedback-filters" style="margin-bottom:2em;display:flex;gap:1em;align-items:center;flex-wrap:wrap;">
    <label for="ib-feedback-note-filter" style="font-weight:600;color:#bfa2c7;">Filtrer par note :</label>
    <select id="ib-feedback-note-filter" style="border-radius:12px;padding:0.5em 1em;font-size:1.08em;">
      <option value="">Toutes</option>
      <?php for($i=5;$i>=1;$i--): ?><option value="<?php echo $i; ?>"><?php echo $i; ?> étoiles</option><?php endfor; ?>
    </select>
  </div>
  <div class="ib-feedback-cards-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:2rem;">
    <?php if (empty($feedbacks)): ?>
      <div style="padding:2em;text-align:center;color:#888;">Aucun avis trouvé.</div>
    <?php else: ?>
      <?php foreach($feedbacks as $fb): ?>
      <div class="ib-card-feedback" data-note="<?php echo (int)$fb->rating; ?>" style="background:#fff;border-radius:18px;box-shadow:0 4px 24px #e9aebc22;padding:1.5rem 1.2rem 1.2rem 1.2rem;display:flex;flex-direction:column;gap:0.7rem;position:relative;">
        <div style="display:flex;align-items:center;gap:0.7em;">
          <span class="ib-badge-note ib-badge-note-<?php echo (int)$fb->rating; ?>"><?php echo (int)$fb->rating; ?>/5</span>
          <span style="font-weight:700;color:#22223b;"> <?php echo esc_html($fb->client_name ?? ''); ?> </span>
          <span class="ib-badge ib-badge-service">Service : <?php echo esc_html($fb->service_name ?? ''); ?></span>
        </div>
        <div style="color:#bfa2c7;font-size:1.08em;"><span class="dashicons dashicons-format-quote"></span> <?php echo esc_html($fb->comment); ?></div>
        <div style="font-size:0.98em;color:#bfa2c7;">Le <?php echo date('d/m/Y', strtotime($fb->created_at)); ?></div>
        <div style="margin-top:1em;display:flex;gap:0.7em;">
          <a href="#" class="ib-btn-delete-feedback ib-btn danger" data-id="<?php echo $fb->id; ?>"><span class="dashicons dashicons-trash"></span> Supprimer</a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<script>
// Toast premium
function showToast(msg, type = 'success') {
  let toast = document.createElement('div');
  toast.className = 'ib-toast ' + (type === 'error' ? 'error' : 'success');
  toast.innerHTML = `<span class='ib-toast-icon'>${type === 'error' ? '❌' : '✔️'}</span> <span>${msg}</span>`;
  document.body.appendChild(toast);
  setTimeout(() => { toast.remove(); }, 3500);
}
// Suppression AJAX
jQuery(document).ready(function($){
  $('.ib-btn-delete-feedback').on('click', function(e){
    e.preventDefault();
    if (!confirm('Supprimer cet avis ?')) return;
    let id = $(this).data('id');
    fetch(ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'action=ib_delete_feedback&id=' + encodeURIComponent(id)
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        $(this).closest('.ib-card-feedback').remove();
        showToast('Avis supprimé !', 'success');
      } else {
        showToast(res.data && res.data.message ? res.data.message : 'Erreur AJAX', 'error');
      }
    })
    .catch(() => showToast('Erreur AJAX', 'error'));
  });
  // Filtres par note
  $('#ib-feedback-note-filter').on('change', function(){
    let val = $(this).val();
    $('.ib-card-feedback').each(function(){
      $(this).toggle(!val || $(this).data('note') == val);
    });
  });
});
</script>
<style>
.ib-feedback-header { background: linear-gradient(90deg,#fbeff3 80%,#e9aebc 100%); border-radius: 22px; padding: 2em 2em 1.2em 2em; box-shadow: 0 4px 24px #e9aebc33; }
.ib-card.premium { background: #fff; border-radius: 18px; box-shadow: 0 4px 24px #e9aebc22; padding: 2em 1.5em 1.5em 1.5em; display: flex; flex-direction: column; align-items: flex-start; gap: 0.7em; min-height: 120px; position: relative; margin-bottom:0; }
.ib-card .ib-card-label { color: #bfa2c7; font-weight: 600; font-size: 1.08em; }
.ib-card .ib-card-value { font-size: 2.1em; font-weight: 800; color: #22223b; letter-spacing: -1px; }
.ib-badge { display: inline-block; border-radius: 12px; padding: 0.3em 1em; font-size: 1em; font-weight: 600; background: #fbeff3; color: #e38ca6; margin-right: 0.5em; }
.ib-badge-service { background: #e9aebc22; color: #e38ca6; }
.ib-badge-note { background: #e9fbe7; color: #4caf50; font-weight:800; font-size:1.1em; padding:0.3em 1.1em; margin-right:0.3em; }
.ib-badge-note-5 { background: #e9fbe7; color: #4caf50; }
.ib-badge-note-4 { background: #e0f2fe; color: #0284c7; }
.ib-badge-note-3 { background: #fffbe7; color: #e9a800; }
.ib-badge-note-2 { background: #fde8e8; color: #e87171; }
.ib-badge-note-1 { background: #fde8e8; color: #e87171; }
.ib-btn.danger { background: #fde8e8; color: #e87171; border: none; border-radius: 14px; padding: 0.6em 1.3em; font-weight: 600; font-size: 1em; box-shadow: 0 2px 8px #e9aebc22; cursor: pointer; transition: background 0.18s, box-shadow 0.18s, transform 0.12s; }
.ib-btn.danger:hover { background: #fca5a5; color: #fff; }
.ib-toast { border-radius: 18px; padding: 1.2em 2em 1.2em 1.7em; font-size: 1.13em; margin-bottom: 1.5em; box-shadow: 0 4px 24px #e9aebc33, 0 1.5px 6px #bfa2c733; background: #fff; color: #22223b; border: 2px solid #e9aebc; display: flex; align-items: center; gap: 1em; min-width: 220px; max-width: 340px; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; position: fixed; top: 32px; right: 32px; z-index: 9999; opacity: 0; transform: translateY(-20px) scale(0.98); animation: ib-toast-in 0.35s cubic-bezier(.4,1.4,.6,1) forwards, ib-toast-out 0.35s 3.1s cubic-bezier(.4,1.4,.6,1) forwards; }
.ib-toast.success { background: linear-gradient(90deg, #fbeff3 80%, #e9aebc 100%); color: #22223b; border-color: #e9aebc; }
.ib-toast.error { background: linear-gradient(90deg, #fff3f3 80%, #fca5a5 100%); color: #e87171; border-color: #fca5a5; }
.ib-toast .ib-toast-icon { font-size: 1.5em; display: flex; align-items: center; }
@keyframes ib-toast-in { to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes ib-toast-out { to { opacity: 0; transform: translateY(-20px) scale(0.98); } }
@media (max-width: 900px) { .ib-feedback-header { padding: 1.2em 0.7em 1em 0.7em; border-radius: 16px; } .ib-card.premium { padding: 1.2em 0.7em 1em 0.7em; border-radius: 12px; } .ib-card-feedback { padding: 1.2em 0.7em 1em 0.7em; border-radius: 12px; } }
</style>
