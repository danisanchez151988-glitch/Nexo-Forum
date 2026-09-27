<?php
require_auth();
$user_id = $_SESSION['user_id'];
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['_csrf'] ?? '')) die('Token inválido.');
    
    $bio = trim($_POST['bio'] ?? '');
    $bio = $conn->real_escape_string($bio);
    
    $conn->query("UPDATE users SET bio = '$bio' WHERE id = $user_id");
    $_SESSION['user']['bio'] = $bio;
    $success = 'Perfil actualizado.';
}

// Obtener stats del usuario
$stats = [];
$result = $conn->query("SELECT COUNT(*) as topics FROM topics WHERE user_id = $user_id");
$stats['topics'] = $result->fetch_assoc()['topics'];

$result = $conn->query("SELECT COUNT(*) as posts FROM posts WHERE user_id = $user_id");
$stats['posts'] = $result->fetch_assoc()['posts'];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mi perfil · Nexo</title>
    <link rel="stylesheet" href="assets/css/nexo.css">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="index.php"><span class="brand-mark">N</span><span>NEXO</span></a>
        <nav>
            <a href="index.php">Explorar</a>
            <a href="topics.php">Conversaciones</a>
            <a href="members.php">Miembros</a>
        </nav>
        <div class="top-actions">
            <a class="text-link" href="logout.php">Salir</a>
        </div>
    </header>
    <main class="shell">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar" style="background: linear-gradient(135deg, #7b3ff2, #6be7ed);"><?=e($user['username'][0])?></div>
                <div>
                    <h1><?=e($user['username'])?></h1>
                    <span class="profile-role" style="background: <?=$user['role'] === 'admin' ? '#ef4444' : (#'f59e0b')?>22; color: <?=$user['role'] === 'admin' ? '#ef4444' : '#f59e0b'?>"><?=ucfirst($user['role'])?></span>
                    <p class="profile-date">Miembro desde <?=date('M d, Y', strtotime($user['created_at']))?></p>
                </div>
            </div>
            
            <div class="profile-stats">
                <div class="stat-box">
                    <strong><?=$stats['topics']?></strong>
                    <span>conversaciones</span>
                </div>
                <div class="stat-box">
                    <strong><?=$stats['posts']?></strong>
                    <span>respuestas</span>
                </div>
            </div>
            
            <form method="post" class="profile-form">
                <input type="hidden" name="_csrf" value="<?=csrf()?>">
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" maxlength="200" rows="3" placeholder="Cuéntanos sobre ti..."><?=e($user['bio'] ?? '')?></textarea>
                </div>
                <button type="submit" class="button">Guardar cambios</button>
            </form>
        </div>
    </main>
    <style>
        .profile-container { max-width: 600px; margin: 50px auto 100px; }
        .profile-header { display: flex; gap: 25px; align-items: flex-start; margin-bottom: 40px; }
        .profile-avatar { width: 80px; height: 80px; min-width: 80px; border-radius: 50%; display: grid; place-items: center; color: white; font-size: 32px; font-weight: 700; }
        .profile-header h1 { margin: 0 0 10px; font-size: 32px; }
        .profile-role { display: inline-block; font: 11px 'DM Mono'; padding: 4px 10px; border-radius: 4px; margin-bottom: 10px; }
        .profile-date { color: var(--muted); font: 11px 'DM Mono'; margin: 0; }
        .profile-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 40px; padding: 25px 0; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
        .stat-box { text-align: center; }
        .stat-box strong { display: block; font-size: 24px; margin-bottom: 5px; }
        .stat-box span { font: 11px 'DM Mono'; color: var(--muted); }
        .profile-form { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 30px; }
        .profile-form .form-group { margin-bottom: 20px; }
        .profile-form label { display: block; color: #c5cad1; font-weight: 500; font-size: 12px; margin-bottom: 8px; }
        .profile-form textarea { width: 100%; background: #080a0d; border: 1px solid var(--line); color: var(--text); padding: 12px; border-radius: 6px; outline: 0; font: 14px Manrope; resize: vertical; }
        .profile-form textarea:focus { border-color: var(--cyan); }
    </style>
</body>
</html>