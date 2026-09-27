<?php
/**
 * NEXO FORUM - Global Functions
 * =============================
 */

/**
 * Sanitizar entrada del usuario
 */
function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(trim(strip_tags($input)));
}

/**
 * Validar email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
}

/**
 * Hash de contraseña segura
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verificar contraseña
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Redirigir
 */
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

/**
 * Obtener perfil del usuario actual
 */
function getCurrentUser() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $user_id = intval($_SESSION['user_id']);
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id LIMIT 1");
    
    return $result->fetch_assoc();
}

/**
 * Verificar si es administrador
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

/**
 * Verificar si es moderador
 */
function isModerator() {
    $user = getCurrentUser();
    return $user && in_array($user['role'], ['admin', 'moderator']);
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Tiempo transcurrido
 */
function timeAgo($date) {
    $time = strtotime($date);
    $current = time();
    $difference = $current - $time;
    
    $periods = [
        'segundo' => 60,
        'minuto' => 3600,
        'hora' => 86400,
        'día' => 604800,
        'semana' => 2592000,
        'mes' => 31536000,
        'año' => PHP_INT_MAX
    ];
    
    foreach ($periods as $key => $value) {
        if ($difference < $value) {
            $time = floor($difference / ($value / $periods[key($periods)]));
            if ($time == 0) $time = 1;
            return $time . ' ' . $key . ($time != 1 ? 's' : '') . ' atrás';
        }
    }
}

/**
 * Contar posts del usuario
 */
function getUserPostCount($user_id) {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as count FROM posts WHERE user_id = " . intval($user_id));
    return $result->fetch_assoc()['count'];
}

/**
 * Obtener rango del usuario
 */
function getUserBadge($user_id) {
    global $conn;
    $result = $conn->query("SELECT role FROM users WHERE id = " . intval($user_id));
    $user = $result->fetch_assoc();
    
    $badges = [
        'admin' => ['Administrador', '#EF4444'],
        'moderator' => ['Moderador', '#F59E0B'],
        'member' => ['Miembro', '#10B981'],
        'guest' => ['Visitante', '#9CA3AF']
    ];
    
    return $badges[$user['role']] ?? $badges['guest'];
}

/**
 * Limpiar HTML pero permitir etiquetas seguras
 */
function cleanHTML($text) {
    $allowed = '<b><i><u><br><p><a><strong><em><code><pre>';
    return strip_tags($text, $allowed);
}

/**
 * Convertir URLs en links
 */
function convertURLsToLinks($text) {
    $urlPattern = '/(https?|ftp):\/\/[^\s]+/i';
    return preg_replace($urlPattern, '<a href="$0" target="_blank" class="text-cyan-400">$0</a>', $text);
}

/**
 * Paginar resultados
 */
function paginate($total, $per_page, $current_page = 1) {
    $total_pages = ceil($total / $per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $per_page;
    
    return [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => $offset,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

/**
 * Log de actividades
 */
function logActivity($user_id, $action, $description = '') {
    global $conn;
    $user_id = intval($user_id);
    $action = sanitize($action);
    $description = sanitize($description);
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $conn->query("INSERT INTO activity_log (user_id, action, description, ip_address) 
                  VALUES ($user_id, '$action', '$description', '$ip')");
}

/**
 * Generar slug
 */
function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Validar URL
 */
function isValidURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) ? true : false;
}

/**
 * Obtener avatar del usuario
 */
function getAvatar($user_id) {
    global $conn;
    $result = $conn->query("SELECT avatar FROM users WHERE id = " . intval($user_id));
    $user = $result->fetch_assoc();
    
    if ($user['avatar'] && file_exists(UPLOAD_DIR . $user['avatar'])) {
        return SITE_URL . '/public/uploads/' . $user['avatar'];
    }
    
    return SITE_URL . '/public/images/default-avatar.png';
}

?>
