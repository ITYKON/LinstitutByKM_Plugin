<?php
/**
 * Script de débogage pour vérifier les jours de travail des employés
 */

define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

global $wpdb;

// Récupérer tous les employés avec leurs jours de travail
$employees = $wpdb->get_results("SELECT id, name, working_days FROM {$wpdb->prefix}ib_employees");

echo "<h1>Débogage des jours de travail</h1>";
echo "<style>body{font-family: Arial, sans-serif; margin: 20px; line-height: 1.6;}</style>";

echo "<h2>Données brutes de la base de données :</h2>";
echo "<pre>";
print_r($employees);
echo "</pre>";

// Tester la méthode works_on_day pour chaque employé
require_once(plugin_dir_path(__FILE__) . 'includes/class-employees.php');

echo "<h2>Vérification des jours de travail :</h2>";
$test_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

foreach ($employees as $employee) {
    echo "<h3>Employé: {$employee->name} (ID: {$employee->id})</h3>";
    
    echo "<p>Jours de travail (brut): " . htmlspecialchars($employee->working_days) . "</p>";
    
    $working_days = json_decode($employee->working_days, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<p style='color:red'>Erreur de décodage JSON: " . json_last_error_msg() . "</p>";
    } else {
        echo "<p>Jours de travail (décodés): " . implode(", ", $working_days) . "</p>";
    }
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Jour</th><th>works_on_day()</th></tr>";
    
    foreach ($test_days as $day) {
        $works = IB_Employees::works_on_day($employee->id, $day) ? 'Oui' : 'Non';
        echo "<tr><td>$day</td><td>$works</td></tr>";
    }
    
    echo "</table><br>";
}

// Vérifier la date d'aujourd'hui
$today = strtolower(date('l'));
echo "<h2>Test pour aujourd'hui ($today) :</h2>";

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Employé</th><th>ID</th><th>Travaille aujourd'hui</th></tr>";

foreach ($employees as $employee) {
    $works_today = IB_Employees::works_on_day($employee->id, $today) ? 'Oui' : 'Non';
    echo "<tr>";
    echo "<td>{$employee->name}</td>";
    echo "<td>{$employee->id}</td>";
    echo "<td>$works_today</td>";
    echo "</tr>";
}

echo "</table>";
?>
