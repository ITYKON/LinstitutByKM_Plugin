<?php
/**
 * 🎨 DÉMONSTRATION DU SYSTÈME DE NOTIFICATIONS MODERNE
 * ================================================================
 * Fichier de démonstration pour tester le nouveau panneau de notifications
 * À utiliser uniquement en développement
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
 * 🧪 CRÉER DES NOTIFICATIONS DE TEST
 */
function create_demo_notifications() {
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    
    // Notifications de test variées
    $demo_notifications = [
        [
            'type' => 'booking_new',
            'message' => 'Nouvelle réservation : Soin visage pour Marie Dubois le 15/01/2024 (Sophie)',
            'target' => 'admin',
            'status' => 'unread',
            'client_name' => 'Marie Dubois',
            'service_name' => 'Soin visage',
            'link' => admin_url('admin.php?page=institut-booking-bookings&action=edit&id=123'),
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
        ],
        [
            'type' => 'booking_confirmed',
            'message' => 'Réservation confirmée : Massage relaxant pour Julie Martin le 16/01/2024 (Emma)',
            'target' => 'admin',
            'status' => 'unread',
            'client_name' => 'Julie Martin',
            'service_name' => 'Massage relaxant',
            'link' => admin_url('admin.php?page=institut-booking-bookings&action=edit&id=124'),
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
        ],
        [
            'type' => 'booking_cancelled',
            'message' => 'Annulation : Épilation jambes pour Sarah Leroy le 17/01/2024 (Lisa)',
            'target' => 'admin',
            'status' => 'read',
            'client_name' => 'Sarah Leroy',
            'service_name' => 'Épilation jambes',
            'link' => admin_url('admin.php?page=institut-booking-bookings&action=edit&id=125'),
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ],
        [
            'type' => 'email',
            'message' => 'Email de confirmation envoyé à claire.bernard@email.com',
            'target' => 'admin',
            'status' => 'read',
            'client_name' => 'Claire Bernard',
            'service_name' => 'Manucure',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'type' => 'reminder',
            'message' => 'Rappel envoyé : RDV demain à 14h pour Amélie Rousseau',
            'target' => 'admin',
            'status' => 'unread',
            'client_name' => 'Amélie Rousseau',
            'service_name' => 'Soin anti-âge',
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
        ],
        [
            'type' => 'booking_new',
            'message' => 'Nouvelle réservation : Pédicure pour Laura Petit le 18/01/2024 (Sophie)',
            'target' => 'admin',
            'status' => 'unread',
            'client_name' => 'Laura Petit',
            'service_name' => 'Pédicure',
            'link' => admin_url('admin.php?page=institut-booking-bookings&action=edit&id=126'),
            'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))
        ],
        [
            'type' => 'email',
            'message' => 'Email de remerciement envoyé à marie.dubois@email.com',
            'target' => 'admin',
            'status' => 'read',
            'client_name' => 'Marie Dubois',
            'service_name' => 'Soin visage',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours'))
        ],
        [
            'type' => 'email',
            'message' => 'Email de rappel envoyé à julie.martin@email.com',
            'target' => 'admin',
            'status' => 'read',
            'client_name' => 'Julie Martin',
            'service_name' => 'Massage relaxant',
            'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours'))
        ],
        [
            'type' => 'email',
            'message' => 'Email de confirmation envoyé à laura.petit@email.com',
            'target' => 'admin',
            'status' => 'read',
            'client_name' => 'Laura Petit',
            'service_name' => 'Pédicure',
            'created_at' => date('Y-m-d H:i:s', strtotime('-7 hours'))
        ],
        [
            'type' => 'email',
            'message' => 'Email promotionnel envoyé à amelie.rousseau@email.com',
            'target' => 'admin',
            'status' => 'read',
            'client_name' => 'Amélie Rousseau',
            'service_name' => 'Soin anti-âge',
            'created_at' => date('Y-m-d H:i:s', strtotime('-8 hours'))
        ]
    ];
    
    // Insérer les notifications de test
    foreach ($demo_notifications as $notification) {
        $wpdb->insert($table, $notification);
    }
    
    return count($demo_notifications);
}

/**
 * 🧹 NETTOYER LES NOTIFICATIONS DE TEST
 */
function cleanup_demo_notifications() {
    global $wpdb;
    $table = $wpdb->prefix . 'ib_notifications';
    
    // Supprimer toutes les notifications de test (créées dans les dernières 24h)
    $result = $wpdb->query($wpdb->prepare(
        "DELETE FROM {$table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
    ));
    
    return $result;
}

// Traitement des actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'create_demo':
            $count = create_demo_notifications();
            $message = "✅ {$count} notifications de démonstration créées avec succès !";
            break;
            
        case 'cleanup_demo':
            $count = cleanup_demo_notifications();
            $message = "🧹 {$count} notifications de test supprimées.";
            break;
            
        default:
            $message = "❌ Action non reconnue.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Démonstration - Notifications Modernes</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: #f9fafb;
        }
        .demo-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .demo-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .demo-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .demo-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        .demo-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .demo-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
        }
        .demo-btn-primary {
            background: #e8b4cb;
            color: white;
        }
        .demo-btn-primary:hover {
            background: #d89bb5;
            transform: translateY(-1px);
        }
        .demo-btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }
        .demo-btn-secondary:hover {
            background: #e5e7eb;
        }
        .demo-message {
            background: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .demo-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .demo-feature {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #e8b4cb;
        }
        .demo-feature h3 {
            margin: 0 0 0.5rem 0;
            color: #1f2937;
        }
        .demo-feature p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }
        .demo-instructions {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        .demo-instructions h3 {
            margin: 0 0 1rem 0;
            color: #92400e;
        }
        .demo-instructions ol {
            margin: 0;
            padding-left: 1.5rem;
            color: #92400e;
        }
        .demo-instructions li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <div class="demo-header">
            <h1 class="demo-title">🎨 Notifications Modernes</h1>
            <p class="demo-subtitle">Système de notifications refondu pour Institut Booking</p>
        </div>

        <?php if (isset($message)): ?>
            <div class="demo-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="demo-actions">
            <a href="?action=create_demo" class="demo-btn demo-btn-primary">
                ✨ Créer des notifications de test
            </a>
            <a href="?action=cleanup_demo" class="demo-btn demo-btn-secondary">
                🧹 Nettoyer les notifications de test
            </a>
        </div>

        <div class="demo-features">
            <div class="demo-feature">
                <h3>🎯 Design Moderne</h3>
                <p>Interface minimaliste inspirée de Planity/Fresha avec couleurs douces et espaces aérés</p>
            </div>
            <div class="demo-feature">
                <h3>📱 Onglets Intelligents</h3>
                <p>Filtrage par type : Toutes, Réservations, Emails, Archivées avec compteurs en temps réel</p>
            </div>
            <div class="demo-feature">
                <h3>✅ Sélection Multiple</h3>
                <p>Mode batch avec barre d'actions flottante pour marquer, archiver ou supprimer en lot</p>
            </div>
            <div class="demo-feature">
                <h3>🔍 Recherche Avancée</h3>
                <p>Recherche en temps réel par nom de client, service ou contenu de notification</p>
            </div>
            <div class="demo-feature">
                <h3>📂 Regroupement Auto</h3>
                <p>Regroupement intelligent des emails similaires et nettoyage automatique</p>
            </div>
            <div class="demo-feature">
                <h3>⚡ Animations Fluides</h3>
                <p>Transitions et animations modernes pour une expérience utilisateur premium</p>
            </div>
        </div>

        <div class="demo-instructions">
            <h3>📋 Instructions de test</h3>
            <ol>
                <li>Cliquez sur "Créer des notifications de test" pour générer des exemples</li>
                <li>Ouvrez le panneau de notifications en cliquant sur la cloche 🔔</li>
                <li>Testez les différents onglets et la recherche</li>
                <li>Essayez le mode sélection multiple (clic long sur une carte)</li>
                <li>Explorez les animations et interactions</li>
                <li>Nettoyez les données de test quand vous avez terminé</li>
            </ol>
        </div>
    </div>
</body>
</html>
