// JS pour la recherche client dans le booking form admin
jQuery(function ($) {
  // Vérifier si le formulaire de réservation est présent
  if ($("#add-booking-client-phone").length === 0) return;
  
  // Ajout du message d'information
  if ($("#client-message").length === 0) {
    $("#add-booking-client-phone").after(
      '<div id="client-message" style="margin-top:8px;color:#d35400;min-height:20px;"></div>'
    );
  }

  // Délai pour éviter trop de requêtes
  var delayTimer;
  
  $("#add-booking-client-phone").on("input", function () {
    var $this = $(this);
    var phone = $this.val().replace(/\D/g, ''); // Nettoyer le numéro
    
    // Effacer le message précédent
    $("#client-message").html('');
    
    // Effacer le timer précédent
    clearTimeout(delayTimer);
    
    // Attendre que l'utilisateur ait fini de taper (500ms de délai)
    delayTimer = setTimeout(function() {
      if (phone.length > 2) {
        // Afficher un indicateur de chargement
        $("#client-message").html('<span style="color:#666;">Recherche en cours...</span>');
        
        // Créer un nouvel objet FormData pour gérer correctement les données
      var formData = new FormData();
      formData.append('action', 'ib_client_lookup');
      formData.append('nonce', ib_ajax_object.nonce);
      formData.append('phone', phone);
      
      // Effectuer la requête AJAX
      $.ajax({
          url: ib_ajax_object.ajax_url,
          type: 'POST',
          dataType: 'json',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            if (response.success && response.data) {
              $("#client-message").html(
                '<span style="cursor:pointer;color:#27ae60;text-decoration:underline;">✓ Client trouvé. Cliquez pour remplir automatiquement.</span>'
              );
              
              // Gestion du clic sur le message
              $("#client-message span")
                .off("click")
                .on("click", function() {
                  // Si le nom complet est disponible dans response.data.name, on le divise
                  if (response.data.name) {
                    const nameParts = response.data.name.trim().split(' ');
                    const firstName = nameParts.shift() || '';
                    const lastName = nameParts.join(' ') || '';
                    $("#add-booking-client-firstname").val(firstName);
                    $("#add-booking-client-lastname").val(lastName);
                  } else {
                    // Fallback sur firstname/lastname séparés si name n'est pas disponible
                    $("#add-booking-client-firstname").val(response.data.firstname || '');
                    $("#add-booking-client-lastname").val(response.data.lastname || '');
                  }
                  $("#add-booking-client-email").val(response.data.email || '');
                  // On ne modifie pas le numéro de téléphone pour éviter les erreurs de formatage
                  
                  // Mettre en surbrillance les champs remplis
                  $("#add-booking-client-lastname, #add-booking-client-firstname, #add-booking-client-email")
                    .addClass('ib-field-filled')
                    .delay(2000)
                    .queue(function() {
                      $(this).removeClass('ib-field-filled').dequeue();
                    });
                });
            } else {
              // Aucun client trouvé
              if (response.data && response.data.message) {
                $("#client-message").html('<span style="color:#e74c3c;">' + response.data.message + '</span>');
              } else {
                $("#client-message").html('<span style="color:#e74c3c;">Aucun client trouvé avec ce numéro</span>');
              }
              
              // Réinitialiser uniquement si les champs sont vides (pour ne pas écraser la saisie manuelle)
              if (!$("#add-booking-client-lastname").val()) $("#add-booking-client-lastname").val('');
              if (!$("#add-booking-client-firstname").val()) $("#add-booking-client-firstname").val('');
              if (!$("#add-booking-client-email").val()) $("#add-booking-client-email").val('');
            }
          },
          error: function(xhr, status, error) {
            console.error('Erreur lors de la recherche du client:', error);
            $("#client-message").html('<span style="color:#e74c3c;">Erreur lors de la recherche. Veuillez réessayer.</span>');
          }
        });
      }
    }, 500); // Délai de 500ms après la fin de la frappe
  });
  
  // Style pour les champs remplis automatiquement
  $('<style>' +
    '.ib-field-filled {' +
    '  background-color: #f8f9fa !important;' +
    '  border-color: #27ae60 !important;' +
    '  transition: all 0.3s ease;' +
    '}' +
    '</style>').appendTo('head');
});
