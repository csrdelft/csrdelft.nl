<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# htdocs/tools/query.php
# -------------------------------------------------------------------
# Geeft de in savedquery opgeslagen query's weer


require_once('include.config.php');
require_once('class.savedquery.php');

if(!$lid->hasPermission('P_LOGGED_IN')){ header('location: '.CSR_ROOT); }

if(isset($_GET['id']) AND (int)$_GET['id']==$_GET['id']){
	$savedquery=new savedQuery((int)$_GET['id']);
}

$html='<h1>Opgeslagen Query\'s</h1>';

$html.=SavedQuery::getQueryselector((int)$_GET['id']);

if(isset($savedquery) AND $savedquery->magBekijken()){
	$html.=$savedquery->getHtml();
}
require_once('class.stringincluder.php');
$body=new stringincluder($html, 'Opgeslagen query\'s');
$pagina=new csrdelft($body);
$pagina->view();
?>
