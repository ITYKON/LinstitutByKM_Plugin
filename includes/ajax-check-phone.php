<?php
/**
 * Fonction pour vérifier si un numéro de téléphone existe dans les tables clients ou bookings
 */

// Hook pour gérer la requête AJAX (utilisateurs connectés et non connectés)
add_action('wp_ajax_check_phone_exists', 'ib_check_phone_exists');
add_action('wp_ajax_nopriv_check_phone_exists', 'ib_check_phone_exists');

function ib_check_phone_exists() {
    // Vérification du nonce pour la sécurité
    if (!wp_verify_nonce($_POST['nonce'], 'ib_ajax_nonce')) {
        wp_die('Erreur de sécurité');
    }
    
    // Récupérer et nettoyer le numéro de téléphone
    $phone = sanitize_text_field($_POST['phone']);
    
    // Valider que le numéro contient au moins 9 chiffres (pour gérer +213, 0, etc.)
    $clean_phone = preg_replace('/\D/', '', $phone);
    if (strlen($clean_phone) < 9) {
        wp_send_json_error('Numéro de téléphone invalide');
        return;
    }
    $last9 = substr($clean_phone, -9);
    
    global $wpdb;
    
    // Noms des tables (ajustez selon votre structure de base de données)
    $clients_table = $wpdb->prefix . 'ib_clients'; // Table des clients
    $bookings_table = $wpdb->prefix . 'ib_bookings'; // Table des réservations
    
    $result = array(
        'exists' => false,
        'source' => null,
        'client_name' => null,
        'client_email' => null
    );
    
    try {
        // 1. Vérifier d'abord dans la table des clients (sur les 9 derniers chiffres, tous formats)
        $client_query = $wpdb->prepare(
            "SELECT name, email, phone FROM {$clients_table} WHERE RIGHT(REGEXP_REPLACE(phone, '[^0-9]', ''), 9) = %s LIMIT 1",
            $last9
        );
        $client = $wpdb->get_row($client_query);
        if ($client) {
            $result['exists'] = true;
            $result['source'] = 'client';
            $result['client_name'] = $client->name;
            $result['client_email'] = $client->email;
            wp_send_json_success($result);
            return;
        }
        // 2. Si pas trouvé dans clients, vérifier dans les réservations (sur les 9 derniers chiffres)
        $booking_query = $wpdb->prepare(
            "SELECT DISTINCT client_name, client_phone, client_email FROM {$bookings_table} WHERE RIGHT(REGEXP_REPLACE(client_phone, '[^0-9]', ''), 9) = %s LIMIT 1",
            $last9
        );
        $booking = $wpdb->get_row($booking_query);
        if ($booking) {
            $result['exists'] = true;
            $result['source'] = 'booking';
            $result['client_name'] = $booking->client_name;
            $result['client_email'] = isset($booking->client_email) ? $booking->client_email : '';
            wp_send_json_success($result);
            return;
        }
        // 3. Aucun résultat trouvé
        wp_send_json_success($result);
    } catch (Exception $e) {
        error_log('Erreur lors de la vérification du téléphone: ' . $e->getMessage());
        wp_send_json_error('Erreur lors de la vérification dans la base de données');
    }
}

/**
 * Enregistrer les scripts et variables JavaScript nécessaires
 */
function ib_enqueue_phone_check_scripts() {
    // Enregistrer le script JavaScript (ajustez le chemin selon votre structure)
    wp_enqueue_script(
        'ib-phone-check', 
        plugin_dir_url(__FILE__) . 'js/phone-check.js', 
        array('jquery'), 
        '1.0.0', 
        true
    );
    
    // Localiser les variables pour AJAX
    wp_localize_script('ib-phone-check', 'ib_ajax_vars', array(
        'ib_ajax_url' => admin_url('admin-ajax.php'),
        'ib_ajax_nonce' => wp_create_nonce('ib_ajax_nonce')
    ));
}

// Hook pour charger les scripts (ajustez selon votre contexte)
add_action('admin_enqueue_scripts', 'ib_enqueue_phone_check_scripts');
add_action('wp_enqueue_scripts', 'ib_enqueue_phone_check_scripts');

/**
 * Fonction utilitaire pour formater un numéro de téléphone
 */
function ib_format_phone($phone) {
    // Supprimer tous les caractères non numériques
    $clean = preg_replace('/\D/', '', $phone);
    
    // Formater si c'est un numéro à 10 chiffres
    if (strlen($clean) === 10) {
        return substr($clean, 0, 3) . '-' . substr($clean, 3, 3) . '-' . substr($clean, 6, 4);
    }
    
    return $clean;
}

/**
 * Fonction utilitaire pour valider un numéro de téléphone
 */
function ib_validate_phone($phone) {
    $clean = preg_replace('/\D/', '', $phone);
    return strlen($clean) === 10;
}

/**
 * Fonction pour rechercher un client par téléphone (utilisation dans d'autres contextes)
 */
function ib_find_client_by_phone($phone) {
    global $wpdb;
    
    $clean_phone = preg_replace('/\D/', '', $phone);
    if (strlen($clean_phone) !== 10) {
        return false;
    }
    
    // Tables
    $clients_table = $wpdb->prefix . 'ib_clients';
    $bookings_table = $wpdb->prefix . 'ib_bookings';
    
    // Chercher d'abord dans les clients
    $client = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$clients_table} WHERE phone = %s LIMIT 1",
        $clean_phone
    ));
    
    if ($client) {
        return array(
            'source' => 'client',
            'data' => $client
        );
    }
    
    // Chercher dans les réservations
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT DISTINCT client_name, client_phone FROM {$bookings_table} WHERE client_phone = %s LIMIT 1",
        $clean_phone
    ));
    
    if ($booking) {
        return array(
            'source' => 'booking',
            'data' => $booking
        );
    }
    
    return false;
}
?>