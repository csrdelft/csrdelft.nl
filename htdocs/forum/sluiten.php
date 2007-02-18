<?php
require_once('include.config.php');


if (!$lid->hasPermission('P_FORUM_MOD')) {
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['forum_foutmelding']='Geen rechten hier';
	exit;
}

require_once('class.forum.php');
$forum = new Forum($lid, $db);
if(isset($_GET['topic'])){
	$iTopicID=(int)$_GET['topic'];
	if(isset($_GET['sluiten'])){
		if($forum->sluitTopic($iTopicID)){
			header('location: '.CSR_ROOT.'forum/onderwerp/'.$iTopicID);
			exit;
		}else{
			header('location: '.CSR_ROOT.'forum/onderwerp/'.$iTopicID);
			$_SESSION['forum_foutmelding']='Oeps, feutje, niets gesloten dus.';
			exit;
		}
	}elseif(isset($_GET['openen'])){
		if($forum->openTopic($iTopicID)){
			header('location: '.CSR_ROOT.'forum/onderwerp/'.$iTopicID);
		}else{
			header('location: '.CSR_ROOT.'forum/onderwerp/'.$iTopicID);
			$_SESSION['forum_foutmelding']='Oeps, feutje, niets geopend dus.';
		}
	}else{
		header('location: '.CSR_ROOT.'forum/onderwerp/'.$iTopicID);
		$_SESSION['forum_foutmelding']='Hier snap ik geen snars van.';
	}
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['forum_foutmelding']='Niets om te sluiten of te openen.';
}


?>
