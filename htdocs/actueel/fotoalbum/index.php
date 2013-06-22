<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# index.php
# -------------------------------------------------------------------
# Pagina's weergeven uit het fotoalbum
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

require_once 'fotoalbum.class.php';
require_once 'fotoalbumcontent.class.php';

$pad=htmlspecialchars(urldecode(substr($_SERVER['REQUEST_URI'], 19)));

if($pad==''){
	$mapnaam='Fotoalbum';
}else{
	$mapnaam=explode('/',$pad);
	array_pop($mapnaam);
	$mapnaam=array_pop($mapnaam);
}

$fotoalbum = new Fotoalbum($pad, $mapnaam);

//uitgelogd heeft nieuwe layout
if(LoginLid::instance()->hasPermission('P_LEDEN_READ')){
	$layout = '';
} else {
	$layout = 'csrdelft2';
}


if($fotoalbum->magBekijken()){
	$fotoalbumcontent = new FotoalbumContent($fotoalbum);
	$fotoalbumcontent->setActie('album');

	$pagina=new csrdelft($fotoalbumcontent, $layout);
	$pagina->addStylesheet('fotoalbum.css');
	$pagina->addStylesheet('jquery.prettyPhoto.css');
	$pagina->addScript('jquery.prettyPhoto.js');
	$pagina->setZijkolom(false);
	$pagina->view();


}else{
	require_once 'paginacontent.class.php';
	$pagina=new Pagina('geentoegang');
	$midden=new PaginaContent($pagina);
	$page=new csrdelft($midden, $layout);
	$page->view();
}