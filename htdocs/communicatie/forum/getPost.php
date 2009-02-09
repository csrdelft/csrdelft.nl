<?php
/*
 * getPost.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Geef een post met citaattags erom terug.
 */

require_once('include.config.php');


require_once('forum/class.forumonderwerp.php');
$forum = new ForumOnderwerp();

//is er uberhaupt wel een postID welke bewerkt moet worden
if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	$forum->loadByPostID($iPostID);


	if($forum->magCiteren()){
		$post=$forum->getSinglePost($iPostID);

		//$jssafePost=htmlspecialchars(str_replace(array("\r\n", "\r", "\n"), "\n", addslashes($post['tekst'])), ENT_QUOTES);

		echo '[citaat='.$post['uid'].']'.$post['tekst'].'[/citaat]';
	}
}
?>
