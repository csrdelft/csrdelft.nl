<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# rss.php
# -------------------------------------------------------------------
# Geeft een rss-feed terug.
# -------------------------------------------------------------------

require_once 'include.config.php';

header('Content-Type: text/xml; charset=UTF-8');
if($loginlid->hasPermission('P_FORUM_READ') OR isset($_GET['token'])){
	require_once 'forum/class.forum.php';
	require_once 'forum/class.forumcontent.php';
	$rss=new ForumContent('rss');

	$rss->view();
}else{
	echo 'jammer joh';
}


?>
