<?php
/**
 * Script de débogage pour vérifier la structure et les données de la table des employés
 */

define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

global $wpdb;

// Vérifier si la table existe
$table_name = $wpdb->prefix . 'ib_employees';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;

// Récupérer la structure de la table
$structure = $table_exists ? $wpdb->get_results("DESCRIBE $table_name") : [];

// Récupérer quelques enregistrements d'exemple
$employees = $table_exists ? $wpdb->get_results("SELECT id, name, working_days FROM $table_name LIMIT 5") : [];

// Fonction pour tester si un employé travaille un jour donné
function test_works_on_day($employee_id, $day) {
    require_once(plugin_dir_path(__FILE__) . 'includes/class-employees.php');
    return IB_Employees::works_on_day($employee_id, $day);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Débogage Table Employés</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
    </style>
</head>
<body>
    <h1>Débogage Table Employés</h1>
    
    <h2>Statut de la table</h2>
    <p>Table <strong><?php echo $table_name; ?></strong> : 
       <span class="<?php echo $table_exists ? 'success' : 'error'; ?>">
           <?php echo $table_exists ? 'Existe' : 'N\'existe pas'; ?>
       </span>
    </p>
    
    <?php if ($table_exists): ?>
        <h2>Structure de la table</h2>
        <table>
            <tr>
                <th>Champ</th>
                <th>Type</th>
                <th>Null</th>
                <th>Clé</th>
                <th>Défaut</th>
                <th>Extra</th>
            </tr>
            <?php foreach ($structure as $column): ?>
            <tr>
                <td><?php echo $column->Field; ?></td>
                <td><?php echo $column->Type; ?></td>
                <td><?php echo $column->Null; ?></td>
                <td><?php echo $column->Key; ?></td>
                <td><?php echo $column->Default ?? 'NULL'; ?></td>
                <td><?php echo $column->Extra; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <h2>Données d'exemple</h2>
        <?php if ($employees): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Jours de travail (brut)</th>
                    <th>Jours de travail (décodé)</th>
                </tr>
                <?php foreach ($employees as $employee): 
                    $working_days = json_decode($employee->working_days, true);
                    $json_error = json_last_error();
                ?>
                <tr>
                    <td><?php echo $employee->id; ?></td>
                    <td><?php echo esc_html($employee->name); ?></td>
                    <td><pre><?php echo htmlspecialchars($employee->working_days); ?></pre></td>
                    <td>
                        <?php if ($json_error === JSON_ERROR_NONE): ?>
                            <pre><?php print_r($working_days); ?></pre>
                        <?php else: ?>
                            <span class="error">Erreur de décodage JSON (<?php echo json_last_error_msg(); ?>)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <h2>Test de la méthode works_on_day</h2>
            <table>
                <tr>
                    <th>Employé</th>
                    <th>Lundi</th>
                    <th>Mardi</th>
                    <th>Mercredi</th>
                    <th>Jeudi</th>
                    <th>Vendredi</th>
                    <th>Samedi</th>
                    <th>Dimanche</th>
                </tr>
                <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?php echo esc_html($employee->name); ?></td>
                    <?php 
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    foreach ($days as $day):
                        $works = test_works_on_day($employee->id, $day);
                    ?>
                    <td class="<?php echo $works ? 'success' : 'error'; ?>">
                        <?php echo $works ? 'Oui' : 'Non'; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>Aucun employé trouvé dans la base de données.</p>
        <?php endif; ?>
    <?php endif; ?>
    
    <h2>Logs d'erreurs</h2>
    <p>Les logs d'erreurs peuvent être consultés dans le fichier :</p>
    <pre>wp-content/debug.log</pre>
    
    <h2>Actions</h2>
    <p><a href="admin.php?page=institut-booking-employees">Retour à la gestion des employés</a></p>
</body>
</html>
