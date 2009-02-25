<?php
/*
 * zoeken.php	| 	C.S.R. Delft
 *
 * Zoeken in het csrdelft.nl-forum
 */

require_once('include.config.php');

# Het middenstuk
if ($lid->hasPermission('P_FORUM_READ')) {
	require_once('forum/class.forum.php');
	$forum = new Forum();
	require_once('forum/class.forumcontent.php');
	$midden = new ForumContent($forum, 'zoeken');
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}


# pagina weergeven
$pagina=new csrdelft($midden);
$pagina->addStylesheet('forum.css');
$pagina->view();


?>
