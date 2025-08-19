# 🧪 Guide de Test - Notifications Améliorées

## 📋 Prérequis
1. Être connecté en tant qu'administrateur WordPress
2. Avoir accès à l'interface admin du plugin Institut Booking

## 🚀 Étapes de test

### 1. Créer des notifications de test
```bash
# Accéder au fichier de test (via navigateur ou terminal)
http://votre-site.com/wp-content/plugins/Booking-plugin-version02/test-notifications.php
```

### 2. Tester le panneau de notifications
1. **Ouvrir l'interface admin** : `http://votre-site.com/wp-admin/admin.php?page=institut-booking`
2. **Ouvrir la console** : Appuyer sur F12 → onglet Console
3. **Cliquer sur la cloche** : En haut à droite de l'interface

### 3. Vérifier les logs de debug
Dans la console, vous devriez voir :
```
🔔 Initialisation du panneau de notifications...
🔔 Panneau de notifications initialisé: [object]
🔔 Tentative d'ouverture du panneau de notifications...
🔔 Panneau trouvé, ouverture...
```

## ✅ Fonctionnalités à tester

### A. Nettoyage automatique & Regroupement
- [ ] **Nettoyage automatique** : Les notifications de "nouvelle réservation" disparaissent si confirmée/annulée
- [ ] **Regroupement des emails** : Les emails sont groupés dans une carte unique
- [ ] **Format attendu** : `📩 15 mails envoyés aujourd'hui` + `12 confirmations · 3 annulations`

### B. Actions en masse (Batch Mode)
- [ ] **Clic long** (500ms) sur une notification → mode batch activé
- [ ] **Cases à cocher** apparaissent à gauche de chaque carte
- [ ] **Barre flottante** en bas avec :
  - [ ] ✔ Marquer lu
  - [ ] 🗑 Supprimer  
  - [ ] 📂 Archiver
  - [ ] Annuler
- [ ] **Bouton "Tout sélectionner"** avec filtres :
  - [ ] Non lues
  - [ ] Nouvelles résa

### C. Interface utilisateur
- [ ] **Animations fluides** lors de l'apparition des cases à cocher
- [ ] **Feedback visuel** lors du clic long (scale effect)
- [ ] **Design responsive** sur mobile/tablette
- [ ] **Support tactile** pour mobile (clic long 600ms)

## 🐛 Dépannage

### Problème : Le panneau ne s'ouvre pas
**Solution :**
1. Vérifier que les scripts sont chargés (F12 → onglet Network)
2. Vérifier les erreurs JavaScript (F12 → onglet Console)
3. Vérifier que `IBNotifBell` est défini dans la console

### Problème : Les notifications ne se chargent pas
**Solution :**
1. Vérifier que la table `wp_ib_notifications` existe
2. Vérifier les logs PHP (error_log)
3. Tester l'endpoint AJAX directement

### Problème : Le mode batch ne fonctionne pas
**Solution :**
1. Vérifier que le clic long dure au moins 500ms
2. Vérifier que les événements tactiles sont bien attachés
3. Tester sur différents navigateurs

## 📱 Test mobile
1. **Ouvrir l'interface sur mobile**
2. **Appuyer longuement** (600ms) sur une notification
3. **Vérifier** que le mode batch s'active
4. **Tester** les actions en masse

## 🎯 Résultat attendu
- ✅ Panneau moderne et responsive
- ✅ Nettoyage automatique des notifications obsolètes
- ✅ Regroupement intelligent des emails
- ✅ Mode batch fonctionnel avec sélection multiple
- ✅ Actions en masse (marquer lu, supprimer, archiver)
- ✅ Filtres rapides pour la sélection

## 📞 Support
Si un problème persiste, vérifier :
1. Les logs de la console (F12)
2. Les logs PHP (error_log)
3. La version de WordPress et PHP
4. Les conflits avec d'autres plugins 