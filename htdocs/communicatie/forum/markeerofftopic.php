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
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Onderwerp kan niet geladen worden (ForumOnderwerp::load()).';
	exit;
}


//is er uberhaupt wel een postID welke offtopic moet worden
if(isset($_GET['post'])){
	//kijken of gebruiker dit bericht mag bewerken
	if($forumonderwerp->isModerator()){
		//is er een bewerkreden opgegeven?
		if(isset($_POST['reden']) AND trim($_POST['reden'])!=''){
			$reden=strip_tags(trim($_POST['reden']));
		}else{
			$reden='';
		}
		if($forumonderwerp->markOfftopicPost($postID, $reden)){
			header('location: '.CSR_ROOT.'forum/reactie/'.$postID);
		}
	}
}

?>
