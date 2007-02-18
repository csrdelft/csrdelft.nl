<?php
require_once('include.config.php');

if (!$lid->hasPermission('P_FORUM_MOD')) {
	header('location: '.CSR_ROOT.'/forum/');
	$_SESSION['forum_foutmelding']='Niets te zoeken hier!';
	exit;
}	
require_once('class.forum.php');
$forum = new Forum($lid, $db);
if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	$iTopicID=$forum->getTopicVoorPostID($iPostID);
	if($forum->deletePost($iPostID)){
		header('location: '.CSR_ROOT.'forum/onderwerp/'.$iTopicID);
	}else{
		header('location: '.CSR_ROOT.'forum/');
		$_SESSION['forum_foutmelding']='Verwijderen van bericht mislukt, iets mis met de db ofzo.';
	}
}elseif(isset($_GET['topic'])){
	$iTopicID=(int)$_GET['topic'];
	$iCatID=$forum->getCategorieVoorTopic($iTopicID);
	if($forum->deleteTopic($iTopicID)){
		header('location: '.CSR_ROOT.'forum/categorie/'.$iCatID);
	}else{
		header('location: '.CSR_ROOT.'forum/');
		$_SESSION['forum_foutmelding']='Verwijderen van topic mislukt, iets mis met de db ofzo.';
	}
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['forum_foutmelding']='Ik heb niets om te verwijderen';
}

?>
