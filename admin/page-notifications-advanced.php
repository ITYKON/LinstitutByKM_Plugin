<?php
// admin/page-notifications-advanced.php
if (!defined('ABSPATH')) exit;
?>
<div class="ib-notifications-advanced-admin">
    <h1>Notifications avancées</h1>
    <?php if (isset($_GET['saved'])) {
        echo '<div class="updated notice"><p>Paramètres enregistrés !</p></div>';
    } ?>
    <form method="post" style="max-width:600px;">
        <h2>Notifications Push</h2>
        <label><input type="checkbox" name="ib_push_enable" value="1" <?php checked(get_option('ib_push_enable'), 1); ?>> Activer les notifications push web/app</label>
        <br><br>
        <h2>Notifications WhatsApp</h2>
        <label><input type="checkbox" name="ib_whatsapp_enable" value="1" <?php checked(get_option('ib_whatsapp_enable'), 1); ?>> Activer l'envoi WhatsApp</label>
        <br><label>API Key / Token WhatsApp :</label>
        <input type="text" name="ib_whatsapp_token" value="<?php echo esc_attr(get_option('ib_whatsapp_token')); ?>" class="regular-text">
        <br><br>
        <h2>Rappels automatiques</h2>
        <label><input type="checkbox" name="ib_reminder_enable" value="1" <?php checked(get_option('ib_reminder_enable'), 1); ?>> Activer les rappels automatiques (email, SMS, push, WhatsApp)</label>
        <br><label>Heure d'envoi du rappel (ex: 09:00) :</label>
        <input type="time" name="ib_reminder_time" value="<?php echo esc_attr(get_option('ib_reminder_time', '09:00')); ?>">
        <br><br>
        <button class="button button-primary" type="submit" name="ib_save_notifications_advanced">Enregistrer</button>
    </form>
</div>
