<?php
/**
 * Diagnostic du sélecteur de pays
 * Ce fichier permet d'identifier les problèmes potentiels
 */

// Vérifier si on est dans WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Sélecteur de Pays</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .diagnostic-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .section h3 {
            margin-top: 0;
            color: #333;
        }
        .status {
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
            font-weight: 500;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .test-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .phone-field-with-country {
            position: relative;
            z-index: 1000;
        }
        #simple-country-selector-container {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 1000 !important;
            min-height: 48px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .debug-log {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
        }
        .test-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .test-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="diagnostic-container">
        <h1>🔍 Diagnostic Sélecteur de Pays</h1>
        
        <div class="section">
            <h3>📋 Informations Système</h3>
            <div class="status info">
                <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
                <strong>WordPress:</strong> <?php echo defined('WP_VERSION') ? WP_VERSION : 'Non détecté'; ?><br>
                <strong>Plugin Path:</strong> <?php echo __FILE__; ?><br>
                <strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?>
            </div>
        </div>

        <div class="section">
            <h3>📁 Vérification des Fichiers</h3>
            <div id="file-check-results">
                <div class="status info">Vérification en cours...</div>
            </div>
        </div>

        <div class="section">
            <h3>🧪 Test du Sélecteur</h3>
            <div class="test-form">
                <form id="test-form">
                    <div class="form-group">
                        <label for="name">Nom complet</label>
                        <input type="text" id="name" placeholder="Votre nom">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="Votre email">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <div class="phone-field-with-country">
                            <div id="simple-country-selector-container"></div>
                            <input id="client-phone" type="hidden" value="">
                        </div>
                    </div>
                    
                    <button type="submit" class="test-button">Tester le formulaire</button>
                </form>
            </div>
            
            <div id="test-results"></div>
        </div>

        <div class="section">
            <h3>📊 Logs de Debug</h3>
            <div class="debug-log" id="debug-log">
                Initialisation du diagnostic...
            </div>
        </div>

        <div class="section">
            <h3>🔧 Actions Correctives</h3>
            <div id="corrective-actions">
                <div class="status info">Analyse en cours...</div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/simple-country-selector.js"></script>
    <script>
        // Fonction pour ajouter des messages de statut
        function addStatus(containerId, message, type = 'info') {
            const container = document.getElementById(containerId);
            const statusDiv = document.createElement('div');
            statusDiv.className = `status ${type}`;
            statusDiv.textContent = message;
            container.appendChild(statusDiv);
        }

        // Fonction pour ajouter des messages de debug
        function addDebug(message) {
            const debugLog = document.getElementById('debug-log');
            const timestamp = new Date().toLocaleTimeString();
            debugLog.innerHTML += `<br>[${timestamp}] ${message}`;
            debugLog.scrollTop = debugLog.scrollHeight;
        }

        // Vérification des fichiers
        function checkFiles() {
            addDebug("🔍 Vérification des fichiers...");
            
            const filesToCheck = [
                'assets/js/simple-country-selector.js',
                'assets/js/booking-form-main.js',
                'assets/css/simple-country-selector.css'
            ];
            
            const resultsContainer = document.getElementById('file-check-results');
            resultsContainer.innerHTML = '';
            
            filesToCheck.forEach(file => {
                fetch(file)
                    .then(response => {
                        if (response.ok) {
                            addStatus('file-check-results', `✅ ${file} - Fichier trouvé`, 'success');
                            addDebug(`✅ ${file} - Fichier trouvé`);
                        } else {
                            addStatus('file-check-results', `❌ ${file} - Fichier manquant`, 'error');
                            addDebug(`❌ ${file} - Fichier manquant (${response.status})`);
                        }
                    })
                    .catch(error => {
                        addStatus('file-check-results', `❌ ${file} - Erreur d'accès`, 'error');
                        addDebug(`❌ ${file} - Erreur d'accès: ${error.message}`);
                    });
            });
        }

        // Fonction d'initialisation améliorée
        function initSimpleCountrySelector() {
            addDebug("🔍 Début initSimpleCountrySelector");
            
            const waitForContainer = () => {
                const container = document.querySelector("#simple-country-selector-container");
                if (!container) {
                    addDebug("⏳ Container non trouvé, nouvelle tentative dans 50ms...");
                    setTimeout(waitForContainer, 50);
                    return;
                }
                
                addDebug("✅ Container trouvé");
                initializeSelector(container);
            };

            const initializeSelector = (container) => {
                addDebug("🔍 Container HTML avant: " + container.innerHTML.substring(0, 100) + "...");

                if (typeof SimpleCountrySelector === "undefined") {
                    addDebug("❌ SimpleCountrySelector n'est pas défini");
                    addStatus('test-results', "❌ SimpleCountrySelector non disponible", 'error');
                    
                    setTimeout(() => {
                        if (typeof SimpleCountrySelector !== "undefined") {
                            initializeSelector(container);
                        } else {
                            addDebug("❌ SimpleCountrySelector toujours non disponible");
                            addStatus('test-results', "❌ SimpleCountrySelector toujours non disponible", 'error');
                        }
                    }, 200);
                    return;
                }

                try {
                    container.innerHTML = "";
                    addDebug("🔧 Container nettoyé");

                    // Forcer l'affichage du container
                    container.style.setProperty("display", "block", "important");
                    container.style.setProperty("visibility", "visible", "important");
                    container.style.setProperty("opacity", "1", "important");
                    container.style.setProperty("position", "relative", "important");
                    container.style.setProperty("z-index", "1000", "important");
                    container.style.setProperty("min-height", "48px", "important");
                    container.style.setProperty("width", "100%", "important");

                    // Initialiser le sélecteur
                    window.simpleCountrySelector = new SimpleCountrySelector(container, {
                        defaultCountry: "FR",
                        placeholder: "Numéro de téléphone",
                    });

                    addDebug("✅ Sélecteur créé");

                    // Fonction pour récupérer le numéro complet
                    window.getPhoneNumber = function () {
                        return window.simpleCountrySelector
                            ? window.simpleCountrySelector.getFullPhoneNumber()
                            : "";
                    };

                    // Écouter les changements
                    container.addEventListener("countryChanged", function (e) {
                        addDebug("🌍 Pays sélectionné: " + e.detail.country.name);
                        const hiddenInput = document.querySelector("#client-phone");
                        if (hiddenInput) {
                            hiddenInput.value = window.getPhoneNumber();
                        }
                    });

                    const phoneInput = container.querySelector(".simple-phone-input");
                    if (phoneInput) {
                        phoneInput.addEventListener("input", function () {
                            const hiddenInput = document.querySelector("#client-phone");
                            if (hiddenInput) {
                                hiddenInput.value = window.getPhoneNumber();
                            }
                        });
                    }

                    addDebug("✅ Sélecteur de pays initialisé avec succès");
                    addStatus('test-results', "✅ Sélecteur de pays initialisé avec succès", 'success');

                    // Vérification finale
                    setTimeout(() => {
                        const phoneContainer = container.querySelector(".simple-phone-container");
                        if (phoneContainer) {
                            const isVisible = phoneContainer.offsetWidth > 0 && phoneContainer.offsetHeight > 0;
                            addDebug("🔍 Vérification visibilité: " + isVisible);
                            
                            if (isVisible) {
                                addStatus('test-results', "✅ Sélecteur visible et fonctionnel", 'success');
                            } else {
                                addStatus('test-results', "⚠️ Sélecteur créé mais pas visible", 'warning');
                            }
                        }
                    }, 500);

                } catch (error) {
                    addDebug("❌ Erreur lors de l'initialisation: " + error.message);
                    addStatus('test-results', "❌ Erreur lors de l'initialisation: " + error.message, 'error');
                }
            };

            waitForContainer();
        }

        // Générer les actions correctives
        function generateCorrectiveActions() {
            addDebug("🔧 Génération des actions correctives...");
            
            const actionsContainer = document.getElementById('corrective-actions');
            actionsContainer.innerHTML = '';
            
            const actions = [
                {
                    title: "Vérifier le chargement des scripts",
                    description: "S'assurer que simple-country-selector.js est chargé avant booking-form-main.js",
                    type: "info"
                },
                {
                    title: "Vérifier les conflits CSS",
                    description: "S'assurer qu'aucun CSS n'écrase les styles du sélecteur",
                    type: "warning"
                },
                {
                    title: "Vérifier les conflits JavaScript",
                    description: "S'assurer qu'aucun autre script n'interfère avec le sélecteur",
                    type: "warning"
                },
                {
                    title: "Vérifier le timing d'initialisation",
                    description: "S'assurer que le DOM est complètement chargé avant l'initialisation",
                    type: "info"
                }
            ];
            
            actions.forEach(action => {
                addStatus('corrective-actions', `<strong>${action.title}:</strong> ${action.description}`, action.type);
            });
        }

        // Test du formulaire
        document.getElementById('test-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = window.getPhoneNumber ? window.getPhoneNumber() : '';
            
            addDebug("📝 Test du formulaire:");
            addDebug("  - Nom: " + name);
            addDebug("  - Email: " + email);
            addDebug("  - Téléphone: " + phone);
            
            if (phone) {
                addStatus('test-results', "✅ Numéro de téléphone récupéré: " + phone, 'success');
            } else {
                addStatus('test-results', "❌ Aucun numéro de téléphone récupéré", 'error');
            }
        });

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            addDebug("📄 Page chargée, début du diagnostic");
            
            // Vérifier les fichiers
            checkFiles();
            
            // Initialisation avec plusieurs tentatives
            initSimpleCountrySelector();
            setTimeout(initSimpleCountrySelector, 200);
            setTimeout(initSimpleCountrySelector, 500);
            setTimeout(initSimpleCountrySelector, 1000);
            
            // Générer les actions correctives
            setTimeout(generateCorrectiveActions, 2000);
        });
    </script>
</body>
</html> 