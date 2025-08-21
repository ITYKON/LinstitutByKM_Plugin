<?php
if (!defined('ABSPATH')) exit;

class IB_Migrations {
    private static $migrations = [
        '0002-add-working-days-column',
        '0003-add-working-days-column'
    ];
    
    public static function run() {
        $completed_migrations = get_option('ib_completed_migrations', []);
        
        foreach (self::$migrations as $migration) {
            if (!in_array($migration, $completed_migrations)) {
                $file_path = plugin_dir_path(__FILE__) . "migrations/{$migration}.php";
                
                if (file_exists($file_path)) {
                    require_once $file_path;
                    $completed_migrations[] = $migration;
                    update_option('ib_completed_migrations', $completed_migrations);
                }
            }
        }
    }
    
    public static function get_last_completed_migration() {
        $completed = get_option('ib_completed_migrations', []);
        return !empty($completed) ? end($completed) : null;
    }
}
