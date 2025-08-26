<?php
// Page de vue calendrier - Google Calendar Style
require_once plugin_dir_path(__FILE__) . '../includes/class-services.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-employees.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-categories.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-bookings.php';

// Récupération des données
$bookings = IB_Bookings::get_all();
$services = IB_Services::get_all();
$employees = IB_Employees::get_all();
$categories = IB_Categories::get_all();

// Configuration des couleurs des employés
$employee_colors = [];
$employee_palette = ['#4f8cff','#00c48c','#ffb300','#ff4f64','#7c3aed','#ff6f00','#00bcd4','#8bc34a','#e67e22','#e84393','#00b894','#636e72','#fdcb6e','#0984e3','#d35400','#6c5ce7'];

foreach ($employees as $i => $emp) {
    $employee_colors[$emp->id] = $employee_palette[$i % count($employee_palette)];
}

// Configuration des horaires d'ouverture
$opening_time = get_option('ib_opening_time', '08:00');
$closing_time = get_option('ib_closing_time', '20:00');

// Données pour le JavaScript
wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap');
wp_enqueue_style('ib-google-calendar', plugins_url('../assets/css/google-calendar.css', __FILE__));
wp_enqueue_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js', [], '6.1.8', true);
wp_enqueue_script('ib-google-calendar', plugins_url('../assets/js/google-calendar.js', __FILE__), ['jquery', 'fullcalendar'], '1.0.0', true);

// Localisation des données pour le JavaScript
wp_localize_script('ib-google-calendar', 'ibCalendarVars', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('ib_calendar_nonce'),
    'employee_colors' => $employee_colors,
    'opening_time' => $opening_time,
    'closing_time' => $closing_time,
    'employees' => array_map(function($emp) use ($employee_colors) {
        return [
            'id' => $emp->id,
            'name' => $emp->name,
            'color' => $employee_colors[$emp->id] ?? '#1a73e8',
            'role' => $emp->role ?? 'employé'
        ];
    }, $employees)
]);
?>

<div class="google-calendar-container">
    <div class="google-calendar-header">
        <div class="google-calendar-title">
            <h1>Agenda</h1>
        </form>
        <div class="ib-employee-bar">
            <div class="ib-employee-chip ib-employee-chip-all active" data-employee="">
                <span class="ib-employee-avatar ib-employee-avatar-all"><svg width="24" height="24" fill="none" stroke="#888" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg></span>
                <span class="ib-employee-name">Tous employés</span>
            </div>
            <?php foreach($employees as $e): ?>
                <?php if (isset($e->role) && mb_strtolower(trim($e->role), 'UTF-8') === 'employé'): ?>
                <div class="ib-employee-chip" data-employee="<?php echo $e->id; ?>">
                    <span class="ib-employee-avatar" style="background:<?php echo $employee_colors[$e->id]; ?>;color:#fff;">
                        <?php echo strtoupper(mb_substr($e->name,0,1)); ?>
                    </span>
                    <span class="ib-employee-name"><?php echo esc_html($e->name); ?></span>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="ib-calendar-wrapper">
            <div id="booking-calendar"></div>
            <div id="ib-calendar-no-results" style="display:none;text-align:center;color:#888;margin-top:2em;font-size:1.2em;">Aucun résultat pour ces filtres.</div>
        </div>
        <div id="ib-calendar-modal" class="ib-modal-bg" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;align-items:center;justify-content:center;z-index:99999;">
            <div class="ib-modal">
                <button id="ib-calendar-modal-close" class="ib-modal-close" type="button">&times;</button>
                <div id="ib-calendar-modal-content"></div>
            </div>
        </div>
    </div>
</div>
<style>
body, .ib-calendar-page, .ib-calendar-content {
    font-family: 'Inter', 'Playfair Display', Arial, sans-serif;
    background: #f8f6fa;
}
.fc {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 24px #e9aebc22;
    padding: 1.2em 1.2em 0.5em 1.2em;
}
.fc-timegrid-slot-label {
    color: #bfa2c7;
    font-size: 1.08em;
    font-family: 'Inter', Arial, sans-serif;
    font-weight: 600;
    background: #fff;
    border: none;
}
.fc-timegrid-axis-cushion {
    color: #bfa2c7;
    font-size: 1.08em;
    font-family: 'Inter', Arial, sans-serif;
    font-weight: 600;
}
.fc-scrollgrid-section-header, .fc-col-header-cell {
    background: #fbeff3;
    color: #e9aebc;
    font-weight: 700;
    font-size: 1.1em;
    border: none;
}
.fc-timegrid-slot {
    border-color: #fbeff3;
}
.fc-event {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    z-index: 3 !important;
}
.ib-event-dot {
    display: inline-block;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    box-shadow: 0 2px 8px #e9aebc33;
    border: 2.5px solid #fff;
    margin: 0 2px;
    cursor: pointer;
    transition: box-shadow 0.18s, transform 0.13s;
}
.ib-event-dot:hover, .fc-event:focus .ib-event-dot {
    box-shadow: 0 8px 32px #e9aebc55, 0 2px 12px #e9aebc33;
    transform: scale(1.18);
    z-index: 10;
}
.ib-event-tooltip {
    position: absolute;
    z-index: 9999;
    background: #fff;
    color: #b95c8a;
    border-radius: 1em;
    box-shadow: 0 8px 32px #e9aebc33;
    padding: 0.8em 1.2em;
    font-size: 1.08em;
    font-family: 'Inter', 'Playfair Display', Arial, sans-serif;
    font-weight: 600;
    pointer-events: none;
    border: 1.5px solid #e9aebc;
    min-width: 120px;
    text-align: center;
    opacity: 0.98;
    transition: opacity 0.18s;
}
.ib-calendar-filters .ib-form-group {
    position: relative;
    margin-bottom: 0;
    flex: 1 1 170px;
    min-width: 170px;
}
.ib-calendar-filters .ib-label {
    position: absolute;
    left: 1.1em;
    top: 1.1em;
    color: #bfa2c7;
    font-size: 1em;
    pointer-events: none;
    background: transparent;
    transition: 0.18s;
    padding: 0 0.2em;
    z-index: 2;
}
.ib-calendar-filters .ib-input:focus + .ib-label,
.ib-calendar-filters .ib-input:not([value=""]) + .ib-label,
.ib-calendar-filters .ib-input:valid + .ib-label,
.ib-calendar-filters select:focus + .ib-label,
.ib-calendar-filters select:not([value=""]) + .ib-label {
    top: -0.7em;
    left: 0.9em;
    font-size: 0.92em;
    color: #e9aebc;
    background: #fff;
    padding: 0 0.3em;
}
.ib-calendar-filters .ib-input:focus {
    border: 2px solid #e9aebc;
    box-shadow: 0 0 0 3px #e9aebc33;
    background: #fff;
}
.ib-calendar-filters .ib-btn.accent {
    background: #e9aebc;
    color: #fff;
    border: none;
    border-radius: 14px;
    padding: 0.7em 1.5em;
    font-size: 1.1em;
    font-weight: 700;
    margin-right: 0.5em;
    margin-bottom: 0.5em;
    box-shadow: 0 2px 8px #e9aebc33;
    transition: background 0.18s, color 0.18s, transform 0.12s;
    display: flex;
    align-items: center;
    gap: 0.5em;
}
.ib-calendar-filters .ib-btn.accent:hover {
    background: #d48ca6;
    color: #fff;
    transform: translateY(-2px) scale(1.04);
}
.ib-event-modern {
    font-family: 'Inter', 'Playfair Display', Arial, sans-serif;
    background: #fbeff3;
    border-radius: 1.3em;
    box-shadow: 0 4px 24px #e9aebc22, 0 1.5px 0 #fff;
    border-left: 5px solid #e9aebc;
    padding: 0.7em 1.1em 0.7em 1.3em;
    display: flex;
    flex-direction: column;
    gap: 0.2em;
    min-width: 120px;
    max-width: 220px;
    margin-bottom: 0.2em;
    transition: box-shadow 0.18s, transform 0.13s;
}
.ib-emp-badge {
    width: 2em;
    height: 2em;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 700;
    font-size: 1.1em;
    box-shadow: 0 2px 8px #e9aebc33;
    background: #e9aebc;
    color: #fff;
    margin-right: 0.5em;
}
.fc-timegrid-event-harness {
    z-index: 2 !important;
}
.fc-timegrid-event-harness + .fc-timegrid-event-harness {
    z-index: 1 !important;
}
.ib-daycell-date {
    font-weight: 700;
    color: #e9aebc;
    font-size: 1.08em;
    margin-bottom: 0.2em;
}
.ib-daycell-events {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    min-height: 28px;
    align-items: center;
    justify-content: flex-start;
    gap: 4px;
    padding: 0 2px;
    max-width: 100%;
}
.ib-event-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    box-shadow: 0 2px 8px #e9aebc33;
    border: 2px solid #fff;
    margin: 0;
    cursor: pointer;
    transition: box-shadow 0.18s, transform 0.13s;
    background: #e9aebc;
    flex-shrink: 0;
}
.ib-event-dot:hover {
    box-shadow: 0 8px 32px #e9aebc55, 0 2px 12px #e9aebc33;
    transform: scale(1.18);
    z-index: 10;
}
.ib-daycell-more {
    font-size: 0.95em;
    color: #e9aebc;
    background: #fbeff3;
    border-radius: 8px;
    padding: 0 0.5em;
    margin-left: 2px;
    font-weight: 600;
    cursor: pointer;
    flex-shrink: 0;
}
.ib-event-tooltip {
    position: absolute;
    z-index: 9999;
    background: #fff;
    color: #b95c8a;
    border-radius: 1em;
    box-shadow: 0 8px 32px #e9aebc33;
    padding: 0.8em 1.2em;
    font-size: 1.08em;
    font-family: 'Inter', 'Playfair Display', Arial, sans-serif;
    font-weight: 600;
    pointer-events: none;
    border: 1.5px solid #e9aebc;
    min-width: 120px;
    text-align: center;
    opacity: 0.98;
    transition: opacity 0.18s;
}
.ib-modal-bg {
    position: fixed;
    left: 0; top: 0; width: 100vw; height: 100vh;
    background: rgba(60,30,60,0.13);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.18s;
}
.ib-modal {
    background: #fff;
    border-radius: 2em;
    box-shadow: 0 8px 48px #e9aebc44, 0 1.5px 0 #fbeff3;
    padding: 2.2em 2em 1.5em 2em;
    max-width: 420px;
    width: 90vw;
    text-align: left;
    position: relative;
    animation: pop-in 0.25s cubic-bezier(0.68,-0.55,0.27,1.55);
}

@keyframes pop-in {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.ib-modal-close {
    position: absolute;
    top: 1.1em;
    right: 1.3em;
    font-size: 2em;
    background: none;
    border: none;
    color: #e9aebc;
    cursor: pointer;
    font-weight: 700;
    transition: color 0.18s;
}
.ib-modal-close:hover { color: #b95c8a; }
.ib-modal-header h2 { font-family: 'Playfair Display', Inter, serif; font-size: 1.3em; margin-bottom: 0.5em; }
.ib-modal-day-card { transition: box-shadow 0.18s; }
.ib-modal-day-card:hover { box-shadow: 0 8px 32px #e9aebc33; }
@media (max-width: 600px) {
    .ib-modal { padding: 1.1em 0.5em 1em 0.5em; max-width: 98vw; }
}
.fc-daygrid-day-events {
  min-height: 28px;
  max-height: 28px;
  display: flex;
  flex-wrap: nowrap;
  align-items: center;
  overflow-x: auto;
  gap: 4px;
  padding: 0 2px;
}
.fc-daygrid-event {
  display: flex;
  align-items: center;
  justify-content: center;
  background: none !important;
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;
  margin: 0 !important;
  min-width: 0;
  min-height: 0;
}
.ib-event-dot {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  box-shadow: 0 2px 8px #e9aebc33;
  border: 2px solid #fff;
  margin: 0;
  cursor: pointer;
  transition: box-shadow 0.18s, transform 0.13s;
  background: #e9aebc;
  flex-shrink: 0;
}
.ib-event-dot:hover {
  box-shadow: 0 8px 32px #e9aebc55, 0 2px 12px #e9aebc33;
  transform: scale(1.18);
  z-index: 10;
}
.fc-daygrid-more-link {
  font-size: 0.95em;
  color: #e9aebc !important;
  background: #fbeff3;
  border-radius: 8px;
  padding: 0 0.5em;
  margin-left: 2px;
  font-weight: 600;
  cursor: pointer;
  flex-shrink: 0;
  border: none;
  box-shadow: none;
  transition: background 0.18s, color 0.18s;
}
.fc-daygrid-more-link:hover {
  background: #e9aebc;
  color: #fff !important;
}
.fc-timegrid-event {
  background: none !important;
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;
  margin: 0 !important;
  min-width: 0;
  min-height: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.fc-timegrid-event .ib-event-dot {
  margin: 0 auto;
}
.fc-timegrid-event-harness {
  background: none !important;
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;
  margin: 0 !important;
}
.fc-timegrid-event-harness .fc-event {
  background: none !important;
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;
  margin: 0 !important;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Palette de couleurs employé (récupérée du PHP)
    const employeeColors = <?php echo json_encode($employee_colors); ?>;
    // Contraste automatique (noir ou blanc selon la couleur de fond)
    function getContrastYIQ(hexcolor){
        hexcolor = hexcolor.replace('#','');
        if(hexcolor.length === 3) hexcolor = hexcolor.split('').map(x=>x+x).join('');
        var r = parseInt(hexcolor.substr(0,2),16);
        var g = parseInt(hexcolor.substr(2,2),16);
        var b = parseInt(hexcolor.substr(4,2),16);
        var yiq = ((r*299)+(g*587)+(b*114))/1000;
        return (yiq >= 180) ? '#22223b' : '#fff';
    }
    // Générer un badge employé
    function getEmployeeBadge(name, color) {
        if (!name) return '';
        const initials = name.split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase();
        return `<span class=\"ib-emp-badge\" style=\"background:${color};color:${getContrastYIQ(color)}\">${initials}</span>`;
    }
    // Prépare la liste complète des événements (non filtrée)
    var allEvents = <?php echo json_encode(array_map(function($b) use ($services, $employees, $employee_colors) {
        $service = array_filter($services, function($s) use ($b) { return $s->id == $b->service_id; });
        $service = reset($service);
        $employee = array_filter($employees, function($e) use ($b) { return $e->id == $b->employee_id; });
        $employee = reset($employee);
        $service_name = $service ? $service->name : 'Service inconnu';
        $employee_name = $employee ? $employee->name : 'Employé inconnu';
        $heure = !empty($b->time) ? $b->time : ( !empty($b->start_time) ? date('H:i', strtotime($b->start_time)) : '09:00' );
        return [
            'id' => $b->id,
            'title' => $service_name,
            'start' => $b->date . 'T' . $heure,
            'end' => $b->date . 'T' . $heure,
            'color' => $employee_colors[$b->employee_id] ?? '#e9aebc',
            'extendedProps' => [
                'employee' => $employee_name,
                'employee_color' => $employee_colors[$b->employee_id] ?? '#e9aebc',
                'service' => $service_name,
                'service_id' => $b->service_id,
                'category_id' => $b->category_id ?? null,
                'client' => $b->client_name,
                'status' => $b->status,
                'notes' => $b->notes,
                'date' => $b->date,
                'time' => $heure,
                'employee_id' => $b->employee_id
            ]
        ];
    }, $bookings)); ?>;
    allEvents = allEvents.filter(ev => (ev.extendedProps.status === 'confirmé' || ev.extendedProps.status === 'confirme' || ev.extendedProps.status === 'confirmee'));

    // Variables de filtre
    let currentEmployee = '';
    let currentService = '';
    let currentCategory = '';

    // Fonction de filtrage multi-critères
    function getFilteredEvents() {
        return allEvents.filter(function(ev) {
            let matchEmp = !currentEmployee || ev.extendedProps.employee_id == currentEmployee;
            let matchServ = !currentService || ev.extendedProps.service_id == currentService;
            let matchCat = !currentCategory || ev.extendedProps.category_id == currentCategory;
            return matchEmp && matchServ && matchCat;
        });
    }

    // Initialisation du calendrier
    var calendarEl = document.getElementById("booking-calendar");
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "timeGridWeek",
        slotMinTime: '<?php echo $opening_time; ?>',
        slotMaxTime: '<?php echo $closing_time; ?>',
        allDaySlot: false,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotDuration: '00:30:00',
        slotLabelFormat: { hour: 'numeric', minute: '2-digit', omitZeroMinute: false },
        slotLabelInterval: '01:00',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
        locale: 'fr',
        firstDay: 1,
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEventRows: 1,
        dayMaxEvents: 5,
        moreLinkContent: function(args) {
            return { html: `<span class='fc-daygrid-more-link'>+${args.num}</span>` };
        },
        eventContent: function(arg) {
            // Toutes les vues : pastille simple
            const color = arg.event.extendedProps.employee_color || arg.event.backgroundColor || '#e9aebc';
            const dot = document.createElement('span');
            dot.className = 'ib-event-dot';
            dot.style.background = color;
            dot.title = `${arg.event.title} | ${arg.event.extendedProps.client} | ${arg.event.start.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}`;
            return { domNodes: [dot] };
        },
        eventDidMount: function(info) {
            const event = info.event;
            const eventEl = info.el;
            eventEl.setAttribute('tabindex', '0');
            eventEl.setAttribute('aria-label', `${event.title} avec ${event.extendedProps.employee} pour ${event.extendedProps.client}`);
            eventEl.onmouseenter = () => {
                let tooltip = document.createElement('div');
                tooltip.className = 'ib-event-tooltip';
                tooltip.innerHTML = `<strong>${event.title}</strong><br>${event.extendedProps.client}<br><span style='color:#bfa2c7;'>${event.start.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</span>`;
                document.body.appendChild(tooltip);
                const rect = eventEl.getBoundingClientRect();
                tooltip.style.left = (rect.left + window.scrollX + rect.width/2 - tooltip.offsetWidth/2) + 'px';
                tooltip.style.top = (rect.top + window.scrollY - tooltip.offsetHeight - 8) + 'px';
                eventEl._tooltip = tooltip;
            };
            eventEl.onmouseleave = () => {
                if (eventEl._tooltip) { eventEl._tooltip.remove(); eventEl._tooltip = null; }
            };
        },
        eventClick: function(info) {
            showEventModal(info.event);
        },
        select: function(selectionInfo) {}
    });
    calendar.render();

    // Initialiser le calendrier avec tous les événements par défaut
    calendar.addEventSource(getFilteredEvents());

    // Activer le chip "Tous les employés" par défaut
    document.querySelector('.ib-employee-chip-all').classList.add('active');

    // Fonction pour recharger le calendrier avec les filtres
    function updateCalendar() {
        calendar.removeAllEvents();
        calendar.addEventSource(getFilteredEvents());
    }

    // --- Filtres dynamiques ---
    // Employé (select)
    document.getElementById('ib-calendar-employee').addEventListener('change', function(e) {
        currentEmployee = this.value;
        // Synchronise la barre chips
        document.querySelectorAll('.ib-employee-chip').forEach(function(chip){
            if (!currentEmployee && chip.classList.contains('ib-employee-chip-all')) {
                chip.classList.add('active');
            } else if (chip.getAttribute('data-employee') == currentEmployee) {
                chip.classList.add('active');
            } else {
                chip.classList.remove('active');
            }
        });
        updateCalendar();
    });
    // Service
    document.getElementById('ib-calendar-service').addEventListener('change', function(e) {
        currentService = this.value;
        updateCalendar();
    });
    // Catégorie
    document.getElementById('ib-calendar-category').addEventListener('change', function(e) {
        currentCategory = this.value;
        updateCalendar();
    });
    // Barre employés : filtrage + synchronisation select
    document.querySelectorAll('.ib-employee-chip').forEach(function(chip){
        chip.addEventListener('click', function(){
            document.querySelectorAll('.ib-employee-chip').forEach(c=>c.classList.remove('active'));
            chip.classList.add('active');
            var empId = chip.getAttribute('data-employee');
            currentEmployee = empId;
            // Synchronise le select
            document.getElementById('ib-calendar-employee').value = empId;
            updateCalendar();
        });
    });

    // --- Reste du code (modale, export, etc.) inchangé ---
    function showEventModal(event) {
        var modal = document.getElementById('ib-calendar-modal');
        var modalContent = document.getElementById('ib-calendar-modal-content');
        const color = event.extendedProps.employee_color || event.color || '#4f8cff';
        const textColor = '#22223b';
        modalContent.innerHTML = `
            <div class='ib-modal-header'>
                <h2 style='font-weight:700;color:${color};margin-bottom:0.5em;'>${event.title}</h2>
                <div class='ib-event-meta' style='font-size:1em;color:#888;margin-bottom:0.7em;'>
                    <span class='ib-event-time'>${event.start.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</span>
                    <span class='ib-event-employee' style='margin-left:1em;'><span class='ib-emp-badge' style='background:${color};color:#fff;'>${event.extendedProps.employee ? event.extendedProps.employee[0].toUpperCase() : '?'}</span> <span style='color:${color};font-weight:700;'>${event.extendedProps.employee}</span></span>
                </div>
            </div>
            <div class='ib-modal-body'>
                <div class='ib-event-modern' style='background:${color}22;color:${textColor};padding:1.2em 1.5em;border-radius:1.2em;'>
                    <div><strong>Client :</strong> ${event.extendedProps.client}</div>
                    <div><strong>Service :</strong> ${event.extendedProps.service}</div>
                    <div><strong>Date :</strong> ${event.extendedProps.date}</div>
                    <div><strong>Heure :</strong> ${event.extendedProps.time}</div>
                    ${event.extendedProps.notes ? `<div><strong>Notes :</strong> ${event.extendedProps.notes}</div>` : ''}
                </div>
            </div>
        `;
        modal.style.display = 'flex';
    }
    function showDayModal(dateStr) {
        var modal = document.getElementById('ib-calendar-modal');
        var modalContent = document.getElementById('ib-calendar-modal-content');
        const events = getFilteredEvents().filter(ev => ev.extendedProps.date === dateStr);
        if (events.length === 0) {
            modalContent.innerHTML = `<div style='padding:2em;text-align:center;color:#bfa2c7;'>Aucune réservation ce jour.</div>`;
        } else {
            modalContent.innerHTML = `<h2 style='color:#e9aebc;font-weight:800;margin-bottom:1em;'>Réservations du ${dateStr}</h2>` +
                events.map(ev => `
                    <div class='ib-modal-day-card' style='background:${ev.color}22;border-left:4px solid ${ev.color};border-radius:1em;padding:1em 1.2em;margin-bottom:1em;box-shadow:0 2px 12px #e9aebc22;'>
                        <div style='font-weight:700;color:${ev.color};font-size:1.1em;'>${ev.title}</div>
                        <div style='color:#bfa2c7;font-size:0.98em;'>${ev.extendedProps.client}</div>
                        <div style='color:#b95c8a;font-size:0.97em;'>${ev.extendedProps.time}</div>
                        <div style='color:#888;font-size:0.97em;'>${ev.extendedProps.employee}</div>
                    </div>
                `).join('');
        }
        modal.style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('ib-calendar-modal').style.display = 'none';
    }
    document.getElementById('ib-calendar-modal-close').onclick = closeModal;
    document.getElementById('ib-calendar-modal').onclick = function(e) {
        if (e.target === this) closeModal();
    };
    calendar.setOption('eventClick', function(info) {
        showEventModal(info.event);
    });
    document.addEventListener('click', function(e) {
        const dayCell = e.target.closest('.fc-daygrid-day-frame');
        if (dayCell && !e.target.classList.contains('ib-event-dot')) {
            const dateStr = dayCell.parentElement.getAttribute('data-date');
            if (dateStr) showDayModal(dateStr);
        }
    });
    document.getElementById('ib-calendar-export').addEventListener('click', function() {
        exportToCSV();
    });
    function exportToCSV() {
        var data = getFilteredEvents().map(event => [
            event.title,
            event.extendedProps.employee,
            event.extendedProps.client,
            event.start,
            event.end,
            event.extendedProps.service,
            event.extendedProps.notes
        ]);
        var csvContent = "data:text/csv;charset=utf-8,";
       var headers = ["Service", "Employé", "Client", "Début", "Fin", "Service", "Notes"];
        csvContent += headers.join(",") + "\n";
        data.forEach(function(rowArray) {
            var row = rowArray.join(",");
            csvContent += row + "\n";
        });
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "planning.csv");
        document.body.appendChild(link);
        link.click();
    }
});
</script> 