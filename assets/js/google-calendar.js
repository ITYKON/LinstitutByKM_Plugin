/**
 * Google Calendar Style Booking Calendar
 * Gère l'initialisation et les interactions du calendrier
 */

// Variables globales
let calendar;
let selectedEvent = null;
let isDragging = false;
let startTime, endTime;

// Fonction de débogage pour afficher les données du calendrier
function debugCalendarData() {
  console.group("Données du calendrier");
  console.log("URL AJAX:", ibkCalendarData.ajax_url);
  console.log("Nonces:", ibkCalendarData.nonces);
  console.log("Employés:", ibkCalendarData.employees);
  console.log("Services:", ibkCalendarData.services);
  console.log("Réservations:", ibkCalendarData.bookings);
  console.log(
    "Heures d'ouverture:",
    ibkCalendarData.opening_time,
    "-",
    ibkCalendarData.closing_time
  );
  console.groupEnd();
}

// Initialisation du calendrier
function initCalendar() {
  const calendarEl = document.getElementById("calendar");
  if (!calendarEl) return;

  // Récupérer les données localisées depuis PHP
  const calendarData = window.ibkCalendarData || {};
  const {
    employees = [],
    services = [],
    categories = [],
    bookings = [],
    nonces = {},
  } = calendarData;

  // Afficher les données de débogage dans la console
  if (typeof debugCalendarData === "function") {
    debugCalendarData();
  }

  // Initialiser FullCalendar
  calendar = new FullCalendar.Calendar(calendarEl, {
    // Configuration de base
    headerToolbar: false, // On gère l'en-tête manuellement
    initialView: "dayGridMonth",
    locale: "fr",
    timeZone: "Europe/Paris",
    firstDay: 1, // Lundi comme premier jour de la semaine
    dayMaxEvents: true,
    nowIndicator: true,
    height: "auto",
    contentHeight: "auto",
    aspectRatio: 1.8,

    // Vues disponibles
    views: {
      dayGridMonth: {
        dayMaxEventRows: 4,
        dayHeaderFormat: { weekday: "short", day: "numeric" },
        titleFormat: { year: "numeric", month: "long" },
      },
      timeGridWeek: {
        dayHeaderFormat: { weekday: "short", day: "numeric", month: "short" },
        titleFormat: { year: "numeric", month: "short", day: "numeric" },
        slotMinTime: "07:00:00",
        slotMaxTime: "21:00:00",
        allDaySlot: false,
      },
      timeGridDay: {
        dayHeaderFormat: {
          weekday: "long",
          day: "numeric",
          month: "long",
          year: "numeric",
        },
        slotMinTime: "07:00:00",
        slotMaxTime: "21:00:00",
        allDaySlot: false,
      },
      resourceTimelineDay: {
        type: "resourceTimeline",
        resourceAreaHeaderContent: "Employés",
        resourceAreaWidth: "200px",
        slotMinTime: "07:00:00",
        slotMaxTime: "21:00:00",
      },
      listWeek: {
        titleFormat: { year: "numeric", month: "short", day: "numeric" },
      },
    },

    // Ressources (employés)
    resources: employees.map((emp) => ({
      id: emp.id,
      title: emp.name,
      eventColor: emp.color || "#4f8cff",
      extendedProps: {
        email: emp.email || "",
        phone: emp.phone || "",
      },
    })),

    // Événements
    events: bookings,
    eventTimeFormat: {
      hour: "2-digit",
      minute: "2-digit",
      hour12: false,
    },

    // Personnalisation de l'affichage des événements
    eventContent: function (arg) {
      // Personnaliser le contenu de l'événement
      const event = arg.event;
      const titleEl = document.createElement("div");
      titleEl.classList.add("fc-event-title");
      titleEl.innerHTML = `
                    <div class="fc-event-time">${arg.timeText}</div>
                    <div class="fc-event-title-text">${event.title}</div>
                    ${
                      event.extendedProps.employee
                        ? `<div class="fc-event-employee">${event.extendedProps.employee}</div>`
                        : ""
                    }
                `;

      return { domNodes: [titleEl] };
    },

    // Gestion des clics
    dateClick: function (info) {
      // Créer un nouvel événement au clic sur une cellule
      if (info.view.type === "dayGridMonth") {
        calendar.changeView("timeGridDay", info.date);
        return;
      }

      startTime = info.date;
      endTime = new Date(startTime.getTime() + 60 * 60 * 1000); // +1h par défaut

      openEventModal({
        start: startTime,
        end: endTime,
        allDay: info.allDay,
      });
    },

    // Gestion de la sélection de plage
    selectable: true,
    select: function (info) {
      startTime = info.start;
      endTime = info.end;

      openEventModal({
        start: startTime,
        end: endTime,
        allDay: info.allDay,
      });

      calendar.unselect();
    },

    // Gestion du clic sur un événement
    eventClick: function (info) {
      selectedEvent = info.event;
      
      // Create a deep copy of the event data to avoid modifying the original
      const eventData = {
        id: selectedEvent.id,
        title: selectedEvent.title,
        start: selectedEvent.start,
        end: selectedEvent.end || new Date(selectedEvent.start.getTime() + 60 * 60 * 1000),
        allDay: selectedEvent.allDay,
        extendedProps: {
          ...selectedEvent.extendedProps,
          // Ensure these properties are properly passed through
          service_id: selectedEvent.extendedProps.service_id,
          employee_id: selectedEvent.extendedProps.employee_id,
          service: selectedEvent.extendedProps.service,
          employee: selectedEvent.extendedProps.employee,
          status: selectedEvent.extendedProps.status,
          notes: selectedEvent.extendedProps.notes
        },
        backgroundColor: selectedEvent.backgroundColor,
        textColor: selectedEvent.textColor,
        borderColor: selectedEvent.borderColor,
      };

      openEventModal(eventData);
      info.jsEvent.preventDefault();
    },

    // Gestion du glisser-déposer
    eventDrop: function (info) {
      updateEvent(info.event);
    },

    // Gestion du redimensionnement
    eventResize: function (info) {
      updateEvent(info.event);
    },

    // Personnalisation des en-têtes de colonnes
    columnHeaderHtml: function (mom) {
      return [
        '<div class="fc-col-header-cell-cushion">',
        mom.format("ddd"),
        '<span class="fc-col-header-cell-date">',
        mom.format("D"),
        "</span>",
        "</div>",
      ].join("");
    },

    // Personnalisation des cellules du jour
    dayCellContent: function (args) {
      return {
        html:
          '<div class="fc-daygrid-day-number">' + args.dayNumberText + "</div>",
      };
    },

    // Chargement des événements
    events: function (fetchInfo, successCallback, failureCallback) {
      // Récupérer les filtres actifs
      const filters = getActiveFilters();

      // Appel AJAX pour récupérer les événements
      fetch(ibkCalendarData.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "ibk_get_events",
          nonce: ibkCalendarData.nonces.get_events,
          start: fetchInfo.startStr,
          end: fetchInfo.endStr,
          ...filters,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            successCallback(data.data);
          } else {
            console.error(
              "Erreur lors du chargement des événements:",
              data.data
            );
            failureCallback(data.data);
          }
        })
        .catch((error) => {
          console.error("Erreur réseau:", error);
          failureCallback(error);
        });
    },

    // Personnalisation du rendu des événements
    eventDidMount: function (info) {
      // Ajouter des classes CSS supplémentaires si nécessaire
      if (info.event.extendedProps.status === "confirmed") {
        info.el.classList.add("event-confirmed");
      } else if (info.event.extendedProps.status === "cancelled") {
        info.el.classList.add("event-cancelled");
      }

      // Afficher un tooltip personnalisé
      if (info.event.extendedProps.notes) {
        new bootstrap.Tooltip(info.el, {
          title: info.event.extendedProps.notes,
          placement: "top",
          trigger: "hover",
          container: "body",
        });
      }
    },
  });

  // Initialiser les écouteurs d'événements
  initEventListeners();

  // Afficher le calendrier
  calendar.render();

  // Mettre à jour la vue actuelle dans l'UI
  updateViewSelector(calendar.view.type);
}

// Initialiser les écouteurs d'événements
function initEventListeners() {
  // Boutons de navigation
  document.querySelectorAll("[data-calendar-nav]").forEach((button) => {
    button.addEventListener("click", function () {
      const action = this.getAttribute("data-calendar-nav");
      switch (action) {
        case "prev":
          calendar.prev();
          break;
        case "next":
          calendar.next();
          break;
        case "today":
          calendar.today();
          break;
      }
      updateViewTitle();
    });
  });

  // Sélecteur de vue
  document.querySelectorAll("[data-calendar-view]").forEach((button) => {
    button.addEventListener("click", function () {
      const view = this.getAttribute("data-calendar-view");
      calendar.changeView(view);
      updateViewSelector(view);
    });
  });

  // Filtres
  document.querySelectorAll(".filter-select").forEach((select) => {
    select.addEventListener("change", function () {
      refetchEvents();
    });
  });

  // Bouton de création
  document
    .getElementById("create-event-btn")
    ?.addEventListener("click", function () {
      openEventModal();
    });
}

// Mettre à jour le sélecteur de vue actif
function updateViewSelector(view) {
  document.querySelectorAll("[data-calendar-view]").forEach((button) => {
    if (button.getAttribute("data-calendar-view") === view) {
      button.classList.add("active");
    } else {
      button.classList.remove("active");
    }
  });
}

// Mettre à jour le titre de la vue
function updateViewTitle() {
  const titleEl = document.querySelector(".calendar-title");
  if (titleEl) {
    titleEl.textContent = calendar.view.title;
  }
}

// Obtenir les filtres actifs
function getActiveFilters() {
  const filters = {};

  // Filtre employé
  const employeeFilter = document.getElementById("filter-employee");
  if (employeeFilter && employeeFilter.value) {
    filters.employee_id = employeeFilter.value;
  }

  // Filtre service
  const serviceFilter = document.getElementById("filter-service");
  if (serviceFilter && serviceFilter.value) {
    filters.service_id = serviceFilter.value;
  }

  // Filtre catégorie
  const categoryFilter = document.getElementById("filter-category");
  if (categoryFilter && categoryFilter.value) {
    filters.category_id = categoryFilter.value;
  }

  // Filtre statut
  const statusFilter = document.getElementById("filter-status");
  if (statusFilter && statusFilter.value) {
    filters.status = statusFilter.value;
  }

  return filters;
}

// Recharger les événements avec les filtres actuels
function refetchEvents() {
  calendar.refetchEvents();
}

// Ouvrir la modale d'édition d'événement
function openEventModal(eventData = {}) {
  const modal = new bootstrap.Modal(document.getElementById("event-modal"));
  const form = document.getElementById("event-form");

  // Always map service and employee names from IDs if missing
  if (!eventData.extendedProps) eventData.extendedProps = {};
  // Map service name
  if (eventData.extendedProps.service_id && !eventData.extendedProps.service) {
    if (window.services) {
      const serviceObj = window.services.find(
        (s) => s.id == eventData.extendedProps.service_id
      );
      if (serviceObj)
        eventData.extendedProps.service =
          serviceObj.name || serviceObj.title || "Non défini";
    }
  }
  // Map employee name
  if (
    eventData.extendedProps.employee_id &&
    !eventData.extendedProps.employee
  ) {
    if (window.employees) {
      const empObj = window.employees.find(
        (e) => e.id == eventData.extendedProps.employee_id
      );
      if (empObj)
        eventData.extendedProps.employee =
          empObj.name || empObj.title || "Non attribué";
    }
  }

  if (!eventData.id) {
    // Nouvel événement
    form.reset();
    document.getElementById("event-id").value = "";
    document.getElementById("event-title").value = "";
    document.getElementById("event-start").value = formatDateTime(
      eventData.start
    );
    document.getElementById("event-end").value = formatDateTime(eventData.end);
    document.getElementById("event-all-day").checked =
      eventData.allDay || false;
    document.getElementById("event-notes").value = "";

    // Définir l'employé par défaut s'il n'y en a qu'un
    const employeeSelect = document.getElementById("event-employee");
    if (employeeSelect && employeeSelect.options.length === 2) {
      employeeSelect.selectedIndex = 1;
    }

    // Définir le service par défaut s'il n'y en a qu'un
    const serviceSelect = document.getElementById("event-service");
    if (serviceSelect && serviceSelect.options.length === 2) {
      serviceSelect.selectedIndex = 1;
    }

    // Définir le statut par défaut
    document.getElementById("event-status").value = "scheduled";

    // Mettre à jour le titre de la modale
    document.getElementById("event-modal-title").textContent =
      "Nouveau rendez-vous";

    // Afficher le bouton de suppression
    document.getElementById("event-delete-btn").classList.add("d-none");
  } else {
    // Édition d'un événement existant
    document.getElementById("event-id").value = eventData.id;
    document.getElementById("event-title").value = eventData.title || "";
    document.getElementById("event-start").value = formatDateTime(
      eventData.start
    );
    document.getElementById("event-end").value = formatDateTime(eventData.end);
    document.getElementById("event-all-day").checked =
      eventData.allDay || false;
    document.getElementById("event-notes").value =
      eventData.extendedProps.notes || "";

    // Définir les valeurs des sélecteurs
    if (eventData.extendedProps.employee_id) {
      setSelectValue("event-employee", eventData.extendedProps.employee_id);
    }

    if (eventData.extendedProps.service_id) {
      setSelectValue("event-service", eventData.extendedProps.service_id);
    }

    if (eventData.extendedProps.status) {
      setSelectValue("event-status", eventData.extendedProps.status);
    }

    // Mettre à jour le titre de la modale
    document.getElementById("event-modal-title").textContent =
      "Modifier le rendez-vous";

    // Afficher le bouton de suppression
    document.getElementById("event-delete-btn").classList.remove("d-none");
  }

  // Afficher la modale
  modal.show();

  // Gérer la soumission du formulaire
  form.onsubmit = function (e) {
    e.preventDefault();
    saveEvent();
  };
}

// Formater une date pour les champs datetime-local
function formatDateTime(date) {
  if (!date) return "";

  const d = new Date(date);
  const pad = (num) => num.toString().padStart(2, "0");

  return (
    [d.getFullYear(), pad(d.getMonth() + 1), pad(d.getDate())].join("-") +
    "T" +
    [pad(d.getHours()), pad(d.getMinutes())].join(":")
  );
}

// Définir la valeur d'un élément select
function setSelectValue(selectId, value) {
  const select = document.getElementById(selectId);
  if (!select) return;

  for (let i = 0; i < select.options.length; i++) {
    if (select.options[i].value === value.toString()) {
      select.selectedIndex = i;
      break;
    }
  }
}

// Enregistrer un événement
function saveEvent() {
  const form = document.getElementById("event-form");
  const formData = new FormData(form);
  const eventId = formData.get("event_id");

  // Convertir les dates en objets Date
  const start = new Date(formData.get("start") + ":00");
  const end = new Date(formData.get("end") + ":00");

  // Vérifier les dates
  if (start >= end) {
    alert("La date de fin doit être postérieure à la date de début");
    return;
  }

  // Préparer les données à envoyer
  const data = {
    action: eventId ? "ibk_update_event" : "ibk_create_event",
    nonce: ibkCalendarData.nonces[eventId ? "update_event" : "create_event"],
    event_id: eventId,
    title: formData.get("title"),
    start: start.toISOString(),
    end: end.toISOString(),
    all_day: formData.get("all_day") ? 1 : 0,
    employee_id: formData.get("employee_id"),
    service_id: formData.get("service_id"),
    status: formData.get("status"),
    notes: formData.get("notes"),
    customer_name: formData.get("customer_name"),
    customer_email: formData.get("customer_email"),
    customer_phone: formData.get("customer_phone"),
  };

  // Envoyer la requête AJAX
  fetch(ibkCalendarData.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        // Fermer la modale et recharger les événements
        bootstrap.Modal.getInstance(
          document.getElementById("event-modal")
        ).hide();
        calendar.refetchEvents();

        // Afficher un message de succès
        showAlert(
          "success",
          result.data.message || "Événement enregistré avec succès"
        );
      } else {
        // Afficher les erreurs
        showAlert("danger", result.data.message || "Une erreur est survenue");
        console.error("Erreur lors de l'enregistrement:", result.data);
      }
    })
    .catch((error) => {
      console.error("Erreur réseau:", error);
      showAlert("danger", "Erreur de connexion. Veuillez réessayer.");
    });
}

// Supprimer un événement
function deleteEvent(eventId) {
  if (
    !confirm(
      "Êtes-vous sûr de vouloir supprimer ce rendez-vous ? Cette action est irréversible."
    )
  ) {
    return;
  }

  fetch(ibkCalendarData.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      action: "ibk_delete_event",
      nonce: ibkCalendarData.nonces.delete_event,
      event_id: eventId,
    }),
  })
    .then((response) => response.json())
    .then((result) => {
      if (result.success) {
        // Fermer la modale et recharger les événements
        bootstrap.Modal.getInstance(
          document.getElementById("event-modal")
        ).hide();
        calendar.refetchEvents();

        // Afficher un message de succès
        showAlert(
          "success",
          result.data.message || "Événement supprimé avec succès"
        );
      } else {
        // Afficher les erreurs
        showAlert("danger", result.data.message || "Une erreur est survenue");
        console.error("Erreur lors de la suppression:", result.data);
      }
    })
    .catch((error) => {
      console.error("Erreur réseau:", error);
      showAlert("danger", "Erreur de connexion. Veuillez réessayer.");
    });
}

// Mettre à jour un événement (drag & drop, redimensionnement)
function updateEvent(event) {
  const data = {
    action: "ibk_update_event",
    nonce: ibkCalendarData.nonces.update_event,
    event_id: event.id,
    start: event.start ? event.start.toISOString() : null,
    end: event.end ? event.end.toISOString() : null,
    all_day: event.allDay ? 1 : 0,
  };

  fetch(ibkCalendarData.ajax_url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(data),
  })
    .then((response) => response.json())
    .then((result) => {
      if (!result.success) {
        console.error("Erreur lors de la mise à jour:", result.data);
        // Annuler les changements en cas d'erreur
        calendar.refetchEvents();
      }
    })
    .catch((error) => {
      console.error("Erreur réseau:", error);
      // Annuler les changements en cas d'erreur
      calendar.refetchEvents();
    });
}

// Afficher une alerte
function showAlert(type, message) {
  const alertContainer = document.getElementById("alert-container");
  if (!alertContainer) return;

  const alert = document.createElement("div");
  alert.className = `alert alert-${type} alert-dismissible fade show`;
  alert.role = "alert";
  alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        `;

  alertContainer.appendChild(alert);

  // Supprimer l'alerte après 5 secondes
  setTimeout(() => {
    alert.classList.remove("show");
    setTimeout(() => alert.remove(), 150);
  }, 5000);
}

// Initialiser le calendrier au chargement de la page
function initializeGoogleCalendar() {
  // Vérifier si l'élément du calendrier existe
  const calendarEl = document.getElementById("calendar");
  if (!calendarEl) {
    console.error("Élément du calendrier non trouvé");
    return;
  }

  // Vérifier si FullCalendar est chargé
  if (typeof FullCalendar === "undefined") {
    console.error("FullCalendar n'est pas chargé");
    return;
  }

  // Initialiser le calendrier
  initCalendar();
}

// Attendre que le DOM soit complètement chargé
document.addEventListener("DOMContentLoaded", initializeGoogleCalendar);

// Exposer les fonctions globales
window.deleteEvent = deleteEvent;

// Initialiser les tooltips
document.addEventListener("DOMContentLoaded", function () {
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
