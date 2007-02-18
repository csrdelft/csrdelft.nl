<?php

# prevent global namespace poisoning


# instellingen & rommeltjes
require_once('include.config.php');

# Het middenstuk
if ($lid->hasPermission('P_FORUM_READ')) {
	require_once('class.forum.php');
	require_once('class.forumcontent.php');
	require_once('class.forumpoll.php');   
	require_once('class.pollcontent.php');
	$forum=new Forum($lid, $db);
	$forumPoll=new ForumPoll($forum);
	$midden=new PollContent($forumPoll);
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}
## zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	$forumcontent=new forumcontent($forum, 'lastposts');
	$zijkolom->add($forumcontent);
	
$page=new csrdelft($midden, $lid, $db);
$page->setZijkolom($zijkolom);
$page->view();
	
?>
