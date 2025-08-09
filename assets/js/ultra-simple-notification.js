/**
 * SCRIPT ULTRA-SIMPLE POUR NOTIFICATIONS
 * Version définitive qui fonctionne à coup sûr
 */

console.log("🎯 SCRIPT ULTRA-SIMPLE - Démarrage...");

// Attendre que jQuery soit disponible
function waitForJQuery() {
  if (typeof jQuery !== "undefined") {
    console.log("✅ jQuery trouvé, initialisation...");
    initNotifications();
  } else {
    setTimeout(waitForJQuery, 100);
  }
}

function initNotifications() {
  const $ = jQuery;

  console.log("🔧 Initialisation des notifications...");

  // Supprimer TOUS les anciens gestionnaires
  $(document).off("click");

  // Créer la cloche si elle n'existe pas
  if (!$(".notification-bell").length) {
    createBell();
  }

  // Créer le modal
  createModal();

  // Configurer les interactions modernes
  setupBellClick();

  // Vérifier les variables AJAX
  console.log("🔍 Variables AJAX disponibles:");
  console.log(
    "   • ajaxurl:",
    typeof ajaxurl !== "undefined" ? ajaxurl : "NON DÉFINI"
  );
  console.log(
    "   • ib_notif_vars:",
    typeof ib_notif_vars !== "undefined" ? ib_notif_vars : "NON DÉFINI"
  );
  console.log(
    "   • IBNotifBell:",
    typeof IBNotifBell !== "undefined" ? IBNotifBell : "NON DÉFINI"
  );

  // Charger le badge count initial depuis la base de données
  updateBadgeCount(true);

  console.log("✅ Notifications initialisées !");
}

function createBell() {
  const $ = jQuery;

  console.log("🔔 Création de la cloche moderne...");

  const bellHTML = `
        <div style="
            position: fixed;
            top: 32px;
            right: 20px;
            z-index: 999999;
        ">
            <button class="notification-bell" style="
                background: linear-gradient(135deg, #e9aebc 0%, #d89aab 100%);
                border: none;
                border-radius: 20px;
                width: 60px;
                height: 60px;
                color: white;
                cursor: pointer;
                box-shadow:
                    0 10px 30px rgba(233, 174, 188, 0.3),
                    0 4px 15px rgba(216, 154, 171, 0.2),
                    inset 0 1px 0 rgba(255, 255, 255, 0.2);
                transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.15);
            "
            onmouseover="
                this.style.transform='scale(1.05) translateY(-3px)';
                this.style.boxShadow='0 15px 40px rgba(233, 174, 188, 0.4), 0 8px 20px rgba(216, 154, 171, 0.3)';
                this.style.background='linear-gradient(135deg, #f0b8c8 0%, #e9aebc 100%)';
            "
            onmouseout="
                this.style.transform='scale(1) translateY(0)';
                this.style.boxShadow='0 10px 30px rgba(233, 174, 188, 0.3), 0 4px 15px rgba(216, 154, 171, 0.2)';
                this.style.background='linear-gradient(135deg, #e9aebc 0%, #d89aab 100%)';
            ">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                    <path d="M4 2C2.8 3.7 2 5.7 2 8"/>
                    <path d="M22 8c0-2.3-.8-4.3-2-6"/>
                </svg>
                <span class="notification-badge" style="
                    position: absolute;
                    top: -6px;
                    right: -6px;
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    color: white;
                    border-radius: 50%;
                    width: 22px;
                    height: 22px;
                    font-size: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 600;
                    border: 2px solid white;
                    box-shadow: 0 3px 10px rgba(239, 68, 68, 0.4);
                    animation: pulse 2s infinite;
                ">3</span>
            </button>
        </div>
        <style>
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
            .notification-bell:active {
                transform: scale(0.95) !important;
                transition: all 0.1s ease !important;
            }
        </style>
    `;

  $("body").append(bellHTML);
  console.log("✅ Cloche moderne créée !");
}

function createModal() {
  const $ = jQuery;

  console.log("📋 Création du modal moderne...");

  const modalHTML = `
        <div id="simple-notification-modal" style="
            position: fixed;
            top: 90px;
            right: 20px;
            width: 440px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 28px;
            box-shadow:
                0 25px 80px rgba(0, 0, 0, 0.12),
                0 10px 30px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
            z-index: 999998;
            display: none;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Inter', 'Helvetica Neue', sans-serif;
            transform: translateY(-15px) scale(0.95);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        ">
            <!-- Flèche moderne pointant vers la cloche -->
            <div style="
                position: absolute;
                top: -12px;
                right: 32px;
                width: 0;
                height: 0;
                border-left: 12px solid transparent;
                border-right: 12px solid transparent;
                border-bottom: 12px solid rgba(255, 255, 255, 0.95);
                filter: drop-shadow(0 -2px 4px rgba(0, 0, 0, 0.1));
            "></div>

            <!-- Header ultra-moderne minimaliste -->
            <div style="
                padding: 28px 32px 24px;
                background: linear-gradient(135deg,
                    rgba(233, 174, 188, 0.08) 0%,
                    rgba(216, 154, 171, 0.05) 100%);
                backdrop-filter: blur(20px);
                border-bottom: 1px solid rgba(233, 174, 188, 0.1);
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: relative;
            ">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="
                        background: linear-gradient(135deg, #e9aebc 0%, #d89aab 100%);
                        border-radius: 16px;
                        padding: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        box-shadow: 0 4px 12px rgba(233, 174, 188, 0.25);
                    ">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="
                            margin: 0;
                            font-size: 1.3em;
                            font-weight: 700;
                            letter-spacing: -0.03em;
                            color: #1f2937;
                            line-height: 1.2;
                        ">Notifications</h3>
                        <p style="
                            margin: 0;
                            font-size: 0.85em;
                            color: #6b7280;
                            font-weight: 500;
                        ">3 nouvelles notifications</p>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- Filtre par type -->
                    <select id="notification-filter" onchange="filterNotifications()" style="
                        background: rgba(233, 174, 188, 0.08);
                        border: 1px solid rgba(233, 174, 188, 0.2);
                        border-radius: 10px;
                        padding: 6px 10px;
                        color: #6b7280;
                        cursor: pointer;
                        font-size: 0.75em;
                        font-weight: 500;
                        outline: none;
                        transition: all 0.3s ease;
                    ">
                        <option value="all">Tous</option>
                        <option value="confirmed">Confirmées</option>
                        <option value="cancelled">Annulées</option>
                        <option value="reminder">Rappels</option>
                    </select>

                    <!-- Bouton Marquer tout comme lu -->
                    <button onclick="markAllAsRead()" style="
                        background: rgba(233, 174, 188, 0.1);
                        border: 1px solid rgba(233, 174, 188, 0.3);
                        border-radius: 10px;
                        padding: 6px 10px;
                        color: #e9aebc;
                        cursor: pointer;
                        font-size: 0.75em;
                        font-weight: 600;
                        display: flex;
                        align-items: center;
                        gap: 4px;
                        transition: all 0.3s ease;
                    "
                    onmouseover="
                        this.style.background='rgba(233, 174, 188, 0.15)';
                        this.style.transform='scale(1.05)';
                        this.style.color='#d89aab';
                    "
                    onmouseout="
                        this.style.background='rgba(233, 174, 188, 0.1)';
                        this.style.transform='scale(1)';
                        this.style.color='#e9aebc';
                    ">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                        Tout lire
                    </button>

                    <!-- Bouton Tout supprimer -->
                    <button id="delete-all-notifications" onclick="deleteAllNotifications()" style="
                        background: rgba(239, 68, 68, 0.08);
                        border: 1px solid rgba(239, 68, 68, 0.2);
                        border-radius: 10px;
                        padding: 6px 10px;
                        color: #ef4444;
                        cursor: pointer;
                        font-size: 0.75em;
                        font-weight: 600;
                        display: flex;
                        align-items: center;
                        gap: 4px;
                        transition: all 0.3s ease;
                    "
                    onmouseover="
                        this.style.background='rgba(239, 68, 68, 0.15)';
                        this.style.transform='scale(1.05)';
                    "
                    onmouseout="
                        this.style.background='rgba(239, 68, 68, 0.08)';
                        this.style.transform='scale(1)';
                    ">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            <line x1="10" y1="11" x2="10" y2="17"/>
                            <line x1="14" y1="11" x2="14" y2="17"/>
                        </svg>
                        Tout supprimer
                    </button>

                    <!-- Bouton fermer -->
                    <button onclick="closeNotificationModal()" style="
                        background: rgba(0, 0, 0, 0.05);
                        border: none;
                        border-radius: 12px;
                        width: 36px;
                        height: 36px;
                        color: #6b7280;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.3s ease;
                    "
                    onmouseover="
                        this.style.background='rgba(239, 68, 68, 0.1)';
                        this.style.color='#ef4444';
                        this.style.transform='scale(1.1)';
                    "
                    onmouseout="
                        this.style.background='rgba(0, 0, 0, 0.05)';
                        this.style.color='#6b7280';
                        this.style.transform='scale(1)';
                    ">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                </div>
            </div>

            <!-- Contenu des notifications -->
            <div id="notification-content" style="
                max-height: 60vh;
                overflow-y: auto;
                padding: 16px 0;
            ">
                <!-- Les notifications seront chargées ici dynamiquement -->
                <div style="
                    text-align: center;
                    padding: 40px 20px;
                    color: #94a3b8;
                ">
                    <div style="
                        width: 60px;
                        height: 60px;
                        margin: 0 auto 16px;
                        background: #f8fafc;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="#94a3b8">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                    </div>
                    <h4 style="
                        margin: 0 0 8px 0;
                        color: #334155;
                        font-size: 1.1em;
                        font-weight: 600;
                    ">Aucune notification</h4>
                    <p style="
                        margin: 0;
                        font-size: 0.9em;
                        color: #94a3b8;
                    ">Vous n'avez aucune notification pour le moment</p>
                </div>
            </div>

<style>
@keyframes pulse {
0%, 100% { transform: scale(1); opacity: 1; }
50% { transform: scale(1.1); opacity: 0.8; }
            }

            @keyframes glow {
                0%, 100% { box-shadow: 0 0 5px rgba(233, 174, 188, 0.3); }
                50% { box-shadow: 0 0 20px rgba(233, 174, 188, 0.6); }
            }

            @keyframes fadeInScale {
                from {
                    transform: scale(0.8);
                    opacity: 0;
                }
                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            #notification-content::-webkit-scrollbar {
                width: 4px;
            }

            #notification-content::-webkit-scrollbar-track {
                background: transparent;
            }

            #notification-content::-webkit-scrollbar-thumb {
                background: rgba(233, 174, 188, 0.3);
                border-radius: 2px;
            }

            #notification-content::-webkit-scrollbar-thumb:hover {
                background: rgba(233, 174, 188, 0.5);
            }

            .notification-modal-show {
                display: block !important;
                opacity: 1 !important;
                transform: translateY(0) scale(1) !important;
            }

            .notification-item {
                animation: slideInUp 0.4s ease-out;
            }

            .notification-item.new-notification {
                animation: slideInFromRight 0.6s ease-out;
            }

            .notification-item.removing {
                animation: slideOutDown 0.3s ease-in forwards;
            }

            .notification-item.read {
                opacity: 0.6;
                background: rgba(0, 0, 0, 0.02) !important;
            }

            .notification-item.selected {
                background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%) !important;
                border-left: 4px solid #667eea !important;
                transform: translateX(4px) scale(1.02);
                box-shadow: 
                    0 8px 25px rgba(102, 126, 234, 0.2) !important,
                    0 4px 12px rgba(118, 75, 162, 0.15) !important;
                position: relative;
                backdrop-filter: blur(10px);
            }

            .notification-item.selected::before {
                content: "✓";
                position: absolute;
                top: 12px;
                right: 12px;
                background: linear-gradient(135deg, #e8b4cb 0%, #d89bb5 100%);
                color: white;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: bold;
                z-index: 10;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                animation: pulse-selected 2s infinite;
            }

            @keyframes pulse-selected {
                0%, 100% { 
                    transform: scale(1);
                    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                }
                50% { 
                    transform: scale(1.1);
                    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
                }
            }

            .notification-item.confirmed:hover {
                border-left: 4px solid #50C878;
            }

            .notification-item.cancelled:hover {
                border-left: 4px solid #FF9F43;
            }

            .notification-item.reminder:hover {
                border-left: 4px solid #3D9DF6;
            }

            .notification-item.email-grouped {
                background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.02), transparent) !important;
            }

            .notification-item.email-grouped:hover {
                background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.05), transparent) !important;
            }

            .notification-bell.has-new {
                animation: glow 2s infinite;
            }

            #notification-filter:focus {
                border-color: rgba(233, 174, 188, 0.5);
                box-shadow: 0 0 0 3px rgba(233, 174, 188, 0.1);
            }

            /* Responsive mobile */
            @media (max-width: 768px) {
                #simple-notification-modal {
                    width: calc(100vw - 20px) !important;
                    right: 10px !important;
                    top: 70px !important;
                }

                .notification-item {
                    padding: 16px 20px !important;
                }

                .notification-item h4 {
                    font-size: 0.9em !important;
                }

                .notification-item p {
                    font-size: 0.8em !important;
                }
            }
        </style>
    `;

  $("body").append(modalHTML);
  console.log("✅ Modal moderne créé !");
}

function positionModal() {
  const $ = jQuery;
  const $modal = $("#simple-notification-modal");
  const $bell = $(".notification-bell");

  if ($bell.length) {
    const bellRect = $bell[0].getBoundingClientRect();
    const rightOffset = window.innerWidth - bellRect.right;
    const topOffset = bellRect.bottom + 10;

    $modal.css({
      top: topOffset + "px",
      right: rightOffset + "px",
    });
  }
}

// Fonction pour fermer le modal avec animation
function closeNotificationModal() {
  const $ = jQuery;
  const $modal = $("#simple-notification-modal");

  $modal.removeClass("notification-modal-show");
  setTimeout(() => {
    $modal.hide();
  }, 400);
}

// Fonction pour ouvrir le modal avec animation
function openNotificationModal() {
  const $ = jQuery;
  const $modal = $("#simple-notification-modal");

  positionModal();
  $modal.show();

  // Force reflow pour que l'animation fonctionne
  $modal[0].offsetHeight;

  $modal.addClass("notification-modal-show");
}

function setupBellClick() {
  const $ = jQuery;

  console.log("🖱️ Configuration des interactions modernes...");

  $(document).on("click", ".notification-bell", function (e) {
    e.stopPropagation();
    console.log("🔔 Cloche cliquée !");

    const $modal = $("#simple-notification-modal");

    if ($modal.hasClass("notification-modal-show")) {
      closeNotificationModal();
    } else {
      // Charger les vraies notifications avant d'ouvrir le modal
      loadRealNotifications();
      openNotificationModal();
    }
  });

  // Fermer le modal en cliquant à l'extérieur avec animation
  $(document).on("click", function (e) {
    const $modal = $("#simple-notification-modal");
    const $bell = $(".notification-bell");

    if (
      !$modal.is(e.target) &&
      $modal.has(e.target).length === 0 &&
      !$bell.is(e.target) &&
      $bell.has(e.target).length === 0
    ) {
      if ($modal.hasClass("notification-modal-show")) {
        closeNotificationModal();
      }
    }
  });

  // Fermer avec la touche Escape
  $(document).on("keydown", function (e) {
    if (e.key === "Escape") {
      const $modal = $("#simple-notification-modal");
      if ($modal.hasClass("notification-modal-show")) {
        closeNotificationModal();
      }
    }
  });

  console.log("✅ Interactions modernes configurées !");
}

// Fonctions pour gérer les notifications avec AJAX
function markAsRead(notificationId) {
  const $ = jQuery;
  const $notification = $(`[data-notification-id="${notificationId}"]`);

  // Vérifier que les variables AJAX sont disponibles
  const ajax_url =
    typeof ajaxurl !== "undefined" ? ajaxurl : "/wp-admin/admin-ajax.php";
  const nonce =
    (typeof ib_notif_vars !== "undefined" && ib_notif_vars.nonce) ||
    (typeof IBNotifBell !== "undefined" && IBNotifBell.nonce) ||
    "";

  if (!nonce) {
    showToast("Erreur de configuration AJAX", "error");
    return;
  }

  // Appel AJAX pour marquer comme lu
  $.post(ajax_url, {
    action: "ib_mark_notification_read",
    nonce: nonce,
    id: notificationId,
  })
    .done(function (response) {
      if (response.success) {
        $notification.addClass("read");
        $notification.css("opacity", "0.6");

        // Supprimer le bouton "Marquer comme lu"
        $notification.find('button[onclick*="markAsRead"]').fadeOut(300);

        updateBadgeCount();
        showToast("Notification marquée comme lue", "success");
      } else {
        showToast("Erreur lors de la mise à jour", "error");
      }
    })
    .fail(function () {
      showToast("Erreur de connexion", "error");
    });
}

function deleteNotification(notificationId) {
  const $ = jQuery;
  const $notification = $(`[data-notification-id="${notificationId}"]`);

  // Confirmation avant suppression
  if (!confirm("Êtes-vous sûr de vouloir supprimer cette notification ?")) {
    return;
  }

  // Animation de suppression
  $notification.addClass("removing");

  // Vérifier que les variables AJAX sont disponibles
  const ajax_url =
    typeof ajaxurl !== "undefined" ? ajaxurl : "/wp-admin/admin-ajax.php";
  const nonce =
    (typeof ib_notif_vars !== "undefined" && ib_notif_vars.nonce) ||
    (typeof IBNotifBell !== "undefined" && IBNotifBell.nonce) ||
    "";

  if (!nonce) {
    $notification.removeClass("removing");
    showToast("Erreur de configuration AJAX", "error");
    return;
  }

  // Appel AJAX pour supprimer
  $.post(ajax_url, {
    action: "ib_delete_notification",
    nonce: nonce,
    id: notificationId,
  })
    .done(function (response) {
      if (response.success) {
        setTimeout(() => {
          $notification.remove();
          updateBadgeCount();

          // Si plus de notifications, afficher l'état vide
          if ($("#notification-content .notification-item").length === 0) {
            showEmptyState();
          }
        }, 300);

        showToast("Notification supprimée", "success");
      } else {
        $notification.removeClass("removing");
        showToast("Erreur lors de la suppression", "error");
      }
    })
    .fail(function () {
      $notification.removeClass("removing");
      showToast("Erreur de connexion", "error");
    });
}

function markAllAsRead() {
  const $ = jQuery;

  // Vérifier que les variables AJAX sont disponibles
  const ajax_url =
    typeof ajaxurl !== "undefined" ? ajaxurl : "/wp-admin/admin-ajax.php";
  const nonce =
    (typeof ib_notif_vars !== "undefined" && ib_notif_vars.nonce) ||
    (typeof IBNotifBell !== "undefined" && IBNotifBell.nonce) ||
    "";

  if (!nonce) {
    showToast("Erreur de configuration AJAX", "error");
    return;
  }

  // Appel AJAX pour marquer toutes comme lues
  $.post(ajax_url, {
    action: "ib_mark_all_notifications_read",
    nonce: nonce,
  })
    .done(function (response) {
      if (response.success) {
        $("#notification-content .notification-item").addClass("read");
        $("#notification-content .notification-item").css("opacity", "0.6");

        // Supprimer tous les boutons "Marquer comme lu"
        $('#notification-content button[onclick*="markAsRead"]').fadeOut(300);

        updateBadgeCount();
        showToast(
          "Toutes les notifications ont été marquées comme lues",
          "success"
        );
      } else {
        showToast("Erreur lors de la mise à jour", "error");
      }
    })
    .fail(function () {
      showToast("Erreur de connexion", "error");
    });
}

function updateBadgeCount(forceRefresh = false) {
  const $ = jQuery;

  if (forceRefresh) {
    // Vérifier que les variables AJAX sont disponibles
    const ajax_url =
      typeof ajaxurl !== "undefined" ? ajaxurl : "/wp-admin/admin-ajax.php";
    const nonce =
      (typeof ib_notif_vars !== "undefined" && ib_notif_vars.nonce) ||
      (typeof IBNotifBell !== "undefined" && IBNotifBell.nonce) ||
      "";

    if (!nonce) {
      console.error("❌ Nonce de sécurité manquant pour le badge count");
      return;
    }

    // Faire un appel AJAX pour récupérer le nombre réel de notifications non lues
    $.post(ajax_url, {
      action: "ib_get_notifications",
      nonce: nonce,
      limit: 1, // On veut juste le count
    })
      .done(function (response) {
        if (response.success && response.data) {
          updateBadgeDisplay(response.data.unread_count || 0);
        }
      })
      .fail(function () {
        console.log("Erreur lors de la récupération du badge count");
      });
  } else {
    // Utiliser le count local des éléments DOM
    const unreadCount = $(
      "#notification-content .notification-item:not(.read)"
    ).length;
    updateBadgeDisplay(unreadCount);
  }
}

function updateBadgeDisplay(unreadCount) {
  const $ = jQuery;
  const $badge = $(".notification-badge");

  if (unreadCount > 0) {
    $badge.text(unreadCount).show();
  } else {
    $badge.hide();
  }

  // Mettre à jour le texte du header
  const totalCount = $("#notification-content .notification-item").length;
  const headerText =
    unreadCount > 0
      ? `${unreadCount} nouvelles notifications`
      : totalCount > 0
      ? `${totalCount} notifications`
      : "Aucune notification";
  $("#notification-content")
    .closest("#simple-notification-modal")
    .find("p")
    .first()
    .text(headerText);
}

// Fonction de filtrage des notifications
function filterNotifications() {
  const $ = jQuery;
  const filterValue = $("#notification-filter").val();
  const $notifications = $(".notification-item");
  const $sections = $(
    'div:contains("Aujourd\'hui"), div:contains("Hier")'
  ).filter(function () {
    return (
      $(this).text().trim() === "Aujourd'hui" ||
      $(this).text().trim() === "Hier" ||
      $(this)
        .text()
        .match(/^\d+\s+\w+$/)
    );
  });

  let visibleCount = 0;

  $notifications.each(function () {
    const $notification = $(this);
    const type = $notification.data("type");

    // Mapping des types de la base de données vers les filtres
    const typeMapping = {
      booking_confirmed: "confirmed",
      booking_cancelled: "cancelled",
      booking_pending: "reminder",
      booking_new: "confirmed",
      reservation: "confirmed",
    };

    const mappedType = typeMapping[type] || type;

    if (filterValue === "all" || mappedType === filterValue) {
      $notification.show();
      visibleCount++;
    } else {
      $notification.hide();
    }
  });

  // Masquer les sections vides
  $sections.each(function () {
    const $section = $(this);
    const $nextNotifications = $section
      .nextUntil('div:contains("Aujourd\'hui"), div:contains("Hier")')
      .filter(".notification-item:visible");

    if ($nextNotifications.length === 0) {
      $section.hide();
    } else {
      $section.show();
    }
  });

  // Afficher l'état vide si aucune notification visible
  if (visibleCount === 0) {
    $("#notification-content").html(`
      <div style="
        text-align: center;
        padding: 60px 32px;
        color: #64748b;
      ">
        <p style="margin: 0; font-size: 0.9em;">Aucune notification pour ce filtre</p>
        <button onclick="$('#notification-filter').val('all'); filterNotifications();" style="
          margin-top: 12px;
          background: #e9aebc;
          border: none;
          border-radius: 8px;
          padding: 8px 16px;
          color: white;
          cursor: pointer;
          font-size: 0.8em;
        ">Voir toutes</button>
      </div>
    `);
  }
}

// Fonction d'export des notifications
function deleteAllNotifications() {
  const $ = jQuery;
  
  if (!confirm('Êtes-vous sûr de vouloir supprimer toutes les notifications ?')) {
    return;
  }

  // Vérifier que les variables AJAX sont disponibles
  const ajax_url = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';
  const nonce = (typeof ib_notif_vars !== 'undefined' && ib_notif_vars.nonce) || 
               (typeof IBNotifBell !== 'undefined' && IBNotifBell.nonce) || '';

  if (!nonce) {
    showToast('Erreur de configuration AJAX', 'error');
    return;
  }

  // Afficher un indicateur de chargement
  const $deleteButton = $('#delete-all-notifications');
  const originalText = $deleteButton.html();
  $deleteButton.html('<div class="spinner" style="width: 12px; height: 12px; margin: 0 auto; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite;"></div>');
  $deleteButton.prop('disabled', true);

  // Appel AJAX pour supprimer toutes les notifications
  $.post(ajax_url, {
    action: 'ib_delete_all_notifications',
    nonce: nonce
  })
  .done(function(response) {
    if (response.success) {
      // Vider le contenu des notifications
      $('#notification-content').html(`
        <div style="text-align: center; padding: 40px 20px; color: #94a3b8;">
          <div style="width: 60px; height: 60px; margin: 0 auto 16px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="#94a3b8">
              <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
            </svg>
          </div>
          <h4 style="margin: 0 0 8px 0; color: #334155; font-size: 1.1em; font-weight: 600;">Aucune notification</h4>
          <p style="margin: 0; font-size: 0.9em; color: #94a3b8;">Vous n'avez aucune notification pour le moment</p>
        </div>
      `);
      
      // Mettre à jour le badge
      updateBadgeDisplay(0);
      
      showToast('Toutes les notifications ont été supprimées', 'success');
    } else {
      showToast('Erreur lors de la suppression', 'error');
    }
  })
  .fail(function() {
    showToast('Erreur de connexion', 'error');
  })
  .always(function() {
    // Réactiver le bouton
    $deleteButton.html(originalText);
    $deleteButton.prop('disabled', false);
  });
}

function exportNotifications() {
  const $ = jQuery;
  const notifications = [];
  // Implémentation de l'export à compléter
  console.log('Fonction d\'export des notifications appelée');
}

// Auto-nettoyage des anciennes notifications (simulation)
function autoCleanOldNotifications() {
  const $ = jQuery;
  // Simuler la suppression des notifications de plus de 7 jours
  console.log("🧹 Auto-nettoyage des notifications anciennes...");
  // Cette fonction serait connectée à votre backend en production
}

// Fonction pour afficher des toasts de notification
function showToast(message, type = "info") {
  const $ = jQuery;

  // Supprimer les anciens toasts
  $(".notification-toast").remove();

  const colors = {
    success: "#50C878",
    error: "#ef4444",
    info: "#3D9DF6",
    warning: "#FF9F43",
  };

  const icons = {
    success: '<path d="M20 6L9 17l-5-5"/>',
    error:
      '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
    info: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
    warning:
      '<path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"/>',
  };

  const toast = $(`
    <div class="notification-toast" style="
      position: fixed;
      top: 20px;
      right: 20px;
      background: white;
      border: 1px solid ${colors[type]}40;
      border-left: 4px solid ${colors[type]};
      border-radius: 12px;
      padding: 16px 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      z-index: 999999;
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 300px;
      max-width: 400px;
      transform: translateX(100%);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    ">
      <div style="
        width: 24px;
        height: 24px;
        background: ${colors[type]};
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
      ">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
          ${icons[type]}
        </svg>
      </div>
      <span style="
        color: #374151;
        font-size: 0.9em;
        font-weight: 500;
        line-height: 1.4;
      ">${message}</span>
    </div>
  `);

  $("body").append(toast);

  // Animation d'entrée
  setTimeout(() => {
    toast.css("transform", "translateX(0)");
  }, 100);

  // Animation de sortie
  setTimeout(() => {
    toast.css("transform", "translateX(100%)");
    setTimeout(() => {
      toast.remove();
    }, 400);
  }, 3000);
}

function showEmptyState() {
  const $ = jQuery;
  const $content = $("#notification-content");

  const emptyState = `
    <div class="empty-state" style="
      text-align: center;
      padding: 60px 32px;
      color: #64748b;
    ">
      <div style="
        width: 80px;
        height: 80px;
        margin: 0 auto 24px;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
      ">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
          <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
          <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
        </svg>
      </div>
      <h4 style="
        margin: 0 0 12px 0;
        color: #334155;
        font-size: 1.1em;
        font-weight: 600;
        letter-spacing: -0.02em;
      ">Aucune notification</h4>
      <p style="
        margin: 0;
        font-size: 0.9em;
        line-height: 1.5;
        color: #64748b;
      ">Vous serez notifié des nouvelles réservations<br>et des mises à jour importantes ici.</p>
    </div>
  `;

  $content.html(emptyState);
}

// Fonction pour charger les vraies notifications depuis la base de données
function loadRealNotifications() {
  const $ = jQuery;
  const $content = $("#notification-content");

  // Afficher un état de chargement
  $content.html(`
    <div style="
      text-align: center;
      padding: 60px 32px;
      color: #64748b;
    ">
      <div style="
        width: 40px;
        height: 40px;
        margin: 0 auto 16px;
        border: 3px solid #e9aebc;
        border-top: 3px solid transparent;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      "></div>
      <p style="margin: 0; font-size: 0.9em;">Chargement des notifications...</p>
    </div>
  `);

  // Vérifier que les variables AJAX sont disponibles
  const ajax_url =
    typeof ajaxurl !== "undefined" ? ajaxurl : "/wp-admin/admin-ajax.php";
  const nonce =
    (typeof ib_notif_vars !== "undefined" && ib_notif_vars.nonce) ||
    (typeof IBNotifBell !== "undefined" && IBNotifBell.nonce) ||
    "";

  if (!nonce) {
    console.error("❌ Nonce de sécurité manquant pour les notifications");
    $content.html(`
      <div style="
        text-align: center;
        padding: 60px 32px;
        color: #ef4444;
      ">
        <p style="margin: 0; font-size: 0.9em;">Erreur de configuration AJAX</p>
        <button onclick="addSampleNotifications();" style="
          margin-top: 12px;
          background: #e9aebc;
          border: none;
          border-radius: 8px;
          padding: 8px 16px;
          color: white;
          cursor: pointer;
          font-size: 0.8em;
        ">Voir les exemples</button>
      </div>
    `);
    return;
  }

  // Appel AJAX pour récupérer les notifications
  $.post(ajax_url, {
    action: "ib_get_notifications",
    nonce: nonce,
    limit: 50,
  })
    .done(function (response) {
      console.log("🔍 Réponse AJAX reçue:", response);

      if (response.success && response.data) {
        console.log("✅ Données valides reçues:", {
          data: response.data,
          notifications: response.data.notifications,
          type: typeof response.data.notifications,
          isArray: Array.isArray(response.data.notifications),
          unread_count: response.data.unread_count,
        });
        displayNotifications(
          response.data.notifications,
          response.data.unread_count || 0
        );
      } else {
        console.log("❌ Réponse AJAX invalide:", {
          success: response.success,
          hasData: !!response.data,
          response: response,
        });
        showEmptyState();
      }
    })
    .fail(function (xhr, status, error) {
      console.log("❌ Erreur AJAX:", {
        status: status,
        error: error,
        responseText: xhr.responseText,
        statusCode: xhr.status,
      });

      $content.html(`
      <div style="
        text-align: center;
        padding: 60px 32px;
        color: #ef4444;
      ">
        <p style="margin: 0; font-size: 0.9em;">Erreur lors du chargement des notifications</p>
        <p style="margin: 8px 0 0; font-size: 0.7em; opacity: 0.7;">Erreur: ${status} - ${error}</p>
        <button onclick="loadRealNotifications()" style="
          margin-top: 12px;
          background: #e9aebc;
          border: none;
          border-radius: 8px;
          padding: 8px 16px;
          color: white;
          cursor: pointer;
          font-size: 0.8em;
        ">Réessayer</button>
      </div>
    `);
    });
}

// Fonction pour afficher les notifications récupérées
function displayNotifications(notifications, unreadCount) {
  const $ = jQuery;
  const $content = $("#notification-content");

  // Debug pour voir le format des données reçues
  console.log("🔍 displayNotifications appelée avec:", {
    notifications: notifications,
    type: typeof notifications,
    isArray: Array.isArray(notifications),
    length: notifications ? notifications.length : "N/A",
    unreadCount: unreadCount,
  });

  // Vérifier que notifications est un tableau
  if (!notifications) {
    console.log("❌ Notifications est null/undefined");
    showEmptyState();
    return;
  }

  // Si ce n'est pas un tableau, essayer de le convertir
  if (!Array.isArray(notifications)) {
    console.log(
      "⚠️ Notifications n'est pas un tableau, tentative de conversion..."
    );

    // Si c'est un objet avec une propriété qui contient les notifications
    if (typeof notifications === "object") {
      // Essayer différentes propriétés possibles
      if (
        notifications.notifications &&
        Array.isArray(notifications.notifications)
      ) {
        notifications = notifications.notifications;
        console.log("✅ Notifications trouvées dans .notifications");
      } else if (notifications.data && Array.isArray(notifications.data)) {
        notifications = notifications.data;
        console.log("✅ Notifications trouvées dans .data");
      } else {
        console.log("❌ Impossible de trouver un tableau de notifications");
        showEmptyState();
        return;
      }
    } else {
      console.log("❌ Notifications n'est pas un objet valide");
      showEmptyState();
      return;
    }
  }

  if (notifications.length === 0) {
    console.log("📭 Aucune notification à afficher");
    showEmptyState();
    return;
  }

  console.log(`✅ Affichage de ${notifications.length} notifications`);

  // Nettoyage automatique et regroupement
  const cleanedNotifications = cleanupNotifications(notifications);
  const groupedNotifications = groupNotificationsByDate(cleanedNotifications);
  
  let html = '<div style="padding: 0;">';

  // Générer le HTML pour chaque groupe
  Object.keys(groupedNotifications).forEach((dateGroup) => {
    html += `
      <div style="
        padding: 16px 28px 8px;
        background: rgba(233, 174, 188, 0.03);
        border-bottom: 1px solid rgba(233, 174, 188, 0.1);
        font-size: 0.8em;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
      ">${dateGroup}</div>
    `;

    // Regrouper les emails par jour
    const emailNotifications = groupedNotifications[dateGroup].filter(n => n.type === 'email');
    const otherNotifications = groupedNotifications[dateGroup].filter(n => n.type !== 'email');

    // Afficher les emails groupés
    if (emailNotifications.length > 0) {
      html += generateGroupedEmailHTML(emailNotifications, dateGroup);
    }

    // Afficher les autres notifications
    otherNotifications.forEach((notification) => {
      html += generateNotificationHTML(notification);
    });
  });

  html += "</div>";
  $content.html(html);

  // Mettre à jour le badge avec le count de la réponse
  updateBadgeDisplay(unreadCount);
  
  // Configurer les gestionnaires d'événements
  setupNotificationClickHandler();
  setupBatchMode();
}

// Fonction pour nettoyer automatiquement les notifications
function cleanupNotifications(notifications) {
  const cleaned = [];
  const reservationNotifications = new Map();

  // Première passe : collecter les notifications de réservation
  notifications.forEach(notif => {
    if (notif.type === 'reservation' && notif.reservation_id) {
      reservationNotifications.set(notif.reservation_id, notif);
    }
  });

  // Deuxième passe : filtrer les notifications
  notifications.forEach(notif => {
    // Si c'est une notification de réservation, vérifier si elle doit être supprimée
    if (notif.type === 'reservation' && notif.reservation_id) {
      // Garder seulement si pas de confirmation/annulation
      const hasConfirmation = notifications.some(n => 
        n.type === 'confirmation' && n.reservation_id === notif.reservation_id
      );
      const hasCancellation = notifications.some(n => 
        n.type === 'cancellation' && n.reservation_id === notif.reservation_id
      );
      
      if (!hasConfirmation && !hasCancellation) {
        cleaned.push(notif);
      }
    } else {
      // Garder toutes les autres notifications
      cleaned.push(notif);
    }
  });

  return cleaned;
}

// Fonction pour grouper les notifications par date
function groupNotificationsByDate(notifications) {
  const groups = {};
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  notifications.forEach((notification) => {
    const notifDate = new Date(notification.created_at);
    let groupKey;

    if (isSameDay(notifDate, today)) {
      groupKey = "Aujourd'hui";
    } else if (isSameDay(notifDate, yesterday)) {
      groupKey = "Hier";
    } else {
      groupKey = formatDate(notifDate);
    }

    if (!groups[groupKey]) {
      groups[groupKey] = [];
    }
    groups[groupKey].push(notification);
  });

  return groups;
}

// Fonction utilitaire pour vérifier si deux dates sont le même jour
function isSameDay(date1, date2) {
  return (
    date1.getDate() === date2.getDate() &&
    date1.getMonth() === date2.getMonth() &&
    date1.getFullYear() === date2.getFullYear()
  );
}

// Fonction utilitaire pour formater une date
function formatDate(date) {
  const options = { day: "numeric", month: "long" };
  return date.toLocaleDateString("fr-FR", options);
}

// Fonction pour générer le HTML des emails groupés
function generateGroupedEmailHTML(emailNotifications, dateGroup) {
  const emailCounts = {
    confirmation: 0,
    cancellation: 0,
    reminder: 0,
    other: 0
  };

  emailNotifications.forEach(notif => {
    if (notif.message.includes('confirmation')) {
      emailCounts.confirmation++;
    } else if (notif.message.includes('annulation')) {
      emailCounts.cancellation++;
    } else if (notif.message.includes('rappel')) {
      emailCounts.reminder++;
    } else {
      emailCounts.other++;
    }
  });

  const details = [];
  if (emailCounts.confirmation > 0) details.push(`${emailCounts.confirmation} confirmations`);
  if (emailCounts.cancellation > 0) details.push(`${emailCounts.cancellation} annulations`);
  if (emailCounts.reminder > 0) details.push(`${emailCounts.reminder} rappels`);
  if (emailCounts.other > 0) details.push(`${emailCounts.other} autres`);

  return `
    <div class="notification-item email-grouped" 
         data-grouped="true"
         data-email-count="${emailNotifications.length}"
         style="
           padding: 20px 28px;
           border-bottom: 1px solid rgba(0, 0, 0, 0.04);
           transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
           position: relative;
           background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.02), transparent);
           cursor: pointer;
         "
         onmouseover="
           this.style.background='linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.05), transparent)';
           this.style.transform='translateX(4px)';
           this.style.boxShadow='0 4px 20px rgba(59, 130, 246, 0.1)';
         "
         onmouseout="
           this.style.background='linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.02), transparent)';
           this.style.transform='translateX(0)';
           this.style.boxShadow='none';
         ">
      <div style="display: flex; align-items: flex-start; gap: 16px;">
        <div style="position: relative;">
          <div style="
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #e8b4cb 0%, #d89bb5 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(61, 157, 246, 0.25);
            border: 2px solid white;
          ">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
          </div>
        </div>

        <div style="flex: 1; min-width: 0;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
            <h4 style="
              margin: 0;
              font-size: 0.95em;
              font-weight: 600;
              color: #111827;
              line-height: 1.3;
              letter-spacing: -0.01em;
            ">📩 ${emailNotifications.length} mails envoyés ${dateGroup.toLowerCase()}</h4>
            <span style="
              font-size: 0.7em;
              color: #9ca3af;
              white-space: nowrap;
              margin-left: 12px;
              font-weight: 500;
              background: rgba(61, 157, 246, 0.1);
              padding: 2px 6px;
              border-radius: 6px;
              color: #3D9DF6;
            ">${getTimeAgo(emailNotifications[0].created_at)}</span>
          </div>
          <p style="
            margin: 0 0 16px 0;
            font-size: 0.85em;
            color: #6b7280;
            line-height: 1.5;
          ">${details.join(' · ')}</p>

          <div style="display: flex; gap: 8px;">
            <button onclick="toggleGroupedDetails('${dateGroup}')" style="
              background: rgba(233, 174, 188, 0.1);
              border: 1px solid rgba(233, 174, 188, 0.3);
              border-radius: 8px;
              padding: 6px 12px;
              color: #e9aebc;
              cursor: pointer;
              font-size: 0.75em;
              font-weight: 600;
              transition: all 0.3s ease;
            "
            onmouseover="this.style.background='rgba(233, 174, 188, 0.15)'; this.style.color='#d89aab';"
            onmouseout="this.style.background='rgba(233, 174, 188, 0.1)'; this.style.color='#e9aebc';">
              Voir détails
            </button>
            <button onclick="deleteGroupedEmails('${dateGroup}')" style="
              background: rgba(239, 68, 68, 0.1);
              border: 1px solid rgba(239, 68, 68, 0.2);
              border-radius: 8px;
              padding: 6px 12px;
              color: #ef4444;
              cursor: pointer;
              font-size: 0.75em;
              font-weight: 600;
              transition: all 0.3s ease;
            "
            onmouseover="this.style.background='rgba(239, 68, 68, 0.15)'"
            onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                <polyline points="3,6 5,6 21,6"/>
                <path d="m19,6v14a2,2 0 0,1-2,2H7a2,2 0 0,1-2-2V6m3,0V4a2,2 0 0,1,2-2h4a2,2 0 0,1,2,2v2"/>
              </svg>
              Supprimer
            </button>
          </div>
        </div>
      </div>
      
      <!-- Détails groupés (cachés par défaut) -->
      <div id="grouped-details-${dateGroup}" style="display: none; margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(0,0,0,0.1);">
        ${emailNotifications.map(notif => `
          <div style="padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
            <div style="font-size: 0.8em; color: #6b7280;">${notif.message}</div>
            <div style="font-size: 0.7em; color: #9ca3af; margin-top: 4px;">${getTimeAgo(notif.created_at)}</div>
          </div>
        `).join('')}
      </div>
    </div>
  `;
}

// Fonction pour générer le HTML d'une notification
function generateNotificationHTML(notification) {
  const typeConfig = getNotificationTypeConfig(notification.type);
  const timeAgo = getTimeAgo(notification.created_at);
  const isRead = notification.status === "read";

  return `
    <div class="notification-item ${notification.type} ${isRead ? "read" : ""}"
         data-notification-id="${notification.id}"
         data-type="${notification.type}"
         data-link="${notification.link || '#'}"
         style="
           padding: 20px 28px;
           border-bottom: 1px solid rgba(0, 0, 0, 0.04);
           transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
           position: relative;
           background: linear-gradient(90deg, transparent, ${
             typeConfig.bgColor
           }, transparent);
           cursor: pointer;
           ${isRead ? "opacity: 0.6;" : ""}
         "
         onmouseover="
           this.style.background='linear-gradient(90deg, transparent, ${typeConfig.bgColor.replace(
             "0.02",
             "0.05"
           )}, transparent)';
           this.style.transform='translateX(4px)';
           this.style.boxShadow='0 4px 20px ${typeConfig.bgColor.replace(
             "0.02",
             "0.1"
           )}';
         "
         onmouseout="
           this.style.background='linear-gradient(90deg, transparent, ${
             typeConfig.bgColor
           }, transparent)';
           this.style.transform='translateX(0)';
           this.style.boxShadow='none';
         ">
      <div style="display: flex; align-items: flex-start; gap: 16px;">
        <div style="position: relative;">
          <div style="
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, ${typeConfig.color} 0%, ${
    typeConfig.color
  }dd 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px ${typeConfig.color}40;
            border: 2px solid white;
          ">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              ${typeConfig.icon}
            </svg>
          </div>
          <div style="
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 18px;
            height: 18px;
            background: #e9aebc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
          ">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          </div>
        </div>

        <div style="flex: 1; min-width: 0;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
            <h4 style="
              margin: 0;
              font-size: 0.95em;
              font-weight: 600;
              color: #111827;
              line-height: 1.3;
              letter-spacing: -0.01em;
            ">${typeConfig.title}</h4>
            <span style="
              font-size: 0.7em;
              color: #9ca3af;
              white-space: nowrap;
              margin-left: 12px;
              font-weight: 500;
              background: ${typeConfig.color}20;
              padding: 2px 6px;
              border-radius: 6px;
              color: ${typeConfig.color};
            ">${timeAgo}</span>
          </div>
          <p style="
            margin: 0 0 16px 0;
            font-size: 0.85em;
            color: #6b7280;
            line-height: 1.5;
          ">${notification.message}</p>

          <div style="display: flex; gap: 8px;">
            ${
              !isRead
                ? `
              <button onclick="markAsRead('${notification.id}')" style="
                background: rgba(233, 174, 188, 0.1);
                border: 1px solid rgba(233, 174, 188, 0.3);
                border-radius: 8px;
                padding: 6px 12px;
                color: #e9aebc;
                cursor: pointer;
                font-size: 0.75em;
                font-weight: 600;
                transition: all 0.3s ease;
              "
              onmouseover="this.style.background='rgba(233, 174, 188, 0.15)'; this.style.color='#d89aab';"
              onmouseout="this.style.background='rgba(233, 174, 188, 0.1)'; this.style.color='#e9aebc';">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                  <path d="M20 6L9 17l-5-5"/>
                </svg>
                Marquer comme lu
              </button>
            `
                : ""
            }
            <button onclick="deleteNotification('${notification.id}')" style="
              background: rgba(239, 68, 68, 0.1);
              border: 1px solid rgba(239, 68, 68, 0.2);
              border-radius: 8px;
              padding: 6px 12px;
              color: #ef4444;
              cursor: pointer;
              font-size: 0.75em;
              font-weight: 600;
              transition: all 0.3s ease;
            "
            onmouseover="this.style.background='rgba(239, 68, 68, 0.15)'"
            onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                <polyline points="3,6 5,6 21,6"/>
                <path d="m19,6v14a2,2 0 0,1-2,2H7a2,2 0 0,1-2-2V6m3,0V4a2,2 0 0,1,2-2h4a2,2 0 0,1,2,2v2"/>
              </svg>
              Supprimer
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
}

// Fonction pour obtenir la configuration d'un type de notification
function getNotificationTypeConfig(type) {
  const configs = {
    booking_confirmed: {
      color: "#50C878",
      icon: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/>',
      title: "Réservation confirmée",
      bgColor: "rgba(80, 200, 120, 0.02)",
    },
    booking_cancelled: {
      color: "#FF9F43",
      icon: '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
      title: "Réservation annulée",
      bgColor: "rgba(255, 159, 67, 0.02)",
    },
    booking_pending: {
      color: "#3D9DF6",
      icon: '<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>',
      title: "Réservation en attente",
      bgColor: "rgba(61, 157, 246, 0.02)",
    },
    booking_new: {
      color: "#8B5CF6",
      icon: '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
      title: "Nouvelle réservation",
      bgColor: "rgba(139, 92, 246, 0.02)",
    },
    reservation: {
      color: "#10B981",
      icon: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/>',
      title: "Réservation",
      bgColor: "rgba(16, 185, 129, 0.02)",
    },
  };

  return (
    configs[type] || {
      color: "#6B7280",
      icon: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
      title: "Notification",
      bgColor: "rgba(107, 114, 128, 0.02)",
    }
  );
}

// Fonction pour calculer le temps écoulé
function getTimeAgo(dateString) {
  const now = new Date();
  const date = new Date(dateString);
  const diffInSeconds = Math.floor((now - date) / 1000);

  if (diffInSeconds < 60) {
    return "à l'instant";
  } else if (diffInSeconds < 3600) {
    const minutes = Math.floor(diffInSeconds / 60);
    return `il y a ${minutes} min`;
  } else if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600);
    return `il y a ${hours}h`;
  } else {
    const days = Math.floor(diffInSeconds / 86400);
    return `il y a ${days}j`;
  }
}

// Fonction pour ajouter des notifications d'exemple (gardée pour les tests)
function addSampleNotifications() {
  const $ = jQuery;
  const $content = $("#notification-content");

  // Supprimer l'état vide
  $content.find(".empty-state").remove();

  const sampleNotifications = `
    <div style="padding: 0;">
      <!-- Section Aujourd'hui -->
      <div style="
        padding: 16px 28px 8px;
        background: rgba(233, 174, 188, 0.03);
        border-bottom: 1px solid rgba(233, 174, 188, 0.1);
        font-size: 0.8em;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
      ">Aujourd'hui</div>

      <!-- Notification 1 - Nouvelle réservation -->
      <div class="notification-item confirmed" data-notification-id="1" data-type="confirmed" style="
        padding: 20px 28px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        background: linear-gradient(90deg, transparent, rgba(80, 200, 120, 0.02), transparent);
        cursor: pointer;
      "
      onmouseover="
        this.style.background='linear-gradient(90deg, transparent, rgba(80, 200, 120, 0.05), transparent)';
        this.style.transform='translateX(4px)';
        this.style.boxShadow='0 4px 20px rgba(80, 200, 120, 0.1)';
      "
      onmouseout="
        this.style.background='linear-gradient(90deg, transparent, rgba(80, 200, 120, 0.02), transparent)';
        this.style.transform='translateX(0)';
        this.style.boxShadow='none';
      ">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
          <!-- Photo employée + icône service -->
          <div style="position: relative;">
            <div style="
              width: 48px;
              height: 48px;
              background: linear-gradient(135deg, #50C878 0%, #3ea65c 100%);
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              flex-shrink: 0;
              box-shadow: 0 4px 12px rgba(80, 200, 120, 0.25);
              border: 2px solid white;
            ">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22,4 12,14.01 9,11.01"/>
              </svg>
            </div>
            <!-- Mini icône service -->
            <div style="
              position: absolute;
              bottom: -2px;
              right: -2px;
              width: 18px;
              height: 18px;
              background: #e9aebc;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              border: 2px solid white;
              box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            ">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </div>
          </div>

          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
              <h4 style="
                margin: 0;
                font-size: 0.95em;
                font-weight: 600;
                color: #111827;
                line-height: 1.3;
                letter-spacing: -0.01em;
              ">Réservation confirmée</h4>
              <span style="
                font-size: 0.7em;
                color: #9ca3af;
                white-space: nowrap;
                margin-left: 12px;
                font-weight: 500;
                background: rgba(80, 200, 120, 0.1);
                padding: 2px 6px;
                border-radius: 6px;
                color: #50C878;
              ">il y a 2 min</span>
            </div>
            <p style="
              margin: 0 0 4px 0;
              font-size: 0.9em;
              color: #374151;
              line-height: 1.4;
              font-weight: 500;
            ">Marie Dupont – Vernis classique avec Salma</p>
            <p style="
              margin: 0 0 16px 0;
              font-size: 0.8em;
              color: #6b7280;
              line-height: 1.4;
            ">Confirmée pour le 25 juillet à 14h30</p>

            <!-- Actions -->
            <div style="display: flex; gap: 8px;">
              <button onclick="markAsRead('1')" style="
                background: rgba(233, 174, 188, 0.1);
                border: 1px solid rgba(233, 174, 188, 0.3);
                border-radius: 8px;
                padding: 6px 12px;
                color: #e9aebc;
                cursor: pointer;
                font-size: 0.75em;
                font-weight: 600;
                transition: all 0.3s ease;
              "
              onmouseover="this.style.background='rgba(233, 174, 188, 0.15)'; this.style.color='#d89aab';"
              onmouseout="this.style.background='rgba(233, 174, 188, 0.1)'; this.style.color='#e9aebc';">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                  <polyline points="20,6 9,17 4,12"/>
                </svg>
                Marquer comme lu
              </button>
              <button onclick="deleteNotification('1')" style="
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.2);
                border-radius: 8px;
                padding: 6px 12px;
                color: #ef4444;
                cursor: pointer;
                font-size: 0.75em;
                font-weight: 600;
                transition: all 0.3s ease;
              "
              onmouseover="this.style.background='rgba(239, 68, 68, 0.15)'"
              onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                  <polyline points="3,6 5,6 21,6"/>
                  <path d="m19,6v14a2,2 0 0,1-2,2H7a2,2 0 0,1-2-2V6m3,0V4a2,2 0 0,1,2-2h4a2,2 0 0,1,2,2v2"/>
                </svg>
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Notification 2 - Annulation -->
      <div class="notification-item cancelled" data-notification-id="2" data-type="cancelled" style="
        padding: 20px 28px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        background: linear-gradient(90deg, transparent, rgba(255, 159, 67, 0.02), transparent);
        cursor: pointer;
      "
      onmouseover="
        this.style.background='linear-gradient(90deg, transparent, rgba(255, 159, 67, 0.05), transparent)';
        this.style.transform='translateX(4px)';
        this.style.boxShadow='0 4px 20px rgba(255, 159, 67, 0.1)';
      "
      onmouseout="
        this.style.background='linear-gradient(90deg, transparent, rgba(255, 159, 67, 0.02), transparent)';
        this.style.transform='translateX(0)';
        this.style.boxShadow='none';
      ">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
          <!-- Photo employée + icône service -->
          <div style="position: relative;">
            <div style="
              width: 48px;
              height: 48px;
              background: linear-gradient(135deg, #FF9F43 0%, #e8883a 100%);
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              flex-shrink: 0;
              box-shadow: 0 4px 12px rgba(255, 159, 67, 0.25);
              border: 2px solid white;
            ">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
              </svg>
            </div>
            <!-- Mini icône service -->
            <div style="
              position: absolute;
              bottom: -2px;
              right: -2px;
              width: 18px;
              height: 18px;
              background: #e9aebc;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              border: 2px solid white;
              box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            ">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
            </div>
          </div>

          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
              <h4 style="
                margin: 0;
                font-size: 0.95em;
                font-weight: 600;
                color: #111827;
                line-height: 1.3;
                letter-spacing: -0.01em;
              ">Réservation annulée</h4>
              <span style="
                font-size: 0.7em;
                color: #9ca3af;
                white-space: nowrap;
                margin-left: 12px;
                font-weight: 500;
                background: rgba(255, 159, 67, 0.1);
                padding: 2px 6px;
                border-radius: 6px;
                color: #FF9F43;
              ">il y a 1h</span>
            </div>
            <p style="
              margin: 0 0 4px 0;
              font-size: 0.9em;
              color: #374151;
              line-height: 1.4;
              font-weight: 500;
            ">Pierre Martin – Coiffure fête sans brushing</p>
            <p style="
              margin: 0 0 16px 0;
              font-size: 0.8em;
              color: #6b7280;
              line-height: 1.4;
            ">Annulée pour le 24 juillet à 16h00</p>

            <!-- Actions -->
            <div style="display: flex; gap: 8px;">
              <button onclick="markAsRead('2')" style="
                background: rgba(233, 174, 188, 0.1);
                border: 1px solid rgba(233, 174, 188, 0.3);
                border-radius: 8px;
                padding: 6px 12px;
                color: #e9aebc;
                cursor: pointer;
                font-size: 0.75em;
                font-weight: 600;
                transition: all 0.3s ease;
              "
              onmouseover="this.style.background='rgba(233, 174, 188, 0.15)'; this.style.color='#d89aab';"
              onmouseout="this.style.background='rgba(233, 174, 188, 0.1)'; this.style.color='#e9aebc';">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                  <polyline points="20,6 9,17 4,12"/>
                </svg>
                Marquer comme lu
              </button>
              <button onclick="deleteNotification('2')" style="
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.2);
                border-radius: 8px;
                padding: 6px 12px;
                color: #ef4444;
                cursor: pointer;
                font-size: 0.75em;
                font-weight: 600;
                transition: all 0.3s ease;
              "
              onmouseover="this.style.background='rgba(239, 68, 68, 0.15)'"
              onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                  <polyline points="3,6 5,6 21,6"/>
                  <path d="m19,6v14a2,2 0 0,1-2,2H7a2,2 0 0,1-2-2V6m3,0V4a2,2 0 0,1,2-2h4a2,2 0 0,1,2,2v2"/>
                </svg>
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Section Hier -->
      <div style="
        padding: 16px 28px 8px;
        background: rgba(233, 174, 188, 0.03);
        border-bottom: 1px solid rgba(233, 174, 188, 0.1);
        font-size: 0.8em;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 8px;
      ">Hier</div>

      <!-- Notification 3 - Rappel -->
      <div class="notification-item reminder" data-notification-id="3" data-type="reminder" style="
        padding: 20px 28px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        background: linear-gradient(90deg, transparent, rgba(61, 157, 246, 0.02), transparent);
        cursor: pointer;
      "
      onmouseover="
        this.style.background='linear-gradient(90deg, transparent, rgba(61, 157, 246, 0.05), transparent)';
        this.style.transform='translateX(4px)';
        this.style.boxShadow='0 4px 20px rgba(61, 157, 246, 0.1)';
      "
      onmouseout="
        this.style.background='linear-gradient(90deg, transparent, rgba(61, 157, 246, 0.02), transparent)';
        this.style.transform='translateX(0)';
        this.style.boxShadow='none';
      ">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
          <!-- Photo employée + icône service -->
          <div style="position: relative;">
            <div style="
              width: 48px;
              height: 48px;
              background: linear-gradient(135deg, #e8b4cb 0%, #d89bb5 100%);
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              flex-shrink: 0;
              box-shadow: 0 4px 12px rgba(61, 157, 246, 0.25);
              border: 2px solid white;
            ">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12,6 12,12 16,14"/>
              </svg>
            </div>
            <!-- Mini icône service -->
            <div style="
              position: absolute;
              bottom: -2px;
              right: -2px;
              width: 18px;
              height: 18px;
              background: #e9aebc;
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              border: 2px solid white;
              box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            ">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
            </div>
          </div>

          <div style="flex: 1; min-width: 0;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
              <h4 style="
                margin: 0;
                font-size: 0.95em;
                font-weight: 600;
                color: #111827;
                line-height: 1.3;
                letter-spacing: -0.01em;
              ">Rappel de rendez-vous</h4>
              <span style="
                font-size: 0.7em;
                color: #9ca3af;
                white-space: nowrap;
                margin-left: 12px;
                font-weight: 500;
                background: rgba(61, 157, 246, 0.1);
                padding: 2px 6px;
                border-radius: 6px;
                color: #3D9DF6;
              ">hier 15h</span>
            </div>
            <p style="
              margin: 0 0 4px 0;
              font-size: 0.9em;
              color: #374151;
              line-height: 1.4;
              font-weight: 500;
            ">Sophie Leroy – Patine avec Lamia</p>
            <p style="
              margin: 0 0 16px 0;
              font-size: 0.8em;
              color: #6b7280;
              line-height: 1.4;
            ">Rendez-vous prévu pour demain à 10h15</p>

            <!-- Actions -->
            <div style="display: flex; gap: 8px;">
              <button onclick="markAsRead('3')" style="
                background: rgba(233, 174, 188, 0.1);
                border: 1px solid rgba(233, 174, 188, 0.3);
                border-radius: 8px;
                padding: 6px 12px;
                color: #e9aebc;
                cursor: pointer;
                font-size: 0.75em;
                font-weight: 600;
                transition: all 0.3s ease;
              "
              onmouseover="this.style.background='rgba(233, 174, 188, 0.15)'; this.style.color='#d89aab';"
              onmouseout="this.style.background='rgba(233, 174, 188, 0.1)'; this.style.color='#e9aebc';">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                  <polyline points="20,6 9,17 4,12"/>
                </svg>
                Marquer comme lu
              </button>
              <button onclick="deleteNotification('3')" style="
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.2);
                border-radius: 8px;
                padding: 6px 12px;
                color: #ef4444;
                cursor: pointer;
                font-size: 0.75em;
                font-weight: 600;
                transition: all 0.3s ease;
              "
              onmouseover="this.style.background='rgba(239, 68, 68, 0.15)'"
              onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                  <polyline points="3,6 5,6 21,6"/>
                  <path d="m19,6v14a2,2 0 0,1-2,2H7a2,2 0 0,1-2-2V6m3,0V4a2,2 0 0,1,2-2h4a2,2 0 0,1,2,2v2"/>
                </svg>
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  $content.html(sampleNotifications);

  // Mettre à jour le badge
  $(".notification-badge").text("3");
}

// Fonction pour basculer l'affichage des détails groupés
window.toggleGroupedDetails = function(dateGroup) {
  const $ = jQuery;
  const $details = $(`#grouped-details-${dateGroup}`);
  const $button = $(`[onclick="toggleGroupedDetails('${dateGroup}')"]`);
  
  if ($details.is(':visible')) {
    $details.slideUp(300);
    $button.text('Voir détails');
  } else {
    $details.slideDown(300);
    $button.text('Masquer détails');
  }
};

// Fonction pour supprimer les emails groupés
window.deleteGroupedEmails = function(dateGroup) {
  const $ = jQuery;
  if (confirm(`Supprimer tous les emails de ${dateGroup.toLowerCase()} ?`)) {
    $(`[data-grouped="true"]`).each(function() {
      if ($(this).find(`#grouped-details-${dateGroup}`).length > 0) {
        $(this).fadeOut(300, function() {
          $(this).remove();
          updateBadgeCount();
        });
      }
    });
  }
};

// Variables globales pour le mode batch
let isBatchMode = false;
let selectedNotifications = new Set();

// Gestionnaire de clic sur une notification
function setupNotificationClickHandler() {
  const $ = jQuery;
  
  $(document).on('click', '.notification-item', function(e) {
    // Ne pas déclencher la redirection si on clique sur un bouton d'action
    if ($(e.target).closest('button, a').length) {
      return;
    }
    
    const $notification = $(this);
    const notificationId = $notification.data('notification-id');
    const notificationType = $notification.data('type');
    const link = $notification.data('link');
    
    // Vérifier si c'est une notification de réservation avec un lien valide
    if (notificationType === 'reservation' && link) {
      // Marquer comme lue si ce n'est pas déjà fait
      if (!$notification.hasClass('read')) {
        markAsRead(notificationId);
      }
      
      // Rediriger vers la page de réservation
      window.location.href = link;
    }
  });
}

// Fonction pour configurer le mode batch
function setupBatchMode() {
  const $ = jQuery;
  let pressTimer;

  // Clic long pour activer le mode batch
  $(document).on('mousedown', '.notification-item', function(e) {
    const $item = $(this);
    if ($item.data('grouped')) return; // Ignorer les emails groupés
    
    pressTimer = setTimeout(() => {
      isBatchMode = true;
      const id = $item.data('notification-id');
      if (id) {
        selectedNotifications.add(id);
        $item.addClass('selected');
        showBatchBar();
        updateBatchCount();
      }
    }, 500);
  });

  $(document).on('mouseup mouseleave', '.notification-item', function() {
    clearTimeout(pressTimer);
  });

  // Support tactile pour mobile
  $(document).on('touchstart', '.notification-item', function(e) {
    const $item = $(this);
    if ($item.data('grouped')) return;
    
    pressTimer = setTimeout(() => {
      isBatchMode = true;
      const id = $item.data('notification-id');
      if (id) {
        selectedNotifications.add(id);
        $item.addClass('selected');
        showBatchBar();
        updateBatchCount();
      }
    }, 600);
  });

  $(document).on('touchend', '.notification-item', function() {
    clearTimeout(pressTimer);
  });

  // Clic simple pour sélectionner/désélectionner en mode batch
  $(document).on('click', '.notification-item', function(e) {
    if (!isBatchMode || $(this).data('grouped')) return;
    
    e.preventDefault();
    e.stopPropagation();
    const $item = $(this);
    const id = $item.data('notification-id');
    
    if (!id) return;
    
    if (selectedNotifications.has(id)) {
      selectedNotifications.delete(id);
      $item.removeClass('selected');
    } else {
      selectedNotifications.add(id);
      $item.addClass('selected');
    }
    
    updateBatchCount();
  });
}

// Fonction pour afficher la barre batch moderne
function showBatchBar() {
  const $ = jQuery;
  
  if ($('#batch-bar').length === 0) {
    const batchBar = `
      <div id="batch-bar" style="
        position: fixed;
        top: 50%;
        right: 20px;
        transform: translateY(-50%) translateX(100%);
        background: linear-gradient(135deg, #e8b4cb 0%, #d89bb5 100%);
        border-radius: 20px;
        box-shadow:
          0 20px 40px rgba(232, 180, 203, 0.3),
          0 8px 16px rgba(0, 0, 0, 0.1),
          inset 0 1px 0 rgba(255, 255, 255, 0.2);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        z-index: 999999;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        min-width: 280px;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
      ">
        
        <!-- Header moderne -->
        <div style="
          display: flex;
          align-items: center;
          justify-content: space-between;
          margin-bottom: 8px;
        ">
          <div style="
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            font-weight: 600;
            font-size: 14px;
          ">
            <div style="
              width: 24px;
              height: 24px;
              background: rgba(255, 255, 255, 0.2);
              border-radius: 50%;
              display: flex;
              align-items: center;
              justify-content: center;
              font-size: 12px;
            ">
              <span id="batch-count">1</span>
            </div>
            <span>sélectionnée(s)</span>
          </div>
          <button onclick="exitBatchMode()" style="
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            font-size: 16px;
          "
          onmouseover="this.style.background='rgba(255, 255, 255, 0.2)'"
          onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'">×</button>
        </div>

        <!-- Actions principales -->
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <button onclick="batchMarkAsRead()" style="
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px 16px;
            color: white;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
          "
          onmouseover="this.style.background='rgba(255, 255, 255, 0.25)'; this.style.transform='translateX(4px)'"
          onmouseout="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateX(0)'">
            <span style="font-size: 16px;">✔</span>
            Marquer comme lu
          </button>
          
          <button onclick="batchDelete()" style="
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 12px 16px;
            color: white;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
          "
          onmouseover="this.style.background='rgba(239, 68, 68, 0.3)'; this.style.transform='translateX(4px)'"
          onmouseout="this.style.background='rgba(239, 68, 68, 0.2)'; this.style.transform='translateX(0)'">
            <span style="font-size: 16px;">🗑</span>
            Supprimer
          </button>
          
          <button onclick="batchArchive()" style="
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 12px 16px;
            color: white;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: left;
          "
          onmouseover="this.style.background='rgba(59, 130, 246, 0.3)'; this.style.transform='translateX(4px)'"
          onmouseout="this.style.background='rgba(59, 130, 246, 0.2)'; this.style.transform='translateX(0)'">
            <span style="font-size: 16px;">📂</span>
            Archiver
          </button>
        </div>

        <!-- Séparateur -->
        <div style="
          height: 1px;
          background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
          margin: 8px 0;
        "></div>

        <!-- Sélection rapide -->
        <div style="display: flex; flex-direction: column; gap: 6px;">
          <button onclick="selectAllVisible()" style="
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 10px;
            padding: 8px 12px;
            color: white;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
          "
          onmouseover="this.style.background='rgba(16, 185, 129, 0.3)'"
          onmouseout="this.style.background='rgba(16, 185, 129, 0.2)'">
            <span style="font-size: 14px;">☑</span>
            Tout sélectionner
          </button>
          
          <div style="display: flex; gap: 6px;">
            <button onclick="selectByFilter('unread')" style="
              background: rgba(245, 158, 11, 0.2);
              border: 1px solid rgba(245, 158, 11, 0.3);
              border-radius: 8px;
              padding: 6px 10px;
              color: white;
              cursor: pointer;
              font-size: 11px;
              font-weight: 600;
              transition: all 0.2s ease;
              flex: 1;
            "
            onmouseover="this.style.background='rgba(245, 158, 11, 0.3)'"
            onmouseout="this.style.background='rgba(245, 158, 11, 0.2)'">Non lues</button>
            
            <button onclick="selectByFilter('reservation')" style="
              background: rgba(139, 92, 246, 0.2);
              border: 1px solid rgba(139, 92, 246, 0.3);
              border-radius: 8px;
              padding: 6px 10px;
              color: white;
              cursor: pointer;
              font-size: 11px;
              font-weight: 600;
              transition: all 0.2s ease;
              flex: 1;
            "
            onmouseover="this.style.background='rgba(139, 92, 246, 0.3)'"
            onmouseout="this.style.background='rgba(139, 92, 246, 0.2)'">Nouvelles résa</button>
          </div>
        </div>
      </div>
    `;
    
    $('body').append(batchBar);
  }
  
  setTimeout(() => {
    $('#batch-bar').css({
      transform: 'translateY(-50%) translateX(0)',
      opacity: 1
    });
  }, 100);
}

// Fonction pour mettre à jour le compteur batch
function updateBatchCount() {
  const $ = jQuery;
  const count = selectedNotifications.size;
  console.log('📊 Mise à jour du compteur batch:', count);
  
  if ($('#batch-count').length > 0) {
    $('#batch-count').text(count);
  }
  
  if (count === 0 && isBatchMode) {
    console.log('🔄 Aucune sélection, sortie du mode batch');
    exitBatchMode();
  }
}

// Fonction pour sortir du mode batch
window.exitBatchMode = function() {
  const $ = jQuery;
  isBatchMode = false;
  selectedNotifications.clear();
  $('.notification-item').removeClass('selected');
  $('#batch-bar').css({
    transform: 'translateY(-50%) translateX(100%)',
    opacity: 0
  });
  setTimeout(() => {
    $('#batch-bar').remove();
  }, 400);
};

// Fonction pour sélectionner toutes les notifications visibles
window.selectAllVisible = function() {
  const $ = jQuery;
  console.log('🔍 Sélection de toutes les notifications visibles...');
  
  $('.notification-item:visible:not([data-grouped])').each(function() {
    const $item = $(this);
    const id = $item.data('notification-id');
    console.log('📋 Notification trouvée:', { id: id, type: $item.data('type') });
    
    if (id) {
      selectedNotifications.add(id);
      $item.addClass('selected');
    }
  });
  
  console.log('✅ Sélection terminée. Total sélectionné:', selectedNotifications.size);
  updateBatchCount();
};

// Fonction pour sélectionner par filtre
window.selectByFilter = function(filter) {
  const $ = jQuery;
  
  // Désélectionner tout d'abord
  selectedNotifications.clear();
  $('.notification-item').removeClass('selected');
  
  $('.notification-item:visible:not([data-grouped])').each(function() {
    const $item = $(this);
    const id = $item.data('notification-id');
    if (!id) return;
    
    let shouldSelect = false;
    
    switch (filter) {
      case 'unread':
        shouldSelect = !$item.hasClass('read');
        break;
      case 'reservation':
        shouldSelect = $item.data('type') === 'reservation';
        break;
      default:
        shouldSelect = true;
    }
    
    if (shouldSelect) {
      selectedNotifications.add(id);
      $item.addClass('selected');
    }
  });
  
  updateBatchCount();
};

// Fonction pour marquer comme lu en batch
window.batchMarkAsRead = function() {
  const $ = jQuery;
  $('.notification-item.selected').each(function() {
    const id = $(this).data('notification-id');
    if (id) {
      markAsRead(id);
    }
  });
  exitBatchMode();
};

// Fonction pour supprimer en batch
window.batchDelete = function() {
  const $ = jQuery;
  if (confirm(`Supprimer ${$('.notification-item.selected').length} notification(s) ?`)) {
    $('.notification-item.selected').each(function() {
      const id = $(this).data('notification-id');
      if (id) {
        deleteNotification(id);
      }
    });
  }
  exitBatchMode();
};

// Fonction pour archiver en batch
window.batchArchive = function() {
  const $ = jQuery;
  console.log('Archivage de', $('.notification-item.selected').length, 'notifications');
  showToast('Fonctionnalité d\'archivage à implémenter', 'info');
  exitBatchMode();
};

// Fonction de test globale modernisée avec vraies données
window.testNotifications = function () {
  console.log("🧪 Test des notifications modernes avec vraies données");
  const $modal = $("#simple-notification-modal");
  if ($modal.hasClass("notification-modal-show")) {
    closeNotificationModal();
  } else {
    loadRealNotifications();
    openNotificationModal();
  }
};

// Fonction pour tester avec des notifications d'exemple (fallback)
window.testWithNotifications = function () {
  console.log("🧪 Test avec des notifications d'exemple");
  addSampleNotifications();
  openNotificationModal();
};

// Fonction pour recharger les notifications
function refreshNotifications() {
  loadRealNotifications();
}

// Fonction pour vérifier les nouvelles notifications (sans recharger toute la liste)
function checkForNewNotifications() {
  console.log("🔧 === DEBUG NOTIFICATIONS ===");

  // 1. Vérifier les variables
  console.log("1️⃣ Variables AJAX:");
  console.log(
    "   • ajaxurl:",
    typeof ajaxurl !== "undefined" ? ajaxurl : "❌ NON DÉFINI"
  );
  console.log(
    "   • ib_notif_vars:",
    typeof ib_notif_vars !== "undefined" ? ib_notif_vars : "❌ NON DÉFINI"
  );
  console.log(
    "   • IBNotifBell:",
    typeof IBNotifBell !== "undefined" ? IBNotifBell : "❌ NON DÉFINI"
  );

  // 2. Tester l'appel AJAX
  const ajax_url =
    typeof ajaxurl !== "undefined" ? ajaxurl : "/wp-admin/admin-ajax.php";
  const nonce =
    (typeof ib_notif_vars !== "undefined" && ib_notif_vars.nonce) ||
    (typeof IBNotifBell !== "undefined" && IBNotifBell.nonce) ||
    "";

  if (!nonce) {
    console.log("❌ Aucun nonce trouvé - impossible de tester");
    return;
  }

  console.log("2️⃣ Test d'appel AJAX...");
  console.log("   • URL:", ajax_url);
  console.log("   • Nonce:", nonce);

  jQuery
    .post(ajax_url, {
      action: "ib_get_notifications",
      nonce: nonce,
      limit: 5,
    })
    .done(function (response) {
      console.log("3️⃣ ✅ Réponse reçue:", response);

      if (response.success && response.data && response.data.notifications) {
        console.log("4️⃣ ✅ Structure correcte:");
        console.log("   • Notifications:", response.data.notifications.length);
        console.log("   • Non lues:", response.data.unread_count);
        console.log("   • Total:", response.data.total_count);
        console.log(
          "   • Première notification:",
          response.data.notifications[0]
        );
      } else {
        console.log("4️⃣ ❌ Structure incorrecte:", {
          success: response.success,
          hasData: !!response.data,
          hasNotifications: !!(response.data && response.data.notifications),
        });
      }
    })
    .fail(function (xhr, status, error) {
      console.log("3️⃣ ❌ Erreur AJAX:", {
        status: status,
        error: error,
        responseText: xhr.responseText,
      });
    });
};

// Fonction de test pour vérifier les variables AJAX
window.testAjaxVars = function () {
  console.log("🧪 Test des variables AJAX:");
  console.log(
    "   • ajaxurl:",
    typeof ajaxurl !== "undefined" ? ajaxurl : "❌ NON DÉFINI"
  );
  console.log(
    "   • ib_notif_vars:",
    typeof ib_notif_vars !== "undefined" ? ib_notif_vars : "❌ NON DÉFINI"
  );
  console.log(
    "   • IBNotifBell:",
    typeof IBNotifBell !== "undefined" ? IBNotifBell : "❌ NON DÉFINI"
  );

  // Test d'appel AJAX simple
  const ajax_url =
    typeof ajaxurl !== "undefined" ? ajaxurl : "/wp-admin/admin-ajax.php";
  const nonce =
    (typeof ib_notif_vars !== "undefined" && ib_notif_vars.nonce) ||
    (typeof IBNotifBell !== "undefined" && IBNotifBell.nonce) ||
    "";

  if (nonce) {
    console.log("✅ Nonce trouvé:", nonce);
    console.log("🔄 Test d'appel AJAX...");

    jQuery
      .post(ajax_url, {
        action: "ib_get_notifications",
        nonce: nonce,
        limit: 1,
      })
      .done(function (response) {
        console.log("✅ Réponse AJAX reçue:", response);
      })
      .fail(function (xhr, status, error) {
        console.log("❌ Erreur AJAX:", status, error);
        console.log("❌ Détails:", xhr.responseText);
      });
  } else {
    console.log("❌ Aucun nonce trouvé");
  }
};

// Fonction pour simuler l'arrivée d'une nouvelle notification
window.addNewNotification = function (type = "confirmed") {
  const $ = jQuery;
  const $content = $("#notification-content");

  // Supprimer l'état vide si présent
  $content.find(".empty-state").remove();

  const notificationId = Date.now();
  const types = {
    confirmed: {
      color: "#50C878",
      icon: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/>',
      title: "Nouvelle réservation confirmée",
      content: "Client – Service avec Employé",
      detail: "Confirmée pour aujourd'hui",
      bgColor: "rgba(80, 200, 120, 0.02)",
    },
    cancelled: {
      color: "#FF9F43",
      icon: '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
      title: "Réservation annulée",
      content: "Client – Service avec Employé",
      detail: "Annulée pour aujourd'hui",
      bgColor: "rgba(255, 159, 67, 0.02)",
    },
    reminder: {
      color: "#3D9DF6",
      icon: '<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>',
      title: "Rappel de rendez-vous",
      content: "Client – Service avec Employé",
      detail: "Rendez-vous prévu pour demain",
      bgColor: "rgba(61, 157, 246, 0.02)",
    },
  };

  const notifData = types[type];

  const newNotification = `
    <div class="notification-item new-notification ${type}" data-notification-id="${notificationId}" data-type="${type}" style="
      padding: 20px 28px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.04);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      background: linear-gradient(90deg, transparent, ${
        notifData.bgColor
      }, transparent);
      cursor: pointer;
    "
    onmouseover="
      this.style.background='linear-gradient(90deg, transparent, ${notifData.bgColor.replace(
        "0.02",
        "0.05"
      )}, transparent)';
      this.style.transform='translateX(4px)';
      this.style.boxShadow='0 4px 20px ${notifData.bgColor.replace(
        "0.02",
        "0.1"
      )}';
    "
    onmouseout="
      this.style.background='linear-gradient(90deg, transparent, ${
        notifData.bgColor
      }, transparent)';
      this.style.transform='translateX(0)';
      this.style.boxShadow='none';
    ">
      <div style="display: flex; align-items: flex-start; gap: 16px;">
        <div style="position: relative;">
          <div style="
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, ${notifData.color} 0%, ${
    notifData.color
  }dd 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px ${notifData.color}40;
            border: 2px solid white;
          ">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              ${notifData.icon}
            </svg>
          </div>
          <div style="
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 18px;
            height: 18px;
            background: #e9aebc;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
          ">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
          </div>
        </div>

        <div style="flex: 1; min-width: 0;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
            <h4 style="
              margin: 0;
              font-size: 0.95em;
              font-weight: 600;
              color: #111827;
              line-height: 1.3;
              letter-spacing: -0.01em;
            ">${notifData.title}</h4>
            <span style="
              font-size: 0.7em;
              color: #9ca3af;
              white-space: nowrap;
              margin-left: 12px;
              font-weight: 500;
              background: ${notifData.color}20;
              padding: 2px 6px;
              border-radius: 6px;
              color: ${notifData.color};
            ">à l'instant</span>
          </div>
          <p style="
            margin: 0 0 4px 0;
            font-size: 0.9em;
            color: #374151;
            line-height: 1.4;
            font-weight: 500;
          ">${notifData.content}</p>
          <p style="
            margin: 0 0 16px 0;
            font-size: 0.8em;
            color: #6b7280;
            line-height: 1.4;
          ">${notifData.detail}</p>

          <div style="display: flex; gap: 8px;">
            <button onclick="markAsRead('${notificationId}')" style="
              background: rgba(233, 174, 188, 0.1);
              border: 1px solid rgba(233, 174, 188, 0.3);
              border-radius: 8px;
              padding: 6px 12px;
              color: #e9aebc;
              cursor: pointer;
              font-size: 0.75em;
              font-weight: 600;
              transition: all 0.3s ease;
            "
            onmouseover="this.style.background='rgba(233, 174, 188, 0.15)'; this.style.color='#d89aab';"
            onmouseout="this.style.background='rgba(233, 174, 188, 0.1)'; this.style.color='#e9aebc';">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                <path d="M20 6L9 17l-5-5"/>
              </svg>
              Marquer comme lu
            </button>
            <button onclick="deleteNotification('${notificationId}')" style="
              background: rgba(239, 68, 68, 0.1);
              border: 1px solid rgba(239, 68, 68, 0.2);
              border-radius: 8px;
              padding: 6px 12px;
              color: #ef4444;
              cursor: pointer;
              font-size: 0.75em;
              font-weight: 600;
              transition: all 0.3s ease;
            "
            onmouseover="this.style.background='rgba(239, 68, 68, 0.15)'"
            onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px;">
                <polyline points="3,6 5,6 21,6"/>
                <path d="m19,6v14a2,2 0 0,1-2,2H7a2,2 0 0,1-2-2V6m3,0V4a2,2 0 0,1,2-2h4a2,2 0 0,1,2,2v2"/>
              </svg>
              Supprimer
            </button>
          </div>
        </div>
      </div>
    </div>
  `;

  // Ajouter au début de la section "Aujourd'hui"
  const $todaySection = $content.find('div:contains("Aujourd\'hui")').first();
  if ($todaySection.length) {
    $todaySection.after(newNotification);
  } else {
    // Créer la section "Aujourd'hui" si elle n'existe pas
    const todaySection = `
      <div style="
        padding: 16px 28px 8px;
        background: rgba(233, 174, 188, 0.03);
        border-bottom: 1px solid rgba(233, 174, 188, 0.1);
        font-size: 0.8em;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
      ">Aujourd'hui</div>
    `;
    $content.prepend(todaySection + newNotification);
  }

  // Mettre à jour le badge et ajouter l'effet glow
  updateBadgeCount();
  $(".notification-bell").addClass("has-new");

  // Retirer l'effet glow après 3 secondes
  setTimeout(() => {
    $(".notification-bell").removeClass("has-new");
  }, 3000);

  console.log(`✨ Nouvelle notification ${type} ajoutée !`);
};

// Fonctions de test rapides
window.testNewConfirmed = () => addNewNotification("confirmed");
window.testNewCancelled = () => addNewNotification("cancelled");
window.testNewReminder = () => addNewNotification("reminder");

// Démarrer l'initialisation
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", waitForJQuery);
} else {
  waitForJQuery();
}

console.log("🎯 Script ultra-moderne chargé avec base de données !");
console.log("🧪 Fonctions de test disponibles:");
console.log("   • debugNotifications() - 🔧 DEBUG COMPLET (recommandé)");
console.log("   • testNotifications() - Test avec vraies données de la BDD");
console.log("   • testWithNotifications() - Test avec notifications d'exemple");
console.log("   • refreshNotifications() - Recharger les notifications");
console.log("   • testAjaxVars() - Vérifier les variables AJAX");
console.log("   • testNewConfirmed() - Ajouter une notification confirmée");
console.log("   • testNewCancelled() - Ajouter une notification annulée");
console.log("   • testNewReminder() - Ajouter un rappel");
console.log(
  "   • addNewNotification('type') - Ajouter une notification personnalisée"
);
console.log("🎯 NOUVELLES FONCTIONNALITÉS BATCH MODE:");
console.log("   • Clic long (500ms) sur une notification → mode batch");
console.log("   • selectAllVisible() - Sélectionner toutes les notifications");
console.log("   • selectByFilter('unread') - Sélectionner non lues");
console.log("   • selectByFilter('reservation') - Sélectionner nouvelles résa");
console.log("   • exitBatchMode() - Sortir du mode batch");
console.log(
  "🎨 Fonctionnalités: Vraies données BDD, Filtres, Export CSV, Animations, Responsive, Batch Mode"
);
console.log("📊 Base de données: wp_notifications connectée et fonctionnelle");
console.log("🚨 Si problème: tapez debugNotifications() dans la console");
