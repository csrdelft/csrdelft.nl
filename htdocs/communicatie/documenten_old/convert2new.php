<?php
exit;

ini_set("memory_limit","128M");
set_time_limit(0);

require_once 'include.config.php';

if(!$loginlid->hasPermission('P_ADMIN')){
	echo 'alleen toegankelijk voor mensen met P_ADMIN';
}
echo '<h1>Converteren oude documenten:</h1><pre>';

require_once 'class.documenten.php';
require_once 'documenten/class.document.php';
require_once 'class.neerladen.php';
require_once 'mimemagic/MimeMagic.php';
$documenten = new Documenten();
$neerladen=new Neerladen();

$query='SELECT ID, naam, categorie, datum FROM _document WHERE verwijderd=\'0\'';
$data=$db->query2array($query);

$teller=0;
foreach($data as $document){
	$filedata=$documenten->getExtensionsByID($document['ID']);

	if(count($filedata)==0){
		continue;
	}else{
		$filedata=$neerladen->getDownloadData($filedata[0]['id']);
	}
	
	$filepath=DATA_PATH.'/documenten/uploads/'.$document['categorie'].'/'.$filedata['bestandsnaam'];
	
	if(file_exists($filepath)){
		$new=new Document(0);
		$new->setNaam(html_entity_decode($document['naam'], ENT_COMPAT, 'UTF-8'));
		$new->setCatID($document['categorie']);
		$new->setToegevoegd($document['datum'].' 12:34:56');
		$new->setBestandsnaam($filedata['bestandsnaam']);
		$new->setEigenaar('x027');
		$new->setSize(filesize($filepath));

		$ext=substr($filedata['bestandsnaam'], -3);
		switch($ext){
			case 'xls': $new->setMimetype('application/vnd.ms-excel'); break;
			case 'doc': $new->setMimetype('application/msword'); break;
			case 'ppt': $new->setMimetype('application/vnd.ms-powerpoint'); break;
			default:
				$new->setMimetype(MimeMagic::singleton()->guessMimeType($filepath, $ext));
			
		}

		
		if($new->save()){
			$new->putFile(file_get_contents($filepath));
			echo 'lalala, weer 1: '.$teller. ' file: '.$filepath. "\n";
		}
		
	}else{
		echo '!! '.$filepath. " Bestaat niet.\n";
	}

	if($teller++ > 3){
	//	exit;
	}
}

/*
foreach($db->query($query) as $categorie){
	$naam=$categorie[0];
	unset($categorie[0]);
	unset($categorie[1]);
	foreach($categorie as $document){
		$filedata=$neerladen->getDownloadData($document['ID']);
		$filepath=DATA_PATH.'/documenten/uploads/'.$filedata['categorie'].'/'.$filedata['bestandsnaam'];
		echo 'cat: '.$naam.' Downloadpad: '.$filepath.' Bestaat: '. (file_exists($filepath) ? 'ja' : 'nee')."\n";
	}
	exit;
}
*/
?>
