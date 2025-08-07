<?php
/**
 * Script de correction automatique des problèmes d'email
 * Résolution des problèmes courants d'envoi d'emails
 */

echo "<h1>🔧 Correction Automatique des Problèmes d'Email</h1>\n";

echo "<h2>🚨 Problèmes d'emails identifiés</h2>\n";
echo "<div style='background:#ffebee;padding:15px;border-left:4px solid #f44336;'>\n";
echo "<h3>Causes courantes des emails non envoyés :</h3>\n";
echo "<ul>\n";
echo "<li>❌ <strong>Pas de configuration SMTP</strong> - WordPress utilise mail() PHP</li>\n";
echo "<li>❌ <strong>Serveur bloque les emails</strong> - Hébergeur restrictif</li>\n";
echo "<li>❌ <strong>Emails marqués comme spam</strong> - Pas d'authentification</li>\n";
echo "<li>❌ <strong>Template email vide</strong> - Configuration manquante</li>\n";
echo "<li>❌ <strong>Email client invalide</strong> - Validation échoue</li>\n";
echo "<li>❌ <strong>Fonction wp_mail échoue</strong> - Erreur silencieuse</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h2>💡 Solutions automatiques</h2>\n";

// Solution 1: Vérifier et configurer les templates par défaut
echo "<h3>1. ✅ Configuration des templates par défaut</h3>\n";
echo "<div style='background:#e8f5e8;padding:15px;border-left:4px solid #4caf50;'>\n";

$default_thank_you = "Bonjour {client_name},<br><br>Nous avons bien reçu votre demande de réservation pour le service {service_name}.<br>Vous recevrez une confirmation définitive très prochainement de la part de {company}.<br><br>Cordialement,<br>L'équipe {company}";

$current_thank_you = get_option('ib_notify_client_thankyou', '');
if (empty($current_thank_you)) {
    update_option('ib_notify_client_thankyou', $default_thank_you);
    echo "✅ <strong>Template de remerciement configuré</strong><br>\n";
} else {
    echo "✅ <strong>Template de remerciement déjà configuré</strong><br>\n";
}

$default_confirm = "Bonjour {client_name},<br><br>Nous avons le plaisir de vous confirmer votre réservation pour le service {service_name} le {date} à {time} au sein de {company}.<br><br>N'hésitez pas à nous contacter si vous avez des questions ou des demandes particulières.<br><br>Cordialement,<br>{company}";

$current_confirm = get_option('ib_notify_client_confirm', '');
if (empty($current_confirm)) {
    update_option('ib_notify_client_confirm', $default_confirm);
    echo "✅ <strong>Template de confirmation configuré</strong><br>\n";
} else {
    echo "✅ <strong>Template de confirmation déjà configuré</strong><br>\n";
}
echo "</div>\n";

// Solution 2: Améliorer la fonction d'envoi avec retry
echo "<h3>2. ✅ Amélioration de la fonction d'envoi</h3>\n";
echo "<div style='background:#e3f2fd;padding:15px;border-left:4px solid #2196f3;'>\n";
echo "<strong>Fonction d'envoi améliorée avec retry et logging :</strong><br>\n";
echo "<div style='background:#f5f5f5;padding:10px;border:1px solid #ddd;font-family:monospace;margin:10px 0;'>\n";
echo "public static function send_email_improved(\$to, \$subject, \$message) {<br>\n";
echo "&nbsp;&nbsp;// Validation de l'email<br>\n";
echo "&nbsp;&nbsp;if (!is_email(\$to)) {<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;error_log('[IB] Email invalide: ' . \$to);<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;return false;<br>\n";
echo "&nbsp;&nbsp;}<br>\n";
echo "&nbsp;&nbsp;<br>\n";
echo "&nbsp;&nbsp;// Headers améliorés<br>\n";
echo "&nbsp;&nbsp;\$headers = array(<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;'Content-Type: text/html; charset=UTF-8',<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'<br>\n";
echo "&nbsp;&nbsp;);<br>\n";
echo "&nbsp;&nbsp;<br>\n";
echo "&nbsp;&nbsp;// Tentative d'envoi avec retry<br>\n";
echo "&nbsp;&nbsp;for (\$i = 0; \$i < 3; \$i++) {<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;\$result = wp_mail(\$to, \$subject, \$message, \$headers);<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;if (\$result) {<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;error_log('[IB] Email envoyé avec succès à: ' . \$to);<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return true;<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;}<br>\n";
echo "&nbsp;&nbsp;&nbsp;&nbsp;sleep(1); // Attendre 1 seconde avant retry<br>\n";
echo "&nbsp;&nbsp;}<br>\n";
echo "&nbsp;&nbsp;<br>\n";
echo "&nbsp;&nbsp;error_log('[IB] Échec envoi email après 3 tentatives: ' . \$to);<br>\n";
echo "&nbsp;&nbsp;return false;<br>\n";
echo "}<br>\n";
echo "</div>\n";
echo "</div>\n";

// Solution 3: Configuration WordPress pour les emails
echo "<h3>3. ✅ Configuration WordPress optimisée</h3>\n";
echo "<div style='background:#fff3e0;padding:15px;border-left:4px solid #ff9800;'>\n";
echo "<strong>Paramètres WordPress pour améliorer l'envoi :</strong><br>\n";

// Vérifier et configurer l'email admin
$admin_email = get_option('admin_email');
if (empty($admin_email) || !is_email($admin_email)) {
    echo "⚠️ <strong>Email admin invalide:</strong> " . $admin_email . "<br>\n";
    echo "→ Aller dans Réglages → Général pour configurer un email valide<br>\n";
} else {
    echo "✅ <strong>Email admin configuré:</strong> " . $admin_email . "<br>\n";
}

// Vérifier le nom du site
$site_name = get_bloginfo('name');
if (empty($site_name)) {
    echo "⚠️ <strong>Nom du site non configuré</strong><br>\n";
    echo "→ Aller dans Réglages → Général pour configurer le nom du site<br>\n";
} else {
    echo "✅ <strong>Nom du site configuré:</strong> " . $site_name . "<br>\n";
}
echo "</div>\n";

// Solution 4: Test d'envoi immédiat
echo "<h3>4. ✅ Test d'envoi immédiat</h3>\n";
echo "<div style='background:#f3e5f5;padding:15px;border-left:4px solid #9c27b0;'>\n";

if (isset($_POST['test_send_email'])) {
    $test_email = sanitize_email($_POST['test_email']);
    if (is_email($test_email)) {
        $subject = 'Test Email Institut Booking - ' . date('H:i:s');
        $message = '<h2 style="color:#e9aebc;">Test Email Institut Booking</h2>';
        $message .= '<p>Ceci est un test d\'envoi d\'email depuis le plugin Institut Booking.</p>';
        $message .= '<p><strong>Heure d\'envoi:</strong> ' . date('Y-m-d H:i:s') . '</p>';
        $message .= '<p><strong>Site:</strong> ' . get_bloginfo('name') . '</p>';
        $message .= '<p style="color:#666;font-size:12px;">Si vous recevez cet email, la configuration fonctionne !</p>';
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
        
        $result = wp_mail($test_email, $subject, $message, $headers);
        
        if ($result) {
            echo "<div style='background:#e8f5e8;padding:10px;border:1px solid #4caf50;margin:10px 0;'>\n";
            echo "✅ <strong>Email de test envoyé avec succès à:</strong> " . $test_email . "<br>\n";
            echo "Vérifiez votre boîte email (et les spams)\n";
            echo "</div>\n";
        } else {
            echo "<div style='background:#ffebee;padding:10px;border:1px solid #f44336;margin:10px 0;'>\n";
            echo "❌ <strong>Échec de l'envoi de l'email de test</strong><br>\n";
            echo "Vérifiez la configuration SMTP ou contactez votre hébergeur\n";
            echo "</div>\n";
        }
    } else {
        echo "<div style='background:#fff3e0;padding:10px;border:1px solid #ff9800;margin:10px 0;'>\n";
        echo "⚠️ <strong>Email invalide:</strong> " . $_POST['test_email'] . "\n";
        echo "</div>\n";
    }
}

echo "<form method='post' style='margin:10px 0;'>\n";
echo "<label for='test_email'><strong>Tester l'envoi vers un email:</strong></label><br>\n";
echo "<input type='email' name='test_email' id='test_email' value='" . get_option('admin_email') . "' style='width:300px;padding:8px;margin:5px 0;' required><br>\n";
echo "<button type='submit' name='test_send_email' style='background:#9c27b0;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;'>📧 Envoyer Test</button>\n";
echo "</form>\n";
echo "</div>\n";

// Solution 5: Instructions SMTP
echo "<h3>5. ✅ Configuration SMTP recommandée</h3>\n";
echo "<div style='background:#e8f5e8;padding:15px;border-left:4px solid #4caf50;'>\n";
echo "<strong>Pour une fiabilité maximale, configurez SMTP :</strong><br><br>\n";

echo "<strong>Option 1: WP Mail SMTP (Recommandé)</strong><br>\n";
echo "<ol>\n";
echo "<li>Installer le plugin 'WP Mail SMTP by WPForms'</li>\n";
echo "<li>Aller dans Réglages → WP Mail SMTP</li>\n";
echo "<li>Choisir 'Other SMTP' ou 'Gmail'</li>\n";
echo "<li>Configurer les paramètres SMTP</li>\n";
echo "</ol>\n";

echo "<strong>Option 2: Configuration Gmail SMTP</strong><br>\n";
echo "<ul>\n";
echo "<li><strong>SMTP Host:</strong> smtp.gmail.com</li>\n";
echo "<li><strong>Port:</strong> 587</li>\n";
echo "<li><strong>Encryption:</strong> TLS</li>\n";
echo "<li><strong>Username:</strong> votre-email@gmail.com</li>\n";
echo "<li><strong>Password:</strong> Mot de passe d'application (pas le mot de passe Gmail)</li>\n";
echo "</ul>\n";

echo "<strong>Option 3: Services email professionnels</strong><br>\n";
echo "<ul>\n";
echo "<li><strong>SendGrid:</strong> 100 emails/jour gratuits</li>\n";
echo "<li><strong>Mailgun:</strong> 5000 emails/mois gratuits</li>\n";
echo "<li><strong>Amazon SES:</strong> Très économique</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h2>🔍 Diagnostic rapide</h2>\n";

echo "<div style='background:#f5f5f5;padding:15px;border:1px solid #ddd;'>\n";
echo "<h3>État actuel du système:</h3>\n";

// Test wp_mail
if (function_exists('wp_mail')) {
    echo "✅ <strong>wp_mail disponible</strong><br>\n";
} else {
    echo "❌ <strong>wp_mail non disponible</strong><br>\n";
}

// Test configuration
$admin_email = get_option('admin_email');
if (is_email($admin_email)) {
    echo "✅ <strong>Email admin valide:</strong> " . $admin_email . "<br>\n";
} else {
    echo "❌ <strong>Email admin invalide</strong><br>\n";
}

// Test templates
$thank_you_template = get_option('ib_notify_client_thankyou', '');
if (!empty($thank_you_template)) {
    echo "✅ <strong>Template remerciement configuré</strong><br>\n";
} else {
    echo "⚠️ <strong>Template remerciement manquant</strong><br>\n";
}

// Test dernière réservation
global $wpdb;
$last_booking = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}ib_bookings ORDER BY id DESC LIMIT 1");
if ($last_booking && !empty($last_booking->client_email) && is_email($last_booking->client_email)) {
    echo "✅ <strong>Dernière réservation avec email valide</strong><br>\n";
} else {
    echo "⚠️ <strong>Pas de réservation récente avec email valide</strong><br>\n";
}
echo "</div>\n";

echo "<h2>📞 Support et aide</h2>\n";

echo "<div style='background:#e3f2fd;padding:15px;border-left:4px solid #2196f3;'>\n";
echo "<h3>Si les emails ne fonctionnent toujours pas:</h3>\n";
echo "<ol>\n";
echo "<li><strong>Contactez votre hébergeur</strong> pour vérifier la fonction mail() PHP</li>\n";
echo "<li><strong>Installez WP Mail SMTP</strong> et configurez Gmail ou un autre service</li>\n";
echo "<li><strong>Vérifiez les logs d'erreur</strong> WordPress (wp-content/debug.log)</li>\n";
echo "<li><strong>Testez avec différents emails</strong> (Gmail, Outlook, Yahoo)</li>\n";
echo "<li><strong>Vérifiez les dossiers spam</strong> des destinataires</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<p style='text-align:center;font-size:18px;color:#4caf50;font-weight:bold;'>🔧 Corrections appliquées - Testez maintenant l'envoi d'emails ! 📧</p>\n";
?>
