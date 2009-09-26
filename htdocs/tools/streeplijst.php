<?php
# C.S.R. Delft
#
# -------------------------------------------------------------------
# tools/streeplijst.php
# -------------------------------------------------------------------

require_once 'include.config.php';

if(!$loginlid->hasPermission('P_LOGGED_IN')){ header('location: '.CSR_ROOT); exit; }

require_once 'class.streeplijstcontent.php';
$body=new Streeplijstcontent();


if(isset($_GET['pdf'])){
	echo $body->getPdf();
}elseif(isset($_GET['iframe'])){
	echo $body->getHtml();
}else{
	$pagina=new csrdelft($body);
	$pagina->setZijkolom(new stringincluder('<h4>Klus hier uw streeplijst</h4>' .
			'Wellicht handig voor polo\'s, weekeinden, wat dan ook. '));
	$pagina->view();
}
