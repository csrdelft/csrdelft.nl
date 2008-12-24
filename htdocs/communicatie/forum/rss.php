<?php
# instellingen & rommeltjes
require_once('include.config.php');

header('Content-Type: text/xml; charset=UTF-8');
if ($lid->hasPermission('P_FORUM_READ')) {
	require_once('forum/class.forum.php');
	$forum = new Forum();
	require_once('forum/class.forumcontent.php');
	$midden = new ForumContent($forum, 'rss');

	$midden->view();
}else{
	echo 'jammer joh';
}


?>
