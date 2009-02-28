<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# plakkerig.php
# -------------------------------------------------------------------
# Verwerkt het plakkerig maken van ondewerpen in het forum.
# -------------------------------------------------------------------

require_once 'include.config.php';
require_once 'forum/class.forum.php';

if(!Forum::isModerator()){
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['forum_foutmelding']='Geen rechten voor het aanpassen van plakkerigheid';
	exit;
}

if(isset($_GET['topic'])){
	require_once 'forum/class.forumonderwerp.php';
	$forum = new ForumOnderwerp((int)$_GET['topic']);

	if(!$forum->togglePlakkerigheid()){
		$_SESSION['melding']='Oeps, feutje, niet gelukt dus';
	}
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID());
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Niets om te sluiten of te openen.';
}


?>
