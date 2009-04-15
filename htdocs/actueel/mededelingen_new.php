<?php

require_once('include.config.php');

$mededelingId=0;
if(isset($_GET['mededelingId'])){
	$mededelingId=(int)$_GET['mededelingId'];
}



$actie='default';
if(isset($_GET['actie'])){
	$actie=$_GET['actie'];
}

require_once('mededelingen/class.mededeling.php');
require_once('mededelingen/class.mededelingcontent.php');

switch($actie){
	case 'verwijderen':
		if($mededelingId>0){
			$mededeling=new Mededeling($mededelingId);
			$mededeling->delete();
		}
		$content=new MededelingenContent();
		// TODO: refreshen.
	break; 

	case 'bewerken':
		$mededeling=new Mededeling($mededelingId);
		$content=new MededelingContent($mededeling);
	break; 

	default:
		$content=new MededelingenContent();
	break;
}

$page=new csrdelft($content);
$page->view();
?>