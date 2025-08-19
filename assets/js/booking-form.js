// Initialisation des variables globales AVANT jQuery
var ajaxurl = window.ajaxurl || "";
window.bookingState = window.bookingState || {
  step: 1,
  selectedService: null,
  selectedEmployee: null,
  selectedDate: null,
  selectedSlot: null,
  services: window.bookingServices || [],
  employees: window.bookingEmployees || [],
};

// Fonction pour afficher/masquer les éléments Planity selon l'étape
function toggleHeroCover() {
  // Masquer les éléments Planity après l'étape 1
  const planityHeader = document.getElementById("planity-header");
  const planityCover = document.getElementById("planity-cover");
  const planityBookingSection = document.getElementById(
    "planity-booking-section"
  );

  const showPlanityElements = window.bookingState.step === 1;

  if (planityHeader) {
    planityHeader.style.display = showPlanityElements ? "block" : "none";
  }
  if (planityCover) {
    planityCover.style.display = showPlanityElements ? "block" : "none";
  }
  if (planityBookingSection) {
    planityBookingSection.style.display = showPlanityElements
      ? "block"
      : "none";
  }
}

// Fonction pour mettre à jour la barre de progression (GLOBALE)
window.updateProgressBar = function () {
  const steps = document.querySelectorAll(".ib-stepper-main .ib-step");
  const progressBar = document.querySelector(
    ".ib-stepper-main .ib-stepper-progress"
  );

  steps.forEach((step, index) => {
    const stepNumber = index + 1;
    const circle = step.querySelector(".ib-step-circle");

    step.classList.remove("active", "completed");

    if (stepNumber < window.bookingState.step) {
      step.classList.add("completed");
      // Afficher une coche pour les étapes complétées
      if (circle) {
        circle.textContent = "✓";
      }
    } else if (stepNumber === window.bookingState.step) {
      step.classList.add("active");
      // Afficher le numéro pour l'étape active
      if (circle) {
        circle.textContent = stepNumber;
      }
    } else {
      // Afficher le numéro pour les étapes futures
      if (circle) {
        circle.textContent = stepNumber;
      }
    }
  });

  // Mettre à jour la largeur de la barre de progression
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

// Fonction pour mettre à jour l'état de réservation (GLOBALE)
window.updateBookingState = function () {
  localStorage.setItem("bookingState", JSON.stringify(window.bookingState));
  toggleHeroCover();
  window.updateProgressBar();
};

// Fonction de test pour les boutons (GLOBALE)
window.setStep = function (step) {
  if (!window.bookingState) {
    return;
  }
  window.bookingState.step = step;
  window.updateProgressBar();
};

// Initialisation immédiate (en dehors de jQuery)
const savedState = localStorage.getItem("bookingState");
if (savedState) {
  Object.assign(window.bookingState, JSON.parse(savedState));
}

// Initialiser l'affichage dès que possible
document.addEventListener("DOMContentLoaded", function () {
  toggleHeroCover();
  window.updateProgressBar();
});

// Forcer la mise à jour après un délai pour s'assurer que tout est prêt
setTimeout(() => {
  window.updateProgressBar();
}, 500);

jQuery(document).ready(function ($) {
  window.updateProgressBar();

  // Gestion des créneaux disponibles
  function loadAvailableSlots(date) {
    if (!bookingState.selectedService || !bookingState.selectedEmployee) {
      return;
    }

    const serviceId = bookingState.selectedService.id;
    const employeeId = bookingState.selectedEmployee.id;
    const selectedDate = date || bookingState.selectedDate;

    jQuery.ajax({
      url: window.ajaxurl,
      type: "POST",
      data: {
        action: "get_available_slots",
        employee_id: employeeId,
        service_id: serviceId,
        date: selectedDate,
      },
      success: function (response) {
        if (response.success) {
          displayAvailableSlots(response.data);
        } else {
          console.error(
            "Erreur lors du chargement des créneaux :",
            response.data
          );
          displayAvailableSlots([]);
        }
      },
      error: function (xhr, status, error) {
        console.error("Erreur AJAX :", error);
        displayAvailableSlots([]);
      },
    });
  }

  function displayAvailableSlots(slots) {
    const slotsList = $("#slots-list");
    slotsList.empty();

    if (slots.length === 0) {
      slotsList.html(
        '<div class="no-slots">Aucun créneau disponible pour cette date</div>'
      );
      return;
    }

    const grid = $('<div class="slots-grid"></div>');
    slots.forEach((slot) => {
      const button = $("<button>")
        .addClass("slot-btn")
        .text(`${slot.start} - ${slot.end}`)
        .data("slot", slot)
        .on("click", function () {
          selectSlot($(this).data("slot"));
        });

      if (slot.is_booked) {
        button.prop("disabled", true).addClass("booked");
      }

      grid.append(button);
    });

    slotsList.append(grid);
  }

  function selectSlot(slot) {
    bookingState.selectedSlot = slot;
    updateBookingState();
    // Passer à l'étape suivante
    bookingState.step++;
    renderStepContent();
    renderActions();
  }

  // Écouteur pour le changement de date
  $(document).on("change", "#booking-date", function () {
    const date = $(this).val();
    bookingState.selectedDate = date;
    loadAvailableSlots(date);
  });

  // Initialiser le chargement des créneaux si une date est déjà sélectionnée
  if (bookingState.selectedDate) {
    loadAvailableSlots(bookingState.selectedDate);
  }
});
