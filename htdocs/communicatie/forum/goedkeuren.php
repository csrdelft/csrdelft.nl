<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# goedkeuren.php
# -------------------------------------------------------------------
# Verwerkt het goedkeuren van berichten en ondewerpen in het forum.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forum.class.php';

if(!Forum::isModerator()){
	header('location: '.CSR_ROOT.'/communicatie/forum/');
	setMelding('U heeft daar niets te zoeken.', -1);
	exit;
}

require_once 'forum/forumonderwerp.class.php';

if(isset($_GET['post'])){
	$postID=(int)$_GET['post'];
	$forumonderwerp=ForumOnderwerp::loadByPostID($postID);

	if($forumonderwerp->getError()=='' AND $forumonderwerp->keurGoed($postID)){
		setMelding('Onderwerp of bericht nu voor iedereen zichtbaar.', 1);
		ForumOnderwerp::redirectByPostID($postID);
	}else{
		header('location: '.CSR_ROOT.'forum/onderwerp/'.$forumonderwerp->getID());
		setMelding('Goedkeuren ging mis (forum/goedkeuren.php). '.$forumonderwerp->getError(), -1);
	}
}else{
	header('location: '.CSR_ROOT.'forum/');
	setMelding('Geen postID gezet (forum/goedkeuren.php).', -1);
}

?>
