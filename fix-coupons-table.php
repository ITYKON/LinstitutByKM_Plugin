<?php
// Script temporaire pour corriger la table wp_ib_coupons
// À supprimer après usage !
require_once('../../../wp-load.php');
global $wpdb;

try {
    // Renomme les colonnes si elles existent
    @$wpdb->query("ALTER TABLE wp_ib_coupons CHANGE COLUMN `value` `discount` DECIMAL(10,2) NOT NULL");
    @$wpdb->query("ALTER TABLE wp_ib_coupons CHANGE COLUMN `start_date` `valid_from` DATE NOT NULL");
    @$wpdb->query("ALTER TABLE wp_ib_coupons CHANGE COLUMN `end_date` `valid_to` DATE NOT NULL");

    // Ajoute les colonnes si besoin
    @$wpdb->query("ALTER TABLE wp_ib_coupons ADD COLUMN `discount` DECIMAL(10,2) NOT NULL AFTER `type`");
    @$wpdb->query("ALTER TABLE wp_ib_coupons ADD COLUMN `valid_from` DATE NOT NULL AFTER `usage_limit`");
    @$wpdb->query("ALTER TABLE wp_ib_coupons ADD COLUMN `valid_to` DATE NOT NULL AFTER `valid_from`");

    echo '<b>Table coupons corrigée !</b>';
} catch (Exception $e) {
    echo 'Erreur : ' . $e->getMessage();
} 