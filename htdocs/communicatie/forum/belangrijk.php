<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# belangrijk.php
# -------------------------------------------------------------------
# Verwerkt het belangrijk maken van ondewerpen in het forum.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forum.class.php';

if(!Forum::isModerator()){
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['forum_foutmelding']='Geen rechten voor het aanpassen van belangrijkheid';
	exit;
}

if(isset($_GET['topic'])){
	require_once 'forum/forumonderwerp.class.php';
	$forum = new ForumOnderwerp((int)$_GET['topic']);

	if(!$forum->toggleBelangrijkheid()){
		$_SESSION['melding']='Oeps, feutje, niet gelukt dus';
	}
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID());
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Niets om te belangrijk of niet belangrijk te maken.';
}


?>