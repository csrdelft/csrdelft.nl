<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# index.php
# -------------------------------------------------------------------
# Pagina's weergeven uit het fotoalbum
# -------------------------------------------------------------------

require_once 'include.config.php';

require_once 'class.fotoalbum.php';
require_once 'class.fotoalbumcontent.php';

$pad=urldecode(substr($_SERVER['REQUEST_URI'], 19));
if($pad==''){
	$mapnaam='Fotoalbum';
}else{
	$mapnaam=explode('/',$pad);
	array_pop($mapnaam);
	$mapnaam=array_pop($mapnaam);
}

$fotoalbum = new Fotoalbum($pad, $mapnaam);
if($fotoalbum->magBekijken()){
	$fotoalbumcontent = new FotoalbumContent($fotoalbum);
	$fotoalbumcontent->setActie('album');
	
	$pagina=new csrdelft($fotoalbumcontent);
	$pagina->addStylesheet('fotoalbum.css');
	$pagina->addStylesheet('lightbox.css');
	$pagina->addScript('prototype.js');
	$pagina->addScript('scriptaculous.js?load=effects,builder');
	$pagina->addScript('fastlightbox.js');
	$pagina->view();
}else{
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden=new PaginaContent($pagina);
	$page=new csrdelft($midden);
	$page->view();
}

?>