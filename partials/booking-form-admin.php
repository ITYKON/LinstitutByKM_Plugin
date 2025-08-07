<?php
// Modale d'ajout réservation (formulaire admin)
?>
<div id="ib-add-booking-modal-bg" class="ib-modal-bg" style="display:none;"></div>
<div id="ib-add-booking-modal" class="ib-modal" style="display:none;max-width:600px;">
  <div class="ib-form-title" style="color:#e9aebc;"><i class="dashicons dashicons-calendar-alt"></i> <span>Ajouter une réservation</span></div>
  <form method="post" class="ib-booking-form-admin" autocomplete="off">
    <label for="add-booking-client-name">Client</label>
    <input id="add-booking-client-name" name="client_name" required>
    <label for="add-booking-client-email">Email</label>
    <input id="add-booking-client-email" name="client_email" type="email" required>
    <div style="width:260px;max-width:100%;margin-bottom:1.2em;">
      <label for="add-booking-client-phone">Téléphone</label>
      <input id="add-booking-client-phone" name="client_phone" type="tel" required placeholder="Téléphone" style="padding-left: 60px;">
    </div>
    <label for="add-booking-service">Service</label>
    <select id="add-booking-service" name="service_id" required>
      <option value="">Choisir</option>
      <?php foreach($services as $s): ?>
        <option value="<?php echo $s->id; ?>"><?php echo esc_html($s->name); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="add-booking-price">Prix (optionnel)</label>
    <input id="add-booking-price" name="price" type="number" min="0" step="0.01" placeholder="Prix (optionnel)">
    <label for="add-booking-employee">Praticienne</label>
    <select id="add-booking-employee" name="employee_id" required>
      <option value="">Choisir</option>
      <?php foreach($employees as $e):
        $service_ids = class_exists('IB_Service_Employees') ? IB_Service_Employees::get_services_for_employee($e->id) : [];
        $service_ids = array_filter(array_map('intval', $service_ids));
        $service_ids_str = $service_ids ? implode(',', $service_ids) : '';
      ?>
        <option value="<?php echo $e->id; ?>" data-services="<?php echo esc_attr($service_ids_str); ?>">
          <?php echo esc_html($e->name ?: 'Praticienne #' . $e->id); ?>
          <?php if (current_user_can('manage_options')) echo ' [services: ' . esc_html($service_ids_str) . ']'; ?>
        </option>
      <?php endforeach; ?>
    </select>
    <div id="add-booking-date-time-fields">
      <label for="add-booking-date">Date</label>
      <input id="add-booking-date" name="date" type="date" required>
      <label for="add-booking-time">Heure</label>
      <input id="add-booking-time" name="time" type="time" required>
    </div>
    <label for="add-booking-status">Statut</label>
    <select id="add-booking-status" name="status" required>
      <option value="en_attente">En attente</option>
      <option value="confirmee">Confirmée</option>
      <option value="annulee">Annulée</option>
      <option value="complete">Complété</option>
      <option value="no_show">No show</option>
    </select>
    <label>Extras</label>
    <?php foreach($extras as $ex): ?>
      <label style="margin-right:1em;"><input type="checkbox" name="extras[]" value="<?php echo $ex->id; ?>"> <?php echo esc_html($ex->name); ?></label>
    <?php endforeach; ?>
    <div class="ib-conflict-msg" style="color:#e05c5c;font-weight:600;margin-bottom:1em;display:none;"></div>
    <button class="ib-btn accent" type="submit" name="add_booking">Ajouter</button>
    <button type="button" class="ib-btn cancel" id="ib-close-add-booking-modal">Annuler</button>
  </form>
  <script>
  // Masquer les champs date/heure si déjà pré-sélectionnés (depuis la matrice)
  function hideDateTimeFieldsIfPrefilled() {
    const dateInput = document.getElementById('add-booking-date');
    const timeInput = document.getElementById('add-booking-time');
    const dateTimeFields = document.getElementById('add-booking-date-time-fields');
    if (dateInput && timeInput && dateInput.value && timeInput.value) {
      dateTimeFields.style.display = 'none';
    } else {
      dateTimeFields.style.display = '';
    }
  }
  document.getElementById('ib-add-booking-modal').addEventListener('show', hideDateTimeFieldsIfPrefilled);
  // Validation téléphone (intl-tel-input)
  if (window.intlTelInput) {
    var addPhoneInput = window.intlTelInput(document.getElementById('add-booking-client-phone'), {
      preferredCountries: ['dz', 'fr'],
      separateDialCode: true,
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.1.1/js/utils.js",
      autoHideDialCode: false,
      autoPlaceholder: "polite",
      formatOnDisplay: true,
      nationalMode: false,
      initialCountry: "dz"
    });
    document.getElementById('add-booking-client-phone').addEventListener('blur', function() {
      if (addPhoneInput.isValidNumber()) {
        this.value = addPhoneInput.getNumber();
        this.classList.remove('invalid-phone');
        this.classList.add('valid-phone');
      } else {
        this.classList.remove('valid-phone');
        this.classList.add('invalid-phone');
      }
    });
  }
  // Gestion des conflits de créneau (AJAX)
  function checkConflict() {
    var s = document.getElementById('add-booking-service').value,
        e = document.getElementById('add-booking-employee').value,
        d = document.getElementById('add-booking-date').value,
        t = document.getElementById('add-booking-time').value,
        msg = document.querySelector('.ib-conflict-msg'),
        submitBtn = document.querySelector('.ib-booking-form-admin button[type="submit"]');
    if (!s || !e || !d || !t) {
      msg.style.display = 'none';
      submitBtn.disabled = false;
      return;
    }
    fetch(window.ajaxurl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=ib_check_booking_conflict&service_id=${s}&employee_id=${e}&date=${d}&time=${t}`
    })
    .then(r => r.json())
    .then(res => {
      if (res && res.success && res.conflict) {
        msg.textContent = 'Ce créneau est déjà réservé pour cette praticienne/service.';
        msg.style.display = 'block';
        submitBtn.disabled = true;
      } else {
        msg.textContent = '';
        msg.style.display = 'none';
        submitBtn.disabled = false;
      }
    });
  }
  document.getElementById('add-booking-service').addEventListener('change', checkConflict);
  document.getElementById('add-booking-employee').addEventListener('change', checkConflict);
  document.getElementById('add-booking-date').addEventListener('change', checkConflict);
  document.getElementById('add-booking-time').addEventListener('change', checkConflict);
  // Feedback UX : focus sur le premier champ
  document.getElementById('ib-add-booking-modal').addEventListener('show', function() {
    setTimeout(function() {
      document.getElementById('add-booking-client-name').focus();
    }, 100);
  });
  </script>
</div> 