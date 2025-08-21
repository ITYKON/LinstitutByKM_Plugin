<?php
/**
 * Run migrations manually
 * 
 * Access this file directly in your browser to run migrations.
 * Example: https://your-site.com/wp-content/plugins/linstitutbykm_plugin/run-migration.php
 */

// Include WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('You do not have permission to run migrations.');
}

// Include migrations class
require_once plugin_dir_path(__FILE__) . 'includes/class-migrations.php';

// Run migrations
try {
    IB_Migrations::run();
    $last_migration = IB_Migrations::get_last_completed_migration();
    echo "<h1>Migrations executed successfully!</h1>";
    echo "<p>Last completed migration: " . ($last_migration ?: 'None') . "</p>";
} catch (Exception $e) {
    echo "<h1>Error running migrations:</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

// Show completed migrations
$completed = get_option('ib_completed_migrations', []);
echo "<h2>Completed Migrations:</h2>";
echo "<ul>";
foreach ($completed as $migration) {
    echo "<li>{$migration}</li>";
}
echo "</ul>";
