<?php
/**
 * 🎨 ACTIVATION DU SYSTÈME DE NOTIFICATIONS MODERNE
 * ================================================================
 * Script d'activation pour intégrer le nouveau système de notifications
 * À exécuter une seule fois pour migrer vers la version moderne
 * Version: 3.0.0 - Refonte complète
 */

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier les permissions admin
if (!current_user_can('manage_options')) {
    wp_die('Accès non autorisé');
}

/**
 * 🚀 ACTIVATION DU NOUVEAU SYSTÈME
 */
function activate_notifications_refonte() {
    global $wpdb;
    
    $results = [];
    
    try {
        // 1. Mise à jour de la table des notifications
        $results['database'] = upgrade_notifications_table();
        
        // 2. Migration des données existantes
        $results['migration'] = migrate_existing_notifications();
        
        // 3. Configuration des options par défaut
        $results['options'] = setup_default_options();
        
        // 4. Programmation des tâches automatiques
        $results['cron'] = setup_cron_jobs();
        
        // 5. Nettoyage initial
        $results['cleanup'] = initial_cleanup();
        
        return [
            'success' => true,
            'results' => $results,
            'message' => 'Système de notifications moderne activé avec succès !'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Erreur lors de l\'activation : ' . $e->getMessage()
        ];
    }
}

/**
 * 🗄️ MISE À JOUR DE LA TABLE
 */
function upgrade_notifications_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    
    $results = [];
    
    // Vérifier si la table existe
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
    
    if (!$table_exists) {
        // Créer la table si elle n'existe pas
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(32) NOT NULL,
            message TEXT NOT NULL,
            target VARCHAR(32) DEFAULT 'admin',
            status VARCHAR(16) DEFAULT 'unread',
            link VARCHAR(255) DEFAULT NULL,
            client_name VARCHAR(255) NULL,
            service_name VARCHAR(255) NULL,
            archived_at DATETIME NULL,
            archive_reason VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_type (type),
            INDEX idx_created_at (created_at),
            INDEX idx_archived_at (archived_at)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        $results['table_created'] = true;
    } else {
        // Ajouter les nouvelles colonnes si nécessaire
        $columns = $wpdb->get_col("DESCRIBE {$table}");
        
        $new_columns = [
            'client_name' => 'VARCHAR(255) NULL',
            'service_name' => 'VARCHAR(255) NULL',
            'archived_at' => 'DATETIME NULL',
            'archive_reason' => 'VARCHAR(255) NULL'
        ];
        
        foreach ($new_columns as $column => $definition) {
            if (!in_array($column, $columns)) {
                $wpdb->query("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
                $results['columns_added'][] = $column;
            }
        }
        
        // Ajouter les index pour les performances
        $indexes = [
            'idx_status' => 'status',
            'idx_type' => 'type',
            'idx_created_at' => 'created_at',
            'idx_archived_at' => 'archived_at'
        ];
        
        foreach ($indexes as $index_name => $column) {
            $existing_indexes = $wpdb->get_results("SHOW INDEX FROM {$table} WHERE Key_name = '{$index_name}'");
            if (empty($existing_indexes)) {
                $wpdb->query("CREATE INDEX {$index_name} ON {$table} ({$column})");
                $results['indexes_added'][] = $index_name;
            }
        }
    }
    
    return $results;
}

/**
 * 📦 MIGRATION DES DONNÉES EXISTANTES
 */
function migrate_existing_notifications() {
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    
    $results = [];
    
    // Extraire les noms de clients et services des messages existants
    $notifications = $wpdb->get_results("SELECT id, message FROM {$table} WHERE client_name IS NULL");
    
    foreach ($notifications as $notification) {
        $client_name = extract_client_name($notification->message);
        $service_name = extract_service_name($notification->message);
        
        if ($client_name || $service_name) {
            $wpdb->update(
                $table,
                [
                    'client_name' => $client_name,
                    'service_name' => $service_name
                ],
                ['id' => $notification->id]
            );
            $results['migrated']++;
        }
    }
    
    return $results;
}

/**
 * ⚙️ CONFIGURATION DES OPTIONS PAR DÉFAUT
 */
function setup_default_options() {
    $default_options = [
        'ib_notif_auto_refresh' => true,
        'ib_notif_refresh_interval' => 30000,
        'ib_notif_auto_archive_days' => 7,
        'ib_notif_max_notifications' => 50,
        'ib_notif_group_emails' => true,
        'ib_notif_smart_cleanup' => true,
        'ib_notif_refonte_activated' => true,
        'ib_notif_refonte_version' => '3.0.0'
    ];
    
    $results = [];
    
    foreach ($default_options as $option => $value) {
        if (get_option($option) === false) {
            add_option($option, $value);
            $results['options_added'][] = $option;
        }
    }
    
    return $results;
}

/**
 * ⏰ PROGRAMMATION DES TÂCHES AUTOMATIQUES
 */
function setup_cron_jobs() {
    $results = [];
    
    // Nettoyage quotidien
    if (!wp_next_scheduled('ib_daily_cleanup')) {
        wp_schedule_event(time(), 'daily', 'ib_daily_cleanup');
        $results['daily_cleanup'] = 'scheduled';
    }
    
    // Archivage hebdomadaire
    if (!wp_next_scheduled('ib_weekly_archive')) {
        wp_schedule_event(time(), 'weekly', 'ib_weekly_archive');
        $results['weekly_archive'] = 'scheduled';
    }
    
    return $results;
}

/**
 * 🧹 NETTOYAGE INITIAL
 */
function initial_cleanup() {
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    
    $results = [];
    
    // Supprimer les très anciennes notifications (plus de 30 jours)
    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM {$table} WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
    ));
    $results['old_notifications_deleted'] = $deleted;
    
    // Marquer les notifications lues anciennes comme archivées
    $archived = $wpdb->query($wpdb->prepare(
        "UPDATE {$table} 
         SET status = 'archived', archived_at = NOW(), archive_reason = 'initial_cleanup'
         WHERE status = 'read' 
         AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
    ));
    $results['notifications_archived'] = $archived;
    
    return $results;
}

/**
 * 🔍 FONCTIONS UTILITAIRES D'EXTRACTION
 */
function extract_client_name($message) {
    // Patterns pour extraire le nom du client
    $patterns = [
        '/pour ([A-Za-zÀ-ÿ\s]+) le/',
        '/([A-Za-zÀ-ÿ\s]+) a réservé/',
        '/([A-Za-zÀ-ÿ\s]+) a annulé/',
        '/à ([A-Za-zÀ-ÿ\s]+@[^\s]+)/' // Email pattern
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $message, $matches)) {
            $name = trim($matches[1]);
            if (strlen($name) > 2 && !strpos($name, '@')) {
                return $name;
            }
        }
    }
    
    return null;
}

function extract_service_name($message) {
    // Patterns pour extraire le nom du service
    $patterns = [
        '/réservé ([^pour]+) pour/',
        '/: ([^pour]+) pour/',
        '/service ([^le]+) le/',
        '/annulé ([^le]+) le/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $message, $matches)) {
            $service = trim($matches[1]);
            if (strlen($service) > 2) {
                return $service;
            }
        }
    }
    
    return null;
}

// Traitement de l'activation
if (isset($_GET['activate']) && $_GET['activate'] === 'refonte') {
    $result = activate_notifications_refonte();
    
    if ($result['success']) {
        $message = "✅ " . $result['message'];
        $details = $result['results'];
    } else {
        $message = "❌ " . $result['message'];
        $details = null;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation - Notifications Modernes</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
            background: #f9fafb;
        }
        .activation-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .activation-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .activation-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .activation-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        .activation-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        .activation-message.success {
            background: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #065f46;
        }
        .activation-message.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .activation-btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: #e8b4cb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .activation-btn:hover {
            background: #d89bb5;
            transform: translateY(-1px);
        }
        .activation-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        .activation-details h3 {
            margin: 0 0 1rem 0;
            color: #1f2937;
        }
        .activation-details pre {
            background: white;
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 0.9rem;
        }
        .activation-warning {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .activation-warning h3 {
            margin: 0 0 1rem 0;
            color: #92400e;
        }
        .activation-warning p {
            margin: 0;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="activation-container">
        <div class="activation-header">
            <h1 class="activation-title">🚀 Activation du Système Moderne</h1>
            <p class="activation-subtitle">Migration vers les notifications refondues</p>
        </div>

        <?php if (isset($message)): ?>
            <div class="activation-message <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_GET['activate'])): ?>
            <div class="activation-warning">
                <h3>⚠️ Attention</h3>
                <p>Cette opération va migrer votre système de notifications vers la nouvelle version moderne. 
                   Assurez-vous d'avoir une sauvegarde de votre base de données avant de continuer.</p>
            </div>

            <div style="text-align: center;">
                <a href="?activate=refonte" class="activation-btn">
                    🎨 Activer le système moderne
                </a>
            </div>
        <?php endif; ?>

        <?php if (isset($details) && $details): ?>
            <div class="activation-details">
                <h3>📊 Détails de l'activation</h3>
                <pre><?php echo json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
