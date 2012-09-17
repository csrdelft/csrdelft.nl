<?php 
require_once 'configuratie.include.php';
$uid=LoginLid::instance()->getUid();
header('Location: /communicatie/profiel/'.$uid.'/voorkeuren');
?>