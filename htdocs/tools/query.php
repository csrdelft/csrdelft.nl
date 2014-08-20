<?php

require_once 'configuratie.include.php';
require_once 'savedquery.class.php';

# C.S.R. Delft
# -------------------------------------------------------------------
# htdocs/tools/query.php
# -------------------------------------------------------------------
# Geeft de in savedquery opgeslagen query's weer

if (!LoginModel::mag('P_LOGGED_IN')) {
	redirect(CSR_ROOT);
}

$id = 0;
if (isset($_GET['id']) AND (int) $_GET['id'] == $_GET['id']) {
	$id = (int) $_GET['id'];
	$savedquery = new savedQuery($id);
} else {
	$savedquery = null;
}

$pagina = new CsrLayoutPage(new SavedQueryContent($savedquery));
$pagina->zijkolom = false;
$pagina->view();
