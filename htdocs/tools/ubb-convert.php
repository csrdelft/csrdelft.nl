<?php
#
# Ubb-codes converteren naar de nieuwe parser...
#

require_once('include.config.php');


//forum-posts
$sPosts="SELECT id, tekst, bbcode_uid FROM forum_post;";
$rPosts=$db->query($sPosts);
while($aPost=$db->next($rPosts)){
	$sPost=str_replace(':'.$aPost['bbcode_uid'], '', $aPost['tekst']);
	$sPost=html_entity_decode($sPost);
	$sNewPost="UPDATE forum_post SET tekst='".$sPost."' WHERE id=".$aPost['id']." LIMIT 1;";
	$db->query($sNewPost);
}
//nieuws-berichten
$sPosts="SELECT id, tekst, bbcode_uid FROM nieuws;";
$rPosts=$db->query($sPosts);
while($aPost=$db->next($rPosts)){
	$sPost=str_replace(':'.$aPost['bbcode_uid'], '', $aPost['tekst']);
	$sPost=html_entity_decode($sPost);
	$sNewPost="UPDATE nieuws SET tekst='".$sPost."' WHERE id=".$aPost['id']." LIMIT 1;";
	$db->query($sNewPost);
}
?>
