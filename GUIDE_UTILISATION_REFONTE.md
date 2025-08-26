# 🎨 Guide d'Utilisation - Notifications Modernes

## 🚀 Mise en Route Rapide

### 1. Activation du Système

Après avoir modifié le fichier `admin/layout.php`, suivez ces étapes :

1. **Migration rapide** : Exécutez `migration-rapide-refonte.php`
2. **Test d'intégration** : Vérifiez avec `test-integration-refonte.php`
3. **Démonstration** : Testez avec `demo-notifications-refonte.php`

### 2. Vérification de l'Installation

✅ **Fichiers requis :**
- `assets/css/ib-notif-refonte.css`
- `includes/notifications-refonte-integration.php`
- `templates/notification-panel-refonte.php`

✅ **Modifications apportées :**
- `admin/layout.php` - Interface mise à jour
- `institut-booking.php` - Intégration ajoutée

## 🎯 Utilisation de l'Interface

### 🔔 Cloche de Notifications

La nouvelle cloche moderne se trouve dans le header :
- **Icône SVG minimaliste** au lieu de l'ancienne dashicon
- **Badge rouge** avec le nombre de notifications non lues
- **Clic** pour ouvrir le panneau moderne

### 📱 Panneau de Notifications

#### Onglets Intelligents
- **📆 Toutes** : Vue globale de toutes les notifications
- **📅 Réservations** : Nouvelles, confirmées, annulées
- **📩 Emails** : Notifications d'envoi groupées
- **📂 Archivées** : Historique organisé

#### Barre de Recherche
- **Recherche en temps réel** par nom de client ou service
- **Filtrage instantané** sans rechargement
- **Résultats surlignés** pour une meilleure visibilité

### 🎨 Cartes de Notification

Chaque notification est présentée dans une carte moderne :

#### Éléments Visuels
- **Icône SVG** selon le type (calendrier, check, enveloppe)
- **Badge de statut** coloré (Nouveau, Confirmé, Annulé)
- **Métadonnées** : Client, service, date/heure
- **Actions rapides** : Marquer lu, archiver, supprimer

#### États Visuels
- **Non lu** : Fond blanc, bordure colorée
- **Lu** : Fond légèrement grisé
- **Sélectionné** : Bordure bleue, checkbox visible
- **Archivé** : Opacité réduite

## ⚡ Fonctionnalités Avancées

### 🎯 Mode Sélection Multiple

#### Activation
- **Clic long** sur une carte (mobile)
- **Ctrl + Clic** sur une carte (desktop)
- **Checkbox** apparaît automatiquement

#### Barre d'Actions Flottante
Apparaît en bas de l'écran avec :
- **Compteur** de sélections
- **Marquer comme lu** en lot
- **Archiver** plusieurs notifications
- **Supprimer** en masse

### 🔍 Recherche et Filtres

#### Recherche Avancée
```
Exemples de recherche :
- "Marie" → Trouve toutes les notifications de Marie
- "Soin visage" → Trouve toutes les notifications de ce service
- "email" → Trouve toutes les notifications d'email
```

#### Filtres Automatiques
- **Par type** : Réservations, emails, rappels
- **Par statut** : Lu, non lu, archivé
- **Par date** : Aujourd'hui, cette semaine, ce mois

### 🤖 Regroupement Intelligent

#### Emails Groupés
- **Plus de 3 emails similaires** → Groupe automatique
- **Expansion** au clic pour voir le détail
- **Actions groupées** disponibles

#### Nettoyage Automatique
- **Suppression des doublons** quotidienne
- **Archivage automatique** des anciennes notifications
- **Regroupement** des notifications résolues

## 🎨 Personnalisation

### Couleurs et Thème

Modifiez les variables CSS dans `ib-notif-refonte.css` :

```css
:root {
  /* Couleurs principales */
  --notif-primary: #e8b4cb;        /* Rose principal */
  --notif-primary-light: #f5f0f1;  /* Rose clair */
  --notif-beige-warm: #fdfcfb;     /* Beige chaud */
  
  /* Personnalisez selon votre charte */
  --notif-primary: #votre-couleur;
}
```

### Configuration des Options

```php
// Dans wp-admin ou via code
update_option('ib_notif_auto_refresh', true);        // Actualisation auto
update_option('ib_notif_refresh_interval', 30000);   // Intervalle (ms)
update_option('ib_notif_auto_archive_days', 7);      // Archivage auto (jours)
update_option('ib_notif_max_notifications', 50);     // Limite d'affichage
```

## 📱 Responsive Design

### Mobile (< 768px)
- **Panneau plein écran** pour une meilleure lisibilité
- **Onglets empilés** verticalement
- **Touch gestures** optimisés
- **Sélection par clic long**

### Tablet (768px - 1024px)
- **Panneau latéral** adaptatif
- **Onglets horizontaux** compacts
- **Zones de clic** agrandies

### Desktop (> 1024px)
- **Panneau latéral** fixe
- **Onglets complets** avec compteurs
- **Raccourcis clavier** disponibles

## 🔧 Maintenance et Optimisation

### Nettoyage Automatique

Le système inclut un nettoyage quotidien :

```php
// Programmé automatiquement
wp_schedule_event(time(), 'daily', 'ib_daily_cleanup');

// Actions de nettoyage :
// - Archivage des notifications anciennes (7 jours par défaut)
// - Suppression des doublons
// - Regroupement des emails similaires
// - Optimisation de la base de données
```

### Monitoring des Performances

```php
// Métriques disponibles
$counts = [
    'total' => get_notification_count('all'),
    'unread' => get_notification_count('unread'),
    'today' => get_notification_count('today')
];

// Logs automatiques
error_log("IB Notifications: {$counts['unread']} non lues");
```

## 🆘 Dépannage

### Problèmes Courants

#### 1. Panneau ne s'ouvre pas
```javascript
// Vérifier dans la console
console.log(typeof testNotifications); // Doit retourner 'function'

// Solutions :
// - Vider le cache du navigateur
// - Vérifier que les fichiers JS sont chargés
// - Contrôler les erreurs JavaScript
```

#### 2. Styles non appliqués
```css
/* Vérifier que le CSS est chargé */
/* Inspecter l'élément .ib-notif-refonte */

/* Solutions : */
/* - Vider le cache */
/* - Vérifier l'URL du CSS */
/* - Contrôler les conflits de styles */
```

#### 3. Notifications non chargées
```php
// Vérifier les permissions
if (!current_user_can('manage_options')) {
    // Problème de permissions
}

// Vérifier les nonces AJAX
wp_verify_nonce($_POST['nonce'], 'ib_notifications_nonce');

// Vérifier la table
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
```

### Mode Debug

```php
// Activer le debug
define('IB_NOTIF_DEBUG', true);

// Dans wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Logs détaillés dans /wp-content/debug.log
```

## 📞 Support et Ressources

### Fichiers de Test
- `test-integration-refonte.php` - Vérification complète
- `demo-notifications-refonte.php` - Démonstration interactive
- `migration-rapide-refonte.php` - Activation express

### Documentation Technique
- `NOTIFICATIONS_REFONTE_README.md` - Documentation complète
- Code source commenté dans tous les fichiers
- Exemples d'utilisation dans les templates

### Compatibilité
- **WordPress** : 5.0+ recommandé
- **PHP** : 7.4+ requis
- **Navigateurs** : Tous navigateurs modernes + IE11
- **Mobile** : iOS Safari, Android Chrome

---

**Version** : 3.0.0 - Refonte complète  
**Dernière mise à jour** : <?php echo date('d/m/Y'); ?>  
**Support** : Consultez les fichiers de test et la documentation technique
