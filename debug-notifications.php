<?php
/**
 * Debug Notifications Page
 * 
 * This file provides a debug interface to check the notification table contents
 * and test notification functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add debug notifications menu to WordPress admin
 */
function ib_add_debug_notifications_menu() {
    add_management_page(
        'Debug Notifications',
        'Debug Notifications', 
        'manage_options',
        'debug-notifications',
        'ib_debug_notifications_page'
    );
}
add_action('admin_menu', 'ib_add_debug_notifications_menu');

/**
 * Display the debug notifications page
 */
function ib_debug_notifications_page() {
    global $wpdb;
    
    echo '<div class="wrap">';
    echo '<h1>Debug Notifications</h1>';
    
    // Handle form submissions
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_test_notification':
                ib_create_test_notification();
                break;
            case 'clear_all_notifications':
                ib_clear_all_notifications();
                break;
        }
    }
    
    // Display notification statistics
    $table_name = $wpdb->prefix . 'ib_notifications';
    $total_notifications = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $unread_notifications = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_read = 0");
    
    echo '<div class="notice notice-info">';
    echo '<p><strong>Notification Statistics:</strong></p>';
    echo '<ul>';
    echo '<li>Total notifications: ' . intval($total_notifications) . '</li>';
    echo '<li>Unread notifications: ' . intval($unread_notifications) . '</li>';
    echo '</ul>';
    echo '</div>';
    
    // Action buttons
    echo '<div style="margin: 20px 0;">';
    echo '<form method="post" style="display: inline-block; margin-right: 10px;">';
    echo '<input type="hidden" name="action" value="create_test_notification">';
    echo '<input type="submit" class="button button-primary" value="Create Test Notification">';
    echo '</form>';
    
    echo '<form method="post" style="display: inline-block;">';
    echo '<input type="hidden" name="action" value="clear_all_notifications">';
    echo '<input type="submit" class="button button-secondary" value="Clear All Notifications" onclick="return confirm(\'Are you sure you want to delete all notifications?\');">';
    echo '</form>';
    echo '</div>';
    
    // Display notifications table
    $notifications = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 50");
    
    if ($notifications) {
        echo '<h2>Recent Notifications (Last 50)</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Title</th>';
        echo '<th>Message</th>';
        echo '<th>Type</th>';
        echo '<th>Status</th>';
        echo '<th>Created</th>';
        echo '<th>Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($notifications as $notification) {
            $status = $notification->is_read ? 'Read' : 'Unread';
            $status_class = $notification->is_read ? 'read' : 'unread';
            
            echo '<tr class="' . esc_attr($status_class) . '">';
            echo '<td>' . intval($notification->id) . '</td>';
            echo '<td>' . esc_html($notification->title) . '</td>';
            echo '<td>' . esc_html($notification->message) . '</td>';
            echo '<td>' . esc_html($notification->type) . '</td>';
            echo '<td><span class="status-' . esc_attr($status_class) . '">' . esc_html($status) . '</span></td>';
            echo '<td>' . esc_html($notification->created_at) . '</td>';
            echo '<td>';
            if (!$notification->is_read) {
                echo '<a href="#" class="button button-small mark-read-btn" data-id="' . intval($notification->id) . '">Mark Read</a>';
            }
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<div class="notice notice-warning">';
        echo '<p>No notifications found in the database.</p>';
        echo '</div>';
    }
    
    // Test JavaScript functionality
    echo '<h2>Test JavaScript Functionality</h2>';
    echo '<div style="margin: 20px 0;">';
    echo '<button id="test-ajax-btn" class="button">Test AJAX Call</button>';
    echo '<button id="refresh-notifications-btn" class="button">Refresh Notifications</button>';
    echo '<div id="test-results" style="margin-top: 10px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; display: none;"></div>';
    echo '</div>';
    
    echo '</div>'; // End wrap
    
    // Add JavaScript for testing
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Test basic AJAX functionality
        $('#test-ajax-btn').click(function() {
            $('#test-results').show().html('Testing AJAX...');
            
            console.log('IBNotifBell object:', window.IBNotifBell);
            console.log('ib_admin_vars object:', window.ib_admin_vars);
            
            var ajaxurl = '';
            var nonce = '';
            
            if (typeof IBNotifBell !== 'undefined') {
                ajaxurl = IBNotifBell.ajaxurl || '';
                nonce = IBNotifBell.nonce || '';
                $('#test-results').append('<br>Using IBNotifBell - ajaxurl: ' + ajaxurl + ', nonce: ' + (nonce ? 'present' : 'missing'));
            } else if (typeof ib_admin_vars !== 'undefined') {
                ajaxurl = ib_admin_vars.ajaxurl || '';
                nonce = ib_admin_vars.nonce || '';
                $('#test-results').append('<br>Using ib_admin_vars - ajaxurl: ' + ajaxurl + ', nonce: ' + (nonce ? 'present' : 'missing'));
            } else {
                $('#test-results').append('<br>No AJAX variables found!');
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ib_get_notifications',
                    nonce: nonce
                },
                success: function(response) {
                    $('#test-results').append('<br><strong>Success:</strong> ' + JSON.stringify(response));
                },
                error: function(xhr, status, error) {
                    $('#test-results').append('<br><strong>Error:</strong> ' + error);
                }
            });
        });
        
        // Mark notification as read
        $('.mark-read-btn').click(function(e) {
            e.preventDefault();
            var notificationId = $(this).data('id');
            var button = $(this);
            
            var ajaxurl = '';
            var nonce = '';
            
            if (typeof IBNotifBell !== 'undefined') {
                ajaxurl = IBNotifBell.ajaxurl || '';
                nonce = IBNotifBell.nonce || '';
            } else if (typeof ib_admin_vars !== 'undefined') {
                ajaxurl = ib_admin_vars.ajaxurl || '';
                nonce = ib_admin_vars.nonce || '';
            }
            
            if (!ajaxurl || !nonce) {
                alert('AJAX variables not available');
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ib_mark_notification_read',
                    notification_id: notificationId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        button.closest('tr').removeClass('unread').addClass('read');
                        button.closest('td').find('.status-unread').removeClass('status-unread').addClass('status-read').text('Read');
                        button.remove();
                    } else {
                        alert('Failed to mark notification as read: ' + (response.data || 'Unknown error'));
                    }
                },
                error: function() {
                    alert('AJAX error occurred');
                }
            });
        });
        
        // Refresh notifications
        $('#refresh-notifications-btn').click(function() {
            location.reload();
        });
    });
    </script>
    
    <style>
    .status-read {
        color: #666;
    }
    .status-unread {
        color: #d63638;
        font-weight: bold;
    }
    tr.unread {
        background-color: #f0f8ff;
    }
    </style>
    <?php
}

/**
 * Create a test notification
 */
function ib_create_test_notification() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'ib_notifications';
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'title' => 'Test Notification',
            'message' => 'This is a test notification created at ' . current_time('mysql'),
            'type' => 'info',
            'is_read' => 0,
            'created_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%d', '%s')
    );
    
    if ($result) {
        echo '<div class="notice notice-success"><p>Test notification created successfully!</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Failed to create test notification: ' . $wpdb->last_error . '</p></div>';
    }
}

/**
 * Clear all notifications
 */
function ib_clear_all_notifications() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'ib_notifications';
    
    $result = $wpdb->query("DELETE FROM $table_name");
    
    if ($result !== false) {
        echo '<div class="notice notice-success"><p>All notifications cleared successfully! (' . intval($result) . ' notifications deleted)</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Failed to clear notifications: ' . $wpdb->last_error . '</p></div>';
    }
}
