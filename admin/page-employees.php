<?php
if (!defined('ABSPATH')) exit;
require_once plugin_dir_path(__FILE__) . '../includes/class-employees.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-employee-absences.php';
require_once plugin_dir_path(__FILE__) . '../includes/class-logs.php';
// Traitement ajout employé
if (isset($_POST['add_employee'])) {
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $specialty = isset($_POST['specialty']) ? sanitize_text_field($_POST['specialty']) : '';
    $role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
    $created_at = isset($_POST['created_at']) ? sanitize_text_field($_POST['created_at']) : date('Y-m-d');
    $working_days = isset($_POST['working_days']) && is_array($_POST['working_days']) ? $_POST['working_days'] : [];
    
    if (!$name || !$email) {
        echo '<div class="notice notice-error" style="margin-bottom:1.5em;"><p>Veuillez remplir tous les champs obligatoires.</p></div>';
    } else {
        $result = IB_Employees::add($name, $email, $phone, $specialty, $role, $working_days, $created_at);
        if ($result === false) {
            echo '<div class="notice notice-error" style="margin-bottom:1.5em;"><p>Erreur : cet employé existe déjà ou problème d\'insertion.</p></div>';
        } else {
            IB_Logs::add(get_current_user_id(), 'ajout_employe', json_encode(['employee_id' => $result, 'name' => $name]));
            echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Employé ajouté avec succès.</p></div>';
        }
    }
}
// Traitement édition employé
if (isset($_POST['update_employee'])) {
    $id = intval($_POST['employee_id']);
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $specialty = sanitize_text_field($_POST['specialty']);
    $role = sanitize_text_field($_POST['role']);
    $created_at = sanitize_text_field($_POST['created_at']);
    $working_days = isset($_POST['working_days']) && is_array($_POST['working_days']) ? $_POST['working_days'] : [];
    
    // Log pour débogage
    error_log('Jours de travail reçus dans le formulaire: ' . print_r($working_days, true));
    
    $result = IB_Employees::update($id, $name, $email, $phone, $specialty, $role, $working_days, $created_at);
    
    // Vérifier le résultat de la mise à jour
    if ($result === false) {
        error_log('Erreur lors de la mise à jour de l\'employé ID: ' . $id);
        echo '<div class="notice notice-error" style="margin-bottom:1.5em;"><p>Erreur lors de la mise à jour de l\'employé.</p></div>';
    } else {
        $updated_employee = IB_Employees::get_by_id($id);
        error_log('Jours de travail après mise à jour: ' . print_r($updated_employee->working_days, true));
        IB_Logs::add(get_current_user_id(), 'modif_employe', json_encode(['employee_id' => $id, 'name' => $name]));
        echo '<script>window.location.href = "admin.php?page=institut-booking-employees&updated=1";</script>';
        exit;
    }
}
// Traitement suppression employé
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    IB_Employees::delete((int)$_GET['id']);
    IB_Logs::add(get_current_user_id(), 'suppression_employe', json_encode(['employee_id' => $_GET['id']]));
    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Employé supprimé avec succès.</p></div>';
}

// Traitement des actions d'absence
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_absence') {
        $employee_id = intval($_POST['employee_id']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $type = sanitize_text_field($_POST['type']);
        $reason = sanitize_textarea_field($_POST['reason']);
        $status = sanitize_text_field($_POST['status']);

        if ($employee_id && $start_date && $end_date && $type) {
            // Validation des dates
            if (strtotime($end_date) < strtotime($start_date)) {
                echo '<div class="notice notice-error" style="margin-bottom:1.5em;"><p>Erreur : la date de fin doit être postérieure ou égale à la date de début.</p></div>';
            } else if (isset($_POST['absence_id']) && !empty($_POST['absence_id'])) {
                // Mise à jour
                $absence_id = intval($_POST['absence_id']);
                $result = IB_Employee_Absences::update($absence_id, $employee_id, $start_date, $end_date, $type, $reason, $status);
                if ($result !== false) {
                    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Absence mise à jour avec succès.</p></div>';
                } else {
                    echo '<div class="notice notice-error" style="margin-bottom:1.5em;"><p>Erreur : conflit avec une autre absence ou problème de mise à jour.</p></div>';
                }
            } else {
                // Ajout
                $result = IB_Employee_Absences::add($employee_id, $start_date, $end_date, $type, $reason, $status);
                if ($result !== false) {
                    IB_Logs::add(get_current_user_id(), 'ajout_absence', json_encode(['employee_id' => $employee_id, 'start_date' => $start_date, 'end_date' => $end_date, 'type' => $type]));
                    echo '<div class="notice notice-success" style="margin-bottom:1.5em;"><p>Absence ajoutée avec succès.</p></div>';
                } else {
                    echo '<div class="notice notice-error" style="margin-bottom:1.5em;"><p>Erreur : conflit avec une autre absence ou problème d\'insertion.</p></div>';
                }
            }
            } // Fermeture du else if pour la validation des dates
        } else {
            echo '<div class="notice notice-error" style="margin-bottom:1.5em;"><p>Veuillez remplir tous les champs obligatoires.</p></div>';
        }
    }
}

$employees = IB_Employees::get_all();
$edit_employee = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_employee = IB_Employees::get_by_id((int)$_GET['id']);
}
?>
<div class="ib-employees-page">
    <div class="ib-employees-content">
        <div class="ib-admin-section-header" style="display:flex;align-items:center;gap:1.2rem;justify-content:flex-start;">
            <div style="font-size:1.18rem;font-weight:700;letter-spacing:-0.5px;color:#222;padding-bottom:0.7rem;">Gérer les employés</div>
            <button class="ib-btn accent" id="btn-add-employee" type="button">+ Ajouter un employé</button>
        </div>
        <?php if (isset($_GET['success'])): ?>
            <div class="notice notice-success" style="margin-bottom:1.5em;"><p>Employé enregistré avec succès.</p></div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="notice notice-success" style="margin-bottom:1.5em;"><p>Employé supprimé avec succès.</p></div>
        <?php endif; ?>
        <?php if (empty($employees)): ?>
            <div style="padding:2em;text-align:center;color:#888;">Aucun employé trouvé.</div>
        <?php else: ?>
        <table class="ib-admin-table" style="width:100%;max-width:none;margin:0;">
            <thead>
                <tr>
                    <th>Avatar</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Spécialité</th>
                    <th>Rôle</th>
                    <th>Jours de repos</th>
                    <th>Date d'ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($employees as $employee): ?>
                <tr>
                    <td>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($employee->name); ?>&background=e9aebc&color=fff&rounded=true" alt="Avatar" style="width:38px;height:38px;border-radius:50%;object-fit:cover;box-shadow:0 2px 8px #e5e7eb33;">
                    </td>
                    <td><?php echo esc_html($employee->name); ?></td>
                    <td><?php echo esc_html($employee->email); ?></td>
                    <td><?php echo esc_html($employee->phone); ?></td>
                    <td><?php echo isset($employee->specialty) ? esc_html($employee->specialty) : '-'; ?></td>
                    <td><?php echo isset($employee->role) ? esc_html($employee->role) : '-'; ?></td>
                    <td>
                        <?php 
                        if (!empty($employee->working_days)) {
                            $days_map = [
                                'monday' => 'Lun',
                                'tuesday' => 'Mar',
                                'wednesday' => 'Mer',
                                'thursday' => 'Jeu',
                                'friday' => 'Ven',
                                'saturday' => 'Sam',
                                'sunday' => 'Dim'
                            ];
                            
                            $working_days = json_decode($employee->working_days, true);
                            $all_days = array_keys($days_map);
                            $off_days = array_diff($all_days, (array)$working_days);
                            
                            if (!empty($off_days)) {
                                $off_days_labels = array_map(function($day) use ($days_map) {
                                    return $days_map[$day] ?? $day;
                                }, $off_days);
                                
                                echo '<div style="display: flex; flex-wrap: wrap; gap: 4px;">';
                                foreach ($off_days as $day) {
                                    echo '<span style="background: #f8d7da; color: #721c24; font-size: 0.8em; padding: 2px 6px; border-radius: 3px; white-space: nowrap;">' . ($days_map[$day] ?? $day) . '</span>';
                                }
                                echo '</div>';
                            } else {
                                echo '<span style="color:#888;">Aucun</span>';
                            }
                        } else {
                            echo '<span style="color:#888;">Non défini</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo isset($employee->created_at) ? date('d/m/Y', strtotime($employee->created_at)) : '-'; ?></td>
                    <td>
                        <div class="ib-action-btns" style="display:flex;gap:0.7em;align-items:center;">
                            <a href="admin.php?page=institut-booking-employees&action=edit&id=<?php echo $employee->id; ?>" class="ib-icon-btn edit" title="Éditer">
                                <svg width="22" height="22" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19.5 3 21l1.5-4L16.5 3.5z"/></svg>
                            </a>
                            <a href="admin.php?page=institut-booking-employees&action=delete&id=<?php echo $employee->id; ?>" class="ib-icon-btn delete" title="Supprimer" onclick="return confirm('Supprimer cet employé ?')">
                                <svg width="22" height="22" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- SECTION CALENDRIER DES ABSENCES -->
        <div class="ib-absences-section" style="margin-top: 3rem;">
            <div class="ib-admin-section-header" style="display:flex;align-items:center;gap:1.2rem;justify-content:space-between;">
                <div style="font-size:1.18rem;font-weight:700;letter-spacing:-0.5px;color:#222;padding-bottom:0.7rem;">Calendrier des absences</div>
                <div style="display:flex;gap:1rem;align-items:center;">
                    <select id="absence-employee-filter" class="ib-input" style="min-width:200px;">
                        <option value="">Tous les employés</option>
                        <?php foreach($employees as $employee): ?>
                            <option value="<?php echo $employee->id; ?>"><?php echo esc_html($employee->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="ib-btn accent" id="btn-add-absence" type="button">+ Ajouter une absence</button>
                </div>
            </div>

            <!-- Calendrier -->
            <div id="absence-calendar" class="ib-absence-calendar">
                <!-- Le calendrier sera généré par JavaScript -->
            </div>
        </div>

        <!-- MODAL BACKDROP POUR AJOUT -->
        <div id="ib-modal-bg-add-employee" class="ib-modal-bg" style="display:none;"></div>
        <!-- FORMULAIRE MODAL D'AJOUT -->
        <div id="ib-add-employee-form" class="ib-modal" style="display:none;">
            <button class="ib-modal-close" type="button" onclick="closeAddEmployeeModal()">&times;</button>
            <div class="ib-form-title">Ajouter un employé</div>
            <form method="post">
                <div class="ib-form-group">
                    <input class="ib-input" name="name" id="add_employee_name" placeholder=" " required>
                    <label for="add_employee_name">Nom</label>
                </div>
                <div class="ib-form-group">
                    <input class="ib-input" name="email" id="add_employee_email" type="email" placeholder=" " required>
                    <label for="add_employee_email">Email</label>
                </div>
                <div class="ib-form-group">
                    <input class="ib-input" name="phone" id="add_employee_phone" type="tel" placeholder=" ">
                    <label for="add_employee_phone">Téléphone</label>
                </div>
                <div class="ib-form-group">
                    <input class="ib-input" name="specialty" id="add_employee_specialty" placeholder=" ">
                    <label for="add_employee_specialty">Spécialité</label>
                </div>
                <div class="ib-form-group">
                    <select class="ib-input" name="role" id="add_employee_role" required>
                        <option value="">Sélectionner un rôle</option>
                        <option value="Employé">Employé</option>
                        <option value="Manager">Manager</option>
                        <option value="Réceptionniste">Réceptionniste</option>
                    </select>
                    <label for="add_employee_role">Rôle</label>
                </div>
                <div class="ib-form-group">
                    <input class="ib-input" name="created_at" id="add_employee_created_at" type="date" placeholder=" " value="<?php echo date('Y-m-d'); ?>" required>
                    <label for="add_employee_created_at">Date d'ajout</label>
                </div>
                <div class="working-days-section">
                    <h4>Jours de travail</h4>
                    <div class="working-days-grid">
                        <?php 
                        $days = [
                            'monday' => 'Lundi',
                            'tuesday' => 'Mardi',
                            'wednesday' => 'Mercredi',
                            'thursday' => 'Jeudi',
                            'friday' => 'Vendredi',
                            'saturday' => 'Samedi',
                            'sunday' => 'Dimanche'
                        ];
                        foreach ($days as $key => $label): 
                            $is_checked = in_array($key, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? 'checked' : '';
                        ?>
                        <label class="working-day-checkbox">
                            <input type="checkbox" name="working_days[]" value="<?php echo $key; ?>" <?php echo $is_checked; ?>>
                            <span><?php echo $label; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="ib-form-group" style="margin-top:1.2em;display:flex;gap:1em;">
                    <button class="ib-btn accent" type="submit" name="add_employee">Ajouter</button>
                    <button type="button" class="ib-btn cancel" onclick="closeAddEmployeeModal()">Annuler</button>
                </div>
            </form>
        </div>
        <?php if ($edit_employee): ?>
        <div id="ib-modal-bg-edit-employee" class="ib-modal-bg" style="display:block;"></div>
        <div id="ib-edit-employee-form" class="ib-modal" style="display:block;">
            <button class="ib-modal-close" type="button" onclick="window.location.href='admin.php?page=institut-booking-employees'">&times;</button>
            <div class="ib-form-title">Modifier l'employé</div>
            <form method="post" autocomplete="off">
                <input type="hidden" name="employee_id" value="<?php echo $edit_employee->id; ?>">
                <div class="ib-form-group">
                    <input class="ib-input" name="name" id="edit_employee_name" value="<?php echo esc_attr($edit_employee->name); ?>" placeholder=" " required>
                    <label for="edit_employee_name">Nom</label>
                </div>
                <div class="ib-form-group">
                    <input class="ib-input" name="email" id="edit_employee_email" type="email" value="<?php echo esc_attr($edit_employee->email); ?>" placeholder=" " required>
                    <label for="edit_employee_email">Email</label>
                </div>
                <div class="ib-form-group">
                    <input class="ib-input" name="phone" id="edit_employee_phone" type="tel" value="<?php echo esc_attr($edit_employee->phone); ?>" placeholder=" ">
                    <label for="edit_employee_phone">Téléphone</label>
                </div>
                <div class="ib-form-group">
                    <input class="ib-input" name="specialty" id="edit_employee_specialty" value="<?php echo isset($edit_employee->specialty) ? esc_attr($edit_employee->specialty) : ''; ?>" placeholder=" ">
                    <label for="edit_employee_specialty">Spécialité</label>
                </div>
                <div class="ib-form-group">
                    <select class="ib-input" name="role" id="edit_employee_role" required>
                        <option value="">Sélectionner un rôle</option>
                        <option value="Employé" <?php if(isset($edit_employee->role) && $edit_employee->role=="Employé") echo 'selected'; ?>>Employé</option>
                        <option value="Manager" <?php if(isset($edit_employee->role) && $edit_employee->role=="Manager") echo 'selected'; ?>>Manager</option>
                        <option value="Réceptionniste" <?php if(isset($edit_employee->role) && $edit_employee->role=="Réceptionniste") echo 'selected'; ?>>Réceptionniste</option>
                    </select>
                    <label for="edit_employee_role">Rôle</label>
                </div>
                <div class="ib-form-group">
                    <input class="ib-input" name="created_at" id="edit_employee_created_at" type="date" placeholder=" " value="<?php echo isset($edit_employee->created_at) ? date('Y-m-d', strtotime($edit_employee->created_at)) : date('Y-m-d'); ?>" required>
                    <label for="edit_employee_created_at">Date d'ajout</label>
                </div>
                <div class="working-days-section">
                    <h4>Jours de travail</h4>
                    <div class="working-days-grid">
                        <?php 
                        // Log les données brutes de l'employé
                        error_log('Données brutes de l\'employé: ' . print_r($edit_employee, true));
                        
                        // Récupérer les jours de travail depuis la base de données
                        $working_days = [];
                        if (!empty($edit_employee->working_days)) {
                            error_log('Données brutes des jours de travail: ' . $edit_employee->working_days);
                            $working_days = json_decode($edit_employee->working_days, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                error_log('Erreur de décodage des jours de travail: ' . json_last_error_msg());
                                $working_days = [];
                            }
                        } else {
                            error_log('Aucun jour de travail défini pour cet employé');
                        }
                        
                        // Si aucun jour n'est défini, définir tous les jours par défaut
                        if (empty($working_days)) {
                            $working_days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            error_log('Utilisation des jours par défaut: ' . print_r($working_days, true));
                        }
                        
                        $days = [
                            'monday' => 'Lundi',
                            'tuesday' => 'Mardi',
                            'wednesday' => 'Mercredi',
                            'thursday' => 'Jeudi',
                            'friday' => 'Vendredi',
                            'saturday' => 'Samedi',
                            'sunday' => 'Dimanche'
                        ];
                        
                        // Log pour débogage
                        error_log('Jours de travail pour l\'affichage: ' . print_r($working_days, true));
                        
                        foreach ($days as $key => $label): 
                            $is_checked = in_array($key, (array)$working_days);
                            error_log(sprintf(
                                'Jour: %s, Clé: %s, Est coché: %s', 
                                $label, 
                                $key, 
                                $is_checked ? 'Oui' : 'Non'
                            ));
                        ?>
                        <label class="working-day-checkbox">
                            <input type="checkbox" 
                                   name="working_days[]" 
                                   value="<?php echo esc_attr($key); ?>" 
                                   <?php echo $is_checked ? 'checked="checked"' : ''; ?>>
                            <span><?php echo esc_html($label); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="ib-form-group" style="margin-top:1.2em;display:flex;gap:1em;">
                    <button class="ib-btn accent" type="submit" name="update_employee">Enregistrer</button>
                    <button type="button" class="ib-btn cancel" onclick="window.location.href='admin.php?page=institut-booking-employees'">Annuler</button>
                </div>
            </form>
        </div>
        <script>
            document.body.style.overflow = 'hidden';
            document.getElementById('ib-modal-bg-edit-employee').onclick = function() {
                window.location.href = 'admin.php?page=institut-booking-employees';
            };
        </script>
        <?php endif; ?>

        <!-- MODALS POUR GESTION DES ABSENCES -->
        <!-- Modal backdrop pour absence -->
        <div id="ib-modal-bg-absence" class="ib-modal-bg" style="display:none;"></div>

        <!-- Modal d'ajout d'absence -->
        <div id="ib-add-absence-form" class="ib-modal" style="display:none;">
            <button class="ib-modal-close" type="button" onclick="closeAbsenceModal()">&times;</button>
            <div class="ib-form-title">Ajouter une absence</div>
            <form id="absence-form" method="post">
                <input type="hidden" name="action" value="add_absence">
                <input type="hidden" name="absence_id" value="">

                <div class="ib-form-group">
                    <select class="ib-input" name="employee_id" id="absence_employee_id" required>
                        <option value="">Sélectionner un employé</option>
                        <?php foreach($employees as $employee): ?>
                            <option value="<?php echo $employee->id; ?>"><?php echo esc_html($employee->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="absence_employee_id">Employé</label>
                </div>

                <div class="ib-form-group">
                    <input class="ib-input" name="start_date" id="absence_start_date" type="date" placeholder=" " required>
                    <label for="absence_start_date">Date de début</label>
                </div>

                <div class="ib-form-group">
                    <input class="ib-input" name="end_date" id="absence_end_date" type="date" placeholder=" " required>
                    <label for="absence_end_date">Date de fin</label>
                </div>

                <div class="ib-form-group">
                    <select class="ib-input" name="type" id="absence_type" required>
                        <option value="">Sélectionner un type</option>
                        <option value="absence">Absence</option>
                        <option value="conge">Congé payé</option>
                        <option value="maladie">Congé maladie</option>
                        <option value="formation">Formation</option>
                        <option value="personnel">Congé personnel</option>
                        <option value="maternite">Congé maternité</option>
                        <option value="paternite">Congé paternité</option>
                    </select>
                    <label for="absence_type">Type d'absence</label>
                </div>

                <div class="ib-form-group">
                    <select class="ib-input" name="status" id="absence_status" required>
                        <option value="approved">Approuvé</option>
                        <option value="pending">En attente</option>
                        <option value="rejected">Refusé</option>
                    </select>
                    <label for="absence_status">Statut</label>
                </div>

                <div class="ib-form-group">
                    <textarea class="ib-input" name="reason" id="absence_reason" placeholder=" " rows="3"></textarea>
                    <label for="absence_reason">Motif (optionnel)</label>
                </div>

                <div class="ib-form-group" style="margin-top:1.2em;display:flex;gap:1em;">
                    <button class="ib-btn accent" type="submit">Enregistrer</button>
                    <button type="button" class="ib-btn cancel" onclick="closeAbsenceModal()">Annuler</button>
                    <button type="button" class="ib-btn delete" id="delete-absence-btn" style="display:none;" onclick="deleteAbsence()">Supprimer</button>
                </div>
            </form>
        </div>

    </div>
</div>
<script>
function openAddEmployeeModal() {
    document.getElementById('ib-modal-bg-add-employee').style.display = 'block';
    document.getElementById('ib-add-employee-form').style.display = 'block';
}
function closeAddEmployeeModal() {
    document.getElementById('ib-modal-bg-add-employee').style.display = 'none';
    document.getElementById('ib-add-employee-form').style.display = 'none';
}
document.getElementById('btn-add-employee').onclick = openAddEmployeeModal;
document.getElementById('ib-modal-bg-add-employee').onclick = closeAddEmployeeModal;
</script>
