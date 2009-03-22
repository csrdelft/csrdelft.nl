<?php

# C.S.R. Delft
# Hans van Kranenburg
# sep 2005

# /leden/profiel.php

require_once 'include.config.php';

require_once 'lid/class.profiel.php';

if(isset($_GET['uid'])){
	$uid = $_GET['uid'];
}else{
	$uid = $loginlid->getUid();
}
$error=0;
if(!($loginlid->hasPermission('P_LEDEN_READ') or $loginlid->hasPermission('P_OUDLEDEN_READ'))){
	$error=3;
}

if(isset($_GET['a']) AND $_GET['a']=='rssToken'){//} AND $uid==$loginlid->getUid()){
	$loginlid->getToken();
	header('location: '.CSR_ROOT.'communicatie/profiel/'.$uid.'#forum');
	exit;
}
switch ($error) {
	case 0:
	case 2:
		require_once 'lid/class.profielcontent.php';
		$midden = new ProfielContent(LidCache::getLid($uid));

	break;
	default:
		# geen rechten
		require_once 'class.paginacontent.php';
		$pagina=new Pagina('geentoegang');
		$midden = new PaginaContent($pagina);
}

$pagina=new csrdelft($midden);
$pagina->addStylesheet('profiel.css');
$pagina->addScript('profiel.js');
$pagina->view();

?>
