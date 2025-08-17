// ===== FONCTIONS GLOBALES POUR LA BARRE DE PROGRESSION =====
// Initialisation immédiate des variables globales
window.bookingState = window.bookingState || {
  step: 1,
  selectedService: null,
  selectedEmployee: null,
  selectedDate: null,
  selectedSlot: null,
  client: {
    firstname: "",
    lastname: "",
    email: "",
    phone: "",
  },
};

// Fonction pour gérer le scroll et la navigation entre les étapes
window.goToStep = function (step) {
  const progressBar = document.querySelector(".planity-progress-bar") || document.querySelector(".ib-stepper-main");
  const content = document.getElementById("booking-step-content");

  // Fonction pour effectuer le scroll automatique vers la barre de progression
  function scrollToProgressBar() {
    if (!progressBar) return;

    const isMobile = window.innerWidth <= 768;

    // Calculer l'offset en tenant compte de la navigation
    let navigationHeight = 0;
    const adminBar = document.getElementById("wpadminbar");
    if (adminBar && adminBar.offsetHeight > 0) {
      navigationHeight += adminBar.offsetHeight;
    }

    // Détecter un header fixe
    const possibleHeaders = [
      'header[class*="fixed"]',
      '.header-fixed',
      '.fixed-header',
      '.sticky-header',
      'nav[class*="fixed"]'
    ];

    for (const selector of possibleHeaders) {
      const header = document.querySelector(selector);
      if (header && window.getComputedStyle(header).position === 'fixed') {
        navigationHeight += header.offsetHeight;
        break;
      }
    }

    // Offset supplémentaire pour l'espacement
    const extraOffset = isMobile ? 10 : 20;
    const totalOffset = navigationHeight + extraOffset;

    // Calculer la position de la barre de progression
    const progressBarRect = progressBar.getBoundingClientRect();
    const progressBarTop = window.pageYOffset + progressBarRect.top;
    const targetPosition = Math.max(0, progressBarTop - totalOffset);

    // Scroll fluide vers la barre de progression
    window.scrollTo({
      top: targetPosition,
      behavior: "smooth",
    });

    console.log(`📍 Scroll vers étape ${step} - Navigation: ${navigationHeight}px, Target: ${targetPosition}px (${isMobile ? 'mobile' : 'desktop'})`);

    // Sur desktop, vérifier que le contenu est visible après le scroll
    if (!isMobile && content) {
      setTimeout(() => {
        const contentRect = content.getBoundingClientRect();
        const progressBarRect = progressBar.getBoundingClientRect();

        // Si le contenu est caché derrière la barre de progression, ajuster
        if (contentRect.top < progressBarRect.bottom + 20) {
          const additionalScroll = (progressBarRect.bottom + 30) - contentRect.top;
          window.scrollBy({
            top: additionalScroll,
            behavior: "smooth"
          });
          console.log(`📍 Ajustement scroll contenu: +${additionalScroll}px`);
        }
      }, 500); // Attendre que le premier scroll soit terminé
    }
  }

  // Exécuter le scroll automatique
  scrollToProgressBar();

  // Mettre à jour le titre de l'étape si nécessaire
  const stepTitles = {
    1: "Choisissez votre prestation",
    2: "Choisissez votre praticienne",
    3: "Date & Heure",
    4: "Vos informations",
    5: "Confirmation",
  };

  // Ajouter une classe pour l'étape actuelle au body pour le CSS
  document.body.className = document.body.className.replace(/step-\d+/g, "");
  document.body.classList.add(`step-${step}`);

  // Animation de la progress bar (mobile ET desktop)
  const progressBarContainer = document.querySelector(".planity-progress-bar");
  const progressBarElement = document.querySelector(".ib-stepper-progress");

  if (progressBarContainer) {
    // Animation du conteneur
    progressBarContainer.classList.add("step-changing");
    setTimeout(() => {
      progressBarContainer.classList.remove("step-changing");
    }, 300);
  }

  if (progressBarElement) {
    progressBarElement.style.transition = "all 0.3s ease";
    progressBarElement.style.boxShadow = "0 2px 8px rgba(31, 41, 55, 0.3)";
    setTimeout(() => {
      progressBarElement.style.boxShadow = "none";
    }, 1000);
  }

  console.log(`📍 Navigation vers étape ${step} terminée`);
};

// Fonction pour ajouter les événements click sur les cercles de progression
window.initProgressBarNavigation = function () {
  const steps = document.querySelectorAll(".ib-stepper-main .ib-step");

  steps.forEach((step, index) => {
    const stepNumber = index + 1;
    const circle = step.querySelector(".ib-step-circle");

    if (circle) {
      // Supprimer les anciens événements
      circle.removeEventListener("click", circle._clickHandler);

      // Créer le gestionnaire d'événement
      circle._clickHandler = function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Permettre seulement de revenir en arrière ou rester sur l'étape actuelle
        if (stepNumber <= window.bookingState.step) {
          console.log(`Navigation vers l'étape ${stepNumber}`);

          // Utiliser goToStep si disponible, sinon setStep
          if (typeof goToStep === "function") {
            goToStep(stepNumber);
          } else {
            window.setStep(stepNumber);
          }
        }
      };

      // Ajouter l'événement
      circle.addEventListener("click", circle._clickHandler);

      // Ajouter un style de curseur pour indiquer la cliquabilité
      if (stepNumber <= window.bookingState.step) {
        circle.style.cursor = "pointer";
        step.style.cursor = "pointer";
      } else {
        circle.style.cursor = "default";
        step.style.cursor = "default";
      }
    }
  });
};

// Fonction pour changer d'étape (GLOBALE)
window.setStep = function (step) {
  if (!window.bookingState) return;

  const stepNumber = parseInt(step);
  if (isNaN(stepNumber) || stepNumber < 1 || stepNumber > 5) return;

  window.bookingState.step = stepNumber;

  // Synchroniser avec le bookingState local si il existe
  if (typeof bookingState !== "undefined") {
    bookingState.step = stepNumber;
  }

  window.updateProgressBar();

  // Sauvegarder dans localStorage
  try {
    localStorage.setItem("bookingState", JSON.stringify(window.bookingState));
  } catch (e) {
    console.warn("Could not save to localStorage:", e);
  }
};

// Initialisation immédiate
try {
  const savedState = localStorage.getItem("bookingState");
  if (savedState) {
    Object.assign(window.bookingState, JSON.parse(savedState));
  }
} catch (e) {
  console.warn("Could not load saved state:", e);
}

// Fonction pour ajuster la position de la barre de progression sous la navigation
window.adjustProgressBarPosition = function() {
  const progressBar = document.querySelector(".planity-progress-bar");
  if (!progressBar) return;

  let topOffset = 0;

  // Détecter l'admin bar WordPress
  const adminBar = document.getElementById("wpadminbar");
  if (adminBar && adminBar.offsetHeight > 0) {
    topOffset += adminBar.offsetHeight;
  }

  // Détecter un header fixe du thème
  const possibleHeaders = [
    'header[class*="fixed"]',
    '.header-fixed',
    '.fixed-header',
    '.sticky-header',
    'nav[class*="fixed"]',
    '.navbar-fixed-top',
    '.site-header.fixed'
  ];

  for (const selector of possibleHeaders) {
    const header = document.querySelector(selector);
    if (header && window.getComputedStyle(header).position === 'fixed') {
      topOffset += header.offsetHeight;
      document.body.classList.add('has-fixed-header');
      break;
    }
  }

  // Appliquer l'offset
  progressBar.style.top = topOffset + 'px';

  console.log(`📍 Position barre de progression ajustée: ${topOffset}px`);
};

// Initialiser dès que possible
document.addEventListener("DOMContentLoaded", function () {
  window.updateProgressBar();
  window.adjustProgressBarPosition();
});

setTimeout(() => {
  window.updateProgressBar();
  window.adjustProgressBarPosition();
}, 500);

// Réajuster lors du redimensionnement
window.addEventListener('resize', window.adjustProgressBarPosition);

// Fonction utilitaire pour le scroll automatique vers la barre de progression
window.scrollToProgressBar = function(callback, delay = 300) {
  const progressBar = document.querySelector(".planity-progress-bar") || document.querySelector(".ib-stepper-main");
  if (progressBar) {
    const isMobile = window.innerWidth <= 768;

    // Calculer l'offset en tenant compte de la navigation
    let navigationHeight = 0;
    const adminBar = document.getElementById("wpadminbar");
    if (adminBar && adminBar.offsetHeight > 0) {
      navigationHeight += adminBar.offsetHeight;
    }

    // Détecter un header fixe
    const possibleHeaders = [
      'header[class*="fixed"]',
      '.header-fixed',
      '.fixed-header',
      '.sticky-header',
      'nav[class*="fixed"]'
    ];

    for (const selector of possibleHeaders) {
      const header = document.querySelector(selector);
      if (header && window.getComputedStyle(header).position === 'fixed') {
        navigationHeight += header.offsetHeight;
        break;
      }
    }

    // Offset supplémentaire pour l'espacement
    const extraOffset = isMobile ? 10 : 20;
    const totalOffset = navigationHeight + extraOffset;

    // Calculer la position de la barre de progression
    const progressBarRect = progressBar.getBoundingClientRect();
    const progressBarTop = window.pageYOffset + progressBarRect.top;
    const targetPosition = Math.max(0, progressBarTop - totalOffset);

    // Scroll fluide vers la barre de progression
    window.scrollTo({
      top: targetPosition,
      behavior: "smooth",
    });

    console.log(`📍 Scroll automatique - Navigation: ${navigationHeight}px, Target: ${targetPosition}px`);

    // Exécuter le callback après le délai
    if (callback && typeof callback === 'function') {
      setTimeout(callback, delay);
    }
  } else {
    // Fallback si pas de barre de progression trouvée
    if (callback && typeof callback === 'function') {
      callback();
    }
  }
};

// ===== FIN FONCTIONS GLOBALES =====

// Attendre que jQuery soit disponible avant d'initialiser
(function () {
  function initBookingWhenReady() {
    if (typeof jQuery === "undefined") {
      console.log("⏳ Attente de jQuery...");
      setTimeout(initBookingWhenReady, 100);
      return;
    }

    console.log("✅ jQuery disponible, initialisation du booking...");
    console.log("typeof jQuery:", typeof jQuery);
    console.log("typeof jQuery.ajax:", typeof jQuery.ajax);

    // Initialiser le booking maintenant que jQuery est disponible
    initBooking();
  }

  function initBooking() {
    var bookingState = {
      step: 1,
      selectedCategory: "ALL",
      selectedService: null,
      selectedEmployee: null,
      selectedDate: null,
      selectedSlot: null,
      services: window.bookingServices || [],
      employees: window.bookingEmployees || [],
      client: {
        firstname: "",
        lastname: "",
        email: "",
        phone: "",
      },
    };

    // Fonction pour mettre à jour l'état
    function updateBookingState() {
      // Sauvegarde l'état dans le localStorage pour la persistance
      localStorage.setItem("bookingState", JSON.stringify(bookingState));

      // Mettre à jour la barre de progression
      if (typeof window.updateProgressBar === "function") {
        window.updateProgressBar();
      }
    }

    // Récupération de l'état sauvegardé si il existe
    localStorage.removeItem("bookingState"); // Reset du localStorage à chaque chargement
    const savedState = localStorage.getItem("bookingState");
    if (savedState) {
      Object.assign(bookingState, JSON.parse(savedState));
    }

    // Fonction pour naviguer entre les étapes (GLOBALE)
    window.goToStep = function goToStep(step) {
      // Synchroniser les deux bookingState
      bookingState.step = step;
      window.bookingState.step = step;

      // Reset complet si retour à l'étape 1
      if (step === 1) {
        // Sauvegarder le numéro de téléphone actuel
        const currentPhone = bookingState.client?.phone || '';
        
        // Réinitialiser l'état
        bookingState.selectedService = null;
        bookingState.selectedEmployee = null;
        bookingState.selectedDate = null;
        bookingState.selectedSlot = null;
        bookingState.client = {
          firstname: "",
          lastname: "",
          email: "",
          phone: currentPhone, // Conserver le numéro de téléphone
        };

        // Synchroniser avec le global
        window.bookingState.selectedService = null;
        window.bookingState.selectedEmployee = null;
        window.bookingState.selectedDate = null;
        window.bookingState.selectedSlot = null;

        localStorage.removeItem("bookingState");
        
        // Réinitialiser le sélecteur de pays si disponible
        if (window.simpleCountrySelector) {
          setTimeout(() => {
            if (currentPhone) {
              window.simpleCountrySelector.setPhoneNumber(currentPhone);
            } else {
              window.simpleCountrySelector.setPhoneNumber("");
            }
          }, 100);
        }
      }
      updateBookingState();
      renderStepContent();
      renderActions();
      renderSidebar();

      // Mettre à jour la barre de progression globale
      window.updateProgressBar();

      // Déclencher un événement pour notifier le changement d'étape
      document.dispatchEvent(
        new CustomEvent("stepChanged", { detail: { step: step } })
      );

      // --- Scroll automatique unifié pour desktop et mobile ---
      setTimeout(() => {
        const progressBar = document.querySelector(".planity-progress-bar") || document.querySelector(".ib-stepper-main");
        const content = document.getElementById("booking-step-content");
        const isMobile = window.innerWidth <= 700;

        if (progressBar) {
          const offset = isMobile ? 10 : 20;
          const progressBarRect = progressBar.getBoundingClientRect();
          const progressBarTop = window.pageYOffset + progressBarRect.top;
          const targetPosition = Math.max(0, progressBarTop - offset);

          // Scroll vers la barre de progression
          window.scrollTo({
            top: targetPosition,
            behavior: "smooth",
          });

          console.log(`📍 Scroll étape ${step} - ${isMobile ? 'Mobile' : 'Desktop'}: ${targetPosition}px`);

          // Sur desktop, vérifier que le contenu reste visible
          if (!isMobile && content) {
            setTimeout(() => {
              const contentRect = content.getBoundingClientRect();
              const progressBarRect = progressBar.getBoundingClientRect();

              if (contentRect.top < progressBarRect.bottom + 20) {
                const additionalScroll = (progressBarRect.bottom + 30) - contentRect.top;
                window.scrollBy({
                  top: additionalScroll,
                  behavior: "smooth"
                });
              }
            }, 500);
          }
        } else if (content) {
          // Fallback si pas de barre de progression trouvée
          content.scrollIntoView({
            behavior: "smooth",
            block: "start",
            inline: "nearest",
          });
        }
      }, 100);

      // --- Synchronise le stepper mobile ---
      if (window.innerWidth <= 700) {
        updateMobileStepper(bookingState.step, 5);
        // Ajouter un feedback tactile pour les interactions
        addMobileTouchFeedback();
      }
    };

    // Fonction pour rendre le contenu de l'étape actuelle
    function renderStepContent() {
      const content = document.getElementById("booking-step-content");
      console.log(
        "renderStepContent appelé, content existe?",
        !!content,
        "step:",
        bookingState.step
      );
      if (!content) return;
      content.innerHTML = "";
      let inner = "";
      switch (bookingState.step) {
        case 1:
          inner = `
        <div class='booking-main-content'>
          <div class="categories">
            <h2 class="category-title-planity">Catégorie</h2>
            <div class="booking-categories" id="category-buttons"></div>
          </div>
          <div class="services" id="services-part">
            <h2>Choisissez votre prestation</h2>
            <div class="services-list-planity" id="services-grid"></div>
          </div>
        </div>
      `;
          content.innerHTML = inner;
          renderCategoryButtons();
          renderServicesGrid();
          break;
        case 2:
          inner = `<div class='booking-main-content'><h2 class='text-center mb-6'>Choisissez votre praticienne</h2><div class="grid" id="employees-grid"></div></div>`;
          content.innerHTML = inner;
          renderEmployeesGrid();
          break;
        case 3:
          inner = `<div class='booking-main-content'>
        <div class="booking-step-date-modern">
          <div class="calendar-col">
            <div class="calendar-inner-card">
              <h2 class="text-2xl font-bold text-pink-400 mb-4 text-center">Date & Heure</h2>
              <div id="calendar-header" class="mb-2"></div>
              <div id="calendar-days"></div>
            </div>
          </div>
          <div class="slots-col">
            <h3>Créneaux disponibles</h3>
            <div id="slots-list"></div>
          </div>
        </div>
      </div>`;
          content.innerHTML = inner;
          renderModernCalendar();
          renderModernSlotsList();
          break;
        case 4:
          inner = `<div class='booking-main-content'>
        <div class="booking-step-infos-modern bg-white rounded-2xl shadow-xl p-8 max-w-lg mx-auto">
          <h2 class="text-2xl font-bold text-pink-400 mb-6 text-center">Vos informations</h2>
          <form id="booking-client-form">
            <div class="input-group-modern">
              <label for="client-firstname" class="booking-label-modern" style="display:flex;align-items:center;gap:0.5em;margin-bottom:0.3em;font-size:1em;">
                <span style="display:inline-block;width:1.2em;height:1.2em;vertical-align:middle;">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="#7B6F5B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="6.5" r="3.5"/><path d="M3 17c0-2.5 3.5-4 7-4s7 1.5 7 4"/></svg>
                </span> Prénom
              </label>
              <input id="client-firstname" class="booking-input-modern" type="text" placeholder="Votre prénom" required value="${
                bookingState.client.firstname || ""
              }" />
            </div>
            <div class="input-group-modern">
              <label for="client-lastname" class="booking-label-modern" style="display:flex;align-items:center;gap:0.5em;margin-bottom:0.3em;font-size:1em;">
                <span style="display:inline-block;width:1.2em;height:1.2em;vertical-align:middle;">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="#7B6F5B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="6.5" r="3.5"/><path d="M3 17c0-2.5 3.5-4 7-4s7 1.5 7 4"/></svg>
                </span> Nom
              </label>
              <input id="client-lastname" class="booking-input-modern" type="text" placeholder="Votre nom" required value="${
                bookingState.client.lastname || ""
              }" />
            </div>
            <div class="input-group-modern">
              <label for="client-email" class="booking-label-modern" style="display:flex;align-items:center;gap:0.5em;margin-bottom:0.3em;font-size:1em;">
                <span style="display:inline-block;width:1.2em;height:1.2em;vertical-align:middle;">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="#7B6F5B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="14" height="10" rx="2"/><path d="M3 5l7 6l7-6"/></svg>
                </span> Email <span style="color:#dc2626">*</span>
              </label>
              <input id="client-email" class="booking-input-modern" type="email" placeholder="Votre email" required value="${
                bookingState.client.email || ""
              }" />
            </div>
            <div id="phone-field-container" class="phone-field-with-country" style="margin-bottom:0.5em;">
              <label for="client-phone" style="color:#606060 !important ;font-size:1.04em;margin-bottom:0.4em;display:block;">Téléphone</label>
              <div id="simple-country-selector-container"></div>
              <input id="client-phone" type="hidden" value="${
                bookingState.client.phone || ""
              }"/>
              <div id="phone-error" class="error-message" style="color: #dc2626; font-size: 0.85em; margin-top: 0.5em; display: none; padding: 5px; background-color: #fef2f2; border-radius: 4px;">
                <span style="display: flex; align-items: center; gap: 5px;">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>
                  </svg>
                  Le numéro doit contenir au moins 9 chiffres
                </span>
              </div>
            </div>
            <!-- Case à cocher RGPD avec meilleure expérience mobile -->
            <div class="ib-legal-checkbox">
              <input id="client-privacy" type="checkbox" required />
              <label for="client-privacy">
                J'ai lu et j'accepte la
                <a href="https://linstitutbykm.com/privacy-policy/" target="_blank" rel="noopener">
                  politique de confidentialité
                </a>
                et les
                <a href="https://linstitutbykm.com/refund_returns" target="_blank" rel="noopener">
                  conditions générales
                </a>.
              </label>
            </div>
            <div class="flex justify-center mt-4">
              <button type="submit" class="btn-modern">Valider la réservation</button>
            </div>
          </form>
        </div>
      </div>`;
          content.innerHTML = inner;
          setTimeout(() => {
            const form = document.getElementById("booking-client-form");
            if (form) {
              // Déclencher la création du sélecteur Planity
              if (window.forceCreatePlanityPhoneSelector) {
                window.forceCreatePlanityPhoneSelector();
              }

              // Déclencher un événement pour notifier que le formulaire est rendu
              document.dispatchEvent(new CustomEvent("formRendered"));

              // Initialiser le sélecteur de pays simple
              setTimeout(() => {
                console.log(
                  "🔍 [DEBUG] Appel initSimpleCountrySelector dans setTimeout"
                );
                console.log(
                  "🔍 [DEBUG] SimpleCountrySelector disponible:",
                  typeof SimpleCountrySelector
                );
                console.log(
                  "🔍 [DEBUG] Container disponible:",
                  !!document.querySelector("#simple-country-selector-container")
                );
                initSimpleCountrySelector();
              }, 500); // Augmenté de 100ms à 500ms

              // Champ téléphone simple - pas besoin d'initialisation complexe
              // Modal Conditions Générales
              if (!document.getElementById("terms-modal")) {
                const modal = document.createElement("div");
                modal.id = "terms-modal";
                modal.style =
                  "display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);align-items:center;justify-content:center;";
                modal.innerHTML = `<div style='background:#fff;max-width:480px;width:90vw;padding:2em 1.5em;border-radius:1.2em;box-shadow:0 8px 32px #606060;position:relative;'>
              <button id='close-terms-modal' style='position:absolute;top:0.7em;right:1em;font-size:1.5em;background:none;border:none;cursor:pointer;'>&times;</button>
              <h3 style='color:#606060;font-size:1.2em;margin-bottom:1em;'>✅ Conditions Générales de Réservation</h3>
              <div style='font-size:0.97em;line-height:1.6;color:#555;text-align:left;max-height:60vh;overflow-y:auto;'>
                En validant votre rendez-vous, vous acceptez les conditions suivantes :<br><br>
                Vos informations personnelles sont utilisées uniquement pour organiser et confirmer votre réservation.<br><br>
                Vous pouvez modifier ou annuler votre rendez-vous à tout moment en nous contactant directement.<br><br>
                Toute utilisation de ce service implique le respect de nos modalités de réservation.<br>
              </div>
            </div>`;
                document.body.appendChild(modal);
                const showTermsBtn = document.getElementById("show-terms");
                if (showTermsBtn) {
                  showTermsBtn.onclick = function (e) {
                    e.preventDefault();
                    modal.style.display = "flex";
                  };
                }
                const closeTermsModalBtn =
                  document.getElementById("close-terms-modal");
                if (closeTermsModalBtn) {
                  closeTermsModalBtn.onclick = function () {
                    modal.style.display = "none";
                  };
                }
              }
              // Modal Politique de Confidentialité
              if (!document.getElementById("privacy-modal")) {
                const modal = document.createElement("div");
                modal.id = "privacy-modal";
                modal.style =
                  "display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);align-items:center;justify-content:center;";
                modal.innerHTML = `<div style='background:#fff;max-width:480px;width:90vw;padding:2em 1.5em;border-radius:1.2em;box-shadow:0 8px 32px #e9aebc55;position:relative;'>
              <button id='close-privacy-modal' style='position:absolute;top:0.7em;right:1em;font-size:1.5em;background:none;border:none;cursor:pointer;'>&times;</button>
              <h3 style='606060 !important;font-size:1.2em;margin-bottom:1em;'>🔐 Politique de Confidentialité</h3>
              <div style='font-size:0.97em;line-height:1.6;color:#555;text-align:left;max-height:60vh;overflow-y:auto;'>
                Dans le respect de la législation en vigueur, nous nous engageons à protéger vos données personnelles :<br><br>
                Les données que vous fournissez (nom, prénom, téléphone, email) sont traitées de manière sécurisée, dans le seul objectif de gérer votre rendez-vous.<br><br>
                Elles ne seront jamais partagées, vendues ni utilisées à des fins commerciales sans votre consentement explicite.<br><br>
                Vous disposez à tout moment d'un droit d'accès, de rectification et de suppression de vos données, sur simple demande.<br>
              </div>
            </div>`;
                document.body.appendChild(modal);
                const showPrivacyBtn = document.getElementById("show-privacy");
                if (showPrivacyBtn) {
                  showPrivacyBtn.onclick = function (e) {
                    e.preventDefault();
                    modal.style.display = "flex";
                  };
                }
                const closePrivacyModalBtn = document.getElementById(
                  "close-privacy-modal"
                );
                if (closePrivacyModalBtn) {
                  closePrivacyModalBtn.onclick = function () {
                    modal.style.display = "none";
                  };
                }
              }
              // Le sélecteur Planity se charge automatiquement via planity-phone-selector.js
              // Appliquer la validation moderne
              setupModernValidation(form);
            }
          }, 100);
          break;
        case 5:
          let prixHtml = "-";
          if (bookingState.selectedService) {
            if (bookingState.selectedService.variable_price == 1) {
              const min = Number(bookingState.selectedService.min_price);
              const max = Number(bookingState.selectedService.max_price);
              if (min > 0 && max > 0 && min !== max) {
                prixHtml = `de ${min.toLocaleString()} DA à ${max.toLocaleString()} DA`;
              } else if (min > 0) {
                prixHtml = `à partir de ${min.toLocaleString()} DA`;
              } else {
                prixHtml = "-";
              }
            } else if (
              typeof bookingState.selectedService.price !== "undefined"
            ) {
              prixHtml =
                Number(bookingState.selectedService.price).toLocaleString() +
                " DA";
            }
          }
          inner = `<div class="booking-ticket-modern">
          <div class="ticket-success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 6L9 17l-5-5"/>
            </svg>
          </div>
          <div class="ticket-success-badge">Réservation confirmée</div>
          <div class="ticket-success-message">Merci pour votre réservation !<br>Un email de confirmation vous a été envoyé.</div>
          <div class="ticket-details">
            <div>
              <span class="ticket-label">Service</span>
              <span class="ticket-value">${
                bookingState.selectedService?.name || "-"
              }</span>
            </div>
            <div>
              <span class="ticket-label">Praticienne</span>
              <span class="ticket-value">${
                bookingState.selectedEmployee?.name || "-"
              }</span>
            </div>
            <div>
              <span class="ticket-label">Date</span>
              <span class="ticket-value">${
                bookingState.selectedDate ? 
                new Date(bookingState.selectedDate).toLocaleDateString('fr-FR', {
                  day: '2-digit',
                  month: '2-digit', 
                  year: 'numeric'
                }) : "-"
              }</span>
            </div>
            <div>
              <span class="ticket-label">Créneau</span>
              <span class="ticket-value">${
                bookingState.selectedSlot || "-"
              }</span>
            </div>
            <div>
              <span class="ticket-label">Client</span>
              <span class="ticket-value">${
                bookingState.client?.firstname || "-"
              } ${bookingState.client?.lastname || "-"}</span>
            </div>
            <div>
              <span class="ticket-label">Email</span>
              <span class="ticket-value">${
                bookingState.client?.email || "-"
              }</span>
            </div>
            <div>
              <span class="ticket-label">Téléphone</span>
              <span class="ticket-value">${
                bookingState.client?.phone || "-"
              }</span>
            </div>
            <div>
              <span class="ticket-label">Prix</span>
              <span class="ticket-value">${prixHtml}</span>
            </div>
          </div>
          <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
            <button id="download-ticket-btn" type="button" style="background: #111827; color: #ffffff; border: none; border-radius: 8px; padding: 12px 24px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;" onmouseover="this.style.background='#374151'" onmouseout="this.style.background='#111827'">Télécharger le ticket</button>
        </div>
      </div>`;
          content.innerHTML = inner;
          setTimeout(() => {
            const btn = document.getElementById("download-ticket-btn");
            if (btn) {
              btn.onclick = () => {
                const ticket = document.querySelector(".booking-ticket-modern");
                if (!ticket) {
                  showBookingNotification("Ticket non trouvé");
                  return;
                }

                // Utiliser directement la méthode corrigée intégrée
                generateTicketPDFFixed(ticket, btn);
              };

              // Fonction corrigée pour générer le PDF sans pages vides
              function generateTicketPDFFixed(ticket, btn) {
                console.log("🎫 [Fix] Début génération PDF...");

                // Fonction pour effectuer la génération
                function doGenerate() {
                  if (!window.html2pdf) {
                    console.error("❌ html2pdf non disponible");
                    showBookingNotification(
                      "Erreur: Générateur PDF non disponible"
                    );
                    if (btn) btn.style.display = "block";
                    return;
                  }

                  // Masquer le bouton avant export
                  if (btn) btn.style.display = "none";

                  try {
                    // Utiliser directement les données du bookingState pour plus de fiabilité
                    const getServiceName = () => {
                      return bookingState.selectedService?.name || "-";
                    };

                    const getEmployeeName = () => {
                      return bookingState.selectedEmployee?.name || "-";
                    };

                    const getDate = () => {
                      if (!bookingState.selectedDate) return "-";
                      const date = new Date(bookingState.selectedDate);
                      return date.toLocaleDateString("fr-FR", {
                        weekday: "long",
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                      });
                    };

                    const getSlot = () => {
                      return bookingState.selectedSlot || "-";
                    };

                    const getClientName = () => {
                      const firstname = bookingState.client?.firstname || "";
                      const lastname = bookingState.client?.lastname || "";
                      return `${firstname} ${lastname}`.trim() || "-";
                    };

                    const getEmail = () => {
                      return bookingState.client?.email || "-";
                    };

                    const getPhone = () => {
                      return bookingState.client?.phone || "-";
                    };

                    const getPrice = () => {
                      if (!bookingState.selectedService) return "-";

                      if (bookingState.selectedService.variable_price == 1) {
                        const min = Number(
                          bookingState.selectedService.min_price
                        );
                        const max = Number(
                          bookingState.selectedService.max_price
                        );
                        if (min > 0 && max > 0 && min !== max) {
                          return `de ${min.toLocaleString()} DA à ${max.toLocaleString()} DA`;
                        } else if (min > 0) {
                          return `à partir de ${min.toLocaleString()} DA`;
                        } else {
                          return "-";
                        }
                      } else if (
                        typeof bookingState.selectedService.price !==
                        "undefined"
                      ) {
                        return (
                          Number(
                            bookingState.selectedService.price
                          ).toLocaleString() + " DA"
                        );
                      }
                      return "-";
                    };

                    // Créer un conteneur temporaire avec contenu simplifié
                    const tempContainer = document.createElement("div");

                    // Configuration compacte pour une seule page
                    const containerWidth = 600; // Largeur fixe plus petite
                    const containerPadding = 15; // Padding réduit
                    const fontSize = 12; // Taille de police réduite
                    const iconSize = 30; // Icône plus petite
                    const titleFontSize = 14; // Titre plus petit

                    tempContainer.style.cssText = `
                      position: fixed;
                      left: 0;
                      top: 0;
                      width: ${containerWidth}px;
                      height: auto;
                      background: white;
                      padding: ${containerPadding}px;
                      font-family: Arial, sans-serif;
                      color: black;
                      box-sizing: border-box;
                      z-index: -9999;
                      visibility: hidden;
                    `;

                    // Créer le contenu HTML compact pour une seule page
                    tempContainer.innerHTML = `
                      <div style="width: 100%; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: ${containerPadding}px; font-family: Arial, sans-serif; color: #000000; max-height: 800px; overflow: hidden;">
                        <div style="text-align: center; margin-bottom: 10px;">
                          <div style="width: ${iconSize}px; height: ${iconSize}px; background: #374151; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: white; font-size: ${
                      iconSize * 0.4
                    }px; font-weight: bold;">✓</div>
                        </div>
                        <div style="background: #374151; color: #ffffff; padding: 8px 15px; border-radius: 6px; font-weight: 600; text-align: center; margin: 10px 0; font-size: ${titleFontSize}px;">Réservation confirmée</div>
                        <div style="text-align: center; color: #374151; margin: 10px 0; font-size: ${fontSize}px; line-height: 1.3;">Merci pour votre réservation !<br>Un email de confirmation vous a été envoyé.</div>
                        <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; margin: 15px 0;">
                          <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;"><span style="font-weight: 600; color: #374151; font-size: ${fontSize}px;">Service</span><span style="color: #111827; font-size: ${fontSize}px;">${getServiceName()}</span></div>
                          <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;"><span style="font-weight: 600; color: #374151; font-size: ${fontSize}px;">Praticienne</span><span style="color: #111827; font-size: ${fontSize}px;">${getEmployeeName()}</span></div>
                          <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;"><span style="font-weight: 600; color: #374151; font-size: ${fontSize}px;">Date</span><span style="color: #111827; font-size: ${fontSize}px;">${getDate()}</span></div>
                          <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;"><span style="font-weight: 600; color: #374151; font-size: ${fontSize}px;">Créneau</span><span style="color: #111827; font-size: ${fontSize}px;">${getSlot()}</span></div>
                          <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;"><span style="font-weight: 600; color: #374151; font-size: ${fontSize}px;">Client</span><span style="color: #111827; font-size: ${fontSize}px;">${getClientName()}</span></div>
                          <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;"><span style="font-weight: 600; color: #374151; font-size: ${fontSize}px;">Email</span><span style="color: #111827; font-size: ${fontSize}px;">${getEmail()}</span></div>
                          <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #e5e7eb;"><span style="font-weight: 600; color: #374151; font-size: ${fontSize}px;">Téléphone</span><span style="color: #111827; font-size: ${fontSize}px;">${getPhone()}</span></div>
                          <div style="display: flex; justify-content: space-between; padding: 5px 0;"><span style="font-weight: 600; color: #374151; font-size: ${fontSize}px;">Prix</span><span style="color: #111827; font-weight: 600; font-size: ${fontSize}px;">${getPrice()}</span></div>
                        </div>
                        <div style="text-align: center; color: #6b7280; font-size: 10px; margin-top: 15px; padding-top: 10px; border-top: 1px solid #e5e7eb;">Ticket généré le ${new Date().toLocaleDateString(
                          "fr-FR"
                        )} à ${new Date().toLocaleTimeString("fr-FR")}</div>
                      </div>
                    `;

                    // Ajouter au DOM
                    document.body.appendChild(tempContainer);

                    // Forcer le rendu et attendre un peu
                    tempContainer.offsetHeight;
                    tempContainer.style.visibility = "visible";
                    tempContainer.style.position = "fixed";
                    tempContainer.style.left = "0";
                    tempContainer.style.top = "0";
                    tempContainer.style.zIndex = "-9999";

                    console.log(
                      "🎫 [Fix] Contenu créé:",
                      tempContainer.innerHTML.substring(0, 200)
                    );
                    console.log(
                      "🎫 [Fix] Dimensions:",
                      tempContainer.offsetWidth,
                      "x",
                      tempContainer.offsetHeight
                    );

                    // Attendre un peu pour s'assurer que le contenu est bien rendu
                    setTimeout(() => {
                      try {
                        // Configuration optimisée pour une seule page compacte
                        html2canvas(tempContainer, {
                          scale: 1.5, // Échelle réduite pour un PDF plus petit
                          backgroundColor: "#ffffff",
                          logging: false,
                          useCORS: true,
                          allowTaint: true,
                          width: containerWidth,
                          height: tempContainer.offsetHeight,
                          scrollX: 0,
                          scrollY: 0,
                          windowWidth: containerWidth,
                          windowHeight: tempContainer.offsetHeight,
                        })
                          .then((canvas) => {
                            console.log(
                              "🎫 [Fix] Canvas généré:",
                              canvas.width,
                              "x",
                              canvas.height
                            );

                            const imgData = canvas.toDataURL("image/png");
                            console.log("🎫 [Fix] Image data générée");

                            // Configuration PDF compacte pour une seule page
                            const pdf = new jsPDF("p", "mm", "a4");
                            const imgWidth = 180; // Largeur réduite pour laisser des marges
                            const pageHeight = 297; // A4 height in mm
                            const imgHeight =
                              (canvas.height * imgWidth) / canvas.width;

                            // Centrer l'image sur la page
                            const xOffset = (210 - imgWidth) / 2; // Centrer horizontalement
                            const yOffset = (pageHeight - imgHeight) / 2; // Centrer verticalement

                            // Vérifier si le contenu tient sur une seule page
                            if (imgHeight <= pageHeight) {
                              // Une seule page
                              pdf.addImage(
                                imgData,
                                "PNG",
                                xOffset,
                                yOffset,
                                imgWidth,
                                imgHeight
                              );
                            } else {
                              // Si le contenu est trop grand, le redimensionner pour tenir sur une page
                              const scale = pageHeight / imgHeight;
                              const scaledWidth = imgWidth * scale;
                              const scaledHeight = imgHeight * scale;
                              const scaledXOffset = (210 - scaledWidth) / 2;

                              pdf.addImage(
                                imgData,
                                "PNG",
                                scaledXOffset,
                                10, // Marge supérieure
                                scaledWidth,
                                scaledHeight
                              );
                            }

                            pdf.save(
                              `ticket-reservation-${
                                new Date().toISOString().split("T")[0]
                              }.pdf`
                            );

                            console.log(
                              "🎫 [Fix] PDF compact généré avec succès"
                            );
                            if (document.body.contains(tempContainer)) {
                              document.body.removeChild(tempContainer);
                            }
                            if (btn) btn.style.display = "block";
                            showBookingNotification(
                              "Ticket téléchargé avec succès !"
                            );
                          })
                          .catch((error) => {
                            console.error("❌ [Fix] Erreur canvas:", error);
                            if (document.body.contains(tempContainer)) {
                              document.body.removeChild(tempContainer);
                            }
                            if (btn) btn.style.display = "block";
                            showBookingNotification(
                              "Erreur lors de la génération du PDF: " +
                                error.message
                            );
                          });
                      } catch (error) {
                        console.error("❌ [Fix] Erreur générale:", error);
                        if (document.body.contains(tempContainer)) {
                          document.body.removeChild(tempContainer);
                        }
                        if (btn) btn.style.display = "block";
                        showBookingNotification(
                          "Erreur lors de la génération du PDF: " +
                            error.message
                        );
                      }
                    }, 500);
                  } catch (error) {
                    console.error("❌ [Fix] Erreur générale:", error);
                    if (btn) btn.style.display = "block";
                    showBookingNotification(
                      "Erreur lors de la génération du PDF: " + error.message
                    );
                  }
                }

                // Charger jsPDF et html2canvas si nécessaire
                if (!window.jsPDF || !window.html2canvas) {
                  console.log("🎫 [Fix] Chargement jsPDF et html2canvas...");
                  loadPDFLibraries(() => {
                    console.log("🎫 [Fix] Bibliothèques chargées");
                    doGenerate();
                  });
                } else {
                  doGenerate();
                }
              }

              // Fonction pour charger jsPDF et html2canvas
              function loadPDFLibraries(callback) {
                let loaded = 0;
                const total = 2;

                function checkLoaded() {
                  loaded++;
                  if (loaded === total) {
                    callback();
                  }
                }

                // Charger jsPDF
                if (!window.jsPDF) {
                  const jsPDFScript = document.createElement("script");
                  jsPDFScript.src =
                    "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js";
                  jsPDFScript.onload = () => {
                    window.jsPDF = window.jspdf.jsPDF;
                    console.log("🎫 [Fix] jsPDF chargé");
                    checkLoaded();
                  };
                  jsPDFScript.onerror = () => {
                    console.error("❌ [Fix] Erreur chargement jsPDF");
                    showBookingNotification(
                      "Erreur lors du chargement de jsPDF"
                    );
                    if (btn) btn.style.display = "block";
                  };
                  document.head.appendChild(jsPDFScript);
                } else {
                  checkLoaded();
                }

                // Charger html2canvas
                if (!window.html2canvas) {
                  const html2canvasScript = document.createElement("script");
                  html2canvasScript.src =
                    "https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js";
                  html2canvasScript.onload = () => {
                    console.log("🎫 [Fix] html2canvas chargé");
                    checkLoaded();
                  };
                  html2canvasScript.onerror = () => {
                    console.error("❌ [Fix] Erreur chargement html2canvas");
                    showBookingNotification(
                      "Erreur lors du chargement de html2canvas"
                    );
                    if (btn) btn.style.display = "block";
                  };
                  document.head.appendChild(html2canvasScript);
                } else {
                  checkLoaded();
                }
              }
            }
          }, 100);
          break;
      }
    }

    // Fonction pour rendre les actions (boutons)
    function renderActions() {
      const actions = document.getElementById("booking-actions");
      actions.innerHTML = "";
      actions.className = "actions";

      // Affiche le bouton retour uniquement si ce n'est pas l'étape 5
      if (bookingState.step > 1 && bookingState.step < 5) {
        const back = document.createElement("button");
        back.className = "back btn-back";
        back.textContent = "← Précédent";
        back.setAttribute("data-action", "back");
        back.onclick = () => {
          // Utiliser la fonction utilitaire pour le scroll automatique
          window.scrollToProgressBar(() => {
            goToStep(bookingState.step - 1);
          }, 200);
        };
        actions.appendChild(back);
      }

      if (bookingState.step < 5) {
        const next = document.createElement("button");
        next.className = "next btn-next";
        next.setAttribute("data-action", "next");

        // Texte du bouton selon l'étape
        const buttonTexts = {
          1: "Choisir la praticienne →",
          2: "Choisir la date →",
          3: "Mes informations →",
          4: "Confirmer la réservation",
        };

        next.innerHTML = buttonTexts[bookingState.step] || "Suivant →";
        actions.appendChild(next);
        next.onclick = () => {
          if (bookingState.step === 1 && !bookingState.selectedService) {
            showBookingNotification("Sélectionnez un service.");
            return;
          }
          if (bookingState.step === 2 && !bookingState.selectedEmployee) {
            showBookingNotification("Sélectionnez une praticienne.");
            return;
          }
          if (
            bookingState.step === 3 &&
            (!bookingState.selectedDate || !bookingState.selectedSlot)
          ) {
            showBookingNotification("Sélectionnez une date et un créneau.");
            return;
          }
          if (
            bookingState.step === 4 &&
            (!bookingState.client.firstname ||
              !bookingState.client.lastname ||
              !bookingState.client.phone)
          ) {
            showBookingNotification("Merci de remplir tous les champs.");
            return;
          }

          // Utiliser la fonction utilitaire pour le scroll automatique
          window.scrollToProgressBar(() => {
            goToStep(bookingState.step + 1);
          }, 200);
        };
        actions.appendChild(next);
      } else if (bookingState.step === 5) {
        const restart = document.createElement("button");
        restart.className = "next btn-next";
        restart.textContent = "Nouvelle réservation";
        restart.setAttribute("data-action", "restart");
        restart.onclick = () => {
          // Réinitialiser l'état de réservation dans le localStorage
          localStorage.removeItem("bookingState");
          
          // Rafraîchir la page pour réinitialiser complètement le formulaire
          window.location.reload();
        };
        actions.appendChild(restart);
      }
    }

    // Fonction pour gérer la sidebar sticky manuellement
    function initStickysidebar() {
      const sidebar = document.querySelector(".sidebar");
      const container = document.querySelector(".container");

      if (!sidebar || !container) return;

      // Vérifier si on est sur desktop
      if (window.innerWidth <= 900) return;

      const sidebarOriginalTop = sidebar.offsetTop;
      let isSticky = false;

      function handleScroll() {
        const scrollTop =
          window.pageYOffset || document.documentElement.scrollTop;
        const containerRect = container.getBoundingClientRect();
        const containerBottom = containerRect.bottom;

        if (scrollTop >= sidebarOriginalTop - 32 && containerBottom > 100) {
          if (!isSticky) {
            sidebar.style.position = "fixed";
            sidebar.style.top = "2rem";
            sidebar.style.zIndex = "100";
            sidebar.style.width = "200px";
            isSticky = true;
          }
        } else {
          if (isSticky) {
            sidebar.style.position = "static";
            sidebar.style.top = "auto";
            sidebar.style.width = "auto";
            isSticky = false;
          }
        }
      }

      window.addEventListener("scroll", handleScroll);
      window.addEventListener("resize", function () {
        if (window.innerWidth <= 900 && isSticky) {
          sidebar.style.position = "static";
          sidebar.style.top = "auto";
          sidebar.style.width = "auto";
          isSticky = false;
        }
      });
    }

    // Fonction pour rendre la grille de services
    document.addEventListener("DOMContentLoaded", function () {
      console.log(
        "DOMContentLoaded: booking-step-content existe?",
        !!document.getElementById("booking-step-content")
      );
      console.log("Services:", bookingState.services);
      console.log("Praticiennes:", bookingState.employees);
      renderSidebar();
      renderStepContent();
      renderActions();

      // Initialiser la sidebar sticky après un court délai
      setTimeout(initStickysidebar, 100);

      if (window.innerWidth <= 700) {
        updateMobileStepper(bookingState.step, 5);
        // Initialiser les améliorations mobile
        setTimeout(() => {
          addMobileTouchFeedback();
          enhanceMobileNavigation();
        }, 200);
      }
    });

    function renderSidebar() {
      const sidebarSteps = document.getElementById("sidebar-steps");
      if (!sidebarSteps) return;
      sidebarSteps.querySelectorAll("li").forEach((li, idx) => {
        // Active l'étape courante
        li.classList.toggle("active", idx === bookingState.step - 1);
        // Les étapes précédentes sont cliquables
        if (idx < bookingState.step - 1) {
          li.classList.remove("disabled");
          li.style.pointerEvents = "auto";
          li.style.opacity = "1";
          li.style.cursor = "pointer";
          li.onclick = () => goToStep(idx + 1);
        } else if (idx === bookingState.step - 1) {
          // Étape courante : surbrillance, non cliquable
          li.classList.remove("disabled");
          li.style.pointerEvents = "none";
          li.style.opacity = "1";
          li.style.cursor = "default";
          li.onclick = null;
        } else {
          // Étapes futures : grisées, non cliquables
          li.classList.add("disabled");
          li.style.pointerEvents = "none";
          li.style.opacity = "0.6";
          li.style.cursor = "not-allowed";
          li.onclick = null;
        }
      });
    }

    function renderCategoryButtons() {
      const container = document.getElementById("category-buttons");
      container.innerHTML = "";

      // Utilise la bonne propriété pour les catégories
      const cats = [
        "ALL",
        ...Array.from(
          new Set(
            bookingState.services.map((s) => s.category_name).filter(Boolean)
          )
        ),
      ];
      console.log("Catégories générées:", cats);

      // Créer les boutons pour desktop
      const buttonsContainer = document.createElement("div");
      buttonsContainer.className = "category-buttons-desktop";

      cats.forEach((cat) => {
        const btn = document.createElement("button");
        btn.textContent = cat;
        btn.title = cat;
        btn.className =
          "booking-category-btn" +
          (cat === bookingState.selectedCategory ? " active" : "");
        btn.onclick = () => {
          bookingState.selectedCategory = cat;
          renderServicesGrid();
          renderCategoryButtons();
          const servicesSection = document.getElementById("services-part");
          if (servicesSection) {
            servicesSection.scrollIntoView({ behavior: "smooth" });
          }
        };
        buttonsContainer.appendChild(btn);
      });

      // Créer l'accordéon pour mobile
      const accordionContainer = document.createElement("div");
      accordionContainer.className = "category-accordion-mobile";

      const accordionTitle = document.createElement("h3");
      accordionTitle.textContent = "Choix de la prestation";
      accordionTitle.className = "category-accordion-title";
      accordionContainer.appendChild(accordionTitle);

      // Grouper les services par catégorie pour l'accordéon
      const servicesByCategory = {};
      bookingState.services.forEach((service) => {
        const category = service.category_name || "Autres";
        if (!servicesByCategory[category]) {
          servicesByCategory[category] = [];
        }
        servicesByCategory[category].push(service);
      });

      // Créer un accordéon pour chaque catégorie
      Object.keys(servicesByCategory).forEach((categoryName) => {
        const categoryServices = servicesByCategory[categoryName];

        const accordionItem = document.createElement("div");
        accordionItem.className = "accordion-item";

        const accordionHeader = document.createElement("div");
        accordionHeader.className = "accordion-header";
        accordionHeader.innerHTML = `
          <span class="accordion-category-name">${categoryName}</span>
          <span class="accordion-arrow">▼</span>
        `;

        const accordionContent = document.createElement("div");
        accordionContent.className = "accordion-content";

        // Ajouter les services de cette catégorie
        categoryServices.forEach((service) => {
          const serviceItem = document.createElement("div");
          serviceItem.className = "accordion-service-item";

          // Formater le prix comme dans Planity
          let formattedPrice = "Prix sur demande";

          if (service.variable_price == 1) {
            // Prix variable avec min/max
            const min = Number(service.min_price);
            const max = Number(service.max_price);
            if (min > 0 && max > 0 && min !== max) {
              formattedPrice = `de ${min.toLocaleString()} DA à ${max.toLocaleString()} DA`;
            } else if (min > 0) {
              formattedPrice = `à partir de ${min.toLocaleString()} DA`;
            } else {
              formattedPrice = "Prix sur demande";
            }
          } else if (service.price && parseFloat(service.price) > 0) {
            // Prix fixe
            formattedPrice = `${Number(service.price).toLocaleString()} DA`;
          }

          // Formater la durée avec unités appropriées
          const formattedDuration = formatDuration(service.duration || 30);

          serviceItem.innerHTML = `
            <div class="service-content">
              <div class="service-main-info">
                <h5 class="service-name">${service.name}</h5>
                <p class="service-price">${formattedPrice}</p>
              </div>
              <div class="service-meta">
                <span class="service-duration">${formattedDuration}</span>
                <button class="service-choose-btn" data-service-id="${service.id}">Choisir</button>
              </div>
            </div>
          `;

          // Ajouter l'élément au DOM d'abord
          accordionContent.appendChild(serviceItem);

          // Puis ajouter l'événement de clic pour choisir le service
          const chooseBtn = serviceItem.querySelector(".service-choose-btn");
          if (chooseBtn) {
            chooseBtn.addEventListener("click", (e) => {
              e.preventDefault();
              e.stopPropagation();
              console.log("Service sélectionné:", service);
              bookingState.selectedService = service;
              bookingState.step = 2;

              // Utiliser la fonction utilitaire pour le scroll automatique
              window.scrollToProgressBar(() => {
                // Utiliser goToStep au lieu de renderBookingForm
                if (typeof goToStep === "function") {
                  goToStep(2);
                } else {
                  console.log(
                    "goToStep non disponible, tentative de navigation manuelle"
                  );
                  // Alternative : déclencher un événement personnalisé
                  const event = new CustomEvent("serviceSelected", {
                    detail: { service: service, step: 2 },
                  });
                  document.dispatchEvent(event);
                }
              });
            });
          } else {
            console.error(
              "Bouton Choisir non trouvé pour le service:",
              service.name
            );
          }
        });

        // Gestion du clic sur l'en-tête
        accordionHeader.onclick = () => {
          const isOpen = accordionItem.classList.contains("open");
          
          // Toggle l'état de l'accordéon cliqué
          if (isOpen) {
            accordionItem.classList.remove("open");
            accordionHeader.querySelector(".accordion-arrow").textContent = "▼";
          } else {
            accordionItem.classList.add("open");
            accordionHeader.querySelector(".accordion-arrow").textContent = "▲";
            
            // Faire défiler jusqu'au contenu de l'accordéon
            setTimeout(() => {
              const content = accordionItem.querySelector('.accordion-content');
              if (content) {
                content.scrollIntoView({ 
                  behavior: 'smooth',
                  block: 'nearest',
                  inline: 'start'
                });
              }
            }, 50);
          }
        };

        accordionItem.appendChild(accordionHeader);
        accordionItem.appendChild(accordionContent);
        accordionContainer.appendChild(accordionItem);
      });

      // Ajouter les deux versions au container
      container.appendChild(buttonsContainer);
      container.appendChild(accordionContainer);
    }

    function renderServicesGrid() {
      const grid = document.getElementById("services-grid");
      if (!grid) return;
      grid.innerHTML = "";
      console.log("Valeur de selectedCategory:", bookingState.selectedCategory);
      console.log(
        "Exemple de category_name:",
        bookingState.services[0]?.category_name
      );
      let filtered =
        bookingState.selectedCategory === "ALL"
          ? bookingState.services
          : bookingState.services.filter(
              (s) =>
                (s.category_name || "").trim() ===
                (bookingState.selectedCategory || "").trim()
            );
      console.log("Services à afficher:", filtered);
      if (filtered.length === 0) {
        grid.innerHTML =
          "<div style='padding:2em;text-align:center;color:#A48D78;'>Aucun service disponible</div>";
        return;
      }

      // Grouper les services par catégorie
      const servicesByCategory = {};
      filtered.forEach((service) => {
        const categoryName = service.category_name || "Sans catégorie";
        if (!servicesByCategory[categoryName]) {
          servicesByCategory[categoryName] = [];
        }
        servicesByCategory[categoryName].push(service);
      });

      // Afficher chaque catégorie avec ses services
      Object.keys(servicesByCategory).forEach((categoryName) => {
        const services = servicesByCategory[categoryName];
        const maxServicesShown = 5; // Limite d'affichage par catégorie
        const servicesToShow = services.slice(0, maxServicesShown);
        const remainingServices = services.length - maxServicesShown;

        // Créer l'en-tête de catégorie
        const categoryHeader = document.createElement("div");
        categoryHeader.className = "category-header-planity";
        categoryHeader.innerHTML = `<h3>${categoryName.toUpperCase()}</h3>`;
        grid.appendChild(categoryHeader);

        // Créer le conteneur pour les services de cette catégorie
        const categoryContainer = document.createElement("div");
        categoryContainer.className = "category-services-container";
        categoryContainer.setAttribute("data-category", categoryName);

        // Ajouter les services visibles de cette catégorie
        servicesToShow.forEach((srv) => {
          const serviceItem = createServiceItem(srv);
          categoryContainer.appendChild(serviceItem);
        });

        // Ajouter le lien "Voir plus" si nécessaire
        if (remainingServices > 0) {
          const seeMoreItem = document.createElement("div");
          seeMoreItem.className = "see-more-services-planity";
          seeMoreItem.innerHTML = `
            <div class="see-more-content">
              <span class="see-more-text">Voir les ${remainingServices} autres prestations</span>
              <span class="see-more-arrow">→</span>
            </div>
          `;

          // Gérer le clic pour afficher tous les services
          seeMoreItem.onclick = () => {
            // Masquer le lien "voir plus"
            seeMoreItem.style.display = "none";

            // Ajouter les services restants
            services.slice(maxServicesShown).forEach((srv) => {
              const serviceItem = createServiceItem(srv);
              categoryContainer.insertBefore(serviceItem, seeMoreItem);
            });

            // Ajouter un lien "Voir moins" optionnel
            const seeLessItem = document.createElement("div");
            seeLessItem.className = "see-less-services-planity";
            seeLessItem.innerHTML = `
              <div class="see-more-content">
                <span class="see-more-text">Voir moins de prestations</span>
                <span class="see-more-arrow">↑</span>
              </div>
            `;

            seeLessItem.onclick = () => {
              // Supprimer les services supplémentaires
              const allServiceItems = categoryContainer.querySelectorAll(
                ".service-item-planity"
              );
              for (let i = maxServicesShown; i < allServiceItems.length; i++) {
                allServiceItems[i].remove();
              }

              // Supprimer le lien "voir moins" et réafficher "voir plus"
              seeLessItem.remove();
              seeMoreItem.style.display = "flex";
            };

            categoryContainer.appendChild(seeLessItem);
          };

          categoryContainer.appendChild(seeMoreItem);
        }

        grid.appendChild(categoryContainer);
      });
    }

    function createServiceItem(srv) {
      const serviceItem = document.createElement("div");
      serviceItem.className =
        "service-item-planity" +
        (bookingState.selectedService &&
        bookingState.selectedService.id === srv.id
          ? " selected"
          : "");

      // Correction affichage prix
      let priceText = "";
      if (srv.variable_price == 1) {
        const min = Number(srv.min_price);
        const max = Number(srv.max_price);
        if (min > 0 && max > 0 && min !== max) {
          priceText = `de ${min.toLocaleString()} DA à ${max.toLocaleString()} DA`;
        } else if (min > 0) {
          priceText = `à partir de ${min.toLocaleString()} DA`;
        } else {
          priceText = "Variable";
        }
      } else if (typeof srv.price === "number" && !isNaN(srv.price)) {
        priceText = srv.price.toLocaleString() + " DA";
      } else if (typeof srv.price === "string" && srv.price.trim() !== "") {
        priceText = srv.price + " DA";
      } else {
        priceText = "Variable";
      }

      // Créer la description du service (nom en majuscules + détails)
      const serviceName = srv.name.toUpperCase();
      const serviceDescription = srv.description || "Service professionnel";

      serviceItem.innerHTML = `
        <div class="service-info-planity">
          <h3 class="service-name-planity">${serviceName}</h3>
          <p class="service-description-planity">${serviceDescription}</p>
          <p class="service-price-planity">${priceText}</p>
        </div>
        <div class="service-meta-planity">
          <span class="service-duration-planity">${formatDuration(
            srv.duration || 30
          )}</span>
          <button class="service-choose-btn" type="button">Choisir</button>
        </div>
      `;

      // Gérer le clic sur tout l'élément ou juste le bouton
      const chooseBtn = serviceItem.querySelector(".service-choose-btn");
      const selectService = () => {
        bookingState.selectedService = srv;
        console.log("Service sélectionné:", srv);

        // Utiliser la fonction utilitaire pour le scroll automatique
        window.scrollToProgressBar(() => {
          goToStep(2);
        });
      };

      serviceItem.onclick = selectService;
      chooseBtn.onclick = (e) => {
        e.stopPropagation();
        selectService();
      };

      return serviceItem;
    }

    function renderEmployeesGrid() {
      const grid = document.getElementById("employees-grid");
      grid.innerHTML = "";
      if (!bookingState.selectedService) return;
      const employeeIds = (bookingState.selectedService.employee_ids || []).map(
        Number
      );
      const filtered = bookingState.employees.filter((e) =>
        employeeIds.includes(Number(e.id))
      );
      if (filtered.length === 0) {
        grid.innerHTML =
          '<div style="padding:2em;text-align:center;color:#bfa2c7;">Aucune praticienne pour ce service</div>';
        return;
      }
      filtered.forEach((emp) => {
        const card = document.createElement("div");
        card.className =
          "employee-card-modern" +
          (bookingState.selectedEmployee &&
          bookingState.selectedEmployee.id === emp.id
            ? " selected"
            : "");
        card.onclick = () => {
          bookingState.selectedEmployee = emp;
          renderEmployeesGrid();
          goToStep(3); // Passe automatiquement à l'étape suivante après sélection
        };
        let imgHtml = emp.photo
          ? `<img src="${emp.photo}" alt="${emp.name}" style="width:72px;height:72px;border-radius:50%;object-fit:cover;">`
          : `<span><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 8-4 8-4s8 0 8 4"/></svg></span>`;
        card.innerHTML = `
      ${imgHtml}
      <div class="mt-3 text-center">
        <div class="font-bold text-brown-400 text-base mb-1">${emp.name}</div>
       
      </div>
    `;
        grid.appendChild(card);
      });
    }

    // Ajoute cette fonction pour charger les jours disponibles dynamiquement
    function loadAvailableDays(year, month, cb) {
      console.log("🔍 loadAvailableDays appelée:", {
        year: year,
        month: month,
        selectedService: bookingState.selectedService,
        selectedEmployee: bookingState.selectedEmployee,
      });

      if (!bookingState.selectedService || !bookingState.selectedEmployee) {
        console.log("❌ Préstation ou employé non sélectionné");
        window.availableDays = {};
        if (cb) cb();
        return;
      }

      // Check if jQuery is available
      if (typeof jQuery === "undefined") {
        console.warn("❌ jQuery not available for AJAX call");
        window.availableDays = {};
        if (cb) cb();
        return;
      }

      // Vérifier les variables nécessaires
      if (!window.ajaxurl) {
        console.error("❌ window.ajaxurl non défini");
        window.availableDays = {};
        if (cb) cb();
        return;
      }

      if (!window.ib_nonce) {
        console.error("❌ window.ib_nonce non défini");
        window.availableDays = {};
        if (cb) cb();
        return;
      }

      console.log("🔄 Appel AJAX get_available_days...");

      jQuery.ajax({
        url: window.ajaxurl,
        type: "POST",
        data: {
          action: "get_available_days",
          employee_id: bookingState.selectedEmployee.id,
          service_id: bookingState.selectedService.id,
          year: year,
          month: month + 1, // JS: 0-11, PHP: 1-12
          nonce: window.ib_nonce,
        },
        success: function (response) {
          console.log("✅ Réponse get_available_days:", response);

          if (response.success && response.data) {
            window.availableDays = response.data;
            console.log("✅ Jours disponibles:", window.availableDays);
          } else {
            console.log("❌ Réponse invalide:", response);
            window.availableDays = {};
          }
          if (cb) cb();
        },
        error: function (xhr, status, error) {
          console.error("❌ Erreur AJAX get_available_days:", {
            status: status,
            error: error,
            responseText: xhr.responseText,
          });
          window.availableDays = {};
          if (cb) cb();
        },
      });
    }
    // Modifie renderModernCalendar pour charger les jours avant d'afficher le calendrier
    function renderModernCalendar() {
      const cal = document.getElementById("calendar-days");
      const header = document.getElementById("calendar-header");
      const monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ];
      const weekDays = ["L", "M", "M", "J", "V", "S", "D"];
      if (!window.calendarState)
        window.calendarState = {
          month: new Date().getMonth(),
          year: new Date().getFullYear(),
        };
      // Charge les jours disponibles avant d'afficher le calendrier
      loadAvailableDays(
        window.calendarState.year,
        window.calendarState.month,
        () => {
          header.innerHTML = `
    <button id='prev-month'>&lt;</button>
    <span style='font-weight:600;font-size:1.1em;display:inline-block;min-width:120px;text-align:center;'>${monthNames[
      window.calendarState.month
    ].toUpperCase()} ${window.calendarState.year}</span>
    <button id='next-month'>&gt;</button>
  `;
          const prevMonthBtn = document.getElementById("prev-month");
          if (prevMonthBtn) {
            prevMonthBtn.onclick = () => {
              window.calendarState.month--;
              if (window.calendarState.month < 0) {
                window.calendarState.month = 11;
                window.calendarState.year--;
              }
              renderModernCalendar();
              document.getElementById("slots-list").innerHTML =
                '<div class="no-slots">Sélectionnez une date</div>';
            };
          }
          const nextMonthBtn = document.getElementById("next-month");
          if (nextMonthBtn) {
            nextMonthBtn.onclick = () => {
              window.calendarState.month++;
              if (window.calendarState.month > 11) {
                window.calendarState.month = 0;
                window.calendarState.year++;
              }
              renderModernCalendar();
              document.getElementById("slots-list").innerHTML =
                '<div class="no-slots">Sélectionnez une date</div>';
            };
          }
          const year = window.calendarState.year;
          const month = window.calendarState.month;
          const daysInMonth = new Date(year, month + 1, 0).getDate();
          let firstDay = new Date(year, month, 1).getDay();
          firstDay = firstDay === 0 ? 6 : firstDay - 1;
          let html = `<div class='calendar-weekdays'>`;
          weekDays.forEach((d) => (html += `<div>${d}</div>`));
          html += '</div><div class="calendar-grid">';
          for (let i = 0; i < firstDay; i++) html += "<div></div>";
          for (let d = 1; d <= daysInMonth; d++) {
            const dateObj = new Date(year, month, d);
            const dateStr = `${year}-${String(month + 1).padStart(
              2,
              "0"
            )}-${String(d).padStart(2, "0")}`;
            const isPast =
              dateObj <
              new Date(
                new Date().getFullYear(),
                new Date().getMonth(),
                new Date().getDate()
              );
            const isSunday = dateObj.getDay() === 0;
            let hasSlot = false;
            if (window.availableDays && window.availableDays[dateStr])
              hasSlot = true;
            let btnClass = "calendly-day";
            if (!hasSlot || isPast || isSunday) btnClass += " disabled";
            if (bookingState.selectedDate === dateStr) btnClass += " selected";
            html += `<button class='${btnClass}' data-date='${dateStr}' ${
              !hasSlot || isPast || isSunday ? "disabled" : ""
            }>${d}</button>`;
          }
          html += "</div>";
          cal.innerHTML = html;
          document.querySelectorAll(".calendly-day").forEach((btn) => {
            if (btn.disabled) return;
            btn.onclick = () => {
              bookingState.selectedDate = btn.getAttribute("data-date");
              bookingState.selectedSlot = null;
              renderModernCalendar();
              renderModernSlotsList();

              // Smooth scroll to show the slots without revealing the footer
              setTimeout(() => {
                const slotsSection = document.getElementById("slots-list");
                if (slotsSection) {
                  // Calculate the position to scroll to (current scroll position + slots section position - some offset)
                  const headerOffset = 120; // Adjust this value based on your header height
                  const elementPosition = slotsSection.getBoundingClientRect().top;
                  const offsetPosition = window.pageYOffset + elementPosition - headerOffset;
                  
                  // Smooth scroll to the calculated position
                  window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                  });
                }
              }, 200);
            };
          });
          document.querySelectorAll(".calendly-day").forEach((btn) => {
            if (bookingState.selectedDate === btn.getAttribute("data-date")) {
              btn.classList.add("selected");
            } else {
              btn.classList.remove("selected");
            }
          });
          if (!bookingState.selectedDate) {
            document.getElementById("slots-list").innerHTML =
              '<div class="no-slots">Sélectionnez une date</div>';
          }
        }
      );
    }

    function renderModernSlotsList() {
      const slotsList = document.getElementById("slots-list");
      // Afficher un message si aucune date sélectionnée
      if (!bookingState.selectedDate) {
        slotsList.innerHTML =
          '<div class="no-slots">Sélectionnez une date</div>';
        return;
      }
      if (!bookingState.selectedEmployee || !bookingState.selectedService) {
        slotsList.innerHTML =
          '<div class="no-slots">Veuillez sélectionner un service et une praticienne</div>';
        return;
      }
      console.log("Déclenchement AJAX get_available_slots", bookingState); // DEBUG
      let html = "";
      jQuery.ajax({
        url: window.ajaxurl,
        type: "POST",
        data: {
          action: "get_available_slots",
          employee_id: bookingState.selectedEmployee.id,
          service_id: bookingState.selectedService.id,
          date: bookingState.selectedDate,
          nonce: window.ib_nonce,
        },
        success: function (response) {
          if (response.success && response.data) {
            html = "";
            // Si data est un tableau simple (array), on affiche tous les créneaux à la suite
            if (Array.isArray(response.data)) {
              if (response.data.length === 0) {
                html =
                  '<div class="no-slots" style="text-align:center;padding:2em 0;color:#606060;font-size:1.1em;font-weight:500;">Aucun créneau disponible pour cette date.<br><span style="font-size:0.97em;color:#bfa2c7;">Essayez une autre date ou une autre praticienne.</span></div>';
              } else {
                html += '<div class="slots-grid-planity">';
                response.data.forEach((slot) => {
                  const isSelected = bookingState.selectedSlot === slot;

                  // Calculer l'heure de fin basée sur la durée du service
                  const serviceDuration =
                    bookingState.selectedService?.duration || 30;
                  const startTime = new Date(`2000-01-01 ${slot}:00`);
                  const endTime = new Date(
                    startTime.getTime() + serviceDuration * 60000
                  );
                  const endTimeStr = endTime.toTimeString().substring(0, 5);

                  html += `<button class='slot-btn slot-btn-planity' ${
                    isSelected ? "disabled" : ""
                  } onclick='window.selectSlot("${slot}")'>
                    <div class="slot-time-main">${slot}</div>
                    <div class="slot-time-end">→ ${endTimeStr}</div>
                  </button>`;
                });
                html += "</div>";
              }
            } else {
              // Ancien format : morning, afternoon, evening
              if (response.data.morning && response.data.morning.length) {
                html +=
                  '<div style="margin-bottom:1em;"><b>Morning</b><div style="margin-top:0.5em;display:flex;flex-wrap:wrap;gap:0.5em;">';
                response.data.morning.forEach((slot) => {
                  html += `<button class='slot-btn' style='padding:0.7em 1.2em;border-radius:18px;border:1.5px solid #f8f8f8;background:#f8f8f8;color:#606060;font-weight:600;cursor:pointer;transition:transform 0.13s;' ${
                    bookingState.selectedSlot === slot ? "disabled" : ""
                  } onclick='window.selectSlot("${slot}")'>${slot} <span style='font-size:0.9em;color:#bbb;font-weight:400;'>Disponible</span></button>`;
                });
                html += "</div></div>";
              }
              if (response.data.afternoon && response.data.afternoon.length) {
                html +=
                  '<div style="margin-bottom:1em;"><b>Afternoon</b><div style="margin-top:0.5em;display:flex;flex-wrap:wrap;gap:0.5em;">';
                response.data.afternoon.forEach((slot) => {
                  html += `<button class='slot-btn' style='padding:0.7em 1.2em;border-radius:18px;border:1.5px solid #f8f8f8;background:#f8f8f8;color:#606060;font-weight:600;cursor:pointer;transition:transform 0.13s;' ${
                    bookingState.selectedSlot === slot ? "disabled" : ""
                  } onclick='window.selectSlot("${slot}")'>${slot} <span style='font-size:0.9em;color:#bbb;font-weight:400;'>Disponible</span></button>`;
                });
                html += "</div></div>";
              }
              if (response.data.evening && response.data.evening.length) {
                html +=
                  '<div style="margin-bottom:1em;"><b>Evening</b><div style="margin-top:0.5em;display:flex;flex-wrap:wrap;gap:0.5em;">';
                response.data.evening.forEach((slot) => {
                  html += `<button class='slot-btn' style='padding:0.7em 1.2em;border-radius:18px;border:1.5px solid #f8f8f8;background:#f8f8f8;color:#606060;font-weight:600;cursor:pointer;transition:transform 0.13s;' ${
                    bookingState.selectedSlot === slot ? "disabled" : ""
                  } onclick='window.selectSlot("${slot}")'>${slot} <span style='font-size:0.9em;color:#bbb;font-weight:400;'>Disponible</span></button>`;
                });
                html += "</div></div>";
              }
            }
            slotsList.innerHTML = html;
          }
        },
        error: function (xhr, status, error) {
          console.error("Erreur AJAX get_available_slots:", status, error, xhr);
        },
      });
      window.selectSlot = function (slot) {
        bookingState.selectedSlot = slot;
        updateBookingState();

        // Scroll automatique vers la barre de progression avant de passer à l'étape suivante
        window.scrollToProgressBar(() => {
          goToStep(4); // Aller à l'étape Infos
        });
      };
    }

    function showBookingNotification(message) {
      if (document.getElementById("booking-notif-modal")) return;

      // Détecter le type de message pour adapter l'icône et le style
      const isSuccess =
        message.includes("succès") ||
        message.includes("téléchargé") ||
        message.includes("confirmé");
      const isError = message.includes("Erreur") || message.includes("erreur");

      const modal = document.createElement("div");
      modal.id = "booking-notif-modal";
      modal.style =
        "position:fixed;z-index:99999;left:0;top:0;width:100vw;height:100vh;background: rgba(0, 0, 0, 0.5);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(6px);";

      // Thème noir/gris/blanc
      const iconColor = isSuccess ? "#374151" : isError ? "#ef4444" : "#374151";
      const bgColor = "#ffffff";
      const borderColor = "#e5e7eb";
      const textColor = "#111827";
      const subtitleColor = "#6b7280";

      const iconSvg = isSuccess
        ? '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>'
        : isError
        ? '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
        : '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>';

      modal.innerHTML = `<div style='background:${bgColor};border:2px solid ${borderColor};border-radius:16px;box-shadow:0 25px 50px rgba(0,0,0,0.25);padding:2.5em 2em;max-width:380px;width:90vw;text-align:center;position:relative;transform:scale(0.9);opacity:0;transition:all 0.3s ease;'>
    <div style='margin-bottom:1.5em;'>
      <span style='display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;background:${iconColor};box-shadow:0 8px 24px ${iconColor}30;color:white;'>
        ${iconSvg}
      </span>
    </div>
    <div style='color:${subtitleColor};font-size:1.05em;line-height:1.5;'>${message}</div>
  </div>`;

      document.body.appendChild(modal);

      // Animation d'entrée
      setTimeout(() => {
        const modalContent = modal.querySelector("div");
        modalContent.style.transform = "scale(1)";
        modalContent.style.opacity = "1";
      }, 10);

      // Fermeture automatique après 3 secondes
      setTimeout(() => {
        const modalContent = modal.querySelector("div");
        modalContent.style.transform = "scale(0.9)";
        modalContent.style.opacity = "0";
        setTimeout(() => {
          if (document.getElementById("booking-notif-modal")) {
            document.getElementById("booking-notif-modal").remove();
          }
        }, 300);
      }, 3000);
    }

    // Protection navigation sidebar (renforcée)
    function setupSidebarStepProtection() {
      const sidebar = document.getElementById("sidebar-steps");
      if (!sidebar) return;
      const currentStep = bookingState.step - 1;
      sidebar.querySelectorAll("li").forEach((li, idx) => {
        // Désactive les étapes futures
        if (idx > currentStep) {
          li.classList.add("disabled");
          li.style.pointerEvents = "none";
          li.style.opacity = "0.5";
          li.style.cursor = "not-allowed";
        } else {
          li.classList.remove("disabled");
          li.style.pointerEvents = "auto";
          li.style.opacity = "1";
          li.style.cursor = "pointer";
        }
        // Navigation autorisée uniquement sur les étapes courantes ou précédentes
        li.onclick = (e) => {
          if (idx > currentStep) {
            e.preventDefault();
            return;
          }
          goToStep(idx + 1);
        };
      });
    }

    // Appeler la protection sidebar après chaque render
    setTimeout(setupSidebarStepProtection, 50);

    // --- INTL-TEL-INPUT ---
    // Dans le setTimeout et DOMContentLoaded, ne plus appeler intl-tel-input ni window.iti
    // --- SUPPRIMER toute initialisation intl-tel-input ---

    function showError(input, message) {
      // Cas spécial pour le champ téléphone
      const isPhoneInput = input.id === 'client-phone' || 
                         (input.classList && input.classList.contains('simple-phone-input'));
      
      let targetInput = input;
      let parentElement = input.parentNode;
      
      // Si c'est le champ téléphone caché, on cible le conteneur parent
      if (isPhoneInput && input.type === 'hidden') {
        const phoneContainer = document.querySelector('.phone-field-with-country');
        if (phoneContainer) {
          parentElement = phoneContainer;
          // On cible le champ de saisie visible si disponible
          const visibleInput = document.querySelector('.simple-phone-input');
          if (visibleInput) {
            targetInput = visibleInput;
          }
        }
      }
      
      let error = parentElement.querySelector(".ib-error-msg");
      if (!error) {
        error = document.createElement("span");
        error.className = "ib-error-msg";
        error.style.color = "#e05c5c";
        error.style.fontSize = "0.97em";
        error.style.display = "block";
        error.style.marginTop = "0.3em";
        error.style.fontWeight = "500";
        // Insérer après le conteneur du champ de téléphone
        parentElement.appendChild(error);
      }
      error.textContent = message;
      targetInput.classList.add("ib-error");
      
      // Ajouter une bordure rouge au conteneur du sélecteur de pays si c'est le champ téléphone
      if (isPhoneInput) {
        const selectorContainer = document.querySelector('.simple-phone-container');
        if (selectorContainer) {
          selectorContainer.style.borderColor = '#e05c5c';
        }
      }
    }
    function clearError(input) {
      // Gestion spéciale pour le champ téléphone
      const isPhoneInput = input.id === 'client-phone' || 
                         (input.classList && input.classList.contains('simple-phone-input'));
      
      let parentElement = input.parentNode;
      let targetInput = input;
      
      if (isPhoneInput) {
        const phoneContainer = document.querySelector('.phone-field-with-country');
        if (phoneContainer) {
          parentElement = phoneContainer;
          // Cibler le champ de saisie visible si disponible
          const visibleInput = document.querySelector('.simple-phone-input');
          if (visibleInput) {
            targetInput = visibleInput;
          }
        }
        // Supprimer la bordure rouge du conteneur
        const selectorContainer = document.querySelector('.simple-phone-container');
        if (selectorContainer) {
          selectorContainer.style.borderColor = '#d1d5db'; // Couleur de bordure par défaut
        }
      }
      
      let error = parentElement.querySelector(".ib-error-msg");
      if (error) error.remove();
      targetInput.classList.remove("ib-error");
    }
    function isValidName(str) {
      // Noms/prénoms : lettres, espaces, tirets, apostrophes, pas de chiffres
      return /^[A-Za-zÀ-ÖØ-öø-ÿ\s'-]{2,}$/.test(str.trim());
    }
    function isValidEmail(str) {
      // Email standard (plus large)
      return /^[^@\s]+@[^@\s]+\.[a-zA-Z]{2,}$/.test(str.trim());
    }

    // Fonction utilitaire globale pour formater les durées
    function formatDuration(durationMinutes) {
      const durationNum = parseInt(durationMinutes);
      if (durationNum >= 60) {
        const hours = Math.floor(durationNum / 60);
        const minutes = durationNum % 60;
        if (minutes === 0) {
          return `${hours}h`;
        } else {
          return `${hours}h${minutes}min`;
        }
      } else {
        return `${durationNum}min`;
      }
    }

    // Rendre la fonction disponible globalement
    window.formatDuration = formatDuration;

    // Fonction utilitaire pour nettoyer un numéro de téléphone
    function cleanPhoneNumber(str) {
      if (!str) return '';
      // Supprimer tous les caractères non numériques
      return str.replace(/[^0-9+]/g, '');
    }

    // Fonction pour valider la longueur d'un numéro selon le pays
    function validatePhoneLength(number, countryCode) {
      // Garder uniquement les chiffres pour la validation de longueur
      const cleanNumber = number.replace(/[^0-9]/g, '');
      
      // Si le numéro commence par le code pays, on l'enlève pour la validation
      let numberWithoutCountry = cleanNumber;
      if (countryCode && cleanNumber.startsWith(countryCode)) {
        numberWithoutCountry = cleanNumber.substring(countryCode.length);
      }
      
      // Validation minimale de 9 chiffres pour tous les pays
      if (numberWithoutCountry.length < 9) {
        console.log('[DEBUG] Numéro trop court:', numberWithoutCountry.length, 'chiffres (minimum 9 requis)');
        return false;
      }
      
      // Validation spécifique par pays si nécessaire
      switch(countryCode) {
        case '33': // France
          // 9 chiffres (sans le 0) ou 10 chiffres (avec le 0)
          return [9, 10].includes(numberWithoutCountry.length);
          
        case '213': // Algérie
        case '212': // Maroc
          // 9 chiffres (sans le 0) ou 10 chiffres (avec le 0)
          return [9, 10].includes(numberWithoutCountry.length);
          
        case '216': // Tunisie
          // 8 chiffres (sans le 0) ou 9 chiffres (avec le 0)
          // On garde cette règle spécifique mais on applique le minimum de 9 chiffres
          return numberWithoutCountry.length >= 9 && [8, 9].includes(numberWithoutCountry.length);
          
        default:
          // Pour les autres pays: minimum 9 chiffres, maximum 13 chiffres (sans le code pays)
          return numberWithoutCountry.length >= 9 && numberWithoutCountry.length <= 13;
      }
    }

    function isValidPhoneNumber(str) {
      if (!str || typeof str !== 'string') return false;
      
      console.log('[DEBUG] Validation du numéro:', str);
      
      // Nettoyer le numéro (supprimer tous les caractères non numériques sauf le +)
      const cleaned = cleanPhoneNumber(str);
      if (!cleaned) {
        console.log('[DEBUG] Numéro vide après nettoyage');
        return false;
      }
      
      // Récupérer le code pays
      let country = "";
      if (window.getPlanityCountryCode) {
        country = window.getPlanityCountryCode().replace("+", "");
        console.log('[DEBUG] Code pays détecté (Planity):', country);
      } else if (window.iti && window.iti.getSelectedCountryData) {
        country = window.iti.getSelectedCountryData().dialCode;
        console.log('[DEBUG] Code pays détecté (intl-tel):', country);
      } else {
        console.log('[DEBUG] Aucun code pays détecté');
      }
      
      // Si on a un code pays, on valide en conséquence
      if (country) {
        // Validation de la longueur en fonction du pays
        const isValidLength = validatePhoneLength(cleaned, country);
        console.log('[DEBUG] Longueur valide pour', country, ':', isValidLength);
        
        if (!isValidLength) {
          console.log('[DEBUG] Longueur invalide pour le pays', country, 'numéro', cleaned);
          return false;
        }
        
        // Préparer le numéro pour validation (sans code pays)
        let numberWithoutCountry = cleaned;
        
        // Supprimer le code pays s'il est présent au début
        if (cleaned.startsWith(country)) {
          numberWithoutCountry = cleaned.substring(country.length);
        } else if (cleaned.startsWith('0' + country)) {
          numberWithoutCountry = cleaned.substring(country.length + 1);
        } else if (cleaned.startsWith('00' + country)) {
          numberWithoutCountry = cleaned.substring(country.length + 2);
        } else if (cleaned.startsWith('+')) {
          // Si le numéro commence par + mais pas par le code pays, on le supprime
          numberWithoutCountry = cleaned.substring(1);
        }
        
        // Supprimer les espaces et caractères spéciaux restants
        numberWithoutCountry = numberWithoutCountry.replace(/[^0-9]/g, '');
        
        console.log('[DEBUG] Numéro sans code pays:', numberWithoutCountry);
        
        // Validation spécifique par pays
        switch(country) {
          case '33': // France
            // Format accepté : 6 ou 7 suivi de 8 chiffres (avec ou sans 0 initial)
            const frValid = /^[67]\d{8}$/.test(numberWithoutCountry) || 
                          /^0[67]\d{8}$/.test(numberWithoutCountry);
            console.log('[DEBUG] Validation France:', frValid);
            return frValid;
            
          case '213': // Algérie
          case '212': // Maroc
            // Format accepté : 5, 6 ou 7 suivi de 8 chiffres (avec ou sans 0 initial)
            const dzmaValid = /^[5-7]\d{8}$/.test(numberWithoutCountry) || 
                            /^0[5-7]\d{8}$/.test(numberWithoutCountry);
            console.log('[DEBUG] Validation Algérie/Maroc:', dzmaValid);
            return dzmaValid;
            
          case '216': // Tunisie
            // Format accepté : 8 chiffres (avec ou sans 0 initial)
            const tnValid = /^\d{8}$/.test(numberWithoutCountry) || 
                          /^0\d{8}$/.test(numberWithoutCountry);
            console.log('[DEBUG] Validation Tunisie:', tnValid);
            return tnValid;
            
          default:
            // Pour les autres pays: entre 6 et 13 chiffres
            const defaultValid = numberWithoutCountry.length >= 6 && 
                               numberWithoutCountry.length <= 13;
            console.log('[DEBUG] Validation autre pays:', defaultValid);
            return defaultValid;
        }
      }
      
      // Si pas de code pays, on fait une validation générique
      const digitsOnly = cleaned.replace(/[^0-9]/g, '');
      const genericValid = digitsOnly.length >= 6 && digitsOnly.length <= 15;
      console.log('[DEBUG] Validation générique:', genericValid);
      
      return genericValid;
    }

    // --- Validation UX moderne ---
    function setupModernValidation(form) {
      const firstnameInput = form.querySelector("#client-firstname");
      const lastnameInput = form.querySelector("#client-lastname");
      const emailInput = form.querySelector("#client-email");
      const phoneInput = form.querySelector("#client-phone");
      const submitBtn = form.querySelector('button[type="submit"]');

      // Pour suivre si le champ a été touché
      const touched = {
        firstname: false,
        lastname: false,
        email: false,
        phone: false,
      };

      function validateField(input, type) {
        // Vérifier si l'input est null ou undefined
        if (!input) {
          console.error("❌ [ERREUR] L'élément input est null ou undefined pour le type:", type);
          return false;
        }
        
        let valid = true;
        // Vérifier que input.value existe avant de l'utiliser
        let value = input && input.value ? input.value : "";
        if (type === "firstname" || type === "lastname") {
          valid = isValidName(value);
          if (!valid && touched[type]) {
            showError(
              input,
              (type === "firstname" ? "Prénom" : "Nom") +
                " invalide (lettres uniquement)"
            );
          } else {
            clearError(input);
          }
        } else if (type === "email") {
          if (value.trim() !== "") {
            valid = isValidEmail(value);
            if (!valid) {
              showError(input, "Format d'email invalide");
            } else {
              clearError(input);
            }
          } else {
            // Champ vide est accepté
            valid = true;
            clearError(input);
          }
        } else if (type === "phone") {
          // Récupérer la valeur du champ téléphone
          let phoneValue = "";

          console.log("🔍 [DEBUG] Validation téléphone - début");

          // Forcer l'initialisation si le sélecteur n'est pas prêt
          if (!window.simpleCountrySelector && window.SimpleCountrySelector) {
            console.log(
              "🔧 [Fix] Sélecteur non initialisé, initialisation forcée..."
            );
            const container = document.getElementById(
              "simple-country-selector-container"
            );
            if (container) {
              window.simpleCountrySelector = new SimpleCountrySelector(
                container,
                {
                  defaultCountry: "DZ",
                  placeholder: "Numéro de téléphone",
                }
              );
              console.log(
                "🔧 [Fix] Sélecteur créé dans validation:",
                window.simpleCountrySelector
              );
            }
          }

          // Récupérer la valeur du champ téléphone depuis le sélecteur personnalisé
          if (window.simpleCountrySelector) {
            const phoneInput =
              window.simpleCountrySelector.container.querySelector(
                ".simple-phone-input"
              );
            if (phoneInput) {
              phoneValue = phoneInput.value.trim();
              console.log("🔍 [DEBUG] phoneInput trouvé, valeur:", phoneValue);
            } else {
              console.log("🔍 [DEBUG] phoneInput non trouvé dans le sélecteur");
            }
          } else if (input) {
            phoneValue = input.value.trim();
            console.log(
              "🔍 [DEBUG] Utilisation input direct, valeur:",
              phoneValue
            );
          } else {
            console.log("🔍 [DEBUG] Aucun sélecteur ni input disponible");
          }

          // Vérifier que le téléphone n'est pas vide
          if (phoneValue === "") {
            valid = false;
            if (touched.phone) {
              // Toujours utiliser l'input caché comme référence pour le téléphone
              const phoneInput = document.getElementById('client-phone') || 
                               (window.simpleCountrySelector?.container?.querySelector(".simple-phone-input"));
             
            }
            console.log("🔍 [DEBUG] Téléphone vide - invalide");
          } else {
            // Si un numéro est saisi, le valider
            let validIntl = false;
            let validCustom = false;
            let country = "";
            let fullPhoneNumber = "";

            // Vérifier que les fonctions globales sont disponibles
            if (window.getPlanityCountryCode && window.getPlanityPhoneNumber) {
              // Sélecteur custom Planity
              country = window.getPlanityCountryCode().replace("+", "");
              fullPhoneNumber = window.getPlanityPhoneNumber();
              validIntl = fullPhoneNumber.length >= 10; // Validation basique avec code pays
              validCustom = isValidPhoneNumber(phoneValue); // Validation du champ local
              console.log("🔍 [DEBUG] Fonctions globales utilisées");
            } else if (window.iti && window.iti.isValidNumber) {
              // Fallback intl-tel-input
              validIntl = window.iti.isValidNumber();
              country = window.iti.getSelectedCountryData
                ? window.iti.getSelectedCountryData().dialCode
                : "";
              fullPhoneNumber = window.iti.getNumber();
              console.log("🔍 [DEBUG] Fallback intl-tel-input utilisé");
            } else {
              console.log("🔍 [DEBUG] Aucune méthode de validation disponible");
            }

            // Validation stricte même pendant la saisie
            if (phoneValue.length > 0) {
              // Compter uniquement les chiffres pour la validation
              const digitCount = phoneValue.replace(/\D/g, '').length;
              if (digitCount < 9) {
                console.log(
                  `⏳ [DEBUG] Numéro trop court: ${digitCount} chiffres (minimum 9 requis)`
                );
                validIntl = false;
                validCustom = false;
              } else {
                console.log(
                  `✅ [DEBUG] Numéro valide: ${digitCount} chiffres`
                );
              }
            }

            console.log("[PHONE VALIDATION]", {
              value: phoneValue,
              fullPhoneNumber,
              country,
              validIntl,
              validCustom,
              usingPlanity: !!window.getPlanityCountryCode,
              selectorExists: !!window.simpleCountrySelector,
            });

            valid = validIntl && validCustom;
            
            console.log('[DEBUG] Validation du téléphone:', {
              phoneValue,
              valid,
              validIntl,
              validCustom,
              touched: touched.phone,
              hasError: !valid && touched.phone
            });
            
            // Afficher directement un message d'erreur si la validation échoue
            if (!valid && touched.phone) {
              console.log('[DEBUG] Affichage du message d\'erreur');
              // Récupérer ou créer le conteneur d'erreur
              console.log('[DEBUG] Recherche du conteneur d\'erreur...');
              
              // Trouver le conteneur parent du champ téléphone
              const phoneFieldContainer = document.querySelector('.phone-field-with-country');
              if (!phoneFieldContainer) {
                console.error('[ERREUR] Conteneur du champ téléphone introuvable');
                return;
              }
              
              let errorContainer = phoneFieldContainer.nextElementSibling;
              
              // Vérifier si le prochain élément est déjà notre conteneur d'erreur
              if (!errorContainer || !errorContainer.classList.contains('phone-error-container')) {
                console.log('[DEBUG] Création d\'un nouveau conteneur d\'erreur');
                errorContainer = document.createElement('div');
                errorContainer.className = 'phone-error-container';
                errorContainer.style.marginTop = '10px';
                errorContainer.style.padding = '8px';
                errorContainer.style.backgroundColor = '#fef2f2';
                errorContainer.style.borderLeft = '4px solid #dc2626';
                errorContainer.style.borderRadius = '4px';
                errorContainer.style.display = 'block';
                
                // Ajouter le message d'erreur
                errorContainer.innerHTML = `
                  <div style="display: flex; align-items: center; color: #dc2626; font-size: 0.9em;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px; flex-shrink: 0;">
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="12" y1="8" x2="12" y2="12"></line>
                      <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>Numéro de téléphone invalide</span>
                  </div>
                `;
                
                // Insérer après le champ téléphone
                console.log('[DEBUG] Recherche du champ téléphone...');
                const phoneField = document.querySelector('.phone-field-with-country');
                console.log('[DEBUG] Champ téléphone trouvé:', phoneField);
                if (phoneField && phoneField.parentNode) {
                  console.log('[DEBUG] Insertion du message d\'erreur après le champ téléphone');
                  phoneField.parentNode.insertBefore(errorContainer, phoneField.nextSibling);
                }
              } else {
                // Le conteneur existe déjà, on s'assure qu'il est visible
                errorContainer.style.display = 'block';
              }
            } else {
              // Cacher le message d'erreur si la validation réussit
              console.log('[DEBUG] Validation réussie, masquage du message d\'erreur');
              const phoneFieldContainer = document.querySelector('.phone-field-with-country');
              if (phoneFieldContainer) {
                const errorContainer = phoneFieldContainer.nextElementSibling;
                if (errorContainer && errorContainer.classList.contains('phone-error-container')) {
                  console.log('[DEBUG] Masquage du conteneur d\'erreur existant');
                  errorContainer.style.display = 'none';
                }
              }
            }
          }
        }
        return valid;
      }

      function validateAll() {
        let valid = true;
        
        // Vérifier que tous les champs d'entrée sont valides avant de les utiliser
        const inputs = {
          firstname: firstnameInput,
          lastname: lastnameInput,
          email: emailInput,
          phone: phoneInput
        };
        
        // Valider chaque champ s'il existe
        const firstnameValid = inputs.firstname ? validateField(inputs.firstname, "firstname") : false;
        const lastnameValid = inputs.lastname ? validateField(inputs.lastname, "lastname") : false;
        const emailValid = inputs.email ? validateField(inputs.email, "email") : false;
        const phoneValid = inputs.phone ? validateField(inputs.phone, "phone") : false;
        
        // Journaliser si des champs sont manquants
        Object.entries(inputs).forEach(([name, input]) => {
          if (!input) {
            console.error(`❌ [ERREUR] Le champ ${name} est null ou non trouvé`);
          }
        });

        // Vérifier la case de politique de confidentialité
        const privacyCheckbox = document.getElementById("client-privacy");
        const privacyValid = privacyCheckbox ? privacyCheckbox.checked : false;
        
        // Si la case est cochée et que l'email est vide, on le marque comme touché pour afficher l'erreur
        if (privacyCheckbox && privacyCheckbox.checked && emailInput && emailInput.value.trim() === '') {
          touched.email = true;
        }

        console.log("🔍 [VALIDATE ALL]", {
          firstname: firstnameValid,
          lastname: lastnameValid,
          email: emailValid,
          phone: phoneValid,
          privacy: privacyValid,
        });

        if (!firstnameValid) valid = false;
        if (!lastnameValid) valid = false;
        if (!emailValid) valid = false;
        if (!phoneValid) valid = false;
        if (!privacyValid) valid = false;

        console.log("✅ [VALIDATE ALL] Résultat final:", valid);
        submitBtn.disabled = !valid;
        return valid;
      }

      // Gestion du "touched" et validation champ par champ
      [
        { input: firstnameInput, type: "firstname" },
        { input: lastnameInput, type: "lastname" },
        { input: emailInput, type: "email" },
        { input: phoneInput, type: "phone" },
      ].forEach(({ input, type }) => {
        // Vérifier que l'input existe avant d'ajouter des écouteurs
        if (input) {
          input.addEventListener("blur", function () {
            touched[type] = true;
            validateField(input, type);
            validateAll();
          });
          
          input.addEventListener("input", function () {
            if (touched[type]) validateField(input, type);
            validateAll();
          });
          
          console.log(` [Écouteurs d'événements ajoutés pour le champ ${type}`);
        } else {
          console.error(` [Impossible d'ajouter les écouteurs d'événements pour le champ ${type}: input non trouvé`);
        }
      });

      // Valider l'email avant de passer au champ téléphone
      phoneInput.addEventListener("focus", function() {
        if (emailInput) {
          // Si l'email n'a pas encore été touché, on le marque comme touché
          if (!touched.email) {
            touched.email = true;
            validateField(emailInput, "email");
          }
          validateAll();
        }
      });

      // Empêche la saisie de chiffres dans nom/prénom
      [
        { input: firstnameInput, name: "prénom" },
        { input: lastnameInput, name: "nom" }
      ].forEach(({ input, name }) => {
        if (input) {
          input.addEventListener("keypress", function (e) {
            if (/[0-9]/.test(e.key)) {
              console.log(` [Tentative de saisie d'un chiffre dans le champ ${name} bloquée`);
              e.preventDefault();
            }
          });
        } else {
          console.error(` [Impossible d'ajouter la validation pour le champ ${name}: input non trouvé`);
        }
      });

      // Gestion du champ téléphone
      if (phoneInput) {
        // Empêche la saisie de caractères non numériques dans le champ téléphone
        phoneInput.addEventListener("keypress", function (e) {
          // Autorise uniquement les chiffres, espaces, tirets et points
          if (!/[0-9\s\-\.]/.test(e.key)) {
            console.log(" [Caractère non autorisé dans le champ téléphone:", e.key);
            e.preventDefault();
            return false;
          }
        });

        // Nettoyage supplémentaire sur le collage (paste) et la validation
        phoneInput.addEventListener('paste', function(e) {
          console.log(" [Collage détecté dans le champ téléphone");
          // Récupère les données collées
          const pastedData = (e.clipboardData || window.clipboardData).getData('text');
          console.log(" [Données collées:", pastedData);
          
          // Vérifie si des caractères non autorisés sont présents
          if (/[^0-9\s\-\.]/.test(pastedData)) {
            console.log(" [Données collées non autorisées, collage bloqué");
            e.preventDefault();
            return false;
          }
          console.log(" [Données collées autorisées");
        });

        // Nettoyage de la valeur lors de la perte de focus
        phoneInput.addEventListener('blur', function() {
          console.log(" [Perte de focus du champ téléphone, nettoyage en cours...");
          // Supprime tous les caractères non numériques sauf les espaces, tirets et points
          const oldValue = this.value;
          this.value = this.value.replace(/[^0-9\s\-\.]/g, '');
          
          if (oldValue !== this.value) {
            console.log(" [Valeur nettoyée:", this.value);
          }
        });
      } // Fin de if (phoneInput)

      // Écouteur pour la case de politique de confidentialité
      const privacyCheckbox = document.getElementById("client-privacy");
      if (privacyCheckbox) {
        privacyCheckbox.addEventListener("change", function () {
          // Si on coche la case et que l'email est vide, on le marque comme touché
          if (this.checked && emailInput && emailInput.value.trim() === '') {
            touched.email = true;
            validateField(emailInput, 'email');
          }
          validateAll();
        });
      }

      // Validation initiale pour désactiver le bouton au chargement
      validateAll();

      // Ajouter un écouteur pour le champ téléphone du sélecteur personnalisé
      if (window.simpleCountrySelector) {
        const customPhoneInput =
          window.simpleCountrySelector.container.querySelector(
            ".simple-phone-input"
          );
        if (customPhoneInput) {
          customPhoneInput.addEventListener("blur", function () {
            touched.phone = true;
            validateField(null, "phone");
            validateAll();
          });
          customPhoneInput.addEventListener("input", function () {
            if (touched.phone) {
              // Délai pour éviter la validation pendant la saisie
              clearTimeout(window.phoneValidationTimeout);
              window.phoneValidationTimeout = setTimeout(() => {
                validateField(null, "phone");
              }, 200);
            }
            validateAll();
          });
          // Empêche la saisie de caractères non numériques dans le champ téléphone personnalisé
          customPhoneInput.addEventListener("keypress", function (e) {
            // Autorise uniquement les chiffres, espaces, tirets et points
            if (!/[0-9\s\-\.]/.test(e.key)) {
              e.preventDefault();
              return false;
            }
          });

          // Nettoyage supplémentaire sur le collage (paste) pour le champ personnalisé
          customPhoneInput.addEventListener('paste', function(e) {
            const pastedData = (e.clipboardData || window.clipboardData).getData('text');
            if (/[^0-9\s\-\.]/.test(pastedData)) {
              e.preventDefault();
              return false;
            }
          });

          // Nettoyage de la valeur lors de la perte de focus pour le champ personnalisé
          customPhoneInput.addEventListener('blur', function() {
            this.value = this.value.replace(/[^0-9\s\-\.]/g, '');
          });
        }
      }

      // Fonction pour forcer la réinitialisation du sélecteur si nécessaire
      function ensureSelectorReady() {
        if (!window.simpleCountrySelector && window.SimpleCountrySelector) {
          const container = document.getElementById(
            "simple-country-selector-container"
          );
          if (container) {
            window.simpleCountrySelector = new SimpleCountrySelector(
              container,
              {
                defaultCountry: "DZ",
                placeholder: "Numéro de téléphone",
              }
            );
            console.log(
              "🔧 [Fix] Sélecteur réinitialisé:",
              window.simpleCountrySelector
            );
          }
        }

        // Vérifier aussi que les fonctions globales sont disponibles
        if (!window.getPlanityCountryCode || !window.getPlanityPhoneNumber) {
          console.log(
            "🔧 [Fix] Fonctions globales manquantes, réinitialisation..."
          );
          if (window.simpleCountrySelector) {
            // Forcer la réexposition des fonctions globales
            window.getPlanityCountryCode = function () {
              return window.simpleCountrySelector.getCountryCode();
            };
            window.getPlanityPhoneNumber = function () {
              return window.simpleCountrySelector.getFullPhoneNumber();
            };
            console.log("🔧 [Fix] Fonctions globales réexposées");
          }
        }
      }

      // Vérifier périodiquement que le sélecteur est prêt
      setInterval(ensureSelectorReady, 500); // Réduit de 1000ms à 500ms

      // Validation au submit
      form.onsubmit = function (e) {
        console.log("[ONSUBMIT] submit triggered");
        e.preventDefault(); // Toujours empêcher le submit natif
        touched.firstname = true;
        touched.lastname = true;
        touched.email = true;
        touched.phone = true;
        if (!validateAll()) {
          console.log("[ONSUBMIT] Validation échouée");
          return false;
        }
        // Mettre à jour toutes les infos client dans bookingState avant d'afficher le ticket
        bookingState.client.firstname = firstnameInput.value;
        bookingState.client.lastname = lastnameInput.value;
        bookingState.client.email = emailInput.value;
        bookingState.client.phone = window.getPlanityPhoneNumber
          ? window.getPlanityPhoneNumber()
          : window.iti
          ? window.iti.getNumber()
          : "";
        updateBookingState();
        submitBtn.disabled = true;
        jQuery.ajax({
          url: window.ajaxurl,
          type: "POST",
          data: {
            action: "add_booking",
            service_id: bookingState.selectedService.id,
            employee_id: bookingState.selectedEmployee.id,
            date: bookingState.selectedDate,
            slot: bookingState.selectedSlot,
            firstname: firstnameInput.value,
            lastname: lastnameInput.value,
            email: emailInput.value ? emailInput.value : "",
            phone: bookingState.client.phone,
            nonce: window.ib_nonce,
          },
          success: function (response) {
            console.log("Réponse AJAX réservation:", response);
            if (typeof response === "string") {
              try {
                response = JSON.parse(response);
              } catch (e) {
                console.error("Erreur parsing JSON:", e, response);
              }
            }
            console.log(
              "Test response.success:",
              response.success,
              "Type:",
              typeof response.success
            );
            if (response.success) {
              console.log("Ticket: goToStep(5)");
              goToStep(5); // Afficher le ticket
            } else {
              console.warn(
                "Réservation échouée, message:",
                response.data && response.data.message
              );
              showBookingNotification(
                "Erreur lors de la réservation : " +
                  (response.data && response.data.message
                    ? response.data.message
                    : "Erreur inconnue")
              );
              if (submitBtn) submitBtn.disabled = false; // Réactive le bouton si erreur
            }
          },
          error: function (xhr, status, error) {
            console.error("[AJAX ERROR]", status, error, xhr);
            showBookingNotification(
              "Erreur AJAX lors de la réservation : " + error
            );
            if (submitBtn) submitBtn.disabled = false; // Réactive le bouton si erreur
          },
        });
        return false;
      };
    }

    // --- Appliquer la validation moderne à l'étape 4 ---
    setTimeout(() => {
      const form = document.getElementById("booking-client-form");
      if (form) {
        setTimeout(() => {
          // Le sélecteur Planity se charge automatiquement via planity-phone-selector.js
          setupModernValidation(form);
        }, 100);
      }
    }, 100);

    // Stepper mobile : met à jour l'étape active et la barre de progression
    function updateMobileStepper(currentStep, totalSteps) {
      const steps = document.querySelectorAll(".ib-stepper-mobile .ib-step");
      steps.forEach((el, idx) => {
        el.classList.remove("active", "completed");
        if (idx + 1 < currentStep) el.classList.add("completed");
        else if (idx + 1 === currentStep) el.classList.add("active");
      });
      // Progress bar
      let progressBar = document.querySelector(".ib-stepper-progress-bar");
      if (!progressBar) {
        const bar = document.createElement("div");
        bar.className = "ib-stepper-progress-bar";
        document.querySelector(".ib-stepper-progress").appendChild(bar);
        progressBar = bar;
      }
      const percent = ((currentStep - 1) / (totalSteps - 1)) * 100;
      progressBar.style.width = percent + "%";

      // Mettre à jour aussi la barre de progression principale
      if (window.bookingState) {
        window.bookingState.step = currentStep;
        if (typeof window.updateProgressBar === "function") {
          window.updateProgressBar();
        }
      }
    }

    // Fonction pour ajouter un feedback tactile mobile
    function addMobileTouchFeedback() {
      // Ajouter des événements touch pour tous les éléments interactifs
      const interactiveElements = document.querySelectorAll(
        ".btn-modern, .slot-btn, .card, .calendly-day, .ib-step"
      );

      interactiveElements.forEach((element) => {
        // Éviter les doublons d'événements
        if (element.hasAttribute("data-touch-enhanced")) return;
        element.setAttribute("data-touch-enhanced", "true");

        element.addEventListener(
          "touchstart",
          function (e) {
            this.style.transform = "scale(0.98)";
            this.style.transition = "transform 0.1s ease";
          },
          { passive: true }
        );

        element.addEventListener(
          "touchend",
          function (e) {
            setTimeout(() => {
              this.style.transform = "";
              this.style.transition = "all 0.2s ease";
            }, 100);
          },
          { passive: true }
        );

        element.addEventListener(
          "touchcancel",
          function (e) {
            this.style.transform = "";
            this.style.transition = "all 0.2s ease";
          },
          { passive: true }
        );
      });
    }

    // Fonction pour améliorer la navigation mobile
    function enhanceMobileNavigation() {
      // Ajouter des gestes de swipe pour la navigation
      let startX = 0;
      let startY = 0;

      document.addEventListener(
        "touchstart",
        function (e) {
          startX = e.touches[0].clientX;
          startY = e.touches[0].clientY;
        },
        { passive: true }
      );

      document.addEventListener(
        "touchend",
        function (e) {
          if (!startX || !startY) return;

          const endX = e.changedTouches[0].clientX;
          const endY = e.changedTouches[0].clientY;

          const diffX = startX - endX;
          const diffY = startY - endY;

          // Swipe horizontal pour navigation
          if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
            if (diffX > 0 && bookingState.step < 5) {
              // Swipe gauche = étape suivante
              nextStep();
            } else if (diffX < 0 && bookingState.step > 1) {
              // Swipe droite = étape précédente
              previousStep();
            }
          }

          startX = 0;
          startY = 0;
        },
        { passive: true }
      );
    }

    // Appelle updateMobileStepper à chaque changement d'étape
    // Exemple d'appel (à adapter selon ta logique de navigation) :
    // updateMobileStepper(bookingState.step, 5);

    // Fonction de debug pour le calendrier
    window.debugCalendar = function () {
      console.log("🔧 === DEBUG CALENDRIER ===");

      // 1. Vérifier l'état du booking
      console.log("1️⃣ État du booking:", bookingState);

      // 2. Vérifier les variables globales
      console.log("2️⃣ Variables globales:");
      console.log("   • window.ajaxurl:", window.ajaxurl);
      console.log("   • window.ib_nonce:", window.ib_nonce);
      console.log("   • window.availableDays:", window.availableDays);

      // 3. Tester un appel direct
      if (bookingState.selectedService && bookingState.selectedEmployee) {
        console.log("3️⃣ Test d'appel AJAX direct...");

        jQuery.ajax({
          url: window.ajaxurl,
          type: "POST",
          data: {
            action: "get_available_days",
            employee_id: bookingState.selectedEmployee.id,
            service_id: bookingState.selectedService.id,
            year: 2026,
            month: 3, // Mars 2026
            nonce: window.ib_nonce,
          },
          success: function (response) {
            console.log("✅ Réponse directe:", response);
          },
          error: function (xhr, status, error) {
            console.error("❌ Erreur directe:", {
              status: status,
              error: error,
              responseText: xhr.responseText,
            });
          },
        });
      } else {
        console.log("3️⃣ ❌ Impossible de tester - service ou employé manquant");
      }
    };

    // Fonction pour tester les créneaux
    window.testBookingSlots = function () {
      console.log("🧪 Test des créneaux de réservation");
      console.log("État actuel:", bookingState);

      if (!bookingState.selectedService || !bookingState.selectedEmployee) {
        console.log(
          "❌ Veuillez d'abord sélectionner un service et un employé"
        );
        return;
      }

      // Test pour le mois actuel
      const now = new Date();
      loadAvailableDays(now.getFullYear(), now.getMonth(), function () {
        console.log(
          "✅ Test terminé, jours disponibles:",
          window.availableDays
        );
      });
    };

    // Écouteur d'événement pour la sélection de service depuis l'accordéon mobile
    document.addEventListener("serviceSelected", function (event) {
      console.log("Événement serviceSelected reçu:", event.detail);
      if (event.detail && event.detail.service && event.detail.step) {
        bookingState.selectedService = event.detail.service;
        bookingState.step = event.detail.step;
        goToStep(event.detail.step);
      }
    });

    // Initialiser le formulaire en affichant la première étape
    console.log("🚀 Initialisation du formulaire de réservation...");
    goToStep(1);
  } // Fin de initBooking

  // Fonction utilitaire pour nettoyer et formater un numéro de téléphone en temps réel
  function formatPhoneInput(input) {
    if (!input) return '';
    
    // Récupérer la position du curseur
    const start = input.selectionStart;
    const end = input.selectionEnd;
    
    // Récupérer la valeur actuelle
    let value = input.value;
    
    // Nettoyer la valeur (conserver uniquement les chiffres et le + initial)
    const cleaned = cleanPhoneNumber(value);
    
    // Mettre à jour la valeur nettoyée
    input.value = cleaned;
    
    // Restaurer la position du curseur en tenant compte des caractères supprimés
    const diff = value.length - cleaned.length;
    input.setSelectionRange(Math.max(0, start - diff), Math.max(0, end - diff));
    
    return cleaned;
  }

  // Fonction pour initialiser le sélecteur de pays simple
  function initSimpleCountrySelector() {
    if (window.simpleCountrySelector) {
      console.log("🔍 [DEBUG] Sélecteur de pays déjà initialisé");
      return;
    }
    console.log("🔍 [DEBUG] Début initSimpleCountrySelector");
    console.log(
      "🔍 [DEBUG] window.simpleCountrySelector avant:",
      window.simpleCountrySelector
    );

    const container = document.querySelector(
      "#simple-country-selector-container"
    );
    if (!container) {
      console.error(
        "❌ Container simple-country-selector-container non trouvé"
      );
      return;
    }

    console.log("✅ Container trouvé:", container);
    console.log("🔍 [DEBUG] Container HTML avant:", container.innerHTML);
    console.log("🔍 [DEBUG] Container styles:", {
      display: container.style.display,
      visibility: container.style.visibility,
      opacity: container.style.opacity,
      position: container.style.position,
      zIndex: container.style.zIndex,
    });

    // Vérifier que SimpleCountrySelector est disponible
    if (typeof SimpleCountrySelector === "undefined") {
      console.error("❌ SimpleCountrySelector n'est pas défini");
      console.log(
        "🔍 [DEBUG] Variables globales disponibles:",
        Object.keys(window).filter(
          (k) => k.includes("Country") || k.includes("Phone")
        )
      );
      return;
    }

    try {
      // Nettoyer le container
      container.innerHTML = "";
      console.log("🔧 Container nettoyé");

      // Forcer l'affichage du container AVANT l'initialisation
      container.style.display = "block";
      container.style.visibility = "visible";
      container.style.opacity = "1";
      container.style.position = "relative";
      container.style.zIndex = "1000";
      container.style.minHeight = "48px";
      container.style.width = "100%";
      console.log("🔧 Styles forcés sur le container");

      // Masquer TOUS les sélecteurs intl-tel-input existants
      const allItiSelectors = document.querySelectorAll(
        '.iti, input[type="tel"]:not(.simple-phone-input)'
      );
      console.log(
        "🔍 [DEBUG] Sélecteurs intl-tel-input trouvés:",
        allItiSelectors.length
      );
      allItiSelectors.forEach((selector, index) => {
        selector.style.display = "none";
        selector.style.visibility = "hidden";
        selector.style.opacity = "0";
        if (selector.parentElement) {
          selector.parentElement.style.display = "none";
        }
        console.log(`🔧 Sélecteur ${index + 1} masqué:`, selector);
      });

      // Initialiser le sélecteur
      window.simpleCountrySelector = new SimpleCountrySelector(container, {
        defaultCountry: "DZ",
        placeholder: "Numéro de téléphone",
      });

      console.log("✅ Sélecteur créé:", window.simpleCountrySelector);
      console.log(
        "🔍 [DEBUG] window.simpleCountrySelector après:",
        window.simpleCountrySelector
      );
      console.log("🔍 [DEBUG] Container HTML après:", container.innerHTML);

      // Fonction globale pour récupérer le numéro complet
      window.getPhoneNumber = function () {
        return window.simpleCountrySelector
          ? window.simpleCountrySelector.getFullPhoneNumber()
          : "";
      };

      // Écouter les changements de pays
      container.addEventListener("countryChanged", function (e) {
        console.log("Pays sélectionné:", e.detail.country);
        // Mettre à jour le champ caché
        const hiddenInput = document.querySelector("#client-phone");
        if (hiddenInput) {
          hiddenInput.value = window.getPhoneNumber();
        }
      });

      // Écouter les changements du numéro de téléphone avec validation en temps réel
      const phoneInput = container.querySelector(".simple-phone-input");
      if (phoneInput) {
        // Ajouter des attributs pour le contrôle de la saisie
        phoneInput.setAttribute('inputmode', 'tel');
        phoneInput.setAttribute('pattern', '[0-9+]*');
        phoneInput.setAttribute('autocomplete', 'tel');
        
        // Fonction pour obtenir la longueur maximale selon le pays
        function getMaxPhoneLength() {
          let country = "";
          if (window.getPlanityCountryCode) {
            country = window.getPlanityCountryCode().replace("+", "");
          } else if (window.iti && window.iti.getSelectedCountryData) {
            country = window.iti.getSelectedCountryData().dialCode;
          }
          
          // Longueur maximale en fonction du pays (uniquement pour le numéro, sans le code pays)
          const maxLengths = {
            '33': 10,   // France: 9 ou 10 chiffres (avec/sans 0 initial)
            '213': 10,  // Algérie: 9 ou 10 chiffres
            '212': 10,  // Maroc: 9 ou 10 chiffres
            '216': 9    // Tunisie: 8 ou 9 chiffres
          };
          
          // Par défaut: 13 chiffres max (uniquement pour le numéro, sans le code pays)
          return maxLengths[country] || 13;
        }

        // Empêcher la saisie de caractères non numériques et limiter la longueur
        phoneInput.addEventListener('keydown', function(e) {
          const currentValue = this.value;
          const selection = window.getSelection().toString();
          
          // Autoriser : backspace, delete, tab, escape, enter, home, end, flèches
          if ([8, 9, 13, 27, 35, 36, 37, 38, 39, 40].includes(e.keyCode) || 
              // Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
              (e.ctrlKey === true && [65, 67, 86, 88].includes(e.keyCode))) {
            return;
          }
          
          // Si la touche est suppr ou backspace, laisser faire
          if (e.keyCode === 46 || e.keyCode === 8) {
            return;
          }
          
          // Vérifier la longueur maximale
          const maxLength = getMaxPhoneLength();
          if (currentValue.length >= maxLength && !selection) {
            e.preventDefault();
            return false;
          }
          
          // Autoriser uniquement les chiffres et le signe +
          if (!/^[0-9+]$/.test(e.key)) {
            e.preventDefault();
            return false;
          }
        });

        // Gérer l'événement input pour la validation en temps réel
        phoneInput.addEventListener("input", function (e) {
          // Nettoyer et formater le numéro
          const cleanedValue = formatPhoneInput(this);
          
          // Mettre à jour le champ caché avec la valeur nettoyée
          const hiddenInput = document.querySelector("#client-phone");
          if (hiddenInput) {
            hiddenInput.value = window.getPhoneNumber ? window.getPhoneNumber() : cleanedValue;
          }
          
          // La validation complète se fera uniquement au blur
        });
        
        // Gérer l'événement blur pour la validation finale
        phoneInput.addEventListener("blur", function() {
          const cleanedValue = cleanPhoneNumber(this.value);
          const isValid = isValidPhoneNumber(cleanedValue);
          
          
        });
        
        // Prévenir le collage de texte non valide
        phoneInput.addEventListener('paste', function(e) {
          e.preventDefault();
          const pastedText = (e.clipboardData || window.clipboardData).getData('text');
          const cleaned = cleanPhoneNumber(pastedText);
          
          // Insérer le texte nettoyé à la position du curseur
          const start = this.selectionStart;
          const end = this.selectionEnd;
          const newValue = this.value.substring(0, start) + cleaned + this.value.substring(end);
          
          // Mettre à jour la valeur et positionner le curseur
          this.value = newValue;
          const newCursorPos = start + cleaned.length;
          this.setSelectionRange(newCursorPos, newCursorPos);
          
          // Déclencher l'événement input pour la validation
          this.dispatchEvent(new Event('input'));
        });
      }

      // Charger la valeur existante si elle existe
      const existingPhone = bookingState.client.phone;
      if (existingPhone) {
        window.simpleCountrySelector.setPhoneNumber(existingPhone);
      }

      console.log("✅ Sélecteur de pays simple initialisé avec succès");

      // Forcer l'affichage du nouveau sélecteur et masquer l'ancien
      setTimeout(() => {
        console.log("🔧 [DEBUG] Application des styles forcés...");

        // Masquer tous les anciens sélecteurs intl-tel-input
        const oldSelectors = document.querySelectorAll(
          '.iti, input[type="tel"]:not(.simple-phone-input)'
        );
        console.log(
          "🔍 [DEBUG] Anciens sélecteurs trouvés:",
          oldSelectors.length
        );
        oldSelectors.forEach((selector) => {
          selector.style.display = "none";
          if (selector.parentElement) {
            selector.parentElement.style.display = "none";
          }
        });

        // Forcer l'affichage du nouveau container
        container.style.display = "block";
        container.style.visibility = "visible";
        container.style.opacity = "1";
        container.style.position = "relative";
        container.style.zIndex = "1000";

        const phoneContainer = container.querySelector(
          ".simple-phone-container"
        );
        if (phoneContainer) {
          phoneContainer.style.display = "flex";
          phoneContainer.style.visibility = "visible";
          phoneContainer.style.opacity = "1";
          phoneContainer.style.position = "relative";
          phoneContainer.style.zIndex = "1001";

          // Forcer l'affichage de tous les éléments enfants
          const allChildren = phoneContainer.querySelectorAll("*");
          allChildren.forEach((child) => {
            child.style.display = child.style.display || "block";
            child.style.visibility = "visible";
            child.style.opacity = "1";
          });
        }

        console.log("🔧 Styles forcés appliqués au sélecteur");

        // Vérification finale
        const finalCheck = container.querySelector(".simple-phone-container");
        if (finalCheck) {
          console.log(
            "✅ Sélecteur finalement visible:",
            finalCheck.offsetWidth > 0 && finalCheck.offsetHeight > 0
          );
          console.log("🔍 [DEBUG] Dimensions du sélecteur:", {
            width: finalCheck.offsetWidth,
            height: finalCheck.offsetHeight,
            rect: finalCheck.getBoundingClientRect(),
          });
        }

        // Vérifier si le sélecteur est réellement visible
        setTimeout(() => {
          const phoneContainer = container.querySelector(
            ".simple-phone-container"
          );
          if (phoneContainer) {
            const rect = phoneContainer.getBoundingClientRect();
            const isVisible =
              rect.width > 0 &&
              rect.height > 0 &&
              rect.top >= 0 &&
              rect.left >= 0 &&
              rect.bottom <= window.innerHeight &&
              rect.right <= window.innerWidth;
            console.log("🔍 [DEBUG] Visibilité finale du sélecteur:", {
              isVisible,
              rect,
              computedStyle: window.getComputedStyle(phoneContainer),
            });
          }
        }, 500);
      }, 200);

      // Surveiller et masquer automatiquement tout nouveau sélecteur intl-tel-input
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1) {
              // Element node
              // Chercher les nouveaux sélecteurs intl-tel-input
              const newItiSelectors = node.querySelectorAll
                ? node.querySelectorAll(".iti")
                : [];
              const newTelInputs = node.querySelectorAll
                ? node.querySelectorAll(
                    'input[type="tel"]:not(.simple-phone-input)'
                  )
                : [];

              [...newItiSelectors, ...newTelInputs].forEach((selector) => {
                selector.style.display = "none !important";
                if (selector.parentElement) {
                  selector.parentElement.style.display = "none !important";
                }
              });
            }
          });
        });
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true,
      });
    } catch (error) {
      console.error("❌ Erreur lors de l'initialisation du sélecteur:", error);
    }
  } // Fin de la fonction initSimpleCountrySelector
  
  // Démarrer l'initialisation
  initBookingWhenReady();
})(); // Fin de la fonction auto-exécutée
