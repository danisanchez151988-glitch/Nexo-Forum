<?php
session_start();
require_once __DIR__ . '/config/config.php';
if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function auth() { return isset($_SESSION['user']); }
function csrf() { return e($_SESSION['csrf']); }
function require_auth() { if (!auth()) { header('Location: login.php'); exit; } }
