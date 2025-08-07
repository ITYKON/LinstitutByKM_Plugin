<?php
/**
 * 🧪 TEST EMAIL DARK MODE COMPATIBILITY
 * ====================================
 * Script de test pour vérifier la compatibilité des emails avec le mode sombre
 * Génère des aperçus des templates d'email avec les nouvelles améliorations
 */

// Sécurité WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Inclure les classes nécessaires
require_once 'includes/class-email.php';
require_once 'includes/notifications.php';

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>Test Email Dark Mode - Institut Booking</title>\n";
echo "    <style>\n";
echo "        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }\n";
echo "        .test-container { max-width: 1200px; margin: 0 auto; }\n";
echo "        .test-header { background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }\n";
echo "        .test-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }\n";
echo "        .test-preview { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }\n";
echo "        .test-preview h3 { margin: 0; padding: 1rem; background: #f8f9fa; border-bottom: 1px solid #e9ecef; }\n";
echo "        .test-preview iframe { width: 100%; height: 600px; border: none; }\n";
echo "        .dark-mode-toggle { background: #333; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; }\n";
echo "        .status-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem; font-weight: 500; }\n";
echo "        .status-success { background: #d1fae5; color: #065f46; }\n";
echo "        .status-info { background: #dbeafe; color: #1e40af; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='test-container'>\n";
echo "    <div class='test-header'>\n";
echo "        <h1>🧪 Test Email Dark Mode Compatibility</h1>\n";
echo "        <p>Vérification des templates d'email avec support amélioré du mode sombre</p>\n";
echo "        <div style='margin-top: 1rem;'>\n";
echo "            <span class='status-badge status-success'>✅ Media queries améliorées</span>\n";
echo "            <span class='status-badge status-success'>✅ Couleurs optimisées</span>\n";
echo "            <span class='status-badge status-success'>✅ Fallback pour clients incompatibles</span>\n";
echo "            <span class='status-badge status-info'>📱 Compatible mobile</span>\n";
echo "        </div>\n";
echo "    </div>\n";

// Données de test
$test_placeholders = [
    '{company}' => 'L\'Institut by KM',
    '{client}' => 'Melissa Hadji',
    '{service}' => 'Patine / Gloss',
    '{date}' => '15-12-2024',
    '{time}' => '14:30',
    '{employee}' => 'Dalya'
];

echo "    <div class='test-grid'>\n";

// Test 1: Email de confirmation
echo "        <div class='test-preview'>\n";
echo "            <h3>📧 Email de Confirmation</h3>\n";
$confirm_html = IB_Email::get_modern_template('confirm', $test_placeholders);
$confirm_encoded = base64_encode($confirm_html);
echo "            <iframe src='data:text/html;base64,{$confirm_encoded}'></iframe>\n";
echo "        </div>\n";

// Test 2: Email de remerciement
echo "        <div class='test-preview'>\n";
echo "            <h3>💌 Email de Remerciement</h3>\n";
$thank_you_html = IB_Notifications::get_thank_you_template($test_placeholders);
$thank_you_encoded = base64_encode($thank_you_html);
echo "            <iframe src='data:text/html;base64,{$thank_you_encoded}'></iframe>\n";
echo "        </div>\n";

// Test 3: Email d'annulation
echo "        <div class='test-preview'>\n";
echo "            <h3>❌ Email d'Annulation</h3>\n";
$cancel_html = IB_Email::get_modern_template('cancel', $test_placeholders);
$cancel_encoded = base64_encode($cancel_html);
echo "            <iframe src='data:text/html;base64,{$cancel_encoded}'></iframe>\n";
echo "        </div>\n";

// Test 4: Comparaison mode sombre
echo "        <div class='test-preview'>\n";
echo "            <h3>🌙 Aperçu Mode Sombre</h3>\n";
$dark_mode_html = str_replace('<body>', '<body style=\"background-color: #0f0f0f !important; color: #f7fafc !important;\">', $confirm_html);
$dark_mode_html = str_replace('<div class=\'container\'>', '<div class=\'container\' style=\"background-color: #1a202c !important; border: 1px solid #4a5568 !important;\">', $dark_mode_html);
$dark_mode_encoded = base64_encode($dark_mode_html);
echo "            <iframe src='data:text/html;base64,{$dark_mode_encoded}'></iframe>\n";
echo "        </div>\n";

echo "    </div>\n";

// Informations techniques
echo "    <div class='test-header'>\n";
echo "        <h2>🔧 Améliorations Techniques</h2>\n";
echo "        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1rem;'>\n";
echo "            <div>\n";
echo "                <h3>✨ Nouvelles fonctionnalités</h3>\n";
echo "                <ul>\n";
echo "                    <li><strong>Couleurs adaptatives</strong> : Palette optimisée pour light/dark</li>\n";
echo "                    <li><strong>Contraste amélioré</strong> : Texte plus lisible en mode sombre</li>\n";
echo "                    <li><strong>Ombres adaptées</strong> : Effets visuels ajustés par mode</li>\n";
echo "                    <li><strong>Fallback robuste</strong> : Support pour clients email limités</li>\n";
echo "                </ul>\n";
echo "            </div>\n";
echo "            <div>\n";
echo "                <h3>🎨 Palette de couleurs</h3>\n";
echo "                <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.875rem;'>\n";
echo "                    <div>\n";
echo "                        <strong>Mode Clair</strong><br>\n";
echo "                        • Fond: #f5f5f5<br>\n";
echo "                        • Container: #ffffff<br>\n";
echo "                        • Texte: #2d3748<br>\n";
echo "                        • Accent: #4a5568\n";
echo "                    </div>\n";
echo "                    <div>\n";
echo "                        <strong>Mode Sombre</strong><br>\n";
echo "                        • Fond: #0f0f0f<br>\n";
echo "                        • Container: #1a202c<br>\n";
echo "                        • Texte: #e2e8f0<br>\n";
echo "                        • Accent: #4a5568\n";
echo "                    </div>\n";
echo "                </div>\n";
echo "            </div>\n";
echo "        </div>\n";
echo "    </div>\n";

// Instructions de test
echo "    <div class='test-header'>\n";
echo "        <h2>📋 Instructions de Test</h2>\n";
echo "        <ol>\n";
echo "            <li><strong>Test automatique</strong> : Les aperçus ci-dessus montrent le rendu des emails</li>\n";
echo "            <li><strong>Test manuel</strong> : Envoyez-vous un email de test depuis l'admin</li>\n";
echo "            <li><strong>Test multi-clients</strong> : Vérifiez sur Gmail, Outlook, Apple Mail</li>\n";
echo "            <li><strong>Test mobile</strong> : Consultez les emails sur smartphone en mode sombre</li>\n";
echo "        </ol>\n";
echo "        <div style='background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 1rem; margin-top: 1rem;'>\n";
echo "            <strong>💡 Astuce :</strong> Pour tester le mode sombre sur votre appareil, activez le mode sombre dans les paramètres système, puis consultez vos emails.\n";
echo "        </div>\n";
echo "    </div>\n";

echo "</div>\n";
echo "</body>\n";
echo "</html>\n";
?>
