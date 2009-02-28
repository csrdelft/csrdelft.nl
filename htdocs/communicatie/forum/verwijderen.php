<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# verwijderen.php
# -------------------------------------------------------------------
# Verwerkt het verwijderen van berichten en onderwerpen in het forum.
# -------------------------------------------------------------------

require_once 'include.config.php';
require_once 'forum/class.forum.php';

if (!Forum::isModerator()) {
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Niets te zoeken hier!';
	exit;
}
require_once 'forum/class.forumonderwerp.php';
if(isset($_GET['post'])){
	$forumonderwerp=ForumOnderwerp::loadByPostID((int)$_GET['post']);
}elseif(isset($_GET['topic'])){
	$forumonderwerp=new ForumOnderwerp((int)$_GET['topic']);
}

//het juiste onderwerp of bericht verwijderen
if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	if($forumonderwerp->deletePost($iPostID)){
		//als er maar één bericht in het onderwerp is, verwijderd deletePost() automagisch
		//het hele onderwerp, dan dus niet weer naar dat onderwerp refreshen.
		if($forumonderwerp->getSize()<=1){
			header('location: '.CSR_ROOT.'forum/');
		}else{
			header('location: '.CSR_ROOT.'forum/onderwerp/'.$forumonderwerp->getID());
		}
	}else{
		header('location: '.CSR_ROOT.'forum/');
		$_SESSION['melding']='Verwijderen van bericht mislukt, iets mis met de db ofzo (ForumOnderwerp::deletePost()).';
	}
}elseif(isset($_GET['topic'])){
	if($forumonderwerp->delete()){
		header('location: '.CSR_ROOT.'forum/categorie/'.$forum->getCategorieID());
	}else{
		header('location: '.CSR_ROOT.'forum/');
		$_SESSION['melding']='Verwijderen van topic mislukt, iets mis met de db ofzo (ForumOnderwerp::delete()).';
	}
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Ik heb niets om te verwijderen';
}

?>
