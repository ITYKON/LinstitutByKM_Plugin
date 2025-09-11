jQuery(document).ready(function($) {
    // Fonction pour rafraîchir les réservations
    function refreshBookings() {
        var $table = $('.ib-table-bookings');
        var $loading = $('<div class="ib-loading" style="text-align:center;padding:20px;">Mise à jour des réservations...</div>');
        
        // Afficher l'indicateur de chargement
        $table.before($loading);
        
        // Récupérer la page actuelle et les paramètres de tri
        var currentPage = $('.pagination .active').data('page') || 1;
        var sortField = $table.find('th[data-sort].sorted').data('sort') || 'date';
        var sortOrder = $table.find('th[data-sort].sorted').hasClass('sort-asc') ? 'asc' : 'desc';
        
        // Récupérer les valeurs actuelles des filtres
        var filters = {
            status: $('#filter-status').val(),
            employee: $('#filter-employee').val(),
            service: $('#filter-service').val(),
            search: $('#ib-booking-search').val(),
            date_from: $('input[name="date_from"]').val(),
            date_to: $('input[name="date_to"]').val()
        };
        
        // Effectuer la requête AJAX
        $.ajax({
            url: typeof ib_admin !== 'undefined' ? ib_admin.ajax_url : ajaxurl,
            type: 'POST',
            data: {
                action: 'ib_get_updated_bookings',
                page: currentPage,
                sort: sortField,
                order: sortOrder,
                filters: filters,
                nonce: typeof ib_admin !== 'undefined' ? ib_admin.nonce : ''
            },
            success: function(response) {
                if (response.success) {
                    // Mettre à jour le tableau uniquement si les données ont changé
                    if (response.data && response.data.html) {
                        var $newContent = $(response.data.html);
                        $table.replaceWith($newContent);
                        
                        // Afficher une notification de mise à jour
                        var $updateNotice = $('<div class="notice notice-success is-dismissible" style="margin:10px 0;">' +
                            '<p>Les réservations ont été mises à jour.</p>' +
                            '</div>');
                        
                        $newContent.before($updateNotice);
                        
                        // Masquer la notification après 3 secondes
                        setTimeout(function() {
                            $updateNotice.fadeOut(500, function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors du rafraîchissement des réservations:', status, error);
            },
            complete: function() {
                $loading.remove();
            }
        });
    }

    // Démarrer le rafraîchissement automatique toutes les 30 secondes
    var refreshInterval = setInterval(refreshBookings, 30000);

    // Arrêter le rafraîchissement lorsque la page n'est pas visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(refreshInterval);
        } else {
            refreshInterval = setInterval(refreshBookings, 30000);
            refreshBookings(); // Rafraîchir immédiatement quand on revient sur l'onglet
        }
    });
});
