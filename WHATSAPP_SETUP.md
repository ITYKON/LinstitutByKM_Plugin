# 📱 Guide de Configuration WhatsApp Business

## 🎯 **Options disponibles pour l'envoi de messages WhatsApp**

### **1. Twilio WhatsApp Business API** (Recommandé)

**Avantages :**

- ✅ Interface simple et fiable
- ✅ Documentation excellente
- ✅ Support multilingue
- ✅ Tarifs compétitifs
- ✅ API stable

**Configuration :**

1. Créez un compte sur [Twilio.com](https://www.twilio.com)
2. Activez WhatsApp Business API
3. Récupérez vos identifiants :
   - Account SID
   - Auth Token
   - Numéro WhatsApp Business
4. Configurez dans le plugin

**Coûts :**

- ~0.0085€ par message (tarifs 2024)
- Compte gratuit pour tests

---

### **2. WhatsApp Business API Direct** (Meta/Facebook)

**Avantages :**

- ✅ Tarifs très bas
- ✅ Intégration native
- ✅ Fonctionnalités avancées

**Inconvénients :**

- ❌ Processus d'approbation complexe
- ❌ Documentation technique
- ❌ Support limité

**Configuration :**

1. Créez un compte Meta Business
2. Demandez l'accès à WhatsApp Business API
3. Configurez votre application
4. Intégrez via webhooks

---

### **3. Solutions alternatives populaires**

#### **A. MessageBird**

- Interface simple
- Support WhatsApp, SMS, Voice
- Tarifs : ~0.01€ par message

#### **B. Vonage (ex-Nexmo)**

- API robuste
- Documentation complète
- Tarifs : ~0.009€ par message

#### **C. 360dialog**

- Spécialisé WhatsApp
- Support premium
- Tarifs : variables

---

## 🔧 **Configuration dans le plugin**

### **Étape 1 : Configuration Twilio**

1. Allez dans **SMS & WhatsApp** dans l'admin
2. Remplissez les champs :
   - **Account SID** : `AC1234567890abcdef...`
   - **Auth Token** : Votre token secret
   - **Numéro WhatsApp** : `+33612345678`

### **Étape 2 : Test de configuration**

1. Cliquez sur **"Tester la configuration Twilio"**
2. Vérifiez que le test passe
3. Si erreur, vérifiez vos identifiants

### **Étape 3 : Envoi de messages**

1. Sélectionnez un client
2. Choisissez **WhatsApp** comme type
3. Rédigez votre message
4. Cliquez sur **Envoyer**

---

## 📋 **Format des numéros de téléphone**

**Important :** Les numéros doivent être au format international :

- ✅ `+33612345678` (France)
- ✅ `+1234567890` (États-Unis)
- ❌ `0612345678` (format local)
- ❌ `33612345678` (sans le +)

---

## 🧪 **Mode Sandbox (Tests)**

Pour tester sans numéro WhatsApp Business :

1. Utilisez le numéro sandbox : `+14155238886`
2. Envoyez `join <mot-de-passe>` à ce numéro
3. Vous pourrez recevoir des messages de test

---

## 💰 **Coûts estimés**

| Service         | Coût par message | Frais mensuels |
| --------------- | ---------------- | -------------- |
| Twilio          | ~0.0085€         | 0€             |
| MessageBird     | ~0.01€           | 0€             |
| Vonage          | ~0.009€          | 0€             |
| WhatsApp Direct | ~0.005€          | Variables      |

---

## 🚨 **Limitations importantes**

### **WhatsApp Business API :**

- Messages uniquement vers des numéros opt-in
- Pas de spam autorisé
- Templates pour messages marketing
- Messages libres pour support client

### **Règles de conformité :**

- Respecter les heures d'envoi (8h-21h)
- Pas de messages automatiques excessifs
- Respecter la vie privée des utilisateurs
- Se conformer au RGPD

---

## 🔍 **Dépannage**

### **Erreur "Configuration Twilio manquante"**

- Vérifiez que tous les champs sont remplis
- Vérifiez le format du numéro de téléphone

### **Erreur "Numéro de téléphone invalide"**

- Utilisez le format international (+33...)
- Vérifiez que le numéro existe

### **Erreur "Erreur Twilio"**

- Vérifiez vos identifiants
- Vérifiez votre solde Twilio
- Vérifiez que WhatsApp Business est activé

---

## 📞 **Support**

Pour toute question :

1. Consultez la [documentation Twilio](https://www.twilio.com/docs/whatsapp)
2. Vérifiez les logs dans la page **Logs** du plugin
3. Contactez le support technique

---

## 🔄 **Mise à jour du plugin**

Le plugin supporte actuellement Twilio. Pour ajouter d'autres services :

1. Modifiez `includes/helpers.php`
2. Ajoutez les nouvelles fonctions d'envoi
3. Mettez à jour l'interface utilisateur
4. Testez avec des messages de test

---

# 📱 Guide WhatsApp Business API - Alternatives Gratuites

## 🆓 **Alternatives GRATUITES**

### **1. WhatsApp Web + Selenium (100% Gratuit)**

**Principe :** Automatisation de WhatsApp Web via navigateur

**Avantages :**

- ✅ 100% gratuit
- ✅ Pas de limite de messages
- ✅ Utilise votre compte WhatsApp personnel
- ✅ Fonctionne immédiatement

**Inconvénients :**

- ❌ Nécessite un navigateur ouvert
- ❌ Peut être détecté par WhatsApp
- ❌ Moins fiable pour un usage professionnel
- ❌ Nécessite Selenium WebDriver

**Installation :**

```bash
# Installer Selenium WebDriver
composer require php-webdriver/webdriver

# Installer ChromeDriver
# Télécharger depuis : https://chromedriver.chromium.org/
```

**Configuration :**

1. Activer le "Mode Gratuit" dans les paramètres
2. Installer Selenium via le bouton "Installer"
3. Configurer ChromeDriver
4. Scanner le QR code WhatsApp Web

---

### **2. APIs Publiques Gratuites**

**Services disponibles :**

- **WhatsApp API Free** : https://whatsapp-api.free.beeceptor.com
- **Free WhatsApp API** : https://api.whatsapp.com/send
- **WhatsApp.me API** : https://wa.me/api/send

**Avantages :**

- ✅ Gratuit
- ✅ Simple à utiliser
- ✅ Pas d'installation requise

**Inconvénients :**

- ❌ Limites de messages
- ❌ Fiabilité variable
- ❌ Peut être interrompu
- ❌ Pas de support officiel

---

### **3. Solutions Hybrides Gratuites**

#### **A. WhatsApp Business App (Gratuit)**

- Utilisez l'app officielle WhatsApp Business
- Envoyez des messages manuellement
- Gratuit pour usage personnel/petit business

#### **B. Intégrations Gratuites**

- **Zapier** (1000 actions/mois gratuites)
- **IFTTT** (gratuit pour usage basique)
- **Make.com** (1000 opérations/mois gratuites)

---

## 💰 **Coûts estimés**

| Service          | Coût par message | Frais mensuels |
| ---------------- | ---------------- | -------------- |
| **Mode Gratuit** | 0€               | 0€             |
| Twilio           | ~0.0085€         | 0€             |
| MessageBird      | ~0.01€           | 0€             |
| Vonage           | ~0.009€          | 0€             |
| WhatsApp Direct  | ~0.005€          | Variables      |

---

## 🚀 **Recommandations par usage**

### **Pour débuter (0€)**

1. **WhatsApp Web + Selenium** - Parfait pour tester
2. **APIs gratuites** - Pour usage léger
3. **WhatsApp Business App** - Pour usage manuel

### **Pour usage professionnel léger**

1. **Twilio** - 15€ de crédit gratuit au démarrage
2. **MessageBird** - 25€ de crédit gratuit
3. **Vonage** - 2€ de crédit gratuit

### **Pour usage intensif**

1. **WhatsApp Business API Direct** - Meilleur prix
2. **Twilio** - Fiabilité et support
3. **MessageBird** - Interface simple

---

## ⚠️ **Limitations du Mode Gratuit**

### **WhatsApp Web + Selenium**

- Maximum 50-100 messages/heure
- Risque de blocage temporaire
- Nécessite un navigateur dédié
- Pas de garantie de livraison

### **APIs Gratuites**

- Limites strictes (10-50 messages/jour)
- Pas de support
- Peut être interrompu sans préavis
- Pas de statistiques

---

## 🔧 **Configuration du Mode Gratuit**

### **Étape 1 : Activer le mode**

1. Aller dans **SMS/WhatsApp** > **Configuration**
2. Sélectionner **"Mode Gratuit"**
3. Cliquer sur **"Installer Selenium"**

### **Étape 2 : Configuration Selenium**

```php
// Le plugin gère automatiquement :
- Installation de Selenium WebDriver
- Configuration de ChromeDriver
- Gestion des sessions WhatsApp Web
- Fallback vers APIs gratuites
```

### **Étape 3 : Test**

1. Envoyer un message test
2. Vérifier la livraison
3. Consulter les logs

---

## 📊 **Comparaison des Solutions**

| Critère          | Gratuit | Twilio     | MessageBird | Direct API |
| ---------------- | ------- | ---------- | ----------- | ---------- |
| **Coût**         | 0€      | ~0.0085€   | ~0.01€      | ~0.005€    |
| **Fiabilité**    | ⭐⭐    | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐    | ⭐⭐⭐⭐⭐ |
| **Support**      | ❌      | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐    | ⭐⭐⭐     |
| **Facilité**     | ⭐⭐    | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐  | ⭐⭐       |
| **Limites**      | Oui     | Non        | Non         | Non        |
| **Statistiques** | Non     | Oui        | Oui         | Oui        |

---

## 🎯 **Recommandation Finale**

### **Pour commencer :**

1. **Testez le mode gratuit** pendant 1-2 semaines
2. **Évaluez vos besoins** (volume, fiabilité)
3. **Passez à Twilio** si besoin de fiabilité

### **Pour usage professionnel :**

1. **Twilio** - Meilleur rapport qualité/prix
2. **MessageBird** - Interface plus simple
3. **Direct API** - Prix les plus bas

---

_Dernière mise à jour : Juin 2024_
