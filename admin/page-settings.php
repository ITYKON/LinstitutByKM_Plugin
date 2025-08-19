<?php
// Page de réglages avancés avec personnalisation des couleurs, logo, nom, horaires...
echo "<!-- DEBUG: Page settings chargée -->";
    // Traitement des formulaires AVANT tout affichage
    $message = '';
    $message_type = '';
    
    if (isset($_POST['save_settings'])) {
        update_option('ib_color_primary', sanitize_hex_color($_POST['color_primary']));
        update_option('ib_color_accent', sanitize_hex_color($_POST['color_accent']));
        update_option('ib_color_secondary', sanitize_hex_color($_POST['color_secondary']));
        update_option('ib_color_danger', sanitize_hex_color($_POST['color_danger']));
        update_option('ib_sidebar_bg', sanitize_hex_color($_POST['sidebar_bg']));
        update_option('ib_sidebar_text', sanitize_hex_color($_POST['sidebar_text']));
        update_option('ib_company_name', sanitize_text_field($_POST['company_name']));
        update_option('ib_text_main', sanitize_hex_color($_POST['text_main']));
        // Gestion du logo : ne pas écraser si pas de nouveau fichier, et sécuriser l'accès à $_FILES
        if (isset($_FILES['company_logo']) && !empty($_FILES['company_logo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            $uploaded = wp_handle_upload($_FILES['company_logo'], ['test_form' => false]);
            if (!isset($uploaded['error']) && isset($uploaded['url'])) {
                update_option('ib_company_logo', esc_url_raw($uploaded['url']));
            } else if (isset($uploaded['error'])) {
                $message = 'Erreur lors de l\'upload du logo : ' . $uploaded['error'];
                $message_type = 'error';
            }
        }
        if (empty($message)) {
            $message = 'Réglages enregistrés !';
            $message_type = 'success';
        }
    }
    if (isset($_POST['ib_save_opening_hours'])) {
        update_option('ib_opening_time', sanitize_text_field($_POST['ib_opening_time']));
        update_option('ib_closing_time', sanitize_text_field($_POST['ib_closing_time']));
        $message = 'Horaires d\'ouverture enregistrés !';
        $message_type = 'success';
    }
    if (isset($_POST['ib_add_offday'])) {
        $offdays = get_option('ib_company_offdays', []);
        $date = sanitize_text_field($_POST['offday']);
        if ($date && !in_array($date, $offdays)) {
            $offdays[] = $date;
            update_option('ib_company_offdays', $offdays);
            $message = 'Jour off ajouté !';
            $message_type = 'success';
        } else {
            $message = 'Date invalide ou déjà existante';
            $message_type = 'error';
        }
    }
    if (isset($_GET['remove_offday'])) {
        $offdays = get_option('ib_company_offdays', []);
        $date = sanitize_text_field($_GET['remove_offday']);
        $offdays = array_diff($offdays, [$date]);
        update_option('ib_company_offdays', $offdays);
        $message = 'Jour off supprimé !';
        $message_type = 'success';
    }
    if (isset($_POST['ib_add_specialday'])) {
        $specials = get_option('ib_company_specialdays', []);
        $date = sanitize_text_field($_POST['specialday']);
        $start = sanitize_text_field($_POST['special_start']);
        $end = sanitize_text_field($_POST['special_end']);
        if ($date && $start && $end) {
            $specials[$date] = ['start'=>$start, 'end'=>$end];
            update_option('ib_company_specialdays', $specials);
            $message = 'Journée spéciale ajoutée !';
            $message_type = 'success';
        } else {
            $message = 'Veuillez remplir tous les champs';
            $message_type = 'error';
        }
    }
    if (isset($_GET['remove_specialday'])) {
        $specials = get_option('ib_company_specialdays', []);
        $date = sanitize_text_field($_GET['remove_specialday']);
        unset($specials[$date]);
        update_option('ib_company_specialdays', $specials);
        $message = 'Journée spéciale supprimée !';
        $message_type = 'success';
    }
    // Toujours relire les options APRÈS toute sauvegarde/redirection
    $color_primary = get_option('ib_color_primary', '#3a7afe');
    $color_accent = get_option('ib_color_accent', '#00c48c');
    $color_secondary = get_option('ib_color_secondary', '#f7fafd');
    $color_danger = get_option('ib_color_danger', '#ff4f64');
    $sidebar_bg = get_option('ib_sidebar_bg', '#23272f');
    $sidebar_text = get_option('ib_sidebar_text', '#fff');
    $company_name = get_option('ib_company_name', 'Mon Institut');
    $company_logo = get_option('ib_company_logo', '');
    $opening_time = get_option('ib_opening_time', '09:00');
    $closing_time = get_option('ib_closing_time', '17:00');
    $offdays = get_option('ib_company_offdays', []);
    $specials = get_option('ib_company_specialdays', []);
    $text_main = get_option('ib_text_main', '#1e293b');
    ?>
    <style>
    :root {
        --primary: <?php echo esc_attr($color_primary); ?>;
        --accent: <?php echo esc_attr($color_accent); ?>;
        --secondary: <?php echo esc_attr($color_secondary); ?>;
        --danger: <?php echo esc_attr($color_danger); ?>;
        --text-main: <?php echo esc_attr($text_main); ?>;
        --bg: #fff;
        --shadow: rgba(37,99,235,0.08);
    }
    .ib-settings-container { max-width: 900px; margin: 2.5em auto 2em auto; background: #fff; border-radius: 18px; box-shadow: 0 2px 24px rgba(37,99,235,0.08); padding: 2.5em 2.5em 2em 2.5em; }
    @media (max-width: 700px) { .ib-settings-container { padding: 1.2em 0.5em; } }
    .ib-settings-header { display: flex; align-items: center; gap: 1.5em; margin-bottom: 2em; color: var(--text-main); flex-wrap: nowrap; }
    .ib-settings-logo { max-width: 90px; max-height: 90px; border-radius: 14px; box-shadow: 0 2px 8px #e0e7ef; background: #f7fafd; padding: 0.5em; }
    .ib-settings-summary { color: var(--text-main); min-width: 0; flex: 1; }
    .ib-badges-row { display: flex; gap: 0.7em; flex-wrap: wrap; margin-top: 0.5em; }
    .ib-badge-color { width: 28px; height: 28px; border-radius: 50%; border: 2.5px solid #dbeafe; display: inline-block; box-shadow: 0 1px 4px #e0e7ef; }
    .ib-form-title { font-size: 1.25em; font-weight: 800; color: var(--primary); margin: 2.2em 0 1.1em 0; display: flex; align-items: center; gap: 0.5em; letter-spacing: -0.5px; }
    .ib-form-group { display: flex; flex-direction: column; gap: 0.3em; margin-bottom: 1.2em; }
    .ib-form-row { display: flex; gap: 1.5em; flex-wrap: wrap; }
    .ib-input, select { border-radius: 10px; border: 1.5px solid var(--secondary); background: var(--bg); padding: 0.7em 1em; font-size: 1.08em; transition: border 0.2s; }
    .ib-input:focus, select:focus { border: 2px solid var(--primary); outline: none; }
    .ib-btn, .button.button-primary { background: linear-gradient(90deg, var(--primary) 80%, var(--accent) 100%); color: #fff; border: none; border-radius: 10px; padding: 0.7em 2.2em; font-size: 1.1em; font-weight: 700; box-shadow: 0 2px 8px var(--shadow); cursor: pointer; margin-top: 1em; transition: background 0.18s, box-shadow 0.18s, color 0.18s; }
    .ib-btn:hover, .button.button-primary:hover { background: linear-gradient(90deg, var(--accent) 80%, var(--primary) 100%); }
    .ib-toast { border-radius: 12px; padding: 1em 1.5em; font-size: 1.1em; margin-bottom: 1.5em; box-shadow: 0 2px 12px rgba(37,99,235,0.08); background: #fff; color: var(--primary); border: 1.5px solid var(--primary); display: flex; align-items: center; gap: 0.7em; }
    .ib-toast.success { background: #e6f7ff; color: var(--primary); border: 1.5px solid var(--primary); }
    .ib-toast.error { background: #fff3f3; color: var(--danger); border: 1.5px solid var(--danger); }
    .ib-settings-logo-upload { display: flex; align-items: center; gap: 1em; }
    .ib-settings-logo-upload input[type="file"] { display: none; }
    .ib-logo-upload-label { background: var(--secondary); color: var(--primary); border-radius: 8px; padding: 0.5em 1.2em; cursor: pointer; font-weight: 600; border: 1.5px solid var(--primary); transition: background 0.18s, color 0.18s; }
    .ib-logo-upload-label:hover { background: var(--primary); color: #fff; }
    .ib-offdays-list, .ib-specialdays-list { margin: 1em 0 2em 0; padding-left: 0; list-style: none; }
    .ib-offdays-list li, .ib-specialdays-list li { background: var(--secondary); color: var(--text-main); border-radius: 8px; padding: 0.5em 1.2em; margin-bottom: 0.5em; display: flex; align-items: center; justify-content: space-between; }
    .ib-offdays-list a, .ib-specialdays-list a { color: var(--danger); font-size: 1.2em; margin-left: 1em; text-decoration: none; }
    .ib-offdays-list a:hover, .ib-specialdays-list a:hover { color: #fff; background: var(--danger); border-radius: 50%; padding: 0.1em 0.4em; }
    @media (max-width: 600px) { .ib-form-row { flex-direction: column; gap: 0.7em; } }
    </style>
    <div class="ib-settings-container">
      <div class="ib-settings-header">
        <?php if ($company_logo): ?>
          <img src="<?php echo esc_url($company_logo); ?>" alt="Logo" class="ib-settings-logo">
        <?php endif; ?>
        <div class="ib-settings-summary">
          <div style="font-size:1.3em;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><span class="dashicons dashicons-admin-home"></span> <?php echo esc_html($company_name); ?></div>
          <div class="ib-badges-row">
            <span class="ib-badge-color" title="Couleur principale" style="background:<?php echo esc_attr($color_primary); ?>;"></span>
            <span class="ib-badge-color" title="Accent" style="background:<?php echo esc_attr($color_accent); ?>;"></span>
            <span class="ib-badge-color" title="Secondaire" style="background:<?php echo esc_attr($color_secondary); ?>;"></span>
            <span class="ib-badge-color" title="Danger" style="background:<?php echo esc_attr($color_danger); ?>;"></span>
            <span class="ib-badge-color" title="Sidebar" style="background:<?php echo esc_attr($sidebar_bg); ?>;"></span>
            <span class="ib-badge-color" title="Texte sidebar" style="background:<?php echo esc_attr($sidebar_text); ?>;"></span>
          </div>
        </div>
      </div>
      <?php if (!empty($message)): ?>
        <div class="ib-toast <?php echo $message_type; ?>">
          <span class="dashicons dashicons-<?php echo $message_type === 'success' ? 'yes' : 'warning'; ?>"></span> 
          <?php echo esc_html($message); ?>
        </div>
      <?php endif; ?>
      <form class="ib-form" method="post" enctype="multipart/form-data">
        <div class="ib-form-title"><span class="dashicons dashicons-admin-home"></span> Identité de l'institut</div>
        <div class="ib-form-group">
          <label>Nom de la compagnie</label>
          <input class="ib-input" name="company_name" value="<?php echo esc_attr($company_name); ?>" required>
        </div>
        <div class="ib-form-group ib-settings-logo-upload">
          <label for="company_logo" class="ib-logo-upload-label"><span class="dashicons dashicons-upload"></span> Choisir un logo</label>
          <input class="ib-input" id="company_logo" name="company_logo" type="file" accept="image/*">
          <?php if ($company_logo): ?>
            <img src="<?php echo esc_url($company_logo); ?>" alt="Logo" style="max-width:60px;max-height:60px;margin-top:0.5em;border-radius:8px;">
          <?php endif; ?>
        </div>
        <div class="ib-form-title"><span class="dashicons dashicons-admin-customizer"></span> Personnalisation des couleurs</div>
        <div class="ib-form-row">
          <div class="ib-form-group"><label>Couleur principale</label><input class="ib-input" name="color_primary" type="color" value="<?php echo esc_attr($color_primary); ?>" style="height:48px;"></div>
          <div class="ib-form-group"><label>Accent</label><input class="ib-input" name="color_accent" type="color" value="<?php echo esc_attr($color_accent); ?>" style="height:48px;"></div>
          <div class="ib-form-group"><label>Secondaire</label><input class="ib-input" name="color_secondary" type="color" value="<?php echo esc_attr($color_secondary); ?>" style="height:48px;"></div>
          <div class="ib-form-group"><label>Danger</label><input class="ib-input" name="color_danger" type="color" value="<?php echo esc_attr($color_danger); ?>" style="height:48px;"></div>
          <div class="ib-form-group"><label>Sidebar</label><input class="ib-input" name="sidebar_bg" type="color" value="<?php echo esc_attr($sidebar_bg); ?>" style="height:48px;"></div>
          <div class="ib-form-group"><label>Texte sidebar</label><input class="ib-input" name="sidebar_text" type="color" value="<?php echo esc_attr($sidebar_text); ?>" style="height:48px;"></div>
          <div class="ib-form-group"><label>Texte principal</label><input class="ib-input" name="text_main" type="color" value="<?php echo esc_attr($text_main); ?>" style="height:48px;"></div>
        </div>
        <button class="ib-btn accent" type="submit" name="save_settings" style="margin-top:1.5em;"><span class="dashicons dashicons-yes"></span> Enregistrer</button>
      </form>
      <div class="ib-form-title"><span class="dashicons dashicons-clock"></span> Horaires d'ouverture</div>
      <form method="post" class="ib-form-row" style="max-width:400px;margin-bottom:2em;align-items:end;">
        <div class="ib-form-group"><label for="ib-opening-time">Heure d'ouverture</label><input class="ib-input" id="ib-opening-time" name="ib_opening_time" type="time" value="<?php echo esc_attr($opening_time); ?>" required></div>
        <div class="ib-form-group"><label for="ib-closing-time">Heure de fermeture</label><input class="ib-input" id="ib-closing-time" name="ib_closing_time" type="time" value="<?php echo esc_attr($closing_time); ?>" required></div>
        <button class="ib-btn accent" type="submit" name="ib_save_opening_hours"><span class="dashicons dashicons-yes"></span> Enregistrer</button>
      </form>
      <div class="ib-form-title"><span class="dashicons dashicons-calendar-alt"></span> Jours off (fermeture exceptionnelle)</div>
      <form method="post" class="ib-form-row" style="max-width:400px;align-items:end;">
        <input type="date" name="offday" class="ib-input">
        <button class="ib-btn" type="submit" name="ib_add_offday"><span class="dashicons dashicons-plus"></span> Ajouter</button>
      </form>
      <?php if (!empty($offdays)): ?>
        <ul class="ib-offdays-list">
          <?php foreach($offdays as $d): ?>
            <li><?php echo esc_html($d); ?> <a href="?page=institut-booking-settings&remove_offday=<?php echo esc_attr($d); ?>" onclick="return confirm('Supprimer ce jour off ?')">❌</a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <div class="ib-form-title"><span class="dashicons dashicons-star-filled"></span> Journées spéciales</div>
      <form method="post" class="ib-form-row" style="max-width:600px;align-items:end;">
        <input type="date" name="specialday" class="ib-input">
        <input type="time" name="special_start" class="ib-input">
        <input type="time" name="special_end" class="ib-input">
        <button class="ib-btn" type="submit" name="ib_add_specialday"><span class="dashicons dashicons-plus"></span> Ajouter</button>
      </form>
      <?php if (!empty($specials)): ?>
        <ul class="ib-specialdays-list">
          <?php foreach($specials as $d=>$h): ?>
            <li><?php echo esc_html($d); ?> : <?php echo esc_html($h['start'].'-'.$h['end']); ?> <a href="?page=institut-booking-settings&remove_specialday=<?php echo esc_attr($d); ?>" onclick="return confirm('Supprimer cette journée spéciale ?')">❌</a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <script>
    // Drag & drop logo upload (optionnel, amélioration UX)
    document.addEventListener('DOMContentLoaded', function() {
      var logoInput = document.getElementById('company_logo');
      var label = document.querySelector('.ib-logo-upload-label');
      if (logoInput && label) {
        label.addEventListener('click', function() { logoInput.click(); });
        logoInput.addEventListener('change', function() {
          if (logoInput.files && logoInput.files[0]) {
            label.textContent = 'Logo sélectionné : ' + logoInput.files[0].name;
          }
        });
      }
      
      // Masquer automatiquement les messages de succès après 3 secondes
      var toast = document.querySelector('.ib-toast.success');
      if (toast) {
        setTimeout(function() {
          toast.style.opacity = '0';
          setTimeout(function() {
            toast.remove();
          }, 300);
        }, 3000);
      }
    });
    </script>
    <?php // Application dynamique des couleurs déjà gérée plus haut ?>

