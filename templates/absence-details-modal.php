<?php
/**
 * Template pour le modal de détails d'absence
 *
 * @package LinstitutByKM_Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<!-- Modal de détails d'absence -->
<div id="absence-details-modal" class="ib-modal-bg" style="display: none;">
    <div id="absence-details-content" class="ib-modal" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <!-- Le contenu sera généré dynamiquement par JavaScript -->
    </div>
</div>

<!-- Styles pour le modal de détails -->
<style>
/* Styles pour le modal de détails d'absence */
#absence-details-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    padding: 20px;
    box-sizing: border-box;
}

#absence-details-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

#absence-details-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    z-index: 10;
}

#absence-details-close:hover {
    color: #000;
}

#absence-details-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    position: relative;
}

#absence-details-title {
    margin: 0;
    font-size: 1.5em;
    color: #333;
}

#absence-details-body {
    padding: 20px;
}

.absence-detail-row {
    display: flex;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f5f5f5;
}

.absence-detail-label {
    font-weight: 600;
    width: 150px;
    color: #666;
    flex-shrink: 0;
}

.absence-detail-value {
    flex: 1;
    color: #333;
}

/* Badge de statut */
.absence-status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-approved {
    background-color: #d4edda;
    color: #155724;
}

.status-rejected {
    background-color: #f8d7da;
    color: #721c24;
}

.status-cancelled {
    background-color: #e2e3e5;
    color: #383d41;
}

/* Formulaire de mise à jour du statut */
#absence-status-form {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

#absence-status-form select {
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #ddd;
    margin-right: 10px;
    min-width: 200px;
    font-size: 14px;
}

#absence-status-form button {
    background-color: #4a90e2;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s;
}

#absence-status-form button:hover {
    background-color: #357abd;
}

#absence-status-form button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 600px) {
    .absence-detail-row {
        flex-direction: column;
    }
    
    .absence-detail-label {
        width: 100%;
        margin-bottom: 5px;
    }
    
    #absence-status-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    #absence-status-form select,
    #absence-status-form button {
        width: 100%;
        margin-right: 0;
    }
}
</style>
