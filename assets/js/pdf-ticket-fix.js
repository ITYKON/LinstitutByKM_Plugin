/**
 * Solution alternative pour la génération PDF des tickets de réservation
 * Corrige le problème de pages vides
 */

// Fallback pour la fonction de notification
if (typeof showBookingNotification === "undefined") {
  window.showBookingNotification = function (message) {
    console.log("[Notification]", message);
    // Essayer d'utiliser la fonction de notification du thème si elle existe
    if (typeof showNotification === "function") {
      showNotification(message);
    }
  };
}

// Fonction alternative pour générer le PDF avec une approche plus simple
function generateTicketPDFAlternative(
  ticketElement,
  buttonElement,
  bookingData
) {
  console.log("🎫 [Alternative] Début génération PDF...");
  console.log("🧾 [PDF] bookingData (resume):", {
    hasData: !!bookingData,
    date: bookingData && bookingData.date,
    price: bookingData && bookingData.price,
    client: bookingData && bookingData.client,
    servicesCount: bookingData && Array.isArray(bookingData.services) ? bookingData.services.length : 'n/a',
  });

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
    // Créer un wrapper pour garantir une hauteur de document > 0
    const wrapper = document.createElement("div");
    wrapper.id = "ib-pdf-wrapper";
    wrapper.style.cssText = `
            display: block;
            width: 100%;
            opacity: 0; /* invisible mais participe au layout */
            pointer-events: none;
        `;

    // Créer un conteneur temporaire avec le contenu du ticket (dans le flux normal)
    const tempContainer = document.createElement("div");
    tempContainer.id = "ib-pdf-temp";
    tempContainer.style.cssText = `
            /* pas de position fixe/absolute -> participe à la hauteur du document */
            width: 100%;
            background: #ffffff;
            padding: 30px;
            font-family: Arial, sans-serif;
            color: #000000;
            box-sizing: border-box;
            display: flex; /* center inner */
            justify-content: center;
            align-items: flex-start;
            min-height: 100px;
            margin: 0 auto;
        `;

    // Préférer capturer le DOM réel de la confirmation si présent
    let usingCaptureArea = false;
    const captureArea = document.getElementById('ticket-capture-area');
    if (captureArea) {
      try {
        usingCaptureArea = true;
        // Cloner uniquement le contenu (sans le bouton)
        const clone = captureArea.cloneNode(true);
        // Envelopper dans un conteneur pour largeur/fond
        const inner = document.createElement('div');
        inner.style.cssText = 'max-width: 820px; margin: 0 auto; background: #ffffff; padding: 12px 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; font-size:16px; line-height:1.65; color:#111827;';
        // Styles PDF (typo, espacements, bordures, sauts de page)
        const style = document.createElement('style');
        style.textContent = `
          /* Base */
          .pdf-wrapper { max-width:820px; margin:0 auto; }
          .pdf-ticket-header { page-break-inside: avoid; break-inside: avoid; }
          .pdf-ticket-title { font-size: 20px; font-weight: 700; color:#111827; letter-spacing:.2px; }
          .pdf-ticket-sub { font-size: 13px; color:#6b7280; margin-top: 6px; }

          /* Cards */
          .reservation-ticket-card { page-break-inside: avoid; break-inside: avoid; margin-bottom: 18px; border-radius: 12px; border: 1px solid #ececec; padding: 16px 18px; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
          .reservation-ticket-header { display:flex; align-items:center; gap:8px; font-weight:650; color:#111827; margin-bottom:8px; }
          .reservation-ticket-title { font-size: 16px; }
          .reservation-ticket-message { color:#6b7280; font-size:13px; margin: 2px 0 12px; line-height: 1.55; }
          .reservation-ticket-body { font-size: 15px; color:#374151; line-height: 1.65; }

          /* Rows as grid for perfect alignment */
          .reservation-ticket-row { display:grid; grid-template-columns: 180px 1fr; gap: 18px; padding: 12px 0; border-bottom: 1px solid #ececec; page-break-inside: avoid; break-inside: avoid; }
          .reservation-ticket-row:last-child { border-bottom: 0; }
          .reservation-ticket-row span:first-child { color:#6b7280; }
          .reservation-ticket-row span:last-child { text-align: right; color:#111827; }

          /* Price/Total emphasis */
          .reservation-ticket-price span:last-child { color:#a88962; font-weight:700; }
          #ticket-total, .ticket-total { font-size: 16px; font-weight:800; color:#a88962; margin-top: 18px; text-align: right; padding-top: 8px; border-top: 1px solid #ececec; }

          /* General */
          h1,h2,h3,h4 { color:#111827; }
          body { background: #ffffff !important; }
        `;
        inner.appendChild(style);
        // Header PDF
        const header = document.createElement('div');
        header.className = 'pdf-ticket-header';
        header.innerHTML = `
          <div style="text-align:center; padding: 8px 0 14px; border-bottom:1px solid #eee; margin-bottom: 16px;" class="pdf-wrapper">
            <div class="pdf-ticket-title">TICKET DE RÉSERVATION</div>
            <div class="pdf-ticket-sub">${new Date().toLocaleDateString('fr-FR', {weekday:'long', day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit'})}</div>
          </div>`;
        inner.appendChild(header);
        // Utiliser innerHTML pour garder le layout simple
        inner.innerHTML += clone.innerHTML;
        tempContainer.appendChild(inner);
        console.log('🧾 [PDF] Source: ticket-capture-area (DOM réel)');
      } catch (e) {
        console.warn('Capture area clone failed, fallback to generated HTML', e);
        const ticketHTML = createSimpleTicketHTML(bookingData);
        tempContainer.innerHTML = ticketHTML;
      }
    } else {
      // Créer le contenu HTML du ticket de manière simple (fallback)
      const ticketHTML = createSimpleTicketHTML(bookingData);
      // Centrer et styliser également le fallback
      const style = `
        <style>
          .reservation-ticket-card { page-break-inside: avoid; break-inside: avoid; margin-bottom: 16px; border-radius: 10px; border: 1px solid #eee; padding: 14px 16px; background: #fff; }
          .reservation-ticket-header { display:flex; align-items:center; gap:8px; font-weight:600; color:#111827; margin-bottom:8px; }
          .reservation-ticket-title { font-size: 15px; }
          .reservation-ticket-message { color:#6b7280; font-size:12.5px; margin: 2px 0 10px; }
          .reservation-ticket-body { font-size: 13px; color:#374151; }
          .reservation-ticket-row { display:flex; justify-content:space-between; padding: 10px 0; border-bottom: 1px dashed #e5e7eb; }
          .reservation-ticket-row:last-child { border-bottom: 0; }
          .reservation-ticket-row span:first-child { color:#6b7280; min-width: 110px; }
          .reservation-ticket-price span:last-child { color:#a88962; font-weight:700; }
          #ticket-total, .ticket-total { font-size: 15px; font-weight:700; color:#a88962; margin-top: 16px; text-align: right; }
          h1,h2,h3,h4 { color:#111827; }
        </style>
      `;
      tempContainer.innerHTML = `<div style="max-width:700px;margin:0 auto;padding:8px 0;background:#fff;">${style}${ticketHTML}</div>`;
      console.log('🧾 [PDF] Source: simple generated HTML');
    }

    // Logs d'environnement
    try {
      console.log("🧭 [PDF] UA:", navigator.userAgent);
      console.log("🧭 [PDF] viewport:", window.innerWidth, window.innerHeight, "dpr:", window.devicePixelRatio);
      console.log("🧭 [PDF] html2pdf present:", !!window.html2pdf, "html2canvas:", !!window.html2canvas, "jspdf:", !!(window.jspdf && window.jspdf.jsPDF));
      console.log("🧭 [PDF] ticketHTML length:", (ticketHTML || '').length);
    } catch(e) {}

    // Pour éviter les problèmes de rendu PDF, retirer les SVG inline
    try {
      const svgs = tempContainer.querySelectorAll('svg');
      svgs.forEach((svg) => svg.remove());
    } catch (e) {}

    // Ajouter au DOM via wrapper
    wrapper.appendChild(tempContainer);
    document.body.appendChild(wrapper);
    console.log("🧱 [PDF] tempContainer appended. childNodes:", tempContainer.childNodes && tempContainer.childNodes.length, "wrapper in DOM:", document.body.contains(wrapper));

    // Configuration PDF simplifiée (définie avant usage)
    const options = {
      margin: [8, 8, 12, 8],
      filename: `ticket-reservation-${
        bookingData.date || new Date().toISOString().split("T")[0]
      }.pdf`,
      image: {
        type: "jpeg",
        quality: 0.98,
      },
      html2canvas: {
        scale: 2,
        backgroundColor: "#ffffff",
        logging: true,
        useCORS: true,
        allowTaint: true,
        letterRendering: true,
        foreignObjectRendering: false, // avoid FO height=0 edge cases
        removeContainer: true,
        windowWidth: 794,
        scrollX: 0,
        scrollY: 0,
        onclone: (clonedDoc) => {
          try {
            const clonedWrapper = clonedDoc.getElementById('ib-pdf-wrapper');
            const clonedTemp = clonedDoc.getElementById('ib-pdf-temp');
            if (clonedWrapper) {
              clonedWrapper.style.opacity = '1';
              clonedWrapper.style.pointerEvents = 'auto';
              clonedWrapper.style.display = 'block';
              clonedWrapper.style.width = '100%';
            }
            if (clonedTemp) {
              clonedTemp.style.opacity = '1';
              clonedTemp.style.visibility = 'visible';
              clonedTemp.style.display = 'block';
              clonedTemp.style.position = 'static';
              clonedTemp.style.transform = 'none';
              clonedTemp.style.minHeight = '200px';
              clonedTemp.style.width = '794px';
              clonedTemp.style.margin = '0 auto';
              // Log in original window (may not appear from clone)
              if (typeof window !== 'undefined') {
                console.log('🧩 [onclone] Applied forced styles in clone');
              }
            }
            if (clonedDoc && clonedDoc.body) {
              clonedDoc.body.style.minHeight = '2000px';
              clonedDoc.documentElement && (clonedDoc.documentElement.style.minHeight = '2000px');
            }
          } catch (e) {
            if (typeof window !== 'undefined') console.warn('onclone error', e);
          }
        },
      },
      jsPDF: {
        unit: "pt",
        format: "a4",
        orientation: "portrait",
      },
    };
    // Laisser le layout s'appliquer puis mesurer/générer
    setTimeout(() => {
      // Forcer le rendu et log dimensions
      const _ = tempContainer.offsetHeight;
      const rect = tempContainer.getBoundingClientRect();
      const measuredHeight = rect.height || tempContainer.scrollHeight || tempContainer.offsetHeight;
      console.log("🎫 [Alternative] Dimensions conteneur:", rect.width, rect.height, "(measured:", measuredHeight, ")");
      try {
        const cs = window.getComputedStyle(tempContainer);
        console.log("🎯 [PDF] Styles:", {
          position: cs.position,
          display: cs.display,
          visibility: cs.visibility,
          opacity: cs.opacity,
          width: cs.width,
          height: cs.height,
        });
        console.log("📏 [PDF] offset/scroll:", tempContainer.offsetWidth, tempContainer.offsetHeight, tempContainer.scrollWidth, tempContainer.scrollHeight, "wrapperH:", wrapper.offsetHeight);
      } catch(e) { console.warn("[PDF] getComputedStyle error", e); }

      // Si la hauteur est 0, utiliser directement le fallback html2canvas -> jsPDF
      if (!measuredHeight || measuredHeight <= 0) {
        console.warn("🎫 [Alternative] Hauteur mesurée à 0 après délai, fallback html2canvas -> jsPDF");
        try {
          if (window.html2canvas && window.jspdf && window.jspdf.jsPDF) {
            window.html2canvas(tempContainer, options.html2canvas)
              .then((canvas) => {
                console.log("🖼️ [Fallback] Canvas size:", canvas.width, canvas.height);
                const imgData = canvas.toDataURL('image/jpeg', 0.98);
                const pdf = new window.jspdf.jsPDF({ unit: 'pt', format: 'a4', orientation: 'portrait' });
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const imgWidth = pageWidth - 20; // margins
                const imgHeight = imgWidth * ratio;
                console.log("🖨️ [Fallback] Add image dims:", {imgWidth, imgHeight, pageWidth, pageHeight});
                pdf.addImage(imgData, 'JPEG', 10, 10, imgWidth, Math.min(imgHeight, pageHeight - 20));
                pdf.save(`ticket-reservation-${(bookingData.date||new Date().toISOString().split('T')[0])}.pdf`);
                // Nettoyage UI
          if (document.body.contains(wrapper)) {
            document.body.removeChild(wrapper);
          }
          if (buttonElement) buttonElement.style.display = 'block';
          showBookingNotification('Ticket téléchargé avec succès !');
              })
              .catch((e2) => {
                console.warn('Fallback html2canvas->jsPDF échoué:', e2);
                if (document.body.contains(wrapper)) {
                  document.body.removeChild(wrapper);
                  // Switch temp container from position: fixed to normal flow (static/block)
                  tempContainer.style.position = 'static';
                  tempContainer.style.opacity = '0';
                  tempContainer.style.minHeight = '100px';
                  tempContainer.style.clear = 'both';
                }
                if (buttonElement) buttonElement.style.display = 'block';
                showBookingNotification('Erreur lors de la génération du PDF');
              });
            return; // stop primary path
          }
        } catch (e) {
          console.warn('Erreur fallback direct:', e);
        }
      }

      // Forcer les dimensions pour html2canvas
      try {
        options.html2canvas = options.html2canvas || {};
        options.html2canvas.width = Math.max(rect.width || 0, tempContainer.scrollWidth || 0, tempContainer.offsetWidth || 0) || 794;
        options.html2canvas.height = Math.max(measuredHeight || 0, tempContainer.scrollHeight || 0, tempContainer.offsetHeight || 0) || 1123;
        options.html2canvas.windowHeight = (options.html2canvas.height || measuredHeight || 0) + 100;
      } catch(e) { console.warn('[PDF] set width/height failed', e); }

      console.log("🎫 [Alternative] Configuration:", options);
      console.log("🧮 [PDF] html2canvas dims:", {
        width: options.html2canvas.width,
        height: options.html2canvas.height,
        windowHeight: options.html2canvas.windowHeight,
      });
      console.log(
        "🎫 [Alternative] Contenu:",
        tempContainer.innerHTML.substring(0, 200)
      );
      console.log("🧪 [PDF] Using main html2pdf path");

      // Générer le PDF (chemin principal)
      html2pdf()
        .set(options)
        .from(tempContainer)
        .toPdf()
        .get('pdf')
        .then((pdf) => {
          // Vérifier qu'au moins une page existe
          try {
            const pages = pdf.getNumberOfPages && pdf.getNumberOfPages();
            console.log("🎫 [Alternative] Pages PDF:", pages);
            // Ajouter un pied de page avec pagination et nom du salon
            if (typeof pages === 'number' && pages > 0) {
              for (let i = 1; i <= pages; i++) {
                pdf.setPage(i);
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                pdf.setFontSize(9);
                pdf.setTextColor(120);
                const footerLeft = "L'INSTITUT BY KM";
                const footerRight = `Page ${i} / ${pages}`;
                pdf.text(footerLeft, 24, pageHeight - 16);
                const rightX = pageWidth - 24;
                pdf.text(footerRight, rightX, pageHeight - 16, { align: 'right' });
              }
            }
          } catch (e) {}
        })
        .save()
        .then(() => {
          console.log("🎫 [Alternative] PDF généré avec succès");
          // Nettoyer
          if (document.body.contains(wrapper)) {
            document.body.removeChild(wrapper);
          }
          if (buttonElement) {
            buttonElement.style.display = "block";
          }
          showBookingNotification("Ticket téléchargé avec succès !");
        })
        .catch((error) => {
          console.error("❌ [Alternative] Erreur PDF:", error);
          // Fallback 1: html2canvas + jsPDF image (fiable)
          try {
            if (window.html2canvas && window.jspdf && window.jspdf.jsPDF) {
              window.html2canvas(tempContainer, options.html2canvas)
                .then((canvas) => {
                  console.log("🖼️ [Catch Fallback] Canvas size:", canvas.width, canvas.height);
                  const imgData = canvas.toDataURL('image/jpeg', 0.98);
                  const pdf = new window.jspdf.jsPDF({ unit: 'pt', format: 'a4', orientation: 'portrait' });
                  const pageWidth = pdf.internal.pageSize.getWidth();
                  const pageHeight = pdf.internal.pageSize.getHeight();
                  const imgWidth = pageWidth - 20; // margins
                  const ratio = canvas.height / canvas.width;
                  const imgHeight = imgWidth * ratio;
                  console.log("🖨️ [Catch Fallback] Add image dims:", {imgWidth, imgHeight, pageWidth, pageHeight});
                  pdf.addImage(imgData, 'JPEG', 10, 10, imgWidth, Math.min(imgHeight, pageHeight - 20));
                  pdf.save(`ticket-reservation-${(bookingData.date||new Date().toISOString().split('T')[0])}.pdf`);
                  // Nettoyage UI
                  if (document.body.contains(wrapper)) {
                    document.body.removeChild(wrapper);
                  }
                  if (buttonElement) buttonElement.style.display = 'block';
                  showBookingNotification('Ticket téléchargé avec succès !');
                  return;
                })
                .catch((e2) => {
                  console.warn('Fallback html2canvas->jsPDF échoué:', e2);
                });
            }
          } catch (e) {
            console.warn('Erreur fallback html2canvas->jsPDF:', e);
          }

          // Fallback 2: jsPDF texte minimal si tout échoue
          try {
            if (window.jspdf && window.jspdf.jsPDF) {
              const doc = new window.jspdf.jsPDF({ unit: 'pt', format: 'a4', orientation: 'portrait' });
              doc.setFontSize(14);
              doc.text('Ticket de réservation', 40, 60);
              const b = bookingData || {};
              const c = (b.client || {});
              const lines = [
                `Client: ${(c.prenom||'')} ${(c.nom||'')}`,
                `Email: ${(c.email||'')}`,
                `Téléphone: ${(c.telephone||'')}`,
              ];
              let y = 90;
              lines.forEach((l)=>{ doc.text(l, 40, y); y += 20; });
              doc.save(`ticket-reservation-${(b.date||new Date().toISOString().split('T')[0])}.pdf`);
              showBookingNotification('Ticket téléchargé avec succès (fallback) !');
            }
          } catch (e) {
            console.warn('Fallback jsPDF échoué:', e);
          }
          // Nettoyer
          if (document.body.contains(wrapper)) {
            document.body.removeChild(wrapper);
          }
          if (buttonElement) {
            buttonElement.style.display = "block";
          }
          showBookingNotification(
            "Erreur lors de la génération du PDF: " + error.message
          );
        });
    }, 50);
  } catch (error) {
    console.error("❌ [Alternative] Erreur générale:", error);
    if (buttonElement) {
      buttonElement.style.display = "block";
    }
    showBookingNotification(
      "Erreur lors de la génération du PDF: " + error.message
    );
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
    weekday: "long",
    day: "2-digit",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  };
  const formattedDate = date.toLocaleDateString("fr-FR", options);
  const currentDate = new Date();
  const formattedCurrentDate = currentDate.toLocaleDateString("fr-FR");
  const formattedCurrentTime = currentDate.toLocaleTimeString("fr-FR", {
    hour: "2-digit",
    minute: "2-digit",
  });

  // Créer le contenu HTML de base
  let html = [];

  // Ajouter le conteneur principal
  html.push(
    '<div style="font-family: Arial, sans-serif; max-width: 100%; padding: 15px; box-sizing: border-box;">'
  );

  // En-tête
  html.push(
    '  <div style="text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #eee;">'
  );
  html.push(
    '    <h1 style="color: #2c3e50; margin: 0 0 5px 0; font-size: 20px; font-weight: 600;">TICKET DE RÉSERVATION</h1>'
  );
  html.push(
    '    <p style="color: #7f8c8d; margin: 0; font-size: 14px;">' +
      formattedDate +
      "</p>"
  );
  html.push("  </div>");

  // Section client
  html.push('  <div style="margin-bottom: 25px;">');
  html.push(
    '    <div style="font-weight: 600; margin-bottom: 5px; color: #2c3e50;">'
  );
  html.push("      " + (client.prenom || "") + " " + (client.nom || ""));
  html.push("    </div>");
  html.push('    <div style="color: #7f8c8d; font-size: 14px;">');
  html.push("      " + (client.email || "") + "<br>");
  html.push("      " + (client.telephone || ""));
  html.push("    </div>");
  html.push("  </div>");

  // Section services
  html.push('  <div style="margin-bottom: 20px;">');
  html.push(
    '    <div style="font-weight: 600; margin-bottom: 10px; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 5px;">'
  );
  html.push("      Détails de la réservation");
  html.push("    </div>");

  // Ajouter chaque service
  services.forEach(function (service) {
    const startTime = service.startTime
      ? new Date(service.startTime)
      : new Date();
    const serviceTime = startTime.toLocaleTimeString("fr-FR", {
      hour: "2-digit",
      minute: "2-digit",
    });

    // Utiliser la couleur de l'employé si disponible
    const employeeColor = service.employeeColor || "#4f8cff";

    // Ajouter le service
    html.push(
      '    <div style="margin-bottom: 10px; padding: 12px; background: #fff; border-radius: 6px; border-left: 4px solid ' +
        employeeColor +
        '; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">'
    );
    html.push(
      '      <div style="display: flex; justify-content: space-between; align-items: flex-start;">'
    );
    html.push("        <div>");
    html.push(
      '          <div style="font-weight: 600; color: #2c3e50; margin-bottom: 3px;">' +
        (service.nom || "Service") +
        "</div>"
    );
    html.push(
      '          <div style="font-size: 13px; color: #64748b;">' +
        serviceTime +
        " • " +
        (service.employe || "Sans praticien") +
        "</div>"
    );
    html.push("        </div>");
    html.push(
      '        <div style="font-weight: 700; color: #2c3e50;">' +
        (service.prix ? parseFloat(service.prix).toFixed(2) + " €" : "-") +
        "</div>"
    );
    html.push("      </div>");
    html.push("    </div>");
  });

  // Fermer la section services
  html.push("  </div>");

  // Section informations pratiques
  html.push(
    '  <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 20px; margin-bottom: 20px;">'
  );
  html.push(
    '    <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #166534; display: flex; align-items: center; gap: 8px;">'
  );
  html.push(
    '      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">'
  );
  html.push('        <circle cx="12" cy="12" r="10"></circle>');
  html.push('        <line x1="12" y1="16" x2="12" y2="12"></line>');
  html.push('        <line x1="12" y1="8" x2="12.01" y2="8"></line>');
  html.push("      </svg>");
  html.push("      Informations importantes");
  html.push("    </h3>");
  html.push(
    '    <ul style="margin: 0; padding-left: 20px; color: #166534; font-size: 14px; line-height: 1.6;">'
  );
  html.push("      <li>Présentez ce ticket à votre arrivée</li>");
  html.push("      <li>Merci d'arriver 5 minutes avant l'heure prévue</li>");
  html.push(
    "      <li>En cas d'empêchement, merci de nous prévenir au moins 24h à l'avance</li>"
  );
  html.push("    </ul>");
  html.push("  </div>");

  // Pied de page
  html.push(
    '  <div style="text-align: center; padding-top: 20px; border-top: 1px solid #e2e8f0; margin-top: 20px; color: #64748b; font-size: 13px; line-height: 1.5;">'
  );
  html.push('    <p style="margin: 0 0 10px 0;">');
  html.push("      <strong>L'INSTITUT BY KM</strong><br>");
  html.push("      20 Rue des frères Mellali<br>");
  html.push("      06000 Béjaïa, Algérie");
  html.push("    </p>");
  html.push('    <p style="margin: 0; font-size: 12px;">');
  html.push("      Tél: 0770 30 73 85 | Email: contact@linstitutbykm.dz");
  html.push("    </p>");
  html.push(
    '    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #64748b;">'
  );
  html.push(
    '      <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">'
  );
  html.push("        <span>Total:</span>");
  html.push(
    '        <span style="font-weight: 700;">' +
      (booking.price || "-") +
      "</span>"
  );
  html.push("      </div>");
  html.push('      <div style="font-size: 11px; margin-top: 10px;">');
  html.push(
    "        Ticket généré le " +
      formattedCurrentDate +
      " à " +
      formattedCurrentTime
  );
  html.push("      </div>");
  html.push("    </div>");
  html.push("  </div>");

  // Fermer le conteneur principal
  html.push("</div>");

  // Retourner le HTML généré
  return html.join("\n");
}

// Fonction pour extraire les données de réservation depuis l'état global
function extractBookingDataFromState() {
  // Vérifier si bookingState est défini (approche plus robuste)
  if (typeof bookingState !== "undefined" && bookingState !== null) {
    try {
      const cart = Array.isArray(bookingState.cart) ? bookingState.cart : [];

      // Convertir HH:MM en Date si possible
      const buildStartTime = (dateStr, slotStr) => {
        try {
          if (!dateStr || !slotStr) return null;
          const [h, m] = String(slotStr)
            .split(":")
            .map((x) => parseInt(x, 10));
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
          nom: (item.service && item.service.name) || "Service",
          employe: (item.employee && item.employee.name) || "Sans praticien",
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
        : "-";

      // Client
      const client = {
        prenom: (bookingState.client && bookingState.client.firstname) || "",
        nom: (bookingState.client && bookingState.client.lastname) || "",
        email: (bookingState.client && bookingState.client.email) || "",
        telephone: (bookingState.client && bookingState.client.phone) || "",
      };

      return {
        service:
          (bookingState.selectedService && bookingState.selectedService.name) ||
          "-",
        employee:
          (bookingState.selectedEmployee &&
            bookingState.selectedEmployee.name) ||
          "-",
        date: bookingState.selectedDate || "-",
        slot: bookingState.selectedSlot || "-",
        clientName: `${client.prenom} ${client.nom}`.trim() || "-",
        email: client.email || "-",
        phone: client.telephone || "-",
        price: priceText,
        services,
        client,
      };
    } catch (err) {
      console.warn("extractBookingDataFromState fallback (error):", err);
    }
  }

  // Fallback: extraire depuis le DOM
  const ticket = document.querySelector(".booking-ticket-modern");
  if (ticket) {
    const getValue = (selector) => {
      const element = ticket.querySelector(selector);
      return element ? element.textContent.trim() : "-";
    };

    return {
      service: getValue(".ticket-details div:nth-child(1) .ticket-value"),
      employee: getValue(".ticket-details div:nth-child(2) .ticket-value"),
      date: getValue(".ticket-details div:nth-child(3) .ticket-value"),
      slot: getValue(".ticket-details div:nth-child(4) .ticket-value"),
      clientName: getValue(".ticket-details div:nth-child(5) .ticket-value"),
      email: getValue(".ticket-details div:nth-child(6) .ticket-value"),
      phone: getValue(".ticket-details div:nth-child(7) .ticket-value"),
      price: getValue(".ticket-details div:nth-child(8) .ticket-value"),
      // Ajouter un tableau services vide pour la compatibilité avec createSimpleTicketHTML
      services: [],
    };
  }

  return {
    service: "-",
    employee: "-",
    date: "-",
    slot: "-",
    clientName: "-",
    email: "-",
    phone: "-",
    price: "-",
  };
}

// Fonction publique pour remplacer la génération PDF existante
window.generateTicketPDFFixed = function (bookingDataOverride) {
  const buttonElement = document.getElementById("download-ticket-btn");
  // Utiliser les données passées si disponibles, sinon extraire
  const bookingData = bookingDataOverride || extractBookingDataFromState();
  if (buttonElement) {
    buttonElement.disabled = true;
    buttonElement.textContent = "Génération en cours...";
  }
  setTimeout(function () {
    try {
      generateTicketPDFAlternative(null, buttonElement, bookingData);
      if (buttonElement) {
        buttonElement.textContent = "Télécharger le ticket";
        buttonElement.disabled = false;
      }
    } catch (error) {
      console.error("❌ Erreur lors de la génération du PDF:", error);
      showBookingNotification("Erreur lors de la génération du PDF");
      if (buttonElement) {
        buttonElement.textContent = "Télécharger le ticket";
        buttonElement.disabled = false;
      }
    }
  }, 100);
};

// Initialisation
if (typeof document !== "undefined") {
  console.log(
    "🎫 PDF Ticket Fix chargé - Utilisez window.generateTicketPDFFixed()"
  );
}
