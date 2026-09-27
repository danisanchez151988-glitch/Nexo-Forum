<?php
require_once __DIR__ . '/bootstrap.php';
$page = max(1, intval($_GET['page'] ?? 1));
$category_id = intval($_GET['category'] ?? 0);
$search = trim($_GET['q'] ?? '');
$per_page = 20;

$where = "t.is_approved = 1";
if ($category_id > 0) $where .= " AND t.category_id = $category_id";
if ($search) $where .= " AND (t.title LIKE '%" . $conn->real_escape_string($search) . "%' OR t.description LIKE '%" . $conn->real_escape_string($search) . "%')";

$result = $conn->query("SELECT COUNT(*) as total FROM topics WHERE $where");
$total = $result->fetch_assoc()['total'];
$pages = ceil($total / $per_page);
$offset = ($page - 1) * $per_page;

$topics = [];
$sql = "SELECT t.*, c.name category_name, c.color category_color, c.slug category_slug, u.username, (SELECT COUNT(*) FROM posts WHERE topic_id = t.id) as replies FROM topics t JOIN categories c ON c.id = t.category_id JOIN users u ON u.id = t.user_id WHERE $where ORDER BY t.is_pinned DESC, t.last_post_at DESC LIMIT $offset, $per_page";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) $topics[] = $row;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Conversaciones · Nexo</title>
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
            <a class="search" href="search.php">⌕ Buscar</a>
            <?php if(auth()): ?>
            <a class="avatar" href="profile.php"><?=e($_SESSION['user']['username'][0])?></a>
            <?php else: ?>
            <a class="button ghost" href="login.php">Entrar</a>
            <a class="button" href="register.php">Unirme</a>
            <?php endif; ?>
        </div>
    </header>
    <main class="shell">
        <div class="section-head" style="padding-top: 40px; margin-bottom: 35px;">
            <div>
                <span class="eyebrow">COMUNIDAD</span>
                <h1>Todas las conversaciones</h1>
            </div>
            <?php if(auth()): ?>
            <a class="button" href="new-topic.php">Nueva conversación →</a>
            <?php endif; ?>
        </div>
        <div class="topics-layout">
            <div class="topics-main">
                <?php if (!$topics): ?>
                <div class="empty">
                    <span>✦</span>
                    <h3>No hay conversaciones aquí</h3>
                    <p>Sé el primero en iniciar una conexión.</p>
                    <?php if(auth()): ?>
                    <a class="button" href="new-topic.php">Crear una conversación</a>
                    <?php else: ?>
                    <a class="button" href="register.php">Únete y empieza</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="topics-list">
                    <?php foreach($topics as $topic): ?>
                    <article class="topic-item">
                        <div class="topic-badge" style="background-color: <?=e($topic['category_color'])?>22; border-left: 3px solid <?=e($topic['category_color'])?>">
                            <span class="category-tag" style="color: <?=e($topic['category_color'])?>"><?=e($topic['category_name'])?></span>
                        </div>
                        <div class="topic-body">
                            <h2><a href="topic.php?id=<?=e($topic['id'])?>"><?=e($topic['title'])?></a></h2>
                            <p><?=e(substr($topic['description'] ?: $topic['content'], 0, 120))?>...</p>
                            <div class="topic-meta">
                                <span class="author">por <?=e($topic['username'])?></span>
                                <span class="stat">↳ <?=e($topic['replies'])?> respuestas</span>
                                <span class="stat">◉ <?=e($topic['views'])?> vistas</span>
                                <span class="time"><?=timeAgo($topic['last_post_at'] ?: $topic['created_at'])?></span>
                            </div>
                        </div>
                        <?php if($topic['is_pinned']): ?><span class="pin-badge">📌</span><?php endif; ?>
                    </article>
                    <?php endforeach; ?>
                </div>
                <?php if ($pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?><a href="topics.php?page=<?=$page-1?><?=$category_id ? '&category=' . $category_id : ''?><?=$search ? '&q=' . urlencode($search) : ''?>">← Anterior</a><?php endif; ?>
                    <span class="page-info">Página <?=$page?> de <?=$pages?></span>
                    <?php if ($page < $pages): ?><a href="topics.php?page=<?=$page+1?><?=$category_id ? '&category=' . $category_id : ''?><?=$search ? '&q=' . urlencode($search) : ''?>">Siguiente →</a><?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            <aside class="topics-side">
                <div class="panel-card">
                    <h3 style="margin-top: 0;">Categorías</h3>
                    <div style="display: grid; gap: 8px;">
                        <?php
                        $cats = $conn->query("SELECT id, name, slug, color, icon FROM categories ORDER BY id");
                        while($cat = $cats->fetch_assoc()):
                        ?>
                        <a href="topics.php?category=<?=$cat['id']?>" class="space" style="border-left: 3px solid <?=$cat['color']?>">
                            <span style="color: <?=$cat['color']?>; font-weight: 700;"><?=$cat['icon'] ?? '→'?></span>
                            <span style="flex: 1;"><?=e($cat['name'])?></span>
                            <i>→</i>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="panel-card">
                    <span class="eyebrow">REGLA ORO</span>
                    <h3>Conversa con intención</h3>
                    <p style="color: var(--muted); line-height: 1.6; margin-bottom: 0;">Cada publicación suma o resta energía a la comunidad. Antes de escribir, pregúntate: ¿esto añade valor?</p>
                </div>
            </aside>
        </div>
    </main>
    <footer>
        <span>© 2026 Nexo</span>
        <span>Conversaciones con propósito.</span>
        <?php if(isAdmin()): ?><a href="admin/">Panel admin</a><?php endif; ?>
    </footer>
    <style>
        .topics-layout { display: grid; grid-template-columns: 1fr 320px; gap: 50px; padding: 0 0 60px 0; }
        .topics-list { border-top: 1px solid var(--line); }
        .topic-item { display: grid; grid-template-columns: 4px 1fr auto; gap: 20px; padding: 20px 0; border-bottom: 1px solid var(--line); align-items: start; }
        .topic-badge { height: 100%; border-radius: 4px; }
        .category-tag { font: 11px 'DM Mono'; letter-spacing: .1em; text-transform: uppercase; font-weight: 600; }
        .topic-body h2 { font-size: 18px; margin: 0 0 8px; line-height: 1.3; letter-spacing: -.02em; }
        .topic-body h2 a:hover { color: var(--cyan); }
        .topic-body p { color: var(--muted); margin: 0 0 12px; line-height: 1.5; font-size: 13px; }
        .topic-meta { display: flex; gap: 20px; flex-wrap: wrap; font: 11px 'DM Mono'; color: var(--muted); align-items: center; }
        .time { margin-left: auto; }
        .pin-badge { font-size: 18px; }
        .pagination { display: flex; justify-content: center; gap: 20px; margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--line); align-items: center; }
        .pagination a { color: var(--cyan); font-weight: 600; }
        .page-info { color: var(--muted); font-size: 12px; }
        .topics-side h3 { margin: 0 0 15px; }
        .topics-side p { margin-bottom: 0; font-size: 13px; }
        @media(max-width: 900px) {
            .topics-layout { grid-template-columns: 1fr; gap: 30px; }
            .topics-side { order: -1; }
        }
    </style>
</body>
</html>