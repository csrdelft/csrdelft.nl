<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# sluiten.php
# -------------------------------------------------------------------
# Verwerkt het openen en sluiten ondewerpen in het forum.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forum.class.php';

if(!Forum::isModerator()){
	header('location: '.CSR_ROOT.'forum/');
	setMelding('Geen rechten hier');
	exit;
}

if(isset($_GET['topic'])){
	require_once('forum/forumonderwerp.class.php');
	$forumonderwerp = new ForumOnderwerp((int)$_GET['topic']);

	if(!$forumonderwerp->toggleOpenheid()){
		setMelding('Oeps, feutje, niets gesloten dus.');
	}
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$forumonderwerp->getID());
}else{
	setMelding('Niets om te sluiten of te openen.');
	header('location: '.CSR_ROOT.'forum/');
}


?>
