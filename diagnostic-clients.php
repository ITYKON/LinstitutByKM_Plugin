<?php
// Diagnostic rapide de la table ib_clients
require_once dirname(__FILE__) . '/../../../wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'ib_clients';

// Vérifier si la table existe
$table_exists = $wpdb->get_var($wpdb->prepare(
    "SHOW TABLES LIKE %s",
    $table
));
if (!$table_exists) {
    echo '<b style="color:red">La table ' . esc_html($table) . ' n\'existe pas !</b>';
    exit;
}

// Afficher le contenu de la table
$clients = $wpdb->get_results("SELECT * FROM $table");
if (empty($clients)) {
    echo '<b style="color:orange">La table existe mais aucun client n\'est enregistré.</b>';
} else {
    echo '<h3>Clients trouvés dans la table :</h3>';
    echo '<table border="1" cellpadding="6"><tr><th>ID</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Créé</th><th>Modifié</th></tr>';
    foreach ($clients as $c) {
        echo '<tr><td>' . $c->id . '</td><td>' . esc_html($c->name) . '</td><td>' . esc_html($c->email) . '</td><td>' . esc_html($c->phone) . '</td><td>' . $c->created_at . '</td><td>' . $c->updated_at . '</td></tr>';
    }
    echo '</table>';
}
// Afficher la dernière erreur SQL si besoin
if ($wpdb->last_error) {
    echo '<div style="color:red">Erreur SQL : ' . esc_html($wpdb->last_error) . '</div>';
}
