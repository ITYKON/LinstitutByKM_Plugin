<?php
if (!defined('ABSPATH')) exit;

/**
 * Installation du plugin
 */
function ib_install_plugin() {
    global $wpdb;
    
    // Inclusion du fichier nécessaire pour dbDelta
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $charset_collate = $wpdb->get_charset_collate();

    // Table des catégories
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_categories (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        color varchar(7) DEFAULT NULL,
        icon varchar(255) DEFAULT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des services (doit être créée en premier car référencée par d'autres tables)
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_services (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        duration int NOT NULL,
        price decimal(10,2) NOT NULL,
        color varchar(7) NOT NULL,
        image varchar(255) DEFAULT NULL,
        category_id bigint(20) DEFAULT NULL,
        variable_price tinyint(1) DEFAULT 0,
        min_price decimal(10,2) DEFAULT NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des clients (doit être créée avant les réservations)
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_clients (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des praticiennes
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_employees (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        services text NOT NULL,
        working_hours text NOT NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des extras (dépend de services)
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_extras (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        price decimal(10,2) NOT NULL,
        duration int NOT NULL,
        service_id bigint(20) NOT NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY service_id (service_id)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des réservations (dépend de clients, praticiennes et services)
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_bookings (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        client_id bigint(20) NOT NULL,
        employee_id bigint(20) NOT NULL,
        service_id bigint(20) NOT NULL,
        client_name varchar(255) DEFAULT NULL,
        client_email varchar(255) DEFAULT NULL,
        date date NOT NULL,
        start_time datetime NOT NULL,
        end_time datetime NOT NULL,
        status varchar(20) NOT NULL,
        price decimal(10,2) NOT NULL,
        notes text,
        extras text,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY client_id (client_id),
        KEY employee_id (employee_id),
        KEY service_id (service_id),
        KEY date (date),
        KEY status (status)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des coupons
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_coupons (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        code varchar(50) NOT NULL,
        type varchar(20) NOT NULL,
        value decimal(10,2) NOT NULL,
        min_amount decimal(10,2) NOT NULL,
        start_date date NOT NULL,
        end_date date NOT NULL,
        usage_limit int NOT NULL,
        usage_count int NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY code (code)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des feedbacks (dépend de clients et services)
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_feedback (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        booking_id bigint(20) NOT NULL,
        client_id bigint(20) NOT NULL,
        service_id bigint(20) NOT NULL,
        rating int NOT NULL,
        comment text,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY booking_id (booking_id),
        KEY client_id (client_id),
        KEY service_id (service_id)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des logs
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_logs (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        user_name varchar(255) NOT NULL,
        action varchar(255) NOT NULL,
        context text NOT NULL,
        ip varchar(45) NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY action (action)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des tokens
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_tokens (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        token varchar(64) NOT NULL,
        expires datetime NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY token (token),
        KEY user_id (user_id)
    ) $charset_collate;";
    dbDelta($sql);

    // Table des absences des employés
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ib_employee_absences (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        employee_id bigint(20) NOT NULL,
        start_date date NOT NULL,
        end_date date NOT NULL,
        type varchar(50) NOT NULL DEFAULT 'absence',
        reason text,
        status varchar(20) NOT NULL DEFAULT 'approved',
        created_by bigint(20) NOT NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY employee_id (employee_id),
        KEY start_date (start_date),
        KEY end_date (end_date),
        KEY type (type),
        KEY status (status)
    ) $charset_collate;";
    dbDelta($sql);

    // Table de liaison services-praticiennes
    $table_service_employees = $wpdb->prefix . 'ib_service_employees';
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_service_employees'");
    if (!$exists) {
        $wpdb->query("CREATE TABLE $table_service_employees (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            service_id bigint(20) NOT NULL,
            employee_id bigint(20) NOT NULL,
            PRIMARY KEY (id),
            KEY service_id (service_id),
            KEY employee_id (employee_id)
        ) $charset_collate;");
    }

    // Table notifications internes
    $table_notifications = $wpdb->prefix . 'ib_notifications';
    $sql_notifications = "CREATE TABLE $table_notifications (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(32) NOT NULL,
        message TEXT NOT NULL,
        target VARCHAR(32) DEFAULT 'admin',
        status VARCHAR(16) DEFAULT 'unread',
        link VARCHAR(255) DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";
    dbDelta($sql_notifications);

    // Création des rôles
    ib_create_roles();

    // Ajout des données de test si les tables sont vides
    if ($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ib_services") == 0) {
        // Services de test
        $wpdb->insert("{$wpdb->prefix}ib_services", [
            'name' => 'Manucure',
            'description' => 'Soin des mains et des ongles',
            'duration' => 60,
            'price' => 35.00,
            'color' => '#FF5733',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);

        $wpdb->insert("{$wpdb->prefix}ib_services", [
            'name' => 'Pédicure',
            'description' => 'Soin des pieds et des ongles',
            'duration' => 45,
            'price' => 30.00,
            'color' => '#33FF57',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);

        // Praticienne de test
        $wpdb->insert("{$wpdb->prefix}ib_employees", [
            'user_id' => get_current_user_id(),
            'name' => 'Admin',
            'email' => wp_get_current_user()->user_email,
            'phone' => '0123456789',
            'services' => json_encode([1, 2]),
            'working_hours' => json_encode([
                'monday' => ['09:00', '18:00'],
                'tuesday' => ['09:00', '18:00'],
                'wednesday' => ['09:00', '18:00'],
                'thursday' => ['09:00', '18:00'],
                'friday' => ['09:00', '18:00']
            ]),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);

        // Client de test
        $wpdb->insert("{$wpdb->prefix}ib_clients", [
            'name' => 'Client Test',
            'email' => 'client@test.com',
            'phone' => '0123456789',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);
    }

    // Correction automatique colonne 'image' manquante
    $table_services = $wpdb->prefix . 'ib_services';
    $col_image = $wpdb->get_results("SHOW COLUMNS FROM $table_services LIKE 'image'");
    if (empty($col_image)) {
        $wpdb->query("ALTER TABLE $table_services ADD COLUMN image varchar(255) DEFAULT NULL");
    }
    // Correction automatique colonne 'variable_price' manquante
    $col_variable_price = $wpdb->get_results("SHOW COLUMNS FROM $table_services LIKE 'variable_price'");
    if (empty($col_variable_price)) {
        $wpdb->query("ALTER TABLE $table_services ADD COLUMN variable_price tinyint(1) DEFAULT 0");
    }
    // Correction automatique colonne 'min_price' manquante
    $col_min_price = $wpdb->get_results("SHOW COLUMNS FROM $table_services LIKE 'min_price'");
    if (empty($col_min_price)) {
        $wpdb->query("ALTER TABLE $table_services ADD COLUMN min_price decimal(10,2) DEFAULT NULL");
    }
    // Correction automatique colonne 'max_price' manquante
    $col_max_price = $wpdb->get_results("SHOW COLUMNS FROM $table_services LIKE 'max_price'");
    if (empty($col_max_price)) {
        $wpdb->query("ALTER TABLE $table_services ADD COLUMN max_price decimal(10,2) DEFAULT NULL");
    }
    // Correction automatique colonnes 'client_name' et 'client_email' manquantes dans bookings
    $table_bookings = $wpdb->prefix . 'ib_bookings';
    $col_client_name = $wpdb->get_results("SHOW COLUMNS FROM $table_bookings LIKE 'client_name'");
    if (empty($col_client_name)) {
        $wpdb->query("ALTER TABLE $table_bookings ADD COLUMN client_name varchar(255) DEFAULT NULL");
    }
    $col_client_email = $wpdb->get_results("SHOW COLUMNS FROM $table_bookings LIKE 'client_email'");
    if (empty($col_client_email)) {
        $wpdb->query("ALTER TABLE $table_bookings ADD COLUMN client_email varchar(255) DEFAULT NULL");
    }
    // Correction automatique colonne 'client_phone' manquante dans bookings
    $col_client_phone = $wpdb->get_results("SHOW COLUMNS FROM $table_bookings LIKE 'client_phone'");
    if (empty($col_client_phone)) {
        $wpdb->query("ALTER TABLE $table_bookings ADD COLUMN client_phone varchar(20) DEFAULT NULL");
    }
    // Correction automatique colonnes manquantes dans bookings
    $table_bookings = $wpdb->prefix . 'ib_bookings';
    $col_time = $wpdb->get_results("SHOW COLUMNS FROM $table_bookings LIKE 'time'");
    if (empty($col_time)) {
        $wpdb->query("ALTER TABLE $table_bookings ADD COLUMN time varchar(10) DEFAULT NULL");
    }
    $col_client_name = $wpdb->get_results("SHOW COLUMNS FROM $table_bookings LIKE 'client_name'");
    if (empty($col_client_name)) {
        $wpdb->query("ALTER TABLE $table_bookings ADD COLUMN client_name varchar(255) DEFAULT NULL");
    }
    $col_client_email = $wpdb->get_results("SHOW COLUMNS FROM $table_bookings LIKE 'client_email'");
    if (empty($col_client_email)) {
        $wpdb->query("ALTER TABLE $table_bookings ADD COLUMN client_email varchar(255) DEFAULT NULL");
    }
    $col_extras = $wpdb->get_results("SHOW COLUMNS FROM $table_bookings LIKE 'extras'");
    if (empty($col_extras)) {
        $wpdb->query("ALTER TABLE $table_bookings ADD COLUMN extras text DEFAULT NULL");
    }
    // Correction : rendre user_id nullable et supprimer la contrainte unique
    $table_employees = $wpdb->prefix . 'ib_employees';
    $col_user_id = $wpdb->get_results("SHOW COLUMNS FROM $table_employees LIKE 'user_id'");
    if (!empty($col_user_id)) {
        // Supprimer la contrainte unique si elle existe
        $indexes = $wpdb->get_results("SHOW INDEX FROM $table_employees WHERE Key_name = 'user_id'");
        if (!empty($indexes)) {
            $wpdb->query("ALTER TABLE $table_employees DROP INDEX user_id");
        }
        // Rendre user_id nullable
        $wpdb->query("ALTER TABLE $table_employees MODIFY COLUMN user_id bigint(20) NULL");
    }
}

/**
 * Désinstallation du plugin
 */
function ib_uninstall_plugin() {
    global $wpdb;
    $tables = [
        'ib_services',
        'ib_clients',
        'ib_employees',
        'ib_extras',
        'ib_bookings',
        'ib_coupons',
        'ib_feedback',
        'ib_logs',
        'ib_tokens',
        'ib_categories'
    ];
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$table");
    }
}
register_uninstall_hook(__FILE__, 'ib_uninstall_plugin');
