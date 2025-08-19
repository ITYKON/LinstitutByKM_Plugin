<?php
require_once __DIR__ . '/class-services.php';
require_once __DIR__ . '/class-employees.php';
require_once __DIR__ . '/class-bookings.php';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
include dirname(__DIR__) . '/admin/matrix-snippet.php'; 