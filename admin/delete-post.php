<?php
require_once __DIR__ . '/bootstrap.php';
if(!isAdmin()){http_response_code(403);exit('Acceso reservado a administradores.');}
$id=(int)($_GET['id']??0);if($id){$conn->query("DELETE FROM posts WHERE id=$id");}
header('Location: ../index.php');exit;
?>