// Définition globale des noms de jours en français
const dayNamesFr = [
  "Dimanche",
  "Lundi",
  "Mardi",
  "Mercredi",
  "Jeudi",
  "Vendredi",
  "Samedi",
];
const dayNamesShortFr = ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"];

// Palette pastel par employé
const employeeColors = [
  "#F8BBD0",
  "#B2DFDB",
  "#C5CAE9",
  "#FFE0B2",
  "#D1C4E9",
  "#B3E5FC",
  "#FFCCBC",
  "#DCEDC8",
  "#FFD6E0",
  "#E1BEE7",
];
function getEmployeeColor(employee) {
  if (!employee) return "#B2DFDB";
  let hash = 0;
  for (let i = 0; i < employee.length; i++) hash += employee.charCodeAt(i);
  return employeeColors[hash % employeeColors.length];
}

class InstitutCalendar {
  constructor() {
    this.currentDate = new Date();
    this.currentView = "week";
    this.events = [];
    this.selectedEmployees = new Set();
    this.employees = [];
    this.init();
  }

  bindEvents() {
    const addBtn = document.getElementById("addBtn");
    if (addBtn) addBtn.addEventListener("click", () => this.openModal());

    const closeModal = document.getElementById("closeModal");
    if (closeModal)
      closeModal.addEventListener("click", () => this.closeModal());

    const cancelBtn = document.getElementById("cancelBtn");
    if (cancelBtn) cancelBtn.addEventListener("click", () => this.closeModal());

    const eventForm = document.getElementById("eventForm");
    if (eventForm)
      eventForm.addEventListener("submit", (e) => this.handleFormSubmit(e));

    const searchInput = document.getElementById("searchInput");
    if (searchInput)
      searchInput.addEventListener("input", (e) =>
        this.handleSearch(e.target.value)
      );

    document.querySelectorAll(".view-btn").forEach((btn) => {
      btn.addEventListener("click", (e) =>
        this.changeView(e.target.dataset.view)
      );
    });

    const prevBtn = document.getElementById("prevBtn");
    if (prevBtn) prevBtn.addEventListener("click", () => this.navigateDate(-1));

    const nextBtn = document.getElementById("nextBtn");
    if (nextBtn) nextBtn.addEventListener("click", () => this.navigateDate(1));

    const todayBtn = document.getElementById("todayBtn");
    if (todayBtn) todayBtn.addEventListener("click", () => this.goToToday());
  }

  async init() {
    try {
      this.bindEvents();
      await this.fetchFilters();
      this.updateDateTitle();
      this.renderCurrentView();
      this.updateCurrentTimeLine();
      setInterval(() => this.updateCurrentTimeLine(), 60000);
      this.events = [];
      await this.fetchEvents();
    } catch (error) {
      console.error("Calendar initialization error:", error);
    }
  }

  async fetchFilters() {
    try {
      const resp = await fetch("/wp-json/institut-booking/v1/calendar-filters");
      if (!resp.ok) throw new Error("Erreur API filtres");
      const data = await resp.json();
      this.employees = data.employees.map((e) => ({
        ...e,
        color: getEmployeeColor(e.name),
      }));
      this.services = data.services;
      this.categories = data.categories;
      this.renderEmployeeChips();
      this.renderServiceFilter();
      this.renderCategoryFilter();
    } catch (e) {
      console.error("Erreur chargement filtres", e);
    }
  }

  async fetchEvents() {
    try {
      const response = await fetch(
        "/wp-json/institut-booking/v1/calendar-events"
      );
      if (!response.ok) throw new Error("Erreur API");
      const apiEvents = await response.json();
      // Correction du mapping dans fetchEvents
      this.events = apiEvents
        .filter((ev) => {
          if (!ev.start) return false;
          const d = new Date(ev.start.replace(" ", "T"));
          if (isNaN(d.getTime())) {
            console.warn("Événement ignoré (date invalide):", ev);
            return false;
          }
          return true;
        })
        .map((ev) => {
          const startDate = new Date(ev.start.replace(" ", "T"));
          const duration = parseInt(ev.duration) || 60;
          const endDate = new Date(startDate.getTime() + duration * 60000);
          return {
            id: ev.id,
            title: ev.title || ev.service_name || "Réservation",
            employee: ev.employee || ev.employee_name || "",
            client: ev.client_name || ev.client || "",
            service: ev.service_name || "",
            color: getEmployeeColor(ev.employee || ev.employee_name || ""),
            start: startDate,
            end: endDate,
            startTime: startDate.toTimeString().slice(0, 5),
            endTime: endDate.toTimeString().slice(0, 5),
            date: startDate.toISOString().slice(0, 10),
            raw: ev,
          };
        });
      console.log("Événements chargés pour le calendrier :", this.events);
      this.renderCurrentView();
    } catch (e) {
      console.error("Erreur chargement événements", e);
      this.events = [];
      this.renderCurrentView();
    }
  }

  changeView(view) {
    this.currentView = view;
    document
      .querySelectorAll(".view-btn")
      .forEach((btn) => btn.classList.remove("active"));
    document.querySelector(`[data-view="${view}"]`).classList.add("active");
    document.getElementById("weekView").style.display = "none";
    document.getElementById("dayView").style.display = "none";
    document.getElementById("monthView").style.display = "none";
    document.getElementById(`${view}View`).style.display = "block";
    this.updateDateTitle();
    this.renderCurrentView();
    this.updateCurrentTimeLine();
  }

  navigateDate(direction) {
    const newDate = new Date(this.currentDate);
    switch (this.currentView) {
      case "day":
        newDate.setDate(newDate.getDate() + direction);
        break;
      case "week":
        newDate.setDate(newDate.getDate() + direction * 7);
        break;
      case "month":
        newDate.setMonth(newDate.getMonth() + direction);
        break;
    }
    this.currentDate = newDate;
    this.updateDateTitle();
    this.renderCurrentView();
    this.updateCurrentTimeLine();
  }

  goToToday() {
    this.currentDate = new Date();
    this.updateDateTitle();
    this.renderCurrentView();
    this.updateCurrentTimeLine();
  }

  updateDateTitle() {
    let title = "";
    switch (this.currentView) {
      case "day":
        const dayOptions = {
          weekday: "long",
          year: "numeric",
          month: "long",
          day: "numeric",
        };
        title = this.currentDate.toLocaleDateString("fr-FR", dayOptions);
        title = title.charAt(0).toUpperCase() + title.slice(1);
        break;
      case "week":
        const monthOptions = { month: "long", year: "numeric" };
        title = this.currentDate.toLocaleDateString("fr-FR", monthOptions);
        title = title.charAt(0).toUpperCase() + title.slice(1);
        break;
      case "month":
        const monthYearOptions = { month: "long", year: "numeric" };
        title = this.currentDate.toLocaleDateString("fr-FR", monthYearOptions);
        title = title.charAt(0).toUpperCase() + title.slice(1);
        break;
    }
    const dateTitleEl = document.getElementById("dateTitle");
    if (dateTitleEl) dateTitleEl.textContent = title;
  }

  renderCurrentView() {
    document.getElementById("weekView").style.display =
      this.currentView === "week" ? "block" : "none";
    document.getElementById("dayView").style.display =
      this.currentView === "day" ? "block" : "none";
    document.getElementById("monthView").style.display =
      this.currentView === "month" ? "block" : "none";
    document.getElementById("matrixView").style.display =
      this.currentView === "matrix" ? "block" : "none";
    switch (this.currentView) {
      case "day":
        this.renderDayView();
        break;
      case "week":
        this.renderWeekView();
        break;
      case "month":
        this.renderMonthView();
        break;
      case "matrix":
        this.renderMatrixView();
        break;
    }
  }

  renderWeekView() {
    const dayHeaders = document.getElementById("dayHeaders");
    const daysGrid = document.getElementById("daysGrid");
    if (!dayHeaders || !daysGrid) return;
    dayHeaders.innerHTML = "";
    daysGrid.innerHTML = "";
    daysGrid.style.minHeight = "900px";
    daysGrid.style.position = "relative";

    // Générer les jours de la semaine (Dim -> Sam)
    const weekDays = this.getWeekDays();
    // Header : 1ère colonne vide (pour les heures), puis jours
    const emptyHeader = document.createElement("div");
    emptyHeader.className = "day-header";
    dayHeaders.appendChild(emptyHeader);
    weekDays.forEach((day, i) => {
      const dayHeader = document.createElement("div");
      dayHeader.className = "day-header";
      dayHeader.innerHTML = `<div style='font-size:1.1em;font-weight:600;'>${
        dayNamesShortFr[day.getDay()]
      }</div><div style='font-size:1.2em;'>${day.getDate()}</div>`;
      if (this.isToday(day)) dayHeader.style.background = "#e5f0ff";
      dayHeader.onclick = () => this.openDayModal(weekDays[i]);
      dayHeaders.appendChild(dayHeader);
    });

    // All-day events (affichés en haut)
    const allDayRow = document.createElement("div");
    allDayRow.className = "all-day-row";
    // 1ère colonne label
    const allDayLabel = document.createElement("div");
    allDayLabel.className = "all-day-label";
    allDayLabel.textContent = "all-day";
    allDayRow.appendChild(allDayLabel);
    weekDays.forEach((day, i) => {
      const allDayCell = document.createElement("div");
      allDayCell.className = "all-day-cell";
      // Events all-day pour ce jour
      const allDayEvents = this.events.filter(
        (ev) =>
          ev.date === day.toISOString().slice(0, 10) &&
          ev.startTime === "00:00" &&
          ev.endTime === "23:59"
      );
      allDayEvents.forEach((ev) => {
        const evBlock = document.createElement("div");
        evBlock.className = "event-block";
        evBlock.style.position = "relative";
        evBlock.style.top = "2px";
        evBlock.style.height = "28px";
        evBlock.style.background = "#fff";
        evBlock.style.borderLeftColor = ev.color || "#007aff";
        evBlock.innerHTML = `<div class=\"event-title\">${ev.title}</div><div class=\"event-client\">${ev.client}</div>`;
        evBlock.onclick = () => openCalendarModal("Détail réservation", [ev]);
        allDayCell.appendChild(evBlock);
      });
      allDayRow.appendChild(allDayCell);
    });
    daysGrid.appendChild(allDayRow);

    // Grille horaire : colonnes = 1 (heures) + 7 (jours), lignes = heures (9h-17h, demi-heures pointillées)
    const hours = [];
    for (let h = 9; h <= 17; h++) {
      hours.push(h.toString().padStart(2, "0") + ":00");
    }
    for (let i = 0; i < hours.length; i++) {
      // Colonne heure
      const hourCell = document.createElement("div");
      hourCell.className = "hour-cell";
      hourCell.textContent = hours[i];
      hourCell.style.borderRight = "1px solid #e5e5ea";
      daysGrid.appendChild(hourCell);
      // Colonnes jours
      for (let d = 0; d < 7; d++) {
        const dayCol = document.createElement("div");
        dayCol.className = "day-col";
        dayCol.style.position = "relative";
        dayCol.style.borderBottom = "1px solid #e5e5ea";
        dayCol.style.minHeight = "48px";
        // Demi-heure pointillée
        const halfHour = document.createElement("div");
        halfHour.className = "half-hour";
        dayCol.appendChild(halfHour);
        dayCol.dataset.day = weekDays[d].toISOString().slice(0, 10);
        daysGrid.appendChild(dayCol);
      }
    }
    // Positionner les events (hors all-day) dans la bonne colonne/jour
    this.events.forEach((ev) => {
      if (ev.startTime === "00:00" && ev.endTime === "23:59") return; // déjà affiché en all-day
      const evDate = ev.date;
      const dayIdx = weekDays.findIndex(
        (d) => d.toISOString().slice(0, 10) === evDate
      );
      if (dayIdx === -1) return;
      // Calculer la position top/height selon l'heure
      const startHour = parseInt(ev.startTime.split(":")[0]);
      const startMin = parseInt(ev.startTime.split(":")[1]);
      const endHour = parseInt(ev.endTime.split(":")[0]);
      const endMin = parseInt(ev.endTime.split(":")[1]);
      const hourHeight = 48; // px, doit matcher le CSS
      const gridStart =
        (startHour - 7) * hourHeight + (startMin / 60) * hourHeight + 32; // +32 pour all-day
      const gridEnd =
        (endHour - 7) * hourHeight + (endMin / 60) * hourHeight + 32;
      const top = gridStart;
      const height = Math.max(gridEnd - gridStart, 24); // min 24px
      // Sélecteur colonne : (i * 8) + 1 + dayIdx + 8 (pour all-day)
      const colIdx = (startHour - 7) * 8 + 1 + dayIdx + 8; // +8 pour la ligne all-day
      const dayCol = daysGrid.children[colIdx];
      if (!dayCol) return;
      // Empilement si overlap (simple)
      let overlapCount = 0;
      for (let c = 0; c < dayCol.children.length; c++) {
        const child = dayCol.children[c];
        if (child.className === "event-block") overlapCount++;
      }
      // Créer le bloc event
      const eventBlock = document.createElement("div");
      eventBlock.className = "event-block";
      eventBlock.style.top = top + "px";
      eventBlock.style.height = height + "px";
      eventBlock.style.background = "#f7faff";
      eventBlock.style.borderLeftColor = ev.color || "#007aff";
      eventBlock.style.left = overlapCount * 8 + "px";
      eventBlock.style.width = `calc(100% - ${overlapCount * 8 + 8}px)`;
      eventBlock.innerHTML = `<div class=\"event-title\">${ev.title}</div><div class=\"event-client\">${ev.client}</div><div class=\"event-time\">${ev.startTime} - ${ev.endTime}</div>`;
      eventBlock.setAttribute("data-color", ev.color || "#007aff");
      eventBlock.style.position = "absolute";
      eventBlock.onclick = () => openCalendarModal("Détail réservation", [ev]);
      dayCol.appendChild(eventBlock);
    });
    // Ligne rouge "now"
    const now = new Date();
    if (weekDays.some((d) => d.toDateString() === now.toDateString())) {
      const nowHour = now.getHours();
      const nowMin = now.getMinutes();
      if (nowHour >= 7 && nowHour <= 21) {
        const nowLine = document.createElement("div");
        nowLine.className = "now-line";
        nowLine.style.top = (nowHour - 7) * 48 + (nowMin / 60) * 48 + 32 + "px";
        daysGrid.appendChild(nowLine);
      }
    }
  }

  renderDayView() {
    const dayEventsColumn = document.getElementById("dayEventsColumn");
    if (!dayEventsColumn) return;
    dayEventsColumn.innerHTML = "";
    dayEventsColumn.style.minHeight = "900px";
    dayEventsColumn.style.position = "relative";

    // Header sticky (date)
    const date = this.currentDate;
    const dayName = dayNamesFr[date.getDay()];
    const header = document.createElement("div");
    header.className = "day-header";
    header.style.position = "sticky";
    header.style.top = "0";
    header.style.background = "#fff";
    header.style.zIndex = "10";
    header.innerHTML = `<div style='font-size:1.5em;font-weight:700;'>${date.toLocaleDateString(
      "en-US",
      { month: "long", day: "numeric", year: "numeric" }
    )}</div><div style='font-size:1.1em;color:#6e6e73;'>${dayName}</div>`;
    dayEventsColumn.appendChild(header);

    // All-day events
    const allDayRow = document.createElement("div");
    allDayRow.className = "all-day-row";
    const allDayLabel = document.createElement("div");
    allDayLabel.className = "all-day-label";
    allDayLabel.textContent = "all-day";
    allDayRow.appendChild(allDayLabel);
    const allDayCell = document.createElement("div");
    allDayCell.className = "all-day-cell";
    allDayCell.style.width = "100%";
    const allDayEvents = this.events.filter(
      (ev) =>
        ev.date === date.toISOString().slice(0, 10) &&
        ev.startTime === "00:00" &&
        ev.endTime === "23:59"
    );
    allDayEvents.forEach((ev) => {
      const evBlock = document.createElement("div");
      evBlock.className = "event-block";
      evBlock.style.position = "relative";
      evBlock.style.top = "2px";
      evBlock.style.height = "28px";
      evBlock.style.background = "#fff";
      evBlock.style.borderLeftColor = ev.color || "#007aff";
      evBlock.innerHTML = `<div class=\"event-title\">${ev.title}</div><div class=\"event-client\">${ev.client}</div>`;
      evBlock.onclick = () => openCalendarModal("Détail réservation", [ev]);
      allDayCell.appendChild(evBlock);
    });
    allDayRow.appendChild(allDayCell);
    dayEventsColumn.appendChild(allDayRow);

    // Grille horaire (9h-17h, demi-heures pointillées)
    const hours = [];
    for (let h = 9; h <= 17; h++) {
      hours.push(h.toString().padStart(2, "0") + ":00");
    }
    for (let i = 0; i < hours.length; i++) {
      // Heure
      const hourCell = document.createElement("div");
      hourCell.className = "hour-cell";
      hourCell.textContent = hours[i];
      hourCell.style.borderRight = "1px solid #e5e5ea";
      hourCell.style.width = "60px";
      hourCell.style.display = "inline-block";
      hourCell.style.verticalAlign = "top";
      hourCell.style.height = "48px";
      dayEventsColumn.appendChild(hourCell);
      // Colonne events
      const dayCol = document.createElement("div");
      dayCol.className = "day-col";
      dayCol.style.position = "relative";
      dayCol.style.display = "inline-block";
      dayCol.style.width = "calc(100% - 60px)";
      dayCol.style.height = "48px";
      dayCol.style.borderBottom = "1px solid #e5e5ea";
      // Demi-heure pointillée
      const halfHour = document.createElement("div");
      halfHour.className = "half-hour";
      dayCol.appendChild(halfHour);
      dayEventsColumn.appendChild(dayCol);
    }
    // Positionner les events (hors all-day)
    const todayEvents = this.events.filter(
      (ev) =>
        ev.date === date.toISOString().slice(0, 10) &&
        !(ev.startTime === "00:00" && ev.endTime === "23:59")
    );
    const processedDayEvents = this.processOverlappingEvents(todayEvents);
    processedDayEvents.forEach((ev) => {
      const startHour = parseInt(ev.startTime.split(":")[0]);
      const startMin = parseInt(ev.startTime.split(":")[1]);
      const endHour = parseInt(ev.endTime.split(":")[0]);
      const endMin = parseInt(ev.endTime.split(":")[1]);
      const hourHeight = 48;
      const gridStart =
        (startHour - 9) * hourHeight + (startMin / 60) * hourHeight;
      const gridEnd = (endHour - 9) * hourHeight + (endMin / 60) * hourHeight;
      const top = gridStart;
      const height = Math.max(gridEnd - gridStart, 24);
      // Chercher la colonne events du jour (toujours la même)
      const dayCols = Array.from(dayEventsColumn.querySelectorAll(".day-col"));
      const dayCol = dayCols[startHour - 9] || dayCols[0];
      if (!dayCol) return;
      const eventBlock = document.createElement("div");
      eventBlock.className = "event-block";
      eventBlock.style.top = top + "px";
      eventBlock.style.height = height + "px";
      eventBlock.style.background = "#f7faff";
      eventBlock.style.borderLeftColor = ev.color;
      eventBlock.style.left = ev.left || "4px";
      eventBlock.style.width = ev.width || "calc(100% - 8px)";
      eventBlock.innerHTML = `<div class=\"event-title\"><b>${ev.service}</b></div><div class=\"event-employee\" style='color:${ev.color};font-weight:600;'>${ev.employee}</div><div class=\"event-client\" style='font-size:0.92em;color:#888;'>${ev.client}</div><div class=\"event-time\">${ev.startTime} - ${ev.endTime}</div>`;
      eventBlock.setAttribute("data-color", ev.color);
      eventBlock.style.position = "absolute";
      eventBlock.onclick = () => openCalendarModal("Détail réservation", [ev]);
      dayCol.appendChild(eventBlock);
    });
    // Ligne rouge "now"
    const now = new Date();
    if (date.toDateString() === now.toDateString()) {
      const nowHour = now.getHours();
      const nowMin = now.getMinutes();
      if (nowHour >= 7 && nowHour <= 21) {
        const nowLine = document.createElement("div");
        nowLine.className = "now-line";
        nowLine.style.top = (nowHour - 7) * 48 + (nowMin / 60) * 48 + 32 + "px";
        dayEventsColumn.appendChild(nowLine);
      }
    }
  }

  renderMonthView() {
    const monthGrid = document.getElementById("monthGrid");
    if (!monthGrid) return;
    monthGrid.innerHTML = "";
    monthGrid.style.display = "grid";
    monthGrid.style.gridTemplateColumns = "repeat(7, 1fr)";
    monthGrid.style.background = "#fff";
    monthGrid.style.borderRadius = "0 0 16px 16px";
    monthGrid.style.overflow = "hidden";
    monthGrid.style.minHeight = "600px";
    // Sticky header (mois/année)
    const date = this.currentDate;
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    const startDay = firstDay.getDay();
    const daysInMonth = lastDay.getDate();
    // Jours de la semaine
    const dayNames = dayNamesShortFr;
    const headerRow = document.createElement("div");
    headerRow.style.display = "contents";
    for (let i = 0; i < 7; i++) {
      const dayHeader = document.createElement("div");
      dayHeader.className = "day-header";
      dayHeader.textContent = dayNames[i];
      headerRow.appendChild(dayHeader);
    }
    monthGrid.appendChild(headerRow);
    // Cases du mois
    let dayNum = 1;
    const totalCells = Math.ceil((startDay + daysInMonth) / 7) * 7;
    for (let i = 0; i < totalCells; i++) {
      const dayCell = document.createElement("div");
      dayCell.className = "day-col";
      dayCell.style.minHeight = "90px";
      dayCell.style.borderBottom = "1px solid #e5e5ea";
      dayCell.style.borderRight = "1px solid #e5e5ea";
      dayCell.style.position = "relative";
      if (i % 7 === 6) dayCell.style.borderRight = "none";
      if (i >= startDay && dayNum <= daysInMonth) {
        dayCell.innerHTML = `<div style='font-weight:600;font-size:1.1em;color:#222;'>${dayNum}</div>`;
        const cellDate = new Date(date.getFullYear(), date.getMonth(), dayNum);
        // Events du jour
        const cellEvents = this.events.filter(
          (ev) => ev.date === cellDate.toISOString().slice(0, 10)
        );
        const maxToShow = 3;
        cellEvents.slice(0, maxToShow).forEach((ev) => {
          const evDot = document.createElement("div");
          evDot.className = "event-block";
          evDot.style.position = "relative";
          evDot.style.height = "22px";
          evDot.style.margin = "2px 0";
          evDot.style.background = "#f7faff";
          evDot.style.borderLeftColor = ev.color || "#007aff";
          evDot.innerHTML = `<span class=\"event-title\">${ev.title}</span>`;
          evDot.setAttribute("data-color", ev.color || "#007aff");
          evDot.onclick = () => openCalendarModal("Détail réservation", [ev]);
          dayCell.appendChild(evDot);
        });
        if (cellEvents.length > maxToShow) {
          const moreBtn = document.createElement("div");
          moreBtn.style.color = "#007aff";
          moreBtn.style.fontSize = "0.95em";
          moreBtn.style.cursor = "pointer";
          moreBtn.style.marginTop = "2px";
          moreBtn.textContent = `+${cellEvents.length - maxToShow} autres`;
          moreBtn.onclick = () =>
            openCalendarModal(
              `Événements du ${dayNum} ${dayNames[cellDate.getDay()]}`,
              cellEvents
            );
          dayCell.appendChild(moreBtn);
        }
        dayNum++;
      } else {
        dayCell.style.background = "#f7f7fa";
      }
      dayCell.onclick = () => this.openDayModal(cellDate);
      monthGrid.appendChild(dayCell);
    }
  }

  // --- Vue Matrice ---
  // Correction de la vue Matrice : n'afficher que les réservations du jour sélectionné
  renderMatrixView() {
    const matrixGrid = document.getElementById("matrixGrid");
    if (!matrixGrid) return;
    matrixGrid.innerHTML = "";
    // Date sélectionnée
    const dateStr = this.currentDate.toISOString().slice(0, 10);
    // En-têtes employés
    const headerRow = document.createElement("div");
    headerRow.className = "matrix-header-row";
    headerRow.style.display = "flex";
    headerRow.appendChild(document.createElement("div")); // coin vide
    this.employees.forEach((emp) => {
      const cell = document.createElement("div");
      cell.className = "matrix-header-cell";
      cell.textContent = emp.name;
      cell.style.background = emp.color;
      cell.style.flex = "1";
      headerRow.appendChild(cell);
    });
    matrixGrid.appendChild(headerRow);
    // Heures (9h-17h)
    for (let h = 9; h <= 17; h++) {
      const row = document.createElement("div");
      row.className = "matrix-row";
      row.style.display = "flex";
      // Colonne heure
      const hourCell = document.createElement("div");
      hourCell.className = "matrix-hour-cell";
      hourCell.textContent = h.toString().padStart(2, "0") + ":00";
      hourCell.style.width = "60px";
      row.appendChild(hourCell);
      // Colonnes employés
      this.employees.forEach((emp) => {
        const cell = document.createElement("div");
        cell.className = "matrix-cell";
        cell.style.flex = "1";
        cell.style.position = "relative";
        // Events de cet employé à cette heure ET ce jour
        const events = this.filteredEvents.filter(
          (ev) =>
            (ev.employee_id == emp.id || ev.employee == emp.name) &&
            ev.date === dateStr &&
            parseInt(ev.startTime.split(":")[0]) === h
        );
        events.forEach((ev) => {
          const eventBlock = document.createElement("div");
          eventBlock.className = "event-block";
          eventBlock.style.background = "#f7faff";
          eventBlock.style.borderLeftColor = emp.color;
          eventBlock.innerHTML = `<div class=\"event-title\"><b>${ev.service}</b></div><div class=\"event-employee\" style='color:${emp.color};font-weight:600;'>${emp.name}</div><div class=\"event-client\" style='font-size:0.92em;color:#888;'>${ev.client}</div><div class=\"event-time\">${ev.startTime} - ${ev.endTime}</div>`;
          eventBlock.onclick = () =>
            openCalendarModal("Détail réservation", [ev]);
          cell.appendChild(eventBlock);
        });
        row.appendChild(cell);
      });
      matrixGrid.appendChild(row);
    }
  }
  // --- Clic sur case de jour (semaine/mois) ---
  openDayModal(dateObj) {
    const dateStr = dateObj.toISOString().slice(0, 10);
    const events = this.filteredEvents.filter((ev) => ev.date === dateStr);
    if (events.length === 0) return;
    openCalendarModal(
      "Réservations du " +
        dateObj.toLocaleDateString("fr-FR", {
          weekday: "long",
          day: "numeric",
          month: "long",
        }),
      events
    );
  }

  getWeekDays() {
    const startOfWeek = new Date(this.currentDate);
    const day = startOfWeek.getDay();
    const diff = startOfWeek.getDate() - day;
    startOfWeek.setDate(diff);
    const days = [];
    for (let i = 0; i < 7; i++) {
      const date = new Date(startOfWeek);
      date.setDate(startOfWeek.getDate() + i);
      days.push(date);
    }
    return days;
  }

  getMonthDays() {
    const year = this.currentDate.getFullYear();
    const month = this.currentDate.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(firstDay.getDate() - firstDay.getDay());
    const days = [];
    const current = new Date(startDate);
    for (let i = 0; i < 42; i++) {
      days.push(new Date(current));
      current.setDate(current.getDate() + 1);
    }
    return days;
  }

  getEventsForDate(date) {
    const dateStr = date.toISOString().split("T")[0];
    return this.filteredEvents.filter((event) => event.date === dateStr);
  }

  isToday(date) {
    const today = new Date();
    return date.toDateString() === today.toDateString();
  }

  createEventElement(event, detailed = false) {
    const eventEl = document.createElement("div");
    eventEl.className = `event ${event.color}`;
    const position = this.calculateEventPosition(event, detailed);
    eventEl.style.top = position.top;
    eventEl.style.height = position.height;
    eventEl.style.left = event.left || "4px";
    eventEl.style.width = event.width || "calc(100% - 8px)";
    if (detailed) {
      eventEl.innerHTML = `
        <div class="event-title"><span class="event-icon">${event.icon}</span>${
        event.title
      }</div>
        <div class="event-time">${event.startTime} - ${event.endTime}</div>
        <div class="event-client">${event.client || ""}</div>
        <div class="event-service">${event.service || ""}</div>
      `;
    } else {
      eventEl.innerHTML = `
        <div class="event-title"><span class="event-icon">${event.icon}</span>${
        event.title
      }</div>
        <div class="event-time">${event.startTime} - ${event.endTime}</div>
        ${event.client ? `<div class="event-client">${event.client}</div>` : ""}
      `;
    }
    return eventEl;
  }

  calculateEventPosition(event, detailedView = false) {
    const [startHour, startMinute] = event.startTime.split(":").map(Number);
    const [endHour, endMinute] = event.endTime.split(":").map(Number);
    const startMinutesFromNine = (startHour - 9) * 60 + startMinute;
    const endMinutesFromNine = (endHour - 9) * 60 + endMinute;
    const durationMinutes = endMinutesFromNine - startMinutesFromNine;
    const hourHeight = detailedView ? 100 : 80;
    const pixelsPerMinute = hourHeight / 60;
    return {
      top: `${startMinutesFromNine * pixelsPerMinute}px`,
      height: `${durationMinutes * pixelsPerMinute}px`,
    };
  }

  processOverlappingEvents(events, detailed = false) {
    events.sort((a, b) => {
      const timeA = parseInt(a.startTime.replace(":", ""));
      const timeB = parseInt(b.startTime.replace(":", ""));
      return timeA - timeB;
    });
    const processedEvents = [];
    const columns = [];
    events.forEach((event) => {
      const eventStart = parseInt(event.startTime.replace(":", ""));
      const eventEnd = parseInt(event.endTime.replace(":", ""));
      let placed = false;
      for (let i = 0; i < columns.length; i++) {
        let overlaps = false;
        for (const colEvent of columns[i]) {
          const colEventStart = parseInt(colEvent.startTime.replace(":", ""));
          const colEventEnd = parseInt(colEvent.endTime.replace(":", ""));
          if (!(eventEnd <= colEventStart || eventStart >= colEventEnd)) {
            overlaps = true;
            break;
          }
        }
        if (!overlaps) {
          columns[i].push(event);
          event.column = i;
          placed = true;
          break;
        }
      }
      if (!placed) {
        columns.push([event]);
        event.column = columns.length - 1;
      }
      processedEvents.push(event);
    });
    processedEvents.forEach((event) => {
      const totalColumns = columns.length;
      if (totalColumns > 1) {
        event.width = `calc(${100 / totalColumns}% - 8px)`;
        event.left = `calc(${event.column * (100 / totalColumns)}% + 4px)`;
      } else {
        event.width = "calc(100% - 8px)";
        event.left = "4px";
      }
    });
    return processedEvents;
  }

  updateCurrentTimeLine() {
    const currentTimeLine = document.getElementById("currentTimeLine");
    if (!currentTimeLine) return;
    const now = new Date();
    const today = new Date();
    if (
      this.currentDate.toDateString() !== today.toDateString() ||
      (this.currentView !== "day" && this.currentView !== "week")
    ) {
      currentTimeLine.style.display = "none";
      return;
    }
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    if (currentHour < 9 || currentHour >= 17) {
      currentTimeLine.style.display = "none";
      return;
    }
    this.updateCurrentTimeLinePosition(currentTimeLine);
  }

  updateCurrentTimeLinePosition(timeLineEl) {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    if (currentHour < 9 || currentHour >= 17) {
      timeLineEl.style.display = "none";
      return;
    }
    const hourHeight = this.currentView === "day" ? 100 : 80;
    const pixelsPerMinute = hourHeight / 60;
    const minutesFromNine = (currentHour - 9) * 60 + currentMinute;
    const position = minutesFromNine * pixelsPerMinute;
    timeLineEl.style.top = `${position}px`;
    timeLineEl.style.display = "flex";
  }

  openModal() {
    const modal = document.getElementById("addEventModal");
    const today = new Date().toISOString().split("T")[0];
    document.getElementById("eventDate").value = today;
    document.getElementById("startTime").value = "09:00";
    document.getElementById("endTime").value = "10:00";
    modal.classList.add("show");
  }

  closeModal() {
    const modal = document.getElementById("addEventModal");
    modal.classList.remove("show");
    document.getElementById("eventForm").reset();
  }

  handleFormSubmit(e) {
    e.preventDefault();
    const clientName = document.getElementById("clientName").value;
    const serviceName = document.getElementById("serviceName").value;
    const eventTitle = document.getElementById("eventTitle").value;
    const eventDate = document.getElementById("eventDate").value;
    const startTime = document.getElementById("startTime").value;
    const endTime = document.getElementById("endTime").value;
    const serviceType = document.getElementById("serviceType").value;
    const eventColor = document.getElementById("eventColor").value;
    const serviceIcons = {
      facial: "🧴",
      manicure: "💅",
      massage: "💆",
      hair: "✂️",
      consultation: "💬",
      other: "✨",
    };
    const newEvent = {
      id: Date.now(),
      title: eventTitle || `${serviceName} - ${clientName}`,
      client: clientName,
      service: serviceName,
      date: eventDate,
      startTime: startTime,
      endTime: endTime,
      type: serviceType,
      color: eventColor,
      icon: serviceIcons[serviceType],
    };
    this.events.push(newEvent);
    this.filteredEvents = [...this.events];
    this.renderCurrentView();
    this.closeModal();
  }

  handleSearch(query) {
    if (!query.trim()) {
      this.filteredEvents = [...this.events];
    } else {
      this.filteredEvents = this.events.filter(
        (event) =>
          event.title.toLowerCase().includes(query.toLowerCase()) ||
          event.client.toLowerCase().includes(query.toLowerCase()) ||
          event.service.toLowerCase().includes(query.toLowerCase())
      );
    }
    this.renderCurrentView();
  }

  initializeEmployeeChips() {
    const container = document.getElementById("employeeChips");
    if (!container) return;

    // Add "All" chip
    const allChip = this.createEmployeeChip({
      id: "all",
      name: "Tous",
      color: "#86868b",
    });
    container.appendChild(allChip);

    // Add employee chips
    this.employees.forEach((employee) => {
      const chip = this.createEmployeeChip(employee);
      container.appendChild(chip);
    });
  }

  createEmployeeChip(employee) {
    const chip = document.createElement("div");
    chip.className = "employee-chip";
    chip.dataset.id = employee.id;

    const avatar = document.createElement("div");
    avatar.className = "employee-avatar";
    avatar.style.backgroundColor = employee.color;
    avatar.textContent = employee.name.charAt(0).toUpperCase();

    const name = document.createElement("span");
    name.textContent = employee.name;

    chip.appendChild(avatar);
    chip.appendChild(name);

    chip.addEventListener("click", () =>
      this.toggleEmployeeFilter(employee.id)
    );

    return chip;
  }

  toggleEmployeeFilter(employeeId) {
    if (employeeId === "all") {
      this.selectedEmployees.clear();
    } else {
      if (this.selectedEmployees.has(employeeId)) {
        this.selectedEmployees.delete(employeeId);
      } else {
        this.selectedEmployees.add(employeeId);
      }
    }

    // Update chip appearances
    document.querySelectorAll(".employee-chip").forEach((chip) => {
      const isSelected =
        chip.dataset.id === "all"
          ? this.selectedEmployees.size === 0
          : this.selectedEmployees.has(chip.dataset.id);
      chip.classList.toggle("active", isSelected);
    });

    this.renderCurrentView();
  }

  // Méthodes pour afficher les filtres dynamiquement
  renderEmployeeChips() {
    const container = document.getElementById("employeeChips");
    if (!container) return;
    container.innerHTML = "";
    // Chip "Tous"
    const allChip = document.createElement("div");
    allChip.className = "employee-chip active";
    allChip.textContent = "Tous";
    allChip.onclick = () => {
      this.selectedEmployees.clear();
      this.renderEmployeeChips();
      this.renderCurrentView();
    };
    container.appendChild(allChip);
    // Chips employés
    this.employees.forEach((emp) => {
      const chip = document.createElement("div");
      chip.className = "employee-chip";
      chip.textContent = emp.name;
      chip.style.background = emp.color;
      chip.onclick = () => {
        if (this.selectedEmployees.has(emp.id)) {
          this.selectedEmployees.delete(emp.id);
        } else {
          this.selectedEmployees.add(emp.id);
        }
        this.renderEmployeeChips();
        this.renderCurrentView();
      };
      if (
        this.selectedEmployees.size === 0 ||
        this.selectedEmployees.has(emp.id)
      ) {
        chip.classList.add("active");
      }
      container.appendChild(chip);
    });
  }

  renderServiceFilter() {
    let select = document.getElementById("serviceFilter");
    if (!select) {
      select = document.createElement("select");
      select.id = "serviceFilter";
      select.className = "ib-input";
      const header = document.querySelector(".header-center");
      if (header) header.appendChild(select);
    }
    select.innerHTML =
      '<option value="">Tous les services</option>' +
      this.services
        .map((s) => `<option value="${s.id}">${s.name}</option>`)
        .join("");
    select.onchange = () => {
      this.selectedService = select.value;
      this.renderCurrentView();
    };
  }

  renderCategoryFilter() {
    let select = document.getElementById("categoryFilter");
    if (!select) {
      select = document.createElement("select");
      select.id = "categoryFilter";
      select.className = "ib-input";
      const header = document.querySelector(".header-center");
      if (header) header.appendChild(select);
    }
    select.innerHTML =
      '<option value="">Toutes les catégories</option>' +
      this.categories
        .map((c) => `<option value="${c.id}">${c.name}</option>`)
        .join("");
    select.onchange = () => {
      this.selectedCategory = select.value;
      this.renderCurrentView();
    };
  }

  // Appliquer le filtrage dans renderCurrentView
  get filteredEvents() {
    let events = this.events;
    if (this.selectedEmployees && this.selectedEmployees.size > 0) {
      events = events.filter(
        (ev) =>
          this.selectedEmployees.has(ev.employee_id) ||
          this.selectedEmployees.has(ev.employee)
      );
    }
    if (this.selectedService) {
      events = events.filter(
        (ev) =>
          ev.service_id == this.selectedService ||
          ev.service == this.selectedService
      );
    }
    if (this.selectedCategory) {
      events = events.filter(
        (ev) =>
          ev.category_id == this.selectedCategory ||
          ev.category == this.selectedCategory
      );
    }
    return events;
  }
}

// Correction de la modale de détail (openCalendarModal)
function openCalendarModal(title, events) {
  const modal = document.getElementById("beauty-calendar-modal");
  if (!modal) return;
  modal.querySelector(".modal-title").textContent = title;
  const eventsContainer = modal.querySelector(".modal-events");
  eventsContainer.innerHTML = "";
  events.sort((a, b) => (a.start > b.start ? 1 : -1));
  events.forEach((ev) => {
    const evBlock = document.createElement("div");
    evBlock.className = "event-block";
    evBlock.style.background = "#f7faff";
    evBlock.style.borderLeft = "5px solid " + getEmployeeColor(ev.employee);
    evBlock.style.borderRadius = "12px";
    evBlock.style.boxShadow = "0 2px 12px 0 rgba(0,0,0,0.07)";
    evBlock.style.marginBottom = "1.2em";
    evBlock.innerHTML = `<div class=\"event-title\"><b>${
      ev.service
    }</b></div><div class=\"event-employee\" style='color:${getEmployeeColor(
      ev.employee
    )};font-weight:600;'>${
      ev.employee
    }</div><div class=\"event-client\"><b>Client :</b> ${
      ev.client
    }</div><div class=\"event-time\"><b>Heure :</b> ${ev.startTime} - ${
      ev.endTime
    }</div>`;
    eventsContainer.appendChild(evBlock);
  });
  modal.style.display = "flex";
  modal.onclick = (e) => {
    if (e.target === modal) modal.style.display = "none";
  };
}
