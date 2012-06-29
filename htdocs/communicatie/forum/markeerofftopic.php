<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# markeerofftopic.php
# -------------------------------------------------------------------
# Verwerkt het offtopic markeren van berichten in het forum.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

require_once 'forum/forumonderwerp.class.php';

if(isset($_GET['post'])){
	$postID=(int)$_GET['post'];
	$forumonderwerp=ForumOnderwerp::loadByPostID($postID);
}else{
	header('location: '.CSR_ROOT.'communicatie/forum/');
	setMelding('Onderwerp kan niet geladen worden (ForumOnderwerp::load()).', -1);
	exit;
}


//is er uberhaupt wel een postID welke offtopic moet worden
if(isset($_GET['post'])){
	//kijken of gebruiker dit bericht mag bewerken
	if($forumonderwerp->getError()=='' AND $forumonderwerp->isModerator()){
		//is er een bewerkreden opgegeven?
		if(isset($_POST['reden']) AND trim($_POST['reden'])!=''){
			$reden=strip_tags(trim($_POST['reden']));
		}else{
			$reden='';
		}
		if($forumonderwerp->markPostOfftopic($postID, $reden)){
			header('location: '.CSR_ROOT.'communicatie/forum/reactie/'.$postID);
		}
	}else{
		header('location: '.CSR_ROOT.'communicatie/forum/');
		setMelding('Offtopic markeren mislukt. '.$forumonderwerp->getError(), -1);
		exit;
	}
}

?>
