<?php

use CsrDelft\model\SavedQuery;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\SavedQueryContent;

require_once 'configuratie.include.php';
require_once 'SavedQuery.php';

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
	$savedquery = new SavedQuery($id);
} else {
	$savedquery = null;
}

$pagina = new CsrLayoutPage(new SavedQueryContent($savedquery));
$pagina->view();
