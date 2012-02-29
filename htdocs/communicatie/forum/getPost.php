<?php
/*
 * getPost.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Geef een post -met eventuele citaattags erom- terug.
 */

require_once 'configuratie.include.php';

require_once 'forum/forumonderwerp.class.php';


if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	$forumonderwerp=ForumOnderwerp::loadByPostID($iPostID);

	$citaat=isset($_GET['citaat']);

	// Geef bericht terug als
	// - er gevraagd wordt om een citaat en de gebruiker deze post mag citeren.
	// of
	// - de gebruiker deze post mag bewerken.
	if(	($forumonderwerp->magCiteren() && $citaat) OR
		$forumonderwerp->magBewerken($iPostID)
	){
		$post=$forumonderwerp->getSinglePost($iPostID);

		if(!$loginlid->hasPermission('P_LOGGED_IN')){
			$post=CsrUBB::filterPrive($post);
		}
	
		if($citaat){ echo '[citaat='.$post['uid'].']'; }
		echo $post['tekst'];
		if($citaat){ echo '[/citaat]'; }
	}
}
?>
