<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# belangrijk.php
# -------------------------------------------------------------------
# Verwerkt het belangrijk maken van onderwerpen in het forum.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forum.class.php';

if(!Forum::isModerator()){
	header('location: '.CSR_ROOT.'communicatie/forum/');
	setMelding('Geen rechten voor het aanpassen van belangrijkheid', -1);
	exit;
}

if(isset($_GET['topic'])){
	require_once 'forum/forumonderwerp.class.php';
	$forum = new ForumOnderwerp((int)$_GET['topic']);
	if($forum->getError()!='' OR !$forum->toggleBelangrijkheid()){
		setMelding('Oeps, feutje, niet gelukt dus. '.$forum->getError(), -1);
	}
	header('location: '.CSR_ROOT.'communicatie/forum/onderwerp/'.$forum->getID());
}else{
	header('location: '.CSR_ROOT.'communicatie/forum/');
	setMelding('Niets om te belangrijk of niet belangrijk te maken.', -1);
}


?>
