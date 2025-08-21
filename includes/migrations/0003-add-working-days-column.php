<?php
// Migration to add working_days column to ib_employees table
function ib_add_working_days_column() {
    global $wpdb;
    
    // Check if column already exists
    $column = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = %s 
        AND TABLE_NAME = '{$wpdb->prefix}ib_employees' 
        AND COLUMN_NAME = 'working_days'",
        DB_NAME
    ));
    
    // Add column if it doesn't exist
    if (empty($column)) {
        $wpdb->query("ALTER TABLE {$wpdb->prefix}ib_employees 
                      ADD COLUMN working_days TEXT NOT NULL DEFAULT '[]' AFTER role");
                      
        // Set default working days (all week)
        $default_days = json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}ib_employees SET working_days = %s",
            $default_days
        ));
    }
}

// Run the migration
ib_add_working_days_column();
