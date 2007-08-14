<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/forum/onderwerp.php
# -------------------------------------------------------------------
#  weergave van onderwerpen
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');
require_once('class.forumonderwerp.php');
require_once('class.forumcontent.php');
require_once('class.forumonderwerpcontent.php');

# Het middenstuk
if($lid->hasPermission('P_FORUM_READ')) {
	$forum = new ForumOnderwerp();
	//onderwerp laden
	$forum->load((int)$_GET['topic']);
	$midden = new ForumOnderwerpContent($forum);
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}	

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	$forumcontent=new forumcontent($forum, 'lastposts');
	$zijkolom->add($forumcontent);
	
$page=new csrdelft($midden);
$page->setZijkolom($zijkolom);
$page->view();
?>
