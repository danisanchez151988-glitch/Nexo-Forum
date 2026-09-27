<?php
require_auth();
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['_csrf'] ?? '')) $error = 'Token inválido.';
    else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        
        if (strlen($title) < 5 || strlen($content) < 20) $error = 'El título debe tener al menos 5 caracteres y el contenido 20.';
        elseif ($category_id <= 0) $error = 'Selecciona una categoría válida.';
        else {
            $title = $conn->real_escape_string($title);
            $description = $conn->real_escape_string($description);
            $content = $conn->real_escape_string($content);
            $slug = generateSlug($title) . '-' . uniqid();
            
            if ($conn->query("INSERT INTO topics (category_id, user_id, title, slug, description, content) VALUES ($category_id, $user_id, '$title', '$slug', '$description', '$content')")) {
                $topic_id = $conn->insert_id;
                logActivity($user_id, 'create_topic', "Creó tema: $title");
                redirect("/topic.php?id=$topic_id");
            } else $error = 'Error al crear la conversación.';
        }
    }
}

$categories = [];
$result = $conn->query("SELECT id, name, color FROM categories ORDER BY id");
while ($row = $result->fetch_assoc()) $categories[] = $row;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Nueva conversación · Nexo</title>
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
            <a class="avatar" href="profile.php"><?=e($_SESSION['user']['username'][0])?></a>
        </div>
    </header>
    <main class="shell">
        <div class="editor-container">
            <div class="editor-back"><a href="topics.php">← Volver</a></div>
            <div class="editor-card">
                <h1>Inicia una conversación</h1>
                <p>Comparte una idea, pregunta o reflexión con la comunidad.</p>
                
                <?php if($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?>
                
                <form method="post">
                    <input type="hidden" name="_csrf" value="<?=csrf()?>">
                    
                    <div class="form-group">
                        <label>¿De qué es esta conversación?</label>
                        <select name="category_id" required>
                            <option value="">Selecciona una categoría...</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?=$cat['id']?>" style="color: <?=$cat['color']?>"><?=e($cat['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Título</label>
                        <input type="text" name="title" placeholder="Sé específico, claro y directo" required minlength="5" maxlength="255">
                        <small>Mínimo 5 caracteres. Los títulos claros generan mejores conversaciones.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción (opcional)</label>
                        <input type="text" name="description" placeholder="Contexto breve de tu idea..." maxlength="255">
                        <small>Una línea que resuma el tema. Ayuda a otros a saber si esto les interesa.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Tu mensaje</label>
                        <textarea name="content" placeholder="Desarrolla tu pensamiento aquí..." required minlength="20" maxlength="5000" rows="10"></textarea>
                        <small>Mínimo 20 caracteres. Máximo 5000.</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button">Publicar conversación →</button>
                        <a href="topics.php" class="text-link">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <style>
        .editor-container { max-width: 720px; margin: 50px auto 100px; }
        .editor-back { margin-bottom: 25px; }
        .editor-back a { color: var(--muted); font-size: 13px; }
        .editor-back a:hover { color: var(--cyan); }
        .editor-card { background: var(--panel); border: 1px solid var(--line); border-radius: 10px; padding: 40px; }
        .editor-card h1 { font-size: 32px; margin: 0 0 8px; letter-spacing: -.03em; }
        .editor-card p { color: var(--muted); margin: 0 0 35px; }
        .form-group { margin-bottom: 28px; display: grid; gap: 8px; }
        .form-group label { color: #c5cad1; font-weight: 500; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; background: #080a0d; border: 1px solid var(--line); color: var(--text); padding: 12px; border-radius: 6px; outline: 0; font: 14px Manrope; font-family: Manrope; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--cyan); }
        .form-group small { font-size: 11px; color: var(--muted); }
        .form-actions { display: flex; gap: 15px; align-items: center; margin-top: 35px; }
        .form-actions .button { margin: 0; }
    </style>
</body>
</html>