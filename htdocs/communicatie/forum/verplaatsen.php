<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# verplaatsen.php
# -------------------------------------------------------------------
# Verwerkt het verplaatsen van berichten in het forum
# -------------------------------------------------------------------

require_once('include.config.php');

if (!$lid->hasPermission('P_FORUM_MOD')) {
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Niets te zoeken hier!';
	exit;
}

require_once('class.forumonderwerp.php');
$forum = new ForumOnderwerp();

if(isset($_GET['topic'])){
	$forum->load((int)$_GET['topic']);
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Geen onderwerp in te laeden, helaas!';
	exit;
}

if(isset($_POST['newCat']) AND (int)$_POST['newCat']==$_POST['newCat'] AND 
		$forum->move($_POST['newCat'])){
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID());
	exit;
}else{
	header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID());
	$_SESSION['melding']='Er ging iets mis bij het verplaatsen. (ForumOnderwerp::move())';
	exit;
}