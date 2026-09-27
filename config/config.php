<?php
define('SITE_URL', 'http://localhost');
define('SITE_NAME', 'Nexo Forum');
date_default_timezone_set('America/Mexico_City');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

function timeAgo($date) {
    $time = strtotime($date);
    $current = time();
    $diff = $current - $time;
    
    if ($diff < 60) return 'hace unos segundos';
    if ($diff < 3600) return 'hace ' . intval($diff/60) . ' minutos';
    if ($diff < 86400) return 'hace ' . intval($diff/3600) . ' horas';
    if ($diff < 604800) return 'hace ' . intval($diff/86400) . ' días';
    if ($diff < 2592000) return 'hace ' . intval($diff/604800) . ' semanas';
    return date('d M Y', $time);
}

function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function logActivity($user_id, $action, $description = '') {
    global $conn;
    $action = sanitize($action);
    $description = sanitize($description);
    $ip = $_SERVER['REMOTE_ADDR'];
    $conn->query("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES ($user_id, '$action', '$description', '$ip')");
}

function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(trim(strip_tags($input)));
}
?>