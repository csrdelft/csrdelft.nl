<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# index.php
# -------------------------------------------------------------------
# Weergave van categorieën en het forumoverzicht
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forumcontent.class.php';

if($loginlid->hasPermission('P_FORUM_READ')) {
	require_once 'forum/forum.class.php';

	if(isset($_GET['forum'])){
		if($_GET['forum']==0){
			$body=new ForumContent('recent');
		}else{
			if(isset($_GET['pagina'])){
				$pagina=$_GET['pagina'];
			}else{
				$pagina=1;
			}
			require_once 'forum/forumcategorie.class.php';
			require_once 'forum/forumcategoriecontent.class.php';
			$forumcategorie=new ForumCategorie((int)$_GET['forum'], $pagina);
			$body=new ForumCategorieContent($forumcategorie);
		}
	}else{
		$body=new ForumContent('forum');
	}
}else{
	# geen rechten
	require_once 'paginacontent.class.php';
	$pagina=new Pagina('geentoegang');
	$body=new PaginaContent($pagina);
}

//uitgelogd heeft nieuwe layout
if(LoginLid::instance()->hasPermission('P_LOGGED_IN')){
	$layout = '';
} else {
	$layout = 'csrdelft2';
}

$page=new csrdelft($body, $layout);
$page->addStylesheet('forum.css');
$page->addScript('forum.js');
if($layout=='csrdelft2'){
	$page->addStylesheet('csr2_ubb.css');
	$page->addScript('csrdelft.js');
}
$page->view();
