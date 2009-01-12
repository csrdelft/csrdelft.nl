<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# bewerken.php
# -------------------------------------------------------------------
# Verwerkt het bewerken van berichten in het forum.
# -------------------------------------------------------------------

require_once('include.config.php');

//inhoud
require_once('forum/class.forumonderwerp.php');
$forum = new ForumOnderwerp();

#TODO: als het inline bewerken van berichten goed bevonden wordt, kan dit
#voor een groot deel hdb, weergeven van bewerkdingen hoeft dan niet meer hier.
#functies daarvoor in Forum kunnen dan ook weg.

//is er uberhaupt wel een postID welke bewerkt moet worden
if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	$forum->loadByPostID($iPostID);
	//kijken of gebruiker dit bericht mag bewerken
	if($forum->magBewerken($iPostID)){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			$bericht=trim($_POST['bericht']);

			//is er een bewerkreden opgegeven?
			if(isset($_POST['reden']) AND trim($_POST['reden'])!=''){
				$reden=strip_tags(trim($_POST['reden']));
			}else{
				$reden='';
			}
			if($forum->editPost($iPostID, $bericht, $reden)){
				header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID().'#post'.$iPostID);
			}
		}
	}
}elseif(isset($_GET['topic'], $_POST['titel']) AND $forum->isModerator()){
	//onderwerptitel bewerken.
	if($forum->load($_GET['topic'])){
		if(strlen(trim($_POST['titel']))>=2){
			if(!$forum->rename($_POST['titel'])){
				$_SESSION['melding']='Onderwerptitel wijzigigen mislukt (ForumOnderwerp::rename()).';
			}
		}else{
			$_SESSION['melding']='Titel moet minstens twee tekens lang zijn.';
		}
		header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID());
		exit;
	}else{
		header('location: '.CSR_ROOT.'forum/');
		$_SESSION['melding']='Onderwerp kan niet geladen worden (ForumOnderwerp::load()).';
		exit;
	}
}

?>
