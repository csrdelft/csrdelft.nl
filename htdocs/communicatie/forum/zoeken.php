<?php
/*
 * zoeken.php	| 	C.S.R. Delft
 *
 * Zoeken in het csrdelft.nl-forum
 */

require_once 'configuratie.include.php';

if($loginlid->hasPermission('P_FORUM_READ')) {
	require_once 'forum/forumcontent.class.php';
	$midden = new ForumContent('zoeken');
} else {
	# geen rechten
	require_once 'paginacontent.class.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

//uitgelogd heeft nieuwe layout
if(LoginLid::instance()->hasPermission('P_LOGGED_IN')){
	$layout = '';
} else {
	$layout = 'csrdelft2';
}

# pagina weergeven
$pagina=new csrdelft($midden, $layout);
$pagina->addStylesheet('forum.css');
$pagina->view();
