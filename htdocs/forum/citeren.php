<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# citeren.php
# -------------------------------------------------------------------
# Geeft een onderwerp weer met een geciteerd bericht...
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');
require_once('class.forumonderwerp.php');
require_once('class.forumcontent.php');
require_once('class.forumonderwerpcontent.php');

$forum = new ForumOnderwerp();
$forum->loadByPostID((int)$_GET['post']);

# Het middenstuk
if ($forum->magCiteren()){
	$midden = new ForumOnderwerpContent($forum);
	$midden->citeer((int)$_GET['post']);
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}	

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	$forumcontent=new forumcontent($forum, 'lastposts');
	$zijkolom->add($forumcontent);
	
# pagina weergeven
$pagina=new csrdelft($midden);
$pagina->setZijkolom($zijkolom);

$pagina->view();
?>
