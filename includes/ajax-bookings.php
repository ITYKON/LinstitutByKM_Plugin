<?php
/**
 * Gestion des requêtes AJAX pour les réservations
 */

// Ajouter les actions AJAX
add_action('wp_ajax_ib_get_updated_bookings', 'ib_ajax_get_updated_bookings');

/**
 * Récupère les réservations mises à jour via AJAX
 */
function ib_ajax_get_updated_bookings() {
    // Vérifier le nonce de sécurité
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ib_admin_nonce')) {
        wp_send_json_error('Erreur de sécurité');
        return;
    }

    // Vérifier les permissions utilisateur
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Inclure les dépendances nécessaires
    require_once plugin_dir_path(__FILE__) . 'class-bookings.php';
    require_once plugin_dir_path(__FILE__) . 'class-services.php';
    require_once plugin_dir_path(__FILE__) . 'class-employees.php';

    // Récupérer les paramètres
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'date';
    $order = isset($_POST['order']) && in_array(strtoupper($_POST['order']), ['ASC', 'DESC']) 
        ? strtoupper($_POST['order']) 
        : 'DESC';
    
    // Récupérer les filtres
    $filters = isset($_POST['filters']) ? (array) $_POST['filters'] : [];
    $filters = array_map('sanitize_text_field', $filters);

    // Construire la requête de base
    global $wpdb;
    $table = $wpdb->prefix . 'ib_bookings';
    $services_table = $wpdb->prefix . 'ib_services';
    $employees_table = $wpdb->prefix . 'ib_employees';
    
    $query = "SELECT b.*, s.name as service_name, e.name as employee_name 
              FROM $table b 
              LEFT JOIN $services_table s ON b.service_id = s.id 
              LEFT JOIN $employees_table e ON b.employee_id = e.id 
              WHERE 1=1";

    $params = [];

    // ----- category
    global $wpdb;

// Récupère toutes les catégories
$categories = $wpdb->get_results("SELECT id, name FROM wp_categories");
    if (!empty($filters['category'])) {
        $query .= " AND b.category_id = %d";
        $params[] = intval($filters['category']);
    }
    
    // Appliquer les filtres
    if (!empty($filters['status'])) {
        $query .= " AND b.status = %s";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['employee'])) {
        $query .= " AND b.employee_id = %d";
        $params[] = intval($filters['employee']);
    }
    
    if (!empty($filters['service'])) {
        $query .= " AND b.service_id = %d";
        $params[] = intval($filters['service']);
    }
    
    if (!empty($filters['date_from'])) {
        $query .= " AND DATE(b.date) >= %s";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $query .= " AND DATE(b.date) <= %s";
        $params[] = $filters['date_to'];
    }
    
    if (!empty($filters['search'])) {
        $search = '%' . $wpdb->esc_like($filters['search']) . '%';
        $query .= " AND (b.client_name LIKE %s OR b.client_email LIKE %s OR b.client_phone LIKE %s)";
        $params = array_merge($params, [$search, $search, $search]);
    }
    
    // Ajouter le tri
    $valid_sort_columns = ['client', 'email', 'service', 'employee', 'date', 'status'];
    $sort_column = in_array($sort, $valid_sort_columns) ? $sort : 'date';
    
    // Mapper les noms de colonnes entre le front et la base de données
    $column_mapping = [
        'client' => 'b.client_name',
        'email' => 'b.client_email',
        'service' => 's.name',
        'employee' => 'e.name',
        'date' => 'b.date',
        'status' => 'b.status'
    ];
    
    $sort_column = $column_mapping[$sort_column] ?? 'b.date';
    $query .= " ORDER BY $sort_column $order";
    
    // Préparer et exécuter la requête
    if (!empty($params)) {
        $query = $wpdb->prepare($query, $params);
    }
    
    $bookings = $wpdb->get_results($query);
    
    // Générer le HTML du tableau
    ob_start();
    include plugin_dir_path(__FILE__) . '../admin/partials/bookings-table.php';
    $table_html = ob_get_clean();
    
    // Retourner la réponse
    wp_send_json_success([
        'html' => $table_html,
        'count' => count($bookings),
        'timestamp' => current_time('mysql')
    ]);
}
