<?php

# instellingen & rommeltjes
require_once('include.config.php');
	
# Het middenstuk
if ($lid->hasPermission('P_FORUM_POST')){
	require_once('class.forumonderwerp.php');
	$forum = new ForumOnderwerp();
$forum->load($forum->getTopicVoorPostID((int)$_GET['post']));
	
	require_once('class.forumcontent.php');
	$midden = new ForumContent($forum, 'citeren');
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
