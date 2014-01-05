<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# htdocs/communicatie/forum/onderwerp.php
# -------------------------------------------------------------------
#  weergave van forumonderwerpen
# -------------------------------------------------------------------


require_once 'configuratie.include.php';
require_once 'forum/forumonderwerp.class.php';
require_once 'forum/forumonderwerpcontent.class.php';

if($loginlid->hasPermission('P_FORUM_READ')) {
	if(isset($_GET['topic'])){
		if(isset($_GET['pagina'])){
			$pagina=$_GET['pagina'];
		}else{
			$pagina=1;
		}
	
		$forumonderwerp=new ForumOnderwerp((int)$_GET['topic'], $pagina);
		$forumonderwerp->updateLaatstGelezen();
	}elseif(isset($_GET['post'])){
		// zoek bijbehorende topic en redirect
		ForumOnderwerp::redirectByPostID((int)$_GET['post']);
	}else{
		header('location: '.CSR_ROOT.'communicatie/forum/');
		setMelding('Geen onderwerp- of bericht-id opgegeven.', -1);
		exit;
	}

	if(Instelling::get('forum_filter2008')=='ja'){
		$forumonderwerp->filter2008();
	}
	$midden = new ForumOnderwerpContent($forumonderwerp);
	if(isset($_GET['post'])){
		$midden->citeer((int)$_GET['post']);
	}
} else {
	require_once 'paginacontent.class.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

if(LoginLid::instance()->hasPermission('P_LEDEN_READ')){
	$page=new csrdelft($midden);
}
else {
	//uitgelogd heeft nieuwe layout
	$page=new csrdelft2($midden);
	$page->addStylesheet('ubb.css', '/layout/');
	$page->addScript('csrdelft.js', '/layout/');
}
$page->addStylesheet('forum.css', '/layout/');
$page->addScript('groepen.js', '/layout/');
$page->addScript('forum.js', '/layout/');

$page->view();
