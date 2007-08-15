<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# verwijderen.php
# -------------------------------------------------------------------
# Verwerkt het verwijderen van berichten en ondewerpen in het forum.
# -------------------------------------------------------------------

require_once('include.config.php');

if (!$lid->hasPermission('P_FORUM_MOD')) {
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Niets te zoeken hier!';
	exit;
}	
require_once('class.forumonderwerp.php');
$forum = new ForumOnderwerp();

//het juiste onderwerp inladen
if(isset($_GET['post'])){
	$forum->loadByPostID($_GET['post']);
}elseif(isset($_GET['topic'])){
	$forum->load((int)$_GET['topic']);
}

//het juiste onderwerp of bericht verwijderen
if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	if($forum->deletePost($iPostID)){
		header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID());
	}else{
		header('location: '.CSR_ROOT.'forum/');
		$_SESSION['melding']='Verwijderen van bericht mislukt, iets mis met de db ofzo.';
	}
}elseif(isset($_GET['topic'])){
	if($forum->deleteTopic()){
		header('location: '.CSR_ROOT.'forum/categorie/'.$forum->getCatID());
	}else{
		header('location: '.CSR_ROOT.'forum/');
		$_SESSION['melding']='Verwijderen van topic mislukt, iets mis met de db ofzo.';
	}
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Ik heb niets om te verwijderen';
}

?>
