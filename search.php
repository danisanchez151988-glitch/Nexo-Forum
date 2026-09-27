<?php
require_once __DIR__ . '/bootstrap.php';
$q = trim($_GET['q'] ?? '');
$results = ['topics' => [], 'posts' => [], 'members' => []];

if ($q && strlen($q) >= 2) {
    $search = $conn->real_escape_string($q);
    
    // Buscar temas
    $sql = "SELECT id, title, category_id, (SELECT color FROM categories WHERE id = category_id) as color FROM topics WHERE (title LIKE '%$search%' OR description LIKE '%$search%') AND is_approved = 1 LIMIT 10";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) $results['topics'][] = $row;
    }
    
    // Buscar posts
    $sql = "SELECT p.id, p.topic_id, p.content, t.title FROM posts p JOIN topics t ON t.id = p.topic_id WHERE p.content LIKE '%$search%' AND p.is_approved = 1 LIMIT 10";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) $results['posts'][] = $row;
    }
    
    // Buscar miembros
    $sql = "SELECT id, username, role FROM users WHERE username LIKE '%$search%' AND status = 'active' LIMIT 10";
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) $results['members'][] = $row;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$q ? 'Resultados: ' . e($q) : 'Buscar'?> · Nexo</title>
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
    </header>
    <main class="shell">
        <div style="padding: 40px 0;">
            <form method="get" style="display: flex; gap: 10px;">
                <input type="text" name="q" value="<?=e($q)?>" placeholder="Buscar conversaciones, miembros..." style="flex: 1; background: var(--panel); border: 1px solid var(--line); color: var(--text); padding: 12px; border-radius: 6px; outline: 0; font: 14px Manrope;">
                <button type="submit" class="button">Buscar</button>
            </form>
        </div>
        
        <?php if (!$q): ?>
        <div class="empty">
            <span>⌕</span>
            <h3>Busca en la comunidad</h3>
            <p>Encuentra conversaciones, miembros y más.</p>
        </div>
        <?php elseif (empty($results['topics']) && empty($results['posts']) && empty($results['members'])): ?>
        <div class="empty">
            <span>✦</span>
            <h3>No encontramos coincidencias</h3>
            <p>Intenta con otras palabras o explora las conversaciones.</p>
        </div>
        <?php else: ?>
            <?php if (!empty($results['topics'])): ?>
            <div style="margin-bottom: 50px;">
                <h2 style="margin-bottom: 20px;">Conversaciones (<?=count($results['topics'])?>)</h2>
                <?php foreach($results['topics'] as $topic): ?>
                <a href="topic.php?id=<?=$topic['id']?>" class="search-result">
                    <span class="result-type" style="color: <?=$topic['color']??'var(--cyan)'?>">💬</span>
                    <h3><?=e($topic['title'])?></h3>
                    <i>→</i>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($results['posts'])): ?>
            <div style="margin-bottom: 50px;">
                <h2 style="margin-bottom: 20px;">En respuestas (<?=count($results['posts'])?>)</h2>
                <?php foreach($results['posts'] as $post): ?>
                <a href="topic.php?id=<?=$post['topic_id']?>" class="search-result">
                    <span class="result-type">📄</span>
                    <div>
                        <small style="color: var(--muted);"><?=e($post['title'])?></small>
                        <h3><?=substr(e($post['content']), 0, 60)?>...</h3>
                    </div>
                    <i>→</i>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($results['members'])): ?>
            <div>
                <h2 style="margin-bottom: 20px;">Miembros (<?=count($results['members'])?>)</h2>
                <div class="members-grid-search">
                    <?php foreach($results['members'] as $member): ?>
                    <a href="profile.php?user=<?=$member['id']?>" class="member-search-card">
                        <div class="avatar" style="background: linear-gradient(135deg, #7b3ff2, #6be7ed);"><?=e($member['username'][0])?></div>
                        <h3><?=e($member['username'])?></h3>
                        <span style="font-size: 11px; color: var(--muted);"><?=ucfirst($member['role'])?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <style>
        .search-result { display: flex; gap: 15px; align-items: center; padding: 15px; background: var(--panel); border: 1px solid var(--line); border-radius: 8px; margin-bottom: 12px; transition: all .2s; }
        .search-result:hover { border-color: var(--cyan); }
        .result-type { font-size: 18px; }
        .search-result h3 { margin: 0; font-size: 15px; }
        .search-result i { margin-left: auto; color: var(--muted); font-style: normal; }
        .members-grid-search { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; }
        .member-search-card { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 20px; text-align: center; transition: all .2s; display: flex; flex-direction: column; align-items: center; }
        .member-search-card:hover { border-color: var(--cyan); }
        .member-search-card .avatar { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; color: white; font-weight: 700; margin-bottom: 10px; }
        .member-search-card h3 { margin: 8px 0; font-size: 13px; }
    </style>
</body>
</html>