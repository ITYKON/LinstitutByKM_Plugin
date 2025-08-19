<?php
/**
 * Script de correction automatique pour le sélecteur de pays
 * Ce script corrige les problèmes d'affichage du sélecteur de pays
 */

// Vérifier que nous sommes dans WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Fonction pour vérifier et corriger les fichiers
function fixCountrySelector() {
    $fixes = [];
    
    // 1. Vérifier le fichier CSS
    $cssFile = ABSPATH . 'assets/css/simple-country-selector.css';
    if (file_exists($cssFile)) {
        $cssContent = file_get_contents($cssFile);
        
        // Vérifier si les styles forcés sont présents
        if (strpos($cssContent, '!important') === false) {
            $fixes[] = "CSS: Styles forcés manquants";
        }
        
        // Vérifier la règle pour masquer les anciens sélecteurs
        if (strpos($cssContent, '.iti, input[type="tel"]:not(.simple-phone-input)') === false) {
            $fixes[] = "CSS: Règle de masquage des anciens sélecteurs manquante";
        }
    } else {
        $fixes[] = "CSS: Fichier manquant";
    }
    
    // 2. Vérifier le fichier JS
    $jsFile = ABSPATH . 'assets/js/simple-country-selector.js';
    if (file_exists($jsFile)) {
        $jsContent = file_get_contents($jsFile);
        
        // Vérifier si la méthode forceDisplay est présente
        if (strpos($jsContent, 'forceDisplay()') === false) {
            $fixes[] = "JS: Méthode forceDisplay manquante";
        }
        
        // Vérifier les logs de debug
        if (strpos($jsContent, 'console.log') === false) {
            $fixes[] = "JS: Logs de debug manquants";
        }
    } else {
        $fixes[] = "JS: Fichier manquant";
    }
    
    // 3. Vérifier le fichier principal
    $mainFile = ABSPATH . 'assets/js/booking-form-main.js';
    if (file_exists($mainFile)) {
        $mainContent = file_get_contents($mainFile);
        
        // Vérifier si la fonction initSimpleCountrySelector est présente
        if (strpos($mainContent, 'initSimpleCountrySelector') === false) {
            $fixes[] = "Main JS: Fonction initSimpleCountrySelector manquante";
        }
        
        // Vérifier les logs de debug
        if (strpos($mainContent, '[DEBUG]') === false) {
            $fixes[] = "Main JS: Logs de debug manquants";
        }
    } else {
        $fixes[] = "Main JS: Fichier manquant";
    }
    
    return $fixes;
}

// Fonction pour appliquer les corrections
function applyFixes() {
    $results = [];
    
    // 1. Corriger le CSS
    $cssFile = ABSPATH . 'assets/css/simple-country-selector.css';
    if (file_exists($cssFile)) {
        $cssContent = file_get_contents($cssFile);
        
        // Ajouter les styles forcés si manquants
        if (strpos($cssContent, '!important') === false) {
            $cssContent = str_replace(
                'position: relative;',
                'position: relative !important;',
                $cssContent
            );
            $cssContent = str_replace(
                'display: flex;',
                'display: flex !important;',
                $cssContent
            );
            $cssContent = str_replace(
                'visibility: visible;',
                'visibility: visible !important;',
                $cssContent
            );
            
            // Ajouter la règle pour masquer les anciens sélecteurs
            $cssContent .= "\n\n/* Masquer les anciens sélecteurs */\n.iti, \ninput[type=\"tel\"]:not(.simple-phone-input),\n.iti * {\n  display: none !important;\n  visibility: hidden !important;\n  opacity: 0 !important;\n}\n";
            
            file_put_contents($cssFile, $cssContent);
            $results[] = "CSS corrigé avec succès";
        }
    }
    
    // 2. Corriger le JS
    $jsFile = ABSPATH . 'assets/js/simple-country-selector.js';
    if (file_exists($jsFile)) {
        $jsContent = file_get_contents($jsFile);
        
        // Ajouter la méthode forceDisplay si manquante
        if (strpos($jsContent, 'forceDisplay()') === false) {
            $forceDisplayMethod = "
  forceDisplay() {
    console.log('🔧 [SimpleCountrySelector] Forçage de l\\'affichage...');
    
    // Forcer l'affichage du container principal
    this.container.style.display = 'block !important';
    this.container.style.visibility = 'visible !important';
    this.container.style.opacity = '1 !important';
    this.container.style.position = 'relative !important';
    this.container.style.zIndex = '1000 !important';
    this.container.style.minHeight = '48px !important';
    
    // Forcer l'affichage du container du téléphone
    const phoneContainer = this.container.querySelector('.simple-phone-container');
    if (phoneContainer) {
      phoneContainer.style.display = 'flex !important';
      phoneContainer.style.visibility = 'visible !important';
      phoneContainer.style.opacity = '1 !important';
      phoneContainer.style.position = 'relative !important';
      phoneContainer.style.zIndex = '1001 !important';
      
      // Forcer l'affichage de tous les éléments enfants
      const allChildren = phoneContainer.querySelectorAll('*');
      allChildren.forEach(child => {
        child.style.display = child.style.display || 'block';
        child.style.visibility = 'visible';
        child.style.opacity = '1';
      });
    }
    
    // Masquer les anciens sélecteurs
    const oldSelectors = document.querySelectorAll('.iti, input[type=\"tel\"]:not(.simple-phone-input)');
    oldSelectors.forEach(selector => {
      selector.style.display = 'none !important';
      selector.style.visibility = 'hidden !important';
      selector.style.opacity = '0 !important';
    });
    
    console.log('✅ [SimpleCountrySelector] Affichage forcé');
  }
";
            
            // Insérer la méthode après bindEvents
            $jsContent = str_replace(
                '  bindEvents() {',
                $forceDisplayMethod . "\n  bindEvents() {",
                $jsContent
            );
            
            // Appeler forceDisplay dans init
            $jsContent = str_replace(
                '    this.render();\n    this.bindEvents();',
                '    this.render();\n    this.bindEvents();\n    this.forceDisplay();',
                $jsContent
            );
            
            file_put_contents($jsFile, $jsContent);
            $results[] = "JS corrigé avec succès";
        }
    }
    
    return $results;
}

// Exécuter les vérifications et corrections
$fixes = fixCountrySelector();
$appliedFixes = applyFixes();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Correction Sélecteur de Pays</title>
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
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        .info { background: #d1ecf1; color: #0c5460; }
        .fix-item {
            background: #f8f9fa;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
        }
        .applied-fix {
            border-left-color: #28a745;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Correction Sélecteur de Pays</h1>
        
        <div class="status info">
            <strong>Diagnostic automatique :</strong><br>
            Timestamp: <?php echo date('Y-m-d H:i:s'); ?><br>
            Répertoire: <?php echo __DIR__; ?>
        </div>

        <?php if (empty($fixes)): ?>
            <div class="status success">
                <strong>✅ Aucun problème détecté !</strong><br>
                Le sélecteur de pays semble correctement configuré.
            </div>
        <?php else: ?>
            <div class="status warning">
                <strong>⚠️ Problèmes détectés :</strong><br>
                <?php echo count($fixes); ?> problème(s) identifié(s)
            </div>
            
            <h3>📋 Problèmes détectés :</h3>
            <?php foreach ($fixes as $fix): ?>
                <div class="fix-item">❌ <?php echo htmlspecialchars($fix); ?></div>
            <?php endforeach; ?>
            
            <?php if (!empty($appliedFixes)): ?>
                <h3>🔧 Corrections appliquées :</h3>
                <?php foreach ($appliedFixes as $fix): ?>
                    <div class="fix-item applied-fix">✅ <?php echo htmlspecialchars($fix); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <h3>🧪 Tests recommandés :</h3>
            <ol>
                <li>Ouvrez la console du navigateur (F12)</li>
                <li>Rechargez la page du formulaire de réservation</li>
                <li>Vérifiez que le sélecteur de pays s'affiche correctement</li>
                <li>Testez la sélection d'un pays différent</li>
                <li>Vérifiez que le numéro de téléphone est correctement formaté</li>
            </ol>
        </div>

        <div style="margin-top: 30px;">
            <button onclick="location.reload()">Actualiser</button>
            <button onclick="window.open('./test-country-selector-simple.html', '_blank')">Tester le sélecteur</button>
            <button onclick="window.open('./debug-country-selector.php', '_blank')">Diagnostic complet</button>
        </div>

        <?php if (!empty($fixes)): ?>
            <div class="status info" style="margin-top: 30px;">
                <strong>💡 Conseils :</strong><br>
                • Videz le cache de votre navigateur après les corrections<br>
                • Vérifiez que tous les fichiers sont bien chargés<br>
                • Consultez la console pour d'éventuelles erreurs JavaScript<br>
                • Testez sur différents navigateurs si le problème persiste
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Ajouter des logs pour vérifier le chargement
        console.log('🔧 Script de correction chargé');
        console.log('📁 Répertoire:', window.location.pathname);
        
        // Vérifier si les fichiers sont accessibles
        fetch('./assets/css/simple-country-selector.css')
            .then(response => {
                if (response.ok) {
                    console.log('✅ CSS accessible');
                } else {
                    console.log('❌ CSS non accessible');
                }
            })
            .catch(error => console.log('❌ Erreur CSS:', error));
            
        fetch('./assets/js/simple-country-selector.js')
            .then(response => {
                if (response.ok) {
                    console.log('✅ JS accessible');
                } else {
                    console.log('❌ JS non accessible');
                }
            })
            .catch(error => console.log('❌ Erreur JS:', error));
    </script>
</body>
</html> 