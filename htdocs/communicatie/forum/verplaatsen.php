<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# verplaatsen.php
# -------------------------------------------------------------------
# Verwerkt het verplaatsen van berichten in het forum
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forum.class.php';

if(!Forum::isModerator()) {
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Niets te zoeken hier!';
	exit;
}

require_once 'forum/forumonderwerp.class.php';

if(isset($_GET['topic'])){
	$forumonderwerp=new ForumOnderwerp((int)$_GET['topic']);
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Geen onderwerp in te laeden, helaas (forum/verplaatsen.php)!';
	exit;
}

if(isset($_POST['newCat']) AND (int)$_POST['newCat']==$_POST['newCat'] AND $forumonderwerp->move($_POST['newCat'])){
}else{
	$_SESSION['melding']='Er ging iets mis bij het verplaatsen. (ForumOnderwerp::move())';
}
header('location: '.CSR_ROOT.'forum/onderwerp/'.$forumonderwerp->getID());
