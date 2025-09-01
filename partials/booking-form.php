<?php
require_once plugin_dir_path(__FILE__) . '/../includes/class-services.php';
require_once plugin_dir_path(__FILE__) . '/../includes/class-employees.php';
require_once plugin_dir_path(__FILE__) . '/../includes/class-service-employees.php';
require_once plugin_dir_path(__FILE__) . '/../includes/class-availability.php';

$services = IB_Services::get_all();
$employees = IB_Employees::get_all();

// Ajout du champ employee_ids à chaque service
foreach ($services as &$service) {
    $service->employee_ids = IB_Service_Employees::get_employees_for_service($service->id);
    if ($service->image === "NULL" || $service->image === NULL) {
        $service->image = null;
    }
}
unset($service);

// Fonction de récupération des créneaux disponibles
function get_available_slots($employee_id, $service_id, $date) {
    return IB_Availability::get_available_slots($employee_id, $service_id, $date);
}

// Fonction pour vérifier si une date est valide (jour ouvré)
function is_valid_date($date) {
    $day = strtolower(date('l', strtotime($date)));
    return IB_Availability::is_day_open($day);
}

// Fonction pour obtenir la prochaine date disponible
function get_next_available_date($employee_id, $service_id, $start_date = null) {
    return IB_Availability::get_next_available_date($employee_id, $service_id, $start_date);
}
?>
<!-- Définition de window.ajaxurl pour tous les scripts JS -->
<script>
window.ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
window.ib_nonce = "<?php echo wp_create_nonce('ib_nonce'); ?>";
</script>
<!-- intl-tel-input CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/css/intlTelInput.min.css" />
<!-- intl-tel-input JS -->
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/intlTelInput.min.js"></script>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>../assets/css/booking-form.css">
<?php
// S'assurer que jQuery est chargé
wp_enqueue_script('jquery');
?>

<?php include plugin_dir_path(__FILE__) . '/../templates/booking-form.html'; ?>
<script>
window.bookingServices = <?php echo json_encode($services); ?>;
window.bookingEmployees = <?php echo json_encode($employees); ?>;

// Attendre que jQuery soit chargé avant d'initialiser le booking
function waitForjQuery() {
  if (typeof jQuery !== 'undefined') {
    console.log('✅ jQuery chargé, initialisation du booking...');
    // Charger le script booking-form-main.js après que jQuery soit disponible
    loadBookingScript();
  } else {
    console.log('⏳ Attente de jQuery...');
    setTimeout(waitForjQuery, 100);
  }
}

function loadBookingScript() {
  // Charger d'abord le CSS du sélecteur de pays
  const countryCSS = document.createElement('link');
  countryCSS.rel = 'stylesheet';
  countryCSS.href = '<?php echo plugin_dir_url(__FILE__); ?>../assets/css/simple-country-selector.css';
  document.head.appendChild(countryCSS);

  // Charger le script du sélecteur de pays
  const countryScript = document.createElement('script');
  countryScript.src = '<?php echo plugin_dir_url(__FILE__); ?>../assets/js/simple-country-selector.js';
  countryScript.onload = function() {
    console.log('✅ Script simple-country-selector.js chargé');

    // Puis charger le script de la barre de progression
    const progressScript = document.createElement('script');
    progressScript.src = '<?php echo plugin_dir_url(__FILE__); ?>../assets/js/booking-form.js';
    progressScript.onload = function() {
      console.log('✅ Script booking-form.js chargé');

      // Enfin charger le script principal
      const script = document.createElement('script');
      script.src = '<?php echo plugin_dir_url(__FILE__); ?>../assets/js/booking-form-main.js';
      script.onload = function() {
        console.log('✅ Script booking-form-main.js chargé');
      };
      script.onerror = function() {
        console.error('❌ Erreur lors du chargement de booking-form-main.js');
      };
      document.head.appendChild(script);
    };
    progressScript.onerror = function() {
      console.error('❌ Erreur lors du chargement de booking-form.js');
    };
    document.head.appendChild(progressScript);
  };
  countryScript.onerror = function() {
    console.error('❌ Erreur lors du chargement de simple-country-selector.js');
  };
  document.head.appendChild(countryScript);
}

// Démarrer l'attente de jQuery
waitForjQuery();
</script>

<!-- CSS pour Progress Bar style Planity -->
<style>
/* Progress Bar Container */
.progress-bar-container {
  background: #ffffff;
  padding: 1.5rem 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  margin: 2rem auto;
  max-width: 700px;
  border: 1px solid #e5e7eb;
}

.progress-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  margin: 0 auto;
  padding: 0;
  max-width: 600px;
}

/* Ligne de progression */
.progress-line {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 3px;
  background: #e5e7eb;
  border-radius: 2px;
  z-index: 1;
}

.progress-line-active {
  position: absolute;
  top: 50%;
  left: 0;
  height: 3px;
  background: linear-gradient(90deg, #374151, #1f2937);
  border-radius: 2px;
  z-index: 2;
  transition: width 0.6s ease;
  width: 0%;
}

/* Étapes */
.progress-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  z-index: 3;
  background: #ffffff;
  padding: 0 0.5rem;
}

.progress-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 0.4rem;
  transition: all 0.3s ease;
  border: 2px solid #e2e8f0;
  background: #ffffff;
  color: #94a3b8;
}

.progress-circle.active {
  background: #1f2937;
  border-color: #1f2937;
  color: white;
  box-shadow: 0 0 0 4px rgba(31, 41, 55, 0.2);
}

.progress-circle.completed {
  background: #374151;
  border-color: #374151;
  color: white;
}

.progress-circle.completed::before {
  content: "✓";
  font-size: 16px;
  font-weight: bold;
}

.progress-label {
  font-size: 0.7rem;
  font-weight: 500;
  color: #64748b;
  text-align: center;
  transition: color 0.3s ease;
  white-space: nowrap;
}

.progress-step.active .progress-label {
  color: #1f2937;
  font-weight: 600;
}

.progress-step.completed .progress-label {
  color: #374151;
}

/* Responsive */
@media (max-width: 900px) {
  .progress-bar-container {
    margin: 1rem auto;
    padding: 0;
  }

  .progress-label {
    font-size: 0.65rem;
  }

  .progress-circle {
    width: 22px;
    height: 22px;
    font-size: 11px;
  }

  /* En mobile : garder seulement la progress bar du bas */
  .progress-bar-container:first-of-type {
    display: none !important;
  }

  /* S'assurer qu'il n'y a qu'une seule progress bar visible (la dernière) */
  .progress-bar-container:not(:last-of-type) {
    display: none !important;
  }
}

@media (max-width: 600px) {
  .progress-bar {
    flex-wrap: wrap;
    gap: 1rem;
  }

  .progress-line,
  .progress-line-active {
    display: none;
  }

  .progress-step {
    flex: 1;
    min-width: calc(50% - 0.5rem);
  }
}

/* Masquer la sidebar complètement */
.sidebar {
  display: none !important;
}

/* Styles pour l'intégration dans le flow */
.progress-bar-wrapper {
  width: 100%;
  display: block;
}

/* Design minimaliste unifié - Optimisation de l'espace */
.categories,
.services,
.booking-main-content,
#booking-step-content,
.booking-step-infos-modern,
.booking-step-date-modern,
.booking-ticket-modern {
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  border-radius: 0 !important;
  padding: 0.8rem 0 !important;
  margin-bottom: 0.5rem !important;
  max-width: 100% !important;
}

/* Optimisation de l'espace pour les conteneurs */
.container,
.booking-form-container {
  max-width: 95% !important;
  margin: 0.5rem auto !important;
  padding: 0.5rem !important;
}

/* Réduction des espaces dans les formulaires */
.ib-form-group,
.input-group-modern,
.phone-field-modern {
  margin-bottom: 0.8rem !important;
}

/* Calendrier et créneaux plus compacts */
.calendar-col,
.slots-col {
  padding: 1rem !important;
}

.booking-step-date-modern {
  display: flex !important;
  gap: 1rem !important;
  align-items: flex-start !important;
}

.calendar-col {
  flex: 1 !important;
  min-width: 300px !important;
}

.slots-col {
  flex: 0 0 250px !important;
  max-height: 400px !important;
  overflow-y: auto !important;
}

/* Boutons catégories compacts */
.booking-category-btn,
.categories .buttons button {
  background: #f8f9fa !important;
  border: 1px solid #e5e7eb !important;
  color: #6b7280 !important;
  border-radius: 15px !important;
  padding: 0.4rem 0.8rem !important;
  font-weight: 500 !important;
  font-size: 0.8rem !important;
  transition: all 0.2s ease !important;
  margin: 0.15rem !important;
  display: inline-block !important;
}

.booking-category-btn:hover,
.categories .buttons button:hover {
  background: #374151 !important;
  color: white !important;
  border-color: #374151 !important;
  transform: translateY(-1px) !important;
}

.booking-category-btn.active,
.booking-category-btn.selected,
.categories .buttons button.active,
.categories .buttons button.selected {
  background: #111827 !important;
  color: white !important;
  border-color: #111827 !important;
}

/* FORCER grille EN COLONNE compacte */
.services-list-planity,
.grid,
.category-services-container,
#services-grid {
  display: grid !important;
  grid-template-columns: 1fr !important;
  gap: 0 !important;
  margin: 0 !important;
  align-items: stretch !important;
  width: 100% !important;
  max-width: 100% !important;
  padding: 10px; /* Ajout d'un padding pour éviter le débordement  */
}

/* En-tête de catégorie COMPACT */
.category-header-planity {
  grid-column: 1 / -1 !important;
  width: 100% !important;
  margin: 0.5rem 0 0.5rem 0 !important;
  padding: 0 !important;
}

.category-header-planity h3 {
  font-size: 1rem !important;
  font-weight: 600 !important;
  color: #111827 !important;
  margin: 0 !important;
  padding: 0 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.5px !important;
}

/* Style LISTE ÉPURÉE sans bordures */
.service-item-planity,
.card {
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  justify-content: space-between !important;
  padding: 0.8rem 0 !important;
  margin: 0 !important;
  border-radius: 0 !important;
  height: auto !important;
  min-height: 50px !important;
  width: 100% !important;
  background: transparent !important;
  border: none !important;
  box-sizing: border-box !important;
  position: relative !important;
  overflow: visible !important;
  box-shadow: none !important;
  transition: background 0.2s ease !important;
}

.service-item-planity:hover,
.card:hover {
  background: #f9fafb !important;
}

/* Annuler le style flex original de Planity */
.service-item-planity {
  align-items: stretch !important;
  justify-content: flex-start !important;
  border-bottom: none !important;
}

/* Contenu style LISTE SIMPLE comme dans l'image */
.service-item-planity .service-info-planity {
  display: flex !important;
  flex-direction: column !important;
  justify-content: flex-start !important;
  flex: 1 !important;
  margin: 0 !important;
  padding: 0 !important;
}

.service-item-planity .service-name-planity,
.card h3 {
  font-size: 0.95rem !important;
  font-weight: 400 !important;
  color: #111827 !important;
  margin: 0 0 0.2rem 0 !important;
  line-height: 1.3 !important;
}

.service-item-planity .service-description-planity,
.card p {
  font-size: 0.8rem !important;
  color: #6b7280 !important;
  margin: 0 !important;
  line-height: 1.2 !important;
}

.service-item-planity .service-price-planity {
  display: none !important;
}

.service-item-planity .service-duration-planity {
  font-size: 0.85rem !important;
  color: #6b7280 !important;
  margin: 0 1.5rem 0 0 !important;
  min-width: 50px !important;
  text-align: right !important;
}

/* Zone droite avec durée et bouton seulement */
.service-item-planity .service-meta-planity {
  display: flex !important;
  align-items: center !important;
  gap: 1rem !important;
  margin: 0 !important;
  position: static !important;
  width: auto !important;
}

.service-choose-btn,
.card button {
  background: #1f2937 !important;
  color: white !important;
  border: none !important;
  border-radius: 20px !important;
  padding: 0.5rem 1.2rem !important;
  font-size: 0.8rem !important;
  font-weight: 500 !important;
  cursor: pointer !important;
  transition: all 0.2s ease !important;
  position: static !important;
  margin: 0 !important;
  min-width: 70px !important;
  white-space: nowrap !important;
  height: 32px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

.service-choose-btn:hover,
.card button:hover {
  background: #374151 !important;
}

/* Masquer les éléments qui pourraient perturber la mise en page */
.service-item-planity::before,
.service-item-planity::after {
  display: none !important;
}

/* Créneaux horaires en grille compacte */
.slots-grid,
.slots-list {
  display: grid !important;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)) !important;
  gap: 0.5rem !important;
  max-height: 350px !important;
  overflow-y: auto !important;
}

.slot-btn {
  padding: 0.5rem 0.3rem !important;
  font-size: 0.85rem !important;
  border-radius: 6px !important;
}

/* Auto-scroll vers le haut à chaque clic */
.booking-category-btn,
.categories .buttons button,
.service-item-planity,
.service-choose-btn,
.slot-btn {
  cursor: pointer;
}

/* Responsive - Mobile optimisé */
@media (max-width: 768px) {
  .container,
  .booking-form-container {
    max-width: 98% !important;
    margin: 0.25rem auto !important;
    padding: 0.25rem !important;
  }

  .categories,
  .services,
  .booking-step-infos-modern,
  .booking-step-date-modern {
    padding: 0.5rem 0 !important;
    margin-bottom: 0.3rem !important;
  }

  .booking-category-btn,
  .categories .buttons button {
    padding: 0.3rem 0.6rem !important;
    font-size: 0.75rem !important;
    margin: 0.1rem !important;
  }

  .booking-step-date-modern {
    flex-direction: column !important;
    gap: 0.5rem !important;
  }

  .calendar-col,
  .slots-col {
    flex: none !important;
    min-width: auto !important;
    padding: 0.5rem !important;
  }

  .slots-grid,
  .slots-list {
    grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)) !important;
    gap: 0.3rem !important;
    max-height: 250px !important;
  }

  .slot-btn {
    padding: 0.4rem 0.2rem !important;
    font-size: 0.8rem !important;
  }

  .services-list-planity,
  .grid,
  .category-services-container,
  #services-grid {
    grid-template-columns: 1fr !important;
    gap: 0 !important;
  }

  .service-item-planity,
  .card {
    min-height: 50px !important;
    padding: 1rem 0 !important;
  }

  .service-item-planity .service-name-planity,
  .card h3 {
    font-size: 0.85rem !important;
    margin-bottom: 0.1rem !important;
  }

  .service-item-planity .service-description-planity,
  .card p {
    font-size: 0.7rem !important;
  }

  .service-item-planity .service-duration-planity {
    font-size: 0.75rem !important;
    margin-right: 1rem !important;
    min-width: 40px !important;
  }

  .service-choose-btn,
  .card button {
    padding: 0.4rem 1rem !important;
    font-size: 0.75rem !important;
    min-width: 65px !important;
    height: 28px !important;
    border-radius: 16px !important;
  }

  .category-header-planity {
    margin: 1rem 0 0.5rem 0 !important;
  }

  .category-header-planity h3 {
    font-size: 0.9rem !important;
  }
}

/* RÉDUIRE DRASTIQUEMENT les espacements généraux */
.booking-form-container {
  padding: 0.5rem !important;
  margin: 0 !important;
}

.step-content {
  padding: 0.3rem 0 !important;
  margin: 0 !important;
}

.services-section {
  margin: 0 !important;
  padding: 0 !important;
}

/* Titre principal COMPACT */
.step-title,
h2 {
  margin: 0 0 0.3rem 0 !important;
  padding: 0 !important;
  font-size: 1.1rem !important;
}

/* Réduire l'espace entre progress bar et contenu */
.progress-container,
.step-indicator {
  margin-bottom: 0.3rem !important;
}

/* Réduire l'espace entre catégories et "Choisissez votre service" */
.category-tabs,
.category-filters {
  margin-bottom: 0.3rem !important;
}

/* Réduire l'espace autour des sections */
.booking-step,
.step-container {
  margin: 0 !important;
  padding: 0.5rem 0 !important;
}

/* Compacter les conteneurs de services */
.services-container,
.services-list-container {
  margin: 0 !important;
  padding: 0 !important;
}

/* STYLES pour les informations de l'institut */
.institut-info {
  width: 100%;
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.institut-logo {
  margin-bottom: 1rem;
}

.institut-logo img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #f3f4f6;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.institut-details h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 1rem 0;
}

.institut-address,
.institut-phone {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  margin-bottom: 0.8rem;
  font-size: 0.85rem;
  color: #6b7280;
  line-height: 1.4;
}

.institut-address svg,
.institut-phone svg {
  color: #9ca3af;
  margin-top: 0.1rem;
  flex-shrink: 0;
}

.institut-phone {
  margin-bottom: 0;
}

.institut-phone span {
  font-weight: 500;
  color: #374151;
}

/* VERSION MOBILE des informations institut */
.institut-info-mobile {
  display: none;
  background: white;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.institut-header-mobile {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.institut-header-mobile img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #f3f4f6;
  flex-shrink: 0;
}

.institut-text-mobile h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 0.3rem 0;
}

.institut-text-mobile p {
  font-size: 0.8rem;
  color: #6b7280;
  margin: 0.1rem 0;
  line-height: 1.3;
}

/* Responsive pour mobile */
@media (max-width: 768px) {
  .institut-info {
    display: none; /* Masquer la version desktop sur mobile */
  }

  .institut-info-mobile {
    display: block; /* Afficher la version mobile */
  }
}

/* Responsive pour desktop */
@media (min-width: 769px) {
  .institut-info-mobile {
    display: none; /* Masquer la version mobile sur desktop */
  }
}
</style>

<!-- Script pour Progress Bar style Planity -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  console.log('✅ Progress Bar Planity initialisée');

  // Créer la progress bar
  createProgressBar();

  // Initialiser la progression
  updateProgress(1); // Commencer à l'étape 1 (Service)

  // Détecter automatiquement les changements d'étapes
  setupStepDetection();

  // Ajouter le scroll automatique vers le haut
  setupAutoScroll();
});

function createProgressBar() {
  // DÉSACTIVÉ : Ne plus créer de barre de progression automatique
  // La barre de progression est maintenant intégrée dans le template HTML
  console.log('🚫 Création automatique de progress bar désactivée - utilisation du template HTML');
  return;

    if (title) {
      title.insertAdjacentHTML('afterend', getProgressBarHTML());
      console.log('✅ Progress Bar desktop positionnée après le titre');
    }
  }
}

function getProgressBarHTML() {
  return `
    <div class="progress-bar-wrapper">
      <div class="progress-bar-container">
        <div class="progress-bar">
          <div class="progress-line"></div>
          <div class="progress-line-active"></div>

          <div class="progress-step" data-step="1">
            <div class="progress-circle">1</div>
            <div class="progress-label">Service</div>
          </div>

          <div class="progress-step" data-step="2">
            <div class="progress-circle">2</div>
            <div class="progress-label">Praticienne</div>
          </div>

          <div class="progress-step" data-step="3">
            <div class="progress-circle">3</div>
            <div class="progress-label">Date & Heure</div>
          </div>

          <div class="progress-step" data-step="4">
            <div class="progress-circle">4</div>
            <div class="progress-label">Informations</div>
          </div>

          <div class="progress-step" data-step="5">
            <div class="progress-circle">5</div>
            <div class="progress-label">Confirmation</div>
          </div>
        </div>
      </div>
    </div>
  `;
}

function setupAutoScroll() {
  // Fonction pour scroller vers le haut
  function scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  // Observer pour détecter les clics sur les éléments interactifs
  document.addEventListener('click', function(e) {
    const target = e.target;

    // Vérifier si c'est un élément qui doit déclencher le scroll
    if (target.matches('.booking-category-btn') ||
        target.matches('.categories .buttons button') ||
        target.matches('.service-item-planity') ||
        target.matches('.service-choose-btn') ||
        target.matches('.slot-btn') ||
        target.closest('.service-item-planity') ||
        target.closest('.booking-category-btn')) {

      // Délai court pour laisser l'action se terminer
      setTimeout(scrollToTop, 100);
    }
  });

  console.log('✅ Auto-scroll configuré');
}

function updateProgress(currentStep) {
  const steps = document.querySelectorAll('.progress-step');
  const progressLine = document.querySelector('.progress-line-active');

  if (!steps.length || !progressLine) {
    console.log('❌ Éléments progress bar non trouvés');
    return;
  }

  // Calculer le pourcentage de progression
  const totalSteps = steps.length;
  const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;

  // Mettre à jour la ligne de progression
  progressLine.style.width = progressPercentage + '%';

  // Mettre à jour les étapes
  steps.forEach((step, index) => {
    const stepNumber = index + 1;
    const circle = step.querySelector('.progress-circle');

    // Retirer toutes les classes
    step.classList.remove('active', 'completed');
    circle.classList.remove('active', 'completed');

    if (stepNumber < currentStep) {
      // Étape complétée
      step.classList.add('completed');
      circle.classList.add('completed');
      circle.textContent = ''; // Le ✓ sera ajouté par CSS
    } else if (stepNumber === currentStep) {
      // Étape active
      step.classList.add('active');
      circle.classList.add('active');
      circle.textContent = stepNumber;
    } else {
      // Étape future
      circle.textContent = stepNumber;
    }
  });

  console.log(`✅ Progress mis à jour: étape ${currentStep}/${totalSteps}`);
}

// Fonction pour détecter automatiquement les étapes
function setupStepDetection() {
  // Observer les changements dans le DOM pour détecter les étapes
  const observer = new MutationObserver(function(mutations) {
    detectCurrentStep();
  });

  // Observer les changements dans le container principal
  const container = document.querySelector('.container');
  if (container) {
    observer.observe(container, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['class', 'style']
    });
  }

  // Détecter l'étape initiale
  setTimeout(detectCurrentStep, 500);

  // Vérifier périodiquement
  setInterval(detectCurrentStep, 2000);
}

function detectCurrentStep() {
  let currentStep = 1;

  // Détecter selon le contenu visible
  if (document.querySelector('h2, h3, .step-title')) {
    const titles = document.querySelectorAll('h2, h3, .step-title');
    const titleText = Array.from(titles).map(t => t.textContent.toLowerCase()).join(' ');

    if (titleText.includes('praticienne') || titleText.includes('choisissez votre praticienne')) {
      currentStep = 2;
    } else if (titleText.includes('date') || titleText.includes('heure') || titleText.includes('créneau')) {
      currentStep = 3;
    } else if (titleText.includes('information') || titleText.includes('coordonnées') || titleText.includes('contact')) {
      currentStep = 4;
    } else if (titleText.includes('confirmation') || titleText.includes('récapitulatif') || titleText.includes('valider')) {
      currentStep = 5;
    }
  }

  // Détecter selon les éléments visibles
  if (document.querySelector('.practitioner-selection, .praticienne-list')) {
    currentStep = 2;
  } else if (document.querySelector('.calendar, .time-slots, .date-picker')) {
    currentStep = 3;
  } else if (document.querySelector('.contact-form, .customer-info')) {
    currentStep = 4;
  } else if (document.querySelector('.booking-summary, .confirmation')) {
    currentStep = 5;
  }

  // Mettre à jour si l'étape a changé
  if (window.currentBookingStep !== currentStep) {
    window.currentBookingStep = currentStep;
    updateProgress(currentStep);
    console.log(`🎯 Étape détectée: ${currentStep}`);
  }
}

// Fonction globale pour mettre à jour depuis l'extérieur
window.updateBookingProgress = updateProgress;
window.detectBookingStep = detectCurrentStep;
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
