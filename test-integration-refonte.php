<?php
/**
 * 🧪 TEST D'INTÉGRATION DU SYSTÈME DE NOTIFICATIONS MODERNE
 * ================================================================
 * Fichier de test pour vérifier que l'intégration fonctionne correctement
 * À exécuter après avoir modifié layout.php
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
 * 🔍 VÉRIFICATIONS D'INTÉGRATION
 */
function check_integration_status() {
    $checks = [];
    
    // 1. Vérifier que les fichiers CSS/JS existent
    $css_path = plugin_dir_path(__FILE__) . 'assets/css/ib-notif-refonte.css';
    $js_path = plugin_dir_path(__FILE__) . 'assets/js/ib-notif-refonte.js';
    $integration_path = plugin_dir_path(__FILE__) . 'includes/notifications-refonte-integration.php';
    
    $checks['files'] = [
        'css' => file_exists($css_path),
        'js' => file_exists($js_path),
        'integration' => file_exists($integration_path)
    ];
    
    // 2. Vérifier que la table existe
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
    $checks['database'] = $table_exists;
    
    // 3. Vérifier les options de configuration
    $checks['options'] = [
        'auto_refresh' => get_option('ib_notif_auto_refresh'),
        'refresh_interval' => get_option('ib_notif_refresh_interval'),
        'refonte_activated' => get_option('ib_notif_refonte_activated'),
        'version' => get_option('ib_notif_refonte_version')
    ];
    
    // 4. Vérifier les actions AJAX
    $checks['ajax_actions'] = [
        'get_notifications' => has_action('wp_ajax_ib_get_notifications_refonte'),
        'mark_read' => has_action('wp_ajax_ib_mark_notification_read_refonte'),
        'delete_notification' => has_action('wp_ajax_ib_delete_notification_refonte'),
        'archive_notification' => has_action('wp_ajax_ib_archive_notification_refonte')
    ];
    
    // 5. Compter les notifications existantes
    if ($table_exists) {
        $checks['notifications_count'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $checks['unread_count'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'unread'");
    }
    
    return $checks;
}

/**
 * 🎨 GÉNÉRER DU CSS DE TEST
 */
function generate_test_css() {
    return "
    .integration-test {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .test-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e8b4cb;
    }
    
    .test-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .test-subtitle {
        color: #6b7280;
        font-size: 1.1rem;
    }
    
    .test-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #e8b4cb;
    }
    
    .test-section h3 {
        margin: 0 0 1rem 0;
        color: #1f2937;
        font-size: 1.2rem;
    }
    
    .test-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .test-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        background: white;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }
    
    .test-status {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .test-status.success {
        background: #dcfce7;
        color: #166534;
    }
    
    .test-status.error {
        background: #fef2f2;
        color: #991b1b;
    }
    
    .test-status.warning {
        background: #fffbeb;
        color: #92400e;
    }
    
    .test-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .test-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .test-btn-primary {
        background: #e8b4cb;
        color: white;
    }
    
    .test-btn-primary:hover {
        background: #d89bb5;
        transform: translateY(-1px);
    }
    
    .test-btn-secondary {
        background: #f3f4f6;
        color: #374151;
    }
    
    .test-btn-secondary:hover {
        background: #e5e7eb;
    }
    
    .test-demo-bell {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #e8b4cb;
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .test-demo-bell:hover {
        background: #d89bb5;
        transform: scale(1.05);
    }
    ";
}

// Exécuter les vérifications
$integration_status = check_integration_status();
$all_good = true;

// Vérifier si tout est OK
foreach ($integration_status['files'] as $file => $exists) {
    if (!$exists) $all_good = false;
}
if (!$integration_status['database']) $all_good = false;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test d'Intégration - Notifications Modernes</title>
    <style><?php echo generate_test_css(); ?></style>
</head>
<body>
    <div class="integration-test">
        <div class="test-header">
            <h1 class="test-title">🧪 Test d'Intégration</h1>
            <p class="test-subtitle">Vérification du système de notifications moderne</p>
        </div>

        <!-- Statut global -->
        <div class="test-section">
            <h3>📊 Statut Global</h3>
            <div class="test-item">
                <span>Intégration complète</span>
                <span class="test-status <?php echo $all_good ? 'success' : 'error'; ?>">
                    <?php echo $all_good ? '✅ OK' : '❌ Problème'; ?>
                </span>
            </div>
        </div>

        <!-- Vérification des fichiers -->
        <div class="test-section">
            <h3>📁 Fichiers Requis</h3>
            <div class="test-grid">
                <?php foreach ($integration_status['files'] as $file => $exists): ?>
                    <div class="test-item">
                        <span><?php echo strtoupper($file); ?></span>
                        <span class="test-status <?php echo $exists ? 'success' : 'error'; ?>">
                            <?php echo $exists ? '✅ Trouvé' : '❌ Manquant'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Vérification de la base de données -->
        <div class="test-section">
            <h3>🗄️ Base de Données</h3>
            <div class="test-grid">
                <div class="test-item">
                    <span>Table notifications</span>
                    <span class="test-status <?php echo $integration_status['database'] ? 'success' : 'error'; ?>">
                        <?php echo $integration_status['database'] ? '✅ Existe' : '❌ Manquante'; ?>
                    </span>
                </div>
                <?php if (isset($integration_status['notifications_count'])): ?>
                    <div class="test-item">
                        <span>Total notifications</span>
                        <span class="test-status success"><?php echo $integration_status['notifications_count']; ?></span>
                    </div>
                    <div class="test-item">
                        <span>Non lues</span>
                        <span class="test-status <?php echo $integration_status['unread_count'] > 0 ? 'warning' : 'success'; ?>">
                            <?php echo $integration_status['unread_count']; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Vérification des options -->
        <div class="test-section">
            <h3>⚙️ Configuration</h3>
            <div class="test-grid">
                <?php foreach ($integration_status['options'] as $option => $value): ?>
                    <div class="test-item">
                        <span><?php echo str_replace('_', ' ', $option); ?></span>
                        <span class="test-status <?php echo $value ? 'success' : 'warning'; ?>">
                            <?php echo $value ? '✅ ' . (is_bool($value) ? 'Activé' : $value) : '⚠️ Non défini'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Vérification des actions AJAX -->
        <div class="test-section">
            <h3>🔗 Actions AJAX</h3>
            <div class="test-grid">
                <?php foreach ($integration_status['ajax_actions'] as $action => $registered): ?>
                    <div class="test-item">
                        <span><?php echo str_replace('_', ' ', $action); ?></span>
                        <span class="test-status <?php echo $registered ? 'success' : 'error'; ?>">
                            <?php echo $registered ? '✅ Enregistrée' : '❌ Manquante'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Test de la cloche -->
        <div class="test-section">
            <h3>🔔 Test de la Cloche</h3>
            <p>Cliquez sur la cloche ci-dessous pour tester l'ouverture du panneau :</p>
            <div style="text-align: center; margin: 1rem 0;">
                <a href="#" class="test-demo-bell" onclick="toggleNotificationPanel(); return false;">
                    🔔 Tester la cloche de notifications
                </a>
            </div>
        </div>

        <!-- Actions -->
        <div class="test-actions">
            <a href="<?php echo admin_url('admin.php?page=institut-booking-dashboard'); ?>" class="test-btn test-btn-primary">
                🏠 Retour au Dashboard
            </a>
            <a href="demo-notifications-refonte.php" class="test-btn test-btn-secondary">
                🎨 Voir la Démo
            </a>
            <?php if (!$all_good): ?>
                <a href="activate-notifications-refonte.php" class="test-btn test-btn-primary">
                    🚀 Activer le Système
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Test de la fonction de toggle
    function toggleNotificationPanel() {
        if (typeof NotificationRefonte !== 'undefined') {
            NotificationRefonte.togglePanel();
            alert('✅ Le panneau de notifications moderne fonctionne !');
        } else {
            alert('❌ Le système de notifications moderne n\'est pas encore chargé.\n\nVérifiez que :\n- Les fichiers CSS/JS sont présents\n- L\'intégration est activée\n- Vous êtes sur une page admin du plugin');
        }
    }
    </script>
</body>
</html>
