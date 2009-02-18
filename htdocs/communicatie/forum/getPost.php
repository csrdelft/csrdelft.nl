<?php
/*
 * getPost.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Geef een post met citaattags erom terug.
 */

require_once('include.config.php');


require_once('forum/class.forumonderwerp.php');
$forum = new ForumOnderwerp();

if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	$forum->loadByPostID($iPostID);


	if($forum->magCiteren()){
		$post=$forum->getSinglePost($iPostID);

		if(!$lid->hasPermisson('P_LOGGED_IN')){
			$post=CsrUBB::filterPrive($post);
		}
		$citaat=isset($_GET['citaat']);
		if($citaat){ echo '[citaat='.$post['uid'].']'; }
		echo $post['tekst'];
		if($citaat){ echo '[/citaat]'; }
	}
}
?>
