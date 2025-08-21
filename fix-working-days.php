<?php
/**
 * Script pour ajouter la colonne working_days à la table des employés
 * Accédez à ce script via : http://votresite.test/wp-content/plugins/linstitutbykm_plugin/fix-working-days.php
 */

// Désactiver l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_NAME', 'local');

// Se connecter à la base de données
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Définir le jeu de caractères
$conn->set_charset('utf8mb4');

// Préfixe des tables
$table_name = 'wp_ib_employees';

// Vérifier si la table existe
$table_check = $conn->query("SHOW TABLES LIKE '$table_name'");
if ($table_check->num_rows === 0) {
    die("La table $table_name n'existe pas dans la base de données.");
}

// Vérifier si la colonne existe déjà
$column_check = $conn->query("SHOW COLUMNS FROM $table_name LIKE 'working_days'");

if ($column_check->num_rows === 0) {
    // Ajouter la colonne working_days de type LONGTEXT
    $sql = "ALTER TABLE $table_name ADD COLUMN working_days LONGTEXT NULL DEFAULT NULL";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>La colonne working_days a été ajoutée avec succès.</p>";
        
        // Mettre à jour les enregistrements existants avec une valeur par défaut
        $default_days = json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
        $default_days_escaped = $conn->real_escape_string($default_days);
        
        $update_sql = "UPDATE $table_name SET working_days = '$default_days_escaped'";
        
        if ($conn->query($update_sql) === TRUE) {
            echo "<p style='color: green;'>Les employés ont été mis à jour avec les jours de travail par défaut.</p>";
        } else {
            echo "<p style='color: orange;'>La colonne a été ajoutée, mais erreur lors de la mise à jour des employés : " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Erreur lors de l'ajout de la colonne : " . $conn->error . "</p>";
    }
} else {
    echo "<p>La colonne working_days existe déjà dans la table $table_name.</p>";
}

// Afficher un aperçu des données
echo "<h3>Aperçu des données (5 premiers employés) :</h3>";
$result = $conn->query("SELECT id, name, working_days FROM $table_name LIMIT 5");

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Jours de travail</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['working_days']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Aucun employé trouvé dans la table.</p>";
}

// Fermer la connexion
$conn->close();

echo "<p style='margin-top: 20px;'><strong>Exécution terminée.</strong> Vous pouvez supprimer ce fichier maintenant.</p>";
?>
