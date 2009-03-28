<?php

class NeerladenContent{
	var $_neerladen;
	
	function NeerladenContent($neerladen){
		$this->_neerladen=$neerladen;
	}
	
	function view(){
		$id = (int)$_GET['id'];
		
		// data uit de database halen
		$aDocData = $this->_neerladen->getDownloadData($id);
		$catid = $aDocData['categorie'];
		$filename = $aDocData['bestandsnaam'];
		
		// de gegevens combineren en alles verzamelen
		$path = DATA_PATH.'/documenten/uploads/'.$catid.'/'.$filename;
		$ext = $this->_neerladen->getExtension($filename);
		
		$mime = $this->_neerladen->getMimeType($ext);
		
		// de daadwerkelijke header
		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private',false); // nodig voor sommige browsers
		
		// content data
		header('Content-Description: File Transfer');
		// mime type
		if(isset($mime) && !empty($mime)){
			header('Content-Type: '.$mime);
		} else {
			header('Content-Type: application/octet-stream');
		}
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="'.$filename.'";');
		header('Content-Length: '.filesize($path));

		// bestand lezen
		readfile($path);
	}
}
?>
