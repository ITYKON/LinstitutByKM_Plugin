<?php
// Classe pour la gestion des coupons
if (!defined('ABSPATH')) exit;

class IB_Coupons {
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_coupons ORDER BY valid_from DESC");
    }
    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_coupons WHERE id = %d", $id));
    }
    public static function add($code, $discount, $type, $usage_limit, $valid_from, $valid_to) {
        global $wpdb;
        $wpdb->insert("{$wpdb->prefix}ib_coupons", [
            'code' => strtoupper(sanitize_text_field($code)),
            'discount' => floatval($discount),
            'type' => sanitize_text_field($type),
            'usage_limit' => intval($usage_limit),
            'valid_from' => $valid_from,
            'valid_to' => $valid_to
        ]);
    }
    public static function update($id, $code, $discount, $type, $usage_limit, $valid_from, $valid_to) {
        global $wpdb;
        $wpdb->update("{$wpdb->prefix}ib_coupons",
            [
                'code' => strtoupper(sanitize_text_field($code)),
                'discount' => floatval($discount),
                'type' => sanitize_text_field($type),
                'usage_limit' => intval($usage_limit),
                'valid_from' => $valid_from,
                'valid_to' => $valid_to
            ],
            ['id' => (int)$id]
        );
    }
    public static function delete($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}ib_coupons", ['id' => (int)$id]);
    }
}

// HANDLERS AJAX
add_action('wp_ajax_ib_add_coupon', function() {
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Accès refusé']);
    global $wpdb;
    $code = strtoupper(sanitize_text_field($_POST['code']));
    
    // Validation du code
    if (empty($code)) {
        wp_send_json_error(['message' => 'Le code de coupon est requis.']);
    }
    
    // Vérifier l'unicité du code
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}ib_coupons WHERE code = %s", $code));
    if ($exists > 0) {
        wp_send_json_error(['message' => 'Ce code de coupon existe déjà.']);
    }
    
    $discount = floatval($_POST['discount']);
    $type = sanitize_text_field($_POST['type']);
    $usage_limit = intval($_POST['usage_limit']);
    $valid_from = sanitize_text_field($_POST['valid_from']);
    $valid_to = sanitize_text_field($_POST['valid_to']);
    
    // Validation des données
    if ($discount <= 0) {
        wp_send_json_error(['message' => 'La remise doit être supérieure à 0.']);
    }
    
    if ($type === 'percent' && $discount > 100) {
        wp_send_json_error(['message' => 'La remise en pourcentage ne peut pas dépasser 100%.']);
    }
    
    try {
        IB_Coupons::add($code, $discount, $type, $usage_limit, $valid_from, $valid_to);
        
        // Log de l'action
        ib_log('coupon_added', [
            'code' => $code,
            'discount' => $discount,
            'type' => $type
        ]);
        
        $coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_coupons WHERE code = %s ORDER BY id DESC LIMIT 1", $code));
        ob_start();
        include plugin_dir_path(__FILE__) . '../admin/partials/coupon-row.php';
        $row = ob_get_clean();
        wp_send_json_success(['row' => $row, 'message' => 'Coupon ajouté !']);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Erreur lors de l\'ajout du coupon : ' . $e->getMessage()]);
    }
});
add_action('wp_ajax_ib_update_coupon', function() {
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Accès refusé']);
    global $wpdb;
    $id = intval($_POST['id']);
    $code = strtoupper(sanitize_text_field($_POST['code']));
    
    // Validation du code
    if (empty($code)) {
        wp_send_json_error(['message' => 'Le code de coupon est requis.']);
    }
    
    // Vérifier unicité hors ce coupon
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}ib_coupons WHERE code = %s AND id != %d", $code, $id));
    if ($exists > 0) {
        wp_send_json_error(['message' => 'Ce code de coupon existe déjà.']);
    }
    
    $discount = floatval($_POST['discount']);
    $type = sanitize_text_field($_POST['type']);
    $usage_limit = intval($_POST['usage_limit']);
    $valid_from = sanitize_text_field($_POST['valid_from']);
    $valid_to = sanitize_text_field($_POST['valid_to']);
    
    // Validation des données
    if ($discount <= 0) {
        wp_send_json_error(['message' => 'La remise doit être supérieure à 0.']);
    }
    
    if ($type === 'percent' && $discount > 100) {
        wp_send_json_error(['message' => 'La remise en pourcentage ne peut pas dépasser 100%.']);
    }
    
    try {
        IB_Coupons::update($id, $code, $discount, $type, $usage_limit, $valid_from, $valid_to);
        
        // Log de l'action
        ib_log('coupon_updated', [
            'id' => $id,
            'code' => $code,
            'discount' => $discount,
            'type' => $type
        ]);
        
        $coupon = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_coupons WHERE id = %d", $id));
        ob_start();
        include plugin_dir_path(__FILE__) . '../admin/partials/coupon-row.php';
        $row = ob_get_clean();
        wp_send_json_success(['row' => $row, 'message' => 'Coupon modifié !']);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Erreur lors de la modification du coupon : ' . $e->getMessage()]);
    }
});
add_action('wp_ajax_ib_delete_coupon', function() {
    if (!current_user_can('manage_options')) wp_send_json_error(['message' => 'Accès refusé']);
    $id = intval($_POST['id']);
    
    try {
        // Récupérer les infos du coupon avant suppression pour le log
        $coupon = IB_Coupons::get_by_id($id);
        
        IB_Coupons::delete($id);
        
        // Log de l'action
        if ($coupon) {
            ib_log('coupon_deleted', [
                'id' => $id,
                'code' => $coupon->code,
                'discount' => $coupon->discount,
                'type' => $coupon->type
            ]);
        }
        
        wp_send_json_success(['message' => 'Coupon supprimé !']);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Erreur lors de la suppression du coupon : ' . $e->getMessage()]);
    }
});
