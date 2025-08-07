<?php
require_once('../../../wp-load.php');
global $wpdb;
$table = $wpdb->prefix . 'ib_employees';
$cols = $wpdb->get_results("DESCRIBE $table");
echo '<pre>';
foreach ($cols as $col) {
    echo $col->Field . ' | ' . $col->Type . "\n";
}
echo '</pre>'; 