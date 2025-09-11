<?php
// Désactiver l'affichage des erreurs pour éviter les problèmes d'en-têtes
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclure le fichier wp-load.php en utilisant le chemin absolu
$wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
if (!file_exists($wp_load_path)) {
    die('Erreur: Impossible de trouver wp-load.php');
}

require_once($wp_load_path);

// Vérifier que WordPress est chargé
if (!function_exists('wp')) {
    die('Erreur: WordPress n\'est pas chargé correctement');
}

global $wpdb;

// Vérifier si la table des clients existe
$table_name = $wpdb->prefix . 'ib_clients';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

if ($table_exists) {
    echo "La table $table_name existe.\n";
    
    // Compter le nombre de clients
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo "Nombre de clients dans la table : $count\n";
    
    // Afficher les 5 premiers clients (s'il y en a)
    if ($count > 0) {
        $clients = $wpdb->get_results("SELECT * FROM $table_name LIMIT 5");
        echo "\nExemple de clients :\n";
        foreach ($clients as $client) {
            echo "- ID: $client->id, Nom: $client->name, Email: $client->email, Téléphone: $client->phone\n";
        }
    }
} else {
    echo "La table $table_name n'existe pas.\n";
    
    // Essayer de créer la table
    echo "Tentative de création de la table...\n";
    
    // Inclure le fichier nécessaire pour dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_clients (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        bookings_count int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";
    
    $result = dbDelta($sql);
    
    if (empty($wpdb->last_error)) {
        echo "La table a été créée avec succès.\n";
    } else {
        echo "Erreur lors de la création de la table : " . $wpdb->last_error . "\n";
    }
}

// Vérifier si la fonction ib_search_client_by_phone existe
if (function_exists('ib_search_client_by_phone')) {
    echo "\nLa fonction ib_search_client_by_phone existe.\n";
} else {
    echo "\nLa fonction ib_search_client_by_phone n'existe pas.\n";
}

// Vérifier si le hook AJAX est enregistré
$ajax_actions = array(
    'wp_ajax_ib_client_lookup',
    'wp_ajax_nopriv_ib_client_lookup'
);

foreach ($ajax_actions as $action) {
    if (has_action($action)) {
        echo "Le hook $action est enregistré.\n";
    } else {
        echo "Le hook $action n'est PAS enregistré.\n";
    }
}

// Vérifier si le fichier ajax-client-lookup.php est inclus
$ajax_lookup_file = dirname(__FILE__) . '/includes/ajax-client-lookup.php';
if (file_exists($ajax_lookup_file)) {
    echo "\nLe fichier ajax-client-lookup.php existe.\n";
    
    // Vérifier s'il est inclus
    $included_files = get_included_files();
    if (in_array($ajax_lookup_file, $included_files)) {
        echo "Le fichier ajax-client-lookup.php est inclus.\n";
    } else {
        echo "Le fichier ajax-client-lookup.php n'est PAS inclus.\n";
        
        // Essayer d'inclure le fichier manuellement
        echo "Tentative d'inclusion manuelle...\n";
        require_once($ajax_lookup_file);
        
        // Vérifier à nouveau si la fonction existe
        if (function_exists('ib_search_client_by_phone')) {
            echo "La fonction ib_search_client_by_phone est maintenant disponible.\n";
        } else {
            echo "Échec de l'inclusion du fichier ou de la fonction.\n";
        }
    }
} else {
    echo "\nLe fichier ajax-client-lookup.php n'existe pas.\n";
}

// Vérifier les variables JavaScript globales
echo "\nVariables JavaScript globales :\n";
$js_vars = array(
    'ib_admin_vars',
    'ajaxurl'
);

foreach ($js_vars as $var) {
    echo "- $var : " . (isset($GLOBALS[$var]) ? 'définie' : 'non définie') . "\n";
}

// Vérifier les hooks d'actions
echo "\nHooks d'actions :\n";
$action_hooks = array(
    'admin_enqueue_scripts',
    'wp_enqueue_scripts',
    'wp_ajax_ib_client_lookup',
    'wp_ajax_nopriv_ib_client_lookup'
);

foreach ($action_hooks as $hook) {
    $count = has_action($hook) ? 'oui' : 'non';
    echo "- $hook : $count\n";
}

// Vérifier les scripts enregistrés
echo "\nScripts enregistrés :\n";
$scripts = array(
    'ib-admin-script',
    'ib-pdf-ticket-fix',
    'ib-ultra-simple-notification'
);

foreach ($scripts as $script) {
    $registered = wp_script_is($script, 'registered') ? 'oui' : 'non';
    $enqueued = wp_script_is($script, 'enqueued') ? 'oui' : 'non';
    echo "- $script : enregistré ($registered), mis en file d'attente ($enqueued)\n";
}

// Vérifier les erreurs de base de données
echo "\nDernière erreur de base de données : " . $wpdb->last_error . "\n";
