/**
 * Solution alternative pour la génération PDF des tickets de réservation
 * Corrige le problème de pages vides
 */

// Fallback pour la fonction de notification
if (typeof showBookingNotification === 'undefined') {
    window.showBookingNotification = function(message) {
        console.log('[Notification]', message);
        // Essayer d'utiliser la fonction de notification du thème si elle existe
        if (typeof showNotification === 'function') {
            showNotification(message);
        }
    };
}

// Fonction alternative pour générer le PDF avec une approche plus simple
function generateTicketPDFAlternative(ticketElement, buttonElement, bookingData) {
    console.log("🎫 [Alternative] Début génération PDF...");
    
    // Vérifier que html2pdf est disponible
    if (!window.html2pdf) {
        console.error("❌ html2pdf non disponible");
        showBookingNotification("Erreur: Générateur PDF non disponible");
        return;
    }
    
    // Masquer le bouton
    if (buttonElement) {
        buttonElement.style.display = "none";
    }
    
    try {
        // Créer un conteneur temporaire avec le contenu du ticket
        const tempContainer = document.createElement('div');
        tempContainer.style.cssText = `
            position: absolute;
            left: -9999px;
            top: 0;
            width: 600px;
            background: white;
            padding: 30px;
            font-family: Arial, sans-serif;
            color: black;
            box-sizing: border-box;
        `;
        
        // Créer le contenu HTML du ticket de manière simple
        const ticketHTML = createSimpleTicketHTML(bookingData);
        tempContainer.innerHTML = ticketHTML;
        
        // Ajouter au DOM
        document.body.appendChild(tempContainer);
        
        // Forcer le rendu
        tempContainer.offsetHeight;
        
        // Configuration PDF simplifiée
        const options = {
            margin: 0.5,
            filename: `ticket-reservation-${bookingData.date || new Date().toISOString().split('T')[0]}.pdf`,
            image: { 
                type: 'jpeg', 
                quality: 0.98 
            },
            html2canvas: { 
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                useCORS: true,
                allowTaint: true
            },
            jsPDF: { 
                unit: 'in', 
                format: 'a4', 
                orientation: 'portrait' 
            }
        };
        
        console.log("🎫 [Alternative] Configuration:", options);
        console.log("🎫 [Alternative] Contenu:", tempContainer.innerHTML.substring(0, 200));
        
        // Générer le PDF
        html2pdf()
            .set(options)
            .from(tempContainer)
            .save()
            .then(() => {
                console.log("🎫 [Alternative] PDF généré avec succès");
                // Nettoyer
                if (document.body.contains(tempContainer)) {
                    document.body.removeChild(tempContainer);
                }
                if (buttonElement) {
                    buttonElement.style.display = "block";
                }
                showBookingNotification("Ticket téléchargé avec succès !");
            })
            .catch((error) => {
                console.error("❌ [Alternative] Erreur PDF:", error);
                // Nettoyer
                if (document.body.contains(tempContainer)) {
                    document.body.removeChild(tempContainer);
                }
                if (buttonElement) {
                    buttonElement.style.display = "block";
                }
                showBookingNotification("Erreur lors de la génération du PDF: " + error.message);
            });
            
    } catch (error) {
        console.error("❌ [Alternative] Erreur générale:", error);
        if (buttonElement) {
            buttonElement.style.display = "block";
        }
        showBookingNotification("Erreur lors de la génération du PDF: " + error.message);
    }
}

// Créer le HTML du ticket de manière minimaliste
function createSimpleTicketHTML(bookingData) {
    console.log("🎫 Création du contenu HTML du ticket");
    
    // Vérifier et formater les données
    const booking = bookingData || {};
    const services = booking.services || [];
    const client = booking.client || {};
    const date = booking.date ? new Date(booking.date) : new Date();
    
    // Formater la date et l'heure
    const options = { 
        weekday: 'long', 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric',
        hour: '2-digit', 
        minute: '2-digit' 
    };
    const formattedDate = date.toLocaleDateString('fr-FR', options);
    const currentDate = new Date();
    const formattedCurrentDate = currentDate.toLocaleDateString('fr-FR');
    const formattedCurrentTime = currentDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});

    // Créer le contenu HTML de base
    let html = [];
    
    // Ajouter le conteneur principal
    html.push('<div style="font-family: Arial, sans-serif; max-width: 100%; padding: 15px; box-sizing: border-box;">');
    
    // En-tête
    html.push('  <div style="text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #eee;">');
    html.push('    <h1 style="color: #2c3e50; margin: 0 0 5px 0; font-size: 20px; font-weight: 600;">TICKET DE RÉSERVATION</h1>');
    html.push('    <p style="color: #7f8c8d; margin: 0; font-size: 14px;">' + formattedDate + '</p>');
    html.push('  </div>');
    
    // Section client
    html.push('  <div style="margin-bottom: 25px;">');
    html.push('    <div style="font-weight: 600; margin-bottom: 5px; color: #2c3e50;">');
    html.push('      ' + (client.prenom || '') + ' ' + (client.nom || ''));
    html.push('    </div>');
    html.push('    <div style="color: #7f8c8d; font-size: 14px;">');
    html.push('      ' + (client.email || '') + '<br>');
    html.push('      ' + (client.telephone || ''));
    html.push('    </div>');
    html.push('  </div>');
    
    // Section services
    html.push('  <div style="margin-bottom: 20px;">');
    html.push('    <div style="font-weight: 600; margin-bottom: 10px; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 5px;">');
    html.push('      Détails de la réservation');
    html.push('    </div>');

    // Ajouter chaque service
    services.forEach(function(service) {
        const startTime = service.startTime ? new Date(service.startTime) : new Date();
        const serviceTime = startTime.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Utiliser la couleur de l'employé si disponible
        const employeeColor = service.employeeColor || '#4f8cff';
        
        // Ajouter le service
        html.push('    <div style="margin-bottom: 10px; padding: 12px; background: #fff; border-radius: 6px; border-left: 4px solid ' + employeeColor + '; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">');
        html.push('      <div style="display: flex; justify-content: space-between; align-items: flex-start;">');
        html.push('        <div>');
        html.push('          <div style="font-weight: 600; color: #2c3e50; margin-bottom: 3px;">' + (service.nom || 'Service') + '</div>');
        html.push('          <div style="font-size: 13px; color: #64748b;">' + serviceTime + ' • ' + (service.employe || 'Sans praticien') + '</div>');
        html.push('        </div>');
        html.push('        <div style="font-weight: 700; color: #2c3e50;">' + (service.prix ? parseFloat(service.prix).toFixed(2) + ' €' : '-') + '</div>');
        html.push('      </div>');
        html.push('    </div>');
    });
    
    // Fermer la section services
    html.push('  </div>');
    
    // Section informations pratiques
    html.push('  <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 20px; margin-bottom: 20px;">');
    html.push('    <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #166534; display: flex; align-items: center; gap: 8px;">');
    html.push('      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">');
    html.push('        <circle cx="12" cy="12" r="10"></circle>');
    html.push('        <line x1="12" y1="16" x2="12" y2="12"></line>');
    html.push('        <line x1="12" y1="8" x2="12.01" y2="8"></line>');
    html.push('      </svg>');
    html.push('      Informations importantes');
    html.push('    </h3>');
    html.push('    <ul style="margin: 0; padding-left: 20px; color: #166534; font-size: 14px; line-height: 1.6;">');
    html.push('      <li>Présentez ce ticket à votre arrivée</li>');
    html.push('      <li>Merci d\'arriver 5 minutes avant l\'heure prévue</li>');
    html.push('      <li>En cas d\'empêchement, merci de nous prévenir au moins 24h à l\'avance</li>');
    html.push('    </ul>');
    html.push('  </div>');
  
    // Pied de page
    html.push('  <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e2e8f0; margin-top: 20px; color: #64748b; font-size: 13px; line-height: 1.5;">');
    html.push('    <p style="margin: 0 0 10px 0;">');
    html.push('      <strong>L\'INSTITUT BY KM</strong><br>');
    html.push('      20 Rue des frères Mellali<br>');
    html.push('      06000 Béjaïa, Algérie');
    html.push('    </p>');
    html.push('    <p style="margin: 0; font-size: 12px;">');
    html.push('      Tél: 0770 30 73 85 | Email: contact@linstitutbykm.dz');
    html.push('    </p>');
    html.push('    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b;">');
    html.push('      <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">');
    html.push('        <span>Total:</span>');
    html.push('        <span style="font-weight: 700;">' + (booking.price || '-') + '</span>');
    html.push('      </div>');
    html.push('      <div style="font-size: 11px; margin-top: 10px;">');
    html.push('        Ticket généré le ' + formattedCurrentDate + ' à ' + formattedCurrentTime);
    html.push('      </div>');
    html.push('    </div>');
    html.push('  </div>');
  
    // Fermer le conteneur principal
    html.push('</div>');
  
    // Retourner le HTML généré
    return html.join('\n');
}

// Fonction pour extraire les données de réservation depuis l'état global
function extractBookingDataFromState() {
    // Vérifier si bookingState est défini (approche plus robuste)
    if (typeof bookingState !== 'undefined' && bookingState !== null) {
        try {
            const cart = Array.isArray(bookingState.cart) ? bookingState.cart : [];

            // Convertir HH:MM en Date si possible
            const buildStartTime = (dateStr, slotStr) => {
                try {
                    if (!dateStr || !slotStr) return null;
                    const [h, m] = String(slotStr).split(':').map((x) => parseInt(x, 10));
                    const d = new Date(dateStr);
                    if (!isNaN(h)) d.setHours(h);
                    if (!isNaN(m)) d.setMinutes(m);
                    d.setSeconds(0);
                    d.setMilliseconds(0);
                    return d.toISOString();
                } catch (e) {
                    return null;
                }
            };

            // Mapper le panier vers le format attendu par createSimpleTicketHTML
            const services = cart.map((item) => {
                const priceNum = parseFloat(item.price);
                return {
                    nom: (item.service && item.service.name) || 'Service',
                    employe: (item.employee && item.employee.name) || 'Sans praticien',
                    startTime: buildStartTime(item.date, item.slot),
                    prix: isNaN(priceNum) ? undefined : priceNum,
                    // Couleur employé si disponible ailleurs (facultatif)
                    employeeColor: (item.employee && item.employee.color) || undefined,
                };
            });

            // Calcul du total (en DA)
            let total = 0;
            let minTotal = 0;
            let hasVariable = false;
            cart.forEach((item) => {
                const p = parseFloat(item.price);
                if (!isNaN(p) && p > 0) {
                    total += p;
                } else if (item.service && item.service.variable_price == 1) {
                    hasVariable = true;
                    const min = Number(item.service.min_price) || 0;
                    minTotal += min;
                }
            });

            const priceText = hasVariable
                ? `À partir de ${(total + minTotal).toLocaleString()} DA`
                : total > 0
                ? `${total.toLocaleString()} DA`
                : '-';

            // Client
            const client = {
                prenom: (bookingState.client && bookingState.client.firstname) || '',
                nom: (bookingState.client && bookingState.client.lastname) || '',
                email: (bookingState.client && bookingState.client.email) || '',
                telephone: (bookingState.client && bookingState.client.phone) || '',
            };

            return {
                service: (bookingState.selectedService && bookingState.selectedService.name) || '-',
                employee: (bookingState.selectedEmployee && bookingState.selectedEmployee.name) || '-',
                date: bookingState.selectedDate || '-',
                slot: bookingState.selectedSlot || '-',
                clientName: `${client.prenom} ${client.nom}`.trim() || '-',
                email: client.email || '-',
                phone: client.telephone || '-',
                price: priceText,
                services,
                client,
            };
        } catch (err) {
            console.warn('extractBookingDataFromState fallback (error):', err);
        }
    }
    
    // Fallback: extraire depuis le DOM
    const ticket = document.querySelector('.booking-ticket-modern');
    if (ticket) {
        const getValue = (selector) => {
            const element = ticket.querySelector(selector);
            return element ? element.textContent.trim() : '-';
        };
        
        return {
            service: getValue('.ticket-details div:nth-child(1) .ticket-value'),
            employee: getValue('.ticket-details div:nth-child(2) .ticket-value'),
            date: getValue('.ticket-details div:nth-child(3) .ticket-value'),
            slot: getValue('.ticket-details div:nth-child(4) .ticket-value'),
            clientName: getValue('.ticket-details div:nth-child(5) .ticket-value'),
            email: getValue('.ticket-details div:nth-child(6) .ticket-value'),
            phone: getValue('.ticket-details div:nth-child(7) .ticket-value'),
            price: getValue('.ticket-details div:nth-child(8) .ticket-value'),
            // Ajouter un tableau services vide pour la compatibilité avec createSimpleTicketHTML
            services: []
        };
    }
    
    return {
        service: '-',
        employee: '-',
        date: '-',
        slot: '-',
        clientName: '-',
        email: '-',
        phone: '-',
        price: '-'
    };
}

// Fonction publique pour remplacer la génération PDF existante
window.generateTicketPDFFixed = function() {
    const buttonElement = document.getElementById('download-ticket-btn');
    const bookingData = extractBookingDataFromState();
    
    // Désactiver le bouton pendant la génération
    if (buttonElement) {
        buttonElement.disabled = true;
        buttonElement.textContent = 'Génération en cours...';
    }
    
    // Petit délai pour permettre à l'interface de se mettre à jour
    setTimeout(function() {
        try {
            generateTicketPDFAlternative(null, buttonElement, bookingData);
            if (buttonElement) {
                buttonElement.textContent = 'Télécharger le ticket';
                buttonElement.disabled = false;
            }
        } catch (error) {
            console.error("❌ Erreur lors de la génération du PDF:", error);
            showBookingNotification("Erreur lors de la génération du PDF");
            if (buttonElement) {
                buttonElement.textContent = 'Télécharger le ticket';
                buttonElement.disabled = false;
            }
        }
    }, 100);
};

// Initialisation
if (typeof document !== 'undefined') {
    console.log("🎫 PDF Ticket Fix chargé - Utilisez window.generateTicketPDFFixed()");
}
