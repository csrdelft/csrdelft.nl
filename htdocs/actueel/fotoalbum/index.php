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

if($fotoalbum->magBekijken()){
	$fotoalbumcontent = new FotoalbumContent($fotoalbum);
	$fotoalbumcontent->setActie('album');
	
	if(LoginLid::instance()->hasPermission('P_LEDEN_READ')){
		$pagina=new csrdelft($fotoalbumcontent);
		$pagina->setZijkolom(false);
	}
	else {
		//uitgelogd heeft nieuwe layout
		$pagina=new csrdelft($fotoalbumcontent, 'csrdelft2');
	}
	$pagina->addStylesheet('fotoalbum.css', '/layout/');
	$pagina->addStylesheet('jquery.prettyPhoto.css', '/layout/');
	$pagina->addScript('jquery.prettyPhoto.js', '/layout/');
	$pagina->view();
}
else{
	require_once 'paginacontent.class.php';
	$pagina=new Pagina('geentoegang');
	$midden=new PaginaContent($pagina);
	
	if(LoginLid::instance()->hasPermission('P_LEDEN_READ')){
		$pagina=new csrdelft($midden);
	}
	else {
		//uitgelogd heeft nieuwe layout
		$page=new csrdelft($midden, 'csrdelft2');
	}
	$page->view();
}