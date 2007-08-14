<?php

# instellingen & rommeltjes
require_once('include.config.php');
require_once('class.forumonderwerp.php');
require_once('class.forumcontent.php');
require_once('class.forumonderwerpcontent.php');

# Het middenstuk
if ($lid->hasPermission('P_FORUM_POST')){
	$forum = new ForumOnderwerp();
	$forum->load($forum->getTopicVoorPostID((int)$_GET['post']));
	
	$midden = new ForumOnderwerpContent($forum);
	$midden->citeer((int)$_GET['post']);
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}	

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	$forumcontent=new forumcontent($forum, 'lastposts');
	$zijkolom->add($forumcontent);
	
# pagina weergeven
$pagina=new csrdelft($midden);
$pagina->setZijkolom($zijkolom);

$pagina->view();
?>
