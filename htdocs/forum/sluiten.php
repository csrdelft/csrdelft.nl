<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# sluiten.php
# -------------------------------------------------------------------
# Verwerkt het openen en sluiten ondewerpen in het forum.
# -------------------------------------------------------------------

require_once('include.config.php');

if(!$lid->hasPermission('P_FORUM_MOD')){
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Geen rechten hier';
	exit;
}

require_once('class.forumonderwerp.php');
$forum = new ForumOnderwerp();
if(isset($_GET['topic'])){
	$forum->load((int)$_GET['topic']);
	if(!$forum->toggleOpenheid()){
		$_SESSION['melding']='Oeps, feutje, niets gesloten dus.';
	}
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID());
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Niets om te sluiten of te openen.';
}


?>
