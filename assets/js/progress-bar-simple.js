// Fichier JavaScript simple pour la barre de progression
console.log("Loading progress-bar-simple.js");

// Initialisation immédiate des variables globales
window.bookingState = window.bookingState || {
  step: 1,
  selectedService: null,
  selectedEmployee: null,
  selectedDate: null,
  selectedSlot: null,
  services: [],
  employees: [],
};

console.log("bookingState initialized:", window.bookingState);

// Fonction pour mettre à jour la barre de progression
window.updateProgressBar = function () {
  console.log(
    "updateProgressBar called - current step:",
    window.bookingState.step
  );

  const steps = document.querySelectorAll(".ib-stepper-main .ib-step");
  const progressBar = document.querySelector(
    ".ib-stepper-main .ib-stepper-progress"
  );

  console.log(
    "Found elements:",
    steps.length,
    "steps,",
    !!progressBar ? "progress bar found" : "no progress bar"
  );

  if (steps.length === 0) {
    console.warn("No steps found in DOM");
    return;
  }

  steps.forEach((step, index) => {
    const stepNumber = index + 1;
    const circle = step.querySelector(".ib-step-circle");

    // Nettoyer les classes
    step.classList.remove("active", "completed");

    if (stepNumber < window.bookingState.step) {
      // Étape complétée
      step.classList.add("completed");
      if (circle) {
        circle.textContent = "✓";
      }
      console.log(`Step ${stepNumber}: completed`);
    } else if (stepNumber === window.bookingState.step) {
      // Étape active
      step.classList.add("active");
      if (circle) {
        circle.textContent = stepNumber;
      }
      console.log(`Step ${stepNumber}: active`);
    } else {
      // Étape future
      if (circle) {
        circle.textContent = stepNumber;
      }
      console.log(`Step ${stepNumber}: future`);
    }
  });

  // Mettre à jour la barre de progression
  if (progressBar && steps.length > 0) {
    const currentStep = window.bookingState.step;
    const totalSteps = steps.length;

    // Calculer la largeur de la barre de progression
    let progressWidth = 0;
    if (currentStep > 1) {
      progressWidth = ((currentStep - 1) / (totalSteps - 1)) * 100;
    }

    console.log(
      `Progress: step ${currentStep}/${totalSteps} = ${progressWidth}%`
    );

    // Calculer la largeur réelle en pixels basée sur la distance entre les étapes
    const stepperMain = progressBar.parentElement;
    if (stepperMain) {
      const firstStep = stepperMain.querySelector(
        ".ib-step:first-child .ib-step-circle"
      );
      const lastStep = stepperMain.querySelector(
        ".ib-step:last-child .ib-step-circle"
      );

      if (firstStep && lastStep) {
        const firstStepRect = firstStep.getBoundingClientRect();
        const lastStepRect = lastStep.getBoundingClientRect();
        const totalDistance = lastStepRect.left - firstStepRect.left;
        const progressDistance = (progressWidth / 100) * totalDistance;

        progressBar.style.width = progressDistance + "px";
        console.log(
          `Progress distance: ${progressDistance}px (${progressWidth}%)`
        );
      } else {
        // Fallback vers pourcentage si impossible de calculer en pixels
        progressBar.style.width = progressWidth + "%";
      }
    } else {
      progressBar.style.width = progressWidth + "%";
    }
  }
};

// Fonction pour changer d'étape
window.setStep = function (step) {
  console.log("setStep called with:", step);

  if (!window.bookingState) {
    console.error("bookingState not available!");
    return;
  }

  const stepNumber = parseInt(step);
  if (isNaN(stepNumber) || stepNumber < 1 || stepNumber > 5) {
    console.error("Invalid step number:", step);
    return;
  }

  window.bookingState.step = stepNumber;
  console.log("Step changed to:", stepNumber);

  // Mettre à jour la barre
  window.updateProgressBar();

  // Sauvegarder dans localStorage
  try {
    localStorage.setItem("bookingState", JSON.stringify(window.bookingState));
    console.log("State saved to localStorage");
  } catch (e) {
    console.warn("Could not save to localStorage:", e);
  }
};

// Fonction d'initialisation
function initializeProgressBar() {
  console.log("Initializing progress bar...");

  // Charger l'état sauvegardé
  try {
    const savedState = localStorage.getItem("bookingState");
    if (savedState) {
      const parsed = JSON.parse(savedState);
      Object.assign(window.bookingState, parsed);
      console.log("Loaded saved state:", window.bookingState);
    }
  } catch (e) {
    console.warn("Could not load saved state:", e);
  }

  // Mettre à jour l'affichage
  window.updateProgressBar();
}

// Initialisation multiple pour s'assurer que ça marche
console.log("Setting up initialization...");

// 1. Immédiatement
initializeProgressBar();

// 2. Quand le DOM est prêt
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM ready - initializing");
    setTimeout(initializeProgressBar, 100);
  });
} else {
  console.log("DOM already ready - initializing");
  setTimeout(initializeProgressBar, 100);
}

// 3. Avec jQuery si disponible
if (typeof jQuery !== "undefined") {
  jQuery(document).ready(function () {
    console.log("jQuery ready - initializing");
    setTimeout(initializeProgressBar, 200);
  });
}

// 4. Délai de sécurité
setTimeout(function () {
  console.log("Safety timeout - final initialization");
  initializeProgressBar();
}, 1000);

console.log("progress-bar-simple.js loaded completely");
