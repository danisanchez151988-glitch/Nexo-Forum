<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function auth() {
    return isset($_SESSION['user']);
}

function getCurrentUser() {
    global $conn;
    if (!isset($_SESSION['user_id'])) return null;
    $id = intval($_SESSION['user_id']);
    $result = $conn->query("SELECT * FROM users WHERE id = $id LIMIT 1");
    return $result->fetch_assoc();
}

function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

function isModerator() {
    $user = getCurrentUser();
    return $user && in_array($user['role'], ['admin', 'moderator']);
}

function csrf() {
    return e($_SESSION['csrf']);
}

function require_auth() {
    if (!auth()) {
        header('Location: /login.php');
        exit;
    }
}

function redirect($path) {
    header('Location: ' . SITE_URL . $path);
    exit;
}
?>