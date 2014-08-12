<?php 
require_once 'configuratie.include.php';
$uid=LoginSession::instance()->getUid();
header('Location: /communicatie/profiel/'.$uid.'/voorkeuren');
?>