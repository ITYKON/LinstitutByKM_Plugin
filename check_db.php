<?php
// Vérification de la configuration PHP
echo "=== Vérification de la configuration PHP ===\n";

// Vérifier si l'extension MySQLi est chargée
if (extension_loaded('mysqli')) {
    echo "✅ L'extension MySQLi est chargée.\n";
} else {
    echo "❌ L'extension MySQLi n'est PAS chargée.\n";
}

// Afficher les extensions chargées
echo "\n=== Extensions PHP chargées ===\n";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo "- $ext\n";
}

// Vérifier les informations de configuration de PHP
echo "\n=== Configuration PHP ===\n";
$php_configs = [
    'memory_limit',
    'upload_max_filesize',
    'post_max_size',
    'max_execution_time',
    'max_input_time',
    'display_errors',
    'error_reporting'
];

foreach ($php_configs as $config) {
    echo "- $config: " . ini_get($config) . "\n";
}

// Essayer de se connecter à la base de données
echo "\n=== Test de connexion à la base de données ===\n";

try {
    // Essayer de charger le fichier de configuration WordPress
    $config_file = dirname(__FILE__) . '/../../../wp-config.php';
    
    if (!file_exists($config_file)) {
        throw new Exception("Le fichier de configuration WordPress est introuvable.");
    }
    
    // Extraire les informations de connexion
    $config_content = file_get_contents($config_file);
    
    // Extraire les constantes de configuration
    preg_match_all("/define\s*\(\s*['\"]DB_(NAME|USER|PASSWORD|HOST|CHARSET|COLLATE)['\"]\s*,\s*['\"]([^'\"]*)['\"]\s*\)/i", 
                  $config_content, $matches, PREG_SET_ORDER);
    
    $db_config = [];
    foreach ($matches as $match) {
        $db_config[strtolower($match[1])] = $match[2];
    }
    
    if (empty($db_config)) {
        throw new Exception("Impossible d'extraire les informations de connexion à la base de données.");
    }
    
    echo "- Hôte: " . ($db_config['host'] ?? 'Non défini') . "\n";
    echo "- Base de données: " . ($db_config['name'] ?? 'Non défini') . "\n";
    echo "- Utilisateur: " . ($db_config['user'] ?? 'Non défini') . "\n";
    
    // Essayer de se connecter
    $mysqli = new mysqli(
        $db_config['host'] ?? 'localhost',
        $db_config['user'] ?? '',
        $db_config['password'] ?? '',
        $db_config['name'] ?? ''
    );
    
    if ($mysqli->connect_error) {
        throw new Exception("Échec de la connexion à la base de données: " . $mysqli->connect_error);
    }
    
    echo "✅ Connexion à la base de données réussie.\n";
    
    // Vérifier si la table des clients existe
    $table_name = $mysqli->real_escape_string($mysqli->get_server_info() >= '5.0.2' ? 
        $mysqli->query("SELECT DATABASE()")->fetch_row()[0] : $db_config['name']) . ".{$mysqli->real_escape_string($mysqli->get_server_info() >= '5.0.2' ? '' : $mysqli->get_server_info() >= '4.1.0' ? $mysqli->get_server_info() : '')}ib_clients";
    
    $result = $mysqli->query("SHOW TABLES LIKE '{$mysqli->real_escape_string($mysqli->get_server_info() >= '5.0.2' ? '' : $mysqli->get_server_info() >= '4.1.0' ? $mysqli->get_server_info() : '')}ib_clients'");
    
    if ($result->num_rows > 0) {
        echo "✅ La table ib_clients existe.\n";
        
        // Compter les clients
        $count_result = $mysqli->query("SELECT COUNT(*) as count FROM {$mysqli->real_escape_string($mysqli->get_server_info() >= '5.0.2' ? '' : $mysqli->get_server_info() >= '4.1.0' ? $mysqli->get_server_info() : '')}ib_clients");
        $count = $count_result->fetch_assoc()['count'];
        echo "- Nombre de clients: $count\n";
        
        // Afficher quelques clients
        if ($count > 0) {
            $clients = $mysqli->query("SELECT * FROM {$mysqli->real_escape_string($mysqli->get_server_info() >= '5.0.2' ? '' : $mysqli->get_server_info() >= '4.1.0' ? $mysqli->get_server_info() : '')}ib_clients LIMIT 5");
            echo "\nExemple de clients :\n";
            while ($client = $clients->fetch_assoc()) {
                echo "- ID: {$client['id']}, Nom: {$client['name']}, Email: {$client['email']}, Téléphone: {$client['phone']}\n";
            }
        }
    } else {
        echo "❌ La table ib_clients n'existe pas.\n";
        
        // Essayer de créer la table
        echo "\nTentative de création de la table...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$mysqli->real_escape_string($mysqli->get_server_info() >= '5.0.2' ? '' : $mysqli->get_server_info() >= '4.1.0' ? $mysqli->get_server_info() : '')}ib_clients` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `phone` varchar(20) NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            `bookings_count` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        if ($mysqli->query($sql)) {
            echo "✅ La table a été créée avec succès.\n";
        } else {
            echo "❌ Erreur lors de la création de la table : " . $mysqli->error . "\n";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    
    // Afficher des informations de débogage supplémentaires
    if (isset($mysqli) && $mysqli->error) {
        echo "- Erreur MySQL : " . $mysqli->error . "\n";
    }
}

echo "\n=== Fin du diagnostic ===\n";
