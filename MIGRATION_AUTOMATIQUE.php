<?php
/**
 * 🚀 MIGRATION AUTOMATIQUE VERS LE SYSTÈME MODERNE
 * ================================================================
 * Script de migration complète et automatique
 * Exécute toutes les étapes nécessaires en une seule fois
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
 * 🔍 ÉTAPE 1 : VÉRIFICATION DES PRÉREQUIS
 */
function check_prerequisites() {
    $checks = [];
    
    // Vérifier les fichiers requis
    $required_files = [
        'assets/css/ib-notif-refonte.css',
        'includes/notifications-refonte-integration.php',
        'templates/notification-panel-refonte.php'
    ];
    
    foreach ($required_files as $file) {
        $path = plugin_dir_path(__FILE__) . $file;
        $checks['files'][$file] = file_exists($path);
    }
    
    // Vérifier WordPress et PHP
    $checks['wordpress'] = version_compare(get_bloginfo('version'), '5.0', '>=');
    $checks['php'] = version_compare(PHP_VERSION, '7.4', '>=');
    
    // Vérifier les permissions de base de données
    global $wpdb;
    $checks['database'] = $wpdb->get_var("SELECT 1") === '1';
    
    return $checks;
}

/**
 * 🗄️ ÉTAPE 2 : MISE À JOUR DE LA BASE DE DONNÉES
 */
function upgrade_database() {
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    $results = [];
    
    // Vérifier si la table existe
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
    
    if (!$table_exists) {
        // Créer la table complète
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
            updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_type (type),
            INDEX idx_target (target),
            INDEX idx_created_at (created_at),
            INDEX idx_archived_at (archived_at),
            INDEX idx_client_service (client_name, service_name)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        $results['table_created'] = true;
    } else {
        // Mettre à jour la table existante
        $columns = $wpdb->get_col("DESCRIBE {$table}");
        
        $new_columns = [
            'client_name' => 'VARCHAR(255) NULL',
            'service_name' => 'VARCHAR(255) NULL',
            'archived_at' => 'DATETIME NULL',
            'archive_reason' => 'VARCHAR(255) NULL',
            'updated_at' => 'DATETIME NULL ON UPDATE CURRENT_TIMESTAMP'
        ];
        
        foreach ($new_columns as $column => $definition) {
            if (!in_array($column, $columns)) {
                $wpdb->query("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
                $results['columns_added'][] = $column;
            }
        }
        
        // Ajouter les nouveaux index
        $indexes = [
            'idx_target' => 'target',
            'idx_archived_at' => 'archived_at',
            'idx_client_service' => 'client_name, service_name'
        ];
        
        foreach ($indexes as $index_name => $columns_def) {
            $existing = $wpdb->get_results("SHOW INDEX FROM {$table} WHERE Key_name = '{$index_name}'");
            if (empty($existing)) {
                $wpdb->query("CREATE INDEX {$index_name} ON {$table} ({$columns_def})");
                $results['indexes_added'][] = $index_name;
            }
        }
    }
    
    return $results;
}

/**
 * 📦 ÉTAPE 3 : MIGRATION DES DONNÉES
 */
function migrate_data() {
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    $results = [];
    
    // Extraire les informations des messages existants
    $notifications = $wpdb->get_results("
        SELECT id, message 
        FROM {$table} 
        WHERE (client_name IS NULL OR client_name = '') 
        AND message IS NOT NULL
    ");
    
    $migrated = 0;
    foreach ($notifications as $notification) {
        $client_name = extract_client_name($notification->message);
        $service_name = extract_service_name($notification->message);
        
        if ($client_name || $service_name) {
            $wpdb->update(
                $table,
                [
                    'client_name' => $client_name,
                    'service_name' => $service_name,
                    'updated_at' => current_time('mysql')
                ],
                ['id' => $notification->id]
            );
            $migrated++;
        }
    }
    
    $results['notifications_migrated'] = $migrated;
    
    // Nettoyer les doublons
    $duplicates = $wpdb->query("
        DELETE n1 FROM {$table} n1
        INNER JOIN {$table} n2 
        WHERE n1.id > n2.id 
        AND n1.message = n2.message 
        AND n1.created_at = n2.created_at
    ");
    $results['duplicates_removed'] = $duplicates;
    
    return $results;
}

/**
 * ⚙️ ÉTAPE 4 : CONFIGURATION DU SYSTÈME
 */
function configure_system() {
    $options = [
        'ib_notif_auto_refresh' => true,
        'ib_notif_refresh_interval' => 30000,
        'ib_notif_auto_archive_days' => 7,
        'ib_notif_max_notifications' => 50,
        'ib_notif_group_emails' => true,
        'ib_notif_smart_cleanup' => true,
        'ib_notif_refonte_activated' => true,
        'ib_notif_refonte_version' => '3.0.0',
        'ib_notif_migration_date' => current_time('mysql'),
        'ib_notif_old_system_backup' => get_option('ib_notif_settings', [])
    ];
    
    $results = [];
    foreach ($options as $option => $value) {
        $old_value = get_option($option);
        if ($old_value === false) {
            add_option($option, $value);
            $results['options_added'][] = $option;
        } else {
            update_option($option, $value);
            $results['options_updated'][] = $option;
        }
    }
    
    return $results;
}

/**
 * ⏰ ÉTAPE 5 : PROGRAMMATION DES TÂCHES
 */
function setup_cron_jobs() {
    $results = [];
    
    // Supprimer les anciennes tâches
    wp_clear_scheduled_hook('ib_old_notification_cleanup');
    
    // Programmer les nouvelles tâches
    if (!wp_next_scheduled('ib_daily_cleanup')) {
        wp_schedule_event(time(), 'daily', 'ib_daily_cleanup');
        $results['daily_cleanup'] = 'scheduled';
    }
    
    if (!wp_next_scheduled('ib_weekly_archive')) {
        wp_schedule_event(time(), 'weekly', 'ib_weekly_archive');
        $results['weekly_archive'] = 'scheduled';
    }
    
    if (!wp_next_scheduled('ib_monthly_optimization')) {
        wp_schedule_event(time(), 'monthly', 'ib_monthly_optimization');
        $results['monthly_optimization'] = 'scheduled';
    }
    
    return $results;
}

/**
 * 🧹 ÉTAPE 6 : NETTOYAGE ET OPTIMISATION
 */
function cleanup_and_optimize() {
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    $results = [];
    
    // Archiver les très anciennes notifications
    $archived = $wpdb->query($wpdb->prepare("
        UPDATE {$table} 
        SET status = 'archived', 
            archived_at = NOW(), 
            archive_reason = 'migration_cleanup'
        WHERE status = 'read' 
        AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND archived_at IS NULL
    "));
    $results['old_notifications_archived'] = $archived;
    
    // Supprimer les notifications très anciennes (plus de 90 jours)
    $deleted = $wpdb->query($wpdb->prepare("
        DELETE FROM {$table} 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
        AND status = 'archived'
    "));
    $results['very_old_deleted'] = $deleted;
    
    // Optimiser la table
    $wpdb->query("OPTIMIZE TABLE {$table}");
    $results['table_optimized'] = true;
    
    return $results;
}

/**
 * 🎯 FONCTIONS UTILITAIRES
 */
function extract_client_name($message) {
    $patterns = [
        '/pour ([A-Za-zÀ-ÿ\s\-\']+) le \d/',
        '/([A-Za-zÀ-ÿ\s\-\']+) a réservé/',
        '/([A-Za-zÀ-ÿ\s\-\']+) a annulé/',
        '/client[:\s]+([A-Za-zÀ-ÿ\s\-\']+)/i'
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
    $patterns = [
        '/réservé ([^pour]+) pour/',
        '/: ([^pour]+) pour/',
        '/service[:\s]+([^le\n]+)/i',
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

/**
 * 🚀 EXÉCUTION DE LA MIGRATION COMPLÈTE
 */
function execute_full_migration() {
    $migration_log = [];
    $start_time = microtime(true);
    
    try {
        // Étape 1 : Vérifications
        $migration_log['step_1'] = check_prerequisites();
        
        // Vérifier que tout est OK avant de continuer
        $can_proceed = true;
        foreach ($migration_log['step_1']['files'] as $file => $exists) {
            if (!$exists) $can_proceed = false;
        }
        if (!$migration_log['step_1']['wordpress'] || !$migration_log['step_1']['php']) {
            $can_proceed = false;
        }
        
        if (!$can_proceed) {
            throw new Exception('Prérequis non satisfaits');
        }
        
        // Étape 2 : Base de données
        $migration_log['step_2'] = upgrade_database();
        
        // Étape 3 : Migration des données
        $migration_log['step_3'] = migrate_data();
        
        // Étape 4 : Configuration
        $migration_log['step_4'] = configure_system();
        
        // Étape 5 : Tâches programmées
        $migration_log['step_5'] = setup_cron_jobs();
        
        // Étape 6 : Nettoyage
        $migration_log['step_6'] = cleanup_and_optimize();
        
        $end_time = microtime(true);
        $migration_log['execution_time'] = round($end_time - $start_time, 2);
        $migration_log['success'] = true;
        $migration_log['message'] = 'Migration complète réussie !';
        
    } catch (Exception $e) {
        $migration_log['success'] = false;
        $migration_log['error'] = $e->getMessage();
        $migration_log['message'] = 'Erreur lors de la migration : ' . $e->getMessage();
    }
    
    // Sauvegarder le log de migration
    update_option('ib_notif_migration_log', $migration_log);
    
    return $migration_log;
}

// Traitement de la migration
if (isset($_GET['migrate']) && $_GET['migrate'] === 'auto') {
    $result = execute_full_migration();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration Automatique - Notifications Modernes</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
            background: #f9fafb;
        }
        .migration-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .migration-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .migration-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .migration-subtitle {
            color: #6b7280;
            font-size: 1.2rem;
        }
        .migration-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .migration-step {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #e8b4cb;
        }
        .migration-step h3 {
            margin: 0 0 1rem 0;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .migration-step p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }
        .migration-btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: #e8b4cb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
        }
        .migration-btn:hover {
            background: #d89bb5;
            transform: translateY(-1px);
        }
        .migration-result {
            margin-top: 2rem;
            padding: 1.5rem;
            border-radius: 8px;
            font-weight: 600;
        }
        .migration-result.success {
            background: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #065f46;
        }
        .migration-result.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .migration-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .migration-details pre {
            background: white;
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 0.8rem;
            max-height: 400px;
            overflow-y: auto;
        }
        .migration-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .migration-btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        .migration-btn-secondary:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="migration-container">
        <div class="migration-header">
            <h1 class="migration-title">🚀 Migration Automatique</h1>
            <p class="migration-subtitle">Passage au système de notifications moderne</p>
        </div>

        <?php if (isset($result)): ?>
            <div class="migration-result <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <?php echo $result['message']; ?>
                <?php if ($result['success']): ?>
                    <br><small>Temps d'exécution : <?php echo $result['execution_time']; ?>s</small>
                <?php endif; ?>
            </div>
            
            <?php if (isset($result['step_1'])): ?>
                <div class="migration-details">
                    <h3>📊 Détails de la migration</h3>
                    <pre><?php echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="migration-steps">
                <div class="migration-step">
                    <h3>🔍 Étape 1 : Vérifications</h3>
                    <p>Contrôle des fichiers requis, versions WordPress/PHP, permissions base de données</p>
                </div>
                <div class="migration-step">
                    <h3>🗄️ Étape 2 : Base de données</h3>
                    <p>Création/mise à jour de la table, ajout des nouvelles colonnes et index</p>
                </div>
                <div class="migration-step">
                    <h3>📦 Étape 3 : Migration données</h3>
                    <p>Extraction des noms clients/services, nettoyage des doublons</p>
                </div>
                <div class="migration-step">
                    <h3>⚙️ Étape 4 : Configuration</h3>
                    <p>Paramétrage des options, sauvegarde de l'ancien système</p>
                </div>
                <div class="migration-step">
                    <h3>⏰ Étape 5 : Tâches programmées</h3>
                    <p>Configuration du nettoyage automatique et de l'archivage</p>
                </div>
                <div class="migration-step">
                    <h3>🧹 Étape 6 : Optimisation</h3>
                    <p>Nettoyage final, archivage des anciennes données, optimisation</p>
                </div>
            </div>

            <div style="text-align: center;">
                <button onclick="startMigration()" class="migration-btn">
                    🚀 Lancer la migration automatique
                </button>
            </div>
        <?php endif; ?>

        <div class="migration-actions">
            <a href="<?php echo admin_url('admin.php?page=institut-booking-dashboard'); ?>" class="migration-btn migration-btn-secondary">
                🏠 Dashboard
            </a>
            <?php if (isset($result) && $result['success']): ?>
                <a href="test-integration-refonte.php" class="migration-btn">
                    🧪 Tester l'intégration
                </a>
                <a href="demo-notifications-refonte.php" class="migration-btn">
                    🎨 Voir la démo
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function startMigration() {
        if (confirm('🚀 MIGRATION AUTOMATIQUE\n\nCette opération va :\n✅ Vérifier tous les prérequis\n✅ Mettre à jour la base de données\n✅ Migrer vos données existantes\n✅ Configurer le nouveau système\n✅ Programmer les tâches automatiques\n✅ Optimiser les performances\n\n⚠️ Assurez-vous d\'avoir une sauvegarde !\n\nContinuer ?')) {
            window.location.href = '?migrate=auto';
        }
    }
    </script>
</body>
</html>
