<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# htdocs/tools/query.php
# -------------------------------------------------------------------
# Geeft de in savedquery opgeslagen query's weer


require_once 'configuratie.include.php';
require_once 'savedquery.class.php';

if(!$loginlid->hasPermission('P_LOGGED_IN')){ header('location: '.CSR_ROOT); }

$id=0;
if(isset($_GET['id']) AND (int)$_GET['id']==$_GET['id']){
	$id=(int)$_GET['id'];
	$savedquery=new savedQuery($id);
}else{
	$savedquery=null;
}

$pagina=new csrdelft(new SavedQueryContent($savedquery));
$pagina->zijkolom=false;
$pagina->view();
