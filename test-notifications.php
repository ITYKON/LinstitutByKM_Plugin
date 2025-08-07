<?php
/**
 * Script de test pour les notifications
 * À exécuter une fois pour créer des notifications de test
 */

// Inclure WordPress
require_once('../../../wp-load.php');

// Vérifier que nous sommes admin
if (!current_user_can('administrator')) {
    die('Accès refusé');
}

global $wpdb;
$table = $wpdb->prefix . 'ib_notifications';

echo "<h1>Test des notifications</h1>";

// Créer quelques notifications de test
$test_notifications = [
    [
        'type' => 'reservation',
        'message' => 'ahlem ahlem a réservé Balayage le 2025-08-14 (Lamia)',
        'status' => 'unread',
        'reservation_id' => 123
    ],
    [
        'type' => 'confirmation',
        'message' => 'Réservation confirmée : coiffure fête sans brushing pour ahlem ahlem le 2025-08-14 (Lamia)',
        'status' => 'unread',
        'reservation_id' => 123
    ],
    [
        'type' => 'email',
        'message' => 'Mail de confirmation envoyé au client (ahlem@test.com) pour la réservation 123',
        'status' => 'read'
    ],
    [
        'type' => 'email',
        'message' => 'Mail de remerciement envoyé au client (melissa@test.com) pour la réservation 124',
        'status' => 'read'
    ],
    [
        'type' => 'reservation',
        'message' => 'melissa had a réservé Patine le 2025-08-09 (Dalia)',
        'status' => 'unread',
        'reservation_id' => 125
    ]
];

$inserted = 0;
foreach ($test_notifications as $notif) {
    $result = $wpdb->insert($table, [
        'type' => $notif['type'],
        'message' => $notif['message'],
        'target' => 'admin',
        'status' => $notif['status'],
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql')
    ]);
    
    if ($result) {
        $inserted++;
        echo "<p>✅ Notification créée : {$notif['message']}</p>";
    } else {
        echo "<p>❌ Erreur lors de la création : {$notif['message']}</p>";
    }
}

echo "<h2>Résultat</h2>";
echo "<p>$inserted notifications de test créées.</p>";

// Vérifier les notifications existantes
$count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE target = 'admin'");
$unread = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE target = 'admin' AND status = 'unread'");

echo "<h2>Statistiques</h2>";
echo "<p>Total notifications admin : $count</p>";
echo "<p>Notifications non lues : $unread</p>";

echo "<h2>Test AJAX</h2>";
echo "<p>Vous pouvez maintenant tester le panneau de notifications en cliquant sur la cloche dans l'interface admin.</p>";
echo "<p>Ouvrez la console du navigateur (F12) pour voir les logs de debug.</p>";

echo "<h2>Fonctionnalités à tester</h2>";
echo "<ul>";
echo "<li>✅ Clic sur la cloche → ouverture du panneau</li>";
echo "<li>✅ Nettoyage automatique des réservations confirmées</li>";
echo "<li>✅ Regroupement des emails</li>";
echo "<li>✅ Clic long → mode batch</li>";
echo "<li>✅ Sélection multiple</li>";
echo "<li>✅ Actions en masse (marquer lu, supprimer, archiver)</li>";
echo "<li>✅ Filtres rapides (non lues, nouvelles résa)</li>";
echo "</ul>";
?> 