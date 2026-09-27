<?php
require_once __DIR__ . '/bootstrap.php';
$topic_id = intval($_GET['id'] ?? 0);
if (!$topic_id) redirect('/topics.php');

$result = $conn->query("SELECT t.*, c.name category_name, c.slug category_slug, c.color category_color, u.username, u.id user_id FROM topics t JOIN categories c ON c.id = t.category_id JOIN users u ON u.id = t.user_id WHERE t.id = $topic_id LIMIT 1");
$topic = $result->fetch_assoc();
if (!$topic) redirect('/topics.php');

// Incrementar vistas
$conn->query("UPDATE topics SET views = views + 1 WHERE id = $topic_id");

// Cargar posts
$posts = [];
$result = $conn->query("SELECT p.*, u.username, u.role FROM posts p JOIN users u ON u.id = p.user_id WHERE p.topic_id = $topic_id AND p.is_approved = 1 ORDER BY p.created_at ASC");
while ($row = $result->fetch_assoc()) $posts[] = $row;

// Procesar nuevo post
$error = '';
if (auth() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['_csrf'] ?? '')) $error = 'Token inválido.';
    elseif ($topic['is_locked'] && !isAdmin()) $error = 'Este tema está cerrado. No puedes responder.';
    else {
        $content = trim($_POST['content'] ?? '');
        if (strlen($content) < 5) $error = 'Tu respuesta debe tener al menos 5 caracteres.';
        else {
            $user_id = $_SESSION['user_id'];
            $content = $conn->real_escape_string($content);
            if ($conn->query("INSERT INTO posts (topic_id, user_id, content) VALUES ($topic_id, $user_id, '$content')")) {
                $conn->query("UPDATE topics SET replies = replies + 1, last_post_at = NOW(), last_post_by = $user_id WHERE id = $topic_id");
                logActivity($user_id, 'create_post', "Respondió en tema: $topic_id");
                redirect("/topic.php?id=$topic_id");
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=e($topic['title'])?> · Nexo</title>
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
            <?php if(auth()): ?>
            <a class="avatar" href="profile.php"><?=e($_SESSION['user']['username'][0])?></a>
            <?php endif; ?>
        </div>
    </header>
    <main class="shell">
        <div class="topic-head">
            <a href="topics.php?category=" class="crumb" style="color: <?=e($topic['category_color'])?>"><?=e($topic['category_name'])?></a>
            <h1><?=e($topic['title'])?></h1>
            <div class="topic-stats">
                <span>por <?=e($topic['username'])?></span>
                <span>· <?=timeAgo($topic['created_at'])?></span>
                <span>· <?=e($topic['views'])?> vistas</span>
                <span>· <?=e($topic['replies'])?> respuestas</span>
            </div>
        </div>
        
        <article class="post original">
            <div class="post-header">
                <div class="avatar-large" style="background: linear-gradient(135deg, #7b3ff2, #6be7ed);"><?=e($topic['username'][0])?></div>
                <div>
                    <strong><?=e($topic['username'])?></strong>
                    <span class="role-badge">Autor</span>
                    <div class="timestamp"><?=formatDate($topic['created_at'])?></div>
                </div>
            </div>
            <div class="post-content"><?=nl2br(e($topic['content']))?></div>
        </article>
        
        <section class="posts-section">
            <h2><?=e($topic['replies'])?> respuestas</h2>
            
            <?php if (!$posts): ?>
            <div class="empty" style="text-align: center; padding: 40px;">
                <span>✦</span>
                <h3>Aún no hay respuestas</h3>
                <p>Sé el primero en continuar esta conversación.</p>
            </div>
            <?php else: ?>
            <div class="posts-list">
                <?php foreach($posts as $post): ?>
                <article class="post">
                    <div class="post-header">
                        <div class="avatar-large" style="background: linear-gradient(135deg, #7b3ff2, #6be7ed);"><?=e($post['username'][0])?></div>
                        <div>
                            <strong><?=e($post['username'])?></strong>
                            <?php if(in_array($post['role'], ['admin', 'moderator'])): ?><span class="role-badge" style="background: <?=$post['role'] === 'admin' ? '#ef4444' : '#f59e0b'?>; color: white;"><?=ucfirst($post['role'])?></span><?php endif; ?>
                            <div class="timestamp"><?=formatDate($post['created_at'])?></div>
                        </div>
                    </div>
                    <div class="post-content"><?=nl2br(e($post['content']))?></div>
                    <div class="post-actions">
                        <button class="action-btn">♥ <?=$post['likes']?></button>
                        <?php if(isAdmin()): ?>
                        <a href="admin/delete-post.php?id=<?=$post['id']?>" class="action-btn danger">🗑 Eliminar</a>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
        
        <?php if(auth()): ?>
        <section class="reply-section">
            <h2>Tu respuesta</h2>
            <?php if($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="_csrf" value="<?=csrf()?>">
                <textarea name="content" placeholder="Aporta a la conversación..." required minlength="5" rows="6"></textarea>
                <button type="submit" class="button">Publicar respuesta</button>
            </form>
        </section>
        <?php else: ?>
        <div class="auth-prompt">
            <p>¿Quieres responder? <a href="login.php">Inicia sesión</a> o <a href="register.php">crea una cuenta</a>.</p>
        </div>
        <?php endif; ?>
    </main>
    <style>
        .topic-head { padding: 40px 0 20px; border-bottom: 1px solid var(--line); margin-bottom: 30px; }
        .topic-head .crumb { font-size: 12px; color: inherit; margin-bottom: 12px; display: inline-block; }
        .topic-head h1 { font-size: 38px; margin: 8px 0; letter-spacing: -.04em; line-height: 1.1; }
        .topic-stats { font: 12px 'DM Mono'; color: var(--muted); display: flex; gap: 10px; margin-top: 15px; }
        .post { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 25px; margin-bottom: 20px; }
        .post.original { border-left: 3px solid var(--cyan); }
        .post-header { display: flex; gap: 15px; margin-bottom: 20px; align-items: flex-start; }
        .avatar-large { width: 42px; height: 42px; min-width: 42px; border-radius: 50%; display: grid; place-items: center; color: white; font-weight: 700; }
        .post-header > div { flex: 1; }
        .post-header strong { display: block; margin-bottom: 3px; }
        .role-badge { display: inline-block; font: 10px 'DM Mono'; background: #6be7ed22; color: var(--cyan); padding: 3px 6px; border-radius: 3px; margin-right: 6px; }
        .timestamp { font: 11px 'DM Mono'; color: var(--muted); margin-top: 4px; }
        .post-content { line-height: 1.7; color: #ddd; margin-bottom: 15px; }
        .post-actions { display: flex; gap: 10px; }
        .action-btn { background: none; border: 1px solid var(--line); color: var(--muted); padding: 6px 12px; border-radius: 4px; cursor: pointer; font: 11px 'DM Mono'; transition: all .2s; }
        .action-btn:hover { border-color: var(--cyan); color: var(--cyan); }
        .action-btn.danger { color: #ef4444; border-color: #ef4444; }
        .posts-section h2 { margin: 40px 0 25px; }
        .reply-section { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 30px; margin-top: 40px; margin-bottom: 60px; }
        .reply-section h2 { margin-top: 0; }
        .reply-section textarea { width: 100%; background: #080a0d; border: 1px solid var(--line); color: var(--text); padding: 15px; border-radius: 6px; font: 14px Manrope; outline: 0; resize: vertical; margin-bottom: 15px; }
        .reply-section textarea:focus { border-color: var(--cyan); }
        .reply-section .button { margin: 0; }
        .auth-prompt { text-align: center; padding: 40px; color: var(--muted); }
        .auth-prompt a { color: var(--cyan); }
    </style>
</body>
</html>