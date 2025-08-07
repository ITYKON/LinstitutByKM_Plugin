<?php
/**
 * Script de diagnostic pour le sélecteur de pays
 * Placez ce fichier dans le répertoire racine du plugin et accédez-y via l'URL
 */

// Vérifier que nous sommes dans WordPress
if (!defined('ABSPATH')) {
    // Si nous ne sommes pas dans WordPress, définir les constantes de base
    define('ABSPATH', dirname(__FILE__) . '/');
    define('WP_PLUGIN_URL', 'http://localhost/wp-content/plugins/');
}

// Fonction pour vérifier si un fichier existe
function checkFile($path) {
    $fullPath = ABSPATH . $path;
    return [
        'exists' => file_exists($fullPath),
        'readable' => is_readable($fullPath),
        'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
        'path' => $fullPath
    ];
}

// Vérifier les fichiers
$files = [
    'assets/js/simple-country-selector.js',
    'assets/css/simple-country-selector.css',
    'assets/js/booking-form-main.js',
    'partials/booking-form.php'
];

$fileStatus = [];
foreach ($files as $file) {
    $fileStatus[$file] = checkFile($file);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Diagnostic Sélecteur de Pays</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        .info { background: #d1ecf1; color: #0c5460; }
        .file-check {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
        }
        .test-area {
            border: 2px dashed #ccc;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic Sélecteur de Pays</h1>
        
        <div class="status info">
            <strong>Informations système :</strong><br>
            PHP Version: <?php echo PHP_VERSION; ?><br>
            Répertoire courant: <?php echo __DIR__; ?><br>
            Timestamp: <?php echo date('Y-m-d H:i:s'); ?>
        </div>

        <h2>📁 Vérification des fichiers</h2>
        <?php foreach ($fileStatus as $file => $status): ?>
            <div class="file-check">
                <strong><?php echo $file; ?></strong><br>
                <?php if ($status['exists']): ?>
                    <span class="status success">✅ Fichier existe</span><br>
                    Taille: <?php echo number_format($status['size']); ?> octets<br>
                    <?php if ($status['readable']): ?>
                        <span class="status success">✅ Fichier lisible</span>
                    <?php else: ?>
                        <span class="status error">❌ Fichier non lisible</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="status error">❌ Fichier manquant</span><br>
                    Chemin attendu: <?php echo $status['path']; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <h2>🧪 Test du sélecteur</h2>
        <div class="test-area">
            <div id="simple-country-selector-container"></div>
            <div id="test-results"></div>
        </div>

        <div>
            <button onclick="loadFiles()">Charger les fichiers</button>
            <button onclick="testSelector()">Tester le sélecteur</button>
            <button onclick="checkConsole()">Vérifier la console</button>
        </div>

        <h2>📊 Logs de debug</h2>
        <div id="debug-logs"></div>
    </div>

    <script>
        let debugLogs = document.getElementById('debug-logs');
        let testResults = document.getElementById('test-results');

        function log(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const logDiv = document.createElement('div');
            logDiv.className = `status ${type}`;
            logDiv.innerHTML = `[${timestamp}] ${message}`;
            debugLogs.appendChild(logDiv);
            console.log(`[${timestamp}] ${message}`);
        }

        function loadFiles() {
            log('🔍 Chargement des fichiers...', 'info');
            
            // Charger CSS
            const cssLink = document.createElement('link');
            cssLink.rel = 'stylesheet';
            cssLink.href = './assets/css/simple-country-selector.css';
            cssLink.onload = () => log('✅ CSS chargé', 'success');
            cssLink.onerror = () => log('❌ Erreur CSS', 'error');
            document.head.appendChild(cssLink);

            // Charger JS
            const script = document.createElement('script');
            script.src = './assets/js/simple-country-selector.js';
            script.onload = () => {
                log('✅ JS chargé', 'success');
                checkSimpleCountrySelector();
            };
            script.onerror = () => log('❌ Erreur JS', 'error');
            document.head.appendChild(script);
        }

        function checkSimpleCountrySelector() {
            if (typeof SimpleCountrySelector !== 'undefined') {
                log('✅ SimpleCountrySelector disponible', 'success');
                log(`Type: ${typeof SimpleCountrySelector}`, 'info');
            } else {
                log('❌ SimpleCountrySelector non disponible', 'error');
                log('Variables globales:', 'info');
                Object.keys(window).filter(k => k.includes('Country') || k.includes('Phone')).forEach(k => {
                    log(`  - ${k}: ${typeof window[k]}`, 'info');
                });
            }
        }

        function testSelector() {
            log('🧪 Test du sélecteur...', 'info');
            
            const container = document.getElementById('simple-country-selector-container');
            if (!container) {
                log('❌ Container non trouvé', 'error');
                return;
            }

            if (typeof SimpleCountrySelector === 'undefined') {
                log('❌ SimpleCountrySelector non disponible', 'error');
                return;
            }

            try {
                const selector = new SimpleCountrySelector(container, {
                    defaultCountry: 'FR',
                    placeholder: 'Numéro de téléphone'
                });
                log('✅ Sélecteur créé', 'success');
                
                // Vérifier l'affichage
                const phoneContainer = container.querySelector('.simple-phone-container');
                if (phoneContainer) {
                    const rect = phoneContainer.getBoundingClientRect();
                    log(`📏 Dimensions: ${rect.width}x${rect.height}`, 'info');
                    log(`👁️ Visible: ${rect.width > 0 && rect.height > 0}`, 'info');
                }
                
            } catch (error) {
                log(`❌ Erreur: ${error.message}`, 'error');
            }
        }

        function checkConsole() {
            log('🔍 Vérification de la console...', 'info');
            
            // Vérifier les erreurs
            const originalError = console.error;
            const errors = [];
            console.error = function(...args) {
                errors.push(args.join(' '));
                originalError.apply(console, args);
            };
            
            setTimeout(() => {
                console.error = originalError;
                if (errors.length > 0) {
                    log(`❌ ${errors.length} erreur(s) détectée(s):`, 'error');
                    errors.forEach(error => log(`  - ${error}`, 'error'));
                } else {
                    log('✅ Aucune erreur détectée', 'success');
                }
            }, 1000);
        }

        // Surveillance des erreurs
        window.addEventListener('error', (e) => {
            log(`❌ Erreur JavaScript: ${e.message}`, 'error');
        });

        // Chargement automatique
        window.addEventListener('load', () => {
            log('🚀 Page chargée', 'info');
            loadFiles();
        });
    </script>
</body>
</html> 