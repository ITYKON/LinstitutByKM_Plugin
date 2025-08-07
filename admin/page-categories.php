<?php
// admin/page-categories.php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../includes/class-categories.php';
$categories = IB_Categories::get_all();
$edit_cat = null;
global $wpdb;
if (isset($_GET['edit'])) {
    $edit_cat = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_categories WHERE id = %d", (int)$_GET['edit']));
}
?>
<div class="ib-categories-admin">
    <h1><?php _e('Catégories de services', 'institut-booking'); ?></h1>
    <?php if(isset($edit_cat)): ?>
      <style>
        #ib-modal-bg-category { position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(30,40,60,0.18);z-index:1000; }
        #ib-modal-edit-category { position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;z-index:1001;padding:2.5em 2em 1.5em 2em;border-radius:22px;box-shadow:0 4px 32px rgba(58,122,254,0.18);max-width:420px;width:97vw; max-height:90vh; overflow-y:auto; }
        .ib-form-title { font-size:1.4rem;color: var(--text-main);font-weight:800;margin-bottom:1.2em;display:flex;align-items:center;gap:0.7em; }
        .ib-form-title i { color:#3a7afe;font-size:1.2em; }
        .ib-form-summary { display:flex;align-items:center;gap:1.2em;margin-bottom:1.2em;padding-bottom:1em;border-bottom:1.5px solid #e0e7ef; }
        .ib-form-summary .ib-summary-info { display:flex;flex-direction:column;gap:0.3em; }
        .ib-form-summary .ib-summary-title { font-size:1.08em;font-weight:700;color: var(--text-main); }
        .ib-form-grid { display: grid; grid-template-columns: 1fr; gap: 1.2em 2em; align-items: end; }
        .ib-form-group { display: flex; flex-direction: column; gap: 0.4em; }
        .ib-label { font-weight: 700; color: #3a7afe; margin-bottom: 0.2em; font-size:1.08em; }
        .ib-input, select { border-radius: 10px; border: 1.5px solid #dbeafe; padding: 0.7em 1em; font-size: 1.08em; background: #f8fafc; transition: border 0.2s; }
        .ib-input:focus, select:focus { border: 2px solid #3a7afe; outline: none; }
        .ib-btn.accent { background: linear-gradient(90deg,#3a7afe,#00c48c); color: #fff; border: none; border-radius: 10px; padding: 0.7em 2.2em; font-size: 1.1em; font-weight: 700; box-shadow: 0 2px 8px rgba(58,122,254,0.08); cursor: pointer; margin-top: 1em; transition: background 0.2s; }
        .ib-btn.accent:hover { background: linear-gradient(90deg,#00c48c,#3a7afe); }
        .ib-btn.cancel { background: #e0e7ef; color: #234; border: none; border-radius: 10px; padding: 0.7em 2.2em; font-size: 1.1em; font-weight: 600; margin-top: 1em; margin-left:1em; cursor:pointer; }
        .ib-btn.cancel:hover { background: #c7d2fe; color:#1e293b; }
        .ib-form-separator { height:1.5px; background:#e0e7ef; margin:2em 0 1.5em 0; border-radius:2px; }
      </style>
      <div id="ib-modal-bg-category" onclick="window.location.href='?page=ib-categories'"></div>
      <div id="ib-modal-edit-category" role="dialog" aria-modal="true" aria-labelledby="ib-edit-category-title">
        <div class="ib-form-title"><i class="dashicons dashicons-tag"></i> <span id="ib-edit-category-title"><?php _e('Modifier la catégorie', 'institut-booking'); ?></span></div>
        <div class="ib-form-summary">
          <div class="ib-summary-info">
            <div class="ib-summary-title"><?php echo esc_html($edit_cat->name); ?></div>
            <div>Couleur : <span style="display:inline-block;width:24px;height:24px;background:<?php echo esc_attr($edit_cat->color); ?>;border-radius:50%;border:1px solid #ccc;vertical-align:middle;"></span></div>
            <div>Icône : <b><?php echo esc_html($edit_cat->icon); ?></b></div>
          </div>
        </div>
        <form method="post" autocomplete="off">
          <input type="hidden" name="id" value="<?php echo esc_attr($edit_cat->id); ?>">
          <div class="ib-form-grid">
            <div class="ib-form-group">
              <label class="ib-label" for="ib-cat-name-edit"><?php _e('Nom', 'institut-booking'); ?></label>
              <input class="ib-input" id="ib-cat-name-edit" type="text" name="name" required value="<?php echo esc_attr($edit_cat->name); ?>">
            </div>
            <div class="ib-form-group">
              <label class="ib-label" for="ib-cat-color-edit"><?php _e('Couleur', 'institut-booking'); ?></label>
              <input class="ib-input" id="ib-cat-color-edit" type="color" name="color" value="<?php echo esc_attr($edit_cat->color); ?>" style="width:50px;height:40px;">
            </div>
            <div class="ib-form-group">
              <label class="ib-label" for="ib-cat-icon-edit"><?php _e('Icône', 'institut-booking'); ?></label>
              <input class="ib-input" id="ib-cat-icon-edit" type="text" name="icon" value="<?php echo esc_attr($edit_cat->icon); ?>">
            </div>
          </div>
          <div class="ib-form-separator"></div>
          <div style="margin-top:1em;display:flex;gap:1.5em;justify-content:flex-end;">
            <button type="submit" name="ib_update_category" class="ib-btn accent"><?php _e('Enregistrer', 'institut-booking'); ?></button>
            <a href="?page=ib-categories" class="ib-btn cancel"><?php _e('Annuler', 'institut-booking'); ?></a>
          </div>
        </form>
      </div>
      <script>
      document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('ib-modal-edit-category')) {
          document.body.style.overflow = 'hidden';
          var bg = document.getElementById('ib-modal-bg-category');
          if(bg) {
            bg.addEventListener('click', function() {
              document.body.style.overflow = '';
            });
          }
        }
      });
      </script>
    <?php else: ?>
    <style>
    body, .ib-categories-admin {
      background: #fbeff3 !important;
      font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
    }
    .ib-categories-admin {
      max-width: 1100px;
      margin: 0 auto;
      padding: 2.5em 1.2em 3em 1.2em;
    }
    .ib-categories-title {
      display: flex;
      align-items: center;
      gap: 0.7em;
      font-size: 2.1em;
      font-weight: 800;
      color: #22223b;
      margin-bottom: 1.7em;
      letter-spacing: -1px;
    }
    .ib-categories-title .dashicons {
      color: #e9aebc;
      font-size: 1.2em;
    }
    .ib-add-category-form {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 8px 32px #e9aebc22, 0 1.5px 6px #bfa2c733;
      padding: 2em 2em 1.5em 2em;
      display: flex;
      gap: 2em;
      align-items: end;
      margin-bottom: 2.5em;
      flex-wrap: wrap;
    }
    .ib-add-category-form label {
      color: #bfa2c7;
      font-weight: 600;
      font-size: 1.01em;
      margin-bottom: 0.3em;
    }
    .ib-add-category-form input[type="text"],
    .ib-add-category-form input[type="color"] {
      border-radius: 16px;
      border: 1.5px solid #e9aebc;
      padding: 0.9em 1.1em;
      font-size: 1.04em;
      background: rgba(255,255,255,0.85);
      color: #22223b;
      font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
      font-weight: 400;
      box-shadow: 0 2px 8px #e9aebc11;
      transition: all 0.2s;
    }
    .ib-add-category-form input[type="color"] {
      width: 48px;
      height: 48px;
      padding: 0;
      border-radius: 50%;
      border: 2px solid #e9aebc;
      box-shadow: 0 2px 8px #e9aebc22;
    }
    .ib-add-category-form input:focus {
      border-color: #e9aebc;
      outline: none;
      box-shadow: 0 0 0 3px #fbeff3;
    }
    .ib-add-category-form button[type="submit"] {
      background: #e9aebc;
      color: #fff;
      border: none;
      border-radius: 16px;
      padding: 1em 2.2em;
      font-weight: 700;
      font-size: 1.13em;
      box-shadow: 0 4px 16px #e9aebc22;
      cursor: pointer;
      transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
    }
    .ib-add-category-form button[type="submit"]:hover {
      background: #e38ca6;
      box-shadow: 0 8px 24px #e9aebc33;
      transform: translateY(-2px) scale(1.03);
    }
    .ib-categories-table-card {
      background: #fff;
      border-radius: 22px;
      box-shadow: 0 8px 32px #e9aebc22, 0 1.5px 6px #bfa2c733;
      padding: 2em 1.2em 2em 1.2em;
      overflow-x: auto;
    }
    .ib-categories-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      font-size: 1.04em;
      background: transparent;
    }
    .ib-categories-table thead th {
      background: #fff;
      color: #bfa2c7;
      font-weight: 700;
      padding: 1em 0.7em;
      position: sticky;
      top: 0;
      z-index: 2;
      border-bottom: 2px solid #fbeff3;
    }
    .ib-categories-table tbody tr {
      background: #fff;
      transition: background 0.15s;
    }
    .ib-categories-table tbody tr:nth-child(even) {
      background: #fbeff3;
    }
    .ib-categories-table td {
      padding: 1em 0.7em;
      color: #22223b;
      font-weight: 500;
      vertical-align: middle;
    }
    .ib-category-color-dot {
      display: inline-block;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 2px solid #e9aebc;
      box-shadow: 0 2px 8px #e9aebc22;
      vertical-align: middle;
    }
    .ib-categories-table .ib-btn-edit {
      background: #e9aebc;
      color: #fff;
      border: none;
      border-radius: 14px;
      padding: 0.6em 1.3em;
      font-weight: 600;
      font-size: 1em;
      margin-right: 0.5em;
      box-shadow: 0 2px 8px #e9aebc22;
      cursor: pointer;
      transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
    }
    .ib-categories-table .ib-btn-edit:hover {
      background: #e38ca6;
      box-shadow: 0 4px 12px #e9aebc33;
      transform: translateY(-1px);
    }
    .ib-categories-table .ib-btn-delete {
      background: #fde8e8;
      color: #e87171;
      border: none;
      border-radius: 14px;
      padding: 0.6em 1.3em;
      font-weight: 600;
      font-size: 1em;
      box-shadow: 0 2px 8px #e9aebc22;
      cursor: pointer;
      transition: background 0.18s, box-shadow 0.18s, transform 0.12s;
    }
    .ib-categories-table .ib-btn-delete:hover {
      background: #fca5a5;
      color: #fff;
      box-shadow: 0 4px 12px #e9aebc33;
      transform: translateY(-1px);
    }
    @media (max-width: 900px) {
      .ib-categories-admin {
        padding: 1.2em 0.2em 2em 0.2em;
      }
      .ib-add-category-form {
        flex-direction: column;
        gap: 1em;
        padding: 1.2em 0.7em 1em 0.7em;
        border-radius: 16px;
      }
      .ib-categories-table-card {
        padding: 1.2em 0.2em 1.2em 0.2em;
        border-radius: 14px;
      }
      .ib-categories-title {
        font-size: 1.3em;
        margin-bottom: 1em;
      }
    }
    </style>
    <!-- Titre stylisé -->
    <div class="ib-categories-title"><span class="dashicons dashicons-category"></span> Catégories de services</div>
    <!-- Formulaire d'ajout stylisé -->
    <form method="post" class="ib-add-category-form">
        <div>
            <label for="ib-cat-name-add">Nom</label><br>
            <input id="ib-cat-name-add" type="text" name="name" required class="ib-input">
        </div>
        <div>
            <label for="ib-cat-color-add">Couleur</label><br>
            <input id="ib-cat-color-add" type="color" name="color" value="#e9aebc">
        </div>
        <div>
            <label for="ib-cat-icon-add">Icône</label><br>
            <input id="ib-cat-icon-add" type="text" name="icon" class="ib-input">
        </div>
        <button type="submit" name="ib_add_category">Ajouter</button>
    </form>
    <!-- Tableau stylisé -->
    <div class="ib-categories-table-card">
    <table class="ib-categories-table">
        <thead><tr><th>Nom</th><th>Couleur</th><th>Icône</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($categories as $cat): ?>
            <tr>
                <td><?php echo esc_html($cat->name); ?></td>
                <td><span class="ib-category-color-dot" style="background:<?php echo esc_attr($cat->color); ?>;"></span></td>
                <td><?php echo esc_html($cat->icon); ?></td>
                <td>
                    <a href="?page=institut-booking-categories&edit=<?php echo $cat->id; ?>" class="ib-btn-edit">Éditer</a>
                    <a href="?page=institut-booking-categories&delete=<?php echo $cat->id; ?>" class="ib-btn-delete" onclick="return confirm('Supprimer cette catégorie ?')">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
<style>
.ib-add-category-form input[type="color"] {border:none;}
</style>
<?php
// Page de gestion des catégories
echo '<div class="ib-admin-main">';
// ...existing code...
echo '</div>';
