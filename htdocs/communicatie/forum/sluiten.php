<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# sluiten.php
# -------------------------------------------------------------------
# Verwerkt het openen en sluiten ondewerpen in het forum.
# -------------------------------------------------------------------

require_once 'include.config.php';
require_once 'forum/class.forum.php';

if(!Forum::isModerator()){
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Geen rechten hier';
	exit;
}

if(isset($_GET['topic'])){
	require_once('forum/class.forumonderwerp.php');
	$forumonderwerp = new ForumOnderwerp((int)$_GET['topic']);

	if(!$forumonderwerp->toggleOpenheid()){
		$_SESSION['melding']='Oeps, feutje, niets gesloten dus.';
	}
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$forumonderwerp->getID());
}else{
	$_SESSION['melding']='Niets om te sluiten of te openen.';
	header('location: '.CSR_ROOT.'forum/');
}


?>
