<?php
/**
 * 🚀 MIGRATION RAPIDE VERS LE SYSTÈME MODERNE
 * ================================================================
 * Script de migration express pour activer rapidement le nouveau système
 * À exécuter une seule fois après avoir modifié layout.php
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
 * 🔧 MIGRATION EXPRESS
 */
function execute_quick_migration() {
    global $wpdb;
    $results = [];
    
    try {
        // 1. Vérifier/créer la table
        $table = $wpdb->prefix . 'ib_notifications';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
        
        if (!$table_exists) {
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
                INDEX idx_created_at (created_at)
            ) {$charset_collate};";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            $results['table_created'] = true;
        } else {
            $results['table_exists'] = true;
        }
        
        // 2. Configurer les options par défaut
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
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
                $results['options_set'][] = $option;
            }
        }
        
        // 3. Créer quelques notifications de test si la table est vide
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        if ($count == 0) {
            $test_notifications = [
                [
                    'type' => 'booking_new',
                    'message' => 'Nouvelle réservation : Soin visage pour Marie Dubois le ' . date('d/m/Y', strtotime('+1 day')),
                    'target' => 'admin',
                    'status' => 'unread',
                    'client_name' => 'Marie Dubois',
                    'service_name' => 'Soin visage',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
                ],
                [
                    'type' => 'booking_confirmed',
                    'message' => 'Réservation confirmée : Massage relaxant pour Julie Martin le ' . date('d/m/Y', strtotime('+2 days')),
                    'target' => 'admin',
                    'status' => 'unread',
                    'client_name' => 'Julie Martin',
                    'service_name' => 'Massage relaxant',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
                ],
                [
                    'type' => 'email',
                    'message' => 'Email de confirmation envoyé à marie.dubois@email.com',
                    'target' => 'admin',
                    'status' => 'read',
                    'client_name' => 'Marie Dubois',
                    'service_name' => 'Soin visage',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                ]
            ];
            
            foreach ($test_notifications as $notification) {
                $wpdb->insert($table, $notification);
            }
            $results['test_notifications_created'] = count($test_notifications);
        }
        
        // 4. Vérifier que les fichiers existent
        $css_path = plugin_dir_path(__FILE__) . 'assets/css/ib-notif-refonte.css';
        
        $results['files_check'] = [
            'css' => file_exists($css_path),
        ];
        
        return [
            'success' => true,
            'results' => $results,
            'message' => 'Migration rapide terminée avec succès !'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Erreur lors de la migration : ' . $e->getMessage()
        ];
    }
}

// Traitement de la migration
if (isset($_GET['migrate']) && $_GET['migrate'] === 'now') {
    $result = execute_quick_migration();
    
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
    <title>Migration Rapide - Notifications Modernes</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
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
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .migration-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        .migration-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        .migration-message.success {
            background: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #065f46;
        }
        .migration-message.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
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
        }
        .migration-btn:hover {
            background: #d89bb5;
            transform: translateY(-1px);
        }
        .migration-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        .migration-details h3 {
            margin: 0 0 1rem 0;
            color: #1f2937;
        }
        .migration-details pre {
            background: white;
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 0.9rem;
        }
        .migration-steps {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .migration-steps h3 {
            margin: 0 0 1rem 0;
            color: #92400e;
        }
        .migration-steps ol {
            margin: 0;
            padding-left: 1.5rem;
            color: #92400e;
        }
        .migration-steps li {
            margin-bottom: 0.5rem;
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
            <h1 class="migration-title">🚀 Migration Rapide</h1>
            <p class="migration-subtitle">Activation express du système moderne</p>
        </div>

        <?php if (isset($message)): ?>
            <div class="migration-message <?php echo $result['success'] ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_GET['migrate'])): ?>
            <div class="migration-steps">
                <h3>📋 Ce que fait cette migration :</h3>
                <ol>
                    <li>Vérifie/crée la table de notifications avec les nouvelles colonnes</li>
                    <li>Configure les options par défaut du système moderne</li>
                    <li>Crée quelques notifications de test si nécessaire</li>
                    <li>Vérifie la présence des fichiers CSS/JS</li>
                    <li>Active le nouveau système immédiatement</li>
                </ol>
            </div>

            <div style="text-align: center;">
                <button onclick="startMigration()" class="migration-btn">
                    ⚡ Lancer la migration rapide
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($details) && $details): ?>
            <div class="migration-details">
                <h3>📊 Détails de la migration</h3>
                <pre><?php echo json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            </div>
        <?php endif; ?>

        <div class="migration-actions">
            <a href="<?php echo admin_url('admin.php?page=institut-booking-dashboard'); ?>" class="migration-btn migration-btn-secondary">
                🏠 Dashboard
            </a>
            <a href="test-integration-refonte.php" class="migration-btn migration-btn-secondary">
                🧪 Tester l'intégration
            </a>
            <?php if (isset($result) && $result['success']): ?>
                <a href="demo-notifications-refonte.php" class="migration-btn">
                    🎨 Voir la démo
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function startMigration() {
        if (confirm('Êtes-vous sûr de vouloir lancer la migration ?\n\nCela va :\n- Modifier la base de données\n- Configurer les options\n- Activer le nouveau système')) {
            window.location.href = '?migrate=now';
        }
    }
    </script>
</body>
</html>
