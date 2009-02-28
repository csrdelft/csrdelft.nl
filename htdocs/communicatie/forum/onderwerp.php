<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/communicatie/forum/onderwerp.php
# -------------------------------------------------------------------
#  weergave van forumonderwerpen
# -------------------------------------------------------------------


require_once 'include.config.php';
require_once 'forum/class.forumonderwerp.php';
require_once 'forum/class.forumonderwerpcontent.php';

# Het middenstuk
if($lid->hasPermission('P_FORUM_READ')) {
	if(isset($_GET['topic'])){
		$forumonderwerp=new ForumOnderwerp((int)$_GET['topic']);
	}elseif(isset($_GET['post'])){
		$forumonderwerp=ForumOnderwerp::loadByPostID((int)$_GET['post']);
	}else{
		header('location: '.CSR_ROOT.'communicatie/forum/');
		$_SESSION['melding']='Gen onderwerp- of bericht-id opgegeven.';
		exit;
	}
	Forum::updateLaatstBekeken();

	$midden = new ForumOnderwerpContent($forumonderwerp);
	if(isset($_GET['post'])){
		$midden->citeer((int)$_GET['post']);
	}
} else {
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

$page=new csrdelft($midden);
$page->addStylesheet('forum.css');
$page->view();
?>
