<?php
// Script temporaire d'insertion de services, catégories et employés à partir d'une liste (extraction PDF à compléter)
// À placer dans le dossier du plugin puis exécuter une fois via le navigateur ou WP-CLI

if (!defined('ABSPATH')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; // adapte automatiquement le chemin
}

global $wpdb;

// Liste complète des services extraite du PDF
$services = [
    // Onglerie
    ['name'=>'Vernis classique','category'=>'Onglerie','price'=>1500,'employees'=>['Selma']],
    ['name'=>'VSP mains','category'=>'Onglerie','price'=>3000,'employees'=>['Selma']],
    ['name'=>'Gel mains','category'=>'Onglerie','price'=>4000,'employees'=>['Selma']],
    ['name'=>'Extention chablon','category'=>'Onglerie','price'=>5500,'employees'=>['Selma']],
    ['name'=>'Remplissage-extention','category'=>'Onglerie','price'=>3500,'employees'=>['Selma']],
    ['name'=>'Manucure russe','category'=>'Onglerie','price'=>1500,'employees'=>['Selma']],
    ['name'=>'VSP pieds','category'=>'Onglerie','price'=>3000,'employees'=>['Selma']],
    ['name'=>'Gel pieds','category'=>'Onglerie','price'=>4000,'employees'=>['Selma']],
    ['name'=>'Soin des mains','category'=>'Onglerie','price'=>3000,'employees'=>['Selma']],
    ['name'=>'Soin des pieds basic','category'=>'Onglerie','price'=>4500,'employees'=>['Selma']],
    ['name'=>'Soin des pieds basic+ vsp','category'=>'Onglerie','price'=>6500,'employees'=>['Selma']],
    ['name'=>'Soin des pieds complets','category'=>'Onglerie','price'=>6500,'employees'=>['Selma']],
    ['name'=>'Soin des pieds complets+ vsp','category'=>'Onglerie','price'=>8500,'employees'=>['Selma']],
    ['name'=>'French','category'=>'Onglerie','price'=>1000,'employees'=>['Selma']],
    ['name'=>'Babyboomer','category'=>'Onglerie','price'=>1000,'employees'=>['Selma']],
    ['name'=>'Reconstruction ongle m','category'=>'Onglerie','price'=>350,'employees'=>['Selma']],
    ['name'=>'Reconstruction ongle P','category'=>'Onglerie','price'=>150,'employees'=>['Selma']],
    ['name'=>'Effet chrome','category'=>'Onglerie','price'=>500,'employees'=>['Selma']],
    ['name'=>'Effet aquarium par doigt','category'=>'Onglerie','price'=>100,'employees'=>['Selma']],
    ['name'=>'Couleur','category'=>'Onglerie','price'=>2000,'employees'=>['Selma']],
    ['name'=>'Dépose','category'=>'Onglerie','price'=>1000,'employees'=>['Selma']],
    // Coiffure
    ['name'=>'Brushing','category'=>'Coiffure','price'=>1550,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Brushing L\'Oréal','category'=>'Coiffure','price'=>1800,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Coupe','category'=>'Coiffure','price'=>1000,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Coloration racine L\'Oréal','category'=>'Coiffure','price'=>4850,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Coloration L’Oréal','category'=>'Coiffure','price'=>7500,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Coloration racine','category'=>'Coiffure','price'=>2950,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Coloration','category'=>'Coiffure','price'=>3500,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Patine','category'=>'Coiffure','price'=>3000,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Balayage','category'=>'Coiffure','price'=>12000,'employees'=>[]],
    ['name'=>'Coiffure fête','category'=>'Coiffure','price'=>2000,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Wavy sans brush','category'=>'Coiffure','price'=>5000,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Wavy avec brush','category'=>'Coiffure','price'=>5500,'employees'=>['Lamia','Assia','Chafia']],
    // Soins capillaires
    ['name'=>'Myriam K5','category'=>'Soins capillaires','price'=>7000,'employees'=>['Lamia','Chafia']],
    ['name'=>'Power mask','category'=>'Soins capillaires','price'=>6500,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Soin réparateur 4 étapes','category'=>'Soins capillaires','price'=>4500,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Blowtox water','category'=>'Soins capillaires','price'=>1000,'employees'=>['Lamia','Assia','Chafia']],
    // Soin lissant
    ['name'=>'Protéine','category'=>'Soin lissant','price'=>12000,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Botox','category'=>'Soin lissant','price'=>15000,'employees'=>['Lamia','Assia','Chafia']],
    ['name'=>'Kératine','category'=>'Soin lissant','price'=>12000,'employees'=>['Lamia','Assia','Chafia']],
    // Soin visage
    ['name'=>'Nettoyage profond','category'=>'Soin visage','price'=>4000,'employees'=>['Chafia']],
    ['name'=>'Rides et fermeté','category'=>'Soin visage','price'=>9000,'employees'=>['Chafia']],
    ['name'=>'Hydratation profonde','category'=>'Soin visage','price'=>6500,'employees'=>['Chafia']],
    ['name'=>'Purifiant matifiant','category'=>'Soin visage','price'=>6000,'employees'=>['Chafia']],
    ['name'=>'Automne / Hiver','category'=>'Soin visage','price'=>5000,'employees'=>['Chafia']],
    ['name'=>'Peeling','category'=>'Soin visage','price'=>5000,'employees'=>['Chafia']],
    // Massage
    ['name'=>'Massage 1h','category'=>'Massage','price'=>4000,'employees'=>['Chafia']],
    ['name'=>'Massage 30min','category'=>'Massage','price'=>2500,'employees'=>['Chafia']],
    ['name'=>'Massage Demi jambes 15mn','category'=>'Massage','price'=>600,'employees'=>['Chafia']],
    ['name'=>'Massage Dos et jambes 30 min','category'=>'Massage','price'=>3000,'employees'=>['Chafia']],
    // Épilation
    ['name'=>'Lèvre supérieur','category'=>'Épilation','price'=>500,'employees'=>['Chafia','Assia']],
    ['name'=>'Sourcils reposses','category'=>'Épilation','price'=>500,'employees'=>['Assia','Lamia']],
    ['name'=>'Sourcils forme','category'=>'Épilation','price'=>1000,'employees'=>['Assia','Lamia']],
    ['name'=>'Visage','category'=>'Épilation','price'=>1500,'employees'=>['Chafia','Assia']],
    ['name'=>'Aisselles','category'=>'Épilation','price'=>500,'employees'=>['Chafia','Assia']],
    ['name'=>'Demi-bras','category'=>'Épilation','price'=>800,'employees'=>['Chafia','Assia']],
    ['name'=>'Bras entier','category'=>'Épilation','price'=>1200,'employees'=>['Chafia','Assia']],
    ['name'=>'Jambes entières','category'=>'Épilation','price'=>2000,'employees'=>['Chafia','Assia']],
    ['name'=>'Corps entier','category'=>'Épilation','price'=>6500,'employees'=>['Chafia','Assia']],
    ['name'=>'Maillot classique','category'=>'Épilation','price'=>1500,'employees'=>['Assia']],
    ['name'=>'Maillot intégral','category'=>'Épilation','price'=>3000,'employees'=>['Assia']],
    ['name'=>'Cuisses','category'=>'Épilation','price'=>1400,'employees'=>['Chafia','Assia']],
    ['name'=>'Pattes','category'=>'Épilation','price'=>500,'employees'=>['Chafia','Assia']],
    ['name'=>'Menton','category'=>'Épilation','price'=>400,'employees'=>['Chafia','Assia']],
    ['name'=>'Bas du dos','category'=>'Épilation','price'=>700,'employees'=>['Chafia','Assia']],
    ['name'=>'Dos entier','category'=>'Épilation','price'=>1500,'employees'=>['Chafia','Assia']],
    // Maquillage
    ['name'=>'Maquillage','category'=>'Maquillage','price'=>3500,'employees'=>['Assia','Lamia']],
];

// Création des catégories si non existantes
$cat_ids = [];
foreach ($services as $srv) {
    $cat = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_categories WHERE name = %s", $srv['category']));
    if (!$cat) {
        $wpdb->insert("{$wpdb->prefix}ib_categories", [
            'name' => $srv['category'],
        ]);
        $cat_id = $wpdb->insert_id;
    } else {
        $cat_id = $cat->id;
    }
    $cat_ids[$srv['category']] = $cat_id;
}

// Création des employés si non existants
$emp_ids = [];
foreach ($services as $srv) {
    foreach ($srv['employees'] as $emp_name) {
        $emp = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_employees WHERE name = %s", $emp_name));
        if (!$emp) {
            $wpdb->insert("{$wpdb->prefix}ib_employees", [
                'name' => $emp_name,
                'email' => strtolower($emp_name).'@demo.com',
                'phone' => '',
            ]);
            $emp_id = $wpdb->insert_id;
        } else {
            $emp_id = $emp->id;
        }
        $emp_ids[$emp_name] = $emp_id;
    }
}

// Création des services et liaisons
foreach ($services as $srv) {
    $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ib_services WHERE name = %s", $srv['name']));
    // Détection prix variable : si le service a min_price ou max_price dans la définition, on l'utilise
    $is_variable = isset($srv['min_price']) || isset($srv['max_price']);
    $variable_price = $is_variable ? 1 : 0;
    $min_price = $is_variable && isset($srv['min_price']) ? $srv['min_price'] : null;
    $max_price = $is_variable && isset($srv['max_price']) ? $srv['max_price'] : null;
    $price = isset($srv['price']) ? $srv['price'] : null;
    if (!$service) {
        $wpdb->insert("{$wpdb->prefix}ib_services", [
            'name' => $srv['name'],
            'category_id' => $cat_ids[$srv['category']],
            'duration' => 60,
            'price' => $price,
            'variable_price' => $variable_price,
            'min_price' => $min_price,
            'max_price' => $max_price,
        ]);
        $service_id = $wpdb->insert_id;
    } else {
        $service_id = $service->id;
    }
    // Lier les employés au service
    foreach ($srv['employees'] as $emp_name) {
        $emp_id = $emp_ids[$emp_name];
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}ib_service_employees WHERE service_id = %d AND employee_id = %d", $service_id, $emp_id));
        if (!$exists) {
            $wpdb->insert("{$wpdb->prefix}ib_service_employees", [
                'service_id' => $service_id,
                'employee_id' => $emp_id
            ]);
        }
    }
}
echo 'Import terminé';
