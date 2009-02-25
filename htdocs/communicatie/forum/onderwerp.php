<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/communicatie/forum/onderwerp.php
# -------------------------------------------------------------------
#  weergave van onderwerpen
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once 'include.config.php';
require_once 'forum/class.forumonderwerp.php';
require_once 'forum/class.forumcontent.php';
require_once 'forum/class.forumonderwerpcontent.php';

# Het middenstuk
if($lid->hasPermission('P_FORUM_READ')) {
	$forum = new ForumOnderwerp();
	$forum->updateLaatstBekeken();

	if(isset($_GET['topic'])){
		$forum->load((int)$_GET['topic']);
	}elseif(isset($_GET['post'])){
		$forum->loadByPostID((int)$_GET['post']);
	}else{
		header('location: '.CSR_ROOT.'communicatie/forum/');
		$_SESSION['melding']='Gen onderwerp- of bericht-id opgegeven.';
		exit;
	}

	$midden = new ForumOnderwerpContent($forum);
	if(isset($_GET['post'])){
		$midden->citeer((int)$_GET['post']);
	}
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

$page=new csrdelft($midden);
$page->addStylesheet('forum.css');
$page->view();
?>
