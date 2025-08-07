/**
 * 🎨 SYSTÈME DE NOTIFICATIONS MODERNE & MINIMALISTE
 * ================================================================
 * JavaScript pour le panneau de notifications refondu
 * Inspiré de Planity/Fresha avec interactions modernes
 * Version: 3.0.0 - Refonte complète
 */

(function($) {
    'use strict';

    // Configuration globale
    const NotificationRefonte = {
        isInitialized: false,
        isSelectionMode: false,
        selectedNotifications: new Set(),
        currentTab: 'all',
        searchQuery: '',
        cache: {
            notifications: [],
            unreadCount: 0,
            lastUpdate: null
        },
        settings: {
            autoRefresh: true,
            refreshInterval: 30000,
            animationDuration: 300,
            maxNotifications: 50
        }
    };

    // Icônes SVG modernes
    const Icons = {
        calendar: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>`,
        check: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <polyline points="20,6 9,17 4,12"/>
        </svg>`,
        x: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>`,
        mail: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="m4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2h-16c-1.1 0-2-.9-2-2v-12c0-1.1.9-2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
        </svg>`,
        user: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
        </svg>`,
        bell: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
            <path d="m13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>`,
        search: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
        </svg>`,
        chevronDown: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <polyline points="6,9 12,15 18,9"/>
        </svg>`,
        trash: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <polyline points="3,6 5,6 21,6"/>
            <path d="m19 6v14a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2v-14m3 0v-2a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
        </svg>`,
        archive: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <polyline points="21,8 21,21 3,21 3,8"/>
            <rect x="1" y="3" width="22" height="5"/>
            <line x1="10" y1="12" x2="14" y2="12"/>
        </svg>`
    };

    /**
     * 🚀 INITIALISATION PRINCIPALE
     */
    function init() {
        if (NotificationRefonte.isInitialized) {
            return;
        }

        console.log('🎨 Initialisation du système de notifications moderne...');

        // Vérifier les dépendances
        if (!checkDependencies()) {
            console.error('❌ Dépendances manquantes');
            return;
        }

        // Créer l'interface
        createModernInterface();

        // Initialiser les événements
        bindEvents();

        // Charger les notifications
        loadNotifications();

        // Démarrer l'auto-refresh
        if (NotificationRefonte.settings.autoRefresh) {
            startAutoRefresh();
        }

        NotificationRefonte.isInitialized = true;
        console.log('✅ Système de notifications moderne initialisé');

        // Déclencher l'événement d'initialisation
        $(document).trigger('notificationRefonte:ready', NotificationRefonte);
    }

    /**
     * 🔍 VÉRIFICATION DES DÉPENDANCES
     */
    function checkDependencies() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery requis');
            return false;
        }

        if (typeof ib_notif_vars === 'undefined') {
            console.error('Variables AJAX manquantes');
            return false;
        }

        return true;
    }

    /**
     * 🎨 CRÉATION DE L'INTERFACE MODERNE
     */
    function createModernInterface() {
        // Remplacer l'ancien panneau par le nouveau
        const $oldPanel = $('#ib-notif-panel, .ib-notif-panel');
        if ($oldPanel.length) {
            $oldPanel.replaceWith(createPanelHTML());
        } else {
            $('body').append(createPanelHTML());
        }

        // Mettre à jour la cloche
        updateBellInterface();
    }

    /**
     * 📱 CRÉATION DU HTML DU PANNEAU
     */
    function createPanelHTML() {
        return `
            <div class="ib-notif-refonte">
                <div class="ib-notif-panel-refonte" id="ib-notif-panel-refonte">
                    <!-- Header avec onglets -->
                    <div class="ib-notif-header-refonte">
                        <div class="ib-notif-header-top">
                            <h2 class="ib-notif-title-refonte">
                                ${Icons.bell}
                                Notifications
                            </h2>
                            <button class="ib-notif-close-refonte" id="ib-notif-close">
                                ${Icons.x}
                            </button>
                        </div>

                        <!-- Onglets -->
                        <div class="ib-notif-tabs-refonte">
                            <button class="ib-notif-tab-refonte active" data-tab="all">
                                📆 Toutes <span class="tab-count">0</span>
                            </button>
                            <button class="ib-notif-tab-refonte" data-tab="bookings">
                                📅 Réservations <span class="tab-count">0</span>
                            </button>
                            <button class="ib-notif-tab-refonte" data-tab="emails">
                                📩 Emails <span class="tab-count">0</span>
                            </button>
                            <button class="ib-notif-tab-refonte" data-tab="archived">
                                📂 Archivées <span class="tab-count">0</span>
                            </button>
                        </div>

                        <!-- Barre de recherche -->
                        <div class="ib-notif-search-refonte">
                            <div class="ib-notif-search-icon">${Icons.search}</div>
                            <input type="text" 
                                   class="ib-notif-search-input-refonte" 
                                   placeholder="Rechercher un client, service..."
                                   id="ib-notif-search-input">
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="ib-notif-content-refonte" id="ib-notif-content">
                        <div class="ib-notif-loading" id="ib-notif-loading">
                            <div class="ib-notif-spinner"></div>
                        </div>
                    </div>
                </div>

                <!-- Overlay -->
                <div class="ib-notif-overlay-refonte" id="ib-notif-overlay"></div>

                <!-- Barre de sélection multiple -->
                <div class="ib-notif-selection-bar" id="ib-notif-selection-bar">
                    <div class="ib-notif-selection-count">
                        <span id="ib-notif-selection-count">0</span> sélectionnée(s)
                    </div>
                    <div class="ib-notif-selection-actions">
                        <button class="ib-notif-selection-btn" id="ib-notif-mark-read">
                            ${Icons.check} Marquer comme lu
                        </button>
                        <button class="ib-notif-selection-btn" id="ib-notif-archive">
                            ${Icons.archive} Archiver
                        </button>
                        <button class="ib-notif-selection-btn" id="ib-notif-delete">
                            ${Icons.trash} Supprimer
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * 🔔 MISE À JOUR DE L'INTERFACE DE LA CLOCHE
     */
    function updateBellInterface() {
        const $bell = $('#ib-notif-bell, .ib-notif-bell');
        if ($bell.length) {
            $bell.off('click').on('click', togglePanel);
        }
    }

    /**
     * 🎯 LIAISON DES ÉVÉNEMENTS
     */
    function bindEvents() {
        const $panel = $('#ib-notif-panel-refonte');

        // Fermeture du panneau
        $('#ib-notif-close, #ib-notif-overlay').on('click', closePanel);

        // Onglets
        $('.ib-notif-tab-refonte').on('click', handleTabClick);

        // Recherche
        $('#ib-notif-search-input').on('input', debounce(handleSearch, 300));

        // Sélection multiple
        $(document).on('change', '.ib-notif-checkbox input', handleCheckboxChange);
        $(document).on('click', '.ib-notif-card-refonte', handleCardClick);

        // Actions de sélection
        $('#ib-notif-mark-read').on('click', markSelectedAsRead);
        $('#ib-notif-archive').on('click', archiveSelected);
        $('#ib-notif-delete').on('click', deleteSelected);

        // Échap pour fermer
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $panel.hasClass('is-open')) {
                closePanel();
            }
        });
    }

    /**
     * 📥 CHARGEMENT DES NOTIFICATIONS
     */
    function loadNotifications() {
        showLoading();

        $.ajax({
            url: ib_notif_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'ib_get_notifications',
                nonce: ib_notif_vars.nonce,
                limit: NotificationRefonte.settings.maxNotifications,
                tab: NotificationRefonte.currentTab,
                search: NotificationRefonte.searchQuery
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    updateNotificationsList(response.data);
                    updateTabCounts(response.data);
                } else {
                    showError('Erreur lors du chargement des notifications');
                }
            },
            error: function() {
                hideLoading();
                showError('Erreur de connexion');
            }
        });
    }

    // Utilitaire debounce
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Initialisation au chargement du DOM
    $(document).ready(function() {
        // Attendre un peu pour s'assurer que tout est chargé
        setTimeout(init, 100);
    });

    /**
     * 🔄 OUVERTURE/FERMETURE DU PANNEAU
     */
    function togglePanel() {
        const $panel = $('#ib-notif-panel-refonte');
        if ($panel.hasClass('is-open')) {
            closePanel();
        } else {
            openPanel();
        }
    }

    function openPanel() {
        const $panel = $('#ib-notif-panel-refonte');
        const $overlay = $('#ib-notif-overlay');

        $panel.addClass('is-open animate-in');
        $overlay.show();

        // Charger les notifications si nécessaire
        if (!NotificationRefonte.cache.lastUpdate ||
            Date.now() - NotificationRefonte.cache.lastUpdate > 60000) {
            loadNotifications();
        }

        // Focus sur la recherche
        setTimeout(() => {
            $('#ib-notif-search-input').focus();
        }, 300);
    }

    function closePanel() {
        const $panel = $('#ib-notif-panel-refonte');
        const $overlay = $('#ib-notif-overlay');

        $panel.removeClass('is-open').addClass('animate-out');
        $overlay.hide();

        // Sortir du mode sélection
        if (NotificationRefonte.isSelectionMode) {
            exitSelectionMode();
        }

        setTimeout(() => {
            $panel.removeClass('animate-out');
        }, 300);
    }

    /**
     * 📑 GESTION DES ONGLETS
     */
    function handleTabClick(e) {
        const $tab = $(e.currentTarget);
        const tab = $tab.data('tab');

        if (tab === NotificationRefonte.currentTab) return;

        // Mettre à jour l'interface
        $('.ib-notif-tab-refonte').removeClass('active');
        $tab.addClass('active');

        // Mettre à jour l'état
        NotificationRefonte.currentTab = tab;

        // Recharger les notifications
        loadNotifications();
    }

    /**
     * 🔍 GESTION DE LA RECHERCHE
     */
    function handleSearch(e) {
        const query = $(e.target).val().trim();
        NotificationRefonte.searchQuery = query;
        loadNotifications();
    }

    /**
     * ✅ GESTION DE LA SÉLECTION MULTIPLE
     */
    function handleCheckboxChange(e) {
        const $checkbox = $(e.target);
        const notificationId = $checkbox.val();

        if ($checkbox.is(':checked')) {
            NotificationRefonte.selectedNotifications.add(notificationId);
        } else {
            NotificationRefonte.selectedNotifications.delete(notificationId);
        }

        updateSelectionUI();
    }

    function handleCardClick(e) {
        // Vérifier si c'est un clic long pour activer la sélection
        if (e.type === 'contextmenu' || e.ctrlKey || e.metaKey) {
            e.preventDefault();
            toggleSelectionMode();
            return;
        }

        // Clic normal - ouvrir la notification
        const $card = $(e.currentTarget);
        const notificationId = $card.data('notification-id');
        const link = $card.data('link');

        if (link) {
            window.open(link, '_blank');
        }

        // Marquer comme lue
        markAsRead(notificationId);
    }

    function toggleSelectionMode() {
        const $panel = $('#ib-notif-panel-refonte');

        if (NotificationRefonte.isSelectionMode) {
            exitSelectionMode();
        } else {
            enterSelectionMode();
        }
    }

    function enterSelectionMode() {
        const $panel = $('#ib-notif-panel-refonte');

        NotificationRefonte.isSelectionMode = true;
        $panel.addClass('selection-mode');

        showToast('Mode sélection activé', 'info');
    }

    function exitSelectionMode() {
        const $panel = $('#ib-notif-panel-refonte');
        const $selectionBar = $('#ib-notif-selection-bar');

        NotificationRefonte.isSelectionMode = false;
        NotificationRefonte.selectedNotifications.clear();

        $panel.removeClass('selection-mode');
        $selectionBar.removeClass('show');

        // Décocher toutes les cases
        $('.ib-notif-checkbox input').prop('checked', false);
    }

    function updateSelectionUI() {
        const count = NotificationRefonte.selectedNotifications.size;
        const $selectionBar = $('#ib-notif-selection-bar');
        const $countElement = $('#ib-notif-selection-count');

        $countElement.text(count);

        if (count > 0) {
            $selectionBar.addClass('show');
        } else {
            $selectionBar.removeClass('show');
        }
    }

    /**
     * 📝 MISE À JOUR DE LA LISTE DES NOTIFICATIONS
     */
    function updateNotificationsList(data) {
        const $content = $('#ib-notif-content');
        const notifications = data.recent || [];

        NotificationRefonte.cache.notifications = notifications;
        NotificationRefonte.cache.unreadCount = data.unread_count || 0;
        NotificationRefonte.cache.lastUpdate = Date.now();

        if (notifications.length === 0) {
            $content.html(createEmptyState());
        } else {
            const groupedNotifications = groupNotifications(notifications);
            $content.html(createNotificationsList(groupedNotifications));
        }

        // Mettre à jour le badge de la cloche
        updateBellBadge(data.unread_count || 0);
    }

    /**
     * 🎨 CRÉATION DES ÉLÉMENTS HTML
     */
    function createEmptyState() {
        return `
            <div class="ib-notif-empty-refonte">
                <div class="ib-notif-empty-icon">
                    ${Icons.bell}
                </div>
                <h3 class="ib-notif-empty-title">Aucune notification</h3>
                <p class="ib-notif-empty-text">
                    Vous êtes à jour ! Les nouvelles notifications apparaîtront ici.
                </p>
            </div>
        `;
    }

    function createNotificationsList(groupedNotifications) {
        let html = '';

        for (const [groupKey, notifications] of Object.entries(groupedNotifications)) {
            if (groupKey === 'single') {
                // Notifications individuelles
                notifications.forEach(notification => {
                    html += createNotificationCard(notification);
                });
            } else {
                // Groupe de notifications
                html += createNotificationGroup(groupKey, notifications);
            }
        }

        return html;
    }

    function createNotificationCard(notification) {
        const isUnread = notification.status === 'unread';
        const typeClass = getNotificationTypeClass(notification.type);
        const icon = getNotificationIcon(notification.type);
        const badge = getNotificationBadge(notification);
        const timeAgo = formatTimeAgo(notification.created_at);

        return `
            <div class="ib-notif-card-refonte ${isUnread ? 'is-unread' : ''}"
                 data-notification-id="${notification.id}"
                 data-type="${notification.type}"
                 data-link="${notification.link || ''}"
                 data-client="${notification.client_name || ''}"
                 data-service="${notification.service_name || ''}">

                <!-- Checkbox de sélection -->
                <div class="ib-notif-card-checkbox">
                    <label class="ib-notif-checkbox">
                        <input type="checkbox" value="${notification.id}">
                        <span class="ib-notif-checkbox-mark"></span>
                    </label>
                </div>

                <!-- Badge de statut -->
                ${badge}

                <!-- Header avec icône -->
                <div class="ib-notif-card-header">
                    <div class="ib-notif-card-icon ${typeClass}">
                        ${icon}
                    </div>
                    <div class="ib-notif-card-meta">
                        <div class="ib-notif-card-client">
                            ${notification.client_name || 'Client'}
                        </div>
                        <div class="ib-notif-card-action">
                            ${formatNotificationMessage(notification)}
                        </div>
                    </div>
                </div>

                <!-- Footer avec date et actions -->
                <div class="ib-notif-card-footer">
                    <div class="ib-notif-card-date">${timeAgo}</div>
                    <div class="ib-notif-card-actions">
                        <button class="ib-notif-card-action-btn" title="Marquer comme lu" onclick="markAsRead('${notification.id}')">
                            ${Icons.check}
                        </button>
                        <button class="ib-notif-card-action-btn" title="Supprimer" onclick="deleteNotification('${notification.id}')">
                            ${Icons.trash}
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function createNotificationGroup(groupKey, notifications) {
        const count = notifications.length;
        const groupTitle = getGroupTitle(groupKey, count);
        const isExpanded = false; // Par défaut fermé

        let groupContent = '';
        notifications.forEach(notification => {
            groupContent += createNotificationCard(notification);
        });

        return `
            <div class="ib-notif-group-refonte ${isExpanded ? 'expanded' : ''}" data-group="${groupKey}">
                <div class="ib-notif-group-header" onclick="toggleGroup('${groupKey}')">
                    <div class="ib-notif-group-title">
                        ${Icons.mail}
                        ${groupTitle}
                        <span class="ib-notif-group-count">${count}</span>
                    </div>
                    <div class="ib-notif-group-toggle">
                        ${Icons.chevronDown}
                    </div>
                </div>
                <div class="ib-notif-group-content">
                    ${groupContent}
                </div>
            </div>
        `;
    }

    /**
     * 🎯 FONCTIONS UTILITAIRES
     */
    function getNotificationTypeClass(type) {
        const typeMap = {
            'booking_new': 'type-booking',
            'booking_confirmed': 'type-booking',
            'booking_cancelled': 'type-cancellation',
            'email': 'type-email',
            'reminder': 'type-reminder'
        };
        return typeMap[type] || 'type-booking';
    }

    function getNotificationIcon(type) {
        const iconMap = {
            'booking_new': Icons.calendar,
            'booking_confirmed': Icons.check,
            'booking_cancelled': Icons.x,
            'email': Icons.mail,
            'reminder': Icons.bell
        };
        return iconMap[type] || Icons.calendar;
    }

    function getNotificationBadge(notification) {
        if (notification.status === 'unread') {
            return '<div class="ib-notif-card-badge status-new">Nouveau</div>';
        }

        const badgeMap = {
            'booking_confirmed': '<div class="ib-notif-card-badge status-confirmed">Confirmé</div>',
            'booking_cancelled': '<div class="ib-notif-card-badge status-cancelled">Annulé</div>'
        };

        return badgeMap[notification.type] || '';
    }

    function formatNotificationMessage(notification) {
        const service = notification.service_name ? `<span class="ib-notif-card-service">${notification.service_name}</span>` : '';

        const messageMap = {
            'booking_new': `a réservé ${service}`,
            'booking_confirmed': `réservation ${service} confirmée`,
            'booking_cancelled': `a annulé ${service}`,
            'email': 'email envoyé',
            'reminder': `rappel pour ${service}`
        };

        return messageMap[notification.type] || notification.message;
    }

    function formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'À l\'instant';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h`;
        if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}j`;

        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'short'
        });
    }

    function groupNotifications(notifications) {
        const groups = {
            single: []
        };

        const emailNotifications = notifications.filter(n => n.type === 'email');

        if (emailNotifications.length > 3) {
            groups.emails = emailNotifications;
        } else {
            groups.single.push(...emailNotifications);
        }

        // Ajouter les autres notifications individuellement
        const otherNotifications = notifications.filter(n => n.type !== 'email');
        groups.single.push(...otherNotifications);

        return groups;
    }

    function getGroupTitle(groupKey, count) {
        const titleMap = {
            'emails': `${count} emails envoyés aujourd'hui`
        };
        return titleMap[groupKey] || `${count} notifications`;
    }

    /**
     * ⚡ ACTIONS SUR LES NOTIFICATIONS
     */
    function markAsRead(notificationId) {
        $.ajax({
            url: ib_notif_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'ib_mark_notification_read',
                nonce: ib_notif_vars.nonce,
                notification_id: notificationId
            },
            success: function(response) {
                if (response.success) {
                    // Mettre à jour l'interface
                    const $card = $(`.ib-notif-card-refonte[data-notification-id="${notificationId}"]`);
                    $card.removeClass('is-unread');

                    // Mettre à jour le cache
                    const notification = NotificationRefonte.cache.notifications.find(n => n.id == notificationId);
                    if (notification) {
                        notification.status = 'read';
                    }

                    updateBellBadge(NotificationRefonte.cache.unreadCount - 1);
                }
            }
        });
    }

    function deleteNotification(notificationId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
            return;
        }

        $.ajax({
            url: ib_notif_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'ib_delete_notification',
                nonce: ib_notif_vars.nonce,
                notification_id: notificationId
            },
            success: function(response) {
                if (response.success) {
                    // Animation de suppression
                    const $card = $(`.ib-notif-card-refonte[data-notification-id="${notificationId}"]`);
                    $card.addClass('animate-out');

                    setTimeout(() => {
                        $card.remove();

                        // Vérifier s'il faut afficher l'état vide
                        if ($('.ib-notif-card-refonte').length === 0) {
                            $('#ib-notif-content').html(createEmptyState());
                        }
                    }, 300);

                    showToast('Notification supprimée', 'success');
                }
            }
        });
    }

    function markSelectedAsRead() {
        const selectedIds = Array.from(NotificationRefonte.selectedNotifications);

        if (selectedIds.length === 0) return;

        $.ajax({
            url: ib_notif_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'ib_mark_notifications_read',
                nonce: ib_notif_vars.nonce,
                notification_ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    selectedIds.forEach(id => {
                        const $card = $(`.ib-notif-card-refonte[data-notification-id="${id}"]`);
                        $card.removeClass('is-unread');
                    });

                    exitSelectionMode();
                    showToast(`${selectedIds.length} notification(s) marquée(s) comme lue(s)`, 'success');
                }
            }
        });
    }

    function archiveSelected() {
        const selectedIds = Array.from(NotificationRefonte.selectedNotifications);

        if (selectedIds.length === 0) return;

        $.ajax({
            url: ib_notif_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'ib_archive_notifications',
                nonce: ib_notif_vars.nonce,
                notification_ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    selectedIds.forEach(id => {
                        const $card = $(`.ib-notif-card-refonte[data-notification-id="${id}"]`);
                        $card.addClass('animate-out');
                        setTimeout(() => $card.remove(), 300);
                    });

                    exitSelectionMode();
                    showToast(`${selectedIds.length} notification(s) archivée(s)`, 'success');
                }
            }
        });
    }

    function deleteSelected() {
        const selectedIds = Array.from(NotificationRefonte.selectedNotifications);

        if (selectedIds.length === 0) return;

        if (!confirm(`Êtes-vous sûr de vouloir supprimer ${selectedIds.length} notification(s) ?`)) {
            return;
        }

        $.ajax({
            url: ib_notif_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'ib_delete_notifications',
                nonce: ib_notif_vars.nonce,
                notification_ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    selectedIds.forEach(id => {
                        const $card = $(`.ib-notif-card-refonte[data-notification-id="${id}"]`);
                        $card.addClass('animate-out');
                        setTimeout(() => $card.remove(), 300);
                    });

                    exitSelectionMode();
                    showToast(`${selectedIds.length} notification(s) supprimée(s)`, 'success');
                }
            }
        });
    }

    /**
     * 🎨 FONCTIONS D'INTERFACE
     */
    function showLoading() {
        $('#ib-notif-loading').show();
    }

    function hideLoading() {
        $('#ib-notif-loading').hide();
    }

    function showError(message) {
        showToast(message, 'error');
    }

    function showToast(message, type = 'info') {
        const toastHtml = `
            <div class="ib-notif-toast-refonte ${type}">
                <div class="ib-notif-toast-content">
                    <div class="ib-notif-toast-message">${message}</div>
                </div>
                <button class="ib-notif-toast-close">
                    ${Icons.x}
                </button>
            </div>
        `;

        const $toast = $(toastHtml);
        $('body').append($toast);

        setTimeout(() => $toast.addClass('show'), 100);

        // Auto-hide après 3 secondes
        setTimeout(() => {
            $toast.removeClass('show');
            setTimeout(() => $toast.remove(), 300);
        }, 3000);

        // Fermeture manuelle
        $toast.find('.ib-notif-toast-close').on('click', () => {
            $toast.removeClass('show');
            setTimeout(() => $toast.remove(), 300);
        });
    }

    function updateBellBadge(count) {
        const $badge = $('#ib-notif-badge, .ib-notif-badge');

        if (count > 0) {
            $badge.text(count > 99 ? '99+' : count).show();
        } else {
            $badge.hide();
        }

        NotificationRefonte.cache.unreadCount = count;
    }

    function updateTabCounts(data) {
        // Mettre à jour les compteurs des onglets
        $('.ib-notif-tab-refonte[data-tab="all"] .tab-count').text(data.total_count || 0);
        $('.ib-notif-tab-refonte[data-tab="bookings"] .tab-count').text(data.bookings_count || 0);
        $('.ib-notif-tab-refonte[data-tab="emails"] .tab-count').text(data.emails_count || 0);
        $('.ib-notif-tab-refonte[data-tab="archived"] .tab-count').text(data.archived_count || 0);
    }

    function toggleGroup(groupKey) {
        const $group = $(`.ib-notif-group-refonte[data-group="${groupKey}"]`);
        $group.toggleClass('expanded');
    }

    function startAutoRefresh() {
        setInterval(() => {
            if ($('#ib-notif-panel-refonte').hasClass('is-open')) {
                loadNotifications();
            }
        }, NotificationRefonte.settings.refreshInterval);
    }

    // Fonctions globales pour les événements inline
    window.markAsRead = markAsRead;
    window.deleteNotification = deleteNotification;
    window.toggleGroup = toggleGroup;

    // Exposer l'API publique
    window.NotificationRefonte = NotificationRefonte;

})(jQuery);
