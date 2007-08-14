<?php

# prevent global namespace poisoning


# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
if ($lid->hasPermission('P_FORUM_READ')) {
	require_once('class.forum.php');
	$forum = new Forum();
	require_once('class.forumcontent.php');
	$midden = new ForumContent($forum, 'forum');
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
