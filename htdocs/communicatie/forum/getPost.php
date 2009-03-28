<?php
/*
 * getPost.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Geef een post -met eventuele citaattags erom- terug.
 */

require_once 'include.config.php';

require_once 'forum/class.forumonderwerp.php';


if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	$forumonderwerp=ForumOnderwerp::loadByPostID($iPostID);


	if($forumonderwerp->magCiteren()){
		$post=$forumonderwerp->getSinglePost($iPostID);

		if(!$loginlid->hasPermission('P_LOGGED_IN')){
			$post=CsrUBB::filterPrive($post);
		}
		$citaat=isset($_GET['citaat']);
		if($citaat){ echo '[citaat='.$post['uid'].']'; }
		echo $post['tekst'];
		if($citaat){ echo '[/citaat]'; }
	}
}
?>
