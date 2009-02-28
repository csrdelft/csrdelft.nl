<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# index.php
# -------------------------------------------------------------------
# Weergave van categorieën en het forumoverzicht
# -------------------------------------------------------------------

require_once 'include.config.php';
require_once 'forum/class.forumcontent.php';

if($lid->hasPermission('P_FORUM_READ')) {
	require_once 'forum/class.forum.php';

	if(isset($_GET['forum'])){
		if($_GET['forum']==0){
			$body=new ForumContent('recent');
		}else{
			if(isset($_GET['pagina'])){
				$pagina=$_GET['pagina'];
			}else{
				$pagina=1;
			}
			require_once 'forum/class.forumcategorie.php';
			require_once 'forum/class.forumcategoriecontent.php';
			$forumcategorie=new ForumCategorie((int)$_GET['forum'], $pagina);
			$body=new ForumCategorieContent($forumcategorie);
		}
	}else{
		$body=new ForumContent('forum');
	}
}else{
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$body=new PaginaContent($pagina);
}


$page=new csrdelft($body);
$page->addStylesheet('forum.css');
$page->view();

?>
