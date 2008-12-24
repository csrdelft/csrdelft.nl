<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# citeren.php
# -------------------------------------------------------------------
# Geeft een onderwerp weer met een geciteerd bericht...
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');
require_once('forum/class.forumonderwerp.php');
require_once('forum/class.forumcontent.php');
require_once('forum/class.forumonderwerpcontent.php');

$forum = new ForumOnderwerp();
$forum->loadByPostID((int)$_GET['post']);

# Het middenstuk
if ($forum->magCiteren()){
	$midden = new ForumOnderwerpContent($forum);
	$midden->citeer((int)$_GET['post']);
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

## zijkolom in elkaar jetzen
$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($midden);
$pagina->setZijkolom($zijkolom);
$pagina->addStylesheet('forum.css');
$pagina->view();
?>
