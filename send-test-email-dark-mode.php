<?php
/**
 * 📧 ENVOI EMAIL TEST MODE SOMBRE
 * ===============================
 * Script pour envoyer des emails de test avec les nouveaux templates
 * Compatible mode sombre pour vérification sur vrais clients email
 */

// Sécurité WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Inclure les classes nécessaires
require_once 'includes/class-email.php';
require_once 'includes/notifications.php';

// Configuration
$test_email = 'votre-email@example.com'; // ⚠️ CHANGEZ CETTE ADRESSE
$company_name = get_bloginfo('name') ?: 'L\'Institut by KM';

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "    <meta charset='UTF-8'>\n";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "    <title>Envoi Email Test - Mode Sombre</title>\n";
echo "    <style>\n";
echo "        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }\n";
echo "        .container { max-width: 800px; margin: 0 auto; }\n";
echo "        .card { background: white; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }\n";
echo "        .btn { background: #f8b4bc; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; font-weight: 500; }\n";
echo "        .btn:hover { background: #f093a0; }\n";
echo "        .btn-secondary { background: #6b7280; }\n";
echo "        .btn-secondary:hover { background: #4b5563; }\n";
echo "        .alert { padding: 1rem; border-radius: 8px; margin: 1rem 0; }\n";
echo "        .alert-success { background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46; }\n";
echo "        .alert-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }\n";
echo "        .alert-info { background: #dbeafe; border: 1px solid #93c5fd; color: #1e40af; }\n";
echo "        .form-group { margin-bottom: 1rem; }\n";
echo "        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }\n";
echo "        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; }\n";
echo "    </style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='container'>\n";
echo "    <div class='card'>\n";
echo "        <h1>📧 Test Email Mode Sombre</h1>\n";
echo "        <p>Envoyez des emails de test pour vérifier la compatibilité avec le mode sombre sur différents clients email.</p>\n";
echo "    </div>\n";

// Traitement du formulaire
if ($_POST['action'] ?? false) {
    $email_to = $_POST['email'] ?? $test_email;
    $test_type = $_POST['test_type'] ?? 'confirmation';
    
    if (!is_email($email_to)) {
        echo "    <div class='alert alert-error'>❌ Adresse email invalide</div>\n";
    } else {
        // Données de test
        $placeholders = [
            '{company}' => $company_name,
            '{client}' => 'Test User',
            '{service}' => 'Service Test Mode Sombre',
            '{date}' => date('d-m-Y'),
            '{time}' => date('H:i'),
            '{employee}' => 'Équipe Test'
        ];
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $success = false;
        
        switch ($test_type) {
            case 'confirmation':
                $subject = '[TEST] Confirmation de réservation - Mode Sombre';
                $body = IB_Email::get_modern_template('confirm', $placeholders);
                $success = wp_mail($email_to, $subject, $body, $headers);
                break;
                
            case 'thank_you':
                $subject = '[TEST] Merci pour votre réservation - Mode Sombre';
                $body = IB_Notifications::get_thank_you_template($placeholders);
                $success = wp_mail($email_to, $subject, $body, $headers);
                break;
                
            case 'cancellation':
                $subject = '[TEST] Annulation de réservation - Mode Sombre';
                $body = IB_Email::get_modern_template('cancel', $placeholders);
                $success = wp_mail($email_to, $subject, $body, $headers);
                break;
        }
        
        if ($success) {
            echo "    <div class='alert alert-success'>✅ Email envoyé avec succès à {$email_to}</div>\n";
        } else {
            echo "    <div class='alert alert-error'>❌ Erreur lors de l'envoi de l'email</div>\n";
        }
    }
}

// Formulaire d'envoi
echo "    <div class='card'>\n";
echo "        <h2>🚀 Envoyer un Email de Test</h2>\n";
echo "        <form method='post'>\n";
echo "            <div class='form-group'>\n";
echo "                <label for='email'>Adresse email de destination :</label>\n";
echo "                <input type='email' id='email' name='email' value='{$test_email}' required>\n";
echo "            </div>\n";
echo "            <div class='form-group'>\n";
echo "                <label for='test_type'>Type d'email :</label>\n";
echo "                <select id='test_type' name='test_type' style='width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;'>\n";
echo "                    <option value='confirmation'>📧 Email de Confirmation</option>\n";
echo "                    <option value='thank_you'>💌 Email de Remerciement</option>\n";
echo "                    <option value='cancellation'>❌ Email d'Annulation</option>\n";
echo "                </select>\n";
echo "            </div>\n";
echo "            <button type='submit' name='action' value='send' class='btn'>📤 Envoyer Email Test</button>\n";
echo "        </form>\n";
echo "    </div>\n";

// Instructions de test
echo "    <div class='card'>\n";
echo "        <h2>📋 Instructions de Test</h2>\n";
echo "        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;'>\n";
echo "            <div>\n";
echo "                <h3>🔍 Que tester :</h3>\n";
echo "                <ul>\n";
echo "                    <li><strong>Gmail</strong> : Mode sombre automatique</li>\n";
echo "                    <li><strong>Outlook</strong> : Thème sombre</li>\n";
echo "                    <li><strong>Apple Mail</strong> : Mode sombre iOS/macOS</li>\n";
echo "                    <li><strong>Mobile</strong> : Affichage sur smartphone</li>\n";
echo "                </ul>\n";
echo "            </div>\n";
echo "            <div>\n";
echo "                <h3>✅ Points à vérifier :</h3>\n";
echo "                <ul>\n";
echo "                    <li>Texte lisible en mode sombre</li>\n";
echo "                    <li>Couleurs de fond adaptées</li>\n";
echo "                    <li>Contrastes suffisants</li>\n";
echo "                    <li>Icônes et boutons visibles</li>\n";
echo "                </ul>\n";
echo "            </div>\n";
echo "        </div>\n";
echo "    </div>\n";

// Améliorations apportées
echo "    <div class='card'>\n";
echo "        <h2>🎨 Améliorations Apportées</h2>\n";
echo "        <div class='alert alert-info'>\n";
echo "            <strong>🔧 Changements techniques :</strong>\n";
echo "            <ul style='margin: 0.5rem 0 0 1rem;'>\n";
echo "                <li>Couleurs optimisées pour le contraste en mode sombre</li>\n";
echo "                <li>Fond d'email adaptatif (#f5f5f5 → #0f0f0f)</li>\n";
echo "                <li>Texte principal plus contrasté (#2d3748 → #e2e8f0)</li>\n";
echo "                <li>Ombres renforcées pour la profondeur</li>\n";
echo "                <li>Fallback CSS pour clients email incompatibles</li>\n";
echo "            </ul>\n";
echo "        </div>\n";
echo "    </div>\n";

// Liens utiles
echo "    <div class='card'>\n";
echo "        <h2>🔗 Liens Utiles</h2>\n";
echo "        <div style='display: flex; gap: 1rem; flex-wrap: wrap;'>\n";
echo "            <a href='test-email-dark-mode.php' class='btn btn-secondary'>🧪 Aperçu Templates</a>\n";
echo "            <a href='admin.php?page=institut-booking-settings' class='btn btn-secondary'>⚙️ Paramètres Email</a>\n";
echo "            <a href='admin.php?page=institut-booking-notifications' class='btn btn-secondary'>📬 Notifications</a>\n";
echo "        </div>\n";
echo "    </div>\n";

echo "</div>\n";
echo "</body>\n";
echo "</html>\n";
?>
