<?php
if (!defined('ABSPATH')) exit;
$gcal_id = get_option('ib_gcal_client_id', '');
$gcal_secret = get_option('ib_gcal_client_secret', '');
$outlook_id = get_option('ib_outlook_client_id', '');
$outlook_secret = get_option('ib_outlook_client_secret', '');
?>
<div class="ib-admin-main">
    <div class="ib-admin-header">
        <h1>Synchronisation Calendrier</h1>
    </div>
    <div class="ib-admin-content">
        <form method="post">
            <h2>Google Calendar</h2>
            <div style="display:flex;gap:20px;align-items:center;margin-bottom:20px;">
                <div><label>Client ID</label><br><input type="text" name="ib_gcal_client_id" value="<?php echo esc_attr($gcal_id); ?>" style="width:300px;"></div>
                <div><label>Client Secret</label><br><input type="text" name="ib_gcal_client_secret" value="<?php echo esc_attr($gcal_secret); ?>" style="width:300px;"></div>
            </div>
            <h2>Outlook Calendar</h2>
            <div style="display:flex;gap:20px;align-items:center;margin-bottom:20px;">
                <div><label>Client ID</label><br><input type="text" name="ib_outlook_client_id" value="<?php echo esc_attr($outlook_id); ?>" style="width:300px;"></div>
                <div><label>Client Secret</label><br><input type="text" name="ib_outlook_client_secret" value="<?php echo esc_attr($outlook_secret); ?>" style="width:300px;"></div>
            </div>
            <button type="submit" name="ib_save_calendar_sync" class="button button-primary">Enregistrer</button>
        </form>
    </div>
</div>