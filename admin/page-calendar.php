<?php
// Page de vue calendrier
require_once plugin_dir_path(__FILE__) . '../includes/class-services.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-employees.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-categories.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-bookings.php';
$bookings = IB_Bookings::get_all();
$services = IB_Services::get_all();
$employees = IB_Employees::get_all();
$categories = IB_Categories::get_all();
// Couleur unique par employé
$employee_colors = [];
$employee_palette = ['#4f8cff','#00c48c','#ffb300','#ff4f64','#7c3aed','#ff6f00','#00bcd4','#8bc34a','#e67e22','#e84393','#00b894','#636e72','#fdcb6e','#0984e3','#d35400','#6c5ce7'];
foreach ($employees as $i => $emp) {
    $employee_colors[$emp->id] = $employee_palette[$i % count($employee_palette)];
}
$opening_time = get_option('ib_opening_time', '09:00');
$closing_time = get_option('ib_closing_time', '19:00');

?>
<div class="ib-calendar-page">
    <div class="ib-calendar-content">
        <h1>Agenda</h1>
        <form id="ib-calendar-filters-form" class="ib-calendar-filters" style="gap:2em;">
            <span class="ib-calendar-filters-title"><svg width="18" height="18" fill="none" stroke="#2b7cff" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg> Filtres</span>
            <div class="ib-form-group" style="min-width:170px;">
                <select id="ib-calendar-employee" class="ib-input" name="employee">
                    <option value="">👤 Tous employés</option>
                    <?php foreach($employees as $e): ?>
                        <?php if (isset($e->role) && mb_strtolower(trim($e->role), 'UTF-8') === 'employé'): ?>
                        <option value="<?php echo $e->id; ?>" data-color="<?php echo $employee_colors[$e->id]; ?>"><?php echo esc_html($e->name); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <label class="ib-label" for="ib-calendar-employee">Employé</label>
            </div>
            <div class="ib-form-group" style="min-width:170px;">
                <select id="ib-calendar-service" class="ib-input" name="service">
                    <option value="">💼 Tous services</option>
                    <?php foreach($services as $s): ?>
                        <option value="<?php echo $s->id; ?>"><?php echo esc_html($s->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="ib-label" for="ib-calendar-service">Service</label>
            </div>
            <div class="ib-form-group" style="min-width:170px;">
                <select id="ib-calendar-category" class="ib-input" name="category">
                    <option value="">📂 Toutes catégories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat->id; ?>"><?php echo esc_html($cat->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="ib-label" for="ib-calendar-category">Catégorie</label>
            </div>
            <button id="ib-calendar-export" type="button" class="ib-btn accent ib-btn-export"><svg width="18" height="18" fill="none" stroke="#e9aebc" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12l7 7 7-7"/></svg> Export CSV</button>
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
        <div class="calendar-header">
            <div class="month-year">Août 2023</div>
            <div class="calendar-nav">
                <button id="prev-month">&lt;</button>
                <button id="today">Aujourd'hui</button>
                <button id="next-month">&gt;</button>
            </div>
            <div class="calendar-view-switch" style="margin-top: 10px; display: flex; gap: 10px;">
                <button class="calendar-view-btn active" data-view="month">Mois</button>
                <button class="calendar-view-btn" data-view="week">Semaine</button>
                <button class="calendar-view-btn" data-view="day">Jour</button>
            </div>
        </div>

        <div class="days-header">
            <div class="day-header">Lun</div>
            <div class="day-header">Mar</div>
            <div class="day-header">Mer</div>
            <div class="day-header">Jeu</div>
            <div class="day-header">Ven</div>
            <div class="day-header">Sam</div>
            <div class="day-header">Dim</div>
        </div>

        <div class="calendar-grid" id="calendar-grid">
            <!-- Les jours seront générés par JavaScript -->
        </div>

        <!-- Vue semaine avec grille horaire -->
        <div class="week-view-container" id="week-view-container" style="display: none;">
            <div class="week-header-grid">
                <div class="time-header-cell"></div>
                <div class="week-day-header" data-day="0">Lun</div>
                <div class="week-day-header" data-day="1">Mar</div>
                <div class="week-day-header" data-day="2">Mer</div>
                <div class="week-day-header" data-day="3">Jeu</div>
                <div class="week-day-header" data-day="4">Ven</div>
                <div class="week-day-header" data-day="5">Sam</div>
                <div class="week-day-header" data-day="6">Dim</div>
            </div>
            <div class="week-time-grid" id="week-time-grid">
                <!-- La grille horaire sera générée par JavaScript -->
            </div>
        </div>

        <!-- Vue jour avec grille horaire -->
        <div class="day-view-container" id="day-view-container" style="display: none;">
            <div class="day-header-grid">
                <div class="day-navigation">
                    <button class="day-nav-btn" id="prev-day" title="Jour précédent">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                        </svg>
                    </button>
                    <button class="day-nav-btn" id="today-btn" title="Aujourd'hui">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </button>
                </div>
                <div class="day-header-single" id="day-header-single">
                    <!-- En-tête du jour sera généré par JavaScript -->
                </div>
                <div class="day-navigation">
                    <button class="day-nav-btn" id="next-day" title="Jour suivant">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="day-time-grid" id="day-time-grid">
                <div class="day-time-column" id="day-time-column">
                    <!-- Colonne des heures -->
                </div>
                <div class="day-events-column" id="day-events-column">
                    <!-- Colonne des événements -->
                </div>
            </div>
        </div>

        <!-- Le calendrier FullCalendar est toujours là mais masqué -->
        <div class="ib-calendar-container" style="display:none;">
            <div id="booking-calendar"></div>
        </div>
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
/* Reset et styles de base */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.ib-calendar-page {
    width: 100%;
/* Boutons de vue calendrier */
.calendar-view-switch {
    justify-content: flex-end;
}
.calendar-view-btn {
    background: #f5f6fa;
    border: none;
    color: #888;
    padding: 8px 18px;
    border-radius: 20px;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.calendar-view-btn.active {
    background: #e9aebc;
    color: #fff;
}
    max-width: 100%;
    margin: 0;
    padding: 15px;
}

/* En-tête avec le mois et la navigation */
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.month-year {
    font-size: 1.5em;
    font-weight: 500;
}

.calendar-nav {
    display: flex;
    gap: 10px;
}

.calendar-nav button {
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px 15px;
    cursor: pointer;
}

/* Grille des jours */
.days-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #eee;
    margin-bottom: 1px;
}

.day-header {
    background: white;
    padding: 10px;
    text-align: center;
    font-weight: 500;
}

/* Grille des dates */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #eee;
}

/* Vue semaine avec grille horaire */
.week-view-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.week-header-grid {
    display: grid;
    grid-template-columns: 60px repeat(7, 1fr);
    background: #fff;
    border-bottom: 1px solid #dadce0;
    position: sticky;
    top: 0;
    z-index: 10;
}

.time-header-cell {
    padding: 12px 8px;
    font-size: 10px;
    color: #5f6368;
    text-align: center;
    border-right: 1px solid #dadce0;
    background: #fff;
}

.week-day-header {
    padding: 12px 8px;
    text-align: center;
    font-weight: 400;
    color: #3c4043;
    border-right: 1px solid #dadce0;
    cursor: pointer;
    transition: background-color 0.2s;
    background: #fff;
    font-size: 11px;
}

.week-day-header:hover {
    background: #f8f9fa;
}

.week-day-header.today {
    background: #e8f0fe;
    color: #1a73e8;
    font-weight: 500;
}

.week-time-grid {
    display: grid;
    grid-template-columns: 60px repeat(7, 1fr);
    position: relative;
    max-height: 500px;
    overflow-y: auto;
    background: #fff;
}

.time-slot {
    height: 60px;
    border-bottom: 1px solid #f1f3f4;
    border-right: 1px solid #dadce0;
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    padding: 2px 8px 0;
    font-size: 10px;
    color: #5f6368;
    background: #fff;
    position: relative;
}

.day-column {
    height: 60px;
    border-bottom: 1px solid #f1f3f4;
    border-right: 1px solid #dadce0;
    position: relative;
    background: #fff;
    cursor: pointer;
    transition: background-color 0.15s;
}

.day-column:hover {
    background: #f8f9fa;
}

.day-column.today {
    background: #fef7e0;
}

/* Ligne de demi-heure */
.day-column::after {
    content: '';
    position: absolute;
    top: 30px;
    left: 0;
    right: 0;
    height: 1px;
    background: #f1f3f4;
    z-index: 1;
}

/* Événements dans la vue semaine */
.week-event {
    position: absolute;
    background: #e3f2fd;
    border-left: 4px solid #1976d2;
    border-radius: 4px;
    padding: 4px 6px;
    font-size: 11px;
    line-height: 1.2;
    color: #1565c0;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
    transition: all 0.2s ease;
    overflow: hidden;
    min-height: 20px;
}

.week-event:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    transform: translateY(-1px);
    z-index: 100;
}

.week-event-title {
    font-weight: 600;
    margin-bottom: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.week-event-client {
    font-size: 10px;
    opacity: 0.8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.week-event-time {
    font-size: 9px;
    opacity: 0.7;
    margin-top: 1px;
}

/* Ligne "maintenant" */
.now-line {
    position: absolute;
    left: 60px;
    right: 0;
    height: 2px;
    background: #ea4335;
    z-index: 50;
    pointer-events: none;
}

.now-line::before {
    content: '';
    position: absolute;
    left: -6px;
    top: -4px;
    width: 10px;
    height: 10px;
    background: #ea4335;
    border-radius: 50%;
}

/* Vue jour avec grille horaire - Design minimaliste */
.day-view-container {
    background: #fff;
    border-radius: 0;
    box-shadow: none;
    overflow: hidden;
    border: 1px solid #e8eaed;
}

.day-header-grid {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border-bottom: 1px solid #e8eaed;
    position: sticky;
    top: 0;
    z-index: 10;
    padding: 16px 24px;
}

.day-navigation {
    display: flex;
    align-items: center;
    gap: 16px;
}

.day-nav-btn {
    background: none;
    border: none;
    color: #5f6368;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
}

.day-nav-btn:hover {
    background: #f1f3f4;
    color: #202124;
}

.day-header-single {
    text-align: center;
    font-weight: 400;
    color: #3c4043;
    font-size: 16px;
    flex: 1;
}

.day-header-single.today {
    color: #1a73e8;
    font-weight: 500;
}

.day-time-grid {
    display: flex;
    position: relative;
    max-height: 600px;
    overflow-y: auto;
    background: #fff;
}

.day-time-column {
    width: 80px;
    background: #fff;
    border-right: 1px solid #e8eaed;
    flex-shrink: 0;
}

.day-time-slot {
    height: 60px;
    border-bottom: 1px solid #f1f3f4;
    display: flex;
    align-items: flex-start;
    justify-content: flex-end;
    padding: 4px 12px 0 8px;
    font-size: 11px;
    color: #70757a;
    background: #fff;
    position: relative;
    font-weight: 400;
}

.day-events-column {
    flex: 1;
    position: relative;
    background: #fff;
    min-height: 540px; /* 9 heures * 60px */
}

.day-hour-line {
    height: 60px;
    border-bottom: 1px solid #f1f3f4;
    position: relative;
    cursor: pointer;
    transition: background-color 0.15s;
}

.day-hour-line:hover {
    background: #f8f9fa;
}

.day-hour-line.today {
    background: #fef7e0;
}

/* Ligne de demi-heure pour vue jour */
.day-hour-line::after {
    content: '';
    position: absolute;
    top: 30px;
    left: 0;
    right: 0;
    height: 1px;
    background: #f1f3f4;
    z-index: 1;
}

/* Événements dans la vue jour - Style minimaliste */
.day-event {
    position: absolute;
    background: #e3f2fd;
    border-left: 3px solid #1976d2;
    border-radius: 2px;
    padding: 8px 12px;
    font-size: 13px;
    line-height: 1.4;
    color: #1565c0;
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
    overflow: hidden;
    min-height: 28px;
    margin: 2px 4px 2px 0;
    border-right: none;
    border-top: none;
    border-bottom: none;
}

.day-event:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    transform: translateX(2px);
    z-index: 100;
}

.day-event-title {
    font-weight: 500;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 13px;
}

.day-event-client {
    font-size: 12px;
    opacity: 0.85;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.day-event-time {
    font-size: 11px;
    opacity: 0.75;
    font-weight: 400;
}

/* Conteneur du jour */
.calendar-day {
    background: white;
    min-height: 100px;
    padding: 8px;
    position: relative;
    display: flex;
    flex-direction: column;
    max-height: 200px;
    overflow: hidden;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    transition: all 0.1s ease;
}

.calendar-day:hover {
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.day-number {
    font-weight: 500;
    margin-bottom: 5px;
    font-size: 0.9em;
    color: #343a40;
}

.day-events {
    flex: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 2px 0;
}

.calendar-event {
    background: #f8f9fa;
    border-left: 2px solid #2196f3;
    border-radius: 2px;
    padding: 3px 6px;
    font-size: 0.75em;
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: all 0.1s ease;
    display: flex;
    align-items: center;
    cursor: pointer;
}

.calendar-event:hover {
    background: #f1f3f5;
}

.event-time {
    font-weight: 600;
    color: #2c3e50;
    margin-right: 4px;
    flex-shrink: 0;
}

.event-separator {
    color: #adb5bd;
    margin: 0 4px;
    flex-shrink: 0;
    font-size: 0.9em;
}

.event-service {
    color: #495057;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Indicateur d'événements supplémentaires */
.more-events {
    color: #4dabf7;
    font-size: 0.7em;
    text-align: center;
    padding: 2px 0;
    margin-top: 2px;
    border-radius: 2px;
    cursor: pointer;
    transition: all 0.1s ease;
    font-weight: 500;
}

.more-events:hover {
    color: #1971c2;
    text-decoration: underline;
}

/* Style pour aujourd'hui */
.today {
    background-color: #e8f5e9;
}

/* Style pour les jours du mois précédent/suivant */
.other-month {
    color: #999;
    background-color: #f9f9f9;
}

/* Styles FullCalendar à désactiver */
.fc {
    display: none !important;
}
/* Reset des marges et paddings */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* Correction de la largeur de la page */
#wpcontent, #wpbody, #wpbody-content, #wpbody-content > .wrap {
    margin: 0 !important;
    padding: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    overflow-x: hidden !important;
}

/* Conteneur principal */
.ib-calendar-page {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 15px !important;
    box-sizing: border-box;
}

/* Conteneur du contenu */
.ib-calendar-content {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box;
}

/* Conteneur du calendrier */
.ib-calendar-container {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box;
}

/* Styles spécifiques pour les vues du calendrier */
.fc-dayGridMonth-view .fc-daygrid-day-frame,
.fc-timeGridWeek-view .fc-timegrid-slots,
.fc-timeGridDay-view .fc-timegrid-slots {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
}

/* Correction de la largeur de la page */
#wpcontent, #wpbody, #wpbody-content, #wpbody-content > .wrap {
    margin: 0 !important;
    padding: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    overflow-x: hidden !important;
}

/* Correction du conteneur principal */
.ib-calendar-page {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 15px !important;
    box-sizing: border-box;
    overflow: visible !important;
}

/* Correction du conteneur du calendrier */
.ib-calendar-container {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box;
    overflow: visible !important;
}

/* Correction de la largeur de la table */
.fc {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

.fc-view-harness {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
}

.fc-scrollgrid {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    table-layout: fixed;
}

.fc-scroller {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
}

/* Correction des cellules */
.fc-col-header, 
.fc-col-header > tr, 
.fc-col-header > thead, 
.fc-col-header > tbody, 
.fc-col-header-cell {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Correction des jours */
.fc-daygrid-day {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Correction des événements */
.fc-event {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 0 1px 0 !important;
    padding: 0 2px !important;
    box-sizing: border-box;
    overflow: hidden;
}
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
    z-index: 1 !important;}

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
    /* Style de la grille du calendrier */
    .fc {
        --fc-border-color: #e0e0e0;
        --fc-page-bg-color: #fff;
        --fc-today-bg-color: #f8f9fa;
        --fc-neutral-bg-color: #f8f9fa;
        --fc-list-event-hover-bg-color: #f5f5f5;
        --fc-button-bg-color: #fff;
        --fc-button-border-color: #e0e0e0;
        --fc-button-hover-bg-color: #f5f5f5;
        --fc-button-active-bg-color: #f0f0f0;
        --fc-button-text-color: #3c4043;
        --fc-event-bg-color: #1a73e8;
        --fc-event-border-color: #1a73e8;
        --fc-highlight-color: rgba(26, 115, 232, 0.1);
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 auto !important;
        padding: 0 !important;
        height: calc(100vh - 100px) !important;
        min-height: 600px;
        box-sizing: border-box;
        table-layout: fixed;
    }

    /* En-tête du calendrier */
    .fc .fc-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5em 0.5em 0.25em !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
        flex-wrap: wrap;
        gap: 0.5em;
    }
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 0.25em !important;
    margin: 0 0 0.5em 0 !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
}
.fc .fc-toolbar-title {
  font-size: 1.5em;
  font-weight: 500;
  color: #3c4043;
  margin: 0;
  padding: 0 0.5em;
}

.fc .fc-button {
  border-radius: 4px;
  padding: 6px 12px;
  font-weight: 500;
  text-transform: capitalize;
  box-shadow: none;
  border: 1px solid #dadce0;
  background: #fff;
  color: #3c4043;
  transition: all 0.2s;
}

.fc .fc-button-primary:not(:disabled).fc-button-active, 
.fc .fc-button-primary:not(:disabled):active {
  background-color: #f1f3f4;
  color: #1a73e8;
  border-color: #dadce0;
  box-shadow: none;
}

.fc .fc-button-primary:not(:disabled):hover {
  background-color: #f1f3f4;
  border-color: #dadce0;
}

/* Cellules des jours */
.fc .fc-daygrid-day {
    padding: 1px;
    min-height: 100px;
    min-width: 0;
    flex: 1 0 0;
    position: relative;
    width: 100%;
    overflow: hidden;
}

/* Correction de la largeur des colonnes */
.fc .fc-daygrid-body {
    width: 100% !important;
}

.fc .fc-daygrid-day-frame {
    width: 100%;
}

.fc .fc-daygrid-day-events {
    min-height: 0;
}

.fc .fc-scrollgrid-section > td {
    border: none;
}

/* Correction de la largeur de la table */
.fc-scrollgrid {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    table-layout: fixed;
    border: none !important;
}

.fc-scrollgrid-section {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

.fc-scrollgrid-section > * {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

.fc-scrollgrid-sync-table {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    table-layout: fixed;
}

.fc-scroller {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
}

.fc-view-harness {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
}

.fc-view-harness > .fc-view {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    position: relative !important;
    left: 0 !important;
    right: 0 !important;
}

/* Correction de la largeur des cellules */
.fc-col-header,
.fc-col-header-cell,
.fc-timegrid-axis,
.fc-timegrid-slot,
.fc-timegrid-slots > table {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Correction de la largeur des colonnes */
.fc-daygrid-body,
.fc-timegrid-body {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

.fc-daygrid-day-frame,
.fc-timegrid-col-frame {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Correction des événements */
.fc-event {
    width: 100% !important;
    max-width: 100% !important;
    margin: 1px 0 !important;
    box-sizing: border-box;
}

/* Correction des cellules du jour */
.fc-daygrid-day-top {
    width: 100% !important;
    margin: 0 !important;
    padding: 2px 4px !important;
    box-sizing: border-box;
}

/* Correction des en-têtes de colonne */
.fc-col-header-cell-cushion {
    width: 100% !important;
    display: inline-block;
    box-sizing: border-box;
    margin: 0 !important;
    padding: 4px 2px !important;
}

/* Correction de la barre d'outils */
.fc-header-toolbar {
    width: 100% !important;
    margin: 0 0 1em 0 !important;
    padding: 0.5em 0 !important;
    box-sizing: border-box;
}

.fc-toolbar-chunk {
    display: inline-block;
    vertical-align: middle;
    margin: 0 0.5em 0.5em 0 !important;
}

/* Correction du titre */
.fc-toolbar-title {
    margin: 0 0.5em !important;
    padding: 0 !important;
    font-size: 1.4em !important;
    line-height: 1.5 !important;
}

/* Correction des boutons */
.fc-button-group {
    margin: 0 !important;
}

.fc-scrollgrid-section > * {
    width: 100% !important;
}

.fc-col-header {
    width: 100% !important;
}

.fc-col-header-cell,
.fc-timegrid-axis,
.fc-timegrid-slot,
.fc-timegrid-slots > table {
    width: 100% !important;
}

.fc-timegrid-slots > table,
.fc-timegrid-slots > table > tbody,
.fc-timegrid-slots > table > tbody > tr,
.fc-timegrid-slots > table > tbody > tr > td {
    width: 100% !important;
    max-width: 100% !important;
}

/* Correction de la largeur des colonnes */
.fc-daygrid-body,
.fc-timegrid-body {
    width: 100% !important;
}

.fc-daygrid-day-frame,
.fc-timegrid-col-frame {
    width: 100% !important;
    max-width: 100% !important;
}

/* Correction du débordement */
.fc-scroller {
    overflow: hidden !important;
    width: 100% !important;
}

.fc-scroller::-webkit-scrollbar {
    display: none;
}

/* Correction de la largeur du conteneur interne */
.fc-view-harness {
    width: 100% !important;
    max-width: 100% !important;
    overflow: hidden !important;
}

.fc-view-harness > .fc-view {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Correction de la largeur des cellules */
.fc-col-header-cell {
    width: auto !important;
    min-width: 0 !important;
}

.fc-timegrid-slot {
    width: auto !important;
}

/* Correction de la largeur des événements */
.fc-event {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
}

/* Correction de la largeur des cellules du jour */
.fc-daygrid-day-top {
    width: 100% !important;
    box-sizing: border-box;
}

/* Correction de la largeur des en-têtes de colonne */
.fc-col-header-cell-cushion {
    width: 100% !important;
    display: inline-block;
    box-sizing: border-box;
}

.fc-scrollgrid-sync-table {
    width: 100% !important;
}

/* Correction de la largeur des en-têtes */
.fc-col-header {
    width: 100% !important;
}

.fc-col-header-cell {
    padding: 4px 2px !important;
}

/* Ajustement des boutons de navigation */
.fc .fc-toolbar {
    padding: 0.5em 0.25em !important;
    margin: 0 0 0.5em 0 !important;
}

.fc-toolbar-chunk {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    white-space: nowrap;
}

/* Ajustement du titre */
.fc .fc-toolbar-title {
    margin: 0 0.5em;
    font-size: 1.4em;
    white-space: nowrap;
}

/* Ajustement des boutons de vue */
.fc .fc-button-group {
    margin: 0;
}

/* Correction du débordement horizontal */
.fc-scroller {
    overflow-x: visible !important;
    overflow-y: auto !important;
}

/* Ajustement des cellules de temps */
.fc-timegrid-slots {
    width: 100% !important;
}

/* Correction de la largeur des colonnes de temps */
.fc-timegrid-cols {
    width: 100% !important;
}

.fc-timegrid-cols > table {
    width: 100% !important;
}

/* Ajustement des événements */
.fc-event {
    margin: 1px 2px 0;
}

/* Correction du conteneur principal */
#wpbody-content {
    padding-bottom: 0;
    overflow-x: hidden;
}

/* Ajustement du conteneur des filtres */
.ib-calendar-filters {
    margin: 0 0 1em 0;
    padding: 0.5em 0;
    width: 100%;
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
}

/* Ajustement des champs de formulaire */
.ib-form-group {
    display: inline-block;
    margin-right: 0.5em;
    vertical-align: top;
}

/* Correction du débordement du calendrier */
.fc-view-harness {
    overflow: visible !important;
}

.fc-view-harness-active > .fc-view {
    position: relative;
    right: 0;
    overflow: visible !important;
}

.fc .fc-daygrid-day.fc-day-today {
  background-color: #e8f0fe;
}

.fc .fc-daygrid-day-top {
  justify-content: flex-start;
  padding: 2px 4px;
  margin-bottom: 1px;
  min-height: 24px;
}

.fc .fc-daygrid-day-number {
  color: #3c4043;
  font-weight: 500;
  padding: 4px;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fc .fc-day-today .fc-daygrid-day-number {
  background-color: #1a73e8;
  color: white;
}

/* Événements */
.fc-daygrid-day-events {
  min-height: 0;
  margin: 0 1px;
  gap: 1px;
  max-height: none !important;
}

.fc-daygrid-event-harness {
  margin-bottom: 2px;
}

/* Bouton "plus" pour les événements masqués */
.fc-daygrid-more-link {
  color: #1a73e8 !important;
  background: transparent !important;
  padding: 2px 0 !important;
  font-size: 12px !important;
  font-weight: 500 !important;
  margin: 0 !important;
  text-decoration: none;
}

.fc-daygrid-more-link:hover {
  text-decoration: underline;
}

/* En-têtes de colonnes (jours de la semaine) */
.fc .fc-col-header-cell-cushion {
  color: #5f6368;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  text-decoration: none;
  padding: 8px 4px;
}

/* Ligne de temps actuelle */
.fc .fc-timegrid-now-indicator-arrow {
  border-color: #ea4335;
}

.fc .fc-timegrid-now-indicator-line {
  border-color: #ea4335;
  border-width: 2px;
}

/* Style pour les événements récurrents */
.fc-event-main {
  padding: 2px 4px !important;
  margin: 1px 2px !important;
  border-radius: 3px !important;
  font-size: 11px !important;
  line-height: 1.3 !important;
  cursor: pointer !important;
  transition: all 0.2s !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  border-left-width: 2px !important;
  border-left-style: solid !important;
}

/* Style pour les événements sélectionnés */
.fc-event-selected {
  box-shadow: 0 0 0 2px #1a73e8 !important;
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
    // Gestionnaire de clic sur un jour
    function handleDayClick(dayElement, date) {
        const dayBookings = getBookingsForDate(date);
        if (dayBookings.length > 0) {
            showAllBookingsForDay(dayBookings, window.employees, window.services, window.employeeColors);
        }
    }

    // Fonction utilitaire pour récupérer les réservations d'une date avec filtres
    function getBookingsForDate(date) {
        const dateStr = date.toISOString().split('T')[0];

        if (!window.bookings || !Array.isArray(window.bookings)) {
            return [];
        }

        const result = window.bookings.filter(booking => {
            if (!booking.start_time) {
                return false;
            }

            // Essayer différents formats de date
            let bookingDate;
            try {
                // Si c'est déjà au format YYYY-MM-DD
                if (booking.start_time.includes('-') && booking.start_time.length >= 10) {
                    bookingDate = booking.start_time.split(' ')[0]; // Prendre juste la partie date
                } else {
                    bookingDate = new Date(booking.start_time).toISOString().split('T')[0];
                }
            } catch (e) {
                return false;
            }

            if (bookingDate !== dateStr) return false;

            // Appliquer les filtres actifs
            let matchEmp = !currentEmployee || booking.employee_id == currentEmployee;
            let matchServ = !currentService || booking.service_id == currentService;
            let bookingCat = (booking.category_id !== undefined && booking.category_id !== null) ? String(booking.category_id) : '';
            let filterCat = String(currentCategory || '');
            let matchCat = !currentCategory || bookingCat === filterCat;

            return matchEmp && matchServ && matchCat;
        });

        return result;
    }

    // Variables globales
    let currentDate = new Date();
    let currentView = 'month';
    let calendarGrid, monthYearElement, prevMonthBtn, nextMonthBtn, todayBtn;
    
    // Palette de couleurs employé (récupérée du PHP)
    const employeeColors = <?php echo json_encode($employee_colors); ?>;
    
    // Initialisation de l'application
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des boutons de vue calendrier
        const viewButtons = document.querySelectorAll('.calendar-view-btn');
        viewButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                viewButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentView = btn.getAttribute('data-view');
                updateCalendarView();
            });
        });

        function updateCalendarView() {
            // Masquer le calendrier FullCalendar pour toutes les vues personnalisées
            const fcContainer = document.querySelector('.ib-calendar-container');
            if (fcContainer) fcContainer.style.display = 'none';

            // Réinitialiser la classe du calendrier
            if (calendarGrid) {
                calendarGrid.className = 'calendar-grid';
            }

            // Masquer toutes les vues d'abord
            const weekContainer = document.getElementById('week-view-container');
            const dayContainer = document.getElementById('day-view-container');
            if (weekContainer) {
                weekContainer.style.display = 'none';
            }
            if (dayContainer) {
                dayContainer.style.display = 'none';
            }

            // Affiche la bonne vue selon currentView
            if (currentView === 'month') {
                document.querySelector('.days-header').style.display = '';
                if (calendarGrid) {
                    calendarGrid.style.display = '';
                    calendarGrid.className = 'calendar-grid';
                }
                generateCalendar();
                updateMonthYearDisplay();
            } else if (currentView === 'week') {
                document.querySelector('.days-header').style.display = 'none';
                if (calendarGrid) {
                    calendarGrid.style.display = 'none';
                }
                generateWeekCalendar();
                updateWeekDisplay();
            } else if (currentView === 'day') {
                document.querySelector('.days-header').style.display = 'none';
                if (calendarGrid) {
                    calendarGrid.style.display = 'none';
                }
                generateDayCalendar();
                updateDayDisplay();
            }
        }

        // Génère la vue semaine
        function generateWeekCalendar() {
            if (!monthYearElement) return;

            // Masquer la grille normale et afficher la vue semaine
            calendarGrid.style.display = 'none';
            const weekContainer = document.getElementById('week-view-container');
            weekContainer.style.display = 'block';

            // Trouver le lundi de la semaine courante
            const startDate = new Date(currentDate);
            const dayOfWeek = startDate.getDay() === 0 ? 6 : startDate.getDay() - 1;
            startDate.setDate(startDate.getDate() - dayOfWeek);

            // Mettre à jour le titre
            monthYearElement.textContent = 'Semaine du ' + startDate.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });

            // Mettre à jour les en-têtes des jours
            const dayHeaders = weekContainer.querySelectorAll('.week-day-header');
            const today = new Date();

            for (let i = 0; i < 7; i++) {
                const currentDay = new Date(startDate);
                currentDay.setDate(startDate.getDate() + i);

                const dayHeader = dayHeaders[i];
                const dayNames = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
                dayHeader.innerHTML = `
                    <div style="font-size: 12px; color: #70757a;">${dayNames[i]}</div>
                    <div style="font-size: 18px; font-weight: 600; margin-top: 2px;">${currentDay.getDate()}</div>
                `;

                // Marquer aujourd'hui
                if (currentDay.toDateString() === today.toDateString()) {
                    dayHeader.classList.add('today');
                } else {
                    dayHeader.classList.remove('today');
                }

                dayHeader.dataset.date = currentDay.toISOString().slice(0, 10);
            }

            generateWeekTimeGrid(startDate);
            loadWeekEvents(startDate);
        }

        // Génère la grille horaire pour la vue semaine
        function generateWeekTimeGrid(startDate) {
            const timeGrid = document.getElementById('week-time-grid');
            timeGrid.innerHTML = '';

            // Heures de 9h à 17h
            for (let hour = 9; hour <= 17; hour++) {
                // Cellule de l'heure
                const timeSlot = document.createElement('div');
                timeSlot.className = 'time-slot';
                timeSlot.textContent = hour.toString().padStart(2, '0') + ':00';
                timeGrid.appendChild(timeSlot);

                // Colonnes des jours pour cette heure
                for (let day = 0; day < 7; day++) {
                    const dayColumn = document.createElement('div');
                    dayColumn.className = 'day-column';

                    const currentDay = new Date(startDate);
                    currentDay.setDate(startDate.getDate() + day);

                    // Marquer aujourd'hui
                    const today = new Date();
                    if (currentDay.toDateString() === today.toDateString()) {
                        dayColumn.classList.add('today');
                    }

                    dayColumn.dataset.date = currentDay.toISOString().slice(0, 10);
                    dayColumn.dataset.hour = hour;
                    dayColumn.dataset.day = day;

                    timeGrid.appendChild(dayColumn);
                }
            }

            // Ajouter la ligne "maintenant" si c'est aujourd'hui
            addNowLine();
        }

        // Charge les événements pour la vue semaine
        function loadWeekEvents(startDate) {
            const timeGrid = document.getElementById('week-time-grid');

            // Supprimer les événements existants
            timeGrid.querySelectorAll('.week-event').forEach(event => event.remove());

            // Grouper les événements par jour
            const eventsByDay = {};

            for (let day = 0; day < 7; day++) {
                const currentDay = new Date(startDate);
                currentDay.setDate(startDate.getDate() + day);
                const dateStr = currentDay.toISOString().slice(0, 10);

                const dayBookings = getBookingsForDate(currentDay);

                eventsByDay[day] = dayBookings.map(booking => {
                    // Calculer l'heure de fin correcte basée sur la durée du service
                    const calculatedEndTime = calculateEndTime(booking);

                    // Extraire les heures de début et fin
                    let startTime = booking.start_time;
                    let endTime = calculatedEndTime;

                    // Si c'est un datetime complet, extraire juste l'heure
                    if (startTime && startTime.includes(' ')) {
                        startTime = startTime.split(' ')[1];
                    }
                    if (endTime && endTime.includes(' ')) {
                        endTime = endTime.split(' ')[1];
                    }

                    return {
                        ...booking,
                        date: dateStr,
                        start_time_only: startTime,
                        end_time_only: endTime,
                        calculated_end_time: calculatedEndTime,
                        startMinutes: timeToMinutes(booking.start_time),
                        endMinutes: timeToMinutes(calculatedEndTime || booking.end_time)
                    };
                });
            }

            // Traiter les chevauchements et rendre les événements
            Object.keys(eventsByDay).forEach(day => {
                const dayEvents = eventsByDay[day];
                if (dayEvents.length === 0) return;



                const processedEvents = processOverlappingEvents(dayEvents);
                processedEvents.forEach(event => {
                    renderWeekEvent(event, parseInt(day));
                });
            });
        }

        // Convertit une heure en minutes
        function timeToMinutes(timeStr) {
            if (!timeStr) return 0;

            // Si c'est un datetime complet, extraire juste l'heure
            if (timeStr.includes(' ')) {
                timeStr = timeStr.split(' ')[1];
            }

            // Si c'est au format ISO, extraire l'heure
            if (timeStr.includes('T')) {
                timeStr = timeStr.split('T')[1].split('.')[0];
            }

            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + (minutes || 0);
        }

        // Calcule l'heure de fin basée sur l'heure de début + durée du service
        function calculateEndTime(booking) {
            if (!booking.start_time) return null;

            // Trouver la durée du service
            let serviceDuration = 60; // Durée par défaut en minutes

            if (booking.service_id && window.services) {
                const service = window.services.find(s => s.id == booking.service_id);
                if (service && service.duration) {
                    serviceDuration = parseInt(service.duration);
                }
            }

            // Si on a déjà une end_time et qu'elle semble correcte, l'utiliser
            if (booking.end_time) {
                const startMinutes = timeToMinutes(booking.start_time);
                const endMinutes = timeToMinutes(booking.end_time);
                const actualDuration = endMinutes - startMinutes;

                // Si la durée calculée est raisonnable (entre 15 min et 4h), l'utiliser
                if (actualDuration >= 15 && actualDuration <= 240) {
                    return booking.end_time;
                }
            }

            // Calculer l'heure de fin basée sur start_time + durée du service
            const startMinutes = timeToMinutes(booking.start_time);
            const endMinutes = startMinutes + serviceDuration;

            // Convertir en format HH:MM
            const endHour = Math.floor(endMinutes / 60);
            const endMin = endMinutes % 60;

            return `${endHour.toString().padStart(2, '0')}:${endMin.toString().padStart(2, '0')}`;
        }

        // Traite les événements qui se chevauchent
        function processOverlappingEvents(events) {
            if (!events || events.length === 0) return [];

            // Trier par heure de début
            events.sort((a, b) => a.startMinutes - b.startMinutes);

            // Grouper les événements qui se chevauchent
            const groups = [];
            const processed = new Set();

            events.forEach((event, index) => {
                if (processed.has(index)) return;

                const group = [event];
                processed.add(index);

                // Trouver tous les événements qui se chevauchent
                for (let i = index + 1; i < events.length; i++) {
                    if (processed.has(i)) continue;

                    const otherEvent = events[i];
                    if (groupOverlapsWith(group, otherEvent)) {
                        group.push(otherEvent);
                        processed.add(i);
                    }
                }

                groups.push(group);
            });

            // Calculer les positions pour chaque groupe
            groups.forEach(group => {
                calculateEventPositions(group);
            });

            return events;
        }

        // Vérifie si un événement chevauche avec un groupe
        function groupOverlapsWith(group, event) {
            return group.some(groupEvent =>
                !(event.endMinutes <= groupEvent.startMinutes ||
                  event.startMinutes >= groupEvent.endMinutes)
            );
        }

        // Calcule les positions optimales pour un groupe d'événements
        function calculateEventPositions(group) {
            if (group.length === 1) {
                group[0].column = 0;
                group[0].totalColumns = 1;
                group[0].width = 'calc(100% - 4px)';
                group[0].left = '2px';
                return;
            }

            // Algorithme de placement en colonnes
            const columns = [];

            group.forEach(event => {
                let placed = false;

                for (let colIndex = 0; colIndex < columns.length; colIndex++) {
                    const column = columns[colIndex];
                    let canPlace = true;

                    for (const colEvent of column) {
                        if (!(event.endMinutes <= colEvent.startMinutes ||
                              event.startMinutes >= colEvent.endMinutes)) {
                            canPlace = false;
                            break;
                        }
                    }

                    if (canPlace) {
                        column.push(event);
                        event.column = colIndex;
                        placed = true;
                        break;
                    }
                }

                if (!placed) {
                    columns.push([event]);
                    event.column = columns.length - 1;
                }
            });

            // Calculer les largeurs et positions
            const totalColumns = columns.length;
            const baseWidth = 100 / totalColumns;
            const margin = 1;

            group.forEach(event => {
                event.totalColumns = totalColumns;
                const width = baseWidth - margin;
                const leftPosition = event.column * baseWidth + (margin / 2);

                event.width = `${width}%`;
                event.left = `${leftPosition}%`;
            });
        }

        // Rend un événement dans la vue semaine
        function renderWeekEvent(event, dayIndex) {
            const timeGrid = document.getElementById('week-time-grid');

            // Calculer la position verticale
            const startHour = Math.floor(event.startMinutes / 60);
            const startMin = event.startMinutes % 60;
            const endHour = Math.floor(event.endMinutes / 60);
            const endMin = event.endMinutes % 60;

            // Position relative à 9h (première heure affichée)
            const startOffset = (startHour - 9) * 60 + (startMin / 60) * 60;
            const endOffset = (endHour - 9) * 60 + (endMin / 60) * 60;
            const height = Math.max(endOffset - startOffset, 20);

            // Créer l'élément événement
            const eventElement = document.createElement('div');
            eventElement.className = 'week-event';

            // Stocker les données de l'événement pour le clic
            eventElement.dataset.eventId = event.id;
            eventElement.dataset.title = event.title || 'Sans titre';
            eventElement.dataset.client = event.client || 'Client inconnu';
            eventElement.dataset.service = event.service || 'Service inconnu';
            eventElement.dataset.employee = event.employee || 'Employé inconnu';
            eventElement.dataset.notes = event.notes || '';
            eventElement.dataset.color = window.employeeColors[event.employee_id] || '#1976d2';
            eventElement.dataset.startTime = event.start_time_only || event.start_time;
            eventElement.dataset.endTime = event.end_time_only || event.calculated_end_time || event.end_time;

            // Obtenir la couleur de l'employé
            const employeeColor = window.employeeColors[event.employee_id] || '#1976d2';
            const lightColor = lightenColor(employeeColor, 0.9);
            const darkColor = darkenColor(employeeColor, 0.8);

            // Calculer la position et taille
            const columnWidth = `calc((100% - 60px) / 7)`;
            const leftPosition = `calc(60px + ${dayIndex} * (100% - 60px) / 7 + ${event.left || '2px'})`;
            const eventWidth = event.width || 'calc(100% - 4px)';

            eventElement.style.cssText = `
                position: absolute;
                top: ${startOffset}px;
                height: ${height}px;
                left: ${leftPosition};
                width: ${eventWidth};
                background: ${lightColor};
                border-left: 4px solid ${employeeColor};
                color: ${darkColor};
                z-index: ${10 + (event.column || 0)};
                border-radius: 4px;
                padding: 4px 6px;
                font-size: 11px;
                line-height: 1.2;
                cursor: pointer;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
                transition: all 0.2s ease;
                overflow: hidden;
                min-height: 20px;
            `;

            // Contenu de l'événement
            const showDetails = height > 30;
            const serviceName = event.service_name || (window.services && window.services.find(s => s.id == event.service_id)?.name) || 'Réservation';
            const clientName = event.client_name || 'Client';
            const startTime = event.start_time_only || event.start_time;
            const endTime = event.end_time_only || event.calculated_end_time || event.end_time;

            // Calculer la durée pour l'affichage
            const durationMinutes = event.endMinutes - event.startMinutes;
            const durationText = durationMinutes < 60 ? `${durationMinutes}min` : `${Math.floor(durationMinutes/60)}h${(durationMinutes%60).toString().padStart(2, '0')}`;

            let innerHTML = `<div class="week-event-title">${serviceName}</div>`;

            if (showDetails) {
                innerHTML += `<div class="week-event-client">${clientName}</div>`;
                if (height > 50) {
                    innerHTML += `<div class="week-event-time">${startTime} - ${endTime} (${durationText})</div>`;
                } else if (height > 35) {
                    innerHTML += `<div class="week-event-time">${startTime} (${durationText})</div>`;
                }
            }

            eventElement.innerHTML = innerHTML;

            // Gestionnaire de clic
            eventElement.addEventListener('click', (e) => {
                e.stopPropagation();
                const eventData = {
                    id: event.id,
                    title: event.title || 'Réservation sans titre',
                    client: event.client_name || event.client || 'Client non spécifié',
                    client_phone: event.client_phone || event.phone || '',
                    service_name: event.service_name || event.service || 'Service non spécifié',
                    service_id: event.service_id || (event.service && event.service.id) || null,
                    employee: event.employee_name || event.employee || 'Non attribué',
                    employee_id: event.employee_id || (event.employee && event.employee.id) || null,
                    notes: event.notes || '',
                    color: window.employeeColors[event.employee_id] || '#4f8cff',
                    start_time: event.start_time_only || event.start_time,
                    end_time: event.end_time_only || event.calculated_end_time || event.end_time,
                    date: event.date || new Date().toISOString().split('T')[0]
                };
                showEventDetails(eventData);
            });

            // Effets hover
            eventElement.addEventListener('mouseenter', () => {
                eventElement.style.boxShadow = `0 4px 12px ${employeeColor}40`;
            });

            eventElement.addEventListener('mouseleave', () => {
                eventElement.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.12)';
            });

            timeGrid.appendChild(eventElement);
        }

        // Ajoute la ligne "maintenant"
        function addNowLine() {
            const timeGrid = document.getElementById('week-time-grid');
            const existingLine = timeGrid.querySelector('.now-line');
            if (existingLine) existingLine.remove();

            const now = new Date();
            const currentHour = now.getHours();
            const currentMin = now.getMinutes();

            // Afficher seulement pendant les heures de travail
            if (currentHour < 9 || currentHour > 17) return;

            const nowLine = document.createElement('div');
            nowLine.className = 'now-line';

            const topPosition = (currentHour - 9) * 60 + (currentMin / 60) * 60;
            nowLine.style.top = `${topPosition}px`;

            timeGrid.appendChild(nowLine);
        }

        // Fonctions utilitaires pour les couleurs
        function lightenColor(color, amount) {
            const usePound = color[0] === '#';
            const col = usePound ? color.slice(1) : color;
            const num = parseInt(col, 16);
            let r = (num >> 16) + amount * 255;
            let g = (num >> 8 & 0x00FF) + amount * 255;
            let b = (num & 0x0000FF) + amount * 255;
            r = r > 255 ? 255 : r < 0 ? 0 : r;
            g = g > 255 ? 255 : g < 0 ? 0 : g;
            b = b > 255 ? 255 : b < 0 ? 0 : b;
            return (usePound ? '#' : '') + (r << 16 | g << 8 | b).toString(16).padStart(6, '0');
        }

        function darkenColor(color, amount) {
            const usePound = color[0] === '#';
            const col = usePound ? color.slice(1) : color;
            const num = parseInt(col, 16);
            let r = (num >> 16) * amount;
            let g = (num >> 8 & 0x00FF) * amount;
            let b = (num & 0x0000FF) * amount;
            r = Math.floor(r);
            g = Math.floor(g);
            b = Math.floor(b);
            return (usePound ? '#' : '') + (r << 16 | g << 8 | b).toString(16).padStart(6, '0');
        }

        // Génère la vue jour
        function generateDayCalendar() {
            if (!monthYearElement) return;

            // Masquer la grille normale et afficher la vue jour
            calendarGrid.style.display = 'none';
            const dayContainer = document.getElementById('day-view-container');
            dayContainer.style.display = 'block';

            // Mettre à jour le titre
            monthYearElement.textContent = currentDate.toLocaleDateString('fr-FR', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            // Mettre à jour l'en-tête du jour
            const dayHeader = document.getElementById('day-header-single');
            const today = new Date();
            const dayNames = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

            dayHeader.innerHTML = `
                <div style="font-size: 12px; color: #5f6368; margin-bottom: 4px;">${dayNames[currentDate.getDay()]}</div>
                <div style="font-size: 24px; font-weight: 400;">${currentDate.getDate()}</div>
            `;

            // Marquer aujourd'hui
            if (currentDate.toDateString() === today.toDateString()) {
                dayHeader.classList.add('today');
            } else {
                dayHeader.classList.remove('today');
            }

            generateDayTimeGrid();
            loadDayEvents();
            setupDayNavigation();
        }

        // Génère la grille horaire pour la vue jour
        function generateDayTimeGrid() {
            const timeColumn = document.getElementById('day-time-column');
            const eventsColumn = document.getElementById('day-events-column');

            // Vider les colonnes
            timeColumn.innerHTML = '';
            eventsColumn.innerHTML = '';

            const today = new Date();
            const isToday = currentDate.toDateString() === today.toDateString();

            // Heures de 9h à 17h
            for (let hour = 9; hour <= 17; hour++) {
                // Cellule de l'heure dans la colonne de gauche
                const timeSlot = document.createElement('div');
                timeSlot.className = 'day-time-slot';
                timeSlot.textContent = hour.toString().padStart(2, '0') + ':00';
                timeColumn.appendChild(timeSlot);

                // Ligne horaire dans la colonne des événements
                const hourLine = document.createElement('div');
                hourLine.className = 'day-hour-line';

                if (isToday) {
                    hourLine.classList.add('today');
                }

                hourLine.dataset.date = currentDate.toISOString().slice(0, 10);
                hourLine.dataset.hour = hour;

                eventsColumn.appendChild(hourLine);
            }

            // Ajouter la ligne "maintenant" si c'est aujourd'hui
            addDayNowLine();
        }

        // Charge les événements pour la vue jour
        function loadDayEvents() {
            const eventsColumn = document.getElementById('day-events-column');

            // Supprimer les événements existants
            eventsColumn.querySelectorAll('.day-event').forEach(event => event.remove());

            // Récupérer les réservations du jour
            const dayBookings = getBookingsForDate(currentDate);

            if (dayBookings.length === 0) return;

            // Traiter les données des événements
            const dayEvents = dayBookings.map(booking => {
                // Calculer l'heure de fin correcte basée sur la durée du service
                const calculatedEndTime = calculateEndTime(booking);

                // Extraire les heures de début et fin
                let startTime = booking.start_time;
                let endTime = calculatedEndTime;

                // Si c'est un datetime complet, extraire juste l'heure
                if (startTime && startTime.includes(' ')) {
                    startTime = startTime.split(' ')[1];
                }
                if (endTime && endTime.includes(' ')) {
                    endTime = endTime.split(' ')[1];
                }

                return {
                    ...booking,
                    start_time_only: startTime,
                    end_time_only: endTime,
                    calculated_end_time: calculatedEndTime,
                    startMinutes: timeToMinutes(booking.start_time),
                    endMinutes: timeToMinutes(calculatedEndTime || booking.end_time)
                };
            });

            // Traiter les chevauchements
            const processedEvents = processOverlappingEvents(dayEvents);

            // Rendre les événements
            processedEvents.forEach(event => {
                renderDayEvent(event);
            });
        }

        // Rend un événement dans la vue jour
        function renderDayEvent(event) {
            const eventsColumn = document.getElementById('day-events-column');

            // Calculer la position verticale
            const startHour = Math.floor(event.startMinutes / 60);
            const startMin = event.startMinutes % 60;
            const endHour = Math.floor(event.endMinutes / 60);
            const endMin = event.endMinutes % 60;

            // Position relative à 9h (première heure affichée)
            const startOffset = (startHour - 9) * 60 + (startMin / 60) * 60;
            const endOffset = (endHour - 9) * 60 + (endMin / 60) * 60;
            const height = Math.max(endOffset - startOffset, 24);

            // Créer l'élément événement
            const eventElement = document.createElement('div');
            eventElement.className = 'day-event';

            // Stocker les données de l'événement pour le clic
            eventElement.dataset.eventId = event.id;
            eventElement.dataset.title = event.title || 'Sans titre';
            eventElement.dataset.client = event.client_name || 'Client inconnu';
            eventElement.dataset.service = event.service_name || 'Service inconnu';
            eventElement.dataset.employee = event.employee || 'Employé inconnu';
            eventElement.dataset.notes = event.notes || '';
            eventElement.dataset.color = window.employeeColors[event.employee_id] || '#1976d2';
            eventElement.dataset.startTime = event.start_time_only || event.start_time;
            eventElement.dataset.endTime = event.end_time_only || event.calculated_end_time || event.end_time;

            // Obtenir la couleur de l'employé
            const employeeColor = window.employeeColors[event.employee_id] || '#1976d2';
            const lightColor = lightenColor(employeeColor, 0.9);
            const darkColor = darkenColor(employeeColor, 0.8);

            // Calculer la position et taille avec gestion des chevauchements
            const leftPosition = event.left || '2px';
            const eventWidth = event.width || 'calc(100% - 4px)';

            eventElement.style.cssText = `
                position: absolute;
                top: ${startOffset}px;
                height: ${height}px;
                left: ${leftPosition};
                width: ${eventWidth};
                background: ${lightColor};
                border-left: 3px solid ${employeeColor};
                color: ${darkColor};
                z-index: ${10 + (event.column || 0)};
                border-radius: 2px;
                padding: 8px 12px;
                font-size: 13px;
                line-height: 1.4;
                cursor: pointer;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
                transition: all 0.2s ease;
                overflow: hidden;
                min-height: 28px;
                margin: 0;
            `;

            // Contenu de l'événement
            const showDetails = height > 40;
            const serviceName = event.service_name || (window.services && window.services.find(s => s.id == event.service_id)?.name) || 'Réservation';
            const clientName = event.client_name || 'Client';
            const startTime = event.start_time_only || event.start_time;
            const endTime = event.end_time_only || event.calculated_end_time || event.end_time;

            // Calculer la durée pour l'affichage
            const durationMinutes = event.endMinutes - event.startMinutes;
            const durationText = durationMinutes < 60 ? `${durationMinutes}min` : `${Math.floor(durationMinutes/60)}h${(durationMinutes%60).toString().padStart(2, '0')}`;

            let innerHTML = `<div class="day-event-title">${serviceName}</div>`;

            if (showDetails) {
                innerHTML += `<div class="day-event-client">${clientName}</div>`;
                if (height > 60) {
                    innerHTML += `<div class="day-event-time">${startTime} - ${endTime} (${durationText})</div>`;
                } else if (height > 45) {
                    innerHTML += `<div class="day-event-time">${startTime} (${durationText})</div>`;
                }
            }

            eventElement.innerHTML = innerHTML;

            // Gestionnaire de clic
            eventElement.addEventListener('click', (e) => {
                e.stopPropagation();
                const eventData = {
                    id: event.id,
                    title: event.title || 'Réservation sans titre',
                    client: event.client_name || event.client || 'Client non spécifié',
                    client_phone: event.client_phone || event.phone || '',
                    service_name: event.service_name || event.service || 'Service non spécifié',
                    service_id: event.service_id || (event.service && event.service.id) || null,
                    employee: event.employee_name || event.employee || 'Non attribué',
                    employee_id: event.employee_id || (event.employee && event.employee.id) || null,
                    notes: event.notes || '',
                    color: window.employeeColors[event.employee_id] || '#4f8cff',
                    start_time: event.start_time_only || event.start_time,
                    end_time: event.end_time_only || event.calculated_end_time || event.end_time,
                    date: event.date || new Date().toISOString().split('T')[0]
                };
                showEventDetails(eventData);
            });

            // Effets hover
            eventElement.addEventListener('mouseenter', () => {
                eventElement.style.boxShadow = `0 4px 12px ${employeeColor}40`;
            });

            eventElement.addEventListener('mouseleave', () => {
                eventElement.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.12)';
            });

            eventsColumn.appendChild(eventElement);
        }

        // Ajoute la ligne "maintenant" pour la vue jour
        function addDayNowLine() {
            const eventsColumn = document.getElementById('day-events-column');
            const existingLine = eventsColumn.querySelector('.now-line');
            if (existingLine) existingLine.remove();

            const now = new Date();
            const currentHour = now.getHours();
            const currentMin = now.getMinutes();

            // Afficher seulement pendant les heures de travail et si c'est aujourd'hui
            if (currentHour < 9 || currentHour > 17) return;
            if (currentDate.toDateString() !== now.toDateString()) return;

            const nowLine = document.createElement('div');
            nowLine.className = 'now-line';
            nowLine.style.left = '0';
            nowLine.style.right = '0';

            const topPosition = (currentHour - 9) * 60 + (currentMin / 60) * 60;
            nowLine.style.top = `${topPosition}px`;

            eventsColumn.appendChild(nowLine);
        }

        // Configure la navigation entre les jours
        function setupDayNavigation() {
            const prevBtn = document.getElementById('prev-day');
            const nextBtn = document.getElementById('next-day');
            const todayBtn = document.getElementById('today-btn');

            // Navigation jour précédent
            prevBtn.onclick = () => {
                currentDate.setDate(currentDate.getDate() - 1);
                generateDayCalendar();
            };

            // Navigation jour suivant
            nextBtn.onclick = () => {
                currentDate.setDate(currentDate.getDate() + 1);
                generateDayCalendar();
            };

            // Retour à aujourd'hui
            todayBtn.onclick = () => {
                currentDate = new Date();
                generateDayCalendar();
            };

            // Navigation au clavier
            document.addEventListener('keydown', handleDayKeyNavigation);
        }

        // Gestion de la navigation au clavier pour la vue jour
        function handleDayKeyNavigation(e) {
            if (currentView !== 'day') return;

            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    currentDate.setDate(currentDate.getDate() - 1);
                    generateDayCalendar();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    currentDate.setDate(currentDate.getDate() + 1);
                    generateDayCalendar();
                    break;
                case 'Home':
                    e.preventDefault();
                    currentDate = new Date();
                    generateDayCalendar();
                    break;
            }
        }
        try {
            // Stocker les données globalement
            window.bookings = <?php echo json_encode($bookings); ?>;
            window.employees = <?php echo json_encode($employees); ?>;
            window.services = <?php echo json_encode($services); ?>;
            window.employeeColors = <?php echo json_encode($employee_colors); ?>;
            
            // Initialiser les éléments DOM
            calendarGrid = document.getElementById('calendar-grid');
            monthYearElement = document.querySelector('.month-year');
            prevMonthBtn = document.getElementById('prev-month');
            nextMonthBtn = document.getElementById('next-month');
            todayBtn = document.getElementById('today');
            
            // Initialiser le calendrier principal
            initMainCalendar();
            
            // Initialiser le calendrier alternatif si nécessaire
            initAlternativeCalendar();
            
            // Initialiser le calendrier personnalisé si les éléments existent
            if (calendarGrid && monthYearElement) {
                // Initialiser le calendrier personnalisé
                generateCalendar();
                loadEvents();
                
                // Gestion des clics pour le calendrier personnalisé
                document.addEventListener('click', (e) => {
                    // Clic sur une case du calendrier (hors jours du mois précédent/suivant)
                    const dayElement = e.target.closest('.calendar-day:not(.other-month)');
                    if (dayElement && !e.target.classList.contains('more-events')) {
                        const day = parseInt(dayElement.querySelector('.day-number').textContent);
                        const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                        const dayBookings = getBookingsForDate(date);
                        showAllBookingsForDay(dayBookings, window.employees, window.services, window.employeeColors);
                    }
                    // Clic sur "+X plus"
                    if (e.target.classList.contains('more-events')) {
                        e.stopPropagation();
                        const dayElement = e.target.closest('.calendar-day');
                        const day = parseInt(dayElement.querySelector('.day-number').textContent);
                        const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                        const dayBookings = getBookingsForDate(date);
                        showAllBookingsForDay(dayBookings, window.employees, window.services, window.employeeColors);
                    }
                });
                
                // Gestionnaires d'événements pour la navigation
                if (prevMonthBtn) {
                    prevMonthBtn.addEventListener('click', () => {
                        if (currentView === 'month') {
                            currentDate.setMonth(currentDate.getMonth() - 1);
                            generateCalendar();
                            updateMonthYearDisplay();
                        } else if (currentView === 'week') {
                            currentDate.setDate(currentDate.getDate() - 7);
                            generateWeekCalendar();
                            updateWeekDisplay();
                        } else if (currentView === 'day') {
                            currentDate.setDate(currentDate.getDate() - 1);
                            generateDayCalendar();
                            updateDayDisplay();
                        }
                    });
                }
                
                if (nextMonthBtn) {
                    nextMonthBtn.addEventListener('click', () => {
                        if (currentView === 'month') {
                            currentDate.setMonth(currentDate.getMonth() + 1);
                            generateCalendar();
                            updateMonthYearDisplay();
                        } else if (currentView === 'week') {
                            currentDate.setDate(currentDate.getDate() + 7);
                            generateWeekCalendar();
                            updateWeekDisplay();
                        } else if (currentView === 'day') {
                            currentDate.setDate(currentDate.getDate() + 1);
                            generateDayCalendar();
                            updateDayDisplay();
                        }
                    });
                }
                
                if (todayBtn) {
                    todayBtn.addEventListener('click', () => {
                        currentDate = new Date();
                        if (currentView === 'month') {
                            generateCalendar();
                            updateMonthYearDisplay();
                        } else if (currentView === 'week') {
                            generateWeekCalendar();
                            updateWeekDisplay();
                        } else if (currentView === 'day') {
                            generateDayCalendar();
                            updateDayDisplay();
                        }
                    });
                }
            }
            
            // Activation du chip "Tous les employés" par défaut
            const allChip = document.querySelector('.ib-employee-chip-all');
            if (allChip) {
                allChip.classList.add('active');
            }
            
            // Gérer le redimensionnement de la fenêtre
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.calendar) {
                        try {
                            window.calendar.updateSize();
                        } catch (e) {
                            console.error('Erreur lors du redimensionnement du calendrier:', e);
                        }
                    }
                }, 250);
            });
            
        } catch (e) {
            console.error('Erreur lors de l\'initialisation de l\'application:', e);
        }
    }); // Fin de DOMContentLoaded
    
    // Fonction pour mettre à jour l'affichage du mois/année
    function updateMonthYearDisplay() {
        if (!monthYearElement) return;
        const options = { month: 'long', year: 'numeric' };
        monthYearElement.textContent = currentDate.toLocaleDateString('fr-FR', options);
    }
    
    // Fonction pour mettre à jour l'affichage de la semaine
    function updateWeekDisplay() {
        if (!monthYearElement) return;
        const startOfWeek = new Date(currentDate);
        startOfWeek.setDate(currentDate.getDate() - currentDate.getDay() + 1); // Lundi
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6); // Dimanche
        
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        monthYearElement.textContent = 
            'Semaine du ' + 
            startOfWeek.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long' }) + 
            ' au ' + 
            endOfWeek.toLocaleDateString('fr-FR', options);
    }
    
    // Fonction pour mettre à jour l'affichage du jour
    function updateDayDisplay() {
        if (!monthYearElement) return;
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
        monthYearElement.textContent = currentDate.toLocaleDateString('fr-FR', options);
    }
    
    // Fonction pour générer le calendrier
    function generateCalendar() {
        if (!calendarGrid || !monthYearElement) return;
        
        // Vider le calendrier
        calendarGrid.innerHTML = '';
        
        // Mettre à jour le titre du mois/année
        updateMonthYearDisplay();
        
        // Obtenir le premier jour du mois et le nombre de jours
        const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1; // Ajuster pour commencer par lundi
        
        // Ajouter les jours du mois précédent
        const prevMonthLastDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 0).getDate();
        for (let i = startingDay - 1; i >= 0; i--) {
            const dayElement = createDayElement(prevMonthLastDay - i, true);
            calendarGrid.appendChild(dayElement);
        }
        
        // Ajouter les jours du mois actuel
        const today = new Date();
        for (let i = 1; i <= daysInMonth; i++) {
            const dayElement = createDayElement(i, false);
            if (i === today.getDate() && 
                currentDate.getMonth() === today.getMonth() && 
                currentDate.getFullYear() === today.getFullYear()) {
                dayElement.classList.add('today');
            }
            calendarGrid.appendChild(dayElement);
        }
        
        // Ajouter les jours du mois suivant
        const daysToAdd = 42 - (startingDay + daysInMonth); // 6 lignes de 7 jours
        for (let i = 1; i <= daysToAdd; i++) {
            const dayElement = createDayElement(i, true);
            calendarGrid.appendChild(dayElement);
        }
        
        // Charger les événements
        loadEvents();
    }
    
    // Créer un élément jour
    function createDayElement(day, isOtherMonth) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day' + (isOtherMonth ? ' other-month' : '');
        
        const dayNumber = document.createElement('div');
        dayNumber.className = 'day-number';
        dayNumber.textContent = day;
        
        const eventsContainer = document.createElement('div');
        eventsContainer.className = 'day-events';
        
        dayElement.appendChild(dayNumber);
        dayElement.appendChild(eventsContainer);
        
        return dayElement;
    }
    
    // Charger les événements depuis les réservations
    function loadEvents() {
        // Utiliser les bookings globaux
        const bookings = window.bookings || [];
        const employees = window.employees || [];
        const services = window.services || [];
        const employeeColors = window.employeeColors || {};

        if (!bookings || !Array.isArray(bookings)) return;

        // Appliquer les filtres
        const filteredBookings = bookings.filter(function(booking) {
            let matchEmp = !currentEmployee || booking.employee_id == currentEmployee;
            let matchServ = !currentService || booking.service_id == currentService;
            // Correction catégorie : comparer en string et gérer null/undefined
            let bookingCat = (booking.category_id !== undefined && booking.category_id !== null) ? String(booking.category_id) : '';
            let filterCat = String(currentCategory || '');
            let matchCat = !currentCategory || bookingCat === filterCat;
            return matchEmp && matchServ && matchCat;
        });

        // Grouper les réservations filtrées par jour
        const bookingsByDay = {};
        const sortedBookings = [...filteredBookings].sort((a, b) => {
            return new Date(a.start_time) - new Date(b.start_time);
        });
        sortedBookings.forEach(booking => {
            if (!booking.start_time) return;
            const date = new Date(booking.start_time);
            const dayKey = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            if (!bookingsByDay[dayKey]) {
                bookingsByDay[dayKey] = [];
            }
            bookingsByDay[dayKey].push(booking);
        });

        // Afficher les événements filtrés
        Object.entries(bookingsByDay).forEach(([dayKey, dayBookings]) => {
            const date = new Date(dayKey);
            const day = date.getDate();
            const month = date.getMonth();
            const year = date.getFullYear();
            if (month === currentDate.getMonth() && year === currentDate.getFullYear()) {
                const dayElements = document.querySelectorAll('.calendar-day:not(.other-month) .day-number');
                dayElements.forEach(dayEl => {
                    if (parseInt(dayEl.textContent) === day) {
                        const dayContainer = dayEl.parentNode;
                        const eventsContainer = dayContainer.querySelector('.day-events');
                        if (eventsContainer) {
                            eventsContainer.innerHTML = '';
                            const maxEvents = 6;
                            const hasMore = dayBookings.length > maxEvents;
                            const eventsToShow = hasMore ? dayBookings.slice(0, maxEvents - 1) : dayBookings;
                            eventsToShow.forEach(booking => {
                                addEventToDay(eventsContainer, booking, employees, services, employeeColors);
                            });
                            if (hasMore) {
                                const remaining = dayBookings.length - (maxEvents - 1);
                                const moreElement = document.createElement('div');
                                moreElement.className = 'more-events';
                                moreElement.textContent = `+${remaining} plus`;
                                moreElement.onclick = (e) => {
                                    e.stopPropagation();
                                    showAllBookingsForDay(dayBookings, employees, services, employeeColors);
                                };
                                eventsContainer.appendChild(moreElement);
                            }
                        }
                    }
                });
            }
        });
        
        // Fonction utilitaire pour formater l'heure
        function formatTime(date) {
            if (!(date instanceof Date) || isNaN(date)) return '';
            return date.toLocaleTimeString('fr-FR', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false
            });
        }
        
        // Fonction utilitaire pour ajouter un événement à un jour
        function addEventToDay(container, booking, employees, services, employeeColors) {
            if (!booking.start_time) return;
            
            const startDate = new Date(booking.start_time);
            
            // Formater l'heure de début et de fin
            const startTime = formatTime(startDate);
            let endTime = '';
            
            if (booking.end_time) {
                const endDate = new Date(booking.end_time);
                endTime = formatTime(endDate);
            }
            
            // Trouver le nom du client et du service
            const clientName = booking.client_name || 'Client inconnu';
            let serviceName = 'Service inconnu';
            
            if (booking.service_id) {
                const service = services.find(s => s.id == booking.service_id);
                if (service) serviceName = service.name;
            }
            
            // Tronquer le nom du service si trop long
            const maxServiceLength = 15;
            const truncatedService = serviceName.length > maxServiceLength 
                ? serviceName.substring(0, maxServiceLength) + '...' 
                : serviceName;
            
            // Trouver le nom de l'employé
            let employeeName = 'Employé inconnu';
            let employeeColor = '#e3f2fd';
            
            if (booking.employee_id) {
                const employee = employees.find(e => e.id == booking.employee_id);
                if (employee) {
                    employeeName = employee.name;
                    employeeColor = employeeColors[employee.id] || employeeColor;
                }
            }
            
            // Créer l'élément événement
            const eventElement = document.createElement('div');
            eventElement.className = 'calendar-event';
            eventElement.style.backgroundColor = `${employeeColor}33`;
            eventElement.style.borderLeft = `3px solid ${employeeColor}`;
            eventElement.style.cursor = 'pointer';
            
            // Stocker les données complètes pour l'affichage dans la modale
            eventElement.dataset.booking = JSON.stringify({
                client: clientName,
                service: serviceName,
                service_id: booking.service_id || null,
                employee: employeeName,
                employee_id: booking.employee_id || null,
                startTime: startTime,
                endTime: endTime,
                notes: booking.notes || 'Aucune note',
                status: booking.status || 'confirmé'
            });
            
            // Format compact pour l'affichage
            eventElement.innerHTML = `
                <span class="event-time">${startTime}</span>
                <span class="event-separator">-</span>
                <span class="event-service" title="${serviceName}">${truncatedService}</span>
            `;
            
            // Ajouter l'événement de clic pour afficher les détails
            eventElement.addEventListener('click', (e) => {
                e.stopPropagation();
                showBookingDetails(JSON.parse(eventElement.dataset.booking));
            });
            
            container.appendChild(eventElement);
        }

        // Recharger la vue semaine si c'est la vue active
        if (currentView === 'week') {
            const weekContainer = document.getElementById('week-view-container');
            if (weekContainer && weekContainer.style.display !== 'none') {
                // Trouver le lundi de la semaine courante
                const startDate = new Date(currentDate);
                const dayOfWeek = startDate.getDay() === 0 ? 6 : startDate.getDay() - 1;
                startDate.setDate(startDate.getDate() - dayOfWeek);
                loadWeekEvents(startDate);
            }
        }

        // Recharger la vue jour si c'est la vue active
        if (currentView === 'day') {
            const dayContainer = document.getElementById('day-view-container');
            if (dayContainer && dayContainer.style.display !== 'none') {
                loadDayEvents();
            }
        }
    }
    
    // Fonction utilitaire pour formater l'heure
    function formatTime(timeStr) {
        if (!timeStr) return '';
        // Si c'est au format HH:MM:SS, ne garder que HH:MM
        if (timeStr.includes(':')) {
            const parts = timeStr.split(':');
            // Si on a au moins 2 parties (heures et minutes)
            if (parts.length >= 2) {
                return `${parts[0]}:${parts[1]}`;
            }
            return timeStr;
        }
        return timeStr;
    }
    
    // Fonction pour afficher les détails d'un événement
    function showEventDetails(event) {
        const modal = document.getElementById('ib-calendar-modal');
        const modalContent = document.getElementById('ib-calendar-modal-content');
        
        // Récupérer les données de l'événement
        const title = event.title || '';
        const client = event.client || event.client_name || 'Client non spécifié';
        const phone = event.phone || event.client_phone || 'Non renseigné';
        // Cherche le nom du service si seulement l'ID est présent
        let service = event.service || event.service_name;
        if ((!service || service === '' || service === 'Service non spécifié') && event.service_id && window.services) {
            const foundService = window.services.find(s => s.id == event.service_id);
            service = foundService ? foundService.name : 'Service non spécifié';
        }
        if (!service || service === '') service = 'Service non spécifié';

        // Cherche le nom de l'employé si seulement l'ID est présent
        let employee = event.employee || event.employee_name;
        if ((!employee || employee === '' || employee === 'Non attribué') && event.employee_id && window.employees) {
            const foundEmployee = window.employees.find(e => e.id == event.employee_id);
            employee = foundEmployee ? foundEmployee.name : 'Non attribué';
        }
        if (!employee || employee === '') employee = 'Non attribué';
        const notes = event.notes || 'Aucune note';
        const color = event.color || '#4f8cff';
        const startTime = formatTime(event.start_time_only || event.start_time) || 'Non spécifié';
        const endTime = formatTime(event.end_time_only || event.calculated_end_time || event.end_time) || 'Non spécifié';
        
        // Créer le titre avec le service et l'employé
        const modalTitle = service !== 'Service non spécifié' ? service : 'Réservation';
        const employeeInfo = employee !== 'Non attribué' ? ` - ${employee}` : '';
        
        // Créer le contenu du modal
        modalContent.innerHTML = `
            <div class='ib-modal-header'>
                <h2 style='font-weight:700;color:${color};margin-bottom:0.5em;'>${modalTitle}${employeeInfo}</h2>
                <div class='ib-event-meta' style='font-size:1em;color:#888;margin-bottom:0.7em;'>
                    <span class='ib-event-time'>${startTime} - ${endTime}</span>
            </div>
        </div>
        <div class='ib-modal-body'>
            <div class='ib-event-modern' style='background:${color}22;color:#22223b;padding:1.2em 1.5em;border-radius:1.2em;'>
                <div class="event-detail-row">
                    <span class="event-detail-label">Client :</span>
                    <span class="event-detail-value">${client}</span>
                </div>
                <div class="event-detail-row">
                    <span class="event-detail-label">Téléphone :</span>
                    <span class="event-detail-value">${phone}</span>
                </div>
                <div class="event-detail-row">
                    <span class="event-detail-label">Service :</span>
                    <span class="event-detail-value">${service}</span>
                </div>
                <div class="event-detail-row">
                    <span class="event-detail-label">Date :</span>
                    <span class="event-detail-value">${event.date || new Date(event.start).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })}</span>
                </div>
                <div class="event-detail-row">
                    <span class="event-detail-label">Horaire :</span>
                    <span class="event-detail-value">${startTime} - ${endTime}</span>
                </div>
                <div class="event-detail-row">
                    <span class="event-detail-label">Employé :</span>
                    <span class="event-detail-value">${employee}</span>
                </div>
                ${notes !== 'Aucune note' ? `
                <div class="event-detail-row">
                    <span class="event-detail-label">Notes :</span>
                    <span class="event-detail-value">${notes}</span>
                </div>` : ''}
            </div>
            <div style='margin-top: 20px; display: flex; justify-content: flex-end;'>
                <button id='close-event-details' style='padding: 8px 16px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;'>Fermer</button>
        `;
        
        // Afficher le modal
        modal.style.display = 'flex';
        
        // Gestionnaire de fermeture
        const closeBtn = document.getElementById('close-event-details');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
        
        // Fermer en cliquant en dehors du modal
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Fonction pour afficher les détails d'une réservation
    function showBookingDetails(booking) {
        const modal = document.createElement('div');
        modal.className = 'booking-details-modal';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0,0,0,0.7)';
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.zIndex = '1000';
        
        // Récupérer les noms à partir des IDs si disponibles
        let serviceName = booking.service || 'Service inconnu';
        let employeeName = booking.employee || 'Employé inconnu';
        
        // Si les IDs sont disponibles, essayer de récupérer les noms depuis les données globales
        if (booking.service_id && window.services) {
            const service = window.services.find(s => s.id == booking.service_id);
            if (service) serviceName = service.name;
        }
        
        if (booking.employee_id && window.employees) {
            const employee = window.employees.find(e => e.id == booking.employee_id);
            if (employee) employeeName = employee.name;
        }
        
        modal.innerHTML = `
            <div class="booking-details" style="background: white; padding: 20px; border-radius: 8px; max-width: 500px; width: 90%;">
                <h3 style="margin-top: 0; color: #333;">Détails de la réservation</h3>
                <div style="margin-bottom: 10px;"><strong>Client:</strong> ${booking.client || 'Non spécifié'}</div>
                <div style="margin-bottom: 10px;"><strong>Service:</strong> ${serviceName}</div>
                <div style="margin-bottom: 10px;"><strong>Employé:</strong> ${employeeName}</div>
                <div style="margin-bottom: 10px;"><strong>Horaire:</strong> ${booking.startTime || '?'} - ${booking.endTime || '?'}</div>
                <div style="margin-bottom: 10px;"><strong>Statut:</strong> ${booking.status || 'Non spécifié'}</div>
                <div style="margin-bottom: 15px;"><strong>Notes:</strong> ${booking.notes || 'Aucune note'}</div>
                <button id="close-booking-details" style="padding: 8px 16px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">Fermer</button>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Gestionnaire de clic pour fermer la modale
        const closeBtn = modal.querySelector('#close-booking-details');
        closeBtn.addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        // Fermer en cliquant en dehors de la modale
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }
    
    // Fonction pour afficher toutes les réservations d'une journée
    function showAllBookingsForDay(bookings, employees, services, employeeColors) {
        // Fermer toute modale existante
        const existingModal = document.querySelector('.all-bookings-modal');
        if (existingModal) {
            document.body.removeChild(existingModal);
        }

        const modal = document.createElement('div');
        modal.className = 'all-bookings-modal';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0,0,0,0.7)';
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.zIndex = '10000';
        
        // Trier les réservations par heure
        const sortedBookings = [...bookings].sort((a, b) => {
            return new Date(a.start_time) - new Date(b.start_time);
        });
        
        let bookingsHtml = '';
        sortedBookings.forEach(booking => {
            const startDate = booking.start_time ? new Date(booking.start_time) : null;
            const endDate = booking.end_time ? new Date(booking.end_time) : null;
            const startTime = startDate && !isNaN(startDate) ? startDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : '?';
            const endTime = endDate && !isNaN(endDate) ? endDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : '?';

            // Calculer la durée
            let duration = '?';
            if (startDate && endDate && !isNaN(startDate) && !isNaN(endDate)) {
                const durationMs = endDate - startDate;
                const durationMins = Math.round(durationMs / 60000);
                duration = durationMins < 60 ? `${durationMins} min` : `${Math.floor(durationMins/60)}h${(durationMins%60).toString().padStart(2, '0')}`;
            }

            // Trouver l'employé
            const employee = employees.find(emp => emp.id === booking.employee_id) || {};
            const employeeName = employee.name || employee.display_name || 'Employé inconnu';
            const employeeColor = employeeColors[employee.id] || '#6c757d';

            // Trouver le service
            const service = services.find(s => s.id === booking.service_id) || {};
            const serviceName = service.name || 'Service inconnu';

            // Informations client
            const clientName = booking.client_name || 'Client inconnu';

            // Déterminer la couleur et le texte du statut
            let statusInfo = {
                text: 'Confirmé',
                bgColor: '#e6f7ee',
                textColor: '#0d6832'
            };

            if (booking.status === 'completed') {
                statusInfo = {
                    text: 'Terminé',
                    bgColor: '#e9ecef',
                    textColor: '#343a40'
                };
            } else if (booking.status === 'cancelled') {
                statusInfo = {
                    text: 'Annulé',
                    bgColor: '#fce8e8',
                    textColor: '#c92a2a'
                };
            } else if (booking.status === 'pending') {
                statusInfo = {
                    text: 'En attente',
                    bgColor: '#fff8e6',
                    textColor: '#e67700'
                };
            }

            // Construction du HTML
            bookingsHtml += `
                <div class="booking-item" style="margin-bottom: 16px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #eee;">
                    <div style="display: flex; border-left: 4px solid ${employeeColor};">
                        <div style="padding: 16px; flex-grow: 1;">
                            <!-- En-tête avec horaires et statut -->
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; align-items: flex-start;">
                                <div>
                                    <div style="font-weight: 600; color: #2c3e50; font-size: 1.1em; margin-bottom: 2px;">
                                        ${startTime} - ${endTime}
                                        <span style="font-weight: normal; color: #6c757d; font-size: 0.9em; margin-left: 8px;">
                                            (${duration})
                                        </span>
                                    </div>
                                    <div style="font-size: 0.85em; color: #6c757d; margin-bottom: 4px;">
                                        <i class="dashicons dashicons-admin-users" style="font-size: 13px; margin-right: 4px;"></i>
                                        ${employeeName}
                                    </div>
                                </div>
                                <span style="font-size: 0.75em; padding: 3px 10px; border-radius: 12px; background: ${statusInfo.bgColor}; color: ${statusInfo.textColor}; font-weight: 500; white-space: nowrap;">
                                    ${statusInfo.text}
                                </span>
                            </div>
                            <!-- Nom du service -->
                            <div style="font-weight: 500; margin: 12px 0; color: #495057; font-size: 1em;">
                                <i class="dashicons dashicons-nametag" style="font-size: 15px; margin-right: 6px; color: #4dabf7;"></i>
                                ${serviceName}
                            </div>
                            <!-- Informations client -->
                            <div style="font-size: 0.9em; color: #343a40; background: #f8f9fa; padding: 12px; border-radius: 6px;">
                                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                    <i class="dashicons dashicons-businessperson" style="font-size: 15px; width: 16px; height: 16px; margin-right: 8px; color: #6c757d;"></i>
                                    <span style="font-weight: 500;">${clientName}</span>
                                </div>
                                ${booking.client_phone ? `
                                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                    <i class="dashicons dashicons-phone" style="font-size: 15px; width: 16px; height: 16px; margin-right: 8px; color: #6c757d;"></i>
                                    <a href="tel:${booking.client_phone}" style="color: #1971c2; text-decoration: none;">${booking.client_phone}</a>
                                </div>` : ''}
                                ${booking.client_email ? `
                                <div style="display: flex; align-items: center;">
                                    <i class="dashicons dashicons-email" style="font-size: 15px; width: 16px; height: 16px; margin-right: 8px; color: #6c757d;"></i>
                                    <a href="mailto:${booking.client_email}" style="color: #1971c2; text-decoration: none;">${booking.client_email}</a>
                                </div>` : ''}
                            </div>
                            <!-- Notes -->
                            ${booking.notes ? `
                            <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #eee;">
                                <div style="font-size: 0.8em; color: #6c757d; margin-bottom: 6px; display: flex; align-items: center;">
                                    <i class="dashicons dashicons-edit" style="font-size: 13px; margin-right: 6px;"></i>
                                    Notes :
                                </div>
                                <div style="font-size: 0.9em; color: #495057; background: #f8f9fa; padding: 10px; border-radius: 4px; line-height: 1.5;">
                                    ${booking.notes}
                                </div>
                            </div>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Ajouter des styles d'animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes modalFadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            .modal-enter {
                animation: modalFadeIn 0.3s ease-out forwards;
            }
            
            .fade-enter {
                animation: fadeIn 0.3s ease-out forwards;
            }
        `;
        document.head.appendChild(style);
        
        // Date au format lisible
        const dateStr = new Date(bookings[0]?.start_time || new Date()).toLocaleDateString('fr-FR', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
        
        // Création de la modale
        modal.innerHTML = `
            <div class="modal-enter" style="background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); width: 95%; max-width: 600px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
                <!-- En-tête de la modale -->
                <div style="padding: 16px 20px; background: #f8f9fa; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin: 0 0 4px 0; font-size: 1.3em; color: #2c3e50; font-weight: 600; display: flex; align-items: center;">
                            <i class="dashicons dashicons-calendar-alt" style="margin-right: 10px; color: #4dabf7;"></i>
                            ${dateStr}
                        </h3>
                        <div style="font-size: 0.85em; color: #6c757d;">
                            ${bookings.length} réservation${bookings.length > 1 ? 's' : ''} 
                        </div>
                    </div>
                    <button id="close-all-bookings" 
                            style="background: none; border: none; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #6c757d; transition: all 0.2s;"
                            onmouseover="this.style.backgroundColor='#f1f3f5'" 
                            onmouseout="this.style.backgroundColor='transparent'">
                        <span style="font-size: 1.8em; line-height: 1;">&times;</span>
                    </button>
                </div>
                
                <!-- Contenu de la modale -->
                <div id="all-bookings-list" style="padding: 20px; overflow-y: auto; flex-grow: 1;">
                    ${bookingsHtml || `
                    <div style="text-align: center; padding: 40px 20px; color: #6c757d;">
                        <i class="dashicons dashicons-calendar" style="font-size: 3em; color: #dee2e6; margin-bottom: 15px; display: block;"></i>
                        <p style="margin: 0; font-size: 1.1em;">Aucune réservation pour ce jour</p>
                    </div>`}
                </div>
                
                <!-- Pied de page -->
                <div style="padding: 12px 20px; background: #f8f9fa; border-top: 1px solid #e9ecef; text-align: right;">
                    <button id="close-all-bookings-bottom" 
                            style="background: #f1f3f5; border: 1px solid #dee2e6; border-radius: 4px; padding: 8px 16px; color: #495057; cursor: pointer; font-size: 0.9em; transition: all 0.2s;"
                            onmouseover="this.style.backgroundColor='#e9ecef'" 
                            onmouseout="this.style.backgroundColor='#f1f3f5'">
                        Fermer
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Fonction pour fermer la modale avec animation
        const closeModal = () => {
            const modalContent = modal.querySelector('> div');
            if (modalContent) {
                modalContent.style.animation = 'none';
                modalContent.offsetHeight; // Trigger reflow
                modalContent.style.animation = 'modalFadeIn 0.2s ease-in-out reverse';
                
                setTimeout(() => {
                    if (document.body.contains(modal)) {
                        document.body.removeChild(modal);
                    }
                }, 200);
            } else {
                document.body.removeChild(modal);
            }
        };
        
        // Gestion des événements de fermeture
        const closeButtons = [
            modal.querySelector('#close-all-bookings'),
            modal.querySelector('#close-all-bookings-bottom')
        ];
        closeButtons.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => {
                    if (document.body.contains(modal)) {
                        document.body.removeChild(modal);
                    }
                });
            }
        });
        // Fermer en appuyant sur Échap
        document.addEventListener('keydown', function handleEsc(e) {
            if (e.key === 'Escape') {
                if (document.body.contains(modal)) {
                    document.body.removeChild(modal);
                }
                document.removeEventListener('keydown', handleEsc);
            }
        });
        // Fermer en cliquant en dehors de la modale
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                if (document.body.contains(modal)) {
                    document.body.removeChild(modal);
                }
            }
        });
        // Donner le focus au premier élément interactif pour l'accessibilité
        const firstInteractive = modal.querySelector('button, [href], [tabindex]:not([tabindex="-1"])');
        if (firstInteractive) {
            firstInteractive.focus();
        }
    }
    
    
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

    // Initialisation du calendrier FullCalendar
    function initMainCalendar() {
        var calendarEl = document.getElementById("booking-calendar");
        if (!calendarEl) return;
        
        // Détruire le calendrier existant s'il y en a un
        if (window.calendar) {
            window.calendar.destroy();
        }
        
        window.calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            initialView: 'dayGridMonth',
            slotMinTime: '<?php echo $opening_time; ?>',
            slotMaxTime: '<?php echo $closing_time; ?>',
            allDaySlot: false,
            dayMaxEventRows: 6,
            dayMaxEvents: 6,
            contentHeight: 'auto',
            aspectRatio: 1.8,
            expandRows: true,
            views: {
                dayGridMonth: {
                    dayMaxEventRows: 6,
                    dayMaxEvents: 6,
                    displayEventEnd: true,
                    eventDisplay: 'block',
                    dayHeaderFormat: { weekday: 'short', day: 'numeric' },
                    eventTimeFormat: { 
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    }
                },
                timeGridWeek: {
                    dayMaxEventRows: 6,
                    dayMaxEvents: 6,
                    dayHeaderFormat: { weekday: 'short', day: 'numeric' },
                    allDaySlot: false
                },
                timeGridDay: {
                    dayMaxEventRows: 6,
                    dayMaxEvents: 6,
                    dayHeaderFormat: { weekday: 'long', day: 'numeric' },
                    allDaySlot: false
                }
            },
            dayHeaderClassNames: 'fc-day-header-custom',
            slotDuration: '00:30:00',
            slotLabelFormat: { 
                hour: 'numeric', 
                minute: '2-digit', 
                omitZeroMinute: false,
                hour12: false
            },
            slotLabelInterval: '01:00',
            eventTimeFormat: { 
                hour: '2-digit', 
                minute: '2-digit', 
                meridiem: false 
            },
            locale: 'fr',
            firstDay: 1,
            editable: false,
            selectable: true,
            selectMirror: true,
        moreLinkContent: function(args) {
            return { html: `<span class='fc-daygrid-more-link'>+${args.num}</span>` };
        },
        eventContent: function(arg) {
            const event = arg.event;
            const color = event.extendedProps.employee_color || event.backgroundColor || '#e9aebc';
            const timeStr = event.start.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            
            // Création du conteneur de l'événement plus compact
            const eventEl = document.createElement('div');
            eventEl.className = 'fc-event-main';
            eventEl.style.cssText = `
                padding: 1px 4px;
                margin: 1px 0;
                border-radius: 3px;
                font-size: 11px;
                line-height: 1.2;
                background: ${color}15;
                border-left: 2px solid ${color};
                color: #333;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 4px;
            `;
            
            // Ajout du contenu de l'événement (heure + service)
            eventEl.innerHTML = `
                <span style="color: ${color}; font-weight: 600; font-size: 10px;">${timeStr}</span>
                <span style="flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${event.title}</span>
            `;
            
            // Gestion du survol
            eventEl.onmouseenter = function() {
                this.style.boxShadow = `0 2px 8px ${color}66`;
                this.style.transform = 'translateX(2px)';
            };
            eventEl.onmouseleave = function() {
                this.style.boxShadow = 'none';
                this.style.transform = 'none';
            };
            
            return { domNodes: [eventEl] };
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
    }
    // Fonction d'initialisation alternative pour FullCalendar (si un autre élément avec id='calendar' existe)
    function initAlternativeCalendar() {
        // Si le calendrier principal est déjà initialisé, on ne fait rien
        if (window.calendar) return;
        
        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;
        
        try {
            // Configuration de FullCalendar
            const altCalendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                initialView: 'dayGridMonth',
                locale: 'fr',
                firstDay: 1, // Lundi comme premier jour de la semaine
                height: 'auto',
                contentHeight: 'auto',
                dayMaxEventRows: 6,
                dayMaxEvents: 6,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                events: getFilteredEvents(),
                eventDidMount: function(info) {
                    try {
                        const event = info.event;
                        const eventEl = info.el;
                        eventEl.setAttribute('tabindex', '0');
                        eventEl.setAttribute('aria-label', `${event.title} avec ${event.extendedProps.employee} pour ${event.extendedProps.client}`);
                        
                        // Ajouter un tooltip avec plus d'informations
                        eventEl.title = `${event.title}\nAvec: ${event.extendedProps.employee}\nPour: ${event.extendedProps.client}\nStatut: ${event.extendedProps.status}`;
                    } catch (e) {
                        console.error('Erreur dans eventDidMount:', e);
                    }
                },
                eventClick: function(info) {
                    try {
                        showEventModal(info.event);
                    } catch (e) {
                        console.error('Erreur lors du clic sur l\'événement:', e);
                    }
                    info.jsEvent.preventDefault();
                }
            });
            
            // Rendre le calendrier
            altCalendar.render();
            window.calendar = altCalendar; // Rendre le calendrier accessible globalement
            
        } catch (e) {
            console.error('Erreur lors de l\'initialisation du calendrier alternatif:', e);
        }
    }

    // Activation du chip "Tous les employés" par défaut
    const allChip = document.querySelector('.ib-employee-chip-all');
    if (allChip) {
        allChip.classList.add('active');
    }
    
    
    
    // Gérer le redimensionnement de la fenêtre
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.calendar) {
                try {
                    window.calendar.updateSize();
                } catch (e) {
                    console.error('Erreur lors du redimensionnement du calendrier:', e);
                }
            }
        }, 250);
    });

    // Fonction pour recharger le calendrier avec les filtres
    function updateCalendar() {
        if (window.calendar) {
            try {
                window.calendar.removeAllEvents();
                window.calendar.addEventSource(getFilteredEvents());
            } catch (error) {
                console.error('Erreur lors de la mise à jour du calendrier:', error);
            }
        }
    }

    // --- Filtres dynamiques ---
    // Employé (select)
    document.getElementById('ib-calendar-employee').addEventListener('change', function(e) {
        currentEmployee = this.value;
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
        generateCalendar(); // Ajout : met à jour le calendrier personnalisé
    });
    // Service
    document.getElementById('ib-calendar-service').addEventListener('change', function(e) {
    currentService = this.value;
    updateCalendar();
    generateCalendar(); // Ajout : met à jour le calendrier personnalisé
    });
    // Catégorie
    document.getElementById('ib-calendar-category').addEventListener('change', function(e) {
    currentCategory = this.value;
    updateCalendar();
    generateCalendar(); // Ajout : met à jour le calendrier personnalisé
    });
    // Barre employés : filtrage + synchronisation select
    document.querySelectorAll('.ib-employee-chip').forEach(function(chip){
        chip.addEventListener('click', function(){
            document.querySelectorAll('.ib-employee-chip').forEach(c=>c.classList.remove('active'));
            chip.classList.add('active');
            var empId = chip.getAttribute('data-employee');
            currentEmployee = empId;
            document.getElementById('ib-calendar-employee').value = empId;
            updateCalendar();
            generateCalendar(); // Ajout : met à jour le calendrier personnalisé
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
    // Initialize modal handlers
    const initModalHandlers = () => {
        const modal = document.getElementById('ib-calendar-modal');
        const closeBtn = document.getElementById('ib-calendar-modal-close');
        
        if (!modal || !closeBtn) return;
        
        closeBtn.onclick = closeModal;
        modal.onclick = (e) => {
            if (e.target === modal) closeModal();
        };
    };

    // Initialize calendar event handlers
    const initCalendarHandlers = () => {
        if (!window.calendar) return;
        
        window.calendar.setOption('eventClick', (info) => {
            showEventModal(info.event);
        });
        
        // Handle day cell clicks
        document.addEventListener('click', (e) => {
            const dayCell = e.target.closest('.fc-daygrid-day-frame');
            if (!dayCell || e.target.classList.contains('ib-event-dot')) return;
            
            const dateStr = dayCell.parentElement.getAttribute('data-date');
            if (dateStr) showDayModal(dateStr);
        });
    };

    // Initialize everything
    document.addEventListener('DOMContentLoaded', function() {
        try {
            // Initialisation des gestionnaires de modales
            const modal = document.getElementById('ib-calendar-modal');
            const closeBtn = document.getElementById('ib-calendar-modal-close');
            
            if (modal && closeBtn) {
                closeBtn.onclick = closeModal;
                modal.onclick = (e) => {
                    if (e.target === modal) closeModal();
                };
            }

            // Initialisation des gestionnaires
            initCalendarHandlers();
            
            // Initialisation du calendrier principal
            if (typeof initMainCalendar === 'function') {
                initMainCalendar();
            }
            
            // Initialisation alternative si nécessaire
            if (!window.calendar && typeof initAlternativeCalendar === 'function') {
                initAlternativeCalendar();
            }
        } catch (e) {
            console.error('Erreur lors de l\'initialisation du calendrier:', e);
        }
    });
</script>

<!-- Fermeture des balises div restantes -->
</div>
</div>

<?php
// Fermeture de la balise PHP si nécessaire
?>