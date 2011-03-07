<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# bewerken.php
# -------------------------------------------------------------------
# Verwerkt het bewerken van berichten in het forum.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

require_once 'forum/forumonderwerp.class.php';

if(isset($_GET['post'])){
	$postID=(int)$_GET['post'];
	$forumonderwerp=ForumOnderwerp::loadByPostID($postID);
}elseif(isset($_GET['topic'], $_POST['titel'])){
	$forumonderwerp= new ForumOnderwerp($_GET['topic']);
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Onderwerp kan niet geladen worden (ForumOnderwerp::load()).';
	exit;
}



//is er uberhaupt wel een postID welke bewerkt moet worden
if(isset($_GET['post'])){
	//kijken of gebruiker dit bericht mag bewerken
	if($forumonderwerp->magBewerken($postID)){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			$bericht=trim($_POST['bericht']);

			//is er een bewerkreden opgegeven?
			if(isset($_POST['reden']) AND trim($_POST['reden'])!=''){
				$reden=strip_tags(trim($_POST['reden']));
			}else{
				$reden='';
			}
			if($forumonderwerp->editPost($postID, $bericht, $reden)){
				header('location: '.CSR_ROOT.'forum/reactie/'.$postID);
			}
		}
	}
}elseif(isset($_GET['topic'], $_POST['titel']) AND $forumonderwerp->isModerator()){
	if(strlen(trim($_POST['titel']))>=2){
		if(!$forumonderwerp->rename($_POST['titel'])){
			$_SESSION['melding']='Onderwerptitel wijzigigen mislukt (ForumOnderwerp::rename()).';
		}
	}else{
		$_SESSION['melding']='Titel moet minstens twee tekens lang zijn.';
	}
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$forumonderwerp->getID());
	exit;
}

?>
