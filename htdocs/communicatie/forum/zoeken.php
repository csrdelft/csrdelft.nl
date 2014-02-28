<?php

/*
 * zoeken.php	| 	C.S.R. Delft
 *
 * Zoeken in het csrdelft.nl-forum
 */

require_once 'configuratie.include.php';

if ($loginlid->hasPermission('P_FORUM_READ')) {
	require_once 'forum/forumcontent.class.php';
	$midden = new ForumContent('zoeken');
} else {
	# geen rechten
	require_once 'MVC/model/CmsPaginaModel.class.php';
	$model = new CmsPaginaModel();
	$midden = new CmsPaginaView($model->getPagina('geentoegang'));
}

# pagina weergeven
if (LoginLid::instance()->hasPermission('P_LOGGED_IN')) {
	$pagina = new CsrLayoutPage($midden);
} else {
	//uitgelogd heeft nieuwe layout
	$pagina = new CsrLayout2Page($midden);
}
$pagina->addStylesheet('forum.css');
$pagina->view();
