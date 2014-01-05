<?php
# C.S.R. Delft
#
# -------------------------------------------------------------------
# tools/streeplijst.php
# -------------------------------------------------------------------

require_once 'configuratie.include.php';

if(!$loginlid->hasPermission('P_LOGGED_IN')){ header('location: '.CSR_ROOT); exit; }

require_once 'streeplijstcontent.class.php';
$body=new Streeplijstcontent();


if(isset($_GET['pdf'])){
	echo $body->getPdf();
}elseif(isset($_GET['iframe'])){
	echo $body->getHtml();
}else{
	$pagina=new csrdelft($body);
	$pagina->view();
}
