<?php
/**
 * Script de débogage pour le calendrier des absences
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Vérifier les capacités utilisateur
if (!current_user_can('manage_options')) {
    wp_die('Accès non autorisé. Vous devez être administrateur pour accéder à cette page.');
}

global $wpdb;

// Fonction pour afficher les données de débogage
function debug_output($title, $data) {
    echo "<h3>$title</h3>";
    echo "<pre>";
    print_r($data);
    echo "</pre><hr>";
}

// Récupérer les absences pour le mois en cours
$start_date = date('Y-m-01');
$end_date = date('Y-m-t');
$absences = $wpdb->get_results($wpdb->prepare("
    SELECT a.*, e.name as employee_name 
    FROM {$wpdb->prefix}ib_employee_absences a
    LEFT JOIN {$wpdb->prefix}ib_employees e ON a.employee_id = e.id
    WHERE 
        ((a.start_date BETWEEN %s AND %s) 
        OR (a.end_date BETWEEN %s AND %s) 
        OR (a.start_date <= %s AND a.end_date >= %s))
    ORDER BY a.start_date ASC
", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date));

?>
<!DOCTYPE html>
<html>
<head>
    <title>Débogage - Calendrier des absences</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; max-width: 1200px; margin: 0 auto; padding: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Débogage - Calendrier des absences</h1>
    
    <h2>Configuration</h2>
    <p><strong>Date de début :</strong> <?php echo $start_date; ?></p>
    <p><strong>Date de fin :</strong> <?php echo $end_date; ?></p>
    
    <h2>Absences trouvées (<?php echo count($absences); ?>)</h2>
    
    <?php if (!empty($absences)) : ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Employé</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Créé le</th>
            </tr>
            <?php foreach ($absences as $abs) : ?>
                <tr>
                    <td><?php echo $abs->id; ?></td>
                    <td><?php echo esc_html($abs->employee_name); ?> (ID: <?php echo $abs->employee_id; ?>)</td>
                    <td><?php echo $abs->start_date; ?></td>
                    <td><?php echo $abs->end_date; ?></td>
                    <td><?php echo $abs->type; ?></td>
                    <td><?php echo $abs->status; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($abs->created_at)); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <p class="error">Aucune absence trouvée pour cette période.</p>
        
        <h3>Vérification de la table des absences</h3>
        <?php
        $table_name = $wpdb->prefix . 'ib_employee_absences';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        if ($table_exists) {
            echo "<p class='success'>La table <strong>$table_name</strong> existe.</p>";
            
            // Vérifier le contenu de la table
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            echo "<p>Nombre total d'absences enregistrées : <strong>$count</strong></p>";
            
            if ($count > 0) {
                $first_absence = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id ASC LIMIT 1");
                debug_output("Première absence enregistrée", $first_absence);
            }
        } else {
            echo "<p class='error'>La table <strong>$table_name</strong> n'existe pas.</p>";
        }
        ?>
    <?php endif; ?>
    
    <h2>Test de récupération AJAX</h2>
    <button id="test-ajax">Tester la récupération AJAX</button>
    <div id="ajax-result" style="margin-top: 20px; padding: 10px; border: 1px solid #ddd; min-height: 100px;"></div>
    
    <script>
    // Définir ajaxurl pour le débogage
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    
    document.getElementById('test-ajax').addEventListener('click', function() {
        const resultDiv = document.getElementById('ajax-result');
        resultDiv.innerHTML = 'Envoi de la requête AJAX...';
        
        const formData = new FormData();
        formData.append('action', 'get_absences');
        formData.append('start_date', '<?php echo $start_date; ?>');
        formData.append('end_date', '<?php echo $end_date; ?>');
        formData.append('nonce', '<?php echo wp_create_nonce('ib_absence_nonce'); ?>');
        
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            resultDiv.innerHTML = '<h3>Réponse du serveur :</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            
            if (data.success && data.data && data.data.length > 0) {
                let html = '<h3>Absences reçues :</h3><ul>';
                data.data.forEach(abs => {
                    html += `<li>${abs.employee_name} (${abs.start_date} au ${abs.end_date}) - ${abs.type} (${abs.status})</li>`;
                });
                html += '</ul>';
                resultDiv.innerHTML += html;
            } else {
                resultDiv.innerHTML += '<p class="error">Aucune donnée d\'absence reçue.</p>';
                if (data.data) {
                    resultDiv.innerHTML += '<p>Message d\'erreur : ' + data.data + '</p>';
                }
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<p class="error">Erreur lors de la requête AJAX : ' + error.message + '</p>';
        });
    });
    </script>
</body>
</html>
