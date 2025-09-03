<?php
/**
 * Gestion de l'annulation de réservation par email
 */
if (!defined('ABSPATH')) exit;

// Ajoute un endpoint personnalisé pour l'annulation
add_action('init', function() {
    add_rewrite_rule('^annuler-reservation/([^/]+)/?', 'index.php?cancel_booking=$matches[1]', 'top');
    add_rewrite_tag('%cancel_booking%', '([^&]+)');
});

// Intercepte la requête d'annulation
add_action('template_redirect', function() {
    $token = get_query_var('cancel_booking');
    if (!$token) return;
    
    global $wpdb;
    
    // Récupère la réservation avec ce token
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ib_bookings WHERE cancel_token = %s",
        $token
    ));
    
    if (!$booking) {
        wp_redirect(home_url('/reservation-non-trouvee'));
        exit;
    }
    
    // Vérifie si la réservation peut être annulée (par exemple, pas déjà annulée ou passée)
    if ($booking->status === 'annulee') {
        wp_redirect(home_url('/reservation-deja-annulee'));
        exit;
    }
    
    // Met à jour le statut de la réservation
    $wpdb->update(
        "{$wpdb->prefix}ib_bookings",
        ['status' => 'annulee'],
        ['id' => $booking->id]
    );
    
    // Supprime le token pour qu'il ne soit plus utilisable
    $wpdb->update(
        "{$wpdb->prefix}ib_bookings",
        ['cancel_token' => ''],
        ['id' => $booking->id]
    );
    
    // Redirige vers une page de confirmation
    wp_redirect(home_url('/reservation-annulee'));
    exit;
});

// Ajoute une colonne pour stocker le token d'annulation
add_action('plugins_loaded', function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ib_bookings';
    $column = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = %s AND COLUMN_NAME = 'cancel_token'",
        $table_name
    ));
    
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN cancel_token VARCHAR(64) DEFAULT ''");
    }
});
