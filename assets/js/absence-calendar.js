/**
 * Calendrier des absences des employés
 */
class AbsenceCalendar {
    constructor() {
        console.log('Initialisation du calendrier des absences...');
        console.log('ib_absence_ajax:', window.ib_absence_ajax);
        
        if (!window.ib_absence_ajax) {
            console.error('Erreur: La variable ib_absence_ajax n\'est pas définie. Vérifiez que le script est correctement localisé.');
            return;
        }
        
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
            formData.append('nonce', ib_absence_ajax.nonce);
            formData.append('start_date', this.formatDate(startDate));
            formData.append('end_date', this.formatDate(endDate));
            if (this.selectedEmployeeId) {
                formData.append('employee_id', this.selectedEmployeeId);
            }

            // Afficher les informations de la requête
            console.group('Envoi de la requête AJAX');
            console.log('URL:', ib_absence_ajax.ajax_url);
            console.log('Méthode: POST');
            console.log('Headers:', {
                'Content-Type': 'multipart/form-data',
                'X-WP-Nonce': ib_absence_ajax.nonce
            });
            
            // Afficher les données du formulaire
            const formDataObj = {};
            for (let pair of formData.entries()) {
                formDataObj[pair[0]] = pair[1];
            }
            console.log('Données du formulaire:', formDataObj);
            console.groupEnd();

            try {
                const response = await fetch(ib_absence_ajax.ajax_url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin', // Important pour envoyer les cookies d'authentification
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-WP-Nonce': ib_absence_ajax.nonce
                    }
                });

                // Afficher les informations de la réponse
                console.group('Réponse AJAX');
                console.log('Status:', response.status, response.statusText);
                console.log('Headers:');
                for (let [key, value] of response.headers.entries()) {
                    console.log(`  ${key}: ${value}`);
                }

                const responseText = await response.text();
                console.log('Réponse brute:', responseText);
                
                if (response.ok) {
                    try {
                        const data = JSON.parse(responseText);
                        console.log('Données parsées:', data);
                        
                        if (data.success) {
                            console.log('Absences chargées:', data.data);
                            this.absences = data.data || [];
                        } else {
                            console.error('Erreur côté serveur:', data.data);
                        }
                    } catch (e) {
                        console.error('Erreur lors de l\'analyse de la réponse JSON:', e);
                        console.error('Réponse brute du serveur:', responseText);
                    }
                } else {
                    console.error('Erreur HTTP:', response.status, response.statusText);
                    
                    // Afficher plus d'informations sur l'erreur 403
                    if (response.status === 403) {
                        console.error('Accès refusé (403) - Vérifiez que :');
                        console.error('1. Vous êtes connecté en tant qu\'administrateur');
                        console.error('2. Le nonce est valide et correspond à la session');
                        console.error('3. Votre utilisateur a les permissions nécessaires');
                    }
                }
                console.groupEnd();
                
            } catch (error) {
                console.error('Erreur lors de l\'envoi de la requête:', error);
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
        console.log('bindModalEvents called');
        const modal = document.getElementById('ib-add-absence-form');
        const backdrop = document.getElementById('ib-modal-bg-absence');
        const form = document.getElementById('absence-form');

        if (form) {
            console.log('Form found, setting up event listener');
            
            // Créer une nouvelle fonction pour le gestionnaire avec un ID unique
            const handlerId = 'formSubmitHandler_' + Date.now();
            const handleFormSubmit = (e) => {
                console.log('Form submit handler called', handlerId);
                e.preventDefault();
                e.stopPropagation();
                return this.handleFormSubmit(e);
            };
            
            // Supprimer d'abord les écouteurs existants
            if (this._formSubmitHandler) {
                console.log('Removing existing form submit handler');
                form.removeEventListener('submit', this._formSubmitHandler);
            }
            
            // Ajouter le nouvel écouteur
            form.addEventListener('submit', handleFormSubmit);
            
            // Stocker la référence pour une éventuelle suppression ultérieure
            this._formSubmitHandler = handleFormSubmit;
            console.log('New form submit handler registered', handlerId);
        } else {
            console.error('Form not found for binding events');
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
            const employeeColor = this.getEmployeeColor(absence.employee_id);
            
            return `
                <div class="absence-item ${typeClass} ${statusClass}" 
                     onclick="absenceCalendar.editAbsence(${absence.id})" 
                     title="${employeeName} - ${this.getTypeLabel(absence.type)} (${absence.status})"
                     style="background: ${employeeColor}; color: white; border: none;">
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
    
    getEmployeeColor(employeeId) {
        // Palette de couleurs pour les employés
        const colors = [
            '#4f8cff', '#00c48c', '#ffb300', '#ff4f64', '#7c3aed', 
            '#ff6f00', '#00bcd4', '#8bc34a', '#e67e22', '#e84393', 
            '#00b894', '#636e72', '#fdcb6e', '#0984e3', '#d35400', '#6c5ce7'
        ];
        
        // Si l'ID est un nombre, on le convertit en index de couleur
        const id = parseInt(employeeId);
        const index = isNaN(id) ? 0 : id % colors.length;
        
        return colors[index];
    }

    isToday(date) {
        const today = new Date();
        return date.toDateString() === today.toDateString();
    }

    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
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
            
            // Set date if provided and is a string (not FormData)
            if (dateStr && typeof dateStr === 'string') {
                form.querySelector('input[name="start_date"]').value = dateStr;
                form.querySelector('input[name="end_date"]').value = dateStr;
            }

            backdrop.style.display = 'block';
            modal.style.display = 'block';
        }
    }
    
    closeAbsenceModal() {
        const modal = document.getElementById('ib-add-absence-form');
        const backdrop = document.getElementById('ib-modal-bg-absence');
        
        if (modal && backdrop) {
            modal.style.display = 'none';
            backdrop.style.display = 'none';
        }
    }

    async editAbsence(absenceId) {
        try {
            const formData = new FormData();
            formData.append('action', 'get_absence');
            formData.append('absence_id', absenceId);

            const response = await fetch(ib_absence_ajax.ajax_url, {
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
        console.log('handleFormSubmit called');
        e.preventDefault();
        e.stopPropagation();
        
        // Vérifier si une soumission est déjà en cours
        if (this._isSubmitting) {
            console.log('Form submission already in progress, ignoring duplicate');
            return;
        }
        
        this._isSubmitting = true;
        
        // Désactiver le bouton de soumission pour éviter les doubles clics
        const submitButton = e.target.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Enregistrement...';
        }

        // Validation des dates
        const startDate = e.target.querySelector('input[name="start_date"]').value;
        const endDate = e.target.querySelector('input[name="end_date"]').value;

        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            alert('Erreur : la date de fin doit être postérieure ou égale à la date de début.');
            return;
        }

        const formData = new FormData(e.target);
        formData.append('action', 'save_absence');
        formData.append('nonce', ib_absence_ajax.nonce);
        
        try {
            const response = await fetch(ib_absence_ajax.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-WP-Nonce': ib_absence_ajax.nonce,
                    'Cache-Control': 'no-cache'
                }
            });

            // Lire la réponse comme texte d'abord
            const responseText = await response.text();
            let result;
            
            try {
                // Essayer de parser la réponse en JSON
                result = JSON.parse(responseText);
                
                if (result.success) {
                    // Fermer le modal et recharger les données
                    this.closeAbsenceModal();
                    await this.loadAbsences();
                    this.renderCalendar();
                } else {
                    console.error('Erreur du serveur:', result);
                    alert('Erreur: ' + (result.data?.message || 'Échec de l\'enregistrement'));
                }
            } catch (e) {
                // Si le parsing JSON échoue, afficher la réponse brute
                // Si la réponse réussit mais n'est pas du JSON valide, traiter comme un succès
                if (response.ok) {
                    this.closeAbsenceModal();
                    await this.loadAbsences();
                    this.renderCalendar();
                } else {
                    console.error('Réponse du serveur (non-JSON):', responseText);
                    console.error('Erreur de parsing JSON:', e);
                    alert('Erreur inattendue du serveur. Voir la console pour plus de détails.');
                }
            }
        } catch (error) {
            console.error('Erreur lors de la soumission:', error);
            alert('Erreur lors de l\'enregistrement de l\'absence');
        } finally {
            // Réactiver le bouton de soumission en cas d'erreur
            const submitButton = e.target.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Enregistrer';
            }
            
            // Réinitialiser le flag de soumission
            this._isSubmitting = false;
            console.log('Form submission completed');
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

            const response = await fetch(ib_absence_ajax.ajax_url, {
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
