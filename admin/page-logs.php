<?php
// admin/page-logs.php
if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . '../includes/class-logs.php';
$logs = IB_Logs::get_all();

// Stats des logs
$total_logs = count($logs);
$today_logs = array_filter($logs, function($log) {
    return date('Y-m-d', strtotime($log->created_at)) === date('Y-m-d');
});
$today_count = count($today_logs);

// Actions les plus fréquentes
$actions = array_count_values(array_map(function($log) { return $log->action; }, $logs));
arsort($actions);
$top_actions = array_slice($actions, 0, 5, true);
?>
<div class="ib-logs-main">
  <div class="ib-logs-header" style="display:flex;align-items:center;gap:1em;margin-bottom:2em;">
    <span class="dashicons dashicons-list-view" style="font-size:2.2em;color:#e9aebc;"></span>
    <h1 style="font-size:2.1em;font-weight:800;color:#22223b;letter-spacing:-1px;">Logs & Historique</h1>
  </div>
  
  <div class="ib-logs-stats-cards" style="display:flex;gap:2em;margin-bottom:2.5em;flex-wrap:wrap;">
    <div class="ib-card premium" style="min-width:180px;">
      <div class="ib-card-label">Total logs</div>
      <div class="ib-card-value" style="font-size:2.3em;"><?php echo $total_logs; ?></div>
    </div>
    <div class="ib-card premium" style="min-width:180px;">
      <div class="ib-card-label">Aujourd'hui</div>
      <div class="ib-card-value" style="font-size:2.3em;"><?php echo $today_count; ?></div>
    </div>
    <div class="ib-card premium" style="min-width:220px;">
      <div class="ib-card-label">Actions principales</div>
      <div style="display:flex;gap:0.5em;align-items:end;flex-wrap:wrap;">
        <?php foreach($top_actions as $action => $count): ?>
          <span class="ib-badge ib-badge-action"><?php echo esc_html($action); ?></span>
          <span style="font-size:1.1em;font-weight:600;color:#bfa2c7;"><?php echo $count; ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  
  <div class="ib-logs-filters" style="margin-bottom:2em;display:flex;gap:1em;align-items:center;flex-wrap:wrap;">
    <label for="ib-logs-search" style="font-weight:600;color:#bfa2c7;">Rechercher :</label>
    <input type="text" id="ib-logs-search" placeholder="Rechercher dans les logs..." 
           style="border-radius:12px;padding:0.5em 1em;font-size:1.08em;border:2px solid #e9aebc22;min-width:250px;">
    <label for="ib-logs-action-filter" style="font-weight:600;color:#bfa2c7;">Action :</label>
    <select id="ib-logs-action-filter" style="border-radius:12px;padding:0.5em 1em;font-size:1.08em;">
      <option value="">Toutes</option>
      <?php foreach(array_keys($actions) as $action): ?>
        <option value="<?php echo esc_attr($action); ?>"><?php echo esc_html($action); ?></option>
      <?php endforeach; ?>
    </select>
    <button id="ib-export-logs" class="ib-btn secondary">
      <span class="dashicons dashicons-download"></span> Export CSV
    </button>
  </div>
  
  <div class="ib-logs-table-container" style="background:#fff;border-radius:18px;box-shadow:0 4px 24px #e9aebc22;overflow:hidden;">
    <div class="ib-logs-table-header" style="padding:1.5em 2em;background:linear-gradient(90deg,#fbeff3 80%,#e9aebc 100%);border-bottom:2px solid #e9aebc22;">
      <h3 style="margin:0;font-size:1.3em;font-weight:700;color:#22223b;">Historique des actions</h3>
    </div>
    <div class="ib-logs-table-wrapper" style="overflow-x:auto;">
      <table class="ib-logs-table" style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#fbeff3;">
            <th style="padding:1.2em 1.5em;text-align:left;font-weight:700;color:#22223b;border-bottom:2px solid #e9aebc22;">Utilisateur</th>
            <th style="padding:1.2em 1.5em;text-align:left;font-weight:700;color:#22223b;border-bottom:2px solid #e9aebc22;">Action</th>
            <th style="padding:1.2em 1.5em;text-align:left;font-weight:700;color:#22223b;border-bottom:2px solid #e9aebc22;">Contexte</th>
            <th style="padding:1.2em 1.5em;text-align:left;font-weight:700;color:#22223b;border-bottom:2px solid #e9aebc22;">Date</th>
            <th style="padding:1.2em 1.5em;text-align:left;font-weight:700;color:#22223b;border-bottom:2px solid #e9aebc22;">IP</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr>
              <td colspan="5" style="padding:3em;text-align:center;color:#bfa2c7;font-style:italic;">Aucun log trouvé.</td>
            </tr>
          <?php else: ?>
            <?php foreach($logs as $log): ?>
            <tr class="ib-log-row" data-action="<?php echo esc_attr($log->action); ?>" style="border-bottom:1px solid #e9aebc11;transition:background 0.18s;">
              <td style="padding:1.2em 1.5em;color:#22223b;font-weight:600;"><?php echo esc_html($log->user_name); ?></td>
              <td style="padding:1.2em 1.5em;">
                <span class="ib-badge ib-badge-action"><?php echo esc_html($log->action); ?></span>
              </td>
              <td style="padding:1.2em 1.5em;color:#bfa2c7;max-width:300px;word-break:break-word;">
                <?php 
                $context = json_decode($log->context, true);
                if ($context && is_array($context)) {
                    echo '<div style="font-size:0.9em;">';
                    foreach($context as $key => $value) {
                        if (is_string($value) && strlen($value) < 50) {
                            echo '<div><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</div>';
                        }
                    }
                    echo '</div>';
                } else {
                    echo esc_html(substr($log->context, 0, 100)) . (strlen($log->context) > 100 ? '...' : '');
                }
                ?>
              </td>
              <td style="padding:1.2em 1.5em;color:#bfa2c7;font-size:0.95em;">
                <?php echo date('d/m/Y H:i', strtotime($log->created_at)); ?>
              </td>
              <td style="padding:1.2em 1.5em;color:#bfa2c7;font-size:0.9em;font-family:monospace;">
                <?php echo esc_html($log->ip); ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
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

jQuery(document).ready(function($){
  // Recherche en temps réel
  $('#ib-logs-search').on('input', function(){
    let search = $(this).val().toLowerCase();
    $('.ib-log-row').each(function(){
      let text = $(this).text().toLowerCase();
      $(this).toggle(text.includes(search));
    });
  });
  
  // Filtre par action
  $('#ib-logs-action-filter').on('change', function(){
    let action = $(this).val();
    $('.ib-log-row').each(function(){
      $(this).toggle(!action || $(this).data('action') === action);
    });
  });
  
  // Export CSV
  $('#ib-export-logs').on('click', function(){
    let btn = $(this);
    btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Export...');
    
    fetch(ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'action=ib_export_logs'
    })
    .then(r => r.blob())
    .then(blob => {
      let url = window.URL.createObjectURL(blob);
      let a = document.createElement('a');
      a.href = url;
      a.download = 'logs_' + new Date().toISOString().split('T')[0] + '.csv';
      a.click();
      window.URL.revokeObjectURL(url);
      showToast('Export CSV terminé !', 'success');
    })
    .catch(() => showToast('Erreur lors de l\'export', 'error'))
    .finally(() => {
      btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Export CSV');
    });
  });
  
  // Hover effects sur les lignes
  $('.ib-log-row').hover(
    function() { $(this).css('background', '#fbeff3'); },
    function() { $(this).css('background', ''); }
  );
});
</script>

<style>
.ib-logs-header { background: linear-gradient(90deg,#fbeff3 80%,#e9aebc 100%); border-radius: 22px; padding: 2em 2em 1.2em 2em; box-shadow: 0 4px 24px #e9aebc33; }
.ib-card.premium { background: #fff; border-radius: 18px; box-shadow: 0 4px 24px #e9aebc22; padding: 2em 1.5em 1.5em 1.5em; display: flex; flex-direction: column; align-items: flex-start; gap: 0.7em; min-height: 120px; position: relative; margin-bottom:0; }
.ib-card .ib-card-label { color: #bfa2c7; font-weight: 600; font-size: 1.08em; }
.ib-card .ib-card-value { font-size: 2.1em; font-weight: 800; color: #22223b; letter-spacing: -1px; }
.ib-badge { display: inline-block; border-radius: 12px; padding: 0.3em 1em; font-size: 0.9em; font-weight: 600; background: #fbeff3; color: #e38ca6; margin-right: 0.5em; }
.ib-badge-action { background: #e9aebc22; color: #e38ca6; }
.ib-btn { background: #e9aebc; color: #fff; border: none; border-radius: 14px; padding: 0.6em 1.3em; font-weight: 600; font-size: 1em; box-shadow: 0 2px 8px #e9aebc22; cursor: pointer; transition: background 0.18s, box-shadow 0.18s, transform 0.12s; display: inline-flex; align-items: center; gap: 0.5em; }
.ib-btn:hover { background: #d89ba9; transform: translateY(-1px); box-shadow: 0 4px 12px #e9aebc33; }
.ib-btn.secondary { background: #bfa2c7; }
.ib-btn.secondary:hover { background: #a88bb0; }
.ib-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.ib-toast { border-radius: 18px; padding: 1.2em 2em 1.2em 1.7em; font-size: 1.13em; margin-bottom: 1.5em; box-shadow: 0 4px 24px #e9aebc33, 0 1.5px 6px #bfa2c733; background: #fff; color: #22223b; border: 2px solid #e9aebc; display: flex; align-items: center; gap: 1em; min-width: 220px; max-width: 340px; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; position: fixed; top: 32px; right: 32px; z-index: 9999; opacity: 0; transform: translateY(-20px) scale(0.98); animation: ib-toast-in 0.35s cubic-bezier(.4,1.4,.6,1) forwards, ib-toast-out 0.35s 3.1s cubic-bezier(.4,1.4,.6,1) forwards; }
.ib-toast.success { background: linear-gradient(90deg, #fbeff3 80%, #e9aebc 100%); color: #22223b; border-color: #e9aebc; }
.ib-toast.error { background: linear-gradient(90deg, #fff3f3 80%, #fca5a5 100%); color: #e87171; border-color: #fca5a5; }
.ib-toast .ib-toast-icon { font-size: 1.5em; display: flex; align-items: center; }
@keyframes ib-toast-in { to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes ib-toast-out { to { opacity: 0; transform: translateY(-20px) scale(0.98); } }
@media (max-width: 900px) { .ib-logs-header { padding: 1.2em 0.7em 1em 0.7em; border-radius: 16px; } .ib-card.premium { padding: 1.2em 0.7em 1em 0.7em; border-radius: 12px; } .ib-logs-table-wrapper { font-size: 0.9em; } }
</style>
