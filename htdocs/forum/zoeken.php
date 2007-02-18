<?php

# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
if ($lid->hasPermission('P_FORUM_READ')) {
	require_once('class.forum.php');
	$forum = new Forum($lid, $db);
	require_once('class.forumcontent.php');
	$midden = new ForumContent($forum, 'zoeken');
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}

//kolom voor de zijkant maken.
$zijkolom=new kolom();

//laatste forumberichten toevoegen aan zijkolom:
require_once('class.forum.php'); 
require_once('class.forumcontent.php');
$forum=new forum($lid, $db);
$lastposts=new forumcontent($forum, 'lastposts');

$zijkolom->add($lastposts);

# pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);

$pagina->view();


?>
