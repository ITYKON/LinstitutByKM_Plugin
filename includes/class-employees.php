<?php
// Gestion des employés
if (!defined('ABSPATH')) exit;

class IB_Employees {
    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ib_employees ORDER BY name ASC");
    }

    public static function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_employees WHERE id = %d", $id));
    }

    public static function add($name, $email, $phone = '', $specialty = '', $role = '', $working_days = null, $created_at = null) {
        global $wpdb;
        
        // Préparer les données de base
        $data = [
            'name' => sanitize_text_field($name),
            'email' => sanitize_email($email),
            'phone' => sanitize_text_field($phone),
            'specialty' => sanitize_text_field($specialty),
            'role' => sanitize_text_field($role),
            'created_at' => $created_at ? $created_at : current_time('mysql'),
        ];
        
        // Gérer les jours de travail
        if ($working_days !== null && is_array($working_days) && !empty($working_days)) {
            // Nettoyer et valider les jours de travail
            $valid_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $filtered_days = array_intersect($valid_days, $working_days);
            $data['working_days'] = json_encode(array_values($filtered_days));
            error_log('Jours de travail ajoutés pour le nouvel employé: ' . $data['working_days']);
        } else {
            // Valeur par défaut si aucun jour n'est spécifié
            $data['working_days'] = json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            error_log('Utilisation des jours de travail par défaut pour le nouvel employé');
        }
        
        // Insérer dans la base de données
        $result = $wpdb->insert("{$wpdb->prefix}ib_employees", $data);
        
        if ($result === false) {
            error_log('Erreur lors de l\'ajout de l\'employé: ' . $wpdb->last_error);
            return false;
        }
        
        error_log('Nouvel employé ajouté avec succès. ID: ' . $wpdb->insert_id);
        return $wpdb->insert_id;
    }

    public static function update($id, $name, $email, $phone = '', $specialty = '', $role = '', $working_days = null, $created_at = null) {
        global $wpdb;
        
        // Log des données reçues
        error_log('=== DÉBUT MISE À JOUR EMPLOYÉ ===');
        error_log('ID: ' . $id);
        error_log('Nom: ' . $name);
        error_log('Email: ' . $email);
        error_log('Jours de travail reçus: ' . print_r($working_days, true));
        
        // Préparer les données de base
        $update_data = [
            'name' => sanitize_text_field($name),
            'email' => sanitize_email($email),
            'phone' => sanitize_text_field($phone),
            'specialty' => sanitize_text_field($specialty),
            'role' => sanitize_text_field($role),
        ];
        
        // Gestion des jours de travail
        if ($working_days !== null) {
            // S'assurer que c'est bien un tableau
            $working_days = is_array($working_days) ? $working_days : [];
            
            // Nettoyer et valider les jours
            $valid_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $filtered_days = array_intersect($valid_days, $working_days);
            
            // Trier les jours pour une meilleure lisibilité
            usort($filtered_days, function($a, $b) use ($valid_days) {
                return array_search($a, $valid_days) - array_search($b, $valid_days);
            });
            
            $update_data['working_days'] = json_encode(array_values($filtered_days));
            
            error_log('Jours de travail validés: ' . print_r($filtered_days, true));
            error_log('JSON encodé: ' . $update_data['working_days']);
            
            // Vérifier le décodage immédiatement
            $test_decode = json_decode($update_data['working_days'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('ERREUR D\'ENCODAGE JSON: ' . json_last_error_msg());
            } else {
                error_log('Vérification décodage: ' . print_r($test_decode, true));
            }
        } else {
            // Si aucun jour n'est coché, définir un tableau vide
            $update_data['working_days'] = json_encode([]);
            error_log('Aucun jour de travail fourni, réinitialisation à tableau vide');
        }
        
        if ($created_at) {
            $update_data['created_at'] = $created_at;
        }
        
        // Préparer les formats pour la mise à jour
        $formats = ['%s', '%s', '%s', '%s', '%s', '%s'];
        
        // Exécuter la mise à jour
        $result = $wpdb->update(
            "{$wpdb->prefix}ib_employees",
            $update_data,
            ['id' => intval($id)],
            $formats,
            ['%d']
        );
        
        // Vérifier les erreurs
        if ($result === false) {
            error_log('ERREUR SQL: ' . $wpdb->last_error);
        } else {
            error_log('Mise à jour réussie. Lignes affectées: ' . $result);
            
            // Vérifier les données mises à jour
            $updated = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_employees WHERE id = %d", $id));
            if ($updated) {
                error_log('Données après mise à jour:');
                error_log('working_days: ' . $updated->working_days);
                $decoded = json_decode($updated->working_days, true);
                error_log('Décodé: ' . print_r($decoded, true));
            }
        }
        
        error_log('=== FIN MISE À JOUR EMPLOYÉ ===');
        return $result;
    }

    public static function delete($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}ib_employees", ['id' => intval($id)]);
    }

    public static function get_top_employees($limit = 5) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT e.*, 
            COUNT(b.id) as booking_count,
            COALESCE(AVG(f.rating), 0) as satisfaction
            FROM {$wpdb->prefix}ib_employees e 
            LEFT JOIN {$wpdb->prefix}ib_bookings b ON e.id = b.employee_id 
            LEFT JOIN {$wpdb->prefix}ib_feedback f ON b.id = f.booking_id
            GROUP BY e.id 
            ORDER BY booking_count DESC 
            LIMIT %d",
            $limit
        ));
    }

    // Get working days for an employee
    public static function get_working_days($employee_id) {
        global $wpdb;
        
        // Récupérer les données brutes de la base de données
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT working_days FROM {$wpdb->prefix}ib_employees WHERE id = %d",
            $employee_id
        ));
        
        error_log('Données brutes des jours de travail pour l\'employé ' . $employee_id . ': ' . $result);
        
        // Si pas de résultat, retourner tous les jours par défaut
        if (empty($result)) {
            error_log('Aucun jour de travail trouvé, utilisation des valeurs par défaut');
            return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        }
        
        // Décoder le JSON
        $decoded = json_decode($result, true);
        
        // Vérifier les erreurs de décodage
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Erreur de décodage JSON: ' . json_last_error_msg() . ' - Valeur: ' . $result);
            return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        }
        
        // S'assurer que c'est un tableau
        if (!is_array($decoded)) {
            error_log('Les jours de travail ne sont pas un tableau: ' . print_r($decoded, true));
            return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        }
        
        error_log('Jours de travail décodés: ' . print_r($decoded, true));
        return $decoded;
    }
    
    // Check if employee works on a specific day
    public static function works_on_day($employee_id, $day) {
        $working_days = self::get_working_days($employee_id);
        
        // Convertir le jour en minuscules et supprimer les espaces
        $day = strtolower(trim($day));
        
        // Tableau de correspondance des jours
        $day_mapping = [
            // Format anglais
            'monday' => 'monday',
            'tuesday' => 'tuesday',
            'wednesday' => 'wednesday',
            'thursday' => 'thursday',
            'friday' => 'friday',
            'saturday' => 'saturday',
            'sunday' => 'sunday',
            // Format français
            'lundi' => 'monday',
            'mardi' => 'tuesday',
            'mercredi' => 'wednesday',
            'jeudi' => 'thursday',
            'vendredi' => 'friday',
            'samedi' => 'saturday',
            'dimanche' => 'sunday'
        ];
        
        // Normaliser le jour demandé
        $normalized_day = isset($day_mapping[$day]) ? $day_mapping[$day] : $day;
        
        // Normaliser les jours de travail
        $normalized_working_days = [];
        foreach ($working_days as $working_day) {
            $working_day = strtolower(trim($working_day));
            $normalized_working_days[] = isset($day_mapping[$working_day]) ? $day_mapping[$working_day] : $working_day;
        }
        
        // Vérifier si le jour normalisé est dans les jours de travail normalisés
        $result = in_array($normalized_day, $normalized_working_days);
        
        error_log(sprintf(
            "Vérification jour de travail - Employé: %d, Jour demandé: %s, Jour normalisé: %s, Jours de travail: %s, Résultat: %s",
            $employee_id,
            $day,
            $normalized_day,
            json_encode($working_days),
            $result ? 'Oui' : 'Non'
        ));
        
        return $result;
    }
}

// Fin du fichier, ne rien ajouter après cette ligne pour éviter toute sortie parasite.
