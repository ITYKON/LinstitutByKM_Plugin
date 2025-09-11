jQuery(document).ready(function($) {
    console.log('Client lookup script loaded');
    
    // Fonction pour initialiser la recherche de client par téléphone
    function initClientLookup() {
        console.log('Initializing client lookup...');
        
        // Trouver le formulaire d'ajout de réservation dans la modal
        const $form = $('form.ib-booking-form-admin');
        if (!$form.length) {
            console.log('Aucun formulaire de réservation admin trouvé');
            return;
        }

        // Trouver le champ de téléphone dans le formulaire
        const $phoneInput = $('#add-booking-client-phone');
        if (!$phoneInput.length) {
            console.log('Aucun champ de téléphone trouvé dans le formulaire admin');
            return;
        }

        // Créer un conteneur pour les messages
        const $messageContainer = $('<div class="client-lookup-message" style="margin-top: 5px; font-size: 13px; min-height: 20px;"></div>').insertAfter($phoneInput.parent());
        let lookupTimeout;

        // Fonction pour effectuer la recherche
        function searchClient(phone) {
            console.log('Searching client with phone:', phone);
            
            if (!phone || phone.length < 3) {
                $messageContainer.hide();
                return;
            }

            // Nettoyer le numéro de téléphone (ne garder que les chiffres)
            const cleanPhone = phone.replace(/\D/g, '');
            if (cleanPhone.length < 8) {
                $messageContainer.hide();
                return;
            }

            // Afficher un message de chargement
            $messageContainer.html('<span style="color: #666;">Recherche en cours...</span>').show();

            // Annuler la recherche précédente si elle existe
            if (lookupTimeout) {
                clearTimeout(lookupTimeout);
            }

            // Délai avant la recherche pour éviter trop de requêtes
            lookupTimeout = setTimeout(function() {
                $.ajax({
                    url: bookingAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lookup_client',
                        phone: cleanPhone,
                        nonce: bookingAjax.nonce
                    },
                    beforeSend: function() {
                        $messageContainer.html('<div class="loading">Recherche en cours...</div>').show();
                    },
                    success: function(response) {
                        console.log('AJAX response:', response);
                        
                        if (response.success && response.data) {
                            const client = response.data;
                            $messageContainer.html('<span style="color: #1ca97c;"> Client trouvé : ' + client.name + '</span>').show();
                            
                            // Mettre à jour les champs du formulaire
                            if (client.name) {
                                // Sépare le nom complet en prénom et nom
                                const nameParts = client.name.trim().split(' ');
                                const firstName = nameParts.shift() || '';
                                const lastName = nameParts.join(' ') || '';
                                
                                $('#add-booking-client-firstname').val(firstName).trigger('change');
                                $('#add-booking-client-lastname').val(lastName).trigger('change');
                            }
                            if (client.email) {
                                $('#add-booking-client-email').val(client.email).trigger('change');
                            }
                            
                            // Mettre le focus sur le champ suivant
                            $('#add-booking-service').focus();
                        } else {
                            const errorMsg = response.data && response.data.message || 'Aucun client trouvé avec ce numéro';
                            $messageContainer.html('<span style="color: #e74c3c;">' + errorMsg + '</span>').show();
                            
                            // Effacer les champs si aucun client trouvé
                            if (response.data && response.data.clear_fields) {
                                $('#add-booking-client-firstname, #add-booking-client-lastname, #add-booking-client-email').val('');
                                $('#add-booking-client-firstname').focus();
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        $messageContainer.html('<span style="color: #e74c3c;">Erreur lors de la recherche du client</span>').show();
                    }
                });
            }, 1000); // Délai de 1000ms après la dernière frappe
        }

        // Fonction pour trouver un champ dans le formulaire par nom ou placeholder
        function findFormField(fieldName) {
            // Essayer par nom
            let $field = $form.find(`[name*="${fieldName}"]`);
            
            // Si non trouvé, essayer par ID
            if (!$field.length) {
                $field = $form.find(`#${fieldName}, [id*="${fieldName}"]`);
            }
            
            // Si toujours pas trouvé, essayer par placeholder
            if (!$field.length) {
                const placeholders = {
                    'firstname': ['prénom', 'prenom', 'firstname', 'first-name'],
                    'lastname': ['nom', 'lastname', 'last-name', 'name'],
                    'email': ['email', 'courriel', 'mail']
                };
                
                const searchTerms = placeholders[fieldName] || [];
                for (const term of searchTerms) {
                    $field = $form.find(`[placeholder*="${term}" i]`);
                    if ($field.length) break;
                }
            }
            
            return $field.first();
        }

        // Écouter les changements sur le champ téléphone
        $phoneInput.on('input', function() {
            const phone = $(this).val().trim();
            searchClient(phone);
        });

        // Gérer le clic sur le lien de remplissage automatique
        $(document).on('click', '.client-found-link', function(e) {
            e.preventDefault();
            const clientData = $(this).data('client');
            if (clientData) {
                // Trouver les champs du formulaire
                const $firstnameField = findFormField('firstname');
                const $lastnameField = findFormField('lastname');
                const $emailField = findFormField('email');
                
                // Remplir les champs trouvés
                if ($firstnameField.length) $firstnameField.val(clientData.firstname || '').trigger('change');
                if ($lastnameField.length) $lastnameField.val(clientData.lastname || '').trigger('change');
                if ($emailField.length) $emailField.val(clientData.email || '').trigger('change');
                
                // Masquer le message après un court délai
                $messageContainer.fadeOut(300, function() {
                    $(this).remove();
                });
            }
        });
        
        console.log('Initialisation de la recherche de client par téléphone terminée');
    }

    // Attendre que le formulaire soit chargé
    function waitForForm() {
        // Attendre que la modal soit ouverte
        $(document).on('click', '#ib-open-add-booking-modal', function() {
            // Petit délai pour laisser à la modal le temps de s'ouvrir
            setTimeout(initClientLookup, 300);
        });
    }
    
    // Démarrer l'attente du formulaire
    waitForForm();
    
    // Initialiser aussi au chargement de la page si la modal est déjà ouverte (après rechargement par exemple)
    if ($('#ib-add-booking-modal').is(':visible')) {
        initClientLookup();
    }
});
