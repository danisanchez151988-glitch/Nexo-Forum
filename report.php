<?php
require_once __DIR__ . '/bootstrap.php';
if(!auth()){http_response_code(403);exit('Debes iniciar sesión.');}
if($_SERVER['REQUEST_METHOD']==='POST'&&hash_equals($_SESSION['csrf'],$_POST['_csrf']??'')){$topic=(int)($_POST['topic_id']??0);$reason=trim($_POST['reason']??'');if($topic&&$reason){$s=$conn->prepare('INSERT INTO reports(reporter_id,topic_id,reason,description) VALUES(?,?,?,?)');$s->bind_param('iiss',$_SESSION['user_id'],$topic,$reason,$reason);$s->execute();}}
header('Location: topic.php?id='.(int)($_POST['topic_id']??0));exit;
?>