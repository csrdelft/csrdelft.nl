<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# htdocs/tools/query.php
# -------------------------------------------------------------------
# Geeft de in savedquery opgeslagen query's weer


require_once 'include.config.php';
require_once 'class.savedquery.php';

if(!$loginlid->hasPermission('P_LOGGED_IN')){ header('location: '.CSR_ROOT); }

$id=0;
if(isset($_GET['id']) AND (int)$_GET['id']==$_GET['id']){
	$id=(int)$_GET['id'];
	$savedquery=new savedQuery($id);
}

$html='<h1>Opgeslagen Query\'s</h1>';

$html.=SavedQuery::getQueryselector($id);

if(isset($savedquery) AND $savedquery->magBekijken()){
	$html.=$savedquery->getHtml();
}
require_once 'class.stringincluder.php';

$pagina=new csrdelft(new stringincluder($html, 'Opgeslagen query\'s'));
$pagina->setZijkolom(false);

$pagina->view();
?>
