<?php

require_once 'configuratie.include.php';

# C.S.R. Delft
#
# -------------------------------------------------------------------
# tools/streeplijst.php
# -------------------------------------------------------------------

if (!LoginModel::mag('P_LEDEN_READ')) {
	redirect(CSR_ROOT);
}

require_once 'streeplijstcontent.class.php';
$body = new Streeplijstcontent();


if (isset($_GET['pdf'])) {
	echo $body->getPdf();
} elseif (isset($_GET['iframe'])) {
	echo $body->getHTML();
} else {
	$pagina = new CsrLayoutPage($body);
	$pagina->view();
}
