<?php

use CsrDelft\model\security\LoginModel;
use CsrDelft\Streeplijstcontent;
use CsrDelft\view\CsrLayoutPage;

require_once 'configuratie.include.php';

# C.S.R. Delft
#
# -------------------------------------------------------------------
# tools/streeplijst.php
# -------------------------------------------------------------------

if (!LoginModel::mag('P_OUDLEDEN_READ')) {
	redirect(CSR_ROOT);
}

require_once 'Streeplijstcontent.php';
$body = new Streeplijstcontent();


if (isset($_GET['pdf'])) {
	echo $body->getPdf();
} elseif (isset($_GET['iframe'])) {
	echo $body->getHtml();
} else {
	$pagina = new CsrLayoutPage($body);
	$pagina->view();
}
