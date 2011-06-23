<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# rss.php
# -------------------------------------------------------------------
# Geeft een rss-feed terug.
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forum.class.php';
require_once 'forum/forumcontent.class.php';

if(!$loginlid->hasPermission('P_FORUM_READ', $token_authorizable=true)){
	echo 'jammer joh';
	exit;
}

$rss=new ForumContent('rss');

header('Content-Type: application/rss+xml; charset=UTF-8');
$rss->view();


?>
