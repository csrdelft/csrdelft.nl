<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# rss.php
# -------------------------------------------------------------------
# Geeft een rss-feed terug.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

header('Content-Type: text/xml; charset=UTF-8');
if($loginlid->hasPermission('P_FORUM_READ', null, $token_authorizable=true))){
	require_once 'forum/forum.class.php';
	require_once 'forum/forumcontent.class.php';
	$rss=new ForumContent('rss');

	$rss->view();
}else{
	echo 'jammer joh';
}


?>
