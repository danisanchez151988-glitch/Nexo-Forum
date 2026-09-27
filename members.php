<?php
require_once __DIR__ . '/bootstrap.php';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 25;
$search = trim($_GET['q'] ?? '');

$where = "status = 'active'";
if ($search) $where .= " AND username LIKE '%" . $conn->real_escape_string($search) . "%'";

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE $where");
$total = $result->fetch_assoc()['total'];
$pages = ceil($total / $per_page);
$offset = ($page - 1) * $per_page;

$members = [];
$result = $conn->query("SELECT id, username, role, created_at FROM users WHERE $where ORDER BY created_at DESC LIMIT $offset, $per_page");
while ($row = $result->fetch_assoc()) $members[] = $row;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Miembros · Nexo</title>
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
                <h1>Personas que construyen Nexo</h1>
            </div>
        </div>
        
        <div class="members-grid">
            <?php if (!$members): ?>
            <div class="empty" style="grid-column: 1/-1; text-align: center;">
                <span>✦</span>
                <h3>No se encontraron miembros</h3>
            </div>
            <?php else: ?>
                <?php foreach($members as $member): ?>
                <div class="member-card">
                    <div class="member-avatar" style="background: linear-gradient(135deg, #7b3ff2, #6be7ed);"><?=e($member['username'][0])?></div>
                    <h3><?=e($member['username'])?></h3>
                    <span class="member-role" style="background: <?=$member['role'] === 'admin' ? '#ef4444' : ($member['role'] === 'moderator' ? '#f59e0b' : '#6be7ed')?>22; color: <?=$member['role'] === 'admin' ? '#ef4444' : ($member['role'] === 'moderator' ? '#f59e0b' : '#6be7ed')?>"><?=ucfirst($member['role'])?></span>
                    <p class="joined">Se unió <?=timeAgo($member['created_at'])?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($pages > 1): ?>
        <div class="pagination" style="margin-top: 50px;">
            <?php if ($page > 1): ?><a href="members.php?page=<?=$page-1?>">← Anterior</a><?php endif; ?>
            <span class="page-info">Página <?=$page?> de <?=$pages?></span>
            <?php if ($page < $pages): ?><a href="members.php?page=<?=$page+1?>">Siguiente →</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
    <style>
        .members-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 25px; padding: 40px 0 80px; }
        .member-card { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 25px; text-align: center; transition: transform .2s, border-color .2s; }
        .member-card:hover { transform: translateY(-4px); border-color: var(--cyan); }
        .member-avatar { width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 15px; display: grid; place-items: center; color: white; font-weight: 700; font-size: 24px; }
        .member-card h3 { margin: 15px 0 8px; font-size: 16px; }
        .member-role { display: inline-block; font: 10px 'DM Mono'; padding: 4px 8px; border-radius: 4px; margin-bottom: 12px; }
        .joined { font: 11px 'DM Mono'; color: var(--muted); margin: 0; }
        @media(max-width: 900px) {
            .members-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        }
    </style>
</body>
</html>