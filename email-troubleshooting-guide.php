<?php
/**
 * Guide de dépannage complet pour les emails Institut Booking
 * Solutions étape par étape pour résoudre les problèmes d'envoi
 */

echo "<h1>📧 Guide de Dépannage des Emails Institut Booking</h1>\n";

echo "<h2>🚨 Problème: Les emails de remerciement ne s'envoient pas</h2>\n";
echo "<div style='background:#ffebee;padding:15px;border-left:4px solid #f44336;'>\n";
echo "<h3>Symptômes observés :</h3>\n";
echo "<ul>\n";
echo "<li>❌ Le client ne reçoit pas d'email après sa réservation</li>\n";
echo "<li>❌ Aucun email de confirmation dans la boîte du client</li>\n";
echo "<li>❌ Pas d'erreur visible dans l'interface admin</li>\n";
echo "<li>❌ La fonction send_thank_you() semble s'exécuter mais sans résultat</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h2>🔍 Diagnostic étape par étape</h2>\n";

echo "<h3>Étape 1: Vérifier le code d'envoi</h3>\n";
echo "<div style='background:#e8f5e8;padding:15px;border-left:4px solid #4caf50;'>\n";
echo "<strong>✅ Code d'envoi présent et correct :</strong><br>\n";
echo "<div style='background:#f5f5f5;padding:10px;border:1px solid #ddd;font-family:monospace;margin:10px 0;'>\n";
echo "// Dans admin/page-bookings.php ligne 51<br>\n";
echo "if (\$result) {<br>\n";
echo "&nbsp;&nbsp;// Envoyer l'email de remerciement au client<br>\n";
echo "&nbsp;&nbsp;require_once plugin_dir_path(__FILE__) . '../includes/notifications.php';<br>\n";
echo "&nbsp;&nbsp;<strong>IB_Notifications::send_thank_you(\$result);</strong><br>\n";
echo "}<br>\n";
echo "</div>\n";
echo "<p>✅ <strong>Le code d'appel est correct et présent</strong></p>\n";
echo "</div>\n";

echo "<h3>Étape 2: Vérifier la fonction send_thank_you</h3>\n";
echo "<div style='background:#e3f2fd;padding:15px;border-left:4px solid #2196f3;'>\n";
echo "<strong>✅ Fonction send_thank_you complète :</strong><br>\n";
echo "<ul>\n";
echo "<li>✅ Récupération de la réservation depuis la DB</li>\n";
echo "<li>✅ Validation de l'email client avec is_email()</li>\n";
echo "<li>✅ Template d'email configuré</li>\n";
echo "<li>✅ Remplacement des variables {client_name}, {service_name}, etc.</li>\n";
echo "<li>✅ Appel à wp_mail() avec headers HTML</li>\n";
echo "<li>✅ Logging des tentatives d'envoi</li>\n";
echo "</ul>\n";
echo "<p>✅ <strong>La fonction est complète et robuste</strong></p>\n";
echo "</div>\n";

echo "<h3>Étape 3: Causes probables du problème</h3>\n";
echo "<div style='background:#fff3e0;padding:15px;border-left:4px solid #ff9800;'>\n";
echo "<strong>⚠️ Problèmes les plus courants :</strong><br>\n";
echo "<ol>\n";
echo "<li><strong>Configuration SMTP manquante</strong><br>\n";
echo "   → WordPress utilise mail() PHP qui peut être bloqué par l'hébergeur</li>\n";
echo "<li><strong>Emails marqués comme spam</strong><br>\n";
echo "   → Pas d'authentification SMTP, emails rejetés</li>\n";
echo "<li><strong>Fonction mail() désactivée</strong><br>\n";
echo "   → Hébergeur a désactivé la fonction mail() PHP</li>\n";
echo "<li><strong>Headers incorrects</strong><br>\n";
echo "   → Emails rejetés par les serveurs de destination</li>\n";
echo "<li><strong>Limites de l'hébergeur</strong><br>\n";
echo "   → Quota d'emails dépassé ou restrictions</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<h2>💡 Solutions par ordre de priorité</h2>\n";

echo "<h3>Solution 1: Installer et configurer WP Mail SMTP (RECOMMANDÉ)</h3>\n";
echo "<div style='background:#e8f5e8;padding:15px;border-left:4px solid #4caf50;'>\n";
echo "<strong>🎯 Solution la plus efficace :</strong><br><br>\n";

echo "<strong>Étapes d'installation :</strong><br>\n";
echo "<ol>\n";
echo "<li><strong>Installer le plugin :</strong><br>\n";
echo "   • Aller dans Extensions → Ajouter<br>\n";
echo "   • Rechercher 'WP Mail SMTP by WPForms'<br>\n";
echo "   • Installer et activer</li>\n";
echo "<li><strong>Configuration Gmail SMTP :</strong><br>\n";
echo "   • Aller dans Réglages → WP Mail SMTP<br>\n";
echo "   • Choisir 'Gmail' comme mailer<br>\n";
echo "   • Configurer avec votre compte Gmail</li>\n";
echo "<li><strong>Paramètres Gmail :</strong><br>\n";
echo "   • SMTP Host: smtp.gmail.com<br>\n";
echo "   • Port: 587<br>\n";
echo "   • Encryption: TLS<br>\n";
echo "   • Utiliser un mot de passe d'application</li>\n";
echo "<li><strong>Test d'envoi :</strong><br>\n";
echo "   • Utiliser l'outil de test intégré<br>\n";
echo "   • Vérifier que l'email de test arrive</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<h3>Solution 2: Améliorer la fonction d'envoi existante</h3>\n";
echo "<div style='background:#e3f2fd;padding:15px;border-left:4px solid #2196f3;'>\n";
echo "<strong>🔧 Modifications du code :</strong><br>\n";
echo "<div style='background:#f5f5f5;padding:10px;border:1px solid #ddd;font-family:monospace;margin:10px 0;'>\n";
echo "// Amélioration de la fonction send_email dans notifications.php<br>\n";
echo "public static function send_email(\$to, \$subject, \$message) {<br>\n";
echo "&nbsp;&nbsp;// Validation stricte<br>\n";
echo "&nbsp;&nbsp;if (!is_email(\$to)) {<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;error_log('[IB] Email invalide: ' . \$to);<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;return false;<br>\n";
echo "&nbsp;&nbsp;}<br>\n";
echo "&nbsp;&nbsp;<br>\n";
echo "&nbsp;&nbsp;// Headers améliorés<br>\n";
echo "&nbsp;&nbsp;\$headers = array(<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;'Content-Type: text/html; charset=UTF-8',<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;'Reply-To: ' . get_option('admin_email')<br>\n";
echo "&nbsp;&nbsp;);<br>\n";
echo "&nbsp;&nbsp;<br>\n";
echo "&nbsp;&nbsp;// Tentative avec retry<br>\n";
echo "&nbsp;&nbsp;\$result = wp_mail(\$to, \$subject, \$message, \$headers);<br>\n";
echo "&nbsp;&nbsp;<br>\n";
echo "&nbsp;&nbsp;// Logging détaillé<br>\n";
echo "&nbsp;&nbsp;if (\$result) {<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;error_log('[IB] Email envoyé: ' . \$to);<br>\n";
echo "&nbsp;&nbsp;} else {<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;error_log('[IB] Échec email: ' . \$to);<br>\n";
echo "&nbsp;&nbsp;}<br>\n";
echo "&nbsp;&nbsp;<br>\n";
echo "&nbsp;&nbsp;return \$result;<br>\n";
echo "}<br>\n";
echo "</div>\n";
echo "</div>\n";

echo "<h3>Solution 3: Vérification et test immédiat</h3>\n";
echo "<div style='background:#f3e5f5;padding:15px;border-left:4px solid #9c27b0;'>\n";
echo "<strong>🧪 Tests à effectuer :</strong><br>\n";
echo "<ol>\n";
echo "<li><strong>Test wp_mail direct :</strong><br>\n";
echo "   • Utiliser le bouton 'Test Email' dans l'interface</li>\n";
echo "<li><strong>Vérifier les logs :</strong><br>\n";
echo "   • Activer WP_DEBUG_LOG dans wp-config.php<br>\n";
echo "   • Consulter wp-content/debug.log</li>\n";
echo "<li><strong>Test avec différents emails :</strong><br>\n";
echo "   • Gmail, Outlook, Yahoo<br>\n";
echo "   • Vérifier les dossiers spam</li>\n";
echo "<li><strong>Créer une réservation test :</strong><br>\n";
echo "   • Avec votre propre email<br>\n";
echo "   • Observer si l'email arrive</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<h2>🔧 Actions immédiates à effectuer</h2>\n";

echo "<div style='background:#fff3e0;padding:20px;border-left:4px solid #ff9800;margin:20px 0;'>\n";
echo "<h3>📋 Checklist de résolution :</h3>\n";
echo "<ol style='font-size:16px;line-height:1.6;'>\n";
echo "<li>☐ <strong>Cliquer sur 'Test Email'</strong> dans l'interface réservations</li>\n";
echo "<li>☐ <strong>Vérifier si l'email de test arrive</strong> (et les spams)</li>\n";
echo "<li>☐ <strong>Si échec :</strong> Installer WP Mail SMTP</li>\n";
echo "<li>☐ <strong>Configurer Gmail SMTP</strong> dans WP Mail SMTP</li>\n";
echo "<li>☐ <strong>Tester à nouveau</strong> l'envoi d'email</li>\n";
echo "<li>☐ <strong>Créer une réservation test</strong> avec votre email</li>\n";
echo "<li>☐ <strong>Vérifier la réception</strong> de l'email de remerciement</li>\n";
echo "<li>☐ <strong>Si succès :</strong> Problème résolu !</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<h2>📊 Diagnostic technique</h2>\n";

echo "<div style='background:#f5f5f5;padding:15px;border:1px solid #ddd;'>\n";
echo "<h3>État actuel du système :</h3>\n";

// Tests en temps réel
if (function_exists('wp_mail')) {
    echo "✅ <strong>wp_mail() disponible</strong><br>\n";
} else {
    echo "❌ <strong>wp_mail() non disponible</strong><br>\n";
}

$admin_email = get_option('admin_email');
if (is_email($admin_email)) {
    echo "✅ <strong>Email admin valide :</strong> " . $admin_email . "<br>\n";
} else {
    echo "❌ <strong>Email admin invalide :</strong> " . $admin_email . "<br>\n";
}

$site_name = get_bloginfo('name');
if (!empty($site_name)) {
    echo "✅ <strong>Nom du site configuré :</strong> " . $site_name . "<br>\n";
} else {
    echo "⚠️ <strong>Nom du site non configuré</strong><br>\n";
}

// Vérifier les plugins SMTP
$smtp_plugins = array(
    'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
    'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP'
);

$smtp_active = false;
foreach ($smtp_plugins as $plugin_path => $plugin_name) {
    if (is_plugin_active($plugin_path)) {
        echo "✅ <strong>Plugin SMTP actif :</strong> " . $plugin_name . "<br>\n";
        $smtp_active = true;
    }
}

if (!$smtp_active) {
    echo "⚠️ <strong>Aucun plugin SMTP actif</strong><br>\n";
    echo "→ <em>Installation de WP Mail SMTP recommandée</em><br>\n";
}

// Template de remerciement
$thank_you_template = get_option('ib_notify_client_thankyou', '');
if (!empty($thank_you_template)) {
    echo "✅ <strong>Template de remerciement configuré</strong><br>\n";
} else {
    echo "⚠️ <strong>Template de remerciement manquant</strong><br>\n";
}
echo "</div>\n";

echo "<h2>📞 Support et ressources</h2>\n";

echo "<div style='background:#e3f2fd;padding:15px;border-left:4px solid #2196f3;'>\n";
echo "<h3>Ressources utiles :</h3>\n";
echo "<ul>\n";
echo "<li><strong>Plugin WP Mail SMTP :</strong> <a href='https://wordpress.org/plugins/wp-mail-smtp/' target='_blank'>wordpress.org/plugins/wp-mail-smtp/</a></li>\n";
echo "<li><strong>Configuration Gmail SMTP :</strong> <a href='https://support.google.com/accounts/answer/185833' target='_blank'>Mots de passe d'application Google</a></li>\n";
echo "<li><strong>Test d'email en ligne :</strong> <a href='https://www.mail-tester.com/' target='_blank'>mail-tester.com</a></li>\n";
echo "<li><strong>Documentation WordPress :</strong> <a href='https://developer.wordpress.org/reference/functions/wp_mail/' target='_blank'>wp_mail() function</a></li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h2>🎯 Résultat attendu</h2>\n";

echo "<div style='background:#e8f5e8;padding:20px;border-left:4px solid #4caf50;margin:20px 0;'>\n";
echo "<h3>✅ Après résolution du problème :</h3>\n";
echo "<ul style='font-size:16px;line-height:1.6;'>\n";
echo "<li>📧 <strong>Emails de remerciement</strong> envoyés automatiquement</li>\n";
echo "<li>📬 <strong>Clients reçoivent</strong> la confirmation de réception</li>\n";
echo "<li>📝 <strong>Template personnalisé</strong> avec nom du client et service</li>\n";
echo "<li>🔍 <strong>Logs d'envoi</strong> visibles dans les notifications admin</li>\n";
echo "<li>✅ <strong>Test d'email</strong> fonctionne depuis l'interface</li>\n";
echo "<li>🎨 <strong>Emails HTML</strong> avec mise en forme professionnelle</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<p style='text-align:center;font-size:18px;color:#4caf50;font-weight:bold;'>📧 Suivez le guide étape par étape pour résoudre le problème ! 🔧</p>\n";
?>
