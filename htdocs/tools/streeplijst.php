<?php
# C.S.R. Delft
#
# -------------------------------------------------------------------
# tools/streeplijst.php
# -------------------------------------------------------------------

# instellingen & rommeltjes
require_once('include.config.php');

if(!$lid->hasPermission('P_LOGGED_IN')){ header('location: '.CSR_ROOT); exit; }
require_once('class.streeplijstcontent.php');
$body=new Streeplijstcontent($lid, $db);


if(isset($_GET['pdf'])){
	echo $body->getPdf();
}elseif(isset($_GET['iframe'])){
	echo $body->getHtml();
}else{
	$pagina=new csrdelft($body, $lid, $db);
	$pagina->setZijkolom(new stringincluder('Klus hier uw streeplijst'));
	$pagina->view();
}
