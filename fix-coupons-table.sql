-- Script SQL pour corriger la table wp_ib_coupons pour le plugin Institut Booking
-- À exécuter dans phpMyAdmin ou Adminer
-- Sauvegardez votre base avant !

-- Renomme les colonnes si elles existent
ALTER TABLE wp_ib_coupons 
  CHANGE COLUMN `value` `discount` DECIMAL(10,2) NOT NULL,
  CHANGE COLUMN `start_date` `valid_from` DATE NOT NULL,
  CHANGE COLUMN `end_date` `valid_to` DATE NOT NULL;

-- Si les colonnes n'existent pas, les ajouter (ignore les erreurs si déjà là)
ALTER TABLE wp_ib_coupons 
  ADD COLUMN IF NOT EXISTS `discount` DECIMAL(10,2) NOT NULL AFTER `type`,
  ADD COLUMN IF NOT EXISTS `valid_from` DATE NOT NULL AFTER `usage_limit`,
  ADD COLUMN IF NOT EXISTS `valid_to` DATE NOT NULL AFTER `valid_from`;

-- Vérification finale
DESCRIBE wp_ib_coupons; 