jQuery(document).ready(function ($) {
  // Gestion des formulaires
  $(".ib-admin-form").on("submit", function (e) {
    e.preventDefault();
    var $form = $(this);
    var $submit = $form.find('button[type="submit"]');
    var originalText = $submit.text();

    $submit.prop("disabled", true).text("Enregistrement...");

    $.ajax({
      url: window.ajaxurl,
      type: "POST",
      data: $form.serialize(),
      success: function (response) {
        if (response.success) {
          alert("Enregistré avec succès !");
          if (response.redirect) {
            window.location.href = response.redirect;
          }
        } else {
          alert("Erreur : " + response.data);
        }
      },
      error: function () {
        alert("Erreur de communication avec le serveur");
      },
      complete: function () {
        $submit.prop("disabled", false).text(originalText);
      },
    });
  });

  // Gestion des suppressions
  $(".ib-delete").on("click", function (e) {
    e.preventDefault();
    if (!confirm("Êtes-vous sûr de vouloir supprimer cet élément ?")) {
      return;
    }

    var $link = $(this);
    var id = $link.data("id");
    var type = $link.data("type");

    $.ajax({
      url: window.ajaxurl,
      type: "POST",
      data: {
        action: "ib_delete_" + type,
        id: id,
        nonce: ib_admin.nonce,
      },
      success: function (response) {
        if (response.success) {
          $link.closest("tr").fadeOut(function () {
            $(this).remove();
          });
        } else {
          alert("Erreur : " + response.data);
        }
      },
    });
  });

  // Gestion des statuts de réservation
  $(".ib-booking-status").on("change", function () {
    var $select = $(this);
    var id = $select.data("id");
    var status = $select.val();

    $.ajax({
      url: window.ajaxurl,
      type: "POST",
      data: {
        action: "ib_update_booking_status",
        id: id,
        status: status,
        nonce: ib_admin.nonce,
      },
      success: function (response) {
        if (!response.success) {
          alert("Erreur : " + response.data);
          $select.val($select.data("original"));
        }
      },
    });
  });

  // Gestion des filtres
  $(".ib-filter").on("change", function () {
    var $form = $(this).closest("form");
    $form.submit();
  });

  // Gestion des dates
  $(".ib-datepicker").datepicker({
    dateFormat: "dd/mm/yy",
    firstDay: 1,
  });

  // Gestion des timepickers
  // $('.ib-timepicker').timepicker({
  //   timeFormat: 'HH:mm',
  //   interval: 15,
  //   minTime: '08:00',
  //   maxTime: '20:00',
  //   defaultTime: '09:00',
  //   startTime: '08:00',
  //   dynamic: false,
  //   dropdown: true,
  //   scrollbar: true,
  // });

  // Gestion des sélecteurs de couleur
  $(".ib-colorpicker").wpColorPicker();

  // Gestion des uploads d'images
  $(".ib-upload-image").on("click", function (e) {
    e.preventDefault();
    var $button = $(this);
    var $input = $button.siblings('input[type="hidden"]');
    var $preview = $button.siblings(".ib-image-preview");

    var frame = wp.media({
      title: "Sélectionner une image",
      multiple: false,
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      $input.val(attachment.id);
      $preview.html('<img src="' + attachment.url + '" alt="">');
    });

    frame.open();
  });

  // Gestion des sélecteurs de service
  $(".ib-service-select").on("change", function () {
    var $select = $(this);
    var serviceId = $select.val();
    var $extras = $select.closest("form").find(".ib-extras");

    if (serviceId) {
      $.ajax({
        url: window.ajaxurl,
        type: "POST",
        data: {
          action: "ib_get_service_extras",
          service_id: serviceId,
          nonce: ib_admin.nonce,
        },
        success: function (response) {
          if (response.success) {
            $extras.html(response.data);
          }
        },
      });
    } else {
      $extras.empty();
    }
  });

  // Gestion des sélecteurs d'employé
  $(".ib-employee-select").on("change", function () {
    var $select = $(this);
    var employeeId = $select.val();
    var $calendar = $select.closest("form").find(".ib-calendar");

    if (employeeId) {
      $.ajax({
        url: window.ajaxurl,
        type: "POST",
        data: {
          action: "ib_get_employee_calendar",
          employee_id: employeeId,
          nonce: ib_admin.nonce,
        },
        success: function (response) {
          if (response.success) {
            $calendar.html(response.data);
          }
        },
      });
    } else {
      $calendar.empty();
    }
  });

  // Validation stricte et générique pour tous les formulaires admin (.ib-admin-form)
  function validateAdminForm($form, isSubmit) {
    var valid = true;
    $form.find(".ib-error-msg").remove();
    $form.find(".ib-error").removeClass("ib-error");
    // Nom
    var name = $form.find('[name="client_name"]');
    if (name.length && (name.hasClass("touched") || isSubmit)) {
      if (
        !name.val().trim() ||
        !/^[A-Za-zÀ-ÖØ-öø-ÿ\s'-]{2,}$/.test(name.val().trim())
      ) {
        valid = false;
        var msg = createErrorMsg(
          "Le nom doit contenir uniquement des lettres (min 2)."
        );
        name.addClass("ib-error");
        name.after(msg);
        setTimeout(function () {
          msg.css("opacity", 1);
        }, 10);
      }
    }
    // Email
    var email = $form.find('[name="client_email"]');
    if (email.length && (email.hasClass("touched") || isSubmit)) {
      if (
        !email.val().trim() ||
        !/^[^@\s]+@[^@\s]+\.(com|fr)$/.test(email.val().trim())
      ) {
        valid = false;
        var msg = createErrorMsg("Format email attendu : nom@domaine.com");
        email.addClass("ib-error");
        email.after(msg);
        setTimeout(function () {
          msg.css("opacity", 1);
        }, 10);
      }
    }
    // Téléphone
    var phone = $form.find('[name="client_phone"]');
    if (phone.length && (phone.hasClass("touched") || isSubmit)) {
      var phoneVal = phone.val() ? phone.val().replace(/\D/g, "") : "";
      if (!phoneVal || phoneVal.length < 6 || phoneVal.length > 15) {
        valid = false;
        var msg = createErrorMsg("Numéro : 6 à 15 chiffres.");
        phone.addClass("ib-error");
        phone.after(msg);
        setTimeout(function () {
          msg.css("opacity", 1);
        }, 10);
      }
    }
    // Désactiver ou activer le bouton submit
    $form.find('button[type="submit"]').prop("disabled", !valid);
    return valid;
  }
  // Nouvelle fonction pour générer un message d’erreur stylé avec icône et accessibilité
  function createErrorMsg(text) {
    return $(
      '<span class="ib-error-msg" style="color:#e05c5c;font-size:0.97em;display:block;margin-top:0.3em;font-weight:500;opacity:0;transition:opacity 0.3s;" aria-live="polite">' +
        '<svg style="vertical-align:middle;margin-right:0.4em;width:1.1em;height:1.1em;fill:none;stroke:#e05c5c;stroke-width:2;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><circle cx="12" cy="16" r="1.2"/></svg>' +
        text +
        "</span>"
    );
  }
  // Ajout de la classe .touched sur focus/input
  $(document).on(
    "focus input",
    '.ib-admin-form [name="client_name"], .ib-admin-form [name="client_email"], .ib-admin-form [name="client_phone"], .ib-booking-form-admin [name="client_name"], .ib-booking-form-admin [name="client_email"], .ib-booking-form-admin [name="client_phone"]',
    function () {
      $(this).addClass("touched");
    }
  );
  // Validation à la soumission
  $(document).on(
    "submit",
    ".ib-admin-form, .ib-booking-form-admin",
    function (e) {
      if (!validateAdminForm($(this), true)) {
        var $firstError = $(this).find(".ib-error").first();
        if ($firstError.length) {
          $("html,body").animate(
            { scrollTop: $firstError.offset().top - 80 },
            350
          );
          $firstError.focus();
        }
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
    }
  );
  // Validation en temps réel sur chaque champ
  $(document).on(
    "input change",
    '.ib-admin-form [name="client_name"], .ib-admin-form [name="client_email"], .ib-admin-form [name="client_phone"], .ib-booking-form-admin [name="client_name"], .ib-booking-form-admin [name="client_email"], .ib-booking-form-admin [name="client_phone"]',
    function () {
      var $form = $(this).closest("form");
      validateAdminForm($form, false);
    }
  );
  // Empêcher la saisie de chiffres/caractères spéciaux dans le champ nom (en temps réel)
  $(document).on("input", '[name="client_name"]', function () {
    var val = this.value;
    var clean = val.replace(/[^A-Za-zÀ-ÖØ-öø-ÿ\s'-]/g, "");
    if (val !== clean) {
      this.value = clean;
    }
  });
  // Empêcher la saisie de lettres/caractères spéciaux dans le champ téléphone (en temps réel) et limiter à 15 chiffres
  $(document).on("input", '[name="client_phone"]', function () {
    var val = this.value;
    // Autoriser uniquement les chiffres et limiter à 15 caractères
    var clean = val.replace(/[^0-9]/g, "").slice(0, 15);
    if (val !== clean) {
      this.value = clean;
    }
  });
  // Scroll automatique vers le premier champ en erreur à la soumission
  $(document).on(
    "submit",
    ".ib-admin-form, .ib-booking-form-admin",
    function (e) {
      var $form = $(this);
      if (!validateAdminForm($form, true)) {
        var $firstError = $form.find(".ib-error").first();
        if ($firstError.length) {
          $("html,body").animate(
            { scrollTop: $firstError.offset().top - 80 },
            350
          );
          $firstError.focus();
        }
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
    }
  );
});
