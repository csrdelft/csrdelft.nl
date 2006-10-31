<?php
# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

header('Content-Type: text/xml; charset=UTF-8');
if ($lid->hasPermission('P_FORUM_READ')) {
	require_once('class.forum.php');
	$forum = new Forum($lid, $db);
	require_once('class.forumcontent.php');
	$midden = new ForumContent($forum, 'rss');
	
	$midden->view();
}else{
	echo 'jammer joh';
}


?>
