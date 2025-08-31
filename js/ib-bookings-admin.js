// JS pour la vérification du numéro de téléphone dans le modal de réservation
jQuery(function ($) {
    console.log('[IB] JS chargé');
    // Fonction pour vérifier si un numéro de téléphone existe dans la base de données
    function checkPhoneExists(phone) {
        const messageDiv = document.getElementById('ib-phone-check-msg');
        messageDiv.innerHTML = '<span style="color: #666;">Vérification en cours...</span>';
        const formData = new FormData();
        formData.append('action', 'check_phone_exists');
        formData.append('phone', phone);
        formData.append('nonce', ib_vars.ajax_nonce); // Utilise la variable JS localisée
        fetch(ib_vars.ajax_url, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayPhoneCheckResult(data.data);
                } else {
                    messageDiv.innerHTML = '<span style="color: #b00;">Erreur lors de la vérification</span>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                messageDiv.innerHTML = '<span style="color: #b00;">Erreur de connexion</span>';
            });
    }

    function displayPhoneCheckResult(result) {
        const messageDiv = document.getElementById('ib-phone-check-msg');
        const clientNameInput = document.getElementById('add-booking-client-name');
        const clientEmailInput = document.getElementById('add-booking-client-email');
        if (result.exists) {
            let msg = '';
            if (result.source === 'client') {
                msg = `Client trouvé: <span style='color:#1ca127;font-weight:bold;'>${result.client_name}</span>`;
                if (result.client_name) clientNameInput.value = result.client_name;
                if (result.client_email) clientEmailInput.value = result.client_email;
            } else if (result.source === 'booking') {
                msg = `<span style="white-space:nowrap;">Numéro existant: <span style="color:#1ca127;font-weight:bold;">${result.client_name}</span></span>`;
                if (result.client_name) clientNameInput.value = result.client_name;
            }
            messageDiv.innerHTML = `<p style='margin:4px 0 0 0;font-size:14px;color:#222;display:inline;'>${msg}</p>`;
            clientNameInput.style.backgroundColor = '#f0f0f0';
            clientNameInput.title = 'Client existant - modifiez avec précaution';
            if (window.ibMsgTimeout) clearTimeout(window.ibMsgTimeout);
            window.ibMsgTimeout = setTimeout(() => {
                messageDiv.innerHTML = '';
            }, 20000);
        } else {
            messageDiv.innerHTML = '<span style="color:#d63638;white-space:nowrap;">Nouveau client</span>';

            clientNameInput.style.backgroundColor = '';
            clientNameInput.title = '';
            clientNameInput.value = '';
            clientEmailInput.value = '';
        }
    }

    function formatPhone(phone) {
        return phone.replace(/\D/g, '');
    }

    // Attacher l'écouteur dès que le DOM est prêt (jQuery ready)
    const phoneInput = document.getElementById('add-booking-client-phone');
    const messageDiv = document.getElementById('ib-phone-check-msg');
    if (phoneInput) {
        console.log('[IB] Input téléphone trouvé');
        let timeoutId;
        phoneInput.addEventListener('input', function () {
            const phone = this.value;
            const cleanPhone = formatPhone(phone);
            clearTimeout(timeoutId);
            if (cleanPhone.length < 10) {
                messageDiv.innerHTML = '';
                const clientNameInput = document.getElementById('add-booking-client-name');
                if (clientNameInput.style.backgroundColor) {
                    clientNameInput.style.backgroundColor = '';
                    clientNameInput.title = '';
                }
                return;
            }
            if (cleanPhone.length === 10) {
                timeoutId = setTimeout(() => {
                    checkPhoneExists(cleanPhone);
                }, 500);
            }
            if (cleanPhone.length > 10) {
                messageDiv.innerHTML = '<span style="color: #d63638; white-space:nowrap;">Le numéro doit contenir 10 chiffres</span>';
            }
        });
        phoneInput.addEventListener('blur', function () {
            const cleanPhone = formatPhone(this.value);
            if (cleanPhone.length === 10) {
                this.value = cleanPhone.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
            }
        });
    } else {
        console.warn('[IB] Input téléphone non trouvé');
    }
});
