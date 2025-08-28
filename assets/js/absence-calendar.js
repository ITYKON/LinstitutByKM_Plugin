/**
 * Calendrier des absences des employés
 */
class AbsenceCalendar {
    constructor() {
        this.currentDate = new Date();
        this.selectedEmployeeId = null;
        this.absences = [];
        this.employees = [];
        
        this.init();
    }

    init() {
        this.loadEmployees();
        this.loadAbsences();
        this.bindEvents();
        this.renderCalendar();
    }

    loadEmployees() {
        // Récupérer la liste des employés depuis le DOM
        const employeeSelect = document.getElementById('absence-employee-filter');
        if (employeeSelect) {
            this.employees = Array.from(employeeSelect.options).map(option => ({
                id: option.value,
                name: option.text
            })).filter(emp => emp.id !== '');
        }
    }

    async loadAbsences() {
        try {
            const startDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
            const endDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
            
            const formData = new FormData();
            formData.append('action', 'get_absences');
            formData.append('start_date', this.formatDate(startDate));
            formData.append('end_date', this.formatDate(endDate));
            if (this.selectedEmployeeId) {
                formData.append('employee_id', this.selectedEmployeeId);
            }

            const response = await fetch(ajaxurl, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.absences = data.data || [];
                } else {
                    console.error('Erreur lors du chargement des absences:', data.data);
                }
            }
        } catch (error) {
            console.error('Erreur lors du chargement des absences:', error);
        }
    }

    bindEvents() {
        // Filtre par employé
        const employeeFilter = document.getElementById('absence-employee-filter');
        if (employeeFilter) {
            employeeFilter.addEventListener('change', (e) => {
                this.selectedEmployeeId = e.target.value || null;
                this.loadAbsences().then(() => this.renderCalendar());
            });
        }

        // Bouton d'ajout d'absence
        const addBtn = document.getElementById('btn-add-absence');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.openAbsenceModal());
        }

        // Modal events
        this.bindModalEvents();
    }

    bindModalEvents() {
        const modal = document.getElementById('ib-add-absence-form');
        const backdrop = document.getElementById('ib-modal-bg-absence');
        const form = document.getElementById('absence-form');

        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }
    }

    renderCalendar() {
        const calendarContainer = document.getElementById('absence-calendar');
        if (!calendarContainer) return;

        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());
        
        const monthNames = [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];
        
        const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

        let html = `
            <div class="calendar-header">
                <div class="calendar-nav">
                    <button type="button" onclick="absenceCalendar.previousMonth()">‹</button>
                    <button type="button" onclick="absenceCalendar.today()">Aujourd'hui</button>
                    <button type="button" onclick="absenceCalendar.nextMonth()">›</button>
                </div>
                <h2 class="calendar-title">${monthNames[month]} ${year}</h2>
            </div>
            <div class="calendar-grid">
        `;

        // En-têtes des jours
        dayNames.forEach(day => {
            html += `<div class="calendar-day-header">${day}</div>`;
        });

        // Jours du calendrier
        const currentDate = new Date(startDate);
        for (let i = 0; i < 42; i++) {
            const isCurrentMonth = currentDate.getMonth() === month;
            const isToday = this.isToday(currentDate);
            const dayAbsences = this.getAbsencesForDate(currentDate);

            let dayClass = 'calendar-day';
            if (!isCurrentMonth) dayClass += ' other-month';
            if (isToday) dayClass += ' today';

            html += `
                <div class="${dayClass}" data-date="${this.formatDate(currentDate)}" onclick="absenceCalendar.selectDate('${this.formatDate(currentDate)}')">
                    <div class="calendar-day-number">${currentDate.getDate()}</div>
                    ${this.renderAbsencesForDay(dayAbsences)}
                </div>
            `;

            currentDate.setDate(currentDate.getDate() + 1);
        }

        html += `
            </div>
            ${this.renderLegend()}
        `;

        calendarContainer.innerHTML = html;
    }

    renderAbsencesForDay(absences) {
        if (!absences || absences.length === 0) return '';

        return absences.map(absence => {
            const typeClass = `type-${absence.type}`;
            const statusClass = `status-${absence.status}`;
            const employeeName = this.getEmployeeName(absence.employee_id);
            
            return `
                <div class="absence-item ${typeClass} ${statusClass}" 
                     onclick="absenceCalendar.editAbsence(${absence.id})" 
                     title="${employeeName} - ${absence.type} (${absence.status})">
                    ${employeeName.split(' ')[0]} - ${this.getTypeLabel(absence.type)}
                </div>
            `;
        }).join('');
    }

    renderLegend() {
        const types = [
            { key: 'absence', label: 'Absence' },
            { key: 'conge', label: 'Congé payé' },
            { key: 'maladie', label: 'Congé maladie' },
            { key: 'formation', label: 'Formation' },
            { key: 'personnel', label: 'Congé personnel' },
            { key: 'maternite', label: 'Congé maternité' },
            { key: 'paternite', label: 'Congé paternité' }
        ];

        let html = '<div class="absence-legend">';
        types.forEach(type => {
            html += `
                <div class="legend-item">
                    <div class="legend-color type-${type.key}"></div>
                    <span>${type.label}</span>
                </div>
            `;
        });
        html += '</div>';

        return html;
    }

    getAbsencesForDate(date) {
        const dateStr = this.formatDate(date);
        return this.absences.filter(absence => {
            return dateStr >= absence.start_date && dateStr <= absence.end_date;
        });
    }

    getEmployeeName(employeeId) {
        const employee = this.employees.find(emp => emp.id == employeeId);
        return employee ? employee.name : 'Employé inconnu';
    }

    getTypeLabel(type) {
        const types = {
            'absence': 'Absence',
            'conge': 'Congé',
            'maladie': 'Maladie',
            'formation': 'Formation',
            'personnel': 'Personnel',
            'maternite': 'Maternité',
            'paternite': 'Paternité'
        };
        return types[type] || type;
    }

    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }

    formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    previousMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        this.loadAbsences().then(() => this.renderCalendar());
    }

    nextMonth() {
        this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        this.loadAbsences().then(() => this.renderCalendar());
    }

    today() {
        this.currentDate = new Date();
        this.loadAbsences().then(() => this.renderCalendar());
    }

    selectDate(dateStr) {
        this.openAbsenceModal(dateStr);
    }

    openAbsenceModal(dateStr = null) {
        const modal = document.getElementById('ib-add-absence-form');
        const backdrop = document.getElementById('ib-modal-bg-absence');
        const form = document.getElementById('absence-form');
        const deleteBtn = document.getElementById('delete-absence-btn');

        if (modal && backdrop) {
            // Reset form
            form.reset();
            form.querySelector('input[name="absence_id"]').value = '';
            deleteBtn.style.display = 'none';
            
            // Set date if provided
            if (dateStr) {
                form.querySelector('input[name="start_date"]').value = dateStr;
                form.querySelector('input[name="end_date"]').value = dateStr;
            }

            backdrop.style.display = 'block';
            modal.style.display = 'block';
        }
    }

    async editAbsence(absenceId) {
        try {
            const formData = new FormData();
            formData.append('action', 'get_absence');
            formData.append('absence_id', absenceId);

            const response = await fetch(ajaxurl, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.fillAbsenceForm(data.data);
                    this.openAbsenceModal();
                }
            }
        } catch (error) {
            console.error('Erreur lors du chargement de l\'absence:', error);
        }
    }

    fillAbsenceForm(absence) {
        const form = document.getElementById('absence-form');
        const deleteBtn = document.getElementById('delete-absence-btn');

        if (form) {
            form.querySelector('input[name="absence_id"]').value = absence.id;
            form.querySelector('select[name="employee_id"]').value = absence.employee_id;
            form.querySelector('input[name="start_date"]').value = absence.start_date;
            form.querySelector('input[name="end_date"]').value = absence.end_date;
            form.querySelector('select[name="type"]').value = absence.type;
            form.querySelector('select[name="status"]').value = absence.status;
            form.querySelector('textarea[name="reason"]').value = absence.reason || '';
            
            deleteBtn.style.display = 'inline-block';
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();

        // Validation des dates
        const startDate = e.target.querySelector('input[name="start_date"]').value;
        const endDate = e.target.querySelector('input[name="end_date"]').value;

        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            alert('Erreur : la date de fin doit être postérieure ou égale à la date de début.');
            return;
        }

        const formData = new FormData(e.target);
        
        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                // Recharger la page pour voir les changements
                window.location.reload();
            }
        } catch (error) {
            console.error('Erreur lors de la soumission:', error);
            alert('Erreur lors de l\'enregistrement de l\'absence');
        }
    }

    async deleteAbsence() {
        const absenceId = document.getElementById('absence-form').querySelector('input[name="absence_id"]').value;
        
        if (!absenceId || !confirm('Êtes-vous sûr de vouloir supprimer cette absence ?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'delete_absence');
            formData.append('absence_id', absenceId);

            const response = await fetch(ajaxurl, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur lors de la suppression: ' + data.data);
                }
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            alert('Erreur lors de la suppression de l\'absence');
        }
    }
}

// Fonctions globales pour les événements
function openAbsenceModal() {
    if (window.absenceCalendar) {
        window.absenceCalendar.openAbsenceModal();
    }
}

function closeAbsenceModal() {
    const modal = document.getElementById('ib-add-absence-form');
    const backdrop = document.getElementById('ib-modal-bg-absence');
    
    if (modal && backdrop) {
        backdrop.style.display = 'none';
        modal.style.display = 'none';
    }
}

function deleteAbsence() {
    if (window.absenceCalendar) {
        window.absenceCalendar.deleteAbsence();
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('absence-calendar')) {
        window.absenceCalendar = new AbsenceCalendar();
    }
});
