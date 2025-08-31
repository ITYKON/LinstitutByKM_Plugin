<?php
/**
 * Script pour supprimer les congés en double dans la base de données
 * 
 * Un congé est considéré comme un doublon s'il a le même :
 * - employee_id
 * - start_date
 * - end_date
 * - type
 * 
 * Pour les doublons, on conserve l'entrée la plus récente (ID le plus élevé)
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Vérifier les capacités utilisateur
if (!current_user_can('manage_options')) {
    wp_die('Accès non autorisé. Vous devez être administrateur pour accéder à cette page.');
}

global $wpdb;

try {
    echo "Début de la recherche des congés en double...\n";
    
    // 1. Trouver les doublons
    $duplicates = $wpdb->get_results("
        SELECT 
            employee_id,
            start_date,
            end_date,
            type,
            COUNT(*) as count,
            GROUP_CONCAT(id ORDER BY id DESC) as ids
        FROM {$wpdb->prefix}ib_employee_absences
        GROUP BY employee_id, start_date, end_date, type
        HAVING count > 1
        ORDER BY count DESC
    ");
    
    if (empty($duplicates)) {
        echo "Aucun doublon trouvé.\n";
        exit(0);
    }
    
    $total_duplicates = 0;
    $total_deleted = 0;
    
    echo sprintf("Trouvé %d groupes de doublons.\n\n", count($duplicates));
    
    // 2. Traiter chaque groupe de doublons
    foreach ($duplicates as $group) {
        $ids = explode(',', $group->ids);
        $total_duplicates += count($ids) - 1; // -1 car on garde un exemplaire
        
        // Trier les IDs par ordre décroissant pour garder le plus récent
        rsort($ids);
        
        // Le premier ID est le plus récent, on le garde
        $keep_id = array_shift($ids);
        
        // Supprimer les autres
        if (!empty($ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($ids), '%d'));
            $query = $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}ib_employee_absences WHERE id IN ($ids_placeholder)",
                $ids
            );
            
            $deleted = $wpdb->query($query);
            $total_deleted += $deleted;
            
            echo sprintf(
                "Doublons pour employé #%d (%s - %s, %s): %d supprimés (gardé ID: %d)\n",
                $group->employee_id,
                $group->start_date,
                $group->end_date,
                $group->type,
                $deleted,
                $keep_id
            );
        }
    }
    
    echo "\nRécapitulatif :\n";
    echo "- Groupes de doublons traités : " . count($duplicates) . "\n";
    echo "- Total de doublons identifiés : " . $total_duplicates . "\n";
    echo "- Total de doublons supprimés : " . $total_deleted . "\n";
    
} catch (Exception $e) {
    echo "\nERREUR : " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nScript terminé avec succès.\n";
