/**
 * Solution alternative pour la génération PDF des tickets de réservation
 * Corrige le problème de pages vides
 */

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

// Créer le HTML du ticket de manière simple et robuste
function createSimpleTicketHTML(bookingData) {
    return `
        <div style="
            width: 100%;
            max-width: 600px;
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 30px;
            font-family: Arial, sans-serif;
            color: #000000;
            box-sizing: border-box;
        ">
            <!-- Icône de succès -->
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="
                    width: 60px;
                    height: 60px;
                    background: #10b981;
                    border-radius: 50%;
                    margin: 0 auto;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    font-size: 30px;
                    font-weight: bold;
                ">✓</div>
            </div>
            
            <!-- Badge de confirmation -->
            <div style="
                background: #10b981;
                color: #ffffff;
                padding: 15px 25px;
                border-radius: 8px;
                font-weight: 600;
                text-align: center;
                margin: 20px 0;
                font-size: 20px;
            ">Réservation confirmée</div>
            
            <!-- Message de remerciement -->
            <div style="
                text-align: center;
                color: #374151;
                margin: 25px 0;
                font-size: 16px;
                line-height: 1.5;
            ">
                Merci pour votre réservation !<br>
                Un email de confirmation vous a été envoyé.
            </div>
            
            <!-- Détails de la réservation -->
            <div style="
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                padding: 20px;
                margin: 25px 0;
            ">
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                    <span style="font-weight: 600; color: #374151;">Service</span>
                    <span style="color: #111827;">${bookingData.service || '-'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                    <span style="font-weight: 600; color: #374151;">Praticienne</span>
                    <span style="color: #111827;">${bookingData.employee || '-'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                    <span style="font-weight: 600; color: #374151;">Date</span>
                    <span style="color: #111827;">${bookingData.date || '-'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                    <span style="font-weight: 600; color: #374151;">Créneau</span>
                    <span style="color: #111827;">${bookingData.slot || '-'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                    <span style="font-weight: 600; color: #374151;">Client</span>
                    <span style="color: #111827;">${bookingData.clientName || '-'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                    <span style="font-weight: 600; color: #374151;">Email</span>
                    <span style="color: #111827;">${bookingData.email || '-'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                    <span style="font-weight: 600; color: #374151;">Téléphone</span>
                    <span style="color: #111827;">${bookingData.phone || '-'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                    <span style="font-weight: 600; color: #374151;">Prix</span>
                    <span style="color: #111827; font-weight: 600;">${bookingData.price || '-'}</span>
                </div>
            </div>
            
            <!-- Footer -->
            <div style="
                text-align: center;
                color: #6b7280;
                font-size: 12px;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #e5e7eb;
            ">
                Ticket généré le ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}
            </div>
        </div>
    `;
}

// Fonction pour extraire les données de réservation depuis l'état global
function extractBookingDataFromState() {
    if (typeof bookingState !== 'undefined') {
        return {
            service: bookingState.selectedService?.name || '-',
            employee: bookingState.selectedEmployee?.name || '-',
            date: bookingState.selectedDate || '-',
            slot: bookingState.selectedSlot || '-',
            clientName: `${bookingState.client?.firstname || ''} ${bookingState.client?.lastname || ''}`.trim() || '-',
            email: bookingState.client?.email || '-',
            phone: bookingState.client?.phone || '-',
            price: bookingState.selectedService?.price ? `${bookingState.selectedService.price} DA` : '-'
        };
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
            price: getValue('.ticket-details div:nth-child(8) .ticket-value')
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
    const ticketElement = document.querySelector('.booking-ticket-modern');
    const buttonElement = document.getElementById('download-ticket-btn');
    const bookingData = extractBookingDataFromState();
    
    generateTicketPDFAlternative(ticketElement, buttonElement, bookingData);
};

console.log("🎫 PDF Ticket Fix chargé - Utilisez window.generateTicketPDFFixed()");
