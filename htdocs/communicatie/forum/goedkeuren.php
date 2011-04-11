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
	$_SESSION['forum_foutmelding']='U heeft daar niets te zoeken.';
	exit;
}

require_once 'forum/forumonderwerp.class.php';

if(isset($_GET['post'])){
	$postID=(int)$_GET['post'];
	$forumonderwerp=ForumOnderwerp::loadByPostID($postID);

	if($forumonderwerp->keurGoed($postID)){
		$_SESSION['melding']='Onderwerp of bericht nu voor iedereen zichtbaar.';
		ForumOnderwerp::redirectByPostID($postID);
	}else{
		header('location: '.CSR_ROOT.'forum/onderwerp/'.$forumonderwerp->getID());
		$_SESSION['melding']='Goedkeuren ging mis (forum/goedkeuren.php).';
	}
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Geen postID gezet (forum/goedkeuren.php).';
}

?>
