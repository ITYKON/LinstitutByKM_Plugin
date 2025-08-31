<?php
/**
 * Script de débogage pour l'ajout d'absences
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

// Afficher les en-têtes
?>
<!DOCTYPE html>
<html>
<head>
    <title>Débogage - Ajout d'absence</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; max-width: 1000px; margin: 0 auto; padding: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        .test-form { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Débogage - Ajout d'absence</h1>

<?php
// Vérifier si on a des données POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_output("Données POST reçues", $_POST);
    
    // Nettoyer les données
    $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'absence';
    $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'approved';
    
    // Vérifier les données requises
    if ($employee_id && $start_date && $end_date) {
        echo "<h3>Validation des dates :</h3>";
        echo "<p>Date de début : $start_date<br>Date de fin : $end_date</p>";
        
        if (strtotime($end_date) < strtotime($start_date)) {
            echo "<p class='error'>Erreur : La date de fin doit être postérieure ou égale à la date de début.</p>";
        } else {
            echo "<p class='success'>Les dates sont valides.</p>";
            
            // Vérifier les conflits
            echo "<h3>Vérification des conflits :</h3>";
            $conflict = IB_Employee_Absences::has_conflict($employee_id, $start_date, $end_date);
            
            if ($conflict) {
                echo "<p class='error'>Conflit détecté avec une absence existante.</p>";
                
                // Afficher les absences en conflit
                $conflicts = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}ib_employee_absences 
                    WHERE employee_id = %d 
                    AND status = 'approved'
                    AND ((start_date BETWEEN %s AND %s) 
                         OR (end_date BETWEEN %s AND %s) 
                         OR (start_date <= %s AND end_date >= %s))",
                    $employee_id, $start_date, $end_date, $start_date, $end_date, $start_date, $end_date
                ));
                
                debug_output("Absences en conflit", $conflicts);
            } else {
                echo "<p class='success'>Aucun conflit détecté.</p>";
                
                // Tenter d'ajouter l'absence
                echo "<h3>Tentative d'ajout de l'absence :</h3>";
                $result = IB_Employee_Absences::add($employee_id, $start_date, $end_date, $type, $reason, $status);
                
                if ($result !== false) {
                    echo "<p class='success'>Absence ajoutée avec succès (ID: $result)</p>";
                    
                    // Afficher l'absence ajoutée
                    $added_absence = IB_Employee_Absences::get_by_id($result);
                    debug_output("Absence ajoutée", $added_absence);
                } else {
                    echo "<p class='error'>Échec de l'ajout de l'absence.</p>";
                    debug_output("Dernière erreur MySQL", $wpdb->last_error);
                    debug_output("Dernière requête", $wpdb->last_query);
                }
            }
        }
    } else {
        echo "<p class='error'>Données manquantes. Assurez-vous de fournir un employé, une date de début et une date de fin.</p>";
    }
    
    echo "<hr>";
}

// Afficher un formulaire de test
$employees = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}ib_employees ORDER BY name");
?>

<div class="test-form">
    <h2>Formulaire de test</h2>
    <form method="post">
        <div>
            <label>Employé :</label><br>
            <select name="employee_id" required>
                <option value="">Sélectionner un employé</option>
                <?php foreach ($employees as $emp) : ?>
                    <option value="<?php echo $emp->id; ?>"><?php echo esc_html($emp->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
        <div>
            <label>Date de début :</label><br>
            <input type="date" name="start_date" required>
        </div>
        <br>
        <div>
            <label>Date de fin :</label><br>
            <input type="date" name="end_date" required>
        </div>
        <br>
        <div>
            <label>Type :</label><br>
            <select name="type" required>
                <option value="absence">Absence</option>
                <option value="conge">Congé payé</option>
                <option value="maladie">Congé maladie</option>
                <option value="formation">Formation</option>
            </select>
        </div>
        <br>
        <div>
            <label>Statut :</label><br>
            <select name="status" required>
                <option value="approved">Approuvé</option>
                <option value="pending">En attente</option>
            </select>
        </div>
        <br>
        <div>
            <label>Raison (optionnel) :</label><br>
            <textarea name="reason" rows="3" style="width: 100%;"></textarea>
        </div>
        <br>
        <button type="submit">Tester l'ajout d'absence</button>
    </form>
</div>

<h2>Absences existantes</h2>
<?php
$absences = $wpdb->get_results("
    SELECT a.*, e.name as employee_name 
    FROM {$wpdb->prefix}ib_employee_absences a
    LEFT JOIN {$wpdb->prefix}ib_employees e ON a.employee_id = e.id
    ORDER BY a.start_date DESC
    LIMIT 50
");

if ($absences) {
    echo "<table border='1' cellpadding='8' style='width:100%; border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Employé</th><th>Début</th><th>Fin</th><th>Type</th><th>Statut</th><th>Créé le</th></tr>";
    
    foreach ($absences as $abs) {
        echo "<tr>";
        echo "<td>{$abs->id}</td>";
        echo "<td>{$abs->employee_name} (ID: {$abs->employee_id})</td>";
        echo "<td>{$abs->start_date}</td>";
        echo "<td>{$abs->end_date}</td>";
        echo "<td>{$abs->type}</td>";
        echo "<td>{$abs->status}</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($abs->created_at)) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Aucune absence trouvée dans la base de données.</p>";
}
?>

</body>
</html>
