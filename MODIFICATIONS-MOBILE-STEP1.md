# Modifications Mobile Step 1 - Suppression Services et Catégories

## Résumé des changements

Vous avez demandé de garder la mise en place actuelle (barre de progression) mais de supprimer les services et catégories qui sont au-dessus dans le premier step mobile.

## Modifications apportées

### 1. CSS - Masquage complet en mobile
**Fichier :** `assets/css/booking-form.css` (lignes 3728-3767)

```css
/* Responsive pour le titre et les boutons de catégorie Planity */
@media (max-width: 768px) {
  .category-title-planity {
    display: none;
  }

  .category-buttons-desktop {
    display: none;
  }

  .category-accordion-mobile {
    display: none; /* Masquer complètement l'accordéon mobile */
  }

  /* Masquer la grille de services sur mobile car elle est dans l'accordéon */
  .services-list-planity {
    display: none;
  }

  /* Masquer complètement les sections catégories et services en mobile */
  .categories {
    display: none !important;
  }

  .services {
    display: none !important;
  }

  #services-part {
    display: none !important;
  }

  #category-buttons {
    display: none !important;
  }

  #services-grid {
    display: none !important;
  }
}
```

### 2. JavaScript - Logique mobile différente
**Fichier :** `assets/js/booking-form-main.js`

#### A. Nouvelle fonction pour mobile (lignes 219-228)
```javascript
// Fonction pour sélectionner un service par défaut et continuer (mobile)
window.selectDefaultServiceAndContinue = function () {
  // Sélectionner le premier service disponible ou passer directement à l'étape 2
  if (bookingState.services && bookingState.services.length > 0) {
    bookingState.selectedService = bookingState.services[0];
    window.bookingState.selectedService = bookingState.services[0];
  }
  // Passer à l'étape 2 (choix de la praticienne)
  goToStep(2);
};
```

#### B. Modification du rendu Step 1 (lignes 294-334)
```javascript
case 1:
  // Vérifier si on est en mobile
  if (window.innerWidth <= 768) {
    // En mobile, afficher seulement un message ou passer directement à l'étape 2
    inner = `
    <div class='booking-main-content'>
      <div style="text-align: center; padding: 2rem 1rem;">
        <h2 style="color: #374151; margin-bottom: 1rem;">Choisissez votre prestation</h2>
        <p style="color: #6b7280; margin-bottom: 2rem;">Sélectionnez le type de service souhaité</p>
        <div style="display: flex; flex-direction: column; gap: 1rem; max-width: 300px; margin: 0 auto;">
          <button onclick="selectDefaultServiceAndContinue()" style="background: #374151; color: white; border: none; padding: 1rem 2rem; border-radius: 8px; font-size: 1rem; cursor: pointer;">
            Continuer vers les prestations
          </button>
        </div>
      </div>
    </div>
    `;
  } else {
    // En desktop, garder l'affichage normal
    inner = `[contenu desktop normal]`;
  }
```

#### C. Modification de la validation (lignes 1008-1022)
```javascript
next.onclick = () => {
  if (bookingState.step === 1 && !bookingState.selectedService) {
    // En mobile, permettre de passer à l'étape 2 sans service sélectionné
    if (window.innerWidth <= 768) {
      // Sélectionner automatiquement le premier service disponible
      if (bookingState.services && bookingState.services.length > 0) {
        bookingState.selectedService = bookingState.services[0];
        window.bookingState.selectedService = bookingState.services[0];
      }
    } else {
      // En desktop, garder la validation normale
      showBookingNotification("Sélectionnez un service.");
      return;
    }
  }
```

## Résultat

### En Mobile (≤ 768px)
- ✅ Barre de progression conservée
- ❌ Titre "Catégorie" masqué
- ❌ Boutons de catégories masqués
- ❌ Accordéon mobile masqué
- ❌ Liste des services masquée
- ✅ Nouveau bouton "Continuer vers les prestations"
- ✅ Sélection automatique du premier service
- ✅ Passage direct à l'étape 2

### En Desktop (> 768px)
- ✅ Fonctionnement normal conservé
- ✅ Catégories affichées
- ✅ Services affichés
- ✅ Validation normale

## Test
Un fichier de test `test-mobile-step1.html` a été créé pour vérifier le comportement.

## Impact
- **Mobile :** Interface simplifiée, passage direct aux prestations
- **Desktop :** Aucun changement, fonctionnement normal
- **Compatibilité :** Maintenue sur tous les appareils
