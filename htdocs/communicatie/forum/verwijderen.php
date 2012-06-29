<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# verwijderen.php
# -------------------------------------------------------------------
# Verwerkt het verwijderen van berichten en onderwerpen in het forum.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forum.class.php';

if (!Forum::isModerator()) {
	header('location: '.CSR_ROOT.'forum/');
	setMelding('Niets te zoeken hier!', -1);
	exit;
}
require_once 'forum/forumonderwerp.class.php';

$forumonderwerp=null;
if(isset($_GET['post'])){
	$forumonderwerp=ForumOnderwerp::loadByPostID((int)$_GET['post']);
}elseif(isset($_GET['topic'])){
	$forumonderwerp=new ForumOnderwerp((int)$_GET['topic']);
}else{
	setMelding('Ik heb niets om te verwijderen'.($forumonderwerp!==null ? $forumonderwerp->getError() : ''), -1);
	header('location: '.CSR_ROOT.'communicatie/forum?debug_session=1');
	exit;
}

if($forumonderwerp!==null AND $forumonderwerp->getError()!=''){
	setMelding('Verwijderen mislukt. '.$forumonderwerp->getError(), -1);
	header('location: '.CSR_ROOT.'communicatie/');
	exit;
}


//het juiste onderwerp of bericht verwijderen
if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	if($forumonderwerp->deletePost($iPostID)){
		//als er maar één bericht in het onderwerp is, verwijderd deletePost() automagisch
		//het hele onderwerp, dan dus niet weer naar dat onderwerp refreshen.
		if($forumonderwerp->getSize()<=1){
			setMelding('Verwijderen van onderwerp gelukt.', 1);
			header('location: '.CSR_ROOT.'communicatie/forum/');
		}else{
			setMelding('Verwijderen van post gelukt.', 1);
			header('location: '.CSR_ROOT.'communicatie/forum/onderwerp/'.$forumonderwerp->getID().'/'.$forumonderwerp->getPaginaCount().'#laatste');
		}
	}else{
		setMelding('Verwijderen van bericht mislukt, bestaat de post wel in de db? (ForumOnderwerp::deletePost()).', -1);
		header('location: '.CSR_ROOT.'communicatie/forum/');
	}
}elseif(isset($_GET['topic'])){
	if($forumonderwerp->delete()){
		setMelding('Verwijderen van topic gelukt.', 1);
		header('location: '.CSR_ROOT.'communicatie/forum/categorie/'.$forumonderwerp->getCategorieID());
	}else{
		setMelding('Verwijderen van topic mislukt, iets mis met de db ofzo (ForumOnderwerp::delete()).', -1);
		header('location: '.CSR_ROOT.'communicatie/forum/');
	}
}

?>
