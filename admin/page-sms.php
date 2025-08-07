<?php
// admin/page-sms.php
if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . '../includes/class-clients.php';
$clients = IB_Clients::get_all();
?>
<div class="ib-sms-main">
  <div class="ib-sms-header" style="display:flex;align-items:center;gap:1em;margin-bottom:2em;">
    <span class="dashicons dashicons-phone" style="font-size:2.2em;color:#e9aebc;"></span>
    <h1 style="font-size:2.1em;font-weight:800;color:#22223b;letter-spacing:-1px;">SMS & WhatsApp</h1>
  </div>
  
  <div class="ib-sms-content" style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;margin-bottom:2.5em;">
    <!-- Configuration Twilio -->
    <div class="ib-card premium">
      <div class="ib-card-header" style="display:flex;align-items:center;gap:0.7em;margin-bottom:1.5em;">
        <span class="dashicons dashicons-admin-settings" style="font-size:1.5em;color:#e9aebc;"></span>
        <h3 style="font-size:1.4em;font-weight:700;color:#22223b;margin:0;">Configuration Twilio</h3>
      </div>
      
      <!-- Guide de configuration -->
      <div style="background:#fbeff3;border-radius:12px;padding:1.2em;margin-bottom:1.5em;border-left:4px solid #e9aebc;">
        <h4 style="margin:0 0 0.8em 0;color:#22223b;font-size:1.1em;">📋 Guide de configuration WhatsApp :</h4>
        <ol style="margin:0;padding-left:1.2em;color:#bfa2c7;font-size:0.95em;line-height:1.5;">
          <li>Créez un compte sur <a href="https://www.twilio.com" target="_blank" style="color:#e9aebc;">Twilio.com</a></li>
          <li>Activez WhatsApp Business API dans votre console</li>
          <li>Récupérez votre <strong>Account SID</strong> et <strong>Auth Token</strong></li>
          <li>Configurez votre numéro WhatsApp Business</li>
          <li>Pour les tests, utilisez le numéro sandbox : <code>+14155238886</code></li>
        </ol>
      </div>
      
      <form id="ib-sms-config-form" method="post" style="display:flex;flex-direction:column;gap:1.2em;">
        <div>
          <label style="display:block;font-weight:600;color:#bfa2c7;margin-bottom:0.5em;">Twilio Account SID :</label>
          <input type="text" name="twilio_sid" value="<?php echo esc_attr(get_option('ib_twilio_sid')); ?>" 
                 placeholder="AC1234567890abcdef..." 
                 style="width:100%;padding:0.8em 1em;border:2px solid #e9aebc22;border-radius:12px;font-size:1.08em;background:#fff;">
        </div>
        <div>
          <label style="display:block;font-weight:600;color:#bfa2c7;margin-bottom:0.5em;">Twilio Auth Token :</label>
          <input type="password" name="twilio_token" value="<?php echo esc_attr(get_option('ib_twilio_token')); ?>" 
                 placeholder="Votre token d'authentification..." 
                 style="width:100%;padding:0.8em 1em;border:2px solid #e9aebc22;border-radius:12px;font-size:1.08em;background:#fff;">
        </div>
        <div>
          <label style="display:block;font-weight:600;color:#bfa2c7;margin-bottom:0.5em;">Numéro WhatsApp Business :</label>
          <input type="text" name="twilio_from" value="<?php echo esc_attr(get_option('ib_twilio_from')); ?>" 
                 placeholder="+33612345678" 
                 style="width:100%;padding:0.8em 1em;border:2px solid #e9aebc22;border-radius:12px;font-size:1.08em;background:#fff;">
          <small style="color:#bfa2c7;font-size:0.9em;margin-top:0.3em;display:block;">Format international requis (ex: +33612345678)</small>
        </div>
        <button type="submit" name="save_sms" class="ib-btn primary" style="margin-top:1em;">
          <span class="dashicons dashicons-saved"></span> Enregistrer la configuration
        </button>
      </form>
      
      <!-- Test de configuration -->
      <div style="margin-top:1.5em;padding-top:1.5em;border-top:1px solid #e9aebc22;">
        <button id="ib-test-twilio" class="ib-btn secondary" style="width:100%;">
          <span class="dashicons dashicons-testing"></span> Tester la configuration Twilio
        </button>
      </div>
    </div>

    <!-- Envoi de message -->
    <div class="ib-card premium">
      <div class="ib-card-header" style="display:flex;align-items:center;gap:0.7em;margin-bottom:1.5em;">
        <span class="dashicons dashicons-email-alt" style="font-size:1.5em;color:#e9aebc;"></span>
        <h3 style="font-size:1.4em;font-weight:700;color:#22223b;margin:0;">Envoyer un message</h3>
      </div>
      <form id="ib-send-message-form" style="display:flex;flex-direction:column;gap:1.2em;">
        <div>
          <label style="display:block;font-weight:600;color:#bfa2c7;margin-bottom:0.5em;">Type :</label>
          <select id="ib-message-type" style="width:100%;padding:0.8em 1em;border:2px solid #e9aebc22;border-radius:12px;font-size:1.08em;background:#fff;">
            <option value="sms">SMS</option>
            <option value="whatsapp">WhatsApp</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-weight:600;color:#bfa2c7;margin-bottom:0.5em;">Client :</label>
          <select id="ib-message-client" style="width:100%;padding:0.8em 1em;border:2px solid #e9aebc22;border-radius:12px;font-size:1.08em;background:#fff;">
            <option value="">Sélectionner un client...</option>
            <?php foreach($clients as $client): ?>
              <option value="<?php echo $client->id; ?>" data-phone="<?php echo esc_attr($client->phone); ?>">
                <?php echo esc_html($client->name); ?> (<?php echo esc_html($client->phone); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block;font-weight:600;color:#bfa2c7;margin-bottom:0.5em;">Message :</label>
          <textarea id="ib-message-text" rows="4" placeholder="Votre message..." 
                    style="width:100%;padding:0.8em 1em;border:2px solid #e9aebc22;border-radius:12px;font-size:1.08em;background:#fff;resize:vertical;"></textarea>
        </div>
        <button type="submit" class="ib-btn success">
          <span class="dashicons dashicons-arrow-right-alt"></span> Envoyer le message
        </button>
      </form>
    </div>
  </div>

  <!-- Historique des envois -->
  <div class="ib-card premium">
    <div class="ib-card-header" style="display:flex;align-items:center;gap:0.7em;margin-bottom:1.5em;">
      <span class="dashicons dashicons-list-view" style="font-size:1.5em;color:#e9aebc;"></span>
      <h3 style="font-size:1.4em;font-weight:700;color:#22223b;margin:0;">Historique des envois</h3>
    </div>
    <div id="ib-sms-history" style="text-align:center;padding:2em;color:#bfa2c7;">
      <span class="dashicons dashicons-clock" style="font-size:3em;color:#e9aebc22;"></span>
      <p style="margin-top:1em;font-size:1.1em;">Historique des messages envoyés</p>
    </div>
  </div>

  <!-- Configuration WhatsApp (en bas de page, card premium harmonisée) -->
  <div class="ib-card premium" style="margin-top:3em;">
    <div class="ib-card-header" style="display:flex;align-items:center;gap:0.7em;margin-bottom:1.5em;">
      <span class="dashicons dashicons-whatsapp" style="font-size:1.5em;color:#7bd389;"></span>
      <h3 style="font-size:1.4em;font-weight:700;color:#22223b;margin:0;">Configuration WhatsApp</h3>
    </div>
    <div style="background:#e7fbe9;border-radius:12px;padding:1.2em;margin-bottom:1.5em;border-left:4px solid #7bd389;">
      <h4 style="margin:0 0 0.8em 0;color:#22223b;font-size:1.1em;">💡 Modes d'envoi WhatsApp :</h4>
      <ul style="margin:0;padding-left:1.2em;color:#7bd389;font-size:0.97em;line-height:1.5;">
        <li><b>Gratuit</b> : WhatsApp Web ou API gratuite (Selenium, APIs publiques)</li>
        <li><b>Payant</b> : Twilio WhatsApp Business API</li>
      </ul>
    </div>
    <form id="ib-whatsapp-config-form" method="post" style="display:flex;flex-direction:column;gap:1.2em;">
      <div>
        <label style="display:block;font-weight:600;color:#7bd389;margin-bottom:0.5em;">Mode d'envoi WhatsApp :</label>
        <div style="display:flex;gap:2em;align-items:center;">
          <label style="display:flex;align-items:center;gap:0.5em;cursor:pointer;">
            <input type="radio" name="whatsapp_mode" value="free" <?php echo get_option('ib_whatsapp_free_mode', false) ? 'checked' : ''; ?> style="accent-color:#7bd389;width:1.2em;height:1.2em;">
            <span style="color:#388e3c;font-weight:500;">Gratuit <span style="font-size:0.95em;font-weight:400;">(Web/API gratuite)</span></span>
          </label>
          <label style="display:flex;align-items:center;gap:0.5em;cursor:pointer;">
            <input type="radio" name="whatsapp_mode" value="twilio" <?php echo !get_option('ib_whatsapp_free_mode', false) ? 'checked' : ''; ?> style="accent-color:#e9aebc;width:1.2em;height:1.2em;">
            <span style="color:#bfa2c7;font-weight:500;">Payant <span style="font-size:0.95em;font-weight:400;">(Twilio API)</span></span>
          </label>
        </div>
      </div>
      <div id="twilio-config" class="<?php echo get_option('ib_whatsapp_free_mode', false) ? 'hidden' : ''; ?>">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.2em;">
          <div>
            <label style="display:block;font-weight:600;color:#bfa2c7;margin-bottom:0.5em;">Account SID :</label>
            <input type="text" name="twilio_sid" value="<?php echo esc_attr(get_option('ib_twilio_sid', '')); ?>" placeholder="Votre SID Twilio" style="width:100%;padding:0.8em 1em;border:2px solid #e9aebc22;border-radius:12px;font-size:1.08em;background:#fff;">
          </div>
          <div>
            <label style="display:block;font-weight:600;color:#bfa2c7;margin-bottom:0.5em;">Auth Token :</label>
            <input type="password" name="twilio_token" value="<?php echo esc_attr(get_option('ib_twilio_token', '')); ?>" placeholder="Votre token Twilio" style="width:100%;padding:0.8em 1em;border:2px solid #e9aebc22;border-radius:12px;font-size:1.08em;background:#fff;">
          </div>
          <div>
            <label style="display:block;font-weight:600;color:#bfa2c7;margin-bottom:0.5em;">Numéro WhatsApp :</label>
            <input type="text" name="twilio_whatsapp_from" value="<?php echo esc_attr(get_option('ib_twilio_whatsapp_from', '')); ?>" placeholder="+33123456789" style="width:100%;padding:0.8em 1em;border:2px solid #e9aebc22;border-radius:12px;font-size:1.08em;background:#fff;">
          </div>
        </div>
      </div>
      <div id="free-config" class="<?php echo !get_option('ib_whatsapp_free_mode', false) ? 'hidden' : ''; ?>">
        <div style="background:#f5fff7;border-radius:10px;padding:1em 1.2em;margin-top:0.5em;border-left:4px solid #7bd389;">
          <b style="color:#388e3c;">Mode Gratuit activé :</b>
          <ul style="margin:0.5em 0 0 1.2em;padding:0;color:#388e3c;font-size:0.97em;">
            <li>Envoi via WhatsApp Web (Selenium) ou APIs gratuites</li>
            <li>Pas de coût, mais fonctionnalités limitées</li>
          </ul>
          <button type="button" onclick="installSelenium()" style="margin-top:0.7em;padding:0.5em 1.2em;background:#7bd389;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Installer Selenium</button>
        </div>
      </div>
      <button type="button" onclick="saveSmsConfig()" class="ib-btn primary" style="margin-top:1.5em;align-self:flex-end;">
        <span class="dashicons dashicons-saved"></span> Sauvegarder la configuration
      </button>
    </form>
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
  // Envoi de message AJAX
  $('#ib-send-message-form').on('submit', function(e){
    e.preventDefault();
    let clientId = $('#ib-message-client').val();
    let messageType = $('#ib-message-type').val();
    let messageText = $('#ib-message-text').val();
    
    if (!clientId || !messageText) {
      showToast('Veuillez remplir tous les champs', 'error');
      return;
    }
    
    let btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Envoi...');
    
    fetch(ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'action=ib_send_message&client_id=' + encodeURIComponent(clientId) + 
            '&type=' + encodeURIComponent(messageType) + 
            '&message=' + encodeURIComponent(messageText)
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showToast('Message envoyé avec succès !', 'success');
        $('#ib-message-text').val('');
        $('#ib-message-client').val('');
      } else {
        showToast(res.data && res.data.message ? res.data.message : 'Erreur lors de l\'envoi', 'error');
      }
    })
    .catch(() => showToast('Erreur AJAX', 'error'))
    .finally(() => {
      btn.prop('disabled', false).html('<span class="dashicons dashicons-arrow-right-alt"></span> Envoyer le message');
    });
  });
  
  // Sauvegarde configuration AJAX
  $('#ib-sms-config-form').on('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);
    formData.append('action', 'ib_save_sms_config');
    
    let btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Sauvegarde...');
    
    fetch(ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showToast('Configuration sauvegardée !', 'success');
      } else {
        showToast(res.data && res.data.message ? res.data.message : 'Erreur lors de la sauvegarde', 'error');
      }
    })
    .catch(() => showToast('Erreur AJAX', 'error'))
    .finally(() => {
      btn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Enregistrer la configuration');
    });
  });
  
  // Test de configuration Twilio
  $('#ib-test-twilio').on('click', function(){
    let btn = $(this);
    btn.prop('disabled', true).html('<span class="dashicons dashicons-update"></span> Test en cours...');
    
    fetch(ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'action=ib_test_twilio_config'
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showToast('Configuration Twilio valide !', 'success');
      } else {
        showToast(res.data && res.data.message ? res.data.message : 'Configuration invalide', 'error');
      }
    })
    .catch(() => showToast('Erreur lors du test', 'error'))
    .finally(() => {
      btn.prop('disabled', false).html('<span class="dashicons dashicons-testing"></span> Tester la configuration Twilio');
    });
  });

  // Gestion du mode WhatsApp
  document.querySelectorAll('input[name="whatsapp_mode"]').forEach(radio => {
    radio.addEventListener('change', function() {
      const isFreeMode = this.value === 'free';
      document.getElementById('twilio-config').classList.toggle('hidden', isFreeMode);
      document.getElementById('free-config').classList.toggle('hidden', !isFreeMode);
    });
  });
  
  // Installation de Selenium
  window.installSelenium = function() {
    showToast('Installation de Selenium en cours...', 'info');
    
    fetch(ajaxurl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'ib_install_selenium',
        nonce: '<?php echo wp_create_nonce("ib_install_selenium"); ?>'
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('Selenium installé avec succès !', 'success');
      } else {
        showToast('Erreur lors de l\'installation : ' + data.data.message, 'error');
      }
    })
    .catch(error => {
      showToast('Erreur de connexion : ' + error.message, 'error');
    });
  };
  
  // Sauvegarde de la configuration SMS
  window.saveSmsConfig = function() {
    const formData = new FormData();
    formData.append('action', 'ib_save_sms_config');
    formData.append('nonce', '<?php echo wp_create_nonce("ib_save_sms_config"); ?>');
    
    // Configuration SMS
    formData.append('sms_enabled', document.getElementById('sms_enabled').checked ? '1' : '0');
    formData.append('sms_api_key', document.getElementById('sms_api_key').value);
    formData.append('sms_sender', document.getElementById('sms_sender').value);
    
    // Configuration WhatsApp
    const whatsappMode = document.querySelector('input[name="whatsapp_mode"]:checked').value;
    formData.append('whatsapp_free_mode', whatsappMode === 'free' ? '1' : '0');
    
    if (whatsappMode === 'twilio') {
      formData.append('twilio_sid', document.querySelector('input[name="twilio_sid"]').value);
      formData.append('twilio_token', document.querySelector('input[name="twilio_token"]').value);
      formData.append('twilio_whatsapp_from', document.querySelector('input[name="twilio_whatsapp_from"]').value);
    }
    
    showToast('Sauvegarde en cours...', 'info');
    
    fetch(ajaxurl, {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showToast('Configuration sauvegardée avec succès !', 'success');
      } else {
        showToast('Erreur lors de la sauvegarde : ' + data.data.message, 'error');
      }
    })
    .catch(error => {
      showToast('Erreur de connexion : ' + error.message, 'error');
    });
  };
});
</script>

<style>
.ib-sms-header { background: linear-gradient(90deg,#fbeff3 80%,#e9aebc 100%); border-radius: 22px; padding: 2em 2em 1.2em 2em; box-shadow: 0 4px 24px #e9aebc33; }
.ib-card.premium { background: #fff; border-radius: 18px; box-shadow: 0 4px 24px #e9aebc22; padding: 2em 1.5em 1.5em 1.5em; display: flex; flex-direction: column; gap: 0.7em; min-height: 120px; position: relative; margin-bottom:0; }
.ib-card .ib-card-header { display: flex; align-items: center; gap: 0.7em; margin-bottom: 1.5em; }
.ib-btn { background: #e9aebc; color: #fff; border: none; border-radius: 14px; padding: 0.8em 1.5em; font-weight: 600; font-size: 1.08em; box-shadow: 0 2px 8px #e9aebc22; cursor: pointer; transition: background 0.18s, box-shadow 0.18s, transform 0.12s; display: inline-flex; align-items: center; gap: 0.5em; }
.ib-btn:hover { background: #d89ba9; transform: translateY(-1px); box-shadow: 0 4px 12px #e9aebc33; }
.ib-btn.primary { background: #e9aebc; }
.ib-btn.success { background: #4caf50; }
.ib-btn.success:hover { background: #45a049; }
.ib-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.ib-toast { border-radius: 18px; padding: 1.2em 2em 1.2em 1.7em; font-size: 1.13em; margin-bottom: 1.5em; box-shadow: 0 4px 24px #e9aebc33, 0 1.5px 6px #bfa2c733; background: #fff; color: #22223b; border: 2px solid #e9aebc; display: flex; align-items: center; gap: 1em; min-width: 220px; max-width: 340px; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; position: fixed; top: 32px; right: 32px; z-index: 9999; opacity: 0; transform: translateY(-20px) scale(0.98); animation: ib-toast-in 0.35s cubic-bezier(.4,1.4,.6,1) forwards, ib-toast-out 0.35s 3.1s cubic-bezier(.4,1.4,.6,1) forwards; }
.ib-toast.success { background: linear-gradient(90deg, #fbeff3 80%, #e9aebc 100%); color: #22223b; border-color: #e9aebc; }
.ib-toast.error { background: linear-gradient(90deg, #fff3f3 80%, #fca5a5 100%); color: #e87171; border-color: #fca5a5; }
.ib-toast .ib-toast-icon { font-size: 1.5em; display: flex; align-items: center; }
@keyframes ib-toast-in { to { opacity: 1; transform: translateY(0) scale(1); } }
@keyframes ib-toast-out { to { opacity: 0; transform: translateY(-20px) scale(0.98); } }
@media (max-width: 900px) { .ib-sms-content { grid-template-columns: 1fr; } .ib-sms-header { padding: 1.2em 0.7em 1em 0.7em; border-radius: 16px; } .ib-card.premium { padding: 1.2em 0.7em 1em 0.7em; border-radius: 12px; } }
</style>
