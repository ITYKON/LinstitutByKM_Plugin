<?php
/**
 * Template for the admin booking form
 * 
 * @package LinstitutByKM_Plugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form id="booking-form" method="post" action="">
        <?php wp_nonce_field('booking_form_nonce', 'booking_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="client_phone"><?php esc_html_e('Téléphone', 'institut-booking'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="tel" 
                               id="client_phone" 
                               name="client_phone" 
                               class="regular-text" 
                               required 
                               placeholder="<?php esc_attr_e('Entrez le numéro de téléphone et appuyez sur Entrée', 'institut-booking'); ?>"
                        >
                        <p class="description"><?php esc_html_e('Entrez le numéro de téléphone et appuyez sur Entrée pour trouver la cliente', 'institut-booking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="first_name"><?php esc_html_e('Prénom', 'institut-booking'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="first_name" name="first_name" class="regular-text" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="last_name"><?php esc_html_e('Nom', 'institut-booking'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="last_name" name="last_name" class="regular-text" required>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="email"><?php esc_html_e('Email', 'institut-booking'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="email" id="email" name="email" class="regular-text" required>
                    </td>
                </tr>
                
                <!-- Add more booking fields as needed -->
                
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Enregistrer la réservation', 'institut-booking'); ?>">
        </p>
    </form>
</div>

<style>
    .client-lookup-message {
        margin: 5px 0 0 0;
        padding: 5px 10px;
        border-radius: 3px;
    }
    .client-lookup-message.success {
        background-color: #e6f7ee;
        border-left: 4px solid #00a32a;
    }
    .client-lookup-message.error {
        background-color: #fcf0f1;
        border-left: 4px solid #d63638;
    }
    .loading {
        background: #f1f1f1 url(<?php echo admin_url('images/spinner.gif'); ?>) no-repeat right center;
        background-size: 20px 20px;
    }
</style>
