<?php

# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
if ($lid->hasPermission('P_FORUM_READ')) {
	require_once('class.forum.php');
	$forum = new Forum();
	require_once('class.forumcontent.php');
	$midden = new ForumContent($forum, 'zoeken');
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}

//kolom voor de zijkant maken.
$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($midden);
$pagina->setZijkolom($zijkolom);
$pagina->addStylesheet('forum.css');
$pagina->view();


?>
