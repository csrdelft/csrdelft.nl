<?php 
require_once 'configuratie.include.php';
$uid=LoginModel::getUid();
header('Location: /communicatie/profiel/'.$uid.'/voorkeuren');
?>