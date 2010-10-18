<?php

require_once 'configuratie.include.php';
require_once 'lid/ledenlijstcontent.class.php';

if(!($loginlid->hasPermission('P_LOGGED_IN') AND $loginlid->hasPermission('P_OUDLEDEN_READ'))){
	# geen rechten
	require_once 'paginacontent.class.php';
	$pagina=new csrdelft(new PaginaContent(new Pagina('geentoegang')));
	$pagina->view();
	exit;
}

$zoeker=new LidZoeker();
if(isset($_GET['q'])){
	$zoeker->parseQuery($_GET);
}

//redirect to profile if only one result.
if($zoeker->count()==1){
	$leden=$zoeker->getLeden();
	$lid=$leden[0];
	header('location: '.CSR_ROOT.'communicatie/profiel/'.$lid->getUid());
}

if(isset($_GET['addToGoogle'])){
	require_once('googlesync.class.php');
	GoogleSync::doRequestToken(CSR_ROOT.$_SERVER['REQUEST_URI']);

	$gSync=GoogleSync::instance();
	$message='<h2>Sync naar Google-contacts uitgevoerd</h2>'.$gSync->syncLidBatch($zoeker->getLeden());
	
	LedenlijstContent::invokeRefresh($message, CSR_ROOT.'communicatie/lijst.php');
}

$pagina=new csrdelft(new LedenlijstContent($zoeker));

$pagina->addStylesheet('js/datatables/css/datatables_basic.css');
$pagina->addStylesheet('ledenlijst.css');
$pagina->addScript('datatables/jquery.dataTables.min.js');

$pagina->view();

?>
