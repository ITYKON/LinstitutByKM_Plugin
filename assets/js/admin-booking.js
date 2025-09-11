jQuery(document).ready(function($) {
    console.log('Admin booking script loaded');
    console.log('jQuery version:', $.fn.jquery);
    
    // Vérifier que l'élément existe
    const $phoneInput = $('#client_phone');
    console.log('Phone input found:', $phoneInput.length > 0);
    
    if ($phoneInput.length === 0) {
        console.error('Erreur: Le champ client_phone n\'a pas été trouvé dans le DOM');
        return;
    }
    
    // Handle phone number lookup on Enter key press
    $phoneInput.on('keydown', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            console.log('Enter key pressed');
            
            const phoneNumber = $(this).val().trim();
            console.log('Phone number:', phoneNumber);
            
            if (!phoneNumber) {
                console.log('No phone number entered');
                return;
            }

            // Show loading state
            const $field = $(this);
            $field.prop('disabled', true).addClass('loading');
            
            // Vérifier que bookingAjax est défini
            if (typeof bookingAjax === 'undefined') {
                console.error('Erreur: bookingAjax n\'est pas défini');
                console.log('Vérifiez que wp_localize_script est correctement configuré avec la clé "bookingAjax"');
                $field.prop('disabled', false).removeClass('loading');
                return;
            }
            
            // Préparer les données AJAX
            const ajaxData = {
                action: 'lookup_client',
                phone: phoneNumber,
                nonce: bookingAjax.nonce
            };
            
            console.log('Envoi de la requête AJAX:', ajaxData);
            console.log('URL AJAX:', bookingAjax.ajaxurl);
            
            // Send AJAX request
            $.ajax({
                url: bookingAjax ? bookingAjax.ajaxurl : ajaxurl,
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX success:', response);
                    if (response.success) {
                        // Auto-fill the form fields
                        $('#first_name').val(response.firstname || '');
                        $('#last_name').val(response.name || '');
                        $('#email').val(response.email || '');
                        
                        // Show success message
                        showClientLookupMessage('Cliente trouvée : ' + (response.firstname || '') + ' ' + (response.name || ''), 'success');
                    } else {
                        // Clear other fields if client not found
                        if (response.message) {
                            showClientLookupMessage(response.message, 'error');
                        } else {
                            showClientLookupMessage('Une erreur est survenue lors de la recherche de la cliente', 'error');
                        }
                        // Don't clear the phone field, just the others
                        $('#first_name, #last_name, #email').val('');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusText: xhr.statusText,
                        readyState: xhr.readyState
                    });
                    showClientLookupMessage('Erreur lors de la recherche de la cliente: ' + error, 'error');
                },
                complete: function() {
                    $field.prop('disabled', false).removeClass('loading');
                }
            });
        }
    });

    // Function to show messages
    function showClientLookupMessage(message, type = 'info') {
        // Remove any existing messages
        $('.client-lookup-message').remove();
        
        // Create and show new message
        const messageHtml = `
            <div class="notice notice-${type} client-lookup-message" style="margin: 10px 0; padding: 10px;">
                <p>${message}</p>
            </div>
        `;
        
        // Insert after the phone field
        $('#client_phone').closest('tr').after(`
            <tr class="client-lookup-message">
                <td colspan="2">${messageHtml}</td>
            </tr>
        `);
    }
});
