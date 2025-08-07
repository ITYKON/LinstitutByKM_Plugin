<?php
// admin/page-calendar-sync.php
if (!defined('ABSPATH')) exit;
?>
<div class="ib-calendar-sync-admin">
    <h1>Synchronisation Google Calendar / Outlook</h1>
    <?php if (isset($_GET['saved'])) {
        echo '<div class="updated notice"><p>Paramètres enregistrés !</p></div>';
    } ?>
    <form method="post" style="max-width:600px;">
        <h2>Google Calendar</h2>
        <label>Client ID :</label>
        <input type="text" name="ib_gcal_client_id" value="<?php echo esc_attr(get_option('ib_gcal_client_id')); ?>" class="regular-text">
        <br><label>Client Secret :</label>
        <input type="text" name="ib_gcal_client_secret" value="<?php echo esc_attr(get_option('ib_gcal_client_secret')); ?>" class="regular-text">
        <br><button class="button" type="submit" name="ib_gcal_connect">Connecter Google</button>
        <br><br>
        <h2>Outlook Calendar</h2>
        <label>Client ID :</label>
        <input type="text" name="ib_outlook_client_id" value="<?php echo esc_attr(get_option('ib_outlook_client_id')); ?>" class="regular-text">
        <br><label>Client Secret :</label>
        <input type="text" name="ib_outlook_client_secret" value="<?php echo esc_attr(get_option('ib_outlook_client_secret')); ?>" class="regular-text">
        <br><button class="button" type="submit" name="ib_outlook_connect">Connecter Outlook</button>
        <br><br>
        <button class="button button-primary" type="submit" name="ib_save_calendar_sync">Enregistrer</button>
    </form>
</div>
