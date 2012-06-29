<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# plakkerig.php
# -------------------------------------------------------------------
# Verwerkt het plakkerig maken van ondewerpen in het forum.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forum.class.php';

if(!Forum::isModerator()){
	header('location: '.CSR_ROOT.'forum/');
	setMelding('Geen rechten voor het aanpassen van plakkerigheid', -1);
	exit;
}

if(isset($_GET['topic'])){
	require_once 'forum/forumonderwerp.class.php';
	$forum = new ForumOnderwerp((int)$_GET['topic']);

	if(!$forum->togglePlakkerigheid()){
		setMelding('Oeps, feutje, niet gelukt dus', -1);
	}
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID());
}else{
	header('location: '.CSR_ROOT.'forum/');
	setMelding('Niets om te sluiten of te openen.', -1);
}


?>
