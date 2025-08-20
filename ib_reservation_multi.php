<?php
// Handler AJAX pour la réservation multi-prestation
add_action('wp_ajax_ib_reservation_multi', 'ib_reservation_multi_handler');
add_action('wp_ajax_nopriv_ib_reservation_multi', 'ib_reservation_multi_handler');

function ib_reservation_multi_handler() {
    // Sécurité : vérifier les droits si besoin
    // if (!is_user_logged_in()) { wp_send_json_error('Non autorisé'); }

    // Récupérer et décoder les données envoyées
    $cart = isset($_POST['cart']) ? json_decode(stripslashes($_POST['cart']), true) : [];
    $client = isset($_POST['client']) ? json_decode(stripslashes($_POST['client']), true) : [];

    // Exemple de traitement : enregistrer chaque prestation
    foreach ($cart as $item) {
        // Ici, tu peux insérer chaque réservation dans la base de données
        // Exemple :
        // $service = $item['service']['name'];
        // $employee = $item['employee']['name'];
        // $date = $item['date'];
        // $slot = $item['slot'];
        // ...
        // wp_insert_post([...]);
    }

    // Tu peux aussi envoyer un email de confirmation ici

    wp_send_json_success('Réservation multi-prestation enregistrée !');
}
