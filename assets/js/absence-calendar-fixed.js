/**
 * Calendrier des absences des employés - Version corrigée
 */
class AbsenceCalendar {
    constructor() {
        this.currentDate = new Date();
        this.selectedEmployeeId = null;
        this.absences = [];
        this.employees = [];
        this._formEventsBound = false;
        this._isSubmitting = false;
        
        if (!window.ib_absence_ajax) {
            console.error('Erreur: La variable ib_absence_ajax n\'est pas définie.');
            return;
        }
        
        this.init();
    }

    // Méthodes principales
    init() {
        this.loadEmployees();
        this.loadAbsences();
        this.bindEvents();
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

            const response = await fetch(ib_absence_ajax.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-WP-Nonce': ib_absence_ajax.nonce
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.absences = data.data || [];
                    this.renderCalendar();
                }
            }
        } catch (error) {
            console.error('Erreur lors du chargement des absences:', error);
        }
    }

    // Gestion des événements
    bindEvents() {
        const employeeFilter = document.getElementById('absence-employee-filter');
        if (employeeFilter) {
            employeeFilter.addEventListener('change', (e) => {
                this.selectedEmployeeId = e.target.value || null;
                this.loadAbsences();
            });
        }

        const addBtn = document.getElementById('btn-add-absence');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.openAbsenceModal());
        }

        this.bindModalEvents();
    }

    bindModalEvents() {
        const form = document.getElementById('absence-form');
        if (!form || this._formEventsBound) return;
        
        const handleSubmit = async (e) => {
            e.preventDefault();
            if (this._isSubmitting) return false;
            
            if (!this.validateAbsenceDates()) return false;
            
            this._isSubmitting = true;
            const submitButton = form.querySelector('button[type="submit"]');
            
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Enregistrement...';
            }
            
            try {
                const formData = new FormData(form);
                formData.append('action', 'save_absence');
                formData.append('nonce', ib_absence_ajax.nonce);
                
                const response = await fetch(ib_absence_ajax.ajax_url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-WP-Nonce': ib_absence_ajax.nonce
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        await this.loadAbsences();
                        this.closeAbsenceModal();
                    } else {
                        alert('Erreur: ' + (data.data?.message || 'Erreur inconnue'));
                    }
                }
            } catch (error) {
                console.error('Erreur lors de la soumission:', error);
                alert('Une erreur est survenue');
            } finally {
                this._isSubmitting = false;
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Enregistrer';
                }
            }
        };
        
        form.addEventListener('submit', handleSubmit);
        this._formEventsBound = true;
    }

    // Méthodes d'aide
    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    validateAbsenceDates() {
        const form = document.getElementById('absence-form');
        if (!form) return false;
        
        const startDateInput = form.querySelector('input[name="start_date"]');
        const endDateInput = form.querySelector('input[name="end_date"]');
        
        if (!startDateInput || !endDateInput) return false;
        
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (endDate < startDate) {
            alert('La date de fin ne peut pas être antérieure à la date de début');
            return false;
        }
        
        return true;
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('absence-calendar')) {
        window.absenceCalendar = new AbsenceCalendar();
    }
});
