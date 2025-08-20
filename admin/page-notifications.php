<?php
if (!defined('ABSPATH')) exit;

// Inclure les notifications
require_once plugin_dir_path(dirname(__FILE__)) . 'includes/notifications.php';

// Notifications stockées dans les options
$notify_client_confirm = get_option('ib_notify_client_confirm', '');
$notify_client_cancel = get_option('ib_notify_client_cancel', '');
$notify_client_thankyou = get_option('ib_notify_client_thankyou', '');
$notify_admin_confirm = get_option('ib_notify_admin_confirm', '');
$notify_admin_cancel = get_option('ib_notify_admin_cancel', '');
$notify_recept_confirm = get_option('ib_notify_recept_confirm', '');
$notify_recept_cancel = get_option('ib_notify_recept_cancel', '');
$notify_reminder = get_option('ib_notify_reminder', '');
$test_feedback = '';
$test_email = ''; // Initialisation de la variable

// Traitement des notifications avancées
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ib_save_notifications_advanced'])) {
    update_option('ib_push_enable', isset($_POST['ib_push_enable']) ? 1 : 0);
    update_option('ib_whatsapp_enable', isset($_POST['ib_whatsapp_enable']) ? 1 : 0);
    update_option('ib_whatsapp_token', sanitize_text_field($_POST['ib_whatsapp_token'] ?? ''));
    update_option('ib_reminder_enable', isset($_POST['ib_reminder_enable']) ? 1 : 0);
    update_option('ib_reminder_time', sanitize_text_field($_POST['ib_reminder_time'] ?? '09:00'));
    
    // Redirection pour éviter la soumission multiple
    wp_redirect(admin_url('admin.php?page=institut-booking-notifications&saved=1'));
    exit;
}

// Test d'envoi de rappel
if ((isset($_POST['test_reminder']) && !empty($_POST['test_booking_id'])) || 
    (isset($_POST['test_reminder_email_btn']) && !empty($_POST['test_reminder_email']))) {
    
    if (isset($_POST['test_reminder_email_btn'])) {
        // Créer une réservation factice pour le test
        $test_email = sanitize_email($_POST['test_reminder_email']);
        global $wpdb;
        
        // Créer une réservation factice
        $wpdb->insert(
            $wpdb->prefix . 'ib_bookings',
            [
                'client_name' => 'Client Test',
                'client_email' => $test_email,
                'service_id' => 1,
                'date' => date('Y-m-d', strtotime('+1 day')),
                'start_time' => '14:00:00',
                'end_time' => '15:00:00',
                'status' => 'confirmee',
                'created_at' => current_time('mysql')
            ]
        );
        $booking_id = $wpdb->insert_id;
        $is_test = true;
    } else {
        $booking_id = intval($_POST['test_booking_id']);
        $is_test = false;
    }
    
    $test_result = IB_Notifications::send_reminder($booking_id);
    
    if ($is_test) {
        // Supprimer la réservation de test après envoi
        $wpdb->delete($wpdb->prefix . 'ib_bookings', ['id' => $booking_id]);
        
        $test_feedback = $test_result 
            ? '<div class="notice notice-success"><p>✅ Email de test envoyé avec succès à ' . esc_html($test_email) . '</p></div>'
            : '<div class="notice notice-error"><p>❌ Erreur lors de l\'envoi du test à ' . esc_html($test_email) . '</p></div>';
    } else {
        $test_feedback = $test_result 
            ? '<div class="notice notice-success"><p>✅ Email de rappel envoyé avec succès pour la réservation #' . $booking_id . '</p></div>'
            : '<div class="notice notice-error"><p>❌ Erreur lors de l\'envoi du rappel pour la réservation #' . $booking_id . '</p></div>';
    }
}

// Enregistrement des modèles d'emails
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['send_test'])) {
    update_option('ib_notify_client_confirm', wp_unslash($_POST['notify_client_confirm']));
    update_option('ib_notify_client_cancel', wp_unslash($_POST['notify_client_cancel']));
    update_option('ib_notify_client_thankyou', wp_unslash($_POST['notify_client_thankyou']));
    update_option('ib_notify_admin_confirm', wp_unslash($_POST['notify_admin_confirm']));
    update_option('ib_notify_admin_cancel', wp_unslash($_POST['notify_admin_cancel']));
    update_option('ib_notify_recept_confirm', wp_unslash($_POST['notify_recept_confirm']));
    update_option('ib_notify_recept_cancel', wp_unslash($_POST['notify_recept_cancel']));
    update_option('ib_notify_reminder', wp_unslash($_POST['notify_reminder']));
    // Recharger les valeurs pour affichage
    $notify_client_confirm = get_option('ib_notify_client_confirm', '');
    $notify_client_cancel = get_option('ib_notify_client_cancel', '');
    $notify_client_thankyou = get_option('ib_notify_client_thankyou', '');
    $notify_admin_confirm = get_option('ib_notify_admin_confirm', '');
    $notify_admin_cancel = get_option('ib_notify_admin_cancel', '');
    $notify_recept_confirm = get_option('ib_notify_recept_confirm', '');
    $notify_recept_cancel = get_option('ib_notify_recept_cancel', '');
    $notify_reminder = get_option('ib_notify_reminder', '');
    $test_feedback = '<div class="ib-toast success ib-fade-in" style="margin-bottom:2em;"><span class="dashicons dashicons-yes"></span> Notifications enregistrées !</div>';
}

// Envoi de test
if (isset($_POST['send_test'])) {
    $test_email = sanitize_email($_POST['test_email']);
    $type = sanitize_text_field($_POST['test_type']);
    $subject = 'Test notification ' . ucfirst($type);
    $vars = [
        '{client_name}' => 'Jean Dupont',
        '{service}' => 'Massage Relaxant',
        '{service_name}' => 'Massage Relaxant',
        '{date}' => '2024-07-01',
        '{time}' => '14:00',
        '{company}' => 'Institut Booking',
        '{recept_name}' => 'Sophie',
        '{admin_name}' => 'Admin',
    ];
    $body = '';
    if ($type === 'client_confirm') $body = strtr($notify_client_confirm, $vars);
    if ($type === 'client_cancel') $body = strtr($notify_client_cancel, $vars);
    if ($type === 'client_thankyou') $body = strtr($notify_client_thankyou, $vars);
    if ($type === 'admin_confirm') $body = strtr($notify_admin_confirm, $vars);
    if ($type === 'admin_cancel') $body = strtr($notify_admin_cancel, $vars);
    if ($type === 'recept_confirm') $body = strtr($notify_recept_confirm, $vars);
    if ($type === 'recept_cancel') $body = strtr($notify_recept_cancel, $vars);
    if ($type === 'reminder') $body = strtr($notify_reminder, $vars);
    if ($body && is_email($test_email)) {
        $sent = wp_mail($test_email, $subject, $body);
        if ($sent) {
            $test_feedback = '<div class="ib-toast success ib-fade-in" style="margin-bottom:2em;"><span class="dashicons dashicons-yes"></span> Email de test envoyé à '.esc_html($test_email).' !</div>';
        } else {
            $test_feedback = '<div class="ib-toast error ib-fade-in" style="margin-bottom:2em;"><span class="dashicons dashicons-warning"></span> Erreur lors de l\'envoi du mail.</div>';
        }
    } else {
        $test_feedback = '<div class="ib-toast error ib-fade-in" style="margin-bottom:2em;"><span class="dashicons dashicons-warning"></span> Adresse email invalide ou contenu vide.</div>';
    }
}
?>
<style>
body, .wrap, .ib-admin-content {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
  font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  min-height: 100vh;
}

/* Variables CSS modernes */
:root {
  --primary-color: #e9aebc;
  --primary-light: #764ba2;
  --secondary-color: #f093fb;
  --accent-color: #4facfe;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --error-color: #ef4444;
  --text-dark: #1e293b;
  --text-light: #64748b;
  --border-light: #e2e8f0;
  --bg-card: rgba(255, 255, 255, 0.95);
  --shadow-soft: 0 4px 20px #e9aebc;
  --shadow-medium: 0 8px 32px #e9aebc;
  --shadow-strong: 0 16px 48px #e9aebc;
  --radius-sm: 12px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  --gradient-accent: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

/* Onglets modernes avec design glassmorphism */
.ib-tabs {
  background: var(--bg-card);
  backdrop-filter: blur(20px);
  border: 1px solid #e9aebc;
  box-shadow: var(--shadow-soft);
  padding: 0.5em;
  margin-bottom: 2em;
  border-radius: var(--radius-lg);
  display: flex;
  gap: 0.5em;
  flex-wrap: wrap;
}

.ib-tab {
  border: none;
  background: transparent;
  color: var(--text-light);
  border-radius: var(--radius-md);
  font-size: 1.05em;
  font-weight: 600;
  padding: 0.8em 1.8em;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  letter-spacing: 0.025em;
  font-family: inherit;
  position: relative;
  cursor: pointer;
  overflow: hidden;
}

.ib-tab::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--gradient-primary);
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: -1;
}

.ib-tab.active {
  color: #fff;
  transform: translateY(-2px);
  box-shadow: var(--shadow-medium);
}

.ib-tab.active::before {
  opacity: 1;
}

.ib-tab:not(.active):hover {
  color: var(--primary-color);
  transform: translateY(-1px);
  box-shadow: var(--shadow-soft);
}
/* Sections de notifications avec glassmorphism moderne */
.ib-notif-section {
  background: var(--bg-card);
  backdrop-filter: blur(24px);
  border: 1px solid #e9aebc;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-medium);
  padding: 2.5em;
  margin-bottom: 2em;
  position: relative;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ib-notif-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--gradient-primary);
  opacity: 0.8;
}

.ib-notif-section:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-strong);
}

/* Titre modernisé avec icônes */
.ib-notif-title {
  font-size: 1.25em;
  font-weight: 700;
  color: var(--text-dark);
  display: flex;
  align-items: center;
  gap: 0.75em;
  margin-bottom: 1.5em;
  padding-bottom: 1em;
  border-bottom: 2px solid var(--border-light);
  font-family: inherit;
}

.ib-notif-title .dashicons {
  font-size: 1.3em;
  color: var(--primary-color);
  opacity: 0.9;
}

/* Badge modernisé */
.ib-badge {
  background: var(--gradient-accent);
  color: #fff;
  border-radius: var(--radius-sm);
  padding: 0.4em 1.2em;
  font-size: 0.9em;
  font-weight: 600;
  margin-left: auto;
  box-shadow: var(--shadow-soft);
  font-family: inherit;
  letter-spacing: 0.025em;
  text-transform: uppercase;
}
/* Zone de test modernisée */
.ib-test-row {
  display: flex;
  align-items: center;
  gap: 1em;
  margin: 0 0 2.5em 0;
  padding: 1.5em;
  background: var(--bg-card);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-soft);
  border: 1px solid #e9aebc;
}

/* Champ email modernisé */
.ib-test-email {
  border: 2px solid #e9aebc;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.9);
  padding: 1em 1.5em;
  font-size: 1.05em;
  color: var(--text-dark);
  font-weight: 500;
  font-family: inherit;
  box-shadow: var(--shadow-soft);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  height: 52px;
  min-width: 280px;
  flex: 1;
}

.ib-test-email:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 4px #e9aebc;
  background: #fff;
  transform: translateY(-2px);
}

.ib-test-email::placeholder {
  color: var(--text-light);
  opacity: 0.8;
  font-weight: 400;
}

/* Bouton de test modernisé */
.ib-btn-test {
  display: flex;
  align-items: center;
  gap: 0.7em;
  background: var(--gradient-primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-md);
  padding: 1em 2.2em;
  font-size: 1.05em;
  font-weight: 600;
  box-shadow: var(--shadow-medium);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  height: 52px;
  letter-spacing: 0.025em;
  position: relative;
  overflow: hidden;
}

.ib-btn-test::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, #9ca3af, transparent);
  transition: left 0.5s;
}

.ib-btn-test:hover::before {
  left: 100%;
}

.ib-btn-test:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: var(--shadow-strong);
}

.ib-btn-test .dashicons {
  font-size: 1.2em;
}

/* Champs de saisie et textarea modernisés */
.ib-input, .ib-notif-section textarea {
  border: 2px solid #e9aebc;
  border-radius: var(--radius-md);
  padding: 1.2em 1.5em;
  font-size: 1.05em;
  background: rgba(255, 255, 255, 0.9);
  color: var(--text-dark);
  font-family: inherit;
  font-weight: 500;
  box-shadow: var(--shadow-soft);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  width: 100%;
  resize: vertical;
  min-height: 120px;
}

.ib-input:focus, .ib-notif-section textarea:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 4px #e9aebc;
  background: #fff;
  transform: translateY(-2px);
}

.ib-input::placeholder, .ib-notif-section textarea::placeholder {
  color: var(--text-light);
  opacity: 0.7;
  font-weight: 400;
}

/* Labels modernisés */
label {
  display: block;
  font-weight: 600;
  color: var(--text-dark) !important;
  margin-bottom: 0.8em !important;
  font-size: 1.1em;
  letter-spacing: 0.025em;
}

/* Variables et actions */
.ib-notif-vars {
  background: #e9aebc;
  border: 1px solid#e9aebc;
  border-radius: var(--radius-sm);
  padding: 0.8em 1.2em;
  margin: 0.8em 0 1.5em 0;
  color: var(--text-light);
  font-size: 0.95em;
  font-weight: 500;
}

.ib-notif-actions {
  margin: 1.5em 0 2em 0;
}

/* Bouton d'enregistrement modernisé */
.ib-btn-save {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.8em;
  background: var(--gradient-secondary);
  color: #fff;
  border: none;
  border-radius: var(--radius-lg);
  padding: 1.3em 3em;
  font-size: 1.2em;
  font-weight: 700;
  box-shadow: var(--shadow-strong);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  margin: 2em auto 0;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  overflow: hidden;
}

.ib-btn-save::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%; 
color: #e9aebc;
  transition: left 0.6s;
}

.ib-btn-save:hover::before {
  left: 100%;
}

.ib-btn-save:hover {
  transform: translateY(-4px) scale(1.03);
  box-shadow: 0 20px 60px #e9aebc;
}

.ib-btn-save .dashicons {
  font-size: 1.3em;
}
/* Messages toast modernisés */
.ib-toast {
  position: fixed;
  top: 2em;
  right: 2em;
  background: var(--bg-card);
  backdrop-filter: blur(24px);
  border: 1px solid #e9aebc;
  border-radius: var(--radius-md);
  padding: 1.2em 1.8em;
  box-shadow: var(--shadow-strong);
  color: var(--text-dark);
  font-weight: 600;
  font-size: 1.05em;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 0.8em;
  min-width: 300px;
  animation: toastSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.ib-toast.success {
  border-left: 4px solid var(--success-color);
}

.ib-toast.success .dashicons {
  color: var(--success-color);
  font-size: 1.2em;
}

.ib-toast.error {
  border-left: 4px solid var(--error-color);
}

.ib-toast.error .dashicons {
  color: var(--error-color);
  font-size: 1.2em;
}

@keyframes toastSlideIn {
  from {
    opacity: 0;
    transform: translateX(100%) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
}

/* Responsive Design */
@media (max-width: 1024px) {
  .ib-tabs {
    justify-content: center;
  }
  
  .ib-tab {
    padding: 0.7em 1.4em;
    font-size: 1em;
  }
}

@media (max-width: 900px) {
  .ib-notif-section {
    padding: 2em 1.5em;
    margin-bottom: 1.5em;
  }
  
  .ib-btn-save {
    position: relative;
    width: 100%;
    margin: 2em 0 0 0;
    padding: 1.2em 2em;
    font-size: 1.1em;
  }
  
  .ib-test-row {
    padding: 1.2em;
    margin-bottom: 2em;
  }
  
  .ib-toast {
    right: 1em;
    left: 1em;
    min-width: 0;
  }
}

@media (max-width: 700px) {
  .ib-tabs {
    flex-direction: column;
    gap: 0.3em;
  }
  
  .ib-tab {
    width: 100%;
    padding: 0.8em 1em;
    text-align: center;
  }
  
  .ib-test-row {
    flex-direction: column;
    gap: 1em;
    padding: 1em;
  }
  
  .ib-test-email {
    width: 100%;
    min-width: 0;
    height: 48px;
  }
  
  .ib-btn-test {
    width: 100%;
    height: 48px;
    justify-content: center;
  }
  
  .ib-notif-section {
    padding: 1.5em 1em;
  }
  
  .ib-notif-title {
    font-size: 1.1em;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5em;
  }
  
  .ib-badge {
    margin-left: 0;
    align-self: flex-start;
  }
}

@media (max-width: 480px) {
  .ib-notif-section {
    padding: 1em 0.8em;
    margin-bottom: 1em;
  }
  
  .ib-input, .ib-notif-section textarea {
    padding: 1em;
    font-size: 1em;
  }
  
  .ib-btn-save {
    padding: 1em 1.5em;
    font-size: 1em;
  }
  
  .ib-toast {
    top: 1em;
    right: 0.5em;
    left: 0.5em;
    padding: 1em;
    font-size: 1em;
  }
}
</style>

<div class="ib-admin-main">
    <div class="ib-admin-header">
        <h1><span class="dashicons dashicons-email"></span> Notifications et Rappels</h1>
        <p style="color: #e9aebc; margin-top: 0.5em;">Configurez vos paramètres de notifications et d'emails automatiques</p>
    </div>
    <div class="ib-admin-content ib-notif-centered">
        <?php if (!empty($test_feedback)) echo $test_feedback; ?>
        <div class="ib-tabs">
            <button type="button" class="ib-tab" data-tab="client">Client</button>
            <button type="button" class="ib-tab" data-tab="recept">Réceptionniste</button>
            <button type="button" class="ib-tab" data-tab="admin">Admin</button>
            <button type="button" class="ib-tab" data-tab="reminder">Rappel</button>
            <button type="button" class="ib-tab" data-tab="advanced">Paramètres avancés</button>
        </div>
        <form method="post" class="ib-form ib-notif-form" style="max-width:700px;margin:auto;">
            <div class="ib-test-row">
                <input type="email" class="ib-test-email" name="test_email" placeholder="Saisissez l'adresse pour recevoir un test" value="<?php echo esc_attr($test_email); ?>" />
                <button type="submit" name="send_test" class="ib-btn-test"><span class="dashicons dashicons-email"></span> Envoyer un test</button>
            </div>
            <!-- Onglet Client -->
            <div class="ib-notif-section ib-tab-content" data-tab-content="client">
                <div class="ib-notif-title">
                    <span class="dashicons dashicons-admin-users"></span> 
                    Client 
                    <span class="ib-badge">Destinataire</span>
                </div>
                <label style="display:block;font-weight:600;color:#1e293b;margin-bottom:0.5em;"><b>Confirmation</b></label>
                <textarea name="notify_client_confirm" rows="4" class="ib-input" placeholder="Bonjour {client_name}, votre réservation pour {service} le {date} à {time} est confirmée..."><?php echo esc_textarea($notify_client_confirm); ?></textarea>
                <div class="ib-notif-vars">Variables disponibles : {client_name}, {service}, {date}, {time}, {company}</div>
                <div class="ib-notif-actions">
                    <button type="submit" name="send_test" value="1" class="ib-btn-test" onclick="this.form.test_type.value='client_confirm'">Envoyer un test</button>
                </div>
                <label style="display:block;font-weight:600;color:#1e293b;margin:1.5em 0 0.5em 0;"><b>Annulation</b></label>
                <textarea name="notify_client_cancel" rows="4" class="ib-input" placeholder="Bonjour {client_name}, votre réservation pour {service} le {date} à {time} a été annulée..."><?php echo esc_textarea($notify_client_cancel); ?></textarea>
                <div class="ib-notif-vars">Variables disponibles : {client_name}, {service}, {date}, {time}, {company}</div>
                <div class="ib-notif-actions">
                    <button type="submit" name="send_test" value="1" class="ib-btn-test" onclick="this.form.test_type.value='client_cancel'">Envoyer un test</button>
                </div>
                <label style="display:block;font-weight:600;color:#1e293b;margin:1.5em 0 0.5em 0;"><b>Remerciement (après réservation)</b></label>
                <textarea name="notify_client_thankyou" rows="4" class="ib-input" placeholder="Bonjour {client_name}, nous avons bien reçu votre demande de réservation pour {service_name}..."><?php echo esc_textarea($notify_client_thankyou); ?></textarea>
                <div class="ib-notif-vars">Variables disponibles : {client_name}, {service_name}, {company}</div>
                <div class="ib-notif-actions">
                    <button type="submit" name="send_test" value="1" class="ib-btn-test" onclick="this.form.test_type.value='client_thankyou'">Envoyer un test</button>
                </div>
            </div>
            <!-- Onglet Réceptionniste -->
            <div class="ib-notif-section ib-tab-content" data-tab-content="recept" style="display:none;">
                <div class="ib-notif-title">
                    <span class="dashicons dashicons-businesswoman"></span> 
                    Réceptionniste 
                    <span class="ib-badge">Destinataire</span>
                </div>
                <label style="display:block;font-weight:600;color:#1e293b;margin-bottom:0.5em;"><b>Confirmation</b></label>
                <textarea name="notify_recept_confirm" rows="4" class="ib-input" placeholder="Bonjour {recept_name}, nouvelle réservation confirmée pour {service} le {date} à {time}..."><?php echo esc_textarea($notify_recept_confirm); ?></textarea>
                <div class="ib-notif-vars">Variables disponibles : {recept_name}, {service}, {date}, {time}, {client_name}, {company}</div>
                <div class="ib-notif-actions">
                    <button type="submit" name="send_test" value="1" class="ib-btn-test" onclick="this.form.test_type.value='recept_confirm'">Envoyer un test</button>
                </div>
                <label style="display:block;font-weight:600;color:#1e293b;margin:1.5em 0 0.5em 0;"><b>Annulation</b></label>
                <textarea name="notify_recept_cancel" rows="4" class="ib-input" placeholder="Bonjour {recept_name}, réservation annulée pour {service} le {date} à {time}..."><?php echo esc_textarea($notify_recept_cancel); ?></textarea>
                <div class="ib-notif-vars">Variables disponibles : {recept_name}, {service}, {date}, {time}, {client_name}, {company}</div>
                <div class="ib-notif-actions">
                    <button type="submit" name="send_test" value="1" class="ib-btn-test" onclick="this.form.test_type.value='recept_cancel'">Envoyer un test</button>
                </div>
            </div>
            <!-- Onglet Admin -->
            <div class="ib-notif-section ib-tab-content" data-tab-content="admin" style="display:none;">
                <div class="ib-notif-title">
                    <span class="dashicons dashicons-shield"></span> 
                    Administrateur 
                    <span class="ib-badge">Destinataire</span>
                </div>
                <label style="display:block;font-weight:600;color:#1e293b;margin-bottom:0.5em;"><b>Confirmation</b></label>
                <textarea name="notify_admin_confirm" rows="4" class="ib-input" placeholder="Bonjour {admin_name}, nouvelle réservation confirmée pour {service} le {date} à {time}..."><?php echo esc_textarea($notify_admin_confirm); ?></textarea>
                <div class="ib-notif-vars">Variables disponibles : {admin_name}, {service}, {date}, {time}, {client_name}, {company}</div>
                <div class="ib-notif-actions">
                    <button type="submit" name="send_test" value="1" class="ib-btn-test" onclick="this.form.test_type.value='admin_confirm'">Envoyer un test</button>
                </div>
                <label style="display:block;font-weight:600;color:#1e293b;margin:1.5em 0 0.5em 0;"><b>Annulation</b></label>
                <textarea name="notify_admin_cancel" rows="4" class="ib-input" placeholder="Bonjour {admin_name}, réservation annulée pour {service} le {date} à {time}..."><?php echo esc_textarea($notify_admin_cancel); ?></textarea>
                <div class="ib-notif-vars">Variables disponibles : {admin_name}, {service}, {date}, {time}, {client_name}, {company}</div>
                <div class="ib-notif-actions">
                    <button type="submit" name="send_test" value="1" class="ib-btn-test" onclick="this.form.test_type.value='admin_cancel'">Envoyer un test</button>
                </div>
            </div>
            <!-- Onglet Rappel -->
            <div class="ib-notif-section ib-tab-content" data-tab-content="reminder" style="display:none;">
                <div class="ib-notif-title">
                    <span class="dashicons dashicons-clock"></span> 
                    Rappel de rendez-vous 
                    <span class="ib-badge">Client</span>
                </div>
                <label style="display:block;font-weight:600;color:#1e293b;margin-bottom:0.5em;"><b>Contenu du mail de rappel</b></label>
                <textarea name="notify_reminder" rows="4" class="ib-input" placeholder="Bonjour {client_name}, rappel pour votre rendez-vous {service} le {date} à {time}..."><?php echo esc_textarea($notify_reminder); ?></textarea>
                <div class="ib-notif-vars">Variables disponibles : {client_name}, {service}, {date}, {time}, {company}</div>
                <div class="ib-notif-actions">
                    <button type="submit" name="send_test" value="1" class="ib-btn-test" onclick="this.form.test_type.value='reminder'">Envoyer un test</button>
                </div>
            </div>
            <input type="hidden" name="test_type" value="">
            
            <!-- Onglet Paramètres avancés -->
            <div class="ib-notif-section ib-tab-content" data-tab-content="advanced" style="display:none;">
                <div class="ib-notif-title">
                    <span class="dashicons dashicons-admin-generic"></span> 
                    Paramètres avancés
                </div>
                
                <h2>Notifications Push</h2>
                <label><input type="checkbox" name="ib_push_enable" value="1" <?php checked(get_option('ib_push_enable'), 1); ?>> Activer les notifications push web/app</label>
                
                <h2>Notifications WhatsApp</h2>
                <label><input type="checkbox" name="ib_whatsapp_enable" value="1" <?php checked(get_option('ib_whatsapp_enable'), 1); ?>> Activer l'envoi WhatsApp</label>
                <br><label>API Key / Token WhatsApp :</label>
                <input type="text" name="ib_whatsapp_token" value="<?php echo esc_attr(get_option('ib_whatsapp_token')); ?>" class="regular-text">
                
                <h2>Rappels automatiques</h2>
                <label><input type="checkbox" name="ib_reminder_enable" value="1" <?php checked(get_option('ib_reminder_enable'), 1); ?>> Activer les rappels automatiques (email, SMS, push, WhatsApp)</label>
                <br><label>Heure d'envoi du rappel (ex: 09:00) :</label>
                <input type="time" name="ib_reminder_time" value="<?php echo esc_attr(get_option('ib_reminder_time', '09:00')); ?>">
                
                <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-left: 4px solid #2271b1;">
                    <h3>Test des rappels</h3>
                    
                    <div style="margin-bottom: 20px;">
                        <h4>Option 1 : Tester avec un ID de réservation</h4>
                        <p>Pour un test avec des données réelles :</p>
                        <label>ID de la réservation :</label>
                        <input type="number" name="test_booking_id" min="1" style="width: 100px; margin: 0 10px;">
                        <button type="submit" name="test_reminder" class="button button-secondary">Tester avec l'ID</button>
                    </div>
                    
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                        <h4>Option 2 : Tester avec une adresse email</h4>
                        <p>Pour un test rapide (données factices) :</p>
                        <label>Email de test :</label>
                        <input type="email" name="test_reminder_email" placeholder="votre@email.com" style="width: 250px; margin: 0 10px;">
                        <button type="submit" name="test_reminder_email_btn" class="button button-secondary">Tester avec cet email</button>
                    </div>
                    
                    <?php if (isset($test_feedback)) echo $test_feedback; ?>
                </div>
                
                <div style="margin-top: 30px; text-align: right;">
                    <button type="submit" name="ib_save_notifications_advanced" class="button button-primary">Enregistrer les paramètres avancés</button>
                </div>
            </div>
            
            <div style="text-align:center;margin-top:2em;">
                <button type="submit" class="ib-btn-save">
                    <span class="dashicons dashicons-yes"></span> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.ib-tab');
    const contents = document.querySelectorAll('.ib-tab-content');
    const toasts = document.querySelectorAll('.ib-toast');
    
    // Fonction pour activer un onglet avec animation
    function activateTab(tabName) {
        // Animation de sortie pour le contenu actuel
        contents.forEach(content => {
            if (content.style.display !== 'none') {
                content.style.opacity = '0';
                content.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    content.style.display = 'none';
                }, 200);
            }
        });
        
        // Mise à jour des onglets
        tabs.forEach(tab => {
            if(tab.dataset.tab === tabName) {
                tab.classList.add('active');
            } else {
                tab.classList.remove('active');
            }
        });
        
        // Animation d'entrée pour le nouveau contenu
        setTimeout(() => {
            contents.forEach(content => {
                if(content.dataset.tabContent === tabName) {
                    content.style.display = '';
                    content.style.opacity = '0';
                    content.style.transform = 'translateY(20px)';
                    
                    // Animation d'apparition
                    requestAnimationFrame(() => {
                        content.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                        content.style.opacity = '1';
                        content.style.transform = 'translateY(0)';
                    });
                }
            });
        }, 200);
    }
    
    // Gestionnaire des toasts
    function initToasts() {
        toasts.forEach(toast => {
            // Auto-masquage après 4 secondes
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 4000);
            
            // Clic pour fermer
            toast.addEventListener('click', () => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            });
        });
    }
    
    // Animation des champs au focus
    function initFieldAnimations() {
        const inputs = document.querySelectorAll('.ib-input, .ib-test-email');
        
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'scale(1.01)';
            });
            
            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'scale(1)';
            });
        });
    }
    
    // Animation des boutons
    function initButtonAnimations() {
        const buttons = document.querySelectorAll('.ib-btn-test, .ib-btn-save');
        
        buttons.forEach(button => {
            button.addEventListener('mousedown', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            button.addEventListener('mouseup', function() {
                this.style.transform = '';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    }
    
    // Validation en temps réel
    function initRealTimeValidation() {
        const emailInput = document.querySelector('.ib-test-email');
        
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
                
                if (this.value && !isValid) {
                    this.style.borderColor = 'var(--error-color)';
                    this.style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.1)';
                } else if (this.value && isValid) {
                    this.style.borderColor = 'var(--success-color)';
                    this.style.boxShadow = '0 0 0 4px rgba(16, 185, 129, 0.1)';
                } else {
                    this.style.borderColor = '';
                    this.style.boxShadow = '';
                }
            });
        }
    }
    
    // Indicateur de progression pour la sauvegarde
    function initSaveProgress() {
        const saveButton = document.querySelector('.ib-btn-save');
        const form = document.querySelector('.ib-notif-form');
        
        if (saveButton && form) {
            form.addEventListener('submit', function(e) {
                if (!e.target.querySelector('input[name="send_test"]')) {
                    saveButton.innerHTML = '<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> Enregistrement...';
                    saveButton.disabled = true;
                }
            });
        }
    }
    
    // Initialisation
    activateTab('client');
    initToasts();
    initFieldAnimations();
    initButtonAnimations();
    initRealTimeValidation();
    initSaveProgress();
    
    // Gestionnaire des onglets
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            if (!this.classList.contains('active')) {
                activateTab(this.dataset.tab);
            }
        });
    });
    
    // Animation de rotation pour les icônes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .ib-notif-section {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .ib-test-row {
            transition: transform 0.2s ease;
        }
    `;
    document.head.appendChild(style);
});
</script>
