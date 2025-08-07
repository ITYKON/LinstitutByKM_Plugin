<?php
// Script à placer dans le dossier du plugin puis à lancer UNE FOIS via le navigateur
// Exemple d'URL : http://localhost/wp-content/plugins/Booking-plugin-master/fix-status.php

require_once('../../../wp-load.php'); // adapte le chemin si besoin

global $wpdb;
$table = $wpdb->prefix . 'ib_bookings';

$queries = [
    ["valide", "confirmee"],
    ["confirmée", "confirmee"],
    ["confirmé", "confirmee"],
    ["confirme", "confirmee"],
    ["confirm", "confirmee"],
    ["confirmed", "confirmee"],
    ["annule", "annulee"],
    ["annulée", "annulee"],
    ["annulé", "annulee"],
    ["cancel", "annulee"],
    ["cancelled", "annulee"],
    ["en attente", "en_attente"],
    ["pending", "en_attente"],
    ["attente", "en_attente"],
    ["pas_confirme", "en_attente"],
    ["not_confirmed", "en_attente"]
];

$count = 0;
foreach ($queries as [$from, $to]) {
    $r = $wpdb->query($wpdb->prepare("UPDATE $table SET status = %s WHERE status = %s", $to, $from));
    $count += $r;
}
echo "<h2>Correction terminée</h2><p>$count réservations corrigées.</p>";
