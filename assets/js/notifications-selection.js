/**
 * Gestion de la sélection multiple des notifications
 * Version: 1.0.0
 */

class NotificationSelection {
  constructor() {
    this.selectedNotifications = new Set();
    this.selectionMode = false;
    this.actionsElement = null;
    this.selectionCountElement = null;
    
    this.init();
  }
  
  init() {
    this.createActionsBar();
    this.addEventListeners();
  }
  
  createActionsBar() {
    // Créer la barre d'actions flottante avec attributs ARIA
    const actionsBar = document.createElement('div');
    actionsBar.className = 'ib-notif-selection-actions';
    actionsBar.setAttribute('role', 'toolbar');
    actionsBar.setAttribute('aria-label', 'Actions de sélection multiple');
    actionsBar.setAttribute('aria-live', 'polite');
    actionsBar.setAttribute('aria-atomic', 'true');
    actionsBar.innerHTML = `
      <div class="ib-notif-selection-count" role="status" aria-live="polite">0 notification sélectionnée</div>
      <button type="button" class="ib-notif-action-btn primary" data-action="mark-read" aria-label="Marquer les notifications sélectionnées comme lues">
        <span class="dashicons dashicons-yes" aria-hidden="true"></span>
        <span class="text">Marquer comme lu</span>
      </button>
      <button type="button" class="ib-notif-action-btn danger" data-action="delete" aria-label="Supprimer les notifications sélectionnées">
        <span class="dashicons dashicons-trash" aria-hidden="true"></span>
        <span class="text">Supprimer</span>
      </button>
      <button type="button" class="ib-notif-action-btn danger" data-action="delete-all" aria-label="Tout supprimer">
        <span class="dashicons dashicons-trash" aria-hidden="true"></span>
        <span class="text">Tout supprimer</span>
      </button>
      <button type="button" class="ib-notif-action-btn" data-action="cancel" aria-label="Annuler la sélection">
        <span class="dashicons dashicons-no" aria-hidden="true"></span>
        <span class="text">Annuler</span>
      </button>
    `;
    
    document.body.appendChild(actionsBar);
    this.actionsElement = actionsBar;
    this.selectionCountElement = actionsBar.querySelector('.ib-notif-selection-count');
  }
  
  addEventListeners() {
    // Gestionnaire d'événements pour la navigation clavier
    const handleKeyDown = (e) => {
      if (!this.selectionMode) return;
      
      const { key, target } = e;
      const card = target.closest('.ib-notif-card');
      
      if (!card) return;
      
      switch (key) {
        case ' ':
        case 'Enter':
          e.preventDefault();
          this.toggleNotificationSelection(card);
          break;
        case 'Escape':
          this.exitSelectionMode();
          break;
        case 'ArrowUp':
        case 'ArrowDown':
          e.preventDefault();
          const direction = key === 'ArrowUp' ? 'previousElementSibling' : 'nextElementSibling';
          const nextCard = card[direction];
          if (nextCard && nextCard.matches('.ib-notif-card')) {
            nextCard.focus();
          }
          break;
      }
    };
    
    // Délégation d'événements pour les clics sur les notifications
    document.addEventListener('click', (e) => {
      const card = e.target.closest('.ib-notif-card');
      const actionBtn = e.target.closest('[data-action]');
      
      // Gestion du clic sur une notification en mode sélection
      if (this.selectionMode && card) {
        e.preventDefault();
        e.stopPropagation();
        this.toggleNotificationSelection(card);
        return;
      }
      
      // Gestion des boutons d'action
      if (actionBtn) {
        e.preventDefault();
        const action = actionBtn.getAttribute('data-action');
        this.handleAction(action);
      }
    });
    
    // Gestion du clic long pour activer le mode sélection
    let longPressTimer;
    document.addEventListener('mousedown', (e) => {
      const card = e.target.closest('.ib-notif-card');
      if (!card || this.selectionMode) return;
      
      longPressTimer = setTimeout(() => {
        this.enterSelectionMode(card);
      }, 800); // Délai de 800ms pour le clic long
    });
    
    document.addEventListener('mouseup', () => {
      clearTimeout(longPressTimer);
    });
    
    document.addEventListener('touchstart', (e) => {
      const card = e.target.closest('.ib-notif-card');
      if (!card || this.selectionMode) return;
      
      longPressTimer = setTimeout(() => {
        this.enterSelectionMode(card);
      }, 800);
    }, { passive: true });
    
    document.addEventListener('touchend', () => {
      clearTimeout(longPressTimer);
    }, { passive: true });
    
    // Gestionnaire pour la navigation clavier
    document.addEventListener('keydown', handleKeyDown);
    
    // Annuler la sélection en cliquant en dehors
    document.addEventListener('click', (e) => {
      if (this.selectionMode && !e.target.closest('.ib-notif-card') && !e.target.closest('.ib-notif-selection-actions')) {
        this.exitSelectionMode();
      }
    });
  }
  
  enterSelectionMode(firstCard = null) {
    this.selectionMode = true;
    document.body.classList.add('ib-notif-selection-mode');
    this.actionsElement.classList.add('visible');
    
    // Ajouter un attribut aria-selected à toutes les notifications
    document.querySelectorAll('.ib-notif-card').forEach(card => {
      card.setAttribute('aria-selected', 'false');
      card.setAttribute('tabindex', '0');
      card.setAttribute('role', 'option');
    });
    
    // Ajouter la classe selectable à toutes les notifications
    document.querySelectorAll('.ib-notif-card').forEach(card => {
      card.classList.add('selectable');
      
      // Créer et ajouter la checkbox si elle n'existe pas
      if (!card.querySelector('.ib-notif-checkbox-label')) {
        const checkboxId = `notif-checkbox-${card.dataset.id || Date.now()}`;
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = checkboxId;
        checkbox.className = 'ib-notif-checkbox';
        
        const label = document.createElement('label');
        label.htmlFor = checkboxId;
        label.className = 'ib-notif-checkbox-label';
        
        card.appendChild(checkbox);
        card.appendChild(label);
      }
    });
    
    // Sélectionner la première notification si c'est un clic long
    if (firstCard) {
      this.toggleNotificationSelection(firstCard);
    }
  }
  
  exitSelectionMode() {
    this.selectionMode = false;
    this.selectedNotifications.clear();
    this.updateSelectionCount();
    this.actionsElement.classList.remove('visible');
    document.body.classList.remove('ib-notif-selection-mode');
    
    // Nettoyer les classes et les checkboxes
    document.querySelectorAll('.ib-notif-card').forEach(card => {
      card.classList.remove('selectable', 'selected');
      const checkbox = card.querySelector('.ib-notif-checkbox');
      const label = card.querySelector('.ib-notif-checkbox-label');
      if (checkbox) checkbox.remove();
      if (label) label.remove();
    });
    
    // Masquer la barre d'actions
    if (this.actionsElement) {
      this.actionsElement.classList.remove('visible');
    }
    
    // Rétablir les événements de clic normaux
    document.body.style.pointerEvents = '';
    
    // Donner le focus à un élément sécurisé
    document.body.focus();
  }
  
  toggleNotificationSelection(card) {
    const notificationId = card.dataset.id || card.id;
    const isSelected = this.selectedNotifications.has(notificationId);
    
    if (isSelected) {
      this.selectedNotifications.delete(notificationId);
      card.classList.remove('selected');
      card.setAttribute('aria-selected', 'false');
      const checkbox = card.querySelector('.ib-notif-checkbox');
      if (checkbox) checkbox.checked = false;
    } else {
      this.selectedNotifications.add(notificationId);
      card.classList.add('selected');
      card.setAttribute('aria-selected', 'true');
      const checkbox = card.querySelector('.ib-notif-checkbox');
      if (checkbox) checkbox.checked = true;
    }
    
    // Mettre à jour l'état ARIA pour les lecteurs d'écran
    const count = this.selectedNotifications.size;
    const countText = count === 1 ? '1 notification sélectionnée' : `${count} notifications sélectionnées`;
    this.actionsElement.setAttribute('aria-label', `Actions de sélection multiple - ${countText}`);
    
    this.updateSelectionCount();
    
    // Si plus de sélection, sortir du mode sélection
    if (this.selectedNotifications.size === 0) {
      this.exitSelectionMode();
    }
  }
  
  updateSelectionCount() {
    const count = this.selectedNotifications.size;
    if (this.selectionCountElement) {
      const countText = count === 1 ? '1 notification sélectionnée' : `${count} notifications sélectionnées`;
      this.selectionCountElement.textContent = countText;
      this.selectionCountElement.setAttribute('aria-label', countText);
    }
  }
  
  async handleAction(action) {
    const notificationIds = Array.from(this.selectedNotifications);
    
    if (notificationIds.length === 0 && action !== 'delete-all') {
      // Donner un retour visuel si aucune notification n'est sélectionnée
      const actionsBar = document.querySelector('.ib-notif-selection-actions');
      if (actionsBar) {
        actionsBar.classList.add('shake');
        setTimeout(() => actionsBar.classList.remove('shake'), 500);
      }
      return;
    }
    
    try {
      switch (action) {
        case 'mark-read':
          await this.markAsRead(notificationIds);
          break;
          
        case 'delete':
          if (confirm(`Voulez-vous vraiment supprimer ${notificationIds.length} notification(s) ?`)) {
            await this.deleteNotifications(notificationIds);
          }
          break;
          
        case 'delete-all':
          await this.deleteAllNotifications();
          break;
          
        case 'cancel':
          this.exitSelectionMode();
          break;
      }
    } catch (error) {
      console.error(`Erreur lors de l'action ${action}:`, error);
      this.showNotice(`Une erreur est survenue lors de l'exécution de l'action.`, 'error');
    }
  }
  
  async markAsRead(notificationIds) {
    try {
      const response = await fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=ib_mark_notifications_read&ids=${JSON.stringify(notificationIds)}&nonce=${IBNotifBell.nonce}`
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Mettre à jour l'interface
        notificationIds.forEach(id => {
          const card = document.querySelector(`[data-id="${id}"]`);
          if (card) {
            card.classList.remove('unread');
            const unreadBadge = card.querySelector('.ib-notif-unread-badge');
            if (unreadBadge) unreadBadge.remove();
          }
        });
        
        // Mettre à jour le compteur de notifications non lues
        if (window.updateUnreadCount) {
          window.updateUnreadCount(-notificationIds.length);
        }
        
        this.showNotice('Notifications marquées comme lues avec succès.', 'success');
        this.exitSelectionMode();
      } else {
        throw new Error(data.data?.message || 'Erreur lors du marquage comme lu');
      }
    } catch (error) {
      console.error('Erreur lors du marquage comme lu:', error);
      throw error;
    }
  }
  
  async deleteNotifications(notificationIds) {
    try {
      const response = await fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=ib_delete_notifications&ids=${JSON.stringify(notificationIds)}&nonce=${IBNotifBell.nonce}`
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Supprimer les notifications de l'interface
        notificationIds.forEach(id => {
          const card = document.querySelector(`[data-id="${id}"]`);
          if (card) card.remove();
        });
        
        // Vérifier s'il reste des notifications
        const remainingNotifications = document.querySelectorAll('.ib-notif-card').length;
        if (remainingNotifications === 0) {
          const notifList = document.getElementById('ib-notif-list');
          const notifEmpty = document.getElementById('ib-notif-empty');
          if (notifList) notifList.innerHTML = '';
          if (notifEmpty) notifEmpty.style.display = 'block';
        }
        
        // Mettre à jour le compteur de notifications non lues
        if (window.updateUnreadCount) {
          const unreadCount = document.querySelectorAll('.ib-notif-card.unread').length;
          window.updateUnreadCount(unreadCount - notificationIds.length, true);
        }
        
        this.showNotice('Notifications supprimées avec succès.', 'success');
        this.exitSelectionMode();
      } else {
        throw new Error(data.data?.message || 'Erreur lors de la suppression');
      }
    } catch (error) {
      console.error('Erreur lors de la suppression des notifications:', error);
      throw error;
    }
  }
  
  /**
   * Affiche un message de notification
   * @param {string} message - Le message à afficher
   * @param {string} type - Le type de message (success, error, warning, info)
   */
  showNotice(message, type = 'info') {
    // Supprimer les anciens messages
    const existingNotice = document.querySelector('.ib-notif-notice');
    if (existingNotice) {
      existingNotice.remove();
    }
    
    // Créer le conteneur du message
    const notice = document.createElement('div');
    notice.className = `ib-notif-notice ib-notif-notice-${type}`;
    notice.setAttribute('role', 'alert');
    notice.setAttribute('aria-live', 'polite');
    
    // Ajouter l'icône en fonction du type
    let icon = '';
    switch (type) {
      case 'success':
        icon = '<span class="dashicons dashicons-yes"></span>';
        break;
      case 'error':
        icon = '<span class="dashicons dashicons-no-alt"></span>';
        break;
      case 'warning':
        icon = '<span class="dashicons dashicons-warning"></span>';
        break;
      default:
        icon = '<span class="dashicons dashicons-info"></span>';
    }
    
    // Ajouter le contenu du message
    notice.innerHTML = `
      <div class="ib-notif-notice-content">
        <div class="ib-notif-notice-icon">${icon}</div>
        <div class="ib-notif-notice-message">${message}</div>
        <button type="button" class="ib-notif-notice-dismiss" aria-label="Fermer">
          <span class="dashicons dashicons-no"></span>
        </button>
      </div>
    `;
    
    // Ajouter le message au DOM
    document.body.appendChild(notice);
    
    // Animation d'apparition
    setTimeout(() => notice.classList.add('show'), 10);
    
    // Fermeture automatique après 5 secondes
    const timeout = setTimeout(() => {
      notice.classList.remove('show');
      setTimeout(() => notice.remove(), 300);
    }, 5000);
    
    // Gestion de la fermeture manuelle
    const dismissBtn = notice.querySelector('.ib-notif-notice-dismiss');
    if (dismissBtn) {
      dismissBtn.addEventListener('click', () => {
        clearTimeout(timeout);
        notice.classList.remove('show');
        setTimeout(() => notice.remove(), 300);
      });
    }
  }
  
  /**
   * Supprime toutes les notifications
   */
  async deleteAllNotifications() {
    if (!confirm('Êtes-vous sûr de vouloir supprimer toutes les notifications ? Cette action est irréversible.')) {
      return;
    }
    
    try {
      // Afficher un indicateur de chargement
      const loadingIndicator = document.createElement('div');
      loadingIndicator.className = 'ib-notif-loading';
      loadingIndicator.innerHTML = '<div class="spinner is-active"></div>';
      this.actionsElement.appendChild(loadingIndicator);
      
      // Désactiver les boutons pendant la suppression
      const buttons = this.actionsElement.querySelectorAll('button');
      buttons.forEach(btn => btn.disabled = true);
      
      // Appeler l'API pour supprimer toutes les notifications
      const response = await fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=ib_delete_all_notifications&nonce=${IBNotifBell.nonce}`
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Supprimer toutes les notifications de l'interface
        const container = document.querySelector('.ib-notifications-container');
        if (container) {
          container.innerHTML = '<div class="ib-notif-empty">Aucune notification</div>';
        }
        
        // Mettre à jour le compteur de notifications non lues
        if (window.updateUnreadCount) {
          window.updateUnreadCount(0, true);
        }
        
        // Afficher un message de succès
        this.showNotice('Toutes les notifications ont été supprimées avec succès.', 'success');
      } else {
        throw new Error(data.data?.message || 'Erreur lors de la suppression des notifications');
      }
    } catch (error) {
      console.error('Erreur lors de la suppression des notifications :', error);
      this.showNotice(`Une erreur est survenue : ${error.message}`, 'error');
    } finally {
      // Nettoyer l'interface
      const loadingIndicator = this.actionsElement.querySelector('.ib-notif-loading');
      if (loadingIndicator) loadingIndicator.remove();
      
      const buttons = this.actionsElement.querySelectorAll('button');
      buttons.forEach(btn => btn.disabled = false);
      
      // Sortir du mode sélection
      this.exitSelectionMode();
    }
  }
}

// Initialiser la sélection de notifications lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
  if (typeof IBNotifBell !== 'undefined') {
    window.notificationSelection = new NotificationSelection();
  }
});
