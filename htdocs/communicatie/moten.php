<?php

# instellingen & rommeltjes
require_once('include.config.php');

#moten toevoegen
if($loginlid->hasPermission('P_LEDEN_MOD') AND isset($_POST['moot'], $_POST['naam']) AND is_array($_POST['naam'])){
	$iKringGetal=$loginlid->getMaxKringen($_POST['moot'])+1;
	foreach($_POST['naam'] as $sKringLid){
		//echo 'uid: '.$sKringLid.' moot: '.$_POST['moot'].' kring: '.$iKringGetal;
		// TODO: doe dit elegant
		//$lid->addUid2kring($sKringLid, $iKringGetal, $_POST['moot']);
	}
	header('location: '.CSR_ROOT.'communicatie/moten.php');
}

if ($loginlid->hasPermission('P_LEDEN_READ')) {
	# Het middenstuk
	require_once('class.motencontent.php');
	$midden = new MotenContent();
} else {
	# geen rechten
	require_once 'class.paginacontent.php';
	$pagina=new Pagina('geentoegang');
	$midden = new PaginaContent($pagina);
}

$pagina=new csrdelft($midden);
$pagina->addStylesheet('groepen.css');
$pagina->view();

?>
