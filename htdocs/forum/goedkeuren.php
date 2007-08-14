<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# goedkeuren.php
# -------------------------------------------------------------------
# Verwerkt het goedkeuren van berichten en ondewerpen in het forum.
# -------------------------------------------------------------------

require_once('include.config.php');

if(!$lid->hasPermission('P_FORUM_MOD')){
	header('location: http://csrdelft.nl/forum/');
	$_SESSION['forum_foutmelding']='U heeft daar niets te zoeken.';
	exit;
}

require_once('class.forumonderwerp.php');
$forum = new ForumOnderwerp();

if(isset($_GET['post'])){
	$forum->loadByPostID((int)$_GET['post']);
	if($forum->keurGoed($iPostID)){
		header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
		$_SESSION['forum_foutmelding']='Onderwerp of bericht nu voor iedereen zichtbaar.';
	}else{
		header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
		$_SESSION['forum_foutmelding']='Goedkeuren ging mis.';
	}
}else{
	header('location: http://csrdelft.nl/forum/');
	$_SESSION['forum_foutmelding']='Geen postID gezet.';
}

?>