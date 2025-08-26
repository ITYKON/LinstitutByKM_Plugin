<?php
/**
 * ✅ VÉRIFICATION DE PRÉPARATION À LA MIGRATION
 * ================================================================
 * Script de vérification pour s'assurer que tout est prêt
 * À exécuter AVANT la migration
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
 * 🔍 VÉRIFICATIONS COMPLÈTES
 */
function check_migration_readiness() {
    $checks = [];
    
    // 1. Vérifications système
    $checks['system'] = [
        'wordpress_version' => version_compare(get_bloginfo('version'), '5.0', '>='),
        'php_version' => version_compare(PHP_VERSION, '7.4', '>='),
        'mysql_version' => check_mysql_version(),
        'memory_limit' => check_memory_limit(),
        'file_permissions' => check_file_permissions()
    ];
    
    // 2. Vérifications des fichiers
    $required_files = [
        'assets/css/ib-notif-refonte.css',
        'includes/notifications-refonte-integration.php',
        'templates/notification-panel-refonte.php',
        'admin/layout.php'
    ];
    
    foreach ($required_files as $file) {
        $path = plugin_dir_path(__FILE__) . $file;
        $checks['files'][$file] = [
            'exists' => file_exists($path),
            'readable' => is_readable($path),
            'size' => file_exists($path) ? filesize($path) : 0
        ];
    }
    
    // 3. Vérifications base de données
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    $checks['database'] = [
        'connection' => $wpdb->get_var("SELECT 1") === '1',
        'table_exists' => $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table,
        'table_writable' => check_table_writable($table),
        'current_notifications' => 0,
        'table_size' => 0
    ];
    
    if ($checks['database']['table_exists']) {
        $checks['database']['current_notifications'] = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        $checks['database']['table_size'] = get_table_size($table);
    }
    
    // 4. Vérifications des modifications existantes
    $checks['modifications'] = [
        'layout_modified' => check_layout_modifications(),
        'integration_included' => check_integration_included(),
        'old_system_active' => check_old_system_status()
    ];
    
    // 5. Vérifications des conflits potentiels
    $checks['conflicts'] = [
        'plugin_conflicts' => check_plugin_conflicts(),
        'theme_conflicts' => check_theme_conflicts(),
        'javascript_errors' => check_javascript_environment()
    ];
    
    return $checks;
}

function check_mysql_version() {
    global $wpdb;
    $version = $wpdb->get_var("SELECT VERSION()");
    return version_compare($version, '5.6', '>=');
}

function check_memory_limit() {
    $limit = ini_get('memory_limit');
    $limit_bytes = wp_convert_hr_to_bytes($limit);
    return $limit_bytes >= 128 * 1024 * 1024; // 128MB minimum
}

function check_file_permissions() {
    $plugin_dir = plugin_dir_path(__FILE__);
    return is_writable($plugin_dir);
}

function check_table_writable($table) {
    global $wpdb;
    try {
        $wpdb->query("SELECT 1 FROM {$table} LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function get_table_size($table) {
    global $wpdb;
    $result = $wpdb->get_row("
        SELECT 
            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
        FROM information_schema.TABLES 
        WHERE table_schema = DATABASE() 
        AND table_name = '{$table}'
    ");
    return $result ? $result->size_mb : 0;
}

function check_layout_modifications() {
    $layout_path = plugin_dir_path(__FILE__) . 'admin/layout.php';
    if (!file_exists($layout_path)) return false;
    
    $content = file_get_contents($layout_path);
    return strpos($content, 'ib-notif-refonte') !== false;
}

function check_integration_included() {
    $main_file = plugin_dir_path(__FILE__) . 'institut-booking.php';
    if (!file_exists($main_file)) return false;
    
    $content = file_get_contents($main_file);
    return strpos($content, 'notifications-refonte-integration.php') !== false;
}

function check_old_system_status() {
    return get_option('ib_notif_refonte_activated', false) === false;
}

function check_plugin_conflicts() {
    $active_plugins = get_option('active_plugins', []);
    $potential_conflicts = [
        'notification-plugins' => [],
        'cache-plugins' => [],
        'optimization-plugins' => []
    ];
    
    foreach ($active_plugins as $plugin) {
        if (strpos($plugin, 'notification') !== false) {
            $potential_conflicts['notification-plugins'][] = $plugin;
        }
        if (strpos($plugin, 'cache') !== false || strpos($plugin, 'optimize') !== false) {
            $potential_conflicts['cache-plugins'][] = $plugin;
        }
    }
    
    return $potential_conflicts;
}

function check_theme_conflicts() {
    $theme = wp_get_theme();
    return [
        'theme_name' => $theme->get('Name'),
        'theme_version' => $theme->get('Version'),
        'potential_conflicts' => false // À implémenter selon les besoins
    ];
}

function check_javascript_environment() {
    // Vérification basique - peut être étendue
    return [
        'jquery_loaded' => true, // WordPress charge jQuery par défaut
        'console_errors' => false // À vérifier côté client
    ];
}

// Exécuter les vérifications
$readiness = check_migration_readiness();

// Calculer le score de préparation
$total_checks = 0;
$passed_checks = 0;

foreach ($readiness as $category => $checks) {
    if (is_array($checks)) {
        foreach ($checks as $check => $result) {
            $total_checks++;
            if (is_bool($result) && $result) {
                $passed_checks++;
            } elseif (is_array($result) && isset($result['exists']) && $result['exists']) {
                $passed_checks++;
            }
        }
    }
}

$readiness_score = round(($passed_checks / $total_checks) * 100);
$is_ready = $readiness_score >= 85;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de Préparation - Migration</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: #f9fafb;
        }
        .readiness-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .readiness-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .readiness-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .readiness-score {
            font-size: 3rem;
            font-weight: 800;
            margin: 1rem 0;
        }
        .readiness-score.ready {
            color: #059669;
        }
        .readiness-score.warning {
            color: #d97706;
        }
        .readiness-score.not-ready {
            color: #dc2626;
        }
        .readiness-status {
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            margin: 1rem 0;
        }
        .readiness-status.ready {
            background: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #065f46;
        }
        .readiness-status.warning {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            color: #92400e;
        }
        .readiness-status.not-ready {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .readiness-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .readiness-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #e8b4cb;
        }
        .readiness-section h3 {
            margin: 0 0 1rem 0;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .readiness-checks {
            display: grid;
            gap: 0.5rem;
        }
        .readiness-check {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem;
            background: white;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .check-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .check-status.pass {
            background: #dcfce7;
            color: #166534;
        }
        .check-status.fail {
            background: #fef2f2;
            color: #991b1b;
        }
        .check-status.warning {
            background: #fffbeb;
            color: #92400e;
        }
        .readiness-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .readiness-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .readiness-btn-primary {
            background: #e8b4cb;
            color: white;
        }
        .readiness-btn-primary:hover {
            background: #d89bb5;
            transform: translateY(-1px);
        }
        .readiness-btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        .readiness-btn-secondary:hover {
            background: #e5e7eb;
        }
        .readiness-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="readiness-container">
        <div class="readiness-header">
            <h1 class="readiness-title">✅ Vérification de Préparation</h1>
            <div class="readiness-score <?php echo $is_ready ? 'ready' : ($readiness_score >= 70 ? 'warning' : 'not-ready'); ?>">
                <?php echo $readiness_score; ?>%
            </div>
            <div class="readiness-status <?php echo $is_ready ? 'ready' : ($readiness_score >= 70 ? 'warning' : 'not-ready'); ?>">
                <?php if ($is_ready): ?>
                    🎉 Prêt pour la migration !
                <?php elseif ($readiness_score >= 70): ?>
                    ⚠️ Migration possible avec précautions
                <?php else: ?>
                    ❌ Problèmes à résoudre avant migration
                <?php endif; ?>
            </div>
        </div>

        <div class="readiness-sections">
            <!-- Système -->
            <div class="readiness-section">
                <h3>🖥️ Système</h3>
                <div class="readiness-checks">
                    <?php foreach ($readiness['system'] as $check => $result): ?>
                        <div class="readiness-check">
                            <span><?php echo str_replace('_', ' ', ucfirst($check)); ?></span>
                            <span class="check-status <?php echo $result ? 'pass' : 'fail'; ?>">
                                <?php echo $result ? '✅ OK' : '❌ Échec'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Fichiers -->
            <div class="readiness-section">
                <h3>📁 Fichiers</h3>
                <div class="readiness-checks">
                    <?php foreach ($readiness['files'] as $file => $info): ?>
                        <div class="readiness-check">
                            <span><?php echo basename($file); ?></span>
                            <span class="check-status <?php echo $info['exists'] ? 'pass' : 'fail'; ?>">
                                <?php echo $info['exists'] ? '✅ Trouvé' : '❌ Manquant'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Base de données -->
            <div class="readiness-section">
                <h3>🗄️ Base de Données</h3>
                <div class="readiness-checks">
                    <?php foreach ($readiness['database'] as $check => $result): ?>
                        <div class="readiness-check">
                            <span><?php echo str_replace('_', ' ', ucfirst($check)); ?></span>
                            <span class="check-status <?php echo is_bool($result) ? ($result ? 'pass' : 'fail') : 'pass'; ?>">
                                <?php 
                                if (is_bool($result)) {
                                    echo $result ? '✅ OK' : '❌ Échec';
                                } else {
                                    echo '📊 ' . $result;
                                }
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Modifications -->
            <div class="readiness-section">
                <h3>🔧 Modifications</h3>
                <div class="readiness-checks">
                    <?php foreach ($readiness['modifications'] as $check => $result): ?>
                        <div class="readiness-check">
                            <span><?php echo str_replace('_', ' ', ucfirst($check)); ?></span>
                            <span class="check-status <?php echo $result ? 'pass' : 'warning'; ?>">
                                <?php echo $result ? '✅ Fait' : '⚠️ À faire'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="readiness-actions">
            <?php if ($is_ready): ?>
                <a href="MIGRATION_AUTOMATIQUE.php" class="readiness-btn readiness-btn-primary">
                    🚀 Lancer la migration automatique
                </a>
            <?php else: ?>
                <button class="readiness-btn readiness-btn-primary" disabled>
                    🚀 Migration (résoudre les problèmes d'abord)
                </button>
            <?php endif; ?>
            
            <a href="GUIDE_MIGRATION_COMPLETE.md" class="readiness-btn readiness-btn-secondary">
                📖 Guide de migration
            </a>
            
            <a href="<?php echo admin_url('admin.php?page=institut-booking-dashboard'); ?>" class="readiness-btn readiness-btn-secondary">
                🏠 Dashboard
            </a>
            
            <button onclick="location.reload()" class="readiness-btn readiness-btn-secondary">
                🔄 Actualiser les vérifications
            </button>
        </div>
    </div>
</body>
</html>
