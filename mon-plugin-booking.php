<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'database_name_here' );

/** Database username */
define( 'DB_USER', 'username_here' );

/** Database password */
define( 'DB_PASSWORD', 'password_here' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );
define('WP_DEBUG_DISPLAY', true);

/* Add any custom values between this line and the "stop editing" line. */

add_action('admin_enqueue_scripts', function($hook) {
    wp_enqueue_script(
        'ib-admin-script',
        plugins_url('assets/js/admin-script.js', __FILE__),
        array(),
        '1.0',
        true
    );
    
    // Localize script with all necessary AJAX data
    wp_localize_script('ib-admin-script', 'ib_admin_vars', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
    
    // Localize IBNotifBell object for notifications
    wp_localize_script('ib-admin-script', 'IBNotifBell', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ib_notifications_nonce')
    ));
});

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/api-rest.php';

// Add AJAX handlers for notifications
add_action('wp_ajax_ib_get_notifications', 'ib_ajax_get_notifications');
add_action('wp_ajax_ib_mark_notification_read', 'ib_ajax_mark_notification_read');
add_action('wp_ajax_ib_mark_all_notifications_read', 'ib_ajax_mark_all_notifications_read');

function ib_ajax_get_notifications() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ib_notifications_nonce')) {
        wp_die('Security check failed');
    }
    
    // Include notifications class if not loaded
    if (!class_exists('IB_Notifications')) {
        require_once plugin_dir_path(__FILE__) . 'includes/notifications.php';
    }
    
    $recent = IB_Notifications::get_recent('admin', 15);
    $unread_count = count(IB_Notifications::get_unread('admin'));
    
    wp_send_json_success([
        'recent' => $recent,
        'unread_count' => $unread_count
    ]);
}

function ib_ajax_mark_notification_read() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ib_notifications_nonce')) {
        wp_die('Security check failed');
    }
    
    $id = intval($_POST['id']);
    
    // Include notifications class if not loaded
    if (!class_exists('IB_Notifications')) {
        require_once plugin_dir_path(__FILE__) . 'includes/notifications.php';
    }
    
    IB_Notifications::mark_as_read($id);
    wp_send_json_success();
}

function ib_ajax_mark_all_notifications_read() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ib_notifications_nonce')) {
        wp_die('Security check failed');
    }
    
    // Include notifications class if not loaded
    if (!class_exists('IB_Notifications')) {
        require_once plugin_dir_path(__FILE__) . 'includes/notifications.php';
    }
    
    IB_Notifications::mark_all_as_read('admin');
    wp_send_json_success();
}
