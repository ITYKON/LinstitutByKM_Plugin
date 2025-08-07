<?php
// Table d'installation pour les catégories, clients, coupons, extras, feedback, logs, etc.
if (!defined('ABSPATH')) exit;

function ib_install_plugin_advanced() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $tables = [];
    $tables[] = "CREATE TABLE {$wpdb->prefix}ib_categories (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        color VARCHAR(16),
        icon VARCHAR(64),
        PRIMARY KEY (id)
    ) $charset_collate;";
    $tables[] = "CREATE TABLE {$wpdb->prefix}ib_clients (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255),
        email VARCHAR(255),
        phone VARCHAR(32),
        notes TEXT,
        tags VARCHAR(255),
        created_at DATETIME,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $tables[] = "CREATE TABLE {$wpdb->prefix}ib_coupons (
        id INT NOT NULL AUTO_INCREMENT,
        code VARCHAR(64) NOT NULL,
        discount FLOAT,
        type VARCHAR(16),
        usage_limit INT,
        used_count INT DEFAULT 0,
        valid_from DATE,
        valid_to DATE,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $tables[] = "CREATE TABLE {$wpdb->prefix}ib_extras (
        id INT NOT NULL AUTO_INCREMENT,
        service_id INT,
        name VARCHAR(255),
        price FLOAT,
        duration INT,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $tables[] = "CREATE TABLE {$wpdb->prefix}ib_feedback (
        id INT NOT NULL AUTO_INCREMENT,
        booking_id INT,
        client_id INT,
        rating INT,
        comment TEXT,
        created_at DATETIME,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $tables[] = "CREATE TABLE {$wpdb->prefix}ib_logs (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT,
        action VARCHAR(255),
        context TEXT,
        created_at DATETIME,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    foreach ($tables as $sql) {
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'ib_install_plugin_advanced');
