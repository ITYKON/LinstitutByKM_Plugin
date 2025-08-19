/**
 * Force le style Planity sur le sélecteur de pays
 * Script pour surcharger complètement intl-tel-input
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Fonction pour forcer le style Planity
    function forcePlanityStyle() {
        
        // Créer et injecter les styles CSS directement
        const planityStyles = `
            <style id="force-planity-iti">
                /* FORCER STYLE PLANITY AVEC PRIORITÉ MAXIMALE */
                
                .iti__country-list {
                    background: #ffffff !important;
                    border: 1px solid #e5e7eb !important;
                    border-radius: 12px !important;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                    padding: 8px 0 !important;
                    margin-top: 4px !important;
                    max-height: 200px !important;
                    overflow-y: auto !important;
                    z-index: 1000 !important;
                }
                
                .iti__country {
                    background: #ffffff !important;
                    color: #374151 !important;
                    font-weight: 500 !important;
                    padding: 10px 16px !important;
                    border: none !important;
                    font-size: 14px !important;
                    cursor: pointer !important;
                    transition: all 0.2s ease !important;
                    display: flex !important;
                    align-items: center !important;
                    gap: 12px !important;
                    margin: 0 !important;
                    line-height: 1.4 !important;
                }
                
                .iti__country:hover,
                .iti__country.iti__highlight {
                    background: #f9fafb !important;
                    color: #111827 !important;
                }
                
                .iti__country-name {
                    color: #374151 !important;
                    font-weight: 500 !important;
                    font-size: 14px !important;
                    flex: 1 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                
                .iti__dial-code {
                    color: #6b7280 !important;
                    font-weight: 600 !important;
                    font-size: 13px !important;
                    background: #f9fafb !important;
                    padding: 2px 6px !important;
                    border-radius: 4px !important;
                    margin-left: auto !important;
                    margin-right: 0 !important;
                }
                
                .iti__flag {
                    width: 20px !important;
                    height: 15px !important;
                    border-radius: 2px !important;
                    border: 1px solid #e5e7eb !important;
                    margin-right: 8px !important;
                    flex-shrink: 0 !important;
                    background-size: contain !important;
                    background-repeat: no-repeat !important;
                    background-position: center !important;
                }
                
                /* Container ITI */
                .iti {
                    background: #f9fafb !important;
                    border: 1px solid #d1d5db !important;
                    border-radius: 12px !important;
                    box-shadow: none !important;
                }
                
                .iti:focus-within {
                    background: #ffffff !important;
                    border-color: #374151 !important;
                    box-shadow: 0 0 0 3px rgba(55, 65, 81, 0.1) !important;
                }
                
                .iti__flag-container {
                    background: #e5e7eb !important;
                    border-right: 1px solid #d1d5db !important;
                    border-radius: 12px 0 0 12px !important;
                }
                
                .iti__flag-container:hover {
                    background: #d1d5db !important;
                }
                
                .iti input[type="tel"] {
                    background: transparent !important;
                    border: none !important;
                    color: #111827 !important;
                    font-weight: 500 !important;
                }
                
                /* Scrollbar personnalisée */
                .iti__country-list::-webkit-scrollbar {
                    width: 6px !important;
                }
                
                .iti__country-list::-webkit-scrollbar-track {
                    background: #f9fafb !important;
                    border-radius: 3px !important;
                }
                
                .iti__country-list::-webkit-scrollbar-thumb {
                    background: #d1d5db !important;
                    border-radius: 3px !important;
                }
                
                .iti__country-list::-webkit-scrollbar-thumb:hover {
                    background: #9ca3af !important;
                }
            </style>
        `;
        
        // Injecter les styles dans le head
        if (!document.getElementById('force-planity-iti')) {
            document.head.insertAdjacentHTML('beforeend', planityStyles);
        }
        
        // Observer les changements DOM pour appliquer les styles aux nouveaux éléments
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            // Appliquer les styles aux éléments ITI
                            applyPlanityStylesDirectly(node);
                        }
                    });
                }
            });
        });
        
        // Démarrer l'observation
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Appliquer les styles directement aux éléments existants
        applyPlanityStylesDirectly(document);
    }
    
    // Fonction pour appliquer les styles directement via JavaScript
    function applyPlanityStylesDirectly(container) {
        
        // Sélecteur de pays dropdown
        const countryLists = container.querySelectorAll ? container.querySelectorAll('.iti__country-list') : [];
        countryLists.forEach(function(list) {
            list.style.cssText = `
                background: #ffffff !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 12px !important;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                padding: 8px 0 !important;
                margin-top: 4px !important;
                max-height: 200px !important;
                overflow-y: auto !important;
                z-index: 1000 !important;
            `;
        });
        
        // Items pays
        const countries = container.querySelectorAll ? container.querySelectorAll('.iti__country') : [];
        countries.forEach(function(country) {
            country.style.cssText = `
                background: #ffffff !important;
                color: #374151 !important;
                font-weight: 500 !important;
                padding: 10px 16px !important;
                border: none !important;
                font-size: 14px !important;
                cursor: pointer !important;
                transition: all 0.2s ease !important;
                display: flex !important;
                align-items: center !important;
                gap: 12px !important;
                margin: 0 !important;
                line-height: 1.4 !important;
            `;
            
            // Ajouter les événements hover
            country.addEventListener('mouseenter', function() {
                this.style.background = '#f9fafb !important';
                this.style.color = '#111827 !important';
            });
            
            country.addEventListener('mouseleave', function() {
                if (!this.classList.contains('iti__highlight')) {
                    this.style.background = '#ffffff !important';
                    this.style.color = '#374151 !important';
                }
            });
        });
        
        // Noms de pays
        const countryNames = container.querySelectorAll ? container.querySelectorAll('.iti__country-name') : [];
        countryNames.forEach(function(name) {
            name.style.cssText = `
                color: #374151 !important;
                font-weight: 500 !important;
                font-size: 14px !important;
                flex: 1 !important;
                margin: 0 !important;
                padding: 0 !important;
            `;
        });
        
        // Codes pays
        const dialCodes = container.querySelectorAll ? container.querySelectorAll('.iti__dial-code') : [];
        dialCodes.forEach(function(code) {
            code.style.cssText = `
                color: #6b7280 !important;
                font-weight: 600 !important;
                font-size: 13px !important;
                background: #f9fafb !important;
                padding: 2px 6px !important;
                border-radius: 4px !important;
                margin-left: auto !important;
                margin-right: 0 !important;
            `;
        });
        
        // Drapeaux
        const flags = container.querySelectorAll ? container.querySelectorAll('.iti__flag') : [];
        flags.forEach(function(flag) {
            flag.style.cssText = `
                width: 20px !important;
                height: 15px !important;
                border-radius: 2px !important;
                border: 1px solid #e5e7eb !important;
                margin-right: 8px !important;
                flex-shrink: 0 !important;
                background-size: contain !important;
                background-repeat: no-repeat !important;
                background-position: center !important;
            `;
        });
        
        // Container ITI
        const itiContainers = container.querySelectorAll ? container.querySelectorAll('.iti') : [];
        itiContainers.forEach(function(iti) {
            iti.style.cssText = `
                background: #f9fafb !important;
                border: 1px solid #d1d5db !important;
                border-radius: 12px !important;
                box-shadow: none !important;
            `;
        });
        
        // Flag containers
        const flagContainers = container.querySelectorAll ? container.querySelectorAll('.iti__flag-container') : [];
        flagContainers.forEach(function(flagContainer) {
            flagContainer.style.cssText = `
                background: #e5e7eb !important;
                border-right: 1px solid #d1d5db !important;
                border-radius: 12px 0 0 12px !important;
            `;
            
            flagContainer.addEventListener('mouseenter', function() {
                this.style.background = '#d1d5db !important';
            });
            
            flagContainer.addEventListener('mouseleave', function() {
                this.style.background = '#e5e7eb !important';
            });
        });
        
        // Inputs téléphone
        const telInputs = container.querySelectorAll ? container.querySelectorAll('.iti input[type="tel"]') : [];
        telInputs.forEach(function(input) {
            input.style.cssText = `
                background: transparent !important;
                border: none !important;
                color: #111827 !important;
                font-weight: 500 !important;
            `;
        });
    }
    
    // Démarrer le processus de forçage du style
    forcePlanityStyle();
    
    // Réappliquer les styles après un délai pour s'assurer qu'ils sont appliqués
    setTimeout(function() {
        applyPlanityStylesDirectly(document);
    }, 500);
    
    // Réappliquer les styles quand la page est complètement chargée
    window.addEventListener('load', function() {
        setTimeout(function() {
            applyPlanityStylesDirectly(document);
        }, 1000);
    });
    
    console.log('🎨 Force Planity Style: Script chargé et actif');
});
