<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# htdocs/tools/query.php
# -------------------------------------------------------------------
# Geeft de in savedquery opgeslagen query's weer


require_once('include.config.php');
require_once('class.savedquery.php');



if(isset($_GET['id']) AND (int)$_GET['id']==$_GET['id']){
	$savedquery=new savedQuery((int)$_GET['id']);
}

$html='Hieronder kunt u enkele opgeslagen query\'s bekijken.';

$html.='<form method="get" action="query.php"><select name="id"  onchange="this.form.submit();">';
foreach(SavedQuery::getQuerys() as $query){
	$html.='<option value="'.$query['ID'].'"';
	if(isset($_GET['id']) AND $_GET['id']==$query['ID']){ $html.='selected="selected"'; }
	$html.='>'.$query['beschrijving'].'</option>';
}
$html.='</select> <input type="submit" value="laten zien"></form>';

if(isset($savedquery) AND $savedquery->magBekijken()){
	$html.=$savedquery->getHtml();
}
require_once('class.stringincluder.php');
$body=new stringincluder($html, 'Opgeslagen query\'s');
$pagina=new csrdelft($body);
$pagina->view();
?>
